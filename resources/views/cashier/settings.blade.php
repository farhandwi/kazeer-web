@extends('cashier.layout')

@section('title', 'Pengaturan - Settings')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="bg-white rounded-xl card-shadow p-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-cog mr-3 text-purple-600"></i>
                Pengaturan Sistem
            </h1>
            <p class="text-gray-600 mt-2">Kelola pengaturan dan preferensi sistem cashier</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl card-shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-bolt mr-2 text-purple-600"></i>
                    Quick Actions
                </h2>
                
                <div class="space-y-3">
                    <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg font-medium transition-all text-left">
                        <i class="fas fa-sync-alt mr-3"></i>
                        Refresh Menu Items
                    </button>
                    <button class="w-full bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-lg font-medium transition-all text-left">
                        <i class="fas fa-download mr-3"></i>
                        Export Transaksi Hari Ini
                    </button>
                    <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-lg font-medium transition-all text-left">
                        <i class="fas fa-print mr-3"></i>
                        Test Print Receipt
                    </button>
                    <button class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-lg font-medium transition-all text-left">
                        <i class="fas fa-trash mr-3"></i>
                        Clear Cart Cache
                    </button>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-white rounded-xl card-shadow p-6 mt-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2 text-purple-600"></i>
                    System Information
                </h2>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Version:</span>
                        <span class="font-medium">v2.1.0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Update:</span>
                        <span class="font-medium">{{ now()->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Since:</span>
                        <span class="font-medium">{{ now()->subHours(8)->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Restaurant:</span>
                        <span class="font-medium">Main Branch</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Panels -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Display Settings -->
            <div class="bg-white rounded-xl card-shadow">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-desktop mr-2 text-purple-600"></i>
                        Display Settings
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                            <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="light">Light Mode</option>
                                <option value="dark">Dark Mode</option>
                                <option value="auto">Auto (System)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Items per Page</label>
                            <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="12">12 items</option>
                                <option value="24">24 items</option>
                                <option value="36">36 items</option>
                                <option value="48">48 items</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Show Item Images</label>
                                    <p class="text-xs text-gray-500">Display images for menu items in the grid</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Settings -->
            <div class="bg-white rounded-xl card-shadow">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-receipt mr-2 text-purple-600"></i>
                        Receipt Settings
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Format</label>
                            <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="80mm">80mm Thermal</option>
                                <option value="58mm">58mm Thermal</option>
                                <option value="a4">A4 Paper</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Auto Print</label>
                            <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="never">Never</option>
                                <option value="on_confirm">On Order Confirm</option>
                                <option value="on_payment">On Payment</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Header</label>
                            <textarea class="w-full p-3 border rounded-lg resize-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="3" placeholder="Enter custom header text for receipts...">SELAMAT DATANG DI RESTORAN KAMI
Jl. Example No. 123, Jakarta
Terima kasih atas kunjungan Anda</textarea>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Footer</label>
                            <textarea class="w-full p-3 border rounded-lg resize-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="2" placeholder="Enter custom footer text for receipts...">Terima kasih telah berbelanja
Selamat menikmati hidangan Anda!</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-xl card-shadow">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-bell mr-2 text-purple-600"></i>
                        Notification Settings
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Sound Notifications</label>
                                <p class="text-xs text-gray-500">Play sound when orders are received</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Desktop Notifications</label>
                                <p class="text-xs text-gray-500">Show browser notifications for important events</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Auto Refresh Orders</label>
                                <p class="text-xs text-gray-500">Automatically refresh order list every 30 seconds</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="bg-white rounded-xl card-shadow">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-cogs mr-2 text-purple-600"></i>
                        Advanced Settings
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                            <input type="number" step="0.01" value="11" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Charge (%)</label>
                            <input type="number" step="0.01" value="5" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                            <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="IDR">IDR - Indonesian Rupiah</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Time Zone</label>
                            <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                                <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="bg-white rounded-xl card-shadow">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-3 px-6 rounded-lg font-medium transition-all">
                            <i class="fas fa-save mr-2"></i>
                            Save All Settings
                        </button>
                        <button class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg font-medium transition-all">
                            <i class="fas fa-undo mr-2"></i>
                            Reset to Default
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Modal -->
<div id="account-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Account Information</h3>
                    <button id="close-account-modal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" value="{{ auth()->user()->name ?? 'Cashier User' }}" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" value="{{ auth()->user()->email ?? 'cashier@restaurant.com' }}" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <input type="text" value="Cashier" class="w-full p-3 border rounded-lg bg-gray-50" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Change Password</label>
                        <input type="password" placeholder="Enter new password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button id="save-account" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg font-medium transition-all">
                        Save Changes
                    </button>
                    <button id="cancel-account" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg font-medium transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Settings functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load saved settings
    loadSettings();
    
    // Save settings
    document.querySelector('button[class*="bg-purple-600"]').addEventListener('click', saveSettings);
    
    // Reset settings
    document.querySelector('button[class*="bg-gray-300"]').addEventListener('click', resetSettings);
    
    // Quick actions
    setupQuickActions();
});

function loadSettings() {
    // Load settings from localStorage
    const settings = JSON.parse(localStorage.getItem('cashier_settings') || '{}');
    
    // Apply settings to form elements
    Object.keys(settings).forEach(key => {
        const element = document.querySelector(`[name="${key}"], #${key}`);
        if (element) {
            if (element.type === 'checkbox') {
                element.checked = settings[key];
            } else {
                element.value = settings[key];
            }
        }
    });
}

function saveSettings() {
    const settings = {};
    
    // Collect all form values
    document.querySelectorAll('input, select, textarea').forEach(element => {
        if (element.name || element.id) {
            const key = element.name || element.id;
            if (element.type === 'checkbox') {
                settings[key] = element.checked;
            } else {
                settings[key] = element.value;
            }
        }
    });
    
    // Save to localStorage
    localStorage.setItem('cashier_settings', JSON.stringify(settings));
    
    showToast('Settings saved successfully!');
}

function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to default?')) {
        localStorage.removeItem('cashier_settings');
        location.reload();
    }
}

function setupQuickActions() {
    const quickActions = document.querySelectorAll('.bg-blue-500, .bg-green-500, .bg-orange-500, .bg-red-500');
    
    quickActions.forEach((action, index) => {
        action.addEventListener('click', function() {
            const actionName = this.textContent.trim();
            
            switch(index) {
                case 0: // Refresh Menu Items
                    refreshMenuItems();
                    break;
                case 1: // Export Transactions
                    exportTransactions();
                    break;
                case 2: // Test Print
                    testPrint();
                    break;
                case 3: // Clear Cache
                    clearCache();
                    break;
            }
        });
    });
}

function refreshMenuItems() {
    showToast('Refreshing menu items...');
    // Simulate API call
    setTimeout(() => {
        showToast('Menu items refreshed successfully!');
    }, 1500);
}

function exportTransactions() {
    showToast('Exporting today\'s transactions...');
    // Simulate export
    setTimeout(() => {
        // Create a fake download
        const link = document.createElement('a');
        link.href = 'data:text/csv;charset=utf-8,Order Number,Date,Amount\n001,2024-01-01,100000\n002,2024-01-01,75000';
        link.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        showToast('Transactions exported successfully!');
    }, 2000);
}

function testPrint() {
    showToast('Testing receipt printer...');
    // Simulate print test
    setTimeout(() => {
        const testContent = `
            <div style="font-family: monospace; width: 280px; margin: 20px auto; padding: 20px; border: 2px dashed #ccc;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h3>RECEIPT TEST</h3>
                    <p>Date: ${new Date().toLocaleString()}</p>
                </div>
                <div style="border-top: 1px solid #ccc; padding-top: 10px;">
                    <p>Test Item 1 x1 .......... Rp 10,000</p>
                    <p>Test Item 2 x2 .......... Rp 20,000</p>
                </div>
                <div style="border-top: 1px solid #ccc; padding-top: 10px; text-align: right;">
                    <p>Total: <strong>Rp 30,000</strong></p>
                </div>
                <div style="text-align: center; margin-top: 20px; font-size: 12px;">
                    <p>Printer Test Successful</p>
                </div>
            </div>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(testContent);
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
        
        showToast('Print test completed!');
    }, 1000);
}

function clearCache() {
    if (confirm('This will clear all cart data and cached items. Continue?')) {
        localStorage.removeItem('cashier_cart');
        localStorage.removeItem('cashier_cache');
        showToast('Cache cleared successfully!');
    }
}

// Account modal handlers
document.getElementById('close-account-modal')?.addEventListener('click', () => {
    document.getElementById('account-modal').classList.add('hidden');
});

document.getElementById('cancel-account')?.addEventListener('click', () => {
    document.getElementById('account-modal').classList.add('hidden');
});

document.getElementById('save-account')?.addEventListener('click', () => {
    showToast('Account information updated!');
    document.getElementById('account-modal').classList.add('hidden');
});
</script>
@endpush