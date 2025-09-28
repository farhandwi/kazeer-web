class ModernCashierSystem {
    constructor() {
        this.cart = [];
        this.selectedTable = null;
        this.currentItem = null;
        this.searchTimeout = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.showTableSelectionModal();
        this.updateCartDisplay();
        this.setupSearch();
    }

    setupEventListeners() {
        // Table selection
        document.querySelectorAll('.table-select-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.selectTable(e));
        });

        document.getElementById('change-table-btn')?.addEventListener('click', () => {
            this.showTableSelectionModal();
        });

        // Category tabs
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', (e) => this.filterByCategory(e));
        });

        // Add item buttons
        document.querySelectorAll('.add-item-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.addItemToCart(e));
        });

        // Quick add buttons
        document.querySelectorAll('.quick-add-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.addItemToCart(e);
            });
        });

        // Modal controls
        document.getElementById('close-modal')?.addEventListener('click', () => this.closeModal());
        document.getElementById('close-cart-modal')?.addEventListener('click', () => this.closeCartModal());
        document.getElementById('increase-qty')?.addEventListener('click', () => this.changeQuantity(1));
        document.getElementById('decrease-qty')?.addEventListener('click', () => this.changeQuantity(-1));
        document.getElementById('add-to-cart-final')?.addEventListener('click', () => this.addToCartFinal());
        document.getElementById('cart-button')?.addEventListener('click', () => this.showCartModal());
        document.getElementById('process-order')?.addEventListener('click', () => this.processOrder());

        // Search controls
        document.getElementById('clear-search')?.addEventListener('click', () => this.clearSearch());
        document.getElementById('reset-search')?.addEventListener('click', () => this.resetSearch());

        // Close modals when clicking outside
        document.getElementById('options-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'options-modal') this.closeModal();
        });
        
        document.getElementById('cart-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'cart-modal') this.closeCartModal();
        });

        // Add hover effects to item cards
        document.querySelectorAll('.item-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.classList.add('group');
            });
        });
    }

    setupSearch() {
        const searchInput = document.getElementById('search-input');
        const clearButton = document.getElementById('clear-search');

        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            // Show/hide clear button
            if (query.length > 0) {
                clearButton.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
            }

            // Debounce search
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.clearSearch();
            }
        });
    }

    performSearch(query) {
        const menuItems = document.querySelectorAll('.item-card');
        const emptyState = document.getElementById('empty-search-state');
        let visibleCount = 0;

        if (query.length === 0) {
            // Show all items
            menuItems.forEach(item => {
                item.style.display = 'block';
                visibleCount++;
            });
            emptyState.classList.add('hidden');
            this.resetCategoryFilter();
            return;
        }

        const searchTerms = query.toLowerCase().split(' ');

        menuItems.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            const isMatch = searchTerms.every(term => searchData.includes(term));
            
            if (isMatch) {
                item.style.display = 'block';
                visibleCount++;
                this.highlightSearchTerms(item, searchTerms);
            } else {
                item.style.display = 'none';
            }
        });

        // Show empty state if no results
        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }

        // Reset category filter when searching
        this.resetCategoryFilter();
    }

    highlightSearchTerms(item, terms) {
        // You can implement text highlighting here if needed
        // For now, we'll just add a subtle animation
        item.style.animation = 'none';
        item.offsetHeight; // Trigger reflow
        item.style.animation = 'pulse 0.5s ease-in-out';
    }

    clearSearch() {
        const searchInput = document.getElementById('search-input');
        const clearButton = document.getElementById('clear-search');
        
        searchInput.value = '';
        clearButton.classList.add('hidden');
        this.performSearch('');
    }

    resetSearch() {
        this.clearSearch();
        document.getElementById('empty-search-state').classList.add('hidden');
    }

    resetCategoryFilter() {
        // Reset category tabs to "All"
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.classList.remove('active', 'gradient-primary', 'text-white');
            tab.classList.add('bg-white/70', 'text-gray-700');
        });
        
        const allTab = document.querySelector('.category-tab[data-category-id="all"]');
        if (allTab) {
            allTab.classList.add('active', 'gradient-primary', 'text-white');
            allTab.classList.remove('bg-white/70', 'text-gray-700');
        }
    }

    showTableSelectionModal() {
        document.getElementById('table-selection-modal').classList.remove('hidden');
    }

    selectTable(e) {
        const tableId = e.currentTarget.dataset.tableId;
        const tableNumber = e.currentTarget.dataset.tableNumber;

        // Visual feedback
        document.querySelectorAll('.table-select-btn').forEach(btn => {
            btn.classList.remove('gradient-primary', 'text-white');
            btn.classList.add('bg-white/70');
        });

        e.currentTarget.classList.remove('bg-white/70');
        e.currentTarget.classList.add('gradient-primary', 'text-white');

        // Store selection
        this.selectedTable = { id: tableId, number: tableNumber };

        // Update display
        document.getElementById('selected-table-display').classList.remove('hidden');
        document.getElementById('current-table-number').textContent = tableNumber;

        // Hide modal after brief delay
        setTimeout(() => {
            document.getElementById('table-selection-modal').classList.add('hidden');
            showToast(`Table ${tableNumber} selected successfully!`);
        }, 500);
    }

    filterByCategory(e) {
        const categoryId = e.currentTarget.dataset.categoryId;

        // Clear search first
        this.clearSearch();

        // Update active tab
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.classList.remove('active', 'gradient-primary', 'text-white');
            tab.classList.add('bg-white/70', 'text-gray-700');
        });
        
        e.currentTarget.classList.add('active', 'gradient-primary', 'text-white');
        e.currentTarget.classList.remove('bg-white/70', 'text-gray-700');

        // Filter menu items with animation
        document.querySelectorAll('.item-card').forEach((card, index) => {
            if (categoryId === 'all' || card.dataset.category === categoryId) {
                card.style.display = 'none';
                setTimeout(() => {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.3s ease-in-out';
                }, index * 50);
            } else {
                card.style.display = 'none';
            }
        });
    }

    async addItemToCart(e) {
        if (!this.selectedTable) {
            showToast('Please select a table first', 'error');
            this.showTableSelectionModal();
            return;
        }

        const itemId = e.currentTarget.dataset.itemId;
        const itemName = e.currentTarget.dataset.itemName;
        const itemPrice = parseFloat(e.currentTarget.dataset.itemPrice);
        const hasOptions = e.currentTarget.dataset.hasOptions === 'true';

        // Add loading state
        const button = e.currentTarget;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
        button.disabled = true;

        try {
            if (hasOptions) {
                await this.showOptionsModal(itemId, itemName, itemPrice);
            } else {
                this.addSimpleItemToCart(itemId, itemName, itemPrice);
            }
        } finally {
            // Restore button state
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 500);
        }
    }

    async showOptionsModal(itemId, itemName, itemPrice) {
        try {
            const response = await axios.get(`/cashier/menu-items/${itemId}`);
            const item = response.data;

            this.currentItem = {
                id: itemId,
                name: itemName,
                price: itemPrice,
                quantity: 1,
                selectedOptions: [],
                item: item
            };

            // Update modal content
            document.getElementById('modal-item-name').textContent = itemName;
            document.getElementById('item-quantity').textContent = '1';
            
            this.renderOptionsContent(item.option_categories || []);
            this.updateModalPrice();
            
            // Show modal with animation
            const modal = document.getElementById('options-modal');
            modal.classList.remove('hidden');
            modal.style.animation = 'fadeIn 0.3s ease-in-out';
            
        } catch (error) {
            console.error('Error loading item options:', error);
            showToast('Failed to load item options', 'error');
        }
    }

    renderOptionsContent(optionCategories) {
        const container = document.getElementById('modal-options-content');
        
        if (!optionCategories || optionCategories.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-utensils text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">No additional options available</p>
                </div>
            `;
            return;
        }

        container.innerHTML = optionCategories.map(category => `
            <div class="mb-8 p-4 bg-white/50 rounded-2xl">
                <h4 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-list-ul mr-2 text-indigo-600"></i>
                    ${category.name}
                    ${category.pivot.is_required ? '<span class="ml-2 text-red-500 text-sm bg-red-100 px-2 py-1 rounded-full">Required</span>' : ''}
                </h4>
                <div class="space-y-3">
                    ${category.options.map(option => `
                        <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-white hover:border-indigo-300 cursor-pointer transition-all group">
                            <div class="flex items-center">
                                <input type="${category.pivot.is_required ? 'radio' : 'checkbox'}" 
                                       name="option_category_${category.id}" 
                                       value="${option.id}"
                                       data-price="${option.additional_price}"
                                       data-name="${option.name}"
                                       class="option-input mr-4 w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-800 group-hover:text-indigo-600 transition-colors">${option.name}</div>
                                    ${option.description ? `<div class="text-sm text-gray-600 mt-1">${option.description}</div>` : ''}
                                </div>
                            </div>
                            <div class="text-indigo-600 font-bold ml-4">
                                ${option.additional_price > 0 ? '+' : ''}${formatCurrency(option.additional_price)}
                            </div>
                        </label>
                    `).join('')}
                </div>
            </div>
        `).join('');

        // Add event listeners for options
        container.querySelectorAll('.option-input').forEach(input => {
            input.addEventListener('change', () => {
                this.updateSelectedOptions();
                // Add visual feedback
                input.closest('label').style.animation = 'pulse 0.3s ease-in-out';
            });
        });
    }

    updateSelectedOptions() {
        const selectedOptions = [];
        document.querySelectorAll('.option-input:checked').forEach(input => {
            selectedOptions.push({
                id: input.value,
                name: input.dataset.name,
                price: parseFloat(input.dataset.price)
            });
        });
        
        this.currentItem.selectedOptions = selectedOptions;
        this.updateModalPrice();
    }

    changeQuantity(change) {
        const currentQty = this.currentItem.quantity;
        const newQty = Math.max(1, currentQty + change);
        
        this.currentItem.quantity = newQty;
        const qtyElement = document.getElementById('item-quantity');
        qtyElement.textContent = newQty;
        qtyElement.style.animation = 'bounce 0.3s ease-in-out';
        
        this.updateModalPrice();
    }

    updateModalPrice() {
        const basePrice = this.currentItem.price;
        const optionsPrice = this.currentItem.selectedOptions.reduce((sum, option) => sum + option.price, 0);
        const totalPrice = (basePrice + optionsPrice) * this.currentItem.quantity;
        
        const priceElement = document.getElementById('modal-total-price');
        priceElement.textContent = formatCurrency(totalPrice);
        priceElement.style.animation = 'pulse 0.3s ease-in-out';
    }

    addToCartFinal() {
        const specialInstructions = document.getElementById('special-instructions').value;
        
        const cartItem = {
            id: Date.now(), // Unique cart item ID
            menuItemId: this.currentItem.id,
            name: this.currentItem.name,
            price: this.currentItem.price,
            quantity: this.currentItem.quantity,
            selectedOptions: this.currentItem.selectedOptions,
            specialInstructions: specialInstructions,
            totalPrice: this.calculateItemTotal(this.currentItem)
        };

        this.cart.push(cartItem);
        this.updateCartDisplay();
        this.closeModal();
        
        showToast(`${cartItem.name} added to cart!`);
    }

    addSimpleItemToCart(itemId, itemName, itemPrice) {
        const cartItem = {
            id: Date.now(),
            menuItemId: itemId,
            name: itemName,
            price: itemPrice,
            quantity: 1,
            selectedOptions: [],
            specialInstructions: '',
            totalPrice: itemPrice
        };

        this.cart.push(cartItem);
        this.updateCartDisplay();
        
        showToast(`${itemName} added to cart!`);
    }

    calculateItemTotal(item) {
        const basePrice = item.price;
        const optionsPrice = item.selectedOptions.reduce((sum, option) => sum + option.price, 0);
        return (basePrice + optionsPrice) * item.quantity;
    }

    updateCartDisplay() {
        const cartButton = document.getElementById('cart-button');
        const cartCount = document.getElementById('cart-count');
        
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        
        if (totalItems > 0) {
            cartButton.classList.remove('hidden');
            cartCount.textContent = totalItems;
            cartCount.style.animation = 'bounce 0.5s ease-in-out';
        } else {
            cartButton.classList.add('hidden');
        }
    }

    showCartModal() {
        this.renderCartItems();
        this.updateCartTotals();
        const modal = document.getElementById('cart-modal');
        modal.classList.remove('hidden');
        modal.style.animation = 'fadeIn 0.3s ease-in-out';
    }

    renderCartItems() {
        const container = document.getElementById('cart-items');
        
        if (this.cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Your cart is empty</p>
                    <p class="text-gray-500 text-sm">Add some delicious items to get started!</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.cart.map((item, index) => `
            <div class="bg-white/70 rounded-2xl p-5 hover:bg-white transition-all hover-lift">
                <div class="flex justify-between items-start mb-3">
                    <h4 class="font-bold text-lg text-gray-800">${item.name}</h4>
                    <button class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-all" onclick="cashier.removeFromCart(${index})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                
                ${item.selectedOptions.length > 0 ? `
                    <div class="bg-indigo-50 rounded-lg p-3 mb-3">
                        <div class="text-sm font-medium text-indigo-800 mb-1">
                            <i class="fas fa-plus-circle mr-1"></i>Options:
                        </div>
                        <div class="text-sm text-indigo-700">
                            ${item.selectedOptions.map(opt => opt.name).join(', ')}
                        </div>
                    </div>
                ` : ''}
                
                ${item.specialInstructions ? `
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3">
                        <div class="text-sm font-medium text-blue-800 mb-1">
                            <i class="fas fa-sticky-note mr-1"></i>Special Notes:
                        </div>
                        <div class="text-sm text-blue-700">${item.specialInstructions}</div>
                    </div>
                ` : ''}
                
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <button class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-all" onclick="cashier.updateCartItemQuantity(${index}, -1)">
                            <i class="fas fa-minus text-sm"></i>
                        </button>
                        <span class="font-bold text-lg min-w-[2rem] text-center">${item.quantity}</span>
                        <button class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-all" onclick="cashier.updateCartItemQuantity(${index}, 1)">
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                    <div class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        ${formatCurrency(item.totalPrice)}
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateCartTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + item.totalPrice, 0);
        const taxRate = 0.11;
        const serviceRate = 0.05;
        
        const tax = subtotal * taxRate;
        const service = subtotal * serviceRate;
        const total = subtotal + tax + service;

        document.getElementById('cart-subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('cart-tax').textContent = formatCurrency(tax);
        document.getElementById('cart-service').textContent = formatCurrency(service);
        document.getElementById('cart-total').textContent = formatCurrency(total);
    }

    removeFromCart(index) {
        const item = this.cart[index];
        this.cart.splice(index, 1);
        this.updateCartDisplay();
        this.renderCartItems();
        this.updateCartTotals();
        
        showToast(`${item.name} removed from cart`);
        
        if (this.cart.length === 0) {
            this.closeCartModal();
        }
    }

    updateCartItemQuantity(index, change) {
        const item = this.cart[index];
        const newQuantity = Math.max(1, item.quantity + change);
        
        item.quantity = newQuantity;
        item.totalPrice = this.calculateItemTotal(item);
        
        this.updateCartDisplay();
        this.renderCartItems();
        this.updateCartTotals();
    }

    async processOrder() {
        if (!this.selectedTable) {
            showToast('Please select a table first', 'error');
            return;
        }

        if (this.cart.length === 0) {
            showToast('Cart is empty', 'error');
            return;
        }

        const customerName = document.getElementById('customer-name').value;
        const processButton = document.getElementById('process-order');
        const originalText = processButton.innerHTML;

        // Show loading state
        processButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        processButton.disabled = true;

        // Prepare order data
        const orderData = {
            table_id: this.selectedTable.id,
            customer_name: customerName,
            items: this.cart.map(item => ({
                menu_item_id: item.menuItemId,
                quantity: item.quantity,
                options: item.selectedOptions.map(opt => opt.id),
                special_instructions: item.specialInstructions
            }))
        };

        try {
            const response = await axios.post('/cashier/orders', orderData);
            
            if (response.data.success) {
                showToast('Order created successfully!');
                
                // Reset everything
                this.cart = [];
                this.selectedTable = null;
                this.updateCartDisplay();
                this.closeCartModal();
                
                // Reset UI
                document.getElementById('selected-table-display').classList.add('hidden');
                document.getElementById('customer-name').value = '';
                
                // Show success animation
                setTimeout(() => {
                    this.showTableSelectionModal();
                }, 1000);
                
            } else {
                showToast('Failed to create order: ' + response.data.message, 'error');
            }
        } catch (error) {
            console.error('Error creating order:', error);
            showToast('Failed to create order', 'error');
        } finally {
            // Restore button state
            processButton.innerHTML = originalText;
            processButton.disabled = false;
        }
    }

    closeModal() {
        const modal = document.getElementById('options-modal');
        modal.style.animation = 'fadeOut 0.3s ease-in-out';
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('special-instructions').value = '';
            this.currentItem = null;
        }, 300);
    }

    closeCartModal() {
        const modal = document.getElementById('cart-modal');
        modal.style.animation = 'fadeOut 0.3s ease-in-out';
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
        40%, 43% { transform: translateY(-10px); }
        70% { transform: translateY(-5px); }
        90% { transform: translateY(-2px); }
    }
`;
document.head.appendChild(style);

// Initialize the modern cashier system
const cashier = new ModernCashierSystem();