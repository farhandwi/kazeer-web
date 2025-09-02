<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderQueue;

class QueueService
{
    public function addToQueue(Order $order)
    {
        $queueNumber = OrderQueue::generateQueueNumber($order->restaurant_id);
        $estimatedWaitTime = $this->calculateEstimatedWaitTime($order->restaurant_id);

        $queue = OrderQueue::create([
            'restaurant_id' => $order->restaurant_id,
            'order_id' => $order->id,
            'queue_number' => $queueNumber,
            'estimated_wait_time' => $estimatedWaitTime,
        ]);

        return $queueNumber;
    }

    public function updateQueueStatus(Order $order)
    {
        $queue = $order->queue()->first();
        
        if (!$queue) return;

        switch ($order->status) {
            case 'confirmed':
            case 'preparing':
                $queue->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
                break;
                
            case 'ready':
            case 'served':
            case 'completed':
                $queue->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                break;
                
            case 'cancelled':
                $queue->update(['status' => 'cancelled']);
                break;
        }
    }

    public function getCurrentQueue($restaurantId)
    {
        return OrderQueue::with(['order.table', 'order.items.menuItem'])
                         ->where('restaurant_id', $restaurantId)
                         ->whereIn('status', ['waiting', 'in_progress'])
                         ->orderBy('queue_number')
                         ->get();
    }

    public function getQueuePosition(Order $order)
    {
        $queue = $order->queue()->first();
        
        if (!$queue || $queue->status !== 'waiting') {
            return 0;
        }

        return OrderQueue::where('restaurant_id', $order->restaurant_id)
                        ->where('status', 'waiting')
                        ->where('queue_number', '<', $queue->queue_number)
                        ->count() + 1;
    }

    public function getEstimatedWaitTime($restaurantId)
    {
        $avgPrepTime = Order::where('restaurant_id', $restaurantId)
                           ->where('created_at', '>=', now()->subDays(7))
                           ->whereNotNull('ready_at')
                           ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, ready_at)) as avg_time')
                           ->value('avg_time');

        return $avgPrepTime ?? 20; // Default 20 minutes
    }

    public function getEstimatedCompletionTime(Order $order)
    {
        $queuePosition = $this->getQueuePosition($order);
        $avgWaitTime = $this->getEstimatedWaitTime($order->restaurant_id);
        
        $estimatedMinutes = ($queuePosition - 1) * $avgWaitTime + $order->estimated_prep_time;
        
        return now()->addMinutes($estimatedMinutes);
    }

    private function calculateEstimatedWaitTime($restaurantId)
    {
        $waitingOrders = OrderQueue::where('restaurant_id', $restaurantId)
                                  ->where('status', 'waiting')
                                  ->count();

        $baseTime = $this->getEstimatedWaitTime($restaurantId);
        
        return $baseTime + ($waitingOrders * 5); // Add 5 minutes per waiting order
    }
}