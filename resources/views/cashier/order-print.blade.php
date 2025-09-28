{{-- resources/views/cashier/order-print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt - #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .order-info {
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .order-info div {
            margin-bottom: 3px;
        }
        
        .items-section {
            margin-bottom: 20px;
        }
        
        .item {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dotted #ccc;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .item-details {
            font-size: 12px;
            color: #666;
            margin-left: 10px;
        }
        
        .options {
            margin-left: 15px;
            font-size: 12px;
            color: #444;
        }
        
        .instructions {
            margin-left: 15px;
            font-size: 12px;
            font-style: italic;
            color: #666;
        }
        
        .totals {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 20px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .final-total {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 12px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .receipt {
                border: none;
                box-shadow: none;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>RESTAURANT RECEIPT</h1>
            <h2>Order #{{ $order->order_number }}</h2>
        </div>
        
        <div class="order-info">
            <div><strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
            <div><strong>Table:</strong> {{ $order->table->table_number ?? 'N/A' }}</div>
            @if($order->customer_name)
            <div><strong>Customer:</strong> {{ $order->customer_name }}</div>
            @endif
            <div><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $order->status)) }}</div>
            <div><strong>Cashier:</strong> {{ auth()->user()->name }}</div>
        </div>
        
        <div class="items-section">
            <div style="font-weight: bold; margin-bottom: 10px;">ORDER ITEMS:</div>
            @foreach($order->orderItems as $item)
            <div class="item">
                <div class="item-header">
                    <span>{{ $item->menuItem->name }}</span>
                    <span>{{ $item->quantity }}x</span>
                </div>
                <div class="item-details">
                    {{ formatCurrency($item->unit_price) }} × {{ $item->quantity }} = {{ formatCurrency($item->unit_price * $item->quantity) }}
                </div>
                
                @if($item->orderItemOptions && $item->orderItemOptions->count() > 0)
                <div class="options">
                    <strong>Options:</strong><br>
                    @foreach($item->orderItemOptions as $option)
                    - {{ $option->menuOption->name }}
                    @if($option->option_price > 0)
                        (+{{ formatCurrency($option->option_price) }})
                    @endif
                    <br>
                    @endforeach
                </div>
                @endif
                
                @if($item->special_instructions)
                <div class="instructions">
                    <strong>Instructions:</strong> {{ $item->special_instructions }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
        
        <div class="totals">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>{{ formatCurrency($order->subtotal) }}</span>
            </div>
            <div class="total-line">
                <span>Tax (11%):</span>
                <span>{{ formatCurrency($order->tax_amount) }}</span>
            </div>
            <div class="total-line">
                <span>Service (5%):</span>
                <span>{{ formatCurrency($order->service_charge) }}</span>
            </div>
            @if($order->discount_amount > 0)
            <div class="total-line" style="color: green;">
                <span>Discount:</span>
                <span>-{{ formatCurrency($order->discount_amount) }}</span>
            </div>
            @endif
            <div class="total-line final-total">
                <span>TOTAL:</span>
                <span>{{ formatCurrency($order->total_amount) }}</span>
            </div>
        </div>
        
        <div class="footer">
            <div>Thank you for your visit!</div>
            <div style="margin-top: 5px;">{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        }
    </script>
</body>
</html>