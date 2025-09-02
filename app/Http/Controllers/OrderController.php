<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Models\TableSession;
use App\Services\OrderService;
use App\Services\QueueService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $orderService;
    protected $queueService;
    protected $notificationService;

    public function __construct(
        OrderService $orderService,
        QueueService $queueService,
        NotificationService $notificationService
    ) {
        $this->orderService = $orderService;
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
    }

    // Scan QR Code dan ambil info meja
    public function scanTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid QR code'], 400);
        }

        $table = Table::with(['restaurant', 'currentSession'])
                     ->where('qr_code', $request->qr_code)
                     ->first();

        if (!$table) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        if (!$table->restaurant->is_active) {
            return response()->json(['error' => 'Restaurant is currently closed'], 400);
        }

        // Create atau ambil session yang aktif
        $session = $table->currentSession();
        if (!$session) {
            $session = $table->sessions()->create([
                'restaurant_id' => $table->restaurant_id,
                'session_token' => TableSession::generateSessionToken($table->id),
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        // Update table status
        $table->update(['status' => 'occupied']);

        return response()->json([
            'success' => true,
            'data' => [
                'table' => $table,
                'restaurant' => $table->restaurant,
                'session_token' => $session->session_token,
                'menu_url' => route('menu.show', [
                    'restaurant' => $table->restaurant->slug,
                    'session' => $session->session_token
                ])
            ]
        ]);
    }

    // Tampilkan menu untuk meja tertentu
    public function showMenu($restaurantSlug, $sessionToken)
    {
        $session = TableSession::with(['restaurant', 'table'])
                               ->where('session_token', $sessionToken)
                               ->where('status', 'active')
                               ->first();

        if (!$session) {
            return redirect()->route('error')->with('message', 'Invalid session');
        }

        $restaurant = $session->restaurant;
        $categories = $restaurant->categories()
                                ->with(['menuItems' => function($query) {
                                    $query->where('is_available', true)
                                          ->with('variants')
                                          ->orderBy('sort_order');
                                }])
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->get();

        return view('menu.index', compact('restaurant', 'categories', 'session'));
    }

    // Buat pesanan baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variants' => 'nullable|array',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'special_instructions' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            $order = $this->orderService->createOrder($request->all());
            
            // Add to queue
            $queueNumber = $this->queueService->addToQueue($order);
            
            // Send notifications
            $this->notificationService->orderReceived($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $order->load(['items.menuItem', 'table', 'queue']),
                    'queue_number' => $queueNumber,
                    'estimated_wait_time' => $this->queueService->getEstimatedWaitTime($order->restaurant_id)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    // Update status pesanan (untuk staff)
    public function updateStatus(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,preparing,ready,served,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $staff = auth('staff')->user();
            $order->updateStatus($request->status, $staff, $request->notes);

            // Update queue status
            $this->queueService->updateQueueStatus($order);

            // Send notifications
            $this->notificationService->orderStatusChanged($order);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->load(['timeline', 'queue'])
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }

    // Real-time tracking untuk customer
    public function track($orderNumber)
    {
        $order = Order::with([
            'items.menuItem',
            'items.stations.kitchenStation',
            'timeline',
            'queue',
            'table'
        ])
        ->where('order_number', $orderNumber)
        ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'progress' => $this->orderService->getOrderProgress($order),
                'estimated_completion' => $this->queueService->getEstimatedCompletionTime($order),
                'queue_position' => $this->queueService->getQueuePosition($order)
            ]
        ]);
    }

    // Kitchen display untuk staff
    public function kitchenDisplay(Request $request)
    {
        $restaurantId = auth('staff')->user()->restaurant_id;
        $stationId = $request->station_id;

        $query = OrderItem::with([
            'order.table',
            'menuItem',
            'variants',
            'stations' => function($q) use ($stationId) {
                if ($stationId) {
                    $q->where('kitchen_station_id', $stationId);
                }
            }
        ])
        ->whereHas('order', function($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId)
              ->whereIn('status', ['confirmed', 'preparing']);
        })
        ->whereIn('status', ['pending', 'preparing'])
        ->orderBy('created_at');

        if ($stationId) {
            $query->whereHas('stations', function($q) use ($stationId) {
                $q->where('kitchen_station_id', $stationId);
            });
        }

        $orderItems = $query->get();

        return response()->json([
            'success' => true,
            'data' => $orderItems
        ]);
    }

    // Update status item di kitchen
    public function updateItemStatus(Request $request, OrderItem $orderItem)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:preparing,ready,served',
            'station_id' => 'nullable|exists:kitchen_stations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            if ($request->station_id) {
                $station = $orderItem->stations()
                                   ->where('kitchen_station_id', $request->station_id)
                                   ->first();
                
                if ($station) {
                    if ($request->status === 'preparing') {
                        $station->startPreparation();
                    } elseif ($request->status === 'ready') {
                        $station->completePreparation();
                    }
                }
            } else {
                $orderItem->updateItemStatus($request->status);
            }

            // Check if all items in order are ready
            $this->orderService->checkOrderCompletion($orderItem->order);

            // Send real-time notifications
            $this->notificationService->itemStatusChanged($orderItem);

            return response()->json([
                'success' => true,
                'message' => 'Item status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item status: ' . $e->getMessage()], 500);
        }
    }

    // Get queue status
    public function queueStatus($restaurantId)
    {
        $currentQueue = $this->queueService->getCurrentQueue($restaurantId);
        $waitTime = $this->queueService->getEstimatedWaitTime($restaurantId);

        return response()->json([
            'success' => true,
            'data' => [
                'current_queue' => $currentQueue,
                'estimated_wait_time' => $waitTime,
                'orders_ahead' => $currentQueue->where('status', 'waiting')->count()
            ]
        ]);
    }
}