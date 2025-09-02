<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use Illuminate\Support\Facades\Broadcast;

class NotificationService
{
    public function orderReceived(Order $order)
    {
        // Notification untuk kitchen staff
        $notification = Notification::create([
            'restaurant_id' => $order->restaurant_id,
            'order_id' => $order->id,
            'type' => 'order_received',
            'title' => 'New Order Received',
            'message' => "New order #{$order->order_number} from table {$order->table->table_number}",
            'target_type' => 'staff',
            'data' => [
                'order_id' => $order->id,
                'table_number' => $order->table->table_number,
                'total_amount' => $order->total_amount,
                'item_count' => $order->items->count()
            ]
        ]);

        // Broadcast ke kitchen staff
        $this->broadcastToKitchen($order->restaurant_id, 'order.received', [
            'order' => $order->load(['items.menuItem', 'table']),
            'notification' => $notification
        ]);

        // Notification untuk customer
        $this->notifyCustomer($order, 'order_placed', 'Order Placed Successfully', 
            "Your order #{$order->order_number} has been placed and will be prepared shortly.");
    }

    public function orderStatusChanged(Order $order)
    {
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared',
            'preparing' => 'Your order is now being prepared in the kitchen',
            'ready' => 'Your order is ready! Please wait for service',
            'served' => 'Your order has been served. Enjoy your meal!',
            'completed' => 'Thank you for dining with us!',
        ];

        $message = $statusMessages[$order->status] ?? "Order status updated to {$order->status}";

        // Notify customer
        $this->notifyCustomer($order, 'order_status_changed', 
            ucfirst($order->status), $message);

        // Broadcast real-time update
        $this->broadcastToCustomer($order, 'order.status.updated', [
            'order' => $order->load(['items.menuItem', 'timeline', 'queue']),
            'progress' => app(OrderService::class)->getOrderProgress($order)
        ]);

        // Notify kitchen if status affects kitchen workflow
        if (in_array($order->status, ['confirmed', 'ready'])) {
            $this->broadcastToKitchen($order->restaurant_id, 'order.status.updated', [
                'order' => $order->load(['items.menuItem', 'table'])
            ]);
        }
    }

    public function itemStatusChanged(OrderItem $orderItem)
    {
        $order = $orderItem->order;

        // Broadcast to customer with real-time progress
        $this->broadcastToCustomer($order, 'order.item.updated', [
            'order_item' => $orderItem->load('menuItem'),
            'progress' => app(OrderService::class)->getOrderProgress($order)
        ]);

        // Broadcast to kitchen
        $this->broadcastToKitchen($order->restaurant_id, 'kitchen.item.updated', [
            'order_item' => $orderItem->load(['menuItem', 'order.table'])
        ]);
    }

    protected function notifyCustomer(Order $order, $type, $title, $message)
    {
        Notification::create([
            'restaurant_id' => $order->restaurant_id,
            'order_id' => $order->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'target_type' => 'customer',
            'target_id' => $order->customer_id,
            'data' => [
                'order_number' => $order->order_number,
                'table_number' => $order->table->table_number
            ]
        ]);
    }

    protected function broadcastToCustomer(Order $order, $event, $data)
    {
        $session = $order->table->currentSession();
        if ($session) {
            Broadcast::channel("table.{$session->session_token}")
                     ->event($event)
                     ->with($data);
        }
    }

    protected function broadcastToKitchen($restaurantId, $event, $data)
    {
        Broadcast::channel("kitchen.{$restaurantId}")
                 ->event($event)
                 ->with($data);
    }
}