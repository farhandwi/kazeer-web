{{-- resources/views/cashier/order-detail.blade.php --}}

@extends('cashier.layout')

@section('title', 'Order Details - #' . $order->order_number)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
    <!-- Enhanced Header Section -->
    <div class="bg-gradient-to-r from-purple-50 via-white to-blue-50 border-b border-gray-200/50">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Back Navigation -->
                <div class="mb-6">
                    <a href="{{ route('cashier.orders') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700 bg-white px-6 py-3 rounded-2xl shadow-soft hover:shadow-hover transition-all transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-3"></i>
                        <span class="font-semibold">Back to Orders</span>
                    </a>
                </div>

                <!-- Order Header -->
                <div class="bg-white rounded-3xl shadow-soft p-8">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center mb-4">
                                <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mr-6">
                                    <i class="fas fa-receipt text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-4xl font-bold text-gray-800 mb-2">Order #{{ $order->order_number }}</h1>
                                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar mr-2 text-purple-500"></i>
                                            <span class="font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-chair mr-2 text-purple-500"></i>
                                            <span class="font-medium">Table {{ $order->table->table_number ?? '-' }}</span>
                                        </div>
                                        @if($order->customer_name)
                                        <div class="flex items-center">
                                            <i class="fas fa-user mr-2 text-purple-500"></i>
                                            <span class="font-medium">{{ $order->customer_name }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="mb-4">
                                <span class="status-badge inline-block px-6 py-3 rounded-2xl text-lg font-bold shadow-lg animate-pulse-glow
                                    {{ $order->status === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white' : '' }}
                                    {{ $order->status === 'confirmed' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : '' }}
                                    {{ $order->status === 'preparing' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white' : '' }}
                                    {{ $order->status === 'ready' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : '' }}
                                    {{ $order->status === 'served' ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white' : '' }}
                                    {{ $order->status === 'completed' ? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white' : '' }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                            <div class="text-4xl font-bold text-purple-600">
                                {{ formatCurrency($order->total_amount) }}
                            </div>
                            <div class="text-sm text-gray-600 mt-2">
                                Total Amount
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="flex flex-wrap gap-4">
                            @if($order->status === 'pending')
                            <button onclick="updateOrderStatus({{ $order->id }}, 'confirmed')" 
                                    class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-check mr-2"></i>Confirm Order
                            </button>
                            <button onclick="updateOrderStatus({{ $order->id }}, 'cancelled')" 
                                    class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white py-3 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-times mr-2"></i>Cancel Order
                            </button>
                            @elseif($order->status === 'ready')
                            <button onclick="updateOrderStatus({{ $order->id }}, 'served')" 
                                    class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-3 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-utensils mr-2"></i>Mark as Served
                            </button>
                            @elseif($order->status === 'served')
                            <button onclick="updateOrderStatus({{ $order->id }}, 'completed')" 
                                    class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white py-3 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-flag-checkered mr-2"></i>Complete Order
                            </button>
                            @endif
                            
                            <button onclick="printOrder()" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-print mr-2"></i>Print Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Order Items -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-3xl shadow-soft">
                        <div class="p-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-list text-white"></i>
                                </div>
                                Order Items
                                <span class="ml-4 bg-purple-100 text-purple-800 px-4 py-2 rounded-full text-sm font-bold">
                                    {{ $order->orderItems->count() }} items
                                </span>
                            </h2>
                            
                            <div class="space-y-6">
                                @foreach($order->orderItems as $item)
                                <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-200 rounded-3xl p-6 hover:shadow-lg transition-all">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="font-bold text-gray-800 text-xl mr-4">{{ $item->menuItem->name }}</h3>
                                                <span class="status-badge px-3 py-1 rounded-full text-xs font-bold
                                                    {{ $item->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $item->status === 'preparing' ? 'bg-orange-100 text-orange-800' : '' }}
                                                    {{ $item->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $item->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                    {{ $item->status_label }}
                                                </span>
                                            </div>
                                            <div class="text-gray-600 mb-2">
                                                <span class="font-semibold">{{ formatCurrency($item->unit_price) }}</span>
                                                <span class="mx-2">×</span>
                                                <span class="font-semibold">{{ $item->quantity }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-purple-600">
                                                {{ formatCurrency($item->total_price) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($item->orderItemOptions && $item->orderItemOptions->count() > 0)
                                    <div class="bg-white border border-purple-200 rounded-2xl p-4 mb-4">
                                        <div class="flex items-center mb-3">
                                            <i class="fas fa-plus-circle text-purple-500 mr-2"></i>
                                            <span class="font-semibold text-purple-700">Selected Options:</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($item->orderItemOptions as $option)
                                            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-xl">
                                                <span class="text-gray-700 font-medium">{{ $option->menuOption->name }}</span>
                                                <span class="font-bold text-purple-600">
                                                    @if($option->option_price > 0)
                                                        +{{ formatCurrency($option->option_price) }}
                                                    @else
                                                        <span class="text-green-600">Free</span>
                                                    @endif
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($item->special_instructions)
                                    <div class="bg-blue-50 border-l-4 border-blue-400 rounded-r-2xl p-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-sticky-note text-blue-500 mr-3 mt-1"></i>
                                            <div>
                                                <div class="font-semibold text-blue-800 mb-1">Special Instructions:</div>
                                                <div class="text-blue-700">{{ $item->special_instructions }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Payment Summary -->
                    <div class="bg-white rounded-3xl shadow-soft">
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-calculator text-white"></i>
                                </div>
                                Payment Summary
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-semibold text-lg">{{ formatCurrency($order->subtotal) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600">Tax (11%):</span>
                                    <span class="font-semibold text-lg">{{ formatCurrency($order->tax_amount) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600">Service Charge (5%):</span>
                                    <span class="font-semibold text-lg">{{ formatCurrency($order->service_charge) }}</span>
                                </div>
                                @if($order->discount_amount > 0)
                                <div class="flex justify-between items-center py-2 text-green-600">
                                    <span>Discount:</span>
                                    <span class="font-semibold text-lg">-{{ formatCurrency($order->discount_amount) }}</span>
                                </div>
                                @endif
                                <div class="border-t-2 border-gray-200 pt-4 mt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-xl text-gray-800">Total:</span>
                                        <span class="font-bold text-3xl text-purple-600">{{ formatCurrency($order->total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-gray-700 font-semibold">Payment Status:</span>
                                        <span class="status-badge px-3 py-1 rounded-full text-sm font-bold
                                            {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $order->payment_status_label }}
                                        </span>
                                    </div>
                                    @if($order->payment_method)
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700 font-semibold">Payment Method:</span>
                                        <span class="font-bold text-purple-600">{{ ucfirst($order->payment_method) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="bg-white rounded-3xl shadow-soft">
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-history text-white"></i>
                                </div>
                                Order Timeline
                            </h3>
                            
                            <div class="space-y-6">
                                @forelse($order->timeline ?? [] as $event)
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0 w-4 h-4 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full mt-2 shadow-lg"></div>
                                    <div class="flex-1 pb-6 border-l-2 border-gray-200 pl-6 ml-2">
                                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl p-4">
                                            <div class="font-bold text-gray-800 mb-1">{{ $event->title }}</div>
                                            <div class="text-gray-600 mb-2">{{ $event->description }}</div>
                                            <div class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-clock mr-2"></i>
                                                {{ $event->created_at->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-history text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No timeline events yet</p>
                                    <p class="text-gray-400 text-sm">Timeline events will appear here as the order progresses</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Status Update Modal -->
<div id="status-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl animate-slide-up">
            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Update Order Status</h3>
                    <p class="text-gray-600">Add optional notes for this status update</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Notes (Optional)</label>
                    <textarea id="status-notes" 
                             class="w-full p-4 border-2 border-gray-200 rounded-2xl resize-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all" 
                             rows="4" 
                             placeholder="Add any notes about this status update..."></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button id="cancel-status-update" 
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-4 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button id="confirm-status-update" 
                            class="flex-1 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-4 px-6 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-check mr-2"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-section, .print-section * {
        visibility: visible;
    }
    .print-section {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
@endsection

@push('scripts')
<script>
let currentOrderId = {{ $order->id }};
let currentStatus = null;

function updateOrderStatus(orderId, status) {
    currentStatus = status;
    const modal = document.getElementById('status-modal');
    modal.classList.remove('hidden');
    modal.querySelector('.bg-white').classList.add('animate-slide-up');
}

function printOrder() {
    // Create a print-friendly version
    const printWindow = window.open('', '_blank');
    const orderContent = `
        <html>
        <head>
            <title>Order #{{ $order->order_number }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .order-info { margin-bottom: 20px; }
                .items { margin-bottom: 20px; }
                .item { border-bottom: 1px solid #eee; padding: 10px 0; }
                .total { font-weight: bold; font-size: 18px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Order Receipt</h1>
                <h2>#{{ $order->order_number }}</h2>
            </div>
            <div class="order-info">
                <p><strong>Date:</strong> {{ $order->created_at->format('d M Y, H:i') }}</p>
                <p><strong>Table:</strong> {{ $order->table->table_number ?? '-' }}</p>
                @if($order->customer_name)
                <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                @endif
                <p><strong>Status:</strong> {{ $order->status_label }}</p>
            </div>
            <div class="items">
                <h3>Order Items:</h3>
                @foreach($order->orderItems as $item)
                <div class="item">
                    <p><strong>{{ $item->menuItem->name }}</strong></p>
                    <p>Quantity: {{ $item->quantity }} × {{ formatCurrency($item->unit_price) }} = {{ formatCurrency($item->total_price) }}</p>
                    @if($item->orderItemOptions && $item->orderItemOptions->count() > 0)
                    <p>Options: {{ $item->orderItemOptions->pluck('menuOption.name')->implode(', ') }}</p>
                    @endif
                    @if($item->special_instructions)
                    <p>Instructions: {{ $item->special_instructions }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="total">
                <p>Subtotal: {{ formatCurrency($order->subtotal) }}</p>
                <p>Tax: {{ formatCurrency($order->tax_amount) }}</p>
                <p>Service: {{ formatCurrency($order->service_charge) }}</p>
                <p>Total: {{ formatCurrency($order->total_amount) }}</p>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.write(orderContent);
    printWindow.document.close();
    printWindow.print();
}

function closeStatusModal() {
    const modal = document.getElementById('status-modal');
    modal.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.style.opacity = '';
        document.getElementById('status-notes').value = '';
        currentStatus = null;
    }, 200);
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
            button.classList.remove('from-purple-500', 'to-purple-600');
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

// Close modal when clicking outside
document.getElementById('status-modal').addEventListener('click', (e) => {
    if (e.target.id === 'status-modal') {
        closeStatusModal();
    }
});

// Helper function for currency formatting (if not defined globally)
if (typeof formatCurrency === 'undefined') {
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
}
</script>
@endpush