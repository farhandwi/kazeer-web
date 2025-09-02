<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function broadcastOn()
    {
        $session = $this->order->table->currentSession();
        
        return [
            new Channel("table.{$session->session_token}"),
            new Channel("kitchen.{$this->order->restaurant_id}"),
            new Channel("restaurant.{$this->order->restaurant_id}")
        ];
    }

    public function broadcastWith()
    {
        return [
            'order' => $this->order->load(['items.menuItem', 'timeline', 'queue']),
            'progress' => app(\App\Services\OrderService::class)->getOrderProgress($this->order),
            'timestamp' => now()
        ];
    }
}

class ItemStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderItem;

    public function __construct(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    public function broadcastOn()
    {
        $session = $this->orderItem->order->table->currentSession();
        
        return [
            new Channel("table.{$session->session_token}"),
            new Channel("kitchen.{$this->orderItem->order->restaurant_id}")
        ];
    }

    public function broadcastWith()
    {
        return [
            'order_item' => $this->orderItem->load(['menuItem', 'stations.kitchenStation']),
            'order_progress' => app(\App\Services\OrderService::class)->getOrderProgress($this->orderItem->order),
            'timestamp' => now()
        ];
    }
}