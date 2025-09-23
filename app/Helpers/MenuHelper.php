<?php

namespace App\Helpers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\MenuOptionCategory;

class MenuHelper
{
    /**
     * Calculate total price for menu item with selected options
     */
    public static function calculateItemPrice(MenuItem $menuItem, array $selectedOptions = []): float
    {
        $basePrice = $menuItem->price;
        $optionsPrice = 0;
        
        foreach ($selectedOptions as $categoryId => $optionIds) {
            if (!is_array($optionIds)) {
                $optionIds = [$optionIds];
            }
            
            foreach ($optionIds as $optionId) {
                $option = \App\Models\MenuOption::find($optionId);
                if ($option) {
                    $optionsPrice += $option->additional_price;
                }
            }
        }
        
        return $basePrice + $optionsPrice;
    }
    
    /**
     * Validate selected options against menu item requirements
     */
    public static function validateOptions(MenuItem $menuItem, array $selectedOptions = []): array
    {
        $errors = [];
        $requiredCategories = $menuItem->menuOptionCategories()
            ->wherePivot('is_required', true)
            ->get();
            
        foreach ($requiredCategories as $category) {
            if (!isset($selectedOptions[$category->id]) || empty($selectedOptions[$category->id])) {
                $errors[] = "Please select an option for {$category->name}";
            }
        }
        
        return $errors;
    }
    
    /**
     * Format price to Indonesian Rupiah
     */
    public static function formatPrice(float $price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }
    
    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }
    
    /**
     * Calculate order totals
     */
    public static function calculateOrderTotals(array $items, float $taxRate = 0.11, float $serviceCharge = 0): array
    {
        $subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });
        
        $taxAmount = $subtotal * $taxRate;
        $totalBeforeDiscount = $subtotal + $taxAmount + $serviceCharge;
        
        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge' => $serviceCharge,
            'total_before_discount' => $totalBeforeDiscount,
        ];
    }
    
    /**
     * Get menu item availability status
     */
    public static function isMenuItemAvailable(MenuItem $menuItem): bool
    {
        return $menuItem->is_available 
            && $menuItem->restaurant->is_active 
            && $menuItem->category->is_active;
    }
    
    /**
     * Get estimated preparation time for order
     */
    public static function calculateOrderPrepTime(array $menuItems): int
    {
        // Get max prep time from all items (assuming parallel cooking)
        $maxPrepTime = collect($menuItems)->max(function ($item) {
            return $item['menu_item']->preparation_time ?? 15;
        });
        
        // Add buffer time based on number of items
        $itemCount = collect($menuItems)->sum('quantity');
        $bufferTime = min(15, ceil($itemCount / 3) * 2);
        
        return $maxPrepTime + $bufferTime;
    }
}