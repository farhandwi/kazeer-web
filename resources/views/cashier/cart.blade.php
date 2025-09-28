{{-- resources/views/cashier/cart.blade.php --}}
@extends('cashier.layout') 
@section('title', 'Cashier Pro - Cart')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="bg-white rounded-3xl shadow-soft p-6 md:p-8 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-shopping-cart text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Your Cart</h1>
                        <p class="text-gray-500 text-sm md:text-base">Review your order before checking out</p>
                    </div>
                </div>
                <a href="{{ route('cashier.index') }}" 
                   class="text-purple-600 hover:text-purple-700 bg-white px-4 py-2 sm:px-6 sm:py-3 rounded-2xl shadow-soft hover:shadow-hover transition-all transform hover:scale-105 text-sm md:text-base font-semibold flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Menu
                </a>
            </div>
        </div>

        <!-- Current Table Info -->
        <div id="current-table-info" class="mb-6 md:mb-8 hidden">
            <div class="bg-gradient-to-r from-purple-50 via-purple-100 to-purple-50 rounded-2xl md:rounded-3xl shadow-soft p-4 md:p-6 border-2 border-purple-200/50">
                <!-- Mobile Layout: Stack vertically -->
                <div class="flex flex-col space-y-4 sm:hidden">
                    <!-- Table Info -->
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-chair text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-purple-800">Currently Serving</h3>
                            <p class="text-purple-600 text-base">Table <span id="current-table-number" class="font-semibold">-</span></p>
                            <p class="text-purple-500 text-xs">Ready to checkout</p>
                        </div>
                    </div>
                    
                    <!-- Mobile Buttons -->
                    <div class="flex space-x-2">
                        <button id="clear-table-btn" 
                                class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all text-sm">
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </button>
                        <button id="change-table-btn" 
                                class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-xl transition-all shadow-lg">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            Change
                        </button>
                    </div>
                </div>
                
                <!-- Tablet Layout: Horizontal with adjusted spacing -->
                <div class="hidden sm:flex lg:hidden items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-chair text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-purple-800">Currently Serving</h3>
                            <p class="text-purple-600 text-base">Table <span id="current-table-number-tablet" class="font-semibold">-</span></p>
                            <p class="text-purple-500 text-sm">Ready to checkout</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button id="clear-table-btn-tablet" 
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all text-sm">
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </button>
                        <button id="change-table-btn-tablet" 
                                class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-xl transition-all shadow-md">
                            <i class="fas fa-exchange-alt mr-1"></i>
                            Change
                        </button>
                    </div>
                </div>
                
                <!-- Desktop Layout: Original design -->
                <div class="hidden lg:flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                            <i class="fas fa-chair text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-purple-800">Currently Serving</h3>
                            <p class="text-purple-600 text-lg">Table <span id="current-table-number-desktop" class="font-semibold">-</span></p>
                            <p class="text-purple-500 text-sm">Ready to checkout</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button id="clear-table-btn-desktop" 
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all text-sm">
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </button>
                        <button id="change-table-btn-desktop" 
                                class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-2xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            Change Table
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Cart Items Section -->
            <div class="xl:col-span-2">
                <div id="cart-items-container" class="space-y-6"></div>
                
                <div id="empty-cart-message" class="hidden text-center py-12 bg-white rounded-3xl shadow-soft mt-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shopping-cart text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Your cart is empty</p>
                    <p class="text-gray-400 text-sm">Add some delicious food and drinks to your cart!</p>
                </div>
            </div>

            <!-- Sidebar Section -->
            <div class="space-y-8">
                <!-- Customer Information Section -->
                <div class="bg-white rounded-3xl shadow-soft">
                    <div class="p-6 md:p-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            Customer Information
                        </h3>
                        
                        <form id="customer-form" class="space-y-4">
                            <div>
                                <label for="customer-name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Customer Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="customer-name" 
                                       name="customer_name"
                                       placeholder="Enter customer name..." 
                                       class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                                       required>
                                <div class="text-xs text-gray-500 mt-1">Required for order processing</div>
                            </div>
                            
                            <div>
                                <label for="customer-phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number <span class="text-gray-400">(Optional)</span>
                                </label>
                                <input type="tel" 
                                       id="customer-phone" 
                                       name="customer_phone"
                                       placeholder="08123456789" 
                                       class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all">
                                <div class="text-xs text-gray-500 mt-1">For delivery updates</div>
                            </div>

                            <!-- Payment Method Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Payment Method <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3" id="payment-methods">
                                    <label class="payment-method-option cursor-pointer">
                                        <input type="radio" name="payment_method" value="cash" class="sr-only" checked>
                                        <div class="flex items-center justify-center p-3 border-2 border-gray-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                                            <div class="text-center">
                                                <div class="text-xl mb-1 group-hover:scale-110 transition-transform">💵</div>
                                                <div class="font-medium text-gray-700 group-hover:text-blue-600 text-sm">Cash</div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-method-option cursor-pointer">
                                        <input type="radio" name="payment_method" value="card" class="sr-only">
                                        <div class="flex items-center justify-center p-3 border-2 border-gray-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                                            <div class="text-center">
                                                <div class="text-xl mb-1 group-hover:scale-110 transition-transform">💳</div>
                                                <div class="font-medium text-gray-700 group-hover:text-blue-600 text-sm">Card</div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-method-option cursor-pointer">
                                        <input type="radio" name="payment_method" value="digital_wallet" class="sr-only">
                                        <div class="flex items-center justify-center p-3 border-2 border-gray-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                                            <div class="text-center">
                                                <div class="text-xl mb-1 group-hover:scale-110 transition-transform">📱</div>
                                                <div class="font-medium text-gray-700 group-hover:text-blue-600 text-sm">E-Wallet</div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-method-option cursor-pointer">
                                        <input type="radio" name="payment_method" value="transfer" class="sr-only">
                                        <div class="flex items-center justify-center p-3 border-2 border-gray-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                                            <div class="text-center">
                                                <div class="text-xl mb-1 group-hover:scale-110 transition-transform">🏦</div>
                                                <div class="font-medium text-gray-700 group-hover:text-blue-600 text-sm">Transfer</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="text-xs text-gray-500 mt-2">Select how the customer will pay</div>
                            </div>

                            <!-- Coupon Section -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-ticket-alt mr-2 text-yellow-600"></i>
                                    Coupon Code <span class="text-gray-400">(Optional)</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                        id="coupon-code" 
                                        name="coupon_code"
                                        placeholder="Enter coupon code..." 
                                        class="flex-1 p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all text-sm uppercase">
                                    <button type="button" 
                                            id="apply-coupon-btn"
                                            class="px-4 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg">
                                        Apply
                                    </button>
                                </div>
                                <div id="coupon-status" class="mt-2 text-xs hidden"></div>
                                <div id="applied-coupon" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 hidden">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <span id="coupon-name">Coupon Applied</span>
                                        </div>
                                        <button type="button" id="remove-coupon-btn" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions for Customer Info -->
                            <div class="flex flex-wrap gap-2 pt-2">
                                <button type="button" 
                                        class="quick-fill-btn px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs transition-all" 
                                        data-value="Walk-in Customer">
                                    <i class="fas fa-walking mr-1"></i>Walk-in
                                </button>
                                <button type="button" 
                                        class="quick-fill-btn px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs transition-all" 
                                        data-value="Dine-in Guest">
                                    <i class="fas fa-utensils mr-1"></i>Dine-in
                                </button>
                                <button type="button" 
                                        class="quick-fill-btn px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs transition-all" 
                                        data-value="Takeaway Order">
                                    <i class="fas fa-shopping-bag mr-1"></i>Takeaway
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary Section -->
                <div class="bg-white rounded-3xl shadow-soft sticky top-4">
                    <div class="p-6 md:p-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-calculator text-white"></i>
                            </div>
                            Order Summary
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Subtotal:</span>
                                <span id="subtotal-amount" class="font-semibold text-lg">Rp 0</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Tax (11%):</span>
                                <span id="tax-amount" class="font-semibold text-lg">Rp 0</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Service Charge (5%):</span>
                                <span id="service-charge-amount" class="font-semibold text-lg">Rp 0</span>
                            </div>
                            <div id="coupon-discount-row" class="flex justify-between items-center py-2 hidden">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-ticket-alt text-yellow-600 mr-2"></i>
                                    Coupon Discount:
                                </span>
                                <span id="coupon-discount-amount" class="font-semibold text-lg text-green-600">-Rp 0</span>
                            </div>
                            <div class="border-t-2 border-gray-200 pt-4 mt-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-xl text-gray-800">Total:</span>
                                    <span id="total-amount" class="font-bold text-3xl text-purple-600">Rp 0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <button id="checkout-button" 
                                    class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-4 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed" 
                                    disabled>
                                <i class="fas fa-cash-register mr-3"></i>
                                Proceed to Checkout
                            </button>
                        </div>

                        <!-- Order Info -->
                        <div class="mt-4">
                            <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-xl">
                                <i class="fas fa-info-circle mr-1"></i>
                                Order will be sent to kitchen after processing. Customer will receive updates if phone number is provided.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Selection Modal -->
<div id="table-selection-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-chair text-white text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Select Table</h2>
                <p class="text-gray-600 text-lg">Choose a table to process this order</p>
            </div>
            
            <div id="tables-grid" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @if(isset($tables))
                    @foreach($tables as $table)
                    <button class="table-btn group p-8 bg-gradient-to-br from-gray-50 to-gray-100 hover:from-purple-50 hover:to-purple-100 rounded-3xl text-center transition-all transform hover:scale-105 hover:shadow-xl border-2 border-gray-200 hover:border-purple-200" 
                            data-table-id="{{ $table->id }}" 
                            data-table-number="{{ $table->table_number }}">
                        <div class="w-16 h-16 bg-gray-300 group-hover:bg-gradient-to-r group-hover:from-purple-400 group-hover:to-purple-500 rounded-2xl flex items-center justify-center mx-auto mb-4 transition-all shadow-lg">
                            <i class="fas fa-chair text-gray-600 group-hover:text-white transition-all text-2xl"></i>
                        </div>
                        <div class="font-bold text-gray-700 group-hover:text-purple-600 transition-all text-lg">
                            Table {{ $table->table_number }}
                        </div>
                        <div class="text-xs text-gray-500 group-hover:text-purple-500 transition-all mt-1">
                            {{ $table->capacity ?? 4 }} seats
                        </div>
                    </button>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Validation Error Modal -->
<div id="validation-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl animate-slide-up">
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Missing Information</h3>
                    <p class="text-gray-600">Please provide required details before processing the order</p>
                </div>
                
                <div id="validation-errors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm"></div>
                
                <button id="close-validation-modal" 
                        class="w-full px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all">
                    OK, Got It
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl p-8 shadow-2xl">
            <div class="text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mx-auto mb-4"></div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Processing Order</h3>
                <p class="text-gray-600">Please wait while we process your order...</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl animate-slide-up">
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Order Placed Successfully!</h3>
                    <p class="text-gray-600">Your order has been sent to the kitchen</p>
                </div>
                
                <div id="success-message" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm text-center"></div>
                
                <button id="close-success-modal" 
                        class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl font-semibold transition-all transform hover:scale-105">
                    <i class="fas fa-check mr-2"></i>
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notifications-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script src="{{ asset('js/cart-manager.js') }}"></script>
@endpush