{{-- resources/views/cashier/layout.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cashier System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'orange': {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        'primary': {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    },
                    boxShadow: {
                        'soft': '0 2px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 25px -5px rgba(0, 0, 0, 0.04)',
                        'hover': '0 20px 40px -10px rgba(0, 0, 0, 0.15), 0 4px 25px -5px rgba(0, 0, 0, 0.08)',
                        'glow': '0 0 20px rgba(249, 115, 22, 0.3)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-glow': 'pulseGlow 2s infinite',
                        'shake': 'shake 0.5s ease-in-out',
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cart-modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Responsive user profile section */
        .sidebar-minimized-center {
            margin-right: 1rem;
            transition: margin-right 0.3s ease;
        }

        .lg\:w-20 .sidebar-minimized-center {
            margin-right: 0;
        }

        .sidebar-tooltip {
            pointer-events: none;
        }

        .lg\:w-20 .sidebar-tooltip {
            pointer-events: auto;
        }

        /* Hide tooltip when sidebar is expanded */
        .lg\:w-72 .sidebar-tooltip {
            display: none !important;
        }

        /* Ensure proper alignment when minimized */
        .lg\:w-20 .bg-gradient-to-r {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Animation for tooltip */
        .group:hover .sidebar-tooltip {
            animation: tooltipSlideIn 0.2s ease-out;
        }

        @keyframes tooltipSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50%) translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
        }

        /* Responsive behavior for different screen sizes */
        @media (max-width: 1023px) {
            .sidebar-tooltip {
                display: none !important;
            }
        }

        /* Smooth transitions for all elements */
        .sidebar-text {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .lg\:w-20 .sidebar-text {
            opacity: 0;
            transform: translateX(-10px);
        }
        .profile-dropdown {
            transform: translateY(10px);
            transition: all 0.2s ease;
        }

        .nav-item:hover .profile-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Responsive sidebar transitions */
        aside {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        main {
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-text {
            transition: opacity 0.2s ease;
        }

        @media (min-width: 1024px) {
            .sidebar-nav-item {
                justify-content: center;
            }
            
            .lg\\:w-20 .sidebar-nav-item {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .lg\\:w-20 .sidebar-nav-item i {
                margin-right: 0;
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes bounceGentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(249, 115, 22, 0.3); }
            50% { box-shadow: 0 0 30px rgba(249, 115, 22, 0.5); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .gradient-bg { 
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);
        }
        
        .gradient-sidebar {
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
        }
        
        .floating-cart {
            position: fixed;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 40;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @media (max-width: 1024px) {
            .floating-cart {
                position: fixed;
                bottom: 6rem;
                right: 1rem;
                top: auto;
                transform: none;
            }
        }
        
        @media (max-width: 768px) {
            .floating-cart {
                bottom: 5rem;
            }
        }
        
        .item-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .item-card:hover::before {
            left: 100%;
        }
        
        .item-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        .category-tab {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .category-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.3s;
        }
        
        .category-tab:hover::before {
            left: 100%;
        }
        
        .category-tab.active {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
            transform: scale(1.05);
        }
        
        .sidebar-nav-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-nav-item::before {
            content: '';
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.3s;
        }
        
        .sidebar-nav-item:hover::before {
            left: 100%;
        }
        
        .sidebar-nav-item.active {
            background: linear-gradient(90deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.1));
            border-left: 4px solid #f97316;
            transform: translateX(5px);
        }
        
        .bottom-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Enhanced scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #f97316, #ea580c);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #ea580c, #c2410c);
        }

        /* Enhanced animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-in-up {
            animation: slideInUp 0.4s ease-out;
        }
        
        .search-container {
            position: relative;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .search-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(249, 115, 22, 0.05), transparent);
            transition: left 0.5s;
        }
        
        .search-container:focus-within::before {
            left: 100%;
        }
        
        .search-container:focus-within {
            box-shadow: 0 8px 35px -5px rgba(249, 115, 22, 0.2);
            transform: translateY(-2px);
        }
        
        /* Notification styles */
        .notification-toast {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Loading overlay enhancement */
        .loading-spinner {
            position: relative;
        }
        
        .loading-spinner::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top: 2px solid #f97316;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile enhancements */
        @media (max-width: 640px) {
            .item-card {
                transition: all 0.3s ease;
            }
            .item-card:hover {
                transform: translateY(-4px) scale(1.01);
            }
        }
        
        /* Status indicators */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        .status-online {
            background-color: #10b981;
        }
        
        .status-busy {
            background-color: #f59e0b;
        }
        
        .status-offline {
            background-color: #ef4444;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">
    <aside class="hidden lg:flex lg:flex-col lg:w-72 lg:fixed lg:inset-y-0 z-30">
        <div class="gradient-sidebar flex-1 flex flex-col min-h-0 shadow-2xl">
            <div class="flex items-center h-20 px-6 border-b border-gray-700/30 justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-cash-register text-white text-xl"></i>
                    </div>
                    <div class="sidebar-text">
                        <h1 class="text-xl font-bold text-white">Cashier Pro</h1>
                        <p class="text-gray-300 text-xs">Point of Sale System</p>
                    </div>
                </div>
                <button 
                    id="sidebar-toggle" 
                    onclick="toggleSidebar()" 
                    class="hidden lg:flex w-8 h-8 items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                    title="Toggle Sidebar"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
    
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('cashier.index') }}" class="sidebar-nav-item {{ request()->routeIs('cashier.index') ? 'active' : '' }} flex items-center px-4 py-4 text-gray-300 hover:text-white rounded-2xl hover:bg-white/10 transition-all group" title="Dashboard">
                    <i class="fas fa-home text-lg mr-4 group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium sidebar-text">Dashboard</span>
                    <div class="ml-auto sidebar-chevron">
                        <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                
                <a href="{{ route('cashier.cart') }}" class="sidebar-nav-item {{ request()->routeIs('cashier.cart') ? 'active' : '' }} flex items-center px-4 py-4 text-gray-300 hover:text-white rounded-2xl hover:bg-white/10 transition-all group" title="Cart">
                    <i class="fas fa-shopping-cart text-lg mr-4 group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium sidebar-text">Cart</span>
                    <div class="ml-auto sidebar-chevron">
                        <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                
                <a href="{{ route('cashier.orders') }}" class="sidebar-nav-item {{ request()->routeIs('cashier.orders') ? 'active' : '' }} flex items-center px-4 py-4 text-gray-300 hover:text-white rounded-2xl hover:bg-white/10 transition-all group" title="Orders">
                    <i class="fas fa-clipboard-list text-lg mr-4 group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium sidebar-text">Orders</span>
                    <div class="ml-auto">
                        <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full sidebar-text">{{ $pendingOrdersCount ?? 0 }}</span>
                    </div>
                </a>
                
                <a href="{{ route('cashier.settings') ?? '#' }}" class="sidebar-nav-item flex items-center px-4 py-4 text-gray-300 hover:text-white rounded-2xl hover:bg-white/10 transition-all group" title="Settings">
                    <i class="fas fa-cog text-lg mr-4 group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium sidebar-text">Settings</span>
                    <div class="ml-auto sidebar-chevron">
                        <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
            </nav>
    
            <div class="p-4 border-t border-gray-700/30">
                <div class="bg-gradient-to-r from-gray-800/50 to-gray-700/50 backdrop-blur-sm rounded-2xl p-4 relative group">
                    <div class="flex items-center text-white">
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full flex items-center justify-center sidebar-minimized-center">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                            <div class="absolute -bottom-1 -right-1 status-dot status-online"></div>
                        </div>
                        
                        <!-- User info - hidden when minimized -->
                        <div class="flex-1 ml-4 sidebar-text overflow-hidden">
                            <p class="font-semibold truncate">{{ auth()->user()->name ?? 'Cashier' }}</p>
                            <p class="text-xs text-gray-300">Active Now</p>
                        </div>
            
                        <!-- Logout button - hidden when minimized -->
                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="sidebar-text">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-white/10 transition-all" title="Logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
            
                    <!-- Tooltip for minimized state -->
                    <div class="absolute left-full ml-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 lg:block hidden sidebar-tooltip">
                        <div class="flex items-center space-x-3">
                            <div>
                                <p class="font-semibold">{{ auth()->user()->name ?? 'Cashier' }}</p>
                                <p class="text-xs text-gray-300">Active Now</p>
                            </div>
                            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-red-400 p-1 rounded transition-colors" title="Logout">
                                    <i class="fas fa-sign-out-alt text-sm"></i>
                                </button>
                            </form>
                        </div>
                        <!-- Arrow pointing to the profile -->
                        <div class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-800"></div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    
    <header class="lg:hidden gradient-bg text-white shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mr-3">
                        <i class="fas fa-cash-register text-orange-100 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Cashier Pro</h1>
                        <p class="text-orange-100 text-xs">POS System</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button class="glassmorphism p-3 rounded-2xl hover:bg-white/20 transition-all">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="relative">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <div class="absolute -bottom-1 -right-1 status-dot status-online"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="lg:pl-72 pb-20 lg:pb-0 transition-all duration-300">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="lg:hidden bottom-nav fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200/50">
        <div class="px-4">
            <div class="flex justify-around py-3">
                <a href="{{ route('cashier.index') }}" class="nav-item flex flex-col items-center space-y-1 p-3 rounded-2xl transition-all {{ request()->routeIs('cashier.index') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-500' }}">
                    <i class="fas fa-home text-xl"></i>
                    <span class="text-xs font-medium">Dashboard</span>
                </a>
                <a href="{{ route('cashier.cart') }}" class="nav-item flex flex-col items-center space-y-1 p-3 rounded-2xl transition-all {{ request()->routeIs('cashier.cart') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-500' }}">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="text-xs font-medium">Cart</span>
                </a>
                <a href="{{ route('cashier.orders') }}" class="nav-item flex flex-col items-center space-y-1 p-3 rounded-2xl transition-all relative {{ request()->routeIs('cashier.orders') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-500' }}">
                    <i class="fas fa-clipboard-list text-xl"></i>
                    <span class="text-xs font-medium">Orders</span>
                    @if(($pendingOrdersCount ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[1rem] h-4 flex items-center justify-center animate-bounce-gentle">{{ $pendingOrdersCount ?? 0 }}</span>
                    @endif
                </a>
                <div class="nav-item flex flex-col items-center space-y-1 p-3 rounded-2xl transition-all text-gray-600 hover:text-orange-500 relative">
                    <i class="fas fa-user text-xl"></i>
                    <span class="text-xs font-medium">Profile</span>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 profile-dropdown">
                        <div class="p-3 border-b border-gray-100">
                            <p class="font-semibold text-gray-800 text-sm">{{ auth()->user()->name ?? 'Cashier' }}</p>
                            <p class="text-xs text-gray-500">Active Now</p>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('cashier.settings') ?? '#' }}" class="flex items-center px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                                <i class="fas fa-cog mr-3 text-gray-400"></i>
                                <span class="text-sm">Settings</span>
                            </a>
                            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-3 py-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    <span class="text-sm">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.0/axios.min.js"></script>
    <script>
        // Setup CSRF token for axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Enhanced toast notifications
        window.showToast = function(message, type = 'success') {
            const toast = document.createElement('div');
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle';
            const bgColor = type === 'success' ? 'from-emerald-500 to-emerald-600' : 
                           type === 'error' ? 'from-red-500 to-red-600' : 'from-blue-500 to-blue-600';
            
            toast.className = `notification-toast fixed top-4 right-4 bg-gradient-to-r ${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl z-50 transform transition-all duration-300 animate-slide-up max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fas fa-${icon} text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm">${message}</p>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-2">
                            <div class="bg-white rounded-full h-1 transition-all duration-3000 ease-linear" style="width: 0%"></div>
                        </div>
                    </div>
                    <button class="ml-3 text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animate progress bar
            setTimeout(() => {
                const progressBar = toast.querySelector('.bg-white');
                if (progressBar) progressBar.style.width = '100%';
            }, 100);
            
            // Auto remove after 4 seconds
            const removeToast = () => {
                toast.style.transform = 'translateX(100%) scale(0.9)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            };
            
            setTimeout(removeToast, 4000);
            
            // Click to dismiss
            toast.querySelector('button').addEventListener('click', removeToast);
        };

        window.formatCurrency = function(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        };

        // Enhanced loading overlay
        window.showLoading = function(message = 'Loading...') {
            const loading = document.createElement('div');
            loading.id = 'loading-overlay';
            loading.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center';
            loading.innerHTML = `
                <div class="bg-white rounded-3xl p-8 shadow-2xl max-w-sm w-full mx-4">
                    <div class="flex flex-col items-center">
                        <div class="loading-spinner w-12 h-12 border-4 border-orange-200 border-t-orange-500 rounded-full animate-spin mb-6"></div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Please Wait</h3>
                        <p class="text-gray-600 text-center">${message}</p>
                    </div>
                </div>
            `;
            document.body.appendChild(loading);
        };

        window.hideLoading = function() {
            const loading = document.getElementById('loading-overlay');
            if (loading) {
                loading.style.opacity = '0';
                setTimeout(() => loading.remove(), 200);
            }
        };

        // Enhanced notification system
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        window.showNotification = function(title, message, options = {}) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body: message,
                    icon: '/favicon.ico',
                    badge: '/favicon.ico',
                    tag: 'cashier-notification',
                    requireInteraction: false,
                    ...options
                });
                
                // Auto close after 5 seconds
                setTimeout(() => notification.close(), 5000);
                
                return notification;
            }
        };
        
        // Add some interactive enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to interactive elements
            const interactiveElements = document.querySelectorAll('button, .item-card, .category-tab');
            interactiveElements.forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.transform = this.style.transform + ' scale(1.02)';
                });
                el.addEventListener('mouseleave', function() {
                    this.style.transform = this.style.transform.replace(' scale(1.02)', '');
                });
            });
        });
        // Sidebar toggle functionality
        let sidebarMinimized = false;

        window.toggleSidebar = function() {
            const sidebar = document.querySelector('aside');
            const main = document.querySelector('main');
            const toggleBtn = document.querySelector('#sidebar-toggle');
            
            sidebarMinimized = !sidebarMinimized;
            
            if (sidebarMinimized) {
                sidebar.classList.remove('lg:w-72');
                sidebar.classList.add('lg:w-20');
                main.classList.remove('lg:pl-72');
                main.classList.add('lg:pl-20');
                
                // Hide text and show only icons
                document.querySelectorAll('.sidebar-text').forEach(el => {
                    el.classList.add('lg:hidden');
                });
                document.querySelectorAll('.sidebar-chevron').forEach(el => {
                    el.classList.add('lg:hidden');
                });
                
                toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                document.querySelectorAll('.sidebar-tooltip').forEach(el => {
                    el.classList.remove('lg:hidden');
                });
            } else {
                sidebar.classList.remove('lg:w-20');
                sidebar.classList.add('lg:w-72');
                main.classList.remove('lg:pl-20');
                main.classList.add('lg:pl-72');
                
                // Show text again
                document.querySelectorAll('.sidebar-text').forEach(el => {
                    el.classList.remove('lg:hidden');
                });
                document.querySelectorAll('.sidebar-chevron').forEach(el => {
                    el.classList.remove('lg:hidden');
                });
                
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                document.querySelectorAll('.sidebar-tooltip').forEach(el => {
                    el.classList.add('lg:hidden');
                });
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarMinimized', sidebarMinimized);
        };

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedState = localStorage.getItem('sidebarMinimized');
            if (savedState === 'true') {
                toggleSidebar();
            }
        });

        // Handle mobile profile dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.querySelector('.nav-item:last-child');
            const profileDropdown = document.querySelector('.profile-dropdown');
            
            if (profileButton && profileDropdown) {
                profileButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    profileDropdown.classList.toggle('opacity-0');
                    profileDropdown.classList.toggle('invisible');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileButton.contains(e.target)) {
                        profileDropdown.classList.add('opacity-0');
                        profileDropdown.classList.add('invisible');
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>