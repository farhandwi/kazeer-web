<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Models\Customer;
use App\Models\Coupon;

class OrderService
{
    public function createOrder(array $data)
    {
        // Validasi session
        $session = TableSession::where('session_token', $data['session_token'])
                              ->where('status', 'active')
                              ->first();

        if (!$session) {
            throw new \Exception('Invalid session');
        }

        // Create atau ambil customer
        $customer = null;
        if (!empty($data['customer_phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $data['customer_phone']],
                ['name' => $data['customer_name']]
            );
        }

        // Calculate totals
        $subtotal = 0;
        $estimatedPrepTime = 0;

        foreach ($data['items'] as $itemData) {
            $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
            $itemTotal = $menuItem->price * $itemData['quantity'];
            
            // Add variant costs
            if (!empty($itemData['variants'])) {
                foreach ($itemData['variants'] as $variantId) {
                    $variant = $menuItem->variants()->find($variantId);
                    if ($variant) {
                        $itemTotal += $variant->price_modifier * $itemData['quantity'];
                    }
                }
            }
            
            $subtotal += $itemTotal;
            $estimatedPrepTime = max($estimatedPrepTime, $menuItem->preparation_time);
        }

        // Apply coupon if provided
        $discountAmount = 0;
        $coupon = null;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])
                           ->where('restaurant_id', $session->restaurant_id)
                           ->first();
            
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
            }
        }

        // Calculate tax and service charge
        $taxRate = $session->restaurant->getSetting('tax_rate', 10) / 100;
        $serviceChargeRate = $session->restaurant->getSetting('service_charge_rate', 5) / 100;
        
        $taxAmount = $subtotal * $taxRate;
        $serviceCharge = $subtotal * $serviceChargeRate;
        $totalAmount = $subtotal + $taxAmount + $serviceCharge - $discountAmount;

        // Create order
        $order = Order::create([
            'order_number' => Order::generateOrderNumber($session->restaurant_id),
            'restaurant_id' => $session->restaurant_id,
            'table_id' => $session->table_id,
            'customer_id' => $customer?->id,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge' => $serviceCharge,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'special_instructions' => $data['special_instructions'] ?? null,
            'estimated_prep_time' => $estimatedPrepTime,
        ]);

        // Create order items
        foreach ($data['items'] as $itemData) {
            $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
            $unitPrice = $menuItem->price;
            
            // Calculate variant costs
            $variantCost = 0;
            if (!empty($itemData['variants'])) {
                foreach ($itemData['variants'] as $variantId) {
                    $variant = $menuItem->variants()->find($variantId);
                    if ($variant) {
                        $variantCost += $variant->price_modifier;
                    }
                }
            }
            
            $finalUnitPrice = $unitPrice + $variantCost;
            $totalPrice = $finalUnitPrice * $itemData['quantity'];

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $itemData['quantity'],
                'unit_price' => $finalUnitPrice,
                'total_price' => $totalPrice,
                'special_instructions' => $itemData['special_instructions'] ?? null,
            ]);

            // Attach variants
            if (!empty($itemData['variants'])) {
                foreach ($itemData['variants'] as $variantId) {
                    $variant = $menuItem->variants()->find($variantId);
                    if ($variant) {
                        $orderItem->variants()->attach($variantId, [
                            'price_modifier' => $variant->price_modifier
                        ]);
                    }
                }
            }

            // Create kitchen station assignments
            $this->createKitchenStationAssignments($orderItem);
        }

        // Apply coupon
        if ($coupon && $discountAmount > 0) {
            $order->coupons()->attach($coupon->id, [
                'discount_amount' => $discountAmount
            ]);
            $coupon->increment('used_count');
        }

        // Add to timeline
        $order->timeline()->create([
            'event_type' => 'order_placed',
            'title' => 'Order Placed',
            'description' => "Order #{$order->order_number} has been placed",
            'metadata' => [
                'total_amount' => $totalAmount,
                'item_count' => count($data['items'])
            ],
            'created_at' => now(),
        ]);

        return $order;
    }

    protected function createKitchenStationAssignments(OrderItem $orderItem)
    {
        $stations = $orderItem->menuItem->kitchenStations;
        
        foreach ($stations as $station) {
            $orderItem->stations()->create([
                'kitchen_station_id' => $station->id,
                'preparation_order' => $station->pivot->preparation_order,
                'status' => 'pending',
            ]);
        }
    }

    public function getOrderProgress(Order $order)
    {
        $totalItems = $order->items->count();
        $completedItems = $order->items->where('status', 'ready')->count();
        $preparingItems = $order->items->where('status', 'preparing')->count();
        
        $progress = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
        
        return [
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'preparing_items' => $preparingItems,
            'pending_items' => $totalItems - $completedItems - $preparingItems,
            'progress_percentage' => round($progress, 2),
            'status' => $order->status,
        ];
    }

    public function checkOrderCompletion(Order $order)
    {
        $allItemsReady = $order->items()->where('status', '!=', 'ready')->count() === 0;
        
        if ($allItemsReady && $order->status === 'preparing') {
            $order->updateStatus('ready');
            $order->update(['ready_at' => now()]);
        }
        
        return $allItemsReady;
    }
}