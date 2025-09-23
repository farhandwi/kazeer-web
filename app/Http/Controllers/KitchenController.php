<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenStation;
use App\Services\RealTimeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class KitchenController extends Controller
{
    protected $realTimeService;

    public function __construct(RealTimeService $realTimeService)
    {
        $this->middleware('auth:staff');
        $this->realTimeService = $realTimeService;
    }

    // Dashboard dapur utama
    public function dashboard()
    {
        $restaurantId = auth('staff')->user()->restaurant_id;
        
        $stations = KitchenStation::where('restaurant_id', $restaurantId)
                                 ->where('is_active', true)
                                 ->with(['orderItemStations' => function($q) {
                                     $q->whereIn('status', ['pending', 'in_progress'])
                                       ->with(['orderItem.order.table', 'orderItem.menuItem']);
                                 }])
                                 ->get();

        $pendingOrders = Order::where('restaurant_id', $restaurantId)
                             ->whereIn('status', ['pending', 'confirmed'])
                             ->with(['items.menuItem', 'table', 'queue'])
                             ->orderBy('created_at')
                             ->get();

        return view('kitchen.dashboard', compact('stations', 'pendingOrders'));
    }

    // Display untuk station tertentu
    public function stationDisplay($stationId)
    {
        $station = KitchenStation::with(['orderItemStations' => function($q) {
            $q->whereIn('status', ['pending', 'in_progress'])
              ->with(['orderItem.order.table', 'orderItem.menuItem', 'orderItem.variants'])
              ->orderBy('preparation_order')
              ->orderBy('created_at');
        }])
        ->findOrFail($stationId);

        return view('kitchen.station', compact('station'));
    }

    // Start preparation item di station
    public function startItem(Request $request, OrderItem $orderItem)
    {
        $stationId = $request->station_id;
        
        $station = $orderItem->stations()
                           ->where('kitchen_station_id', $stationId)
                           ->where('status', 'pending')
                           ->first();

        if (!$station) {
            return response()->json(['error' => 'Item not found in station queue'], 404);
        }

        $station->startPreparation();
        
        // Broadcast update
        $this->realTimeService->broadcastKitchenUpdate($orderItem->order->restaurant_id, $stationId);
        $this->realTimeService->trackOrderProgress($orderItem->order);

        return response()->json([
            'success' => true,
            'message' => 'Item preparation started'
        ]);
    }

    // Complete item di station
    public function completeItem(Request $request, OrderItem $orderItem)
    {
        $stationId = $request->station_id;
        
        $station = $orderItem->stations()
                           ->where('kitchen_station_id', $stationId)
                           ->where('status', 'in_progress')
                           ->first();

        if (!$station) {
            return response()->json(['error' => 'Item not in progress at this station'], 404);
        }

        $station->completePreparation();
        
        // Broadcast update
        $this->realTimeService->broadcastKitchenUpdate($orderItem->order->restaurant_id, $stationId);
        $this->realTimeService->trackOrderProgress($orderItem->order);

        return response()->json([
            'success' => true,
            'message' => 'Item completed at station'
        ]);
    }

    // Get real-time queue untuk API
    public function getQueue(Request $request)
    {
        $restaurantId = auth('staff')->user()->restaurant_id;
        $stationId = $request->station_id;

        if ($stationId) {
            $station = KitchenStation::findOrFail($stationId);
            $items = $station->getPendingItems();
        } else {
            $items = OrderItem::with(['order.table', 'menuItem', 'stations.kitchenStation'])
                             ->whereHas('order', function($q) use ($restaurantId) {
                                 $q->where('restaurant_id', $restaurantId)
                                   ->whereIn('status', ['confirmed', 'preparing']);
                             })
                             ->whereIn('status', ['pending', 'preparing'])
                             ->orderBy('created_at')
                             ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'updated_at' => now()
        ]);
    }
}