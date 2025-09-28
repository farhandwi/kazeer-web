<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckCashierAccess;
use App\Models\Discount;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CashierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckCashierAccess::class);
    }
    
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;
            
            if (!$restaurantId) {
                Log::error('CashierController@index: User has no restaurant_id', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            Log::info('CashierController@index: Loading cashier page', [
                'user_id' => $user->id,
                'restaurant_id' => $restaurantId
            ]);
            
            $categories = Category::where('restaurant_id', $restaurantId)
                ->where('is_active', true)
                ->with(['menuItems' => function($query) {
                    $query->where('is_available', true)
                          ->with(['optionCategories.options']);
                }])
                ->orderBy('sort_order')
                ->get();

            Log::info('CashierController@index: Categories loaded', [
                'categories_count' => $categories->count(),
                'restaurant_id' => $restaurantId
            ]);

            $tables = Table::where('restaurant_id', $restaurantId)
                ->orderBy('table_number')
                ->get();

            Log::info('CashierController@index: Tables loaded', [
                'tables_count' => $tables->count(),
                'restaurant_id' => $restaurantId
            ]);

            return view('cashier.index', compact('categories', 'tables'));

        } catch (\Exception $e) {
            Log::error('CashierController@index: Error loading cashier page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'Failed to load cashier page: ' . $e->getMessage()]);
        }
    }

    public function getMenuItems(Request $request)
    {
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;
            $categoryId = $request->get('category_id');
            
            if (!$restaurantId) {
                Log::error('CashierController@getMenuItems: User has no restaurant_id', [
                    'user_id' => $user->id
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            Log::info('CashierController@getMenuItems: Loading menu items', [
                'restaurant_id' => $restaurantId,
                'category_id' => $categoryId
            ]);
            
            $query = MenuItem::where('restaurant_id', $restaurantId)
                ->where('is_available', true)
                ->with(['optionCategories' => function($query) {
                    $query->with(['options' => function($q) {
                        $q->where('is_available', true)->orderBy('sort_order');
                    }])->orderBy('menu_item_option_categories.sort_order');
                }]);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $menuItems = $query->orderBy('sort_order')->get();

            Log::info('CashierController@getMenuItems: Menu items loaded successfully', [
                'items_count' => $menuItems->count(),
                'restaurant_id' => $restaurantId,
                'category_id' => $categoryId
            ]);

            return response()->json($menuItems);

        } catch (\Exception $e) {
            Log::error('CashierController@getMenuItems: Error loading menu items', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'category_id' => $request->get('category_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load menu items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMenuItem($id)
    {
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;

            if (!$restaurantId) {
                Log::error('CashierController@getMenuItem: User has no restaurant_id', [
                    'user_id' => $user->id,
                    'menu_item_id' => $id
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            Log::info('CashierController@getMenuItem: Loading menu item', [
                'menu_item_id' => $id,
                'restaurant_id' => $restaurantId
            ]);

            $menuItem = MenuItem::with(['optionCategories' => function($query) {
                $query->with(['options' => function($q) {
                    $q->where('is_available', true)->orderBy('sort_order');
                }])->orderBy('menu_item_option_categories.sort_order');
            }])
            ->where('restaurant_id', $restaurantId)
            ->findOrFail($id);

            Log::info('CashierController@getMenuItem: Menu item loaded successfully', [
                'menu_item_id' => $id,
                'menu_item_name' => $menuItem->name,
                'options_categories_count' => $menuItem->optionCategories->count()
            ]);

            return response()->json($menuItem);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('CashierController@getMenuItem: Menu item not found', [
                'menu_item_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('CashierController@getMenuItem: Error loading menu item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'menu_item_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createOrder(Request $request)
    {
        try {
            Log::info('CashierController@createOrder: Starting order creation', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            // Enhanced validation with payment_method
            $request->validate([
                'table_id' => 'required|exists:tables,id',
                'customer_name' => 'required|string|max:255|min:2',
                'customer_phone' => 'nullable|string|max:20',
                'payment_method' => 'required|in:cash,card,digital_wallet,transfer',
                'items' => 'required|array|min:1',
                'items.*.menu_item_id' => 'required|exists:menu_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.options' => 'nullable|array',
                'items.*.options.*' => 'exists:menu_options,id',
                'items.*.special_instructions' => 'nullable|string|max:500',
                'coupon_id' => 'nullable|exists:coupons,id', // Tambahkan ini
                'discount_id' => 'nullable|exists:discounts,id', // Tambahkan ini
            ]);

            Log::info('CashierController@createOrder: Validation passed');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('CashierController@createOrder: Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;

            if (!$restaurantId) {
                Log::error('CashierController@createOrder: User has no restaurant_id', [
                    'user_id' => $user->id
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            // Verify table belongs to restaurant
            $table = Table::where('id', $request->table_id)
                ->where('restaurant_id', $restaurantId)
                ->first();

            if (!$table) {
                Log::error('CashierController@createOrder: Table not found or not belongs to restaurant', [
                    'table_id' => $request->table_id,
                    'restaurant_id' => $restaurantId,
                    'user_id' => Auth::id()
                ]);
                throw new \Exception('Table not found or does not belong to your restaurant');
            }

            Log::info('CashierController@createOrder: Creating order', [
                'restaurant_id' => $restaurantId,
                'table_id' => $request->table_id,
                'items_count' => count($request->items),
                'customer_name' => $request->customer_name,
                'payment_method' => $request->payment_method
            ]);
            
            // Create order with enhanced fields
            $order = Order::create([
                'order_number' => Order::generateOrderNumber($restaurantId),
                'restaurant_id' => $restaurantId,
                'table_id' => $request->table_id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'subtotal' => 0,
                'tax_amount' => 0,
                'service_charge' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'payment_status' => 'pending',
                'estimated_prep_time' => 0 
            ]);

            Log::info('CashierController@createOrder: Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            $subtotal = 0;
            $totalPrepTime = 0;

            // Add items to order
            foreach ($request->items as $index => $itemData) {
                try {
                    Log::info('CashierController@createOrder: Processing order item', [
                        'item_index' => $index,
                        'menu_item_id' => $itemData['menu_item_id'],
                        'quantity' => $itemData['quantity']
                    ]);

                    $menuItem = MenuItem::where('id', $itemData['menu_item_id'])
                        ->where('restaurant_id', $restaurantId)
                        ->where('is_available', true)
                        ->first();

                    if (!$menuItem) {
                        Log::error('CashierController@createOrder: Menu item not found or not available', [
                            'menu_item_id' => $itemData['menu_item_id'],
                            'restaurant_id' => $restaurantId,
                            'item_index' => $index
                        ]);
                        throw new \Exception("Menu item with ID {$itemData['menu_item_id']} not found or not available");
                    }

                    $quantity = $itemData['quantity'];
                    $unitPrice = $menuItem->getCurrentPrice();
                    $itemPrepTime = $menuItem->preparation_time ?? 0;

                    // Create order item
                    $orderItem = $order->orderItems()->create([
                        'menu_item_id' => $menuItem->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * $quantity,
                        'special_instructions' => $itemData['special_instructions'] ?? null,
                        'status' => 'pending'
                    ]);

                    $totalPrepTime = max($totalPrepTime, $itemPrepTime);

                    Log::info('CashierController@createOrder: Order item created', [
                        'order_item_id' => $orderItem->id,
                        'menu_item_name' => $menuItem->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice
                    ]);

                    $itemTotal = $unitPrice * $quantity;
                    $optionsTotal = 0;

                    // Add selected options
                    if (!empty($itemData['options'])) {
                        Log::info('CashierController@createOrder: Processing options', [
                            'options_count' => count($itemData['options']),
                            'options' => $itemData['options']
                        ]);

                        foreach ($itemData['options'] as $optionIndex => $optionId) {
                            try {
                                $option = \App\Models\MenuOption::where('id', $optionId)
                                    ->where('is_available', true)
                                    ->first();
                                
                                if (!$option) {
                                    Log::error('CashierController@createOrder: Menu option not found', [
                                        'option_id' => $optionId,
                                        'option_index' => $optionIndex
                                    ]);
                                    throw new \Exception("Menu option with ID {$optionId} not found or not available");
                                }

                                OrderItemOption::create([
                                    'order_item_id' => $orderItem->id,
                                    'menu_option_id' => $option->id,
                                    'option_price' => $option->additional_price
                                ]);

                                $optionsTotal += $option->additional_price * $quantity;

                                Log::info('CashierController@createOrder: Option added', [
                                    'option_id' => $option->id,
                                    'option_name' => $option->name,
                                    'additional_price' => $option->additional_price
                                ]);

                            } catch (\Exception $e) {
                                Log::error('CashierController@createOrder: Error processing option', [
                                    'error' => $e->getMessage(),
                                    'option_id' => $optionId,
                                    'option_index' => $optionIndex
                                ]);
                                throw $e;
                            }
                        }
                    }

                    // Update order item total with options
                    $finalItemTotal = $itemTotal + $optionsTotal;
                    $orderItem->update(['total_price' => $finalItemTotal]);
                    $subtotal += $finalItemTotal;

                    Log::info('CashierController@createOrder: Item processed successfully', [
                        'order_item_id' => $orderItem->id,
                        'item_total' => $itemTotal,
                        'options_total' => $optionsTotal,
                        'final_item_total' => $finalItemTotal
                    ]);

                } catch (\Exception $e) {
                    Log::error('CashierController@createOrder: Error processing order item', [
                        'error' => $e->getMessage(),
                        'item_index' => $index,
                        'item_data' => $itemData
                    ]);
                    throw $e;
                }
            }

            // Calculate totals
            $taxRate = 0.11;
            $serviceChargeRate = 0.05;
            $discountAmount = 0;
            $discountId = null;
            $discountCode = null;
            $discountDetails = null;
            if ($request->discount_id) {
                $discount = Discount::find($request->discount_id);
                $discountId = $request->discount_id;
                $discountCode = $discount->code;
                $discountDetails = json_encode([
                    'name' => $discount->name,
                    'type' => $discount->type,
                    'value' => $discount->value,
                    'applied_amount' => $discountAmount
                ]);
            }
            $couponDiscountAmount = 0;
            $totalDiscountAmount = 0;

            // Apply coupon if provided
            if ($request->coupon_id) {
                try {
                    $coupon = \App\Models\Coupon::where('id', $request->coupon_id)
                        ->where('restaurant_id', $restaurantId)
                        ->where('is_active', true)
                        ->first();

                    if ($coupon && $coupon->isValid($subtotal)) {
                        $couponDiscountAmount = $coupon->calculateDiscount($subtotal);
                        
                        // Attach coupon to order
                        $order->coupons()->attach($coupon->id, [
                            'discount_amount' => $couponDiscountAmount
                        ]);
                        
                        // Update coupon usage count
                        $coupon->increment('used_count');
                        
                        Log::info('Coupon applied to order', [
                            'order_id' => $order->id,
                            'coupon_code' => $coupon->code,
                            'discount_amount' => $couponDiscountAmount
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to apply coupon', [
                        'error' => $e->getMessage(),
                        'coupon_id' => $request->coupon_id,
                        'order_id' => $order->id
                    ]);
                }
            }

            // Apply discount if provided
            if ($request->discount_id) {
                try {
                    $discount = \App\Models\Discount::where('id', $request->discount_id)
                        ->where('is_active', true)
                        ->first();

                    if ($discount && $discount->isAvailable() && $discount->isApplicableToRestaurant($restaurantId)) {
                        // Calculate discount from subtotal after coupon applied
                        $subtotalAfterCoupon = $subtotal - $couponDiscountAmount;
                        
                        if (!$discount->minimum_order || $subtotalAfterCoupon >= $discount->minimum_order) {
                            $discountAmount = $discount->calculateDiscount($subtotalAfterCoupon);
                            
                            // Record discount usage
                            \App\Models\DiscountUsage::create([
                                'discount_id' => $discount->id,
                                'order_id' => $order->id,
                                'customer_id' => null,
                                'discount_amount' => $discountAmount
                            ]);
                            
                            // Update discount used count
                            $discount->increment('used_count');
                            
                            Log::info('Discount applied to order', [
                                'order_id' => $order->id,
                                'discount_code' => $discount->code,
                                'discount_amount' => $discountAmount
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to apply discount', [
                        'error' => $e->getMessage(),
                        'discount_id' => $request->discount_id,
                        'order_id' => $order->id
                    ]);
                }
            }

            $totalDiscountAmount = $couponDiscountAmount + $discountAmount;
            
            $taxAmount = $subtotal * $taxRate;
            $serviceCharge = $subtotal * $serviceChargeRate;
            $totalAmount = $subtotal + $taxAmount + $serviceCharge - $discountAmount;

            Log::info('CashierController@createOrder: Calculating totals', [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'total_amount' => $totalAmount
            ]);

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'discount_amount' => $totalDiscountAmount,
                'total_amount' => $totalAmount,
                'estimated_prep_time' => $totalPrepTime,
                'discount_id' => $discountId,
                'discount_code' => $discountCode,
                'discount_details' => $discountDetails
            ]);

            // FIXED: Add to timeline with explicit timestamps
            try {
                $order->timeline()->create([
                    'event_type'   => 'order_created',
                    'title'        => 'Order Created',
                    'description'  => 'New order created by cashier',
                    'metadata'     => json_encode([
                        'created_by'     => $user->name,
                        'items_count'    => count($request->items),
                        'payment_method' => $request->payment_method
                    ]),
                    'created_at'   => now(),
                ]);

                Log::info('CashierController@createOrder: Timeline entry created successfully');

                if ($request->coupon_id) {
                    $coupon = \App\Models\Coupon::find($request->coupon_id);
                    if ($coupon && $coupon->isValid($subtotal)) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                        
                        // Attach coupon to order
                        $order->coupons()->attach($coupon->id, [
                            'discount_amount' => $discountAmount
                        ]);
                        
                        // Update coupon usage count
                        $coupon->increment('used_count');
                        
                        // Recalculate total with discount
                        $totalAmount = $subtotal + $taxAmount + $serviceCharge - $discountAmount;
                        
                        Log::info('Coupon applied to order', [
                            'order_id' => $order->id,
                            'coupon_code' => $coupon->code,
                            'discount_amount' => $discountAmount
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('CashierController@createOrder: Failed to create timeline entry', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
                // Continue processing even if timeline fails
            }

            

            DB::commit();

            Log::info('CashierController@createOrder: Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount,
                'items_count' => count($request->items),
                'customer_name' => $request->customer_name,
                'payment_method' => $request->payment_method
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order->load(['orderItems.menuItem', 'orderItems.orderItemOptions.menuOption'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('CashierController@createOrder: Failed to create order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orders(Request $request)
    {
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;
            $status = $request->get('status', 'all');
            
            if (!$restaurantId) {
                Log::error('CashierController@orders: User has no restaurant_id', [
                    'user_id' => $user->id
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            Log::info('CashierController@orders: Loading orders', [
                'restaurant_id' => $restaurantId,
                'status_filter' => $status
            ]);
            
            $query = Order::where('restaurant_id', $restaurantId)
                ->with(['table', 'orderItems.menuItem', 'orderItems.orderItemOptions.menuOption'])
                ->orderBy('created_at', 'desc');

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $orders = $query->paginate(20);

            Log::info('CashierController@orders: Orders loaded successfully', [
                'orders_count' => $orders->count(),
                'total_orders' => $orders->total(),
                'status_filter' => $status
            ]);

            return view('cashier.orders', compact('orders', 'status'));

        } catch (\Exception $e) {
            Log::error('CashierController@orders: Error loading orders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'Failed to load orders: ' . $e->getMessage()]);
        }
    }

    public function showOrder($id)
    {
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;

            if (!$restaurantId) {
                Log::error('CashierController@showOrder: User has no restaurant_id', [
                    'user_id' => $user->id,
                    'order_id' => $id
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            Log::info('CashierController@showOrder: Loading order detail', [
                'order_id' => $id,
                'restaurant_id' => $restaurantId
            ]);

            $order = Order::with([
                'table', 
                'orderItems.menuItem', 
                'orderItems.orderItemOptions.menuOption',
                'timeline'
            ])
            ->where('restaurant_id', $restaurantId)
            ->findOrFail($id);

            Log::info('CashierController@showOrder: Order loaded successfully', [
                'order_id' => $id,
                'order_number' => $order->order_number,
                'status' => $order->status
            ]);

            return view('cashier.order-detail', compact('order'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('CashierController@showOrder: Order not found', [
                'order_id' => $id,
                'user_id' => Auth::id()
            ]);

            return back()->withErrors(['error' => 'Order not found']);

        } catch (\Exception $e) {
            Log::error('CashierController@showOrder: Error loading order detail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'Failed to load order detail: ' . $e->getMessage()]);
        }
    }

    public function updateOrderStatus(Request $request, $id)
    {
        try {
            Log::info('CashierController@updateOrderStatus: Starting status update', [
                'order_id' => $id,
                'new_status' => $request->get('status'),
                'user_id' => Auth::id()
            ]);

            $request->validate([
                'status' => 'required|in:pending,confirmed,preparing,ready,served,completed,cancelled'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('CashierController@updateOrderStatus: Validation failed', [
                'errors' => $e->errors(),
                'order_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;

            if (!$restaurantId) {
                Log::error('CashierController@updateOrderStatus: User has no restaurant_id', [
                    'user_id' => $user->id,
                    'order_id' => $id
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }

            $order = Order::where('id', $id)
                ->where('restaurant_id', $restaurantId)
                ->first();

            if (!$order) {
                Log::error('CashierController@updateOrderStatus: Order not found', [
                    'order_id' => $id,
                    'restaurant_id' => $restaurantId,
                    'user_id' => Auth::id()
                ]);
                throw new \Exception('Order not found or does not belong to your restaurant');
            }

            $oldStatus = $order->status;
            $order->updateStatus($request->status, $user, $request->notes);

            Log::info('CashierController@updateOrderStatus: Status updated successfully', [
                'order_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'updated_by' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('CashierController@updateOrderStatus: Error updating order status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function settings()
    {
        try {
            Log::info('CashierController@settings: Loading settings page', [
                'user_id' => Auth::id()
            ]);

            return view('cashier.settings');

        } catch (\Exception $e) {
            Log::error('CashierController@settings: Error loading settings page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'Failed to load settings page: ' . $e->getMessage()]);
        }
    }

    public function cart()
    {
        try {
            $user = Auth::user();
            /** @var \App\Models\Staff $user */
            $restaurantId = $user->restaurant_id;
            
            if (!$restaurantId) {
                Log::error('CashierController@cart: User has no restaurant_id', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                throw new \Exception('User is not assigned to any restaurant');
            }
    
            Log::info('CashierController@cart: Loading cart page', [
                'user_id' => $user->id,
                'restaurant_id' => $restaurantId
            ]);
    
            $tables = Table::where('restaurant_id', $restaurantId)
                ->orderBy('table_number')
                ->get();
    
            Log::info('CashierController@cart: Tables loaded for cart', [
                'tables_count' => $tables->count(),
                'restaurant_id' => $restaurantId
            ]);
    
            return view('cashier.cart', compact('tables'));
    
        } catch (\Exception $e) {
            Log::error('CashierController@cart: Error loading cart page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'Failed to load cart page: ' . $e->getMessage()]);
        }
    }
    
    public function validateCoupon(Request $request)
    {
        try {
            $request->validate([
                'coupon_code' => 'required|string|max:50',
                'order_amount' => 'required|numeric|min:0'
            ]);
    
            $user = Auth::user();
            $restaurantId = $user->restaurant_id;
    
            $coupon = \App\Models\Coupon::where('restaurant_id', $restaurantId)
                ->where('code', strtoupper($request->coupon_code))
                ->where('is_active', true)
                ->first();
    
            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon code not found'
                ]);
            }
    
            if (!$coupon->isValid($request->order_amount)) {
                $message = 'Coupon is not valid';
                
                if ($request->order_amount < $coupon->minimum_order_amount) {
                    $message = 'Minimum order amount is Rp ' . number_format($coupon->minimum_order_amount, 0, ',', '.');
                } elseif (!now()->between($coupon->valid_from, $coupon->valid_until)) {
                    $message = 'Coupon has expired or not yet active';
                } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                    $message = 'Coupon usage limit exceeded';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
    
            $discountAmount = $coupon->calculateDiscount($request->order_amount);
    
            return response()->json([
                'success' => true,
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'type' => $coupon->type,
                    'value' => $coupon->value
                ],
                'discount_amount' => $discountAmount
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error validating coupon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate coupon'
            ], 500);
        }
    }

    public function validateDiscount(Request $request)
    {
        try {
            $request->validate([
                'discount_code' => 'required|string|max:50',
                'order_amount' => 'required|numeric|min:0'
            ]);

            $user = Auth::user();
            $restaurantId = $user->restaurant_id;

            $discount = \App\Models\Discount::where('code', strtoupper($request->discount_code))
                ->where('is_active', true)
                ->first();

            if (!$discount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount code not found'
                ]);
            }

            if (!$discount->isApplicableToRestaurant($restaurantId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This discount is not applicable to your restaurant'
                ]);
            }

            if (!$discount->isAvailable()) {
                $message = 'Discount is not available';
                
                if (!$discount->isActive()) {
                    $message = 'Discount has expired or not yet active';
                } elseif ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                    $message = 'Discount usage limit exceeded';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }

            if ($discount->minimum_order && $request->order_amount < $discount->minimum_order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum order amount is Rp ' . number_format($discount->minimum_order, 0, ',', '.')
                ]);
            }

            $discountAmount = $discount->calculateDiscount($request->order_amount);

            return response()->json([
                'success' => true,
                'discount' => [
                    'id' => $discount->id,
                    'code' => $discount->code,
                    'name' => $discount->name,
                    'description' => $discount->description,
                    'type' => $discount->type,
                    'value' => $discount->value,
                    'formatted_value' => $discount->formatted_value
                ],
                'discount_amount' => $discountAmount
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating discount: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate discount'
            ], 500);
        }
    }
    
    // Helper method untuk menghitung discount
    private function calculateDiscountAmount($discount, $orderAmount)
    {
        if ($discount->type === 'percentage') {
            $discountAmount = ($orderAmount * $discount->value) / 100;
            
            // Apply maximum discount if set
            if ($discount->maximum_discount && $discountAmount > $discount->maximum_discount) {
                $discountAmount = $discount->maximum_discount;
            }
            
            return $discountAmount;
        } else if ($discount->type === 'fixed') {
            return min($discount->value, $orderAmount);
        }
        
        return 0;
    }

    private function isDiscountValid($discount, $orderAmount)
    {
        $now = now();
        
        // Check validity period
        if ($discount->starts_at && $now < $discount->starts_at) {
            return false;
        }
        
        if ($discount->expires_at && $now > $discount->expires_at) {
            return false;
        }
        
        // Check minimum order
        if ($discount->minimum_order && $orderAmount < $discount->minimum_order) {
            return false;
        }
        
        // Check usage limit
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            return false;
        }
        
        return true;
    }
}