<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTimeline;
use Pusher\Pusher;

class RealTimeService
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );
    }

    public function trackOrderProgress(Order $order)
    {
        $progress = app(OrderService::class)->getOrderProgress($order);
        $queuePosition = app(QueueService::class)->getQueuePosition($order);
        $estimatedCompletion = app(QueueService::class)->getEstimatedCompletionTime($order);

        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'progress' => $progress,
            'queue_position' => $queuePosition,
            'estimated_completion' => $estimatedCompletion,
            'timeline' => $order->timeline()->latest()->limit(5)->get(),
            'items_status' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->menuItem->name,
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'stations' => $item->stations->map(function($station) {
                        return [
                            'station_name' => $station->kitchenStation->name,
                            'status' => $station->status,
                            'started_at' => $station->started_at,
                            'completed_at' => $station->completed_at,
                        ];
                    })
                ];
            })
        ];

        // Broadcast to customer
        $session = $order->table->currentSession();
        if ($session) {
            $this->pusher->trigger("table.{$session->session_token}", 'order.progress', $data);
        }

        // Broadcast to kitchen dashboard
        $this->pusher->trigger("kitchen.{$order->restaurant_id}", 'order.progress', $data);

        return $data;
    }

    public function broadcastKitchenUpdate($restaurantId, $stationId = null)
    {
        $channel = $stationId ? "kitchen.{$restaurantId}.station.{$stationId}" : "kitchen.{$restaurantId}";
        
        // Get current kitchen queue
        $kitchenQueue = OrderItem::with([
            'order.table', 
            'menuItem', 
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
        ->orderBy('created_at')
        ->get();

        $this->pusher->trigger($channel, 'kitchen.queue.updated', [
            'queue' => $kitchenQueue,
            'station_id' => $stationId,
            'updated_at' => now()
        ]);
    }
}