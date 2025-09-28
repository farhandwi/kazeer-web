{{-- resources/views/cashier/index.blade.php --}}

@extends('cashier.layout')

@section('title', 'Cashier Pro - Order Management')

@section('content')
<div class="min-h-screen">
    <!-- Hero Section with Search -->
    <div class="bg-gradient-to-r from-orange-50 via-white to-orange-50 lg:ml-0 border-b border-orange-100/50">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Welcome Message -->
                <div class="text-center mb-8 animate-fade-in">
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-3">
                        Welcome to <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600">Cashier Pro</span>
                    </h1>
                    <p class="text-gray-600 text-lg">Start taking orders by selecting a table and adding items to cart</p>
                </div>

                <!-- Enhanced Search Bar -->
                <div class="search-container mb-8 transition-all duration-300">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                            <div class="text-gray-400 transition-colors">
                                <i class="fas fa-search text-xl"></i>
                            </div>
                        </div>
                        <input type="text" 
                               id="search-input" 
                               placeholder="Search for delicious food and beverages..." 
                               class="w-full pl-16 pr-16 py-5 text-lg border-0 bg-transparent focus:outline-none transition-all">
                        <button id="clear-search" class="absolute inset-y-0 right-0 pr-6 flex items-center text-gray-400 hover:text-orange-500 hidden transition-all">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                        <!-- Search suggestions dropdown -->
                        <div id="search-suggestions" class="absolute top-full left-0 right-0 bg-white rounded-2xl shadow-2xl mt-2 hidden z-10 max-h-64 overflow-y-auto">
                            <div id="suggestions-content" class="p-4"></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-utensils text-white"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ $categories->sum(function($cat) { return $cat->menuItems->count(); }) }}</p>
                                <p class="text-sm text-gray-600">Total Items</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-chair text-white"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ $tables->count() }}</p>
                                <p class="text-sm text-gray-600">Available Tables</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-tags text-white"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ $categories->count() }}</p>
                                <p class="text-sm text-gray-600">Categories</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-2xl p-4 shadow-soft hover:shadow-hover transition-all">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-percent text-white"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ $categories->flatMap->menuItems->where('discount_percentage', '>', 0)->count() }}</p>
                                <p class="text-sm text-gray-600">On Sale</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Categories Tabs -->
        <div class="mb-8">
            <div class="bg-white rounded-3xl shadow-soft p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-th-large text-white"></i>
                        </div>
                        Menu Categories
                    </h2>
                    <div class="hidden lg:flex items-center space-x-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            Available
                        </div>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                            On Sale
                        </div>
                    </div>
                </div>
                
                <!-- Category Tabs with enhanced styling -->
                <div class="flex flex-wrap gap-3">
                    <button class="category-tab active px-8 py-4 rounded-full text-sm font-semibold transition-all hover:scale-105 transform shadow-lg" data-category-id="all">
                        <i class="fas fa-utensils mr-3"></i>
                        All Menu
                        <span class="ml-2 bg-white/20 px-2 py-1 rounded-full text-xs">{{ $categories->flatMap->menuItems->count() }}</span>
                    </button>
                    @foreach($categories as $category)
                    <button class="category-tab px-8 py-4 rounded-full text-sm font-semibold bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700 hover:from-orange-50 hover:to-orange-100 hover:text-orange-600 transition-all hover:scale-105 transform shadow-lg border border-gray-200 hover:border-orange-200" data-category-id="{{ $category->id }}">
                        <i class="fas fa-tag mr-3"></i>
                        {{ $category->name }}
                        <span class="ml-2 bg-gray-200 px-2 py-1 rounded-full text-xs">{{ $category->menuItems->count() }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Current Table Info -->
        <div id="current-table-info" class="mb-8 hidden">
            <div class="bg-gradient-to-r from-orange-50 via-orange-100 to-orange-50 rounded-3xl shadow-soft p-6 border-2 border-orange-200/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                            <i class="fas fa-chair text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-orange-800">Currently Serving</h3>
                            <p class="text-orange-600 text-lg">Table <span id="current-table-number" class="font-semibold">-</span></p>
                            <p class="text-orange-500 text-sm">Ready to take orders</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button id="clear-table-btn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all text-sm">
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </button>
                        <button id="change-table-btn" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-2xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            Change Table
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Sorting -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-soft p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Sort by:</label>
                            <select id="sort-options" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                                <option value="name">Name (A-Z)</option>
                                <option value="price-low">Price (Low to High)</option>
                                <option value="price-high">Price (High to Low)</option>
                                <option value="featured">Featured First</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Filter:</label>
                            <div class="flex space-x-2">
                                <button id="filter-all" class="filter-btn active px-4 py-2 rounded-xl text-sm font-medium transition-all">All</button>
                                <button id="filter-discount" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-all">On Sale</button>
                                <button id="filter-featured" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-all">Featured</button>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        Showing <span id="items-count">{{ $categories->flatMap->menuItems->count() }}</span> items
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items Grid  -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8" id="menu-items-grid">
            @foreach($categories as $category)
                @foreach($category->menuItems as $item)
                <div class="item-card bg-white rounded-3xl shadow-soft overflow-hidden hover:shadow-hover transition-all duration-500 transform hover:-translate-y-2 group" 
                    data-category="{{ $category->id }}" 
                    data-name="{{ strtolower($item->name) }}"
                    data-price="{{ $item->getCurrentPrice() }}"
                    data-featured="{{ $item->is_featured ? 'true' : 'false' }}"
                    data-discount="{{ $item->isDiscountActive() ? 'true' : 'false' }}">
                    
                    <!-- Image Section - TIDAK BERUBAH -->
                    <div class="relative overflow-hidden">
                        @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" 
                            alt="{{ $item->name }}" 
                            class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                        <div class="w-full h-56 bg-gradient-to-br from-orange-100 via-orange-200 to-orange-300 flex items-center justify-center group-hover:from-orange-200 group-hover:to-orange-400 transition-all duration-500">
                            <i class="fas fa-utensils text-5xl text-orange-500 group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                        @endif
                        
                        <!-- Badges dan overlay - TIDAK BERUBAH -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        @if($item->isDiscountActive())
                        <div class="absolute top-4 left-4 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-pulse-glow">
                            <i class="fas fa-percent mr-1"></i>
                            -{{ $item->discount_percentage }}% OFF
                        </div>
                        @endif
                        
                        @if($item->is_featured)
                        <div class="absolute top-4 right-4 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white p-3 rounded-full shadow-lg">
                            <i class="fas fa-star"></i>
                        </div>
                        @endif

                        <!-- Quick action buttons - TIDAK BERUBAH -->
                        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
                            <div class="flex space-x-2">
                                <button class="quick-add-btn w-12 h-12 bg-white/90 backdrop-blur-sm hover:bg-orange-500 text-gray-700 hover:text-white rounded-full shadow-lg flex items-center justify-center transform hover:scale-110 transition-all" 
                                        data-item-id="{{ $item->id }}"
                                        data-item-name="{{ $item->name }}"
                                        data-item-price="{{ $item->getCurrentPrice() }}"
                                        data-has-options="false">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="w-12 h-12 bg-white/90 backdrop-blur-sm hover:bg-blue-500 text-gray-700 hover:text-white rounded-full shadow-lg flex items-center justify-center transform hover:scale-110 transition-all">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>

                        @if($item->preparation_time)
                        <div class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs flex items-center opacity-0 group-hover:opacity-100 transition-all">
                            <i class="fas fa-clock mr-2"></i>
                            {{ $item->preparation_time }}m
                        </div>
                        @endif
                    </div>
                    
                    <!-- Content Section -->
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-bold text-gray-800 text-lg leading-tight group-hover:text-orange-600 transition-colors">{{ $item->name }}</h3>
                            @if($item->preparation_time)
                            <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $item->preparation_time }}m
                            </div>
                            @endif
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">{{ $item->description ?: 'Delicious menu item prepared with care and quality ingredients.' }}</p>
                        
                        <!-- Price Section - TIDAK BERUBAH -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="price-section">
                                @if($item->isDiscountActive())
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl font-bold text-orange-600">{{ $item->formatted_current_price }}</span>
                                    <span class="text-sm text-gray-500 line-through bg-gray-100 px-2 py-1 rounded">{{ $item->formatted_price }}</span>
                                </div>
                                @else
                                <span class="text-2xl font-bold text-orange-600">{{ $item->formatted_price }}</span>
                                @endif
                            </div>
                            
                            <!-- Rating stars (placeholder) - TIDAK BERUBAH -->
                            <div class="flex items-center space-x-1 text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-xs"></i>
                                @endfor
                                <span class="text-xs text-gray-500 ml-1">(4.8)</span>
                            </div>
                        </div>
                        
                        <!-- Allergens Info - TIDAK BERUBAH -->
                        @if($item->allergens && count($item->allergens) > 0)
                        <div class="mb-4">
                            <div class="text-xs text-amber-700 bg-amber-50 px-3 py-2 rounded-lg border border-amber-200 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span>Allergens: {{ $item->allergens_list }}</span>
                            </div>
                        </div>
                        @endif
                        
                        <!-- MODIFIKASI: Action Button dengan Container untuk Quantity Controls -->
                        <div class="action-container">
                            <!-- Add to Cart Button (default visible) -->
                            <button class="add-item-btn w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white py-4 px-4 rounded-2xl font-semibold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl group-hover:shadow-2xl" 
                                    data-item-id="{{ $item->id }}"
                                    data-item-name="{{ $item->name }}"
                                    data-item-price="{{ $item->getCurrentPrice() }}"
                                    data-has-options="{{ $item->has_options ? 'true' : 'false' }}">
                                <i class="fas fa-plus mr-2"></i>
                                Add to Cart
                            </button>
                            
                            <!-- Quantity Controls (initially hidden) -->
                            <div class="quantity-controls hidden justify-between items-center bg-gradient-to-r from-orange-50 to-orange-100 border-2 border-orange-300 rounded-2xl p-3">
                                <button class="quantity-btn decrease-btn bg-white hover:bg-orange-50 border border-orange-200 hover:border-orange-400 text-orange-600 rounded-xl w-10 h-10 flex items-center justify-center font-bold transition-all transform hover:scale-105 shadow-sm" data-item-id="{{ $item->id }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                
                                <div class="flex-1 text-center">
                                    <div class="quantity-display text-2xl font-bold text-orange-600 bg-white rounded-xl py-2 border border-orange-200">1</div>
                                    <div class="text-xs text-orange-500 mt-1 font-medium">In Cart</div>
                                </div>
                                
                                <button class="quantity-btn increase-btn bg-white hover:bg-orange-50 border border-orange-200 hover:border-orange-400 text-orange-600 rounded-xl w-10 h-10 flex items-center justify-center font-bold transition-all transform hover:scale-105 shadow-sm" data-item-id="{{ $item->id }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endforeach
        </div>

        <!-- No Results Message -->
        <div id="no-results" class="hidden text-center py-16">
            <div class="bg-white rounded-3xl shadow-soft p-12 max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-600 mb-3">No Items Found</h3>
                <p class="text-gray-500 mb-6">Try adjusting your search terms or browse different categories</p>
                <button id="clear-all-filters" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-2xl hover:from-orange-600 hover:to-orange-700 transition-all">
                    <i class="fas fa-refresh mr-2"></i>
                    Clear All Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-grid" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8">
            @for($i = 1; $i <= 8; $i++)
            <div class="bg-white rounded-3xl shadow-soft overflow-hidden animate-pulse">
                <div class="w-full h-56 bg-gray-200"></div>
                <div class="p-6">
                    <div class="h-4 bg-gray-200 rounded mb-3"></div>
                    <div class="h-3 bg-gray-200 rounded mb-4"></div>
                    <div class="h-6 bg-gray-200 rounded mb-4"></div>
                    <div class="h-10 bg-gray-200 rounded"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>

<!-- Table Selection Modal -->
<div id="table-selection-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-orange-500 to-orange-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-chair text-white text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Select Table</h2>
                <p class="text-gray-600 text-lg">Choose a table to start taking orders</p>
            </div>
            
            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($tables as $table)
                <button class="table-btn group p-8 bg-gradient-to-br from-gray-50 to-gray-100 hover:from-orange-50 hover:to-orange-100 rounded-3xl text-center transition-all transform hover:scale-105 hover:shadow-xl border-2 border-gray-200 hover:border-orange-200" 
                        data-table-id="{{ $table->id }}" 
                        data-table-number="{{ $table->table_number }}">
                    <div class="w-16 h-16 bg-gray-300 group-hover:bg-gradient-to-r group-hover:from-orange-400 group-hover:to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 transition-all shadow-lg">
                        <i class="fas fa-chair text-gray-600 group-hover:text-white transition-all text-2xl"></i>
                    </div>
                    <div class="font-bold text-gray-700 group-hover:text-orange-600 transition-all text-lg">
                        Table {{ $table->table_number }}
                    </div>
                    <div class="text-xs text-gray-500 group-hover:text-orange-500 transition-all mt-1">
                        {{ $table->capacity ?? 4 }} seats
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Responsive Floating Cart -->
<div class="floating-cart">
    <div id="cart-button" class="
        fixed bottom-4 right-4 
        sm:bottom-6 sm:right-6 
        lg:bottom-8 lg:right-8
        bg-gradient-to-r from-orange-500 to-orange-600 
        hover:from-orange-600 hover:to-orange-700 
        text-white 
        p-4 sm:p-5 lg:p-6 
        rounded-full 
        shadow-2xl 
        cursor-pointer 
        transition-all duration-300
        transform hover:scale-110 
        animate-pulse 
        hidden 
        relative 
        z-40
        group
        active:scale-95
        ">
        <!-- Cart Icon -->
        <i class="fas fa-shopping-cart text-lg sm:text-xl lg:text-2xl transition-transform group-hover:rotate-12"></i>
        
        <!-- Cart Count Badge -->
        <span id="cart-count" class="
            absolute -top-2 -right-2 
            sm:-top-3 sm:-right-3 
            bg-red-500 
            text-white 
            rounded-full 
            w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 
            flex items-center justify-center 
            text-xs sm:text-sm lg:text-base
            font-bold 
            animate-bounce 
            shadow-lg
            border-2 border-white
            transition-all duration-300
            group-hover:scale-110
            ">0</span>
        
        <!-- Ripple Effect -->
        <div class="absolute inset-0 bg-white/20 rounded-full scale-0 group-hover:scale-100 transition-transform duration-300 ease-out"></div>
        
        <!-- Glow Effect -->
        <div class="absolute inset-0 rounded-full bg-orange-400/30 blur-xl scale-0 group-hover:scale-150 transition-all duration-500 -z-10"></div>
    </div>
    
    <!-- Mini Cart Preview (Optional - shows on hover) -->
    <div id="cart-preview" class="
        fixed bottom-20 right-4 
        sm:bottom-24 sm:right-6 
        lg:bottom-28 lg:right-8
        bg-white 
        rounded-2xl 
        shadow-2xl 
        p-4 
        min-w-[280px] sm:min-w-[320px] 
        opacity-0 
        transform translate-y-4 scale-95
        pointer-events-none
        transition-all duration-300
        border border-gray-100
        z-30
        hidden
        ">
        <!-- Preview Header -->
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-bold text-gray-800 text-sm sm:text-base">Quick Preview</h4>
            <span id="preview-count" class="text-xs sm:text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">0 items</span>
        </div>
        
        <!-- Preview Items -->
        <div id="preview-items" class="space-y-2 max-h-40 overflow-y-auto">
            <!-- Items will be populated by JS -->
        </div>
        
        <!-- Preview Footer -->
        <div class="border-t pt-3 mt-3">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Total:</span>
                <span id="preview-total" class="font-bold text-orange-600">Rp 0</span>
            </div>
            <button onclick="cashier.showCartModal()" class="
                w-full 
                bg-gradient-to-r from-orange-500 to-orange-600 
                hover:from-orange-600 hover:to-orange-700 
                text-white 
                py-2 
                rounded-xl 
                text-sm font-semibold 
                transition-all duration-200
                transform hover:scale-105
                ">
                View Full Cart
            </button>
        </div>
    </div>
</div>

<!-- Item Options Modal -->
<div id="options-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h3 id="modal-item-name" class="text-2xl font-bold text-gray-800"></h3>
                    <button id="close-modal" onclick="cashier.restoreCurrentButton(); cashier.closeModal();" class="text-gray-400 hover:text-gray-600 p-3 hover:bg-gray-100 rounded-full transition-all">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="modal-options-content" class="mb-8"></div>
                
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-3xl p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button id="decrease-qty" class="w-12 h-12 bg-white hover:bg-orange-50 border-2 border-gray-200 hover:border-orange-300 rounded-2xl flex items-center justify-center transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-minus text-gray-600"></i>
                            </button>
                            <div class="text-center">
                                <span id="item-quantity" class="font-bold text-3xl text-gray-800">1</span>
                                <div class="text-xs text-gray-500">Quantity</div>
                            </div>
                            <button id="increase-qty" class="w-12 h-12 bg-white hover:bg-orange-50 border-2 border-gray-200 hover:border-orange-300 rounded-2xl flex items-center justify-center transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-plus text-gray-600"></i>
                            </button>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-sm text-gray-600 mb-1">Total Price:</div>
                            <div id="modal-total-price" class="text-3xl font-bold text-orange-600"></div>
                        </div>
                    </div>
                </div>
                
                <textarea id="special-instructions" 
                         placeholder="Add special instructions or notes (optional)..." 
                         class="w-full p-4 border-2 border-gray-200 rounded-2xl resize-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all mb-6" 
                         rows="3"></textarea>
                
                <button id="add-to-cart-final" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white py-5 px-6 rounded-2xl font-semibold text-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Add to Cart
                    <div class="absolute inset-0 bg-white/10 transform scale-x-0 hover:scale-x-100 transition-transform origin-left rounded-2xl"></div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Cart Modal with Customer Info -->
<div id="cart-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-end lg:items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-t-3xl lg:rounded-3xl w-full max-w-2xl max-h-[95vh] overflow-y-auto shadow-2xl animate-slide-up">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Shopping Cart</h3>
                        <p class="text-gray-600 text-sm">Review your order and customer details</p>
                    </div>
                    <button id="close-cart-modal" class="text-gray-400 hover:text-gray-600 p-3 hover:bg-gray-100 rounded-full transition-all">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Customer Information Section -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl p-6 mb-8 border border-blue-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mr-3">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Customer Information</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Customer Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                id="customer-name" 
                                placeholder="Enter customer name..." 
                                class="w-full p-3 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                                required>
                            <div class="text-xs text-gray-500 mt-1">Required for order processing</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Phone Number <span class="text-gray-400">(Optional)</span>
                            </label>
                            <input type="tel" 
                                id="customer-phone" 
                                placeholder="08123456789" 
                                class="w-full p-3 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all">
                            <div class="text-xs text-gray-500 mt-1">For delivery updates</div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="payment-method-option flex items-center justify-center p-4 border-2 border-blue-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition-all group">
                                <input type="radio" name="payment-method" value="cash" id="payment-cash" class="hidden" checked>
                                <div class="text-center">
                                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">💵</div>
                                    <div class="font-semibold text-gray-700 group-hover:text-blue-600">Cash</div>
                                </div>
                            </label>
                            
                            <label class="payment-method-option flex items-center justify-center p-4 border-2 border-blue-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition-all group">
                                <input type="radio" name="payment-method" value="card" id="payment-card" class="hidden">
                                <div class="text-center">
                                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">💳</div>
                                    <div class="font-semibold text-gray-700 group-hover:text-blue-600">Card</div>
                                </div>
                            </label>
                            
                            <label class="payment-method-option flex items-center justify-center p-4 border-2 border-blue-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition-all group">
                                <input type="radio" name="payment-method" value="digital_wallet" id="payment-digital" class="hidden">
                                <div class="text-center">
                                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">📱</div>
                                    <div class="font-semibold text-gray-700 group-hover:text-blue-600">E-Wallet</div>
                                </div>
                            </label>
                            
                            <label class="payment-method-option flex items-center justify-center p-4 border-2 border-blue-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition-all group">
                                <input type="radio" name="payment-method" value="transfer" id="payment-transfer" class="hidden">
                                <div class="text-center">
                                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">🏦</div>
                                    <div class="font-semibold text-gray-700 group-hover:text-blue-600">Transfer</div>
                                </div>
                            </label>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">Select how the customer will pay for this order</div>
                    </div>

                    <!-- Quick Actions for Customer Info -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="document.getElementById('customer-name').value='Walk-in Customer'" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs transition-all">
                            <i class="fas fa-walking mr-1"></i>Walk-in
                        </button>
                        <button onclick="document.getElementById('customer-name').value='Dine-in Guest'" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs transition-all">
                            <i class="fas fa-utensils mr-1"></i>Dine-in
                        </button>
                        <button onclick="document.getElementById('customer-name').value='Takeaway Order'" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs transition-all">
                            <i class="fas fa-shopping-bag mr-1"></i>Takeaway
                        </button>
                    </div>
                </div>


                <!-- Coupon & Discount Section -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-3xl p-6 mb-8 border border-green-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mr-3">
                            <i class="fas fa-tags text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Promotions & Discounts</h4>
                    </div>
                    
                    <!-- Coupon Section -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-ticket-alt mr-1 text-blue-500"></i>
                            Coupon Code
                        </label>
                        <div class="flex space-x-3">
                            <input type="text" 
                                id="coupon-code" 
                                placeholder="Enter coupon code..." 
                                class="flex-1 p-3 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all">
                            <button id="apply-coupon" 
                                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105">
                                <i class="fas fa-check mr-1"></i>Apply
                            </button>
                        </div>
                        
                        <!-- Coupon Status -->
                        <div id="coupon-status" class="mt-3 hidden">
                            <div id="coupon-success" class="hidden p-3 bg-blue-100 border border-blue-300 rounded-xl text-blue-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span id="coupon-description">Coupon applied successfully!</span>
                                    </div>
                                    <button id="remove-coupon" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="coupon-error" class="hidden p-3 bg-red-100 border border-red-300 rounded-xl text-red-800">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span id="coupon-error-message">Invalid coupon code</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Discount Section -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-percentage mr-1 text-green-500"></i>
                            Discount Code
                        </label>
                        <div class="flex space-x-3">
                            <input type="text" 
                                id="discount-code" 
                                placeholder="Enter discount code..." 
                                class="flex-1 p-3 border-2 border-green-200 rounded-xl focus:ring-2 focus:ring-green-400 focus:border-transparent transition-all">
                            <button id="apply-discount" 
                                    class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105">
                                <i class="fas fa-check mr-1"></i>Apply
                            </button>
                        </div>
                        
                        <!-- Discount Status -->
                        <div id="discount-status" class="mt-3 hidden">
                            <div id="discount-success" class="hidden p-3 bg-green-100 border border-green-300 rounded-xl text-green-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span id="discount-description">Discount applied successfully!</span>
                                    </div>
                                    <button id="remove-discount" class="text-green-600 hover:text-green-800 font-semibold">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="discount-error" class="hidden p-3 bg-red-100 border border-red-300 rounded-xl text-red-800">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span id="discount-error-message">Invalid discount code</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-500 mt-4 bg-gray-50 p-3 rounded-xl">
                        <i class="fas fa-info-circle mr-1"></i>
                        You can apply both coupon and discount codes for maximum savings!
                    </div>
                </div>

                
                <!-- Cart Items -->
                <div id="cart-items" class="space-y-4 mb-8 max-h-96 overflow-y-auto"></div>
                
                <!-- Order Summary -->
                <div class="border-t border-gray-200 pt-8">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-3xl p-6 mb-6">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calculator mr-2 text-orange-500"></i>
                            Order Summary
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <span class="font-medium">Subtotal:</span>
                                <span id="cart-subtotal" class="font-semibold">Rp 0</span>
                            </div>
                            <!-- Coupon Row -->
                            <div id="coupon-row" class="flex justify-between text-blue-600 hidden">
                                <span class="font-medium">Coupon Discount:</span>
                                <span id="cart-coupon" class="font-semibold">- Rp 0</span>
                            </div>
                            <!-- Discount Row -->
                            <div id="discount-row" class="flex justify-between text-green-600 hidden">
                                <span class="font-medium">Additional Discount:</span>
                                <span id="cart-discount" class="font-semibold">- Rp 0</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span class="font-medium">Tax (11%):</span>
                                <span id="cart-tax" class="font-semibold">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span class="font-medium">Service (5%):</span>
                                <span id="cart-service" class="font-semibold">Rp 0</span>
                            </div>
                            <div class="border-t border-gray-300 pt-3">
                                <div class="flex justify-between font-bold text-2xl">
                                    <span class="text-gray-800">Total:</span>
                                    <span id="cart-total" class="text-orange-600">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-4">
                        <button onclick="cashier.closeCartModal()" class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Continue Shopping
                        </button>
                        <button id="process-order" class="flex-2 px-8 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl font-semibold text-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-check mr-2"></i>
                            Process Order
                        </button>
                    </div>

                    <!-- Order Info -->
                    <div class="mt-6 text-center">
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

<!-- Customer Information Validation Modal (Template) -->
<div id="customer-info-modal-template" class="hidden">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl animate-slide-up">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Missing Customer Information</h3>
                        <p class="text-gray-600">Please provide customer details before processing the order</p>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Customer Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="modal-customer-name" 
                                   placeholder="Enter customer name..." 
                                   class="w-full p-4 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-400 focus:border-transparent transition-all"
                                   required>
                            <div class="text-xs text-gray-500 mt-2">Required for order processing</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Phone Number <span class="text-gray-400">(Optional)</span>
                            </label>
                            <input type="tel" 
                                   id="modal-customer-phone" 
                                   placeholder="e.g., 08123456789 or +6281234567890" 
                                   class="w-full p-4 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-400 focus:border-transparent transition-all">
                            <div class="text-xs text-gray-500 mt-2">For delivery updates and contact</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select id="modal-payment-method" 
                                    class="w-full p-4 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-400 focus:border-transparent transition-all bg-white">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Credit/Debit Card</option>
                                <option value="digital_wallet">📱 Digital Wallet (GoPay, OVO, DANA)</option>
                                <option value="transfer">🏦 Bank Transfer</option>
                            </select>
                            <div class="text-xs text-gray-500 mt-2">How the customer will pay</div>
                        </div>

                        <!-- Quick Fill Options -->
                        <div class="bg-gray-50 rounded-2xl p-4">
                            <div class="text-sm font-medium text-gray-700 mb-3">Quick Fill:</div>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="document.getElementById('modal-customer-name').value='Walk-in Customer'" 
                                        class="px-3 py-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-sm transition-all">
                                    <i class="fas fa-walking mr-1"></i>Walk-in Customer
                                </button>
                                <button onclick="document.getElementById('modal-customer-name').value='Dine-in Guest'" 
                                        class="px-3 py-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-sm transition-all">
                                    <i class="fas fa-utensils mr-1"></i>Dine-in Guest
                                </button>
                                <button onclick="document.getElementById('modal-customer-name').value='Takeaway Order'" 
                                        class="px-3 py-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-sm transition-all">
                                    <i class="fas fa-shopping-bag mr-1"></i>Takeaway Order
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-4 mt-8">
                        <button onclick="cashier.closeCustomerInfoModal()" 
                                class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all">
                            Cancel
                        </button>
                        <button onclick="cashier.saveCustomerInfoAndProcess()" 
                                class="flex-1 px-6 py-4 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-2xl font-semibold transition-all transform hover:scale-105">
                            <i class="fas fa-check mr-2"></i>
                            Continue
                        </button>
                    </div>
                    
                    <div class="text-center mt-6">
                        <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-xl">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Customer information is used for order management only
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Responsive Floating Cart */
    .floating-cart {
        position: fixed;
        z-index: 40;
        pointer-events: none; /* Allow clicks to pass through container */
    }

    .floating-cart > * {
        pointer-events: auto; /* Re-enable clicks on children */
    }

    /* Enhanced cart button animations */
    #cart-button {
        animation: float 3s ease-in-out infinite, pulseGlow 2s infinite;
    }

    #cart-button:hover {
        animation: none; /* Stop floating on hover */
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-6px);
        }
    }

    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 
                0 10px 25px -5px rgba(249, 115, 22, 0.2),
                0 0 0 0 rgba(249, 115, 22, 0.4);
        }
        50% {
            box-shadow: 
                0 20px 40px -5px rgba(249, 115, 22, 0.3),
                0 0 0 10px rgba(249, 115, 22, 0);
        }
    }

    /* Cart count badge animation */
    #cart-count {
        animation: heartbeat 1.5s ease-in-out infinite;
    }

    @keyframes heartbeat {
        0%, 100% {
            transform: scale(1);
        }
        25% {
            transform: scale(1.1);
        }
        50% {
            transform: scale(1);
        }
        75% {
            transform: scale(1.05);
        }
    }

    /* Cart Preview Styling */
    #cart-preview {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }

    #cart-preview.show {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .floating-cart #cart-button {
            bottom: 1rem;
            right: 1rem;
        }
        
        .floating-cart #cart-preview {
            bottom: 5rem;
            right: 1rem;
            min-width: 260px;
            max-width: calc(100vw - 2rem);
        }
    }

    @media (max-width: 480px) {
        .floating-cart #cart-preview {
            min-width: 240px;
            padding: 0.75rem;
        }
        
        .floating-cart #cart-button {
            padding: 0.75rem;
        }
    }

    /* Custom scrollbar for preview */
    #preview-items::-webkit-scrollbar {
        width: 4px;
    }

    #preview-items::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    #preview-items::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    /* Loading state */
    .cart-loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .cart-loading::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.8);
        border-radius: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }

    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    .animate-pulse-glow {
        animation: pulseGlow 2s infinite;
    }

    .animate-bounce-gentle {
        animation: bounceGentle 0.6s ease-out;
    }

    .animate-slide-in-right {
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(2rem);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(249, 115, 22, 0);
        }
    }

    @keyframes bounceGentle {
        0%, 20%, 53%, 80%, 100% {
            transform: translate3d(0,0,0);
        }
        40%, 43% {
            transform: translate3d(0, -8px, 0);
        }
        70% {
            transform: translate3d(0, -4px, 0);
        }
        90% {
            transform: translate3d(0, -2px, 0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .shadow-soft {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .shadow-hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .category-tab.active {
        background: linear-gradient(to right, #f97316, #ea580c);
        color: white;
        transform: scale(1.05);
    }

    .filter-btn.active {
        background: linear-gradient(to right, #f97316, #ea580c);
        color: white;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Phone number input formatting */
    input[type="tel"]:focus {
        background: linear-gradient(45deg, #f8fafc, #f1f5f9);
    }

    /* Customer info section styling */
    .customer-info-highlight {
        background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
        border: 2px solid #bfdbfe;
    }

    /* Validation states */
    .input-error {
        border-color: #ef4444 !important;
        background-color: #fef2f2;
    }

    .input-success {
        border-color: #10b981 !important;
        background-color: #f0fdf4;
    }

    /* Loading states */
    .loading-overlay {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
    }

    /* Custom scrollbar for cart items */
    #cart-items::-webkit-scrollbar {
        width: 6px;
    }

    #cart-items::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    #cart-items::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    #cart-items::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Quantity Controls Styling */
    .quantity-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-width: 120px;
    }

    .quantity-btn {
        width: 2.5rem;
        height: 2.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        background: white;
        color: #6b7280;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        cursor: pointer;
    }

    .quantity-btn:hover {
        border-color: #f97316;
        color: #f97316;
        background: #fff7ed;
        transform: scale(1.05);
    }

    .quantity-display {
        font-weight: bold;
        font-size: 1.125rem;
        color: #1f2937;
        min-width: 2rem;
        text-align: center;
        background: #fff7ed;
        border: 2px solid #fed7aa;
        border-radius: 0.75rem;
        padding: 0.5rem;
    }

    .item-card .add-item-btn.hidden {
        display: none;
    }

    .item-card .quantity-controls {
        animation: fadeInScale 0.3s ease-out;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Delete confirmation modal styles */
    .delete-confirmation-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 60;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .delete-confirmation-content {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        max-width: 24rem;
        width: 100%;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.3s ease-out;
    }

    .delete-confirmation-icon {
        width: 4rem;
        height: 4rem;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: white;
        font-size: 1.5rem;
    }
    /* Action Container Styling */
    .action-container {
        position: relative;
    }

    .action-container .add-item-btn {
        position: relative;
        overflow: hidden;
    }

    .action-container .quantity-controls {
        display: none;
        animation: fadeInScale 0.3s ease-out;
    }

    .action-container .quantity-controls.flex {
        display: flex;
    }

    .action-container .add-item-btn.hidden {
        display: none;
    }

    /* Enhanced quantity controls for menu cards */
    .item-card .quantity-controls {
        background: linear-gradient(135deg, #fff7ed, #fed7aa);
        border: 2px solid #fb923c;
        box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.1);
    }

    .item-card .quantity-controls .quantity-display {
        background: white;
        border: 1px solid #fb923c;
        box-shadow: inset 0 1px 3px rgba(249, 115, 22, 0.1);
    }

    /* Cart quantity controls styling */
    .cart-quantity-decrease,
    .cart-quantity-increase {
        border: 2px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .cart-quantity-decrease:hover,
    .cart-quantity-increase:hover {
        border-color: #f97316;
        color: #f97316;
        background: #fff7ed;
        transform: scale(1.05);
    }
</style>
@endpush

@push('scripts')
<script>
    // Enhanced phone number formatting
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        
        phoneInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Format Indonesian phone numbers
                if (value.startsWith('62')) {
                    value = '+' + value;
                } else if (value.startsWith('8') && value.length >= 10) {
                    value = '0' + value;
                } else if (!value.startsWith('0') && value.length > 0) {
                    // Auto-add 0 for local numbers
                    if (value.length >= 9) {
                        value = '0' + value;
                    }
                }
                
                e.target.value = value;
                
                // Visual feedback for valid numbers
                if (value.length >= 10) {
                    e.target.classList.remove('input-error');
                    e.target.classList.add('input-success');
                } else if (value.length > 0) {
                    e.target.classList.add('input-error');
                    e.target.classList.remove('input-success');
                } else {
                    e.target.classList.remove('input-error', 'input-success');
                }
            });

            // Format on paste
            input.addEventListener('paste', function(e) {
                setTimeout(() => {
                    e.target.dispatchEvent(new Event('input'));
                }, 10);
            });
        });

        // Customer name validation
        const nameInputs = document.querySelectorAll('#customer-name, #modal-customer-name');
        nameInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                const value = e.target.value.trim();
                
                if (value.length >= 2) {
                    e.target.classList.remove('input-error');
                    e.target.classList.add('input-success');
                } else if (value.length > 0) {
                    e.target.classList.add('input-error');
                    e.target.classList.remove('input-success');
                } else {
                    e.target.classList.remove('input-error', 'input-success');
                }
            });
        });
    });

    // Utility functions for global use
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    };

    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-2xl shadow-lg text-white transform transition-all duration-300 translate-x-full`;
        
        switch(type) {
            case 'success':
                toast.classList.add('bg-green-500');
                break;
            case 'error':
                toast.classList.add('bg-red-500');
                break;
            case 'info':
                toast.classList.add('bg-blue-500');
                break;
            default:
                toast.classList.add('bg-gray-500');
        }
        
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'info' ? 'info-circle' : 'check-circle'} mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    window.showNotification = function(title, message) {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                new Notification(title, {
                    body: message,
                    icon: '/favicon.ico'
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification(title, {
                            body: message,
                            icon: '/favicon.ico'
                        });
                    }
                });
            }
        }
    };

    window.showLoading = function(message = 'Loading...') {
        const loading = document.createElement('div');
        loading.id = 'loading-overlay';
        loading.className = 'fixed inset-0 z-50 loading-overlay flex items-center justify-center';
        loading.innerHTML = `
            <div class="bg-white rounded-3xl p-8 shadow-2xl text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-orange-500 border-t-transparent mx-auto mb-4"></div>
                <p class="text-gray-700 font-medium">${message}</p>
            </div>
        `;
        document.body.appendChild(loading);
    };

    window.hideLoading = function() {
        const loading = document.getElementById('loading-overlay');
        if (loading) {
            loading.remove();
        }
    };
</script>
<script src="{{ asset('js/cashier.js') }}"></script>
@endpush