{{-- resources/views/cashier/orders.blade.php - Enhanced with Cart Sync --}}

@extends('cashier.layout')

@section('title', 'Order Management - Cashier Pro')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
    <!-- Enhanced Header Section -->
    <div class="bg-gradient-to-r from-purple-50 via-white to-blue-50 border-b border-gray-200/50">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header with Stats -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-3 flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4">
                                <i class="fas fa-receipt text-white text-xl"></i>
                            </div>
                            Order Management
                        </h1>
                        <p class="text-gray-600 text-lg">Monitor and manage all customer orders</p>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('cashier.index') }}" 
                           class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-plus mr-2"></i>Create New Order
                        </a>
                        <button onclick="refreshOrders()" 
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-refresh mr-2"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $orders->where('status', 'pending')->count() }}</div>
                            <div class="text-xs text-gray-600">Pending</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $orders->where('status', 'preparing')->count() }}</div>
                            <div class="text-xs text-gray-600">Preparing</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $orders->where('status', 'ready')->count() }}</div>
                            <div class="text-xs text-gray-600">Ready</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $orders->where('status', 'served')->count() }}</div>
                            <div class="text-xs text-gray-600">Served</div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Filters -->
                <div class="bg-white rounded-3xl shadow-soft p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">Filter Orders</h2>
                            <p class="text-gray-600 text-sm">Filter orders by status and view detailed information</p>
                        </div>
                        
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('cashier.orders') }}" 
                               class="status-filter px-6 py-3 rounded-full text-sm font-semibold transition-all transform hover:scale-105 {{ $status === 'all' ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fas fa-list mr-2"></i>All Orders
                                <span class="ml-2 bg-white/20 px-2 py-1 rounded-full text-xs">{{ $orders->total() }}</span>
                            </a>
                            <a href="{{ route('cashier.orders') }}?status=pending" 
                               class="status-filter px-6 py-3 rounded-full text-sm font-semibold transition-all transform hover:scale-105 {{ $status === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fas fa-clock mr-2"></i>Pending
                            </a>
                            <a href="{{ route('cashier.orders') }}?status=confirmed" 
                               class="status-filter px-6 py-3 rounded-full text-sm font-semibold transition-all transform hover:scale-105 {{ $status === 'confirmed' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fas fa-check-circle mr-2"></i>Confirmed
                            </a>
                            <a href="{{ route('cashier.orders') }}?status=preparing" 
                               class="status-filter px-6 py-3 rounded-full text-sm font-semibold transition-all transform hover:scale-105 {{ $status === 'preparing' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fas fa-fire mr-2"></i>Preparing
                            </a>
                            <a href="{{ route('cashier.orders') }}?status=ready" 
                               class="status-filter px-6 py-3 rounded-full text-sm font-semibold transition-all transform hover:scale-105 {{ $status === 'ready' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fas fa-check mr-2"></i>Ready
                            </a>
                            <a href="{{ route('cashier.orders') }}?status=completed" 
                               class="status-filter px-6 py-3 rounded-full text-sm font-semibold transition-all transform hover:scale-105 {{ $status === 'completed' ? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fas fa-flag-checkered mr-2"></i>Completed
                            </a>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <form method="GET" class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <div class="relative">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Search by order number, customer name, or table..." 
                                           class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="status" value="{{ $status }}">
                            <button type="submit" 
                                    class="px-8 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                            @if(request('search'))
                            <a href="{{ route('cashier.orders') }}?status={{ $status }}" 
                               class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Grid -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8" id="orders-grid">
                @forelse($orders as $order)
                <div class="order-card bg-white rounded-3xl shadow-soft overflow-hidden hover:shadow-hover transition-all duration-500 transform hover:-translate-y-2 animate-fade-in">
                    <!-- Order Header -->
                    <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-hashtag text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-xl text-gray-800">{{ $order->order_number }}</h3>
                                    <p class="text-sm text-gray-600">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            
                            <span class="status-badge px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-pulse-glow
                                {{ $order->status === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white' : '' }}
                                {{ $order->status === 'confirmed' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : '' }}
                                {{ $order->status === 'preparing' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white' : '' }}
                                {{ $order->status === 'ready' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : '' }}
                                {{ $order->status === 'served' ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white' : '' }}
                                {{ $order->status === 'completed' ? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white' : '' }}
                                {{ $order->status === 'cancelled' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white' : '' }}">
                                {{ getOrderStatusLabel($order->status) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-chair mr-2"></i>
                                    <span class="font-semibold">Table {{ $order->table->table_number ?? '-' }}</span>
                                </div>
                                @if($order->customer_name)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-user mr-2"></i>
                                    <span>{{ $order->customer_name }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="text-gray-500">
                                {{ $order->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-6">
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-utensils mr-2 text-purple-500"></i>
                                Order Items ({{ $order->orderItems->count() }})
                            </h4>
                            
                            <div class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar">
                                @foreach($order->orderItems->take(4) as $item)
                                <div class="flex justify-between items-start p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-800 text-sm">{{ $item->menuItem->name }}</div>
                                        @if($item->orderItemOptions && $item->orderItemOptions->count() > 0)
                                        <div class="text-xs text-purple-600 mt-1">
                                            <i class="fas fa-plus mr-1"></i>{{ $item->orderItemOptions->pluck('menuOption.name')->implode(', ') }}
                                        </div>
                                        @endif
                                        @if($item->special_instructions)
                                        <div class="text-xs text-blue-600 italic mt-1">
                                            <i class="fas fa-sticky-note mr-1"></i>{{ Str::limit($item->special_instructions, 30) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="text-right ml-3">
                                        <div class="font-bold text-lg text-purple-600">{{ $item->quantity }}x</div>
                                        <div class="text-sm text-gray-600">{{ formatCurrency($item->total_price) }}</div>
                                    </div>
                                </div>
                                @endforeach
                                
                                @if($order->orderItems->count() > 4)
                                <div class="text-center py-3 text-sm text-gray-600 bg-gray-50 rounded-2xl">
                                    <i class="fas fa-ellipsis-h mr-2"></i>
                                    +{{ $order->orderItems->count() - 4 }} more items
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Total -->
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl p-4 mb-6">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-semibold">{{ formatCurrency($order->subtotal) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax & Service:</span>
                                    <span class="font-semibold">{{ formatCurrency($order->tax_amount + $order->service_charge) }}</span>
                                </div>
                                <div class="border-t border-purple-200 pt-2">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-lg text-gray-800">Total:</span>
                                        <span class="font-bold text-2xl text-purple-600">{{ formatCurrency($order->total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
                        <div class="flex flex-col space-y-3">
                            <a href="{{ route('cashier.orders.show', $order->id) }}" 
                               class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-4 rounded-2xl text-center font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </a>
                            
                            <div class="flex space-x-2">
                                @if($order->status === 'pending')
                                <button onclick="updateOrderStatus({{ $order->id }}, 'confirmed')" 
                                        class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-2 px-3 rounded-xl text-sm font-semibold transition-all transform hover:scale-105">
                                    <i class="fas fa-check mr-1"></i>Confirm
                                </button>
                                <button onclick="updateOrderStatus({{ $order->id }}, 'cancelled')" 
                                        class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white py-2 px-3 rounded-xl text-sm font-semibold transition-all transform hover:scale-105">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                                @elseif($order->status === 'ready')
                                <button onclick="updateOrderStatus({{ $order->id }}, 'served')" 
                                        class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-2 px-3 rounded-xl text-sm font-semibold transition-all transform hover:scale-105">
                                    <i class="fas fa-utensils mr-1"></i>Mark as Served
                                </button>
                                @elseif($order->status === 'served')
                                <button onclick="updateOrderStatus({{ $order->id }}, 'completed')" 
                                        class="w-full bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white py-2 px-3 rounded-xl text-sm font-semibold transition-all transform hover:scale-105">
                                    <i class="fas fa-flag-checkered mr-1"></i>Complete
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-3xl shadow-soft p-16 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-8">
                            <i class="fas fa-receipt text-4xl text-gray-300"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-600 mb-4">No Orders Found</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">
                            @if(request('search'))
                                No orders match your search criteria. Try different keywords or clear the search.
                            @else
                                No orders are available for the selected filter. Start taking new orders to see them here.
                            @endif
                        </p>
                        <div class="flex flex-wrap gap-4 justify-center">
                            <a href="{{ route('cashier.index') }}" class="inline-block bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-4 px-8 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>Create New Order
                            </a>
                            @if(request('search'))
                            <a href="{{ route('cashier.orders') }}" class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-700 py-4 px-8 rounded-2xl font-semibold transition-all transform hover:scale-105">
                                <i class="fas fa-list mr-2"></i>View All Orders
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Enhanced Pagination -->
            @if($orders->hasPages())
            <div class="mt-12 flex justify-center">
                <div class="bg-white rounded-3xl shadow-soft p-6">
                    <div class="flex items-center space-x-4">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Enhanced Status Update Modal -->
<div id="status-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl animate-slide-up">
            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Update Order Status</h3>
                    <p class="text-gray-600">Add optional notes for this status update</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Notes (Optional)</label>
                    <textarea id="status-notes" 
                             class="w-full p-4 border-2 border-gray-200 rounded-2xl resize-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all" 
                             rows="4" 
                             placeholder="Add any notes about this status update..."></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button id="cancel-status-update" 
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-4 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button id="confirm-status-update" 
                            class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-4 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-check mr-2"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #a855f7;
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9333ea;
}
</style>
@endsection

@push('scripts')
<script src="{{ asset('js/cashier.js') }}"></script>
<script>
let currentOrderId = null;
let currentStatus = null;

function updateOrderStatus(orderId, status) {
    currentOrderId = orderId;
    currentStatus = status;
    
    // Add entrance animation
    const modal = document.getElementById('status-modal');
    modal.classList.remove('hidden');
    modal.querySelector('.bg-white').classList.add('animate-slide-up');
}

function refreshOrders() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Refreshing...';
    button.disabled = true;
    
    setTimeout(() => {
        location.reload();
    }, 500);
}

document.getElementById('cancel-status-update').addEventListener('click', () => {
    closeStatusModal();
});

document.getElementById('confirm-status-update').addEventListener('click', async () => {
    const notes = document.getElementById('status-notes').value;
    const button = document.getElementById('confirm-status-update');
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    button.disabled = true;
    
    try {
        const response = await axios.patch(`/cashier/orders/${currentOrderId}/status`, {
            status: currentStatus,
            notes: notes
        });
        
        if (response.data.success) {
            showToast('Order status updated successfully!');
            
            // Show success animation before reload
            button.innerHTML = '<i class="fas fa-check mr-2"></i>Updated!';
            button.classList.remove('from-blue-500', 'to-blue-600');
            button.classList.add('from-green-500', 'to-green-600');
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(response.data.message || 'Failed to update order status');
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        showToast('Failed to update order status', 'error');
        
        // Restore button
        button.innerHTML = originalContent;
        button.disabled = false;
    }
});

function closeStatusModal() {
    const modal = document.getElementById('status-modal');
    modal.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.style.opacity = '';
        document.getElementById('status-notes').value = '';
        currentOrderId = null;
        currentStatus = null;
    }, 200);
}

// Close modal when clicking outside
document.getElementById('status-modal').addEventListener('click', (e) => {
    if (e.target.id === 'status-modal') {
        closeStatusModal();
    }
});

function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-2xl shadow-lg transform translate-x-full transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    toast.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span class="font-medium">${message}</span>
        </div>
    `;

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Auto-refresh every 30 seconds for pending orders
if (window.location.search.includes('status=pending') || window.location.search === '') {
    setInterval(() => {
        // Only refresh if no modal is open
        if (document.getElementById('status-modal').classList.contains('hidden')) {
            // Check if cart notification is active and preserve it
            if (cashierApp && cashierApp.cart && cashierApp.cart.length > 0) {
                // Page will reload and cart will be restored from localStorage
                location.reload();
            } else {
                location.reload();
            }
        }
    }, 30000);
}
</script>
@endpush