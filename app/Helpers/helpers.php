<?php

// File: app/Helpers/helpers.php - Create this file

if (!function_exists('formatCurrency')) {
    /**
     * Format currency to Indonesian Rupiah format
     * 
     * @param float|int $amount
     * @return string
     */
    function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('parsePrice')) {
    /**
     * Parse price from string format back to number
     * 
     * @param string $price
     * @return float
     */
    function parsePrice($price)
    {
        // Remove 'Rp ' and dots, replace commas with dots
        $price = str_replace(['Rp ', '.'], '', $price);
        $price = str_replace(',', '.', $price);
        return (float) $price;
    }
}

if (!function_exists('getOrderStatusLabel')) {
    /**
     * Get human readable order status label
     * 
     * @param string $status
     * @return string
     */
    function getOrderStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'served' => 'Served',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        
        return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
    }
}

if (!function_exists('getOrderStatusColor')) {
    /**
     * Get status color for UI
     * 
     * @param string $status
     * @return string
     */
    function getOrderStatusColor($status)
    {
        $colors = [
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'preparing' => 'orange',
            'ready' => 'green',
            'served' => 'purple',
            'completed' => 'gray',
            'cancelled' => 'red'
        ];
        
        return $colors[$status] ?? 'gray';
    }
}

if (!function_exists('getPaymentStatusLabel')) {
    /**
     * Get human readable payment status label
     * 
     * @param string $status
     * @return string
     */
    function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded'
        ];
        
        return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
    }
}

if (!function_exists('generateOrderNumber')) {
    /**
     * Generate order number with date and sequence
     * 
     * @param int $restaurantId
     * @return string
     */
    function generateOrderNumber($restaurantId = 1)
    {
        $date = now()->format('Ymd');
        $lastOrder = \App\Models\Order::where('restaurant_id', $restaurantId)
                                    ->whereDate('created_at', now()->toDateString())
                                    ->latest('id')
                                    ->first();
        
        $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -3)) + 1 : 1;
        
        return $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('calculateOrderTotals')) {
    /**
     * Calculate order totals with tax and service charge
     * 
     * @param float $subtotal
     * @param float $taxRate
     * @param float $serviceRate
     * @param float $discountAmount
     * @return array
     */
    function calculateOrderTotals($subtotal, $taxRate = 0.11, $serviceRate = 0.05, $discountAmount = 0)
    {
        $taxAmount = $subtotal * $taxRate;
        $serviceCharge = $subtotal * $serviceRate;
        $totalBeforeDiscount = $subtotal + $taxAmount + $serviceCharge;
        $totalAmount = $totalBeforeDiscount - $discountAmount;
        
        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge' => $serviceCharge,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount
        ];
    }
}

if (!function_exists('getTimeAgo')) {
    /**
     * Get time ago in Indonesian format
     * 
     * @param \Carbon\Carbon $date
     * @return string
     */
    function getTimeAgo($date)
    {
        $diff = now()->diffInMinutes($date);
        
        if ($diff < 1) {
            return 'Baru saja';
        } elseif ($diff < 60) {
            return $diff . ' menit yang lalu';
        } elseif ($diff < 1440) {
            $hours = floor($diff / 60);
            return $hours . ' jam yang lalu';
        } else {
            $days = floor($diff / 1440);
            return $days . ' hari yang lalu';
        }
    }
}