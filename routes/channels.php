<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\TableSession;
use App\Models\Staff;

// Channel untuk customer di meja tertentu
Broadcast::channel('table.{sessionToken}', function ($user, $sessionToken) {
    // Allow access jika session valid
    $session = TableSession::where('session_token', $sessionToken)
                          ->where('status', 'active')
                          ->first();
    return $session ? ['session' => $session->session_token] : false;
});

// Channel untuk kitchen staff
Broadcast::channel('kitchen.{restaurantId}', function ($user, $restaurantId) {
    // Verify staff access to restaurant
    if ($user instanceof Staff) {
        return $user->restaurant_id == $restaurantId && $user->is_active;
    }
    return false;
});

// Channel untuk station tertentu
Broadcast::channel('kitchen.{restaurantId}.station.{stationId}', function ($user, $restaurantId, $stationId) {
    if ($user instanceof Staff) {
        return $user->restaurant_id == $restaurantId && $user->is_active;
    }
    return false;
});

// Channel untuk restaurant management
Broadcast::channel('restaurant.{restaurantId}', function ($user, $restaurantId) {
    if ($user instanceof Staff) {
        return $user->restaurant_id == $restaurantId && 
               in_array($user->role, ['admin', 'manager']) && 
               $user->is_active;
    }
    return false;
});