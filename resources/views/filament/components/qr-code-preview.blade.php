<?php
// resources/views/filament/forms/components/qr-code-preview.blade.php
?>

@if($getRecord() && $getRecord()->qr_code_url)
<div class="qr-code-preview">
    <div class="flex flex-col items-center space-y-3 p-4 bg-gray-50 rounded-lg">
        <img src="{{ $getRecord()->qr_code_url }}" 
             alt="QR Code for Table {{ $getRecord()->table_number }}" 
             class="w-32 h-32 border border-gray-200 rounded">
        
        <div class="text-center">
            <p class="text-sm text-gray-600 font-medium">
                Table {{ $getRecord()->table_number }} QR Code
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Scan to order from this table
            </p>
        </div>
        
        <div class="flex space-x-2">
            <a href="{{ $getRecord()->qr_code_url }}" 
               download="table_{{ $getRecord()->table_number }}_qr_code.png"
               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download
            </a>
            
            <button type="button" 
                    onclick="copyToClipboard('{{ $getRecord()->getOrderUrl() }}')"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy URL
            </button>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('URL copied to clipboard!', 'success');
        }, function(err) {
            console.error('Could not copy text: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        var successful = document.execCommand('copy');
        if (successful) {
            showNotification('URL copied to clipboard!', 'success');
        } else {
            showNotification('Failed to copy URL', 'error');
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        showNotification('Failed to copy URL', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>
@else
<div class="qr-code-preview">
    <div class="flex flex-col items-center space-y-3 p-4 bg-gray-50 rounded-lg">
        <div class="w-32 h-32 bg-gray-200 rounded flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
        </div>
        <p class="text-sm text-gray-500 text-center">
            QR code will be generated automatically<br>after saving the table
        </p>
    </div>
</div>
@endif