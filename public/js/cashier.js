// public/js/cashier.js - Enhanced with customer info validation

class CashierSystem {
    constructor() {
        this.cart = [];
        this.selectedTable = null;
        this.currentItem = null;
        this.searchTimeout = null;
        this.currentCategory = 'all';
        this.currentSort = 'name';
        this.currentFilter = 'all';
        this.allItems = [];
        this.isProcessingOrder = false;
        this.storageKeys = {
            cart: 'cashier_cart',
            table: 'cashier_selected_table',
            tableTimestamp: 'cashier_table_timestamp',
            customer: 'cashier_customer_info'
        };
        this.customerInfo = {
            name: '',
            phone: '',
            paymentMethod: 'cash' // Default payment method
        };
        this.appliedCoupon = null;
        this.appliedDiscount = null;
        this.itemQuantities = new Map(); // Track quantities per menu item
        this.pendingDeleteItem = null;
        this.init();
        
    }

    init() {
        this.loadFromStorage();
        this.setupEventListeners();
        this.updateCartDisplay();
        this.initializeTableSelection();
        this.setupSearchFunctionality();
        this.setupFiltersAndSorting();
        this.setupCouponListeners();
        this.setupDiscountListeners();
        this.initializeQuantities();
        this.cacheAllItems();
        this.preloadImages();
        this.setupCartPreview();
        // this.syncWithOrdersPage();
    }

    // Load data from localStorage
    loadFromStorage() {
        try {
            // Load cart
            const savedCart = localStorage.getItem(this.storageKeys.cart);
            if (savedCart) {
                this.cart = JSON.parse(savedCart);
                console.log('Loaded cart from localStorage:', this.cart.length, 'items');
                setTimeout(() => this.updateAllMenuCardQuantities(), 100);
            }

            // Load customer info
            const savedCustomer = localStorage.getItem(this.storageKeys.customer);
            if (savedCustomer) {
                this.customerInfo = JSON.parse(savedCustomer);
                console.log('Loaded customer info from localStorage:', this.customerInfo);
                this.populateCustomerFields();
            }

            // Load table selection with timestamp validation
            const savedTable = localStorage.getItem(this.storageKeys.table);
            const tableTimestamp = localStorage.getItem(this.storageKeys.tableTimestamp);
            
            if (savedTable && tableTimestamp) {
                const timeDiff = Date.now() - parseInt(tableTimestamp);
                const hoursElapsed = timeDiff / (1000 * 60 * 60);
                
                // Table selection expires after 8 hours (shift duration)
                if (hoursElapsed < 8) {
                    this.selectedTable = JSON.parse(savedTable);
                    console.log('Loaded table from localStorage:', this.selectedTable);
                } else {
                    // Clear expired table selection
                    this.clearTableFromStorage();
                    console.log('Table selection expired, cleared from storage');
                }
            }
        } catch (error) {
            console.error('Error loading data from localStorage:', error);
            this.clearAllStorage();
        }
    }

    // Save data to localStorage
    saveToStorage() {
        try {
            localStorage.setItem(this.storageKeys.cart, JSON.stringify(this.cart));
            localStorage.setItem(this.storageKeys.customer, JSON.stringify(this.customerInfo));
            
            if (this.selectedTable) {
                localStorage.setItem(this.storageKeys.table, JSON.stringify(this.selectedTable));
                localStorage.setItem(this.storageKeys.tableTimestamp, Date.now().toString());
            }
            
            console.log('Data saved to localStorage');
        } catch (error) {
            console.error('Error saving to localStorage:', error);
        }
    }

    // Populate customer fields with saved data
    populateCustomerFields() {
        const customerNameField = document.getElementById('customer-name');
        const customerPhoneField = document.getElementById('customer-phone');
        const paymentMethodField = document.getElementById('payment-method');
        const modalCustomerNameField = document.getElementById('modal-customer-name');
        const modalCustomerPhoneField = document.getElementById('modal-customer-phone');
        const modalPaymentMethodField = document.getElementById('modal-payment-method');

        if (customerNameField && this.customerInfo.name) {
            customerNameField.value = this.customerInfo.name;
        }
        if (customerPhoneField && this.customerInfo.phone) {
            customerPhoneField.value = this.customerInfo.phone;
        }
        if (paymentMethodField && this.customerInfo.paymentMethod) {
            paymentMethodField.value = this.customerInfo.paymentMethod;
        }
        if (modalCustomerNameField && this.customerInfo.name) {
            modalCustomerNameField.value = this.customerInfo.name;
        }
        if (modalCustomerPhoneField && this.customerInfo.phone) {
            modalCustomerPhoneField.value = this.customerInfo.phone;
        }
        if (modalPaymentMethodField && this.customerInfo.paymentMethod) {
            modalPaymentMethodField.value = this.customerInfo.paymentMethod;
        }
    }

    // Update customer info and save
    updateCustomerInfo(name, phone, paymentMethod = 'cash') {
        this.customerInfo = {
            name: name ? name.trim() : '',
            phone: phone ? phone.trim() : '',
            paymentMethod: paymentMethod || 'cash'
        };
        this.saveToStorage();
    }

    // Clear specific data from storage
    clearTableFromStorage() {
        localStorage.removeItem(this.storageKeys.table);
        localStorage.removeItem(this.storageKeys.tableTimestamp);
        this.selectedTable = null;
    }

    clearCartFromStorage() {
        localStorage.removeItem(this.storageKeys.cart);
        this.cart = [];
    }

    clearCustomerFromStorage() {
        localStorage.removeItem(this.storageKeys.customer);
        this.customerInfo = { name: '', phone: '' };
    }

    clearAllStorage() {
        Object.values(this.storageKeys).forEach(key => {
            localStorage.removeItem(key);
        });
        this.cart = [];
        this.selectedTable = null;
        this.customerInfo = { name: '', phone: '', paymentMethod: 'cash' };
    }

    // FIXED: Smart table selection initialization
    initializeTableSelection() {
        // Data sudah di-load di loadFromStorage(), jadi langsung cek this.selectedTable
        if (this.selectedTable) {
            // Table sudah ada di localStorage dan valid, tampilkan info dan highlight
            console.log('Table already selected from localStorage:', this.selectedTable);
            this.displaySelectedTableInfo();
            this.highlightSelectedTable();
            // TIDAK menampilkan modal karena table sudah terpilih
        } else {
            // Tidak ada table yang terpilih atau expired, tampilkan modal
            console.log('No table selected, showing modal');
            setTimeout(() => {
                this.showTableSelectionModal();
            }, 500);
        }
    }

    // Display selected table information
    displaySelectedTableInfo() {
        const currentTableInfo = document.getElementById('current-table-info');
        const currentTableNumber = document.getElementById('current-table-number');
        
        if (currentTableInfo && currentTableNumber && this.selectedTable) {
            currentTableInfo.classList.remove('hidden');
            currentTableNumber.textContent = this.selectedTable.number;
            
            // Add session info
            const timestamp = localStorage.getItem(this.storageKeys.tableTimestamp);
            if (timestamp) {
                const sessionStart = new Date(parseInt(timestamp));
                const sessionInfo = document.getElementById('session-info');
                if (sessionInfo) {
                    sessionInfo.textContent = `Session started: ${sessionStart.toLocaleTimeString()}`;
                }
            }
        }
    }

    // Highlight the selected table button
    highlightSelectedTable() {
        if (!this.selectedTable) return;

        // Tambahkan delay kecil untuk memastikan DOM sudah ready
        setTimeout(() => {
            document.querySelectorAll('.table-btn').forEach(btn => {
                if (btn.dataset.tableId === this.selectedTable.id) {
                    btn.classList.remove('bg-gradient-to-br', 'from-gray-50', 'to-gray-100');
                    btn.classList.add('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white', 'scale-105');
                }
            });
        }, 100);
    }

    hideOrdersCartNotification() {
        const notification = document.getElementById('orders-cart-notification');
        if (notification) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }
    }

    goToMenuFromOrders() {
        window.location.href = '/cashier';
    }

    // Update method untuk handle action-container structure
    updateMenuCardQuantity(itemId, quantity) {
        const itemCard = document.querySelector(`.add-item-btn[data-item-id="${itemId}"]`);
        if (!itemCard) return;

        const actionContainer = itemCard.closest('.action-container');
        let quantityContainer = actionContainer.querySelector('.quantity-controls');

        if (quantity > 0) {
            // Hide add button and show quantity controls
            itemCard.classList.add('hidden');
            
            if (quantityContainer) {
                // Update existing quantity display
                quantityContainer.querySelector('.quantity-display').textContent = quantity;
                quantityContainer.classList.remove('hidden');
                quantityContainer.classList.add('flex');
                
                // Setup event listeners jika belum ada
                if (!quantityContainer.dataset.listenersAdded) {
                    this.setupMenuCardQuantityListeners(quantityContainer);
                    quantityContainer.dataset.listenersAdded = 'true';
                }
            }
        } else {
            // Show add button and hide quantity controls
            itemCard.classList.remove('hidden');
            if (quantityContainer) {
                quantityContainer.classList.add('hidden');
                quantityContainer.classList.remove('flex');
            }
        }
        
        // Update stored quantity
        this.itemQuantities.set(itemId, quantity);
    }

    // Method baru untuk setup menu card quantity listeners
    setupMenuCardQuantityListeners(container) {
        const decreaseBtn = container.querySelector('.decrease-btn');
        const increaseBtn = container.querySelector('.increase-btn');
        
        if (decreaseBtn && !decreaseBtn.dataset.listenerAdded) {
            decreaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId = e.currentTarget.dataset.itemId;
                this.decreaseItemQuantity(itemId);
            });
            decreaseBtn.dataset.listenerAdded = 'true';
        }
        
        if (increaseBtn && !increaseBtn.dataset.listenerAdded) {
            increaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId = e.currentTarget.dataset.itemId;
                this.increaseItemQuantity(itemId);
            });
            increaseBtn.dataset.listenerAdded = 'true';
        }
    }

    // Setup event listeners untuk quantity controls
    setupQuantityControlListeners(container) {
        const decreaseBtn = container.querySelector('.decrease-btn');
        const increaseBtn = container.querySelector('.increase-btn');
        
        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId = e.currentTarget.dataset.itemId;
                this.decreaseItemQuantity(itemId);
            });
        }
        
        if (increaseBtn) {
            increaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId = e.currentTarget.dataset.itemId;
                this.increaseItemQuantity(itemId);
            });
        }
    }

    // Method untuk increase quantity
    increaseItemQuantity(itemId) {
        const button = document.querySelector(`.add-item-btn[data-item-id="${itemId}"]`);
        if (!button) return;
        
        const itemName = button.dataset.itemName;
        const itemPrice = parseFloat(button.dataset.itemPrice);
        const hasOptions = button.dataset.hasOptions === 'true';
        
        if (!this.selectedTable) {
            showToast('Please select a table first', 'error');
            this.showTableSelectionModal();
            return;
        }
        
        if (hasOptions) {
            // For items with options, always show modal
            this.showOptionsModalSync(itemId, itemName, itemPrice, button);
            return;
        }
        
        // Find existing item in cart
        const existingItem = this.cart.find(item => 
            item.menuItemId === itemId && 
            item.selectedOptions.length === 0 &&
            !item.specialInstructions
        );
        
        if (existingItem) {
            // Update existing item
            existingItem.quantity += 1;
            existingItem.totalPrice = existingItem.price * existingItem.quantity;
        } else {
            // Add new item to cart
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
        }
        
        // Update UI and storage
        const totalQuantity = this.getTotalQuantityForItem(itemId);
        this.updateMenuCardQuantity(itemId, totalQuantity);
        this.updateCartDisplay();
        this.saveToStorage();
        
        showToast(`${itemName} quantity increased!`);
    }

    // Method untuk decrease quantity
    decreaseItemQuantity(itemId) {
        const totalQuantity = this.getTotalQuantityForItem(itemId);
        
        if (totalQuantity <= 1) {
            // Show confirmation dialog for removing item
            this.showDeleteConfirmation(itemId);
            return;
        }
        
        // Find the most recent simple item (no options) to decrease
        const simpleItem = this.cart.find(item => 
            item.menuItemId === itemId && 
            item.selectedOptions.length === 0 &&
            !item.specialInstructions
        );
        
        if (simpleItem) {
            simpleItem.quantity -= 1;
            simpleItem.totalPrice = simpleItem.price * simpleItem.quantity;
            
            if (simpleItem.quantity <= 0) {
                const index = this.cart.indexOf(simpleItem);
                this.cart.splice(index, 1);
            }
        }
        
        // Update UI segera setelah perubahan
        const newTotalQuantity = this.getTotalQuantityForItem(itemId);
        this.updateMenuCardQuantity(itemId, newTotalQuantity);
        this.updateCartDisplay();
        this.saveToStorage();
        
        const itemName = document.querySelector(`.add-item-btn[data-item-id="${itemId}"]`)?.dataset.itemName || 'Item';
        showToast(`${itemName} quantity decreased!`);
    }

    // Get total quantity untuk specific item ID
    getTotalQuantityForItem(itemId) {
        return this.cart
            .filter(item => item.menuItemId === itemId)
            .reduce((total, item) => total + item.quantity, 0);
    }

    // Show delete confirmation modal
    showDeleteConfirmation(itemId, cartIndex = null) {
        const itemName = cartIndex !== null ? 
            this.cart[cartIndex].name : 
            document.querySelector(`.add-item-btn[data-item-id="${itemId}"]`)?.dataset.itemName || 'this item';
        
        const modal = document.createElement('div');
        modal.className = 'delete-confirmation-modal';
        modal.innerHTML = `
            <div class="delete-confirmation-content">
                <div class="delete-confirmation-icon">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Remove Item?</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to remove <strong>${itemName}</strong> from your cart?</p>
                <div class="flex space-x-3">
                    <button class="cancel-delete flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-all">
                        Cancel
                    </button>
                    <button class="confirm-delete flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl font-semibold transition-all">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
            </div>
        `;
        
        // Add event listeners
        modal.querySelector('.cancel-delete').addEventListener('click', () => {
            modal.remove();
        });
        
        modal.querySelector('.confirm-delete').addEventListener('click', () => {
            if (cartIndex !== null) {
                this.confirmRemoveFromCart(cartIndex);
            } else {
                this.confirmRemoveMenuItem(itemId);
            }
            modal.remove();
        });
        
        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        document.body.appendChild(modal);
    }

    confirmRemoveMenuItem(itemId) {
        // Remove all instances of this item from cart
        this.cart = this.cart.filter(item => item.menuItemId !== itemId);
        
        // Update UI segera setelah menghapus
        this.updateMenuCardQuantity(itemId, 0);
        this.updateCartDisplay();
        this.saveToStorage();
        
        const itemName = document.querySelector(`.add-item-btn[data-item-id="${itemId}"]`)?.dataset.itemName || 'Item';
        showToast(`${itemName} removed from cart`, 'error');
    }

    confirmRemoveFromCart(index) {
        const item = this.cart[index];
        this.cart.splice(index, 1);
        
        // Update menu card quantity segera setelah menghapus dari cart
        const remainingQuantity = this.getTotalQuantityForItem(item.menuItemId);
        this.updateMenuCardQuantity(item.menuItemId, remainingQuantity);
        
        this.updateCartDisplay();
        this.saveToStorage();
        this.renderCartItems();
        this.updateCartTotals();
        
        showToast(`${item.name} removed from cart`, 'error');
    }

    // Update cart preview content
    updateCartPreview() {
        const previewItems = document.getElementById('preview-items');
        const previewCount = document.getElementById('preview-count');
        const previewTotal = document.getElementById('preview-total');
        
        if (!previewItems || !previewCount || !previewTotal) return;
        
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        const totalPrice = this.cart.reduce((sum, item) => sum + item.totalPrice, 0);
        
        previewCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
        previewTotal.textContent = formatCurrency(totalPrice);
        
        if (this.cart.length === 0) {
            previewItems.innerHTML = `
                <div class="text-center py-4 text-gray-500">
                    <i class="fas fa-shopping-cart text-2xl mb-2 opacity-50"></i>
                    <p class="text-sm">No items in cart</p>
                </div>
            `;
            return;
        }
        
        // Show max 3 items in preview
        const itemsToShow = this.cart.slice(0, 3);
        
        previewItems.innerHTML = itemsToShow.map(item => `
            <div class="flex items-center justify-between py-1">
                <div class="flex-1">
                    <div class="font-medium text-sm text-gray-800 truncate">${item.name}</div>
                    <div class="text-xs text-gray-500">${item.quantity}x ${formatCurrency(item.price)}</div>
                </div>
                <div class="text-sm font-semibold text-orange-600 ml-2">
                    ${formatCurrency(item.totalPrice)}
                </div>
            </div>
        `).join('') + (this.cart.length > 3 ? `
            <div class="text-center py-1">
                <span class="text-xs text-gray-500">+${this.cart.length - 3} more items</span>
            </div>
        ` : '');
    }

    setupCartPreview() {
        const cartButton = document.getElementById('cart-button');
        const cartPreview = document.getElementById('cart-preview');
        let previewTimeout;
        
        if (!cartButton || !cartPreview) return;
        
        // Show preview on hover (desktop only)
        cartButton.addEventListener('mouseenter', () => {
            if (window.innerWidth >= 768) { // Only on tablet and desktop
                clearTimeout(previewTimeout);
                this.updateCartPreview();
                cartPreview.classList.remove('hidden');
                setTimeout(() => {
                    cartPreview.classList.add('show');
                }, 10);
            }
        });
        
        // Hide preview on mouse leave
        cartButton.addEventListener('mouseleave', () => {
            previewTimeout = setTimeout(() => {
                cartPreview.classList.remove('show');
                setTimeout(() => {
                    cartPreview.classList.add('hidden');
                }, 300);
            }, 500);
        });
        
        // Keep preview open when hovering over it
        cartPreview.addEventListener('mouseenter', () => {
            clearTimeout(previewTimeout);
        });
        
        cartPreview.addEventListener('mouseleave', () => {
            previewTimeout = setTimeout(() => {
                cartPreview.classList.remove('show');
                setTimeout(() => {
                    cartPreview.classList.add('hidden');
                }, 300);
            }, 200);
        });
    }

    // Method untuk update semua menu card quantities berdasarkan cart
    updateAllMenuCardQuantities() {
        // Reset semua ke 0 dulu
        document.querySelectorAll('.add-item-btn').forEach(btn => {
            const itemId = btn.dataset.itemId;
            this.updateMenuCardQuantity(itemId, 0);
        });
        
        // Hitung ulang quantities dari cart
        const quantityMap = new Map();
        this.cart.forEach(item => {
            const currentQty = quantityMap.get(item.menuItemId) || 0;
            quantityMap.set(item.menuItemId, currentQty + item.quantity);
        });
        
        // Update UI berdasarkan quantities yang baru
        quantityMap.forEach((quantity, itemId) => {
            this.updateMenuCardQuantity(itemId, quantity);
        });
    }

    // Override existing addItemToCart method
    addItemToCart(e) {
        if (!this.selectedTable) {
            showToast('Please select a table first', 'error');
            this.showTableSelectionModal();
            return;
        }

        const button = e.currentTarget;
        const itemId = button.dataset.itemId;
        const itemName = button.dataset.itemName;
        const itemPrice = parseFloat(button.dataset.itemPrice);
        const hasOptions = button.dataset.hasOptions === 'true';

        if (hasOptions) {
            // Items with options always go through modal
            this.showOptionsModalSync(itemId, itemName, itemPrice, button);
        } else {
            // Simple items use inline quantity
            this.increaseItemQuantity(itemId);
        }
    }

    updateCartItemQuantity(index, change) {
        const item = this.cart[index];
        const newQuantity = item.quantity + change;
        
        if (newQuantity <= 0) {
            // Show confirmation for removing item
            this.showDeleteConfirmation(null, index);
            return;
        }
        
        item.quantity = newQuantity;
        item.totalPrice = this.calculateItemTotal(item);
        
        // Update menu card quantity segera
        const totalQuantity = this.getTotalQuantityForItem(item.menuItemId);
        this.updateMenuCardQuantity(item.menuItemId, totalQuantity);
        
        this.updateCartDisplay();
        this.saveToStorage();
        this.renderCartItems();
        this.updateCartTotals();
    }

    // Initialize quantities on load
    initializeQuantities() {
        // Calculate quantities for each menu item from cart
        const quantityMap = new Map();
        
        this.cart.forEach(item => {
            const currentQty = quantityMap.get(item.menuItemId) || 0;
            quantityMap.set(item.menuItemId, currentQty + item.quantity);
        });
        
        // Update UI for all items with quantities > 0
        quantityMap.forEach((quantity, itemId) => {
            if (quantity > 0) {
                this.updateMenuCardQuantity(itemId, quantity);
            }
        });
        
        this.itemQuantities = quantityMap;
    }

    cacheAllItems() {
        // Cache all menu items for better performance
        this.allItems = Array.from(document.querySelectorAll('.item-card')).map(card => ({
            element: card,
            name: card.dataset.name,
            category: card.dataset.category,
            price: parseFloat(card.dataset.price),
            featured: card.dataset.featured === 'true',
            discount: card.dataset.discount === 'true'
        }));
    }

    preloadImages() {
        // Preload images for better UX
        document.querySelectorAll('.item-card img').forEach(img => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = img.src;
            document.head.appendChild(link);
        });
    }

    setupEventListeners() {
        // Table selection
        document.querySelectorAll('.table-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.selectTable(e));
        });

        // Change table button
        const changeTableBtn = document.getElementById('change-table-btn');
        if (changeTableBtn) {
            changeTableBtn.addEventListener('click', () => this.showTableSelectionModal());
        }

        // Add clear table button functionality
        const clearTableBtn = document.getElementById('clear-table-btn');
        if (clearTableBtn) {
            clearTableBtn.addEventListener('click', () => this.clearTableSelection());
        }

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
        this.setupModalEventListeners();

        // Close modals when clicking outside
        this.setupModalCloseListeners();

        // Keyboard shortcuts
        this.setupKeyboardShortcuts();

        // Add page visibility handler
        this.setupPageVisibilityHandler();

        // Setup customer info event listeners
        this.setupCustomerInfoListeners();
    }

    // Setup customer information event listeners
    setupCustomerInfoListeners() {
        const customerNameField = document.getElementById('customer-name');
        const customerPhoneField = document.getElementById('customer-phone');
        const paymentMethodOptions = document.querySelectorAll('input[name="payment-method"]');

        if (customerNameField) {
            customerNameField.addEventListener('input', (e) => {
                this.updateCustomerInfo(e.target.value, this.customerInfo.phone, this.customerInfo.paymentMethod);
                this.validateCustomerName(e.target);
            });

            customerNameField.addEventListener('blur', (e) => {
                this.validateCustomerName(e.target);
            });
        }

        if (customerPhoneField) {
            customerPhoneField.addEventListener('input', (e) => {
                const formattedPhone = this.formatPhoneNumber(e.target.value);
                e.target.value = formattedPhone;
                this.updateCustomerInfo(this.customerInfo.name, formattedPhone, this.customerInfo.paymentMethod);
                this.validatePhoneNumber(e.target);
            });

            customerPhoneField.addEventListener('blur', (e) => {
                this.validatePhoneNumber(e.target);
            });
        }

        // Payment method selection
        paymentMethodOptions.forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    // Update visual selection
                    document.querySelectorAll('.payment-method-option').forEach(option => {
                        option.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'border-blue-400');
                        option.classList.add('border-blue-200');
                    });
                    
                    e.target.closest('.payment-method-option').classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'border-blue-400');
                    e.target.closest('.payment-method-option').classList.remove('border-blue-200');
                    
                    // Update customer info
                    this.updateCustomerInfo(this.customerInfo.name, this.customerInfo.phone, e.target.value);
                    
                    // Show selected payment method feedback
                    showToast(`Payment method: ${this.getPaymentMethodName(e.target.value)} selected`);
                }
            });
        });

        // Set initial payment method selection
        const defaultPayment = document.getElementById('payment-cash');
        if (defaultPayment && !this.customerInfo.paymentMethod) {
            defaultPayment.checked = true;
            defaultPayment.closest('.payment-method-option').classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'border-blue-400');
            this.updateCustomerInfo(this.customerInfo.name, this.customerInfo.phone, 'cash');
        } else if (this.customerInfo.paymentMethod) {
            // Restore saved payment method
            const savedPaymentRadio = document.querySelector(`input[name="payment-method"][value="${this.customerInfo.paymentMethod}"]`);
            if (savedPaymentRadio) {
                savedPaymentRadio.checked = true;
                savedPaymentRadio.closest('.payment-method-option').classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'border-blue-400');
            }
        }
    }

    // Setup discount event listeners
    setupDiscountListeners() {
        const applyDiscountBtn = document.getElementById('apply-discount');
        const removeDiscountBtn = document.getElementById('remove-discount');
        const discountCodeInput = document.getElementById('discount-code');

        if (applyDiscountBtn) {
            applyDiscountBtn.addEventListener('click', () => this.applyDiscount());
        }

        if (removeDiscountBtn) {
            removeDiscountBtn.addEventListener('click', () => this.removeDiscount());
        }

        if (discountCodeInput) {
            discountCodeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.applyDiscount();
                }
            });
        }
    }

    // Apply discount
    async applyDiscount() {
        const discountCode = document.getElementById('discount-code')?.value?.trim();
        if (!discountCode) {
            showToast('Please enter a discount code', 'error');
            return;
        }

        const subtotal = this.cart.reduce((sum, item) => sum + item.totalPrice, 0);
        if (subtotal === 0) {
            showToast('Cart is empty', 'error');
            return;
        }

        showLoading('Validating discount...');

        try {
            const response = await axios.post('/cashier/validate-discount', {
                discount_code: discountCode,
                order_amount: subtotal
            });

            hideLoading();

            if (response.data.success) {
                this.appliedDiscount = {
                    id: response.data.discount.id,
                    code: response.data.discount.code,
                    name: response.data.discount.name,
                    description: response.data.discount.description,
                    discount_amount: response.data.discount_amount
                };

                this.showDiscountSuccess();
                this.updateCartTotals();
                showToast(`Discount "${discountCode}" applied! Saved ${formatCurrency(response.data.discount_amount)}`);
            } else {
                this.showDiscountError(response.data.message);
            }
        } catch (error) {
            hideLoading();
            console.error('Error applying discount:', error);
            this.showDiscountError(error.response?.data?.message || 'Failed to validate discount');
        }
    }

    // Remove discount
    removeDiscount() {
        this.appliedDiscount = null;
        this.hideDiscountStatus();
        this.updateCartTotals();
        document.getElementById('discount-code').value = '';
        showToast('Discount removed');
    }

    // Show discount success
    showDiscountSuccess() {
        const statusDiv = document.getElementById('discount-status');
        const successDiv = document.getElementById('discount-success');
        const errorDiv = document.getElementById('discount-error');
        const descriptionSpan = document.getElementById('discount-description');

        if (statusDiv && successDiv && descriptionSpan) {
            statusDiv.classList.remove('hidden');
            successDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            descriptionSpan.textContent = `${this.appliedDiscount.description || this.appliedDiscount.name} - Save ${formatCurrency(this.appliedDiscount.discount_amount)}`;
        }
    }

    // Show discount error
    showDiscountError(message) {
        const statusDiv = document.getElementById('discount-status');
        const successDiv = document.getElementById('discount-success');
        const errorDiv = document.getElementById('discount-error');
        const errorMessageSpan = document.getElementById('discount-error-message');

        if (statusDiv && errorDiv && errorMessageSpan) {
            statusDiv.classList.remove('hidden');
            errorDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
            errorMessageSpan.textContent = message;
        }

        showToast(message, 'error');
    }

    // Hide discount status
    hideDiscountStatus() {
        const statusDiv = document.getElementById('discount-status');
        if (statusDiv) {
            statusDiv.classList.add('hidden');
        }
    }


    // Setup coupon event listeners
    setupCouponListeners() {
        const applyCouponBtn = document.getElementById('apply-coupon');
        const removeCouponBtn = document.getElementById('remove-coupon');
        const couponCodeInput = document.getElementById('coupon-code');

        if (applyCouponBtn) {
            applyCouponBtn.addEventListener('click', () => this.applyCoupon());
        }

        if (removeCouponBtn) {
            removeCouponBtn.addEventListener('click', () => this.removeCoupon());
        }

        if (couponCodeInput) {
            couponCodeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.applyCoupon();
                }
            });
        }
    }

    // Apply coupon
    async applyCoupon() {
        const couponCode = document.getElementById('coupon-code')?.value?.trim();
        if (!couponCode) {
            showToast('Please enter a coupon code', 'error');
            return;
        }

        const subtotal = this.cart.reduce((sum, item) => sum + item.totalPrice, 0);
        if (subtotal === 0) {
            showToast('Cart is empty', 'error');
            return;
        }

        showLoading('Validating coupon...');

        try {
            const response = await axios.post('/cashier/validate-coupon', {
                coupon_code: couponCode,
                order_amount: subtotal
            });

            hideLoading();

            if (response.data.success) {
                this.appliedCoupon = {
                    id: response.data.coupon.id,
                    code: response.data.coupon.code,
                    name: response.data.coupon.name,
                    description: response.data.coupon.description,
                    discount_amount: response.data.discount_amount
                };

                this.showCouponSuccess();
                this.updateCartTotals();
                showToast(`Coupon "${couponCode}" applied! Saved ${formatCurrency(response.data.discount_amount)}`);
            } else {
                this.showCouponError(response.data.message);
            }
        } catch (error) {
            hideLoading();
            console.error('Error applying coupon:', error);
            this.showCouponError(error.response?.data?.message || 'Failed to validate coupon');
        }
    }

    // Remove coupon
    removeCoupon() {
        this.appliedCoupon = null;
        this.hideCouponStatus();
        this.updateCartTotals();
        document.getElementById('coupon-code').value = '';
        showToast('Coupon removed');
    }

    // Show coupon success
    showCouponSuccess() {
        const statusDiv = document.getElementById('coupon-status');
        const successDiv = document.getElementById('coupon-success');
        const errorDiv = document.getElementById('coupon-error');
        const descriptionSpan = document.getElementById('coupon-description');

        if (statusDiv && successDiv && descriptionSpan) {
            statusDiv.classList.remove('hidden');
            successDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            descriptionSpan.textContent = `${this.appliedCoupon.description || this.appliedCoupon.name} - Save ${formatCurrency(this.appliedCoupon.discount_amount)}`;
        }
    }

    // Show coupon error
    showCouponError(message) {
        const statusDiv = document.getElementById('coupon-status');
        const successDiv = document.getElementById('coupon-success');
        const errorDiv = document.getElementById('coupon-error');
        const errorMessageSpan = document.getElementById('coupon-error-message');

        if (statusDiv && errorDiv && errorMessageSpan) {
            statusDiv.classList.remove('hidden');
            errorDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
            errorMessageSpan.textContent = message;
        }

        showToast(message, 'error');
    }

    // Hide coupon status
    hideCouponStatus() {
        const statusDiv = document.getElementById('coupon-status');
        if (statusDiv) {
            statusDiv.classList.add('hidden');
        }
    }

    // Get payment method display name
    getPaymentMethodName(method) {
        const names = {
            'cash': 'Cash',
            'card': 'Card',
            'digital_wallet': 'E-Wallet',
            'transfer': 'Bank Transfer'
        };
        return names[method] || method;
    }

    // Validate customer name
    validateCustomerName(input) {
        const value = input.value.trim();
        const isValid = value.length >= 2;

        if (isValid) {
            input.classList.remove('input-error');
            input.classList.add('input-success');
            return true;
        } else if (value.length > 0) {
            input.classList.add('input-error');
            input.classList.remove('input-success');
            return false;
        } else {
            input.classList.remove('input-error', 'input-success');
            return false;
        }
    }

    // Validate phone number (optional but must be valid if provided)
    validatePhoneNumber(input) {
        const value = input.value.trim();
        
        if (!value) {
            input.classList.remove('input-error', 'input-success');
            return true; // Optional field, empty is valid
        }

        // Basic Indonesian phone number validation
        const phoneRegex = /^(\+62|62|0)[\s-]?8[1-9][0-9]{6,10}$/;
        const isValid = phoneRegex.test(value.replace(/\s|-/g, ''));

        if (isValid) {
            input.classList.remove('input-error');
            input.classList.add('input-success');
            return true;
        } else {
            input.classList.add('input-error');
            input.classList.remove('input-success');
            return false;
        }
    }

    // Format Indonesian phone number
    formatPhoneNumber(phone) {
        if (!phone) return '';
        
        // Remove all non-digits
        let cleaned = phone.replace(/\D/g, '');
        
        // Handle different formats
        if (cleaned.startsWith('62')) {
            cleaned = '+' + cleaned;
        } else if (cleaned.startsWith('8') && cleaned.length >= 10) {
            cleaned = '0' + cleaned;
        } else if (!cleaned.startsWith('0') && cleaned.length >= 9) {
            cleaned = '0' + cleaned;
        }
        
        return cleaned;
    }

    setupPageVisibilityHandler() {
        // Sync data when page becomes visible (user switches back to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.loadFromStorage();
                this.updateCartDisplay();
                
                // Update UI setelah load data
                if (this.selectedTable) {
                    this.displaySelectedTableInfo();
                    this.highlightSelectedTable();
                }
                
                // Refresh orders page notification if applicable
                // if (window.location.pathname.includes('/orders') && this.cart.length > 0) {
                //     setTimeout(() => this.syncWithOrdersPage(), 500);
                // }
            }
        });
    }

    // Clear table selection
    clearTableSelection() {
        this.clearTableFromStorage();
        this.saveToStorage(); // Save perubahan ke localStorage
        
        // Reset UI
        document.querySelectorAll('.table-btn').forEach(btn => {
            btn.classList.remove('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white', 'scale-105');
            btn.classList.add('bg-gradient-to-br', 'from-gray-50', 'to-gray-100');
        });
        
        const currentTableInfo = document.getElementById('current-table-info');
        if (currentTableInfo) {
            currentTableInfo.classList.add('hidden');
        }

        showToast('Table selection cleared', 'info');
        this.showTableSelectionModal();
    }

    setupFiltersAndSorting() {
        // Sort options
        const sortSelect = document.getElementById('sort-options');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.currentSort = e.target.value;
                this.applySorting();
            });
        }

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-orange-500', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                
                e.target.classList.add('active', 'bg-orange-500', 'text-white');
                e.target.classList.remove('bg-gray-100', 'text-gray-700');
                
                this.currentFilter = e.target.id.replace('filter-', '');
                this.applyFiltersAndSort();
            });
        });

        // Clear all filters
        const clearAllBtn = document.getElementById('clear-all-filters');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => this.clearAllFilters());
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // ESC to close modals
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
            
            // Ctrl/Cmd + F to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('search-input')?.focus();
            }
            
            // Enter to process order when cart is open
            if (e.key === 'Enter' && !document.getElementById('cart-modal').classList.contains('hidden')) {
                e.preventDefault();
                this.processOrder();
            }
        });
    }

    closeAllModals() {
        this.closeModal();
        this.closeCartModal();
        this.closeCustomerInfoModal();
    }

    setupModalEventListeners() {
        const elements = {
            'close-modal': () => this.closeModal(),
            'close-cart-modal': () => this.closeCartModal(),
            'increase-qty': () => this.changeQuantity(1),
            'decrease-qty': () => this.changeQuantity(-1),
            'add-to-cart-final': () => this.addToCartFinal(),
            'cart-button': () => this.showCartModal(),
            'process-order': () => this.processOrder()
        };

        Object.entries(elements).forEach(([id, handler]) => {
            const element = document.getElementById(id);
            if (element) element.addEventListener('click', handler);
        });
    }

    setupModalCloseListeners() {
        const optionsModal = document.getElementById('options-modal');
        if (optionsModal) {
            // Click outside to close
            optionsModal.addEventListener('click', (e) => {
                if (e.target.id === 'options-modal') {
                    this.closeModal();
                }
            });
        }
    
        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('options-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    this.closeModal();
                }
            }
        });
    }

    setupSearchFunctionality() {
        const searchInput = document.getElementById('search-input');
        const clearSearch = document.getElementById('clear-search');
        const suggestionsContainer = document.getElementById('search-suggestions');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                const query = e.target.value;
                
                this.searchTimeout = setTimeout(() => {
                    this.performSearch(query);
                    this.showSearchSuggestions(query);
                }, 200);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim()) {
                    this.showSearchSuggestions(searchInput.value);
                }
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.hideSearchSuggestions();
                }
            });
        }

        if (clearSearch) {
            clearSearch.addEventListener('click', () => {
                searchInput.value = '';
                this.performSearch('');
                clearSearch.classList.add('hidden');
                this.hideSearchSuggestions();
                searchInput.focus();
            });
        }

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#search-input') && !e.target.closest('#search-suggestions')) {
                this.hideSearchSuggestions();
            }
        });
    }

    showSearchSuggestions(query) {
        const suggestionsContainer = document.getElementById('search-suggestions');
        const suggestionsContent = document.getElementById('suggestions-content');
        
        if (!query.trim() || query.length < 2) {
            this.hideSearchSuggestions();
            return;
        }

        const matchingItems = this.allItems.filter(item => 
            item.name.includes(query.toLowerCase())
        ).slice(0, 5);

        if (matchingItems.length === 0) {
            this.hideSearchSuggestions();
            return;
        }

        suggestionsContent.innerHTML = matchingItems.map(item => `
            <div class="suggestion-item flex items-center p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition-all" data-item-name="${item.name}">
                <i class="fas fa-utensils text-orange-500 mr-3"></i>
                <div class="flex-1">
                    <div class="font-medium text-gray-800">${this.highlightMatch(item.element.querySelector('h3').textContent, query)}</div>
                    <div class="text-sm text-gray-600">${item.element.querySelector('p').textContent.substring(0, 50)}...</div>
                </div>
                <div class="text-orange-600 font-bold">${formatCurrency(item.price)}</div>
            </div>
        `).join('');

        // Add click handlers for suggestions
        suggestionsContent.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                const itemName = item.dataset.itemName;
                document.getElementById('search-input').value = itemName;
                this.performSearch(itemName);
                this.hideSearchSuggestions();
            });
        });

        suggestionsContainer.classList.remove('hidden');
    }

    hideSearchSuggestions() {
        const suggestionsContainer = document.getElementById('search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.classList.add('hidden');
        }
    }

    highlightMatch(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark class="bg-orange-100 text-orange-800">$1</mark>');
    }

    performSearch(query) {
        const searchInput = document.getElementById('search-input');
        const clearSearch = document.getElementById('clear-search');
        const noResults = document.getElementById('no-results');
        const menuItems = document.querySelectorAll('.item-card');

        // Show/hide clear button
        if (query.trim()) {
            clearSearch?.classList.remove('hidden');
        } else {
            clearSearch?.classList.add('hidden');
        }

        const searchQuery = query.toLowerCase().trim();
        let hasResults = false;

        menuItems.forEach(card => {
            const itemName = card.dataset.name || '';
            const itemDescription = card.querySelector('p')?.textContent.toLowerCase() || '';
            const isVisible = !searchQuery || 
                             itemName.includes(searchQuery) || 
                             itemDescription.includes(searchQuery);
            
            if (isVisible && this.passesCurrentFilters(card)) {
                card.style.display = 'block';
                hasResults = true;
                // Add subtle animation
                card.style.animation = 'fadeIn 0.3s ease-in-out';
            } else {
                card.style.display = 'none';
            }
        });

        // Update items count
        this.updateItemsCount(hasResults ? document.querySelectorAll('.item-card[style="display: block;"]').length : 0);

        // Show/hide no results message
        if (noResults) {
            noResults.style.display = searchQuery && !hasResults ? 'block' : 'none';
        }

        // Reset category filter when searching
        if (searchQuery) {
            this.resetCategoryTabs();
            this.currentCategory = 'all';
        }
    }

    passesCurrentFilters(card) {
        if (this.currentCategory !== 'all' && card.dataset.category !== this.currentCategory) {
            return false;
        }

        if (this.currentFilter === 'discount' && card.dataset.discount !== 'true') {
            return false;
        }

        if (this.currentFilter === 'featured' && card.dataset.featured !== 'true') {
            return false;
        }

        return true;
    }

    updateItemsCount(count) {
        const itemsCountEl = document.getElementById('items-count');
        if (itemsCountEl) {
            itemsCountEl.textContent = count;
        }
    }

    resetCategoryTabs() {
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.classList.remove('active');
            tab.classList.add('bg-gradient-to-r', 'from-gray-50', 'to-gray-100', 'text-gray-700');
        });
        
        const allTab = document.querySelector('.category-tab[data-category-id="all"]');
        if (allTab) {
            allTab.classList.add('active');
            allTab.classList.remove('bg-gradient-to-r', 'from-gray-50', 'to-gray-100', 'text-gray-700');
        }
    }

    applySorting() {
        const container = document.getElementById('menu-items-grid');
        const items = Array.from(container.children).filter(child => 
            child.classList.contains('item-card')
        );

        items.sort((a, b) => {
            switch (this.currentSort) {
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'featured':
                    const aFeatured = a.dataset.featured === 'true' ? 1 : 0;
                    const bFeatured = b.dataset.featured === 'true' ? 1 : 0;
                    return bFeatured - aFeatured;
                default:
                    return 0;
            }
        });

        // Reorder items with animation
        items.forEach((item, index) => {
            setTimeout(() => {
                container.appendChild(item);
                item.style.animation = 'fadeIn 0.3s ease-in-out';
            }, index * 50);
        });
    }

    applyFiltersAndSort() {
        const menuItems = document.querySelectorAll('.item-card');
        let visibleCount = 0;

        menuItems.forEach(card => {
            const shouldShow = this.passesCurrentFilters(card);
            
            if (shouldShow) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        this.updateItemsCount(visibleCount);
        this.applySorting();

        // Show no results if needed
        const noResults = document.getElementById('no-results');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    clearAllFilters() {
        // Reset search
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';
        document.getElementById('clear-search')?.classList.add('hidden');

        // Reset filters
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-orange-500', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        document.getElementById('filter-all')?.classList.add('active', 'bg-orange-500', 'text-white');
        document.getElementById('filter-all')?.classList.remove('bg-gray-100', 'text-gray-700');

        // Reset category
        this.resetCategoryTabs();

        // Reset sort
        const sortSelect = document.getElementById('sort-options');
        if (sortSelect) sortSelect.value = 'name';

        // Reset values
        this.currentCategory = 'all';
        this.currentFilter = 'all';
        this.currentSort = 'name';

        // Show all items
        document.querySelectorAll('.item-card').forEach(card => {
            card.style.display = 'block';
        });

        this.updateItemsCount(this.allItems.length);
        this.applySorting();

        // Hide no results
        const noResults = document.getElementById('no-results');
        if (noResults) noResults.style.display = 'none';

        showToast('All filters cleared');
    }

    showTableSelectionModal() {
        const modal = document.getElementById('table-selection-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.querySelector('.bg-white').classList.add('animate-slide-up');
        }
    }

    closeTableSelectionModal() {
        const modal = document.getElementById('table-selection-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.style.opacity = '';
            }, 300);
        }
    }

    selectTable(e) {
        const tableId = e.currentTarget.dataset.tableId;
        const tableNumber = e.currentTarget.dataset.tableNumber;

        // Visual feedback with enhanced animation
        document.querySelectorAll('.table-btn').forEach(btn => {
            btn.classList.remove('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white', 'scale-105');
            btn.classList.add('bg-gradient-to-br', 'from-gray-50', 'to-gray-100');
            btn.style.transform = '';
        });

        e.currentTarget.classList.remove('bg-gradient-to-br', 'from-gray-50', 'to-gray-100');
        e.currentTarget.classList.add('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white', 'scale-105');
        e.currentTarget.style.transform = 'scale(1.1)';

        // Update selected table info and save to storage
        this.selectedTable = { id: tableId, number: tableNumber };
        this.saveToStorage(); // Save immediately after selection
        
        // Show current table info with animation
        this.displaySelectedTableInfo();

        // Hide table selection modal with delay
        setTimeout(() => {
            this.closeTableSelectionModal();
        }, 800);

        showToast(`Table ${tableNumber} selected! Ready to take orders.`);
        showNotification('Table Selected', `Now serving Table ${tableNumber}`);
    }

    filterByCategory(e) {
        const categoryId = e.currentTarget.dataset.categoryId;
        this.currentCategory = categoryId;

        // Update active tab with enhanced animation
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.classList.remove('active');
            tab.classList.add('bg-gradient-to-r', 'from-gray-50', 'to-gray-100', 'text-gray-700');
            tab.style.transform = '';
        });
        
        e.currentTarget.classList.add('active');
        e.currentTarget.classList.remove('bg-gradient-to-r', 'from-gray-50', 'to-gray-100', 'text-gray-700');
        e.currentTarget.style.transform = 'scale(1.05)';

        // Clear search when filtering by category
        const searchInput = document.getElementById('search-input');
        if (searchInput && searchInput.value) {
            searchInput.value = '';
            document.getElementById('clear-search')?.classList.add('hidden');
            this.hideSearchSuggestions();
        }

        // Apply filters and sorting
        this.applyFiltersAndSort();

        showToast(`Showing ${categoryId === 'all' ? 'all items' : 'category: ' + e.currentTarget.textContent.trim()}`);
    }

    addItemToCart(e) {
        if (!this.selectedTable) {
            showToast('Please select a table first', 'error');
            this.showTableSelectionModal();
            return;
        }
    
        const button = e.currentTarget;
        const itemId = button.dataset.itemId;
        const itemName = button.dataset.itemName;
        const itemPrice = parseFloat(button.dataset.itemPrice);
        const hasOptions = button.dataset.hasOptions === 'true';
    
        if (hasOptions) {
            // LANGSUNG buka modal tanpa loading state
            this.showOptionsModalSync(itemId, itemName, itemPrice, button);
        } else {
            this.addSimpleItemToCart(itemId, itemName, itemPrice);
        }
    }

    showOptionsModalSync(itemId, itemName, itemPrice, triggerButton) {
        // Store button reference untuk close handlers
        this.modalTriggerButton = triggerButton;
        
        // Set item data langsung
        this.currentItem = {
            id: itemId,
            name: itemName,
            price: itemPrice,
            quantity: 1,
            selectedOptions: [],
            item: null // Will be loaded
        };
    
        // Update modal content langsung
        document.getElementById('modal-item-name').textContent = itemName;
        document.getElementById('item-quantity').textContent = '1';
        
        // Show modal immediately
        const modal = document.getElementById('options-modal');
        modal.classList.remove('hidden');
        
        // Load options in background
        this.loadOptionsInBackground(itemId);
    }

    async loadOptionsInBackground(itemId) {
        const optionsContainer = document.getElementById('modal-options-content');
        
        // Show loading in options area only
        optionsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-orange-500 border-t-transparent mx-auto mb-4"></div>
                <p class="text-gray-600">Loading options...</p>
            </div>
        `;
        
        try {
            const response = await axios.get(`/cashier/menu-items/${itemId}`);
            const item = response.data;
            
            this.currentItem.item = item;
            this.renderOptionsContent(item.option_categories || []);
            this.updateModalPrice();
        } catch (error) {
            optionsContainer.innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>Failed to load options</p>
                </div>
            `;
        }
    }


    renderOptionsContent(optionCategories) {
        const container = document.getElementById('modal-options-content');
        
        if (!optionCategories || optionCategories.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-3xl">
                    <i class="fas fa-info-circle text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 font-medium">No additional options available</p>
                </div>
            `;
            return;
        }

        container.innerHTML = optionCategories.map(category => `
            <div class="mb-8 p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-3xl border border-gray-200">
                <h4 class="font-bold text-gray-800 mb-6 flex items-center text-lg">
                    <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-list text-white text-sm"></i>
                    </div>
                    ${category.name}
                    ${category.pivot.is_required ? '<span class="text-red-500 text-sm ml-2 bg-red-50 px-2 py-1 rounded-full">*Required</span>' : ''}
                </h4>
                <div class="space-y-4">
                    ${category.options.map(option => `
                        <label class="flex items-center justify-between p-5 bg-white border-2 border-gray-200 rounded-2xl hover:border-orange-300 hover:bg-orange-50 cursor-pointer transition-all group transform hover:scale-[1.02]">
                            <div class="flex items-center">
                                <input type="${category.pivot.is_required ? 'radio' : 'checkbox'}" 
                                       name="option_category_${category.id}" 
                                       value="${option.id}"
                                       data-price="${option.additional_price}"
                                       data-name="${option.name}"
                                       class="option-input w-5 h-5 text-orange-600 mr-4 focus:ring-2 focus:ring-orange-400">
                                <div>
                                    <div class="font-semibold text-gray-800 group-hover:text-orange-600 transition-colors">${option.name}</div>
                                    ${option.description ? `<div class="text-sm text-gray-600 mt-1">${option.description}</div>` : ''}
                                </div>
                            </div>
                            <div class="text-orange-600 font-bold text-lg">
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
                const label = input.closest('label');
                if (input.checked) {
                    label.classList.add('ring-2', 'ring-orange-400', 'bg-orange-50');
                } else {
                    label.classList.remove('ring-2', 'ring-orange-400', 'bg-orange-50');
                }
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
        document.getElementById('item-quantity').textContent = newQty;
        this.updateModalPrice();

        // Add visual feedback
        const quantityEl = document.getElementById('item-quantity');
        quantityEl.style.transform = 'scale(1.2)';
        quantityEl.style.color = '#f97316';
        setTimeout(() => {
            quantityEl.style.transform = '';
            quantityEl.style.color = '';
        }, 200);
    }

    updateModalPrice() {
        const basePrice = this.currentItem.price;
        const optionsPrice = this.currentItem.selectedOptions.reduce((sum, option) => sum + option.price, 0);
        const totalPrice = (basePrice + optionsPrice) * this.currentItem.quantity;
        
        const priceEl = document.getElementById('modal-total-price');
        priceEl.textContent = formatCurrency(totalPrice);
        
        // Add price update animation
        priceEl.style.transform = 'scale(1.1)';
        setTimeout(() => {
            priceEl.style.transform = '';
        }, 200);
    }

    addToCartFinal() {
        const specialInstructions = document.getElementById('special-instructions').value;
        
        const cartItem = {
            id: Date.now(),
            menuItemId: this.currentItem.id,
            name: this.currentItem.name,
            price: this.currentItem.price,
            quantity: this.currentItem.quantity,
            selectedOptions: this.currentItem.selectedOptions,
            specialInstructions: specialInstructions,
            totalPrice: this.calculateItemTotal(this.currentItem)
        };
    
        this.cart.push(cartItem);
        
        // Update UI segera setelah menambah ke cart
        const totalQuantity = this.getTotalQuantityForItem(this.currentItem.id);
        this.updateMenuCardQuantity(this.currentItem.id, totalQuantity); // TAMBAH BARIS INI
        
        this.updateCartDisplay();
        this.saveToStorage();
        
        // RESET BUTTON DULU sebelum close modal
        if (this.pendingButtonReset) {
            this.pendingButtonReset();
            this.pendingButtonReset = null;
        }
        
        this.closeModal();
        
        showToast(`${cartItem.name} added to cart!`);
        showNotification('Item Added', `${cartItem.name} has been added to your cart`);
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
        
        // Update UI segera setelah menambah ke cart
        const totalQuantity = this.getTotalQuantityForItem(itemId);
        this.updateMenuCardQuantity(itemId, totalQuantity); // TAMBAH BARIS INI
        
        this.updateCartDisplay();
        this.saveToStorage();
        
        showToast(`${itemName} added to cart!`);
        showNotification('Item Added', `${itemName} has been added to your cart`);
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
            cartCount.textContent = totalItems > 99 ? '99+' : totalItems;
            
            // Add loading animation briefly
            cartButton.classList.add('cart-loading');
            setTimeout(() => {
                cartButton.classList.remove('cart-loading');
            }, 300);
            
            // Add bounce animation for new items
            cartCount.style.animation = 'none';
            setTimeout(() => {
                cartCount.style.animation = 'heartbeat 1.5s ease-in-out infinite';
            }, 10);
            
            // Update preview if visible
            this.updateCartPreview();
        } else {
            cartButton.classList.add('hidden');
        }
    }

    showCartModal() {
        const modal = document.getElementById('cart-modal');
        if (modal) {
            // PERBAIKAN: Selalu reset modal content saat dibuka
            modal.style.opacity = '';
            modal.style.transform = '';
            modal.classList.remove('hidden');
            
            // Pastikan konten modal di-render ulang setiap kali dibuka
            this.renderCartItems();
            this.updateCartTotals();
            
            // Apply animation
            const modalContent = modal.querySelector('.bg-white');
            if (modalContent) {
                modalContent.classList.add('animate-slide-up');
            }
            
            console.log('Cart modal opened successfully');
        } else {
            console.error('Cart modal element not found');
        }
    }

    // TAMBAHAN: Method untuk memaksa reload modal structure
    forceReloadCartModal() {
        const modal = document.getElementById('cart-modal');
        if (!modal) return;
        
        // Tutup modal dulu
        modal.classList.add('hidden');
        
        // Reset originalCartModalContent
        this.originalCartModalContent = null;
        
        // Reload modal dengan delay
        setTimeout(() => {
            this.showCartModal();
        }, 100);
    }

    renderCartItems() {
        const container = document.getElementById('cart-items');
    
        if (!container) {
            console.warn('Cart items container not found, modal structure may be corrupted');
            this.forceReloadCartModal();
            return;
        }
        
        if (this.cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shopping-cart text-4xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-600 mb-3">Cart is Empty</h3>
                    <p class="text-gray-500 mb-6">Add some delicious items to get started</p>
                    <button onclick="cashier.closeCartModal()" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-2xl hover:from-orange-600 hover:to-orange-700 transition-all">
                        <i class="fas fa-utensils mr-2"></i>
                        Browse Menu
                    </button>
                </div>
            `;
            return;
        }
    
        container.innerHTML = this.cart.map((item, index) => `
            <div class="cart-item-card relative p-6 mb-4">
                <!-- Delete Button -->
                <button class="cart-item-delete" onclick="cashier.removeFromCart(${index})">
                    <i class="fas fa-trash text-sm"></i>
                </button>
                
                <!-- Item Header -->
                <div class="cart-item-info mb-4">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <div class="cart-item-price-breakdown">
                        <i class="fas fa-calculator mr-2"></i>
                        ${formatCurrency(item.price)} × ${item.quantity} = ${formatCurrency(item.totalPrice)}
                    </div>
                </div>
                
                <!-- Options Section -->
                ${item.selectedOptions.length > 0 ? `
                    <div class="cart-item-options">
                        <div class="cart-item-options-header">
                            <i class="fas fa-plus-circle"></i>
                            <span>Added Options</span>
                        </div>
                        <div class="cart-item-options-list">
                            ${item.selectedOptions.map(opt => `
                                <span class="inline-block bg-white px-2 py-1 rounded-lg text-xs font-medium mr-2 mb-1 border border-orange-200">
                                    ${opt.name}${opt.price > 0 ? ` (+${formatCurrency(opt.price)})` : ''}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                <!-- Special Instructions -->
                ${item.specialInstructions ? `
                    <div class="cart-item-instructions">
                        <div class="cart-item-instructions-header">
                            <i class="fas fa-sticky-note"></i>
                            <span>Special Instructions</span>
                        </div>
                        <div class="cart-item-instructions-text">
                            "${item.specialInstructions}"
                        </div>
                    </div>
                ` : ''}
                
                <!-- Quantity and Price Section -->
                <div class="cart-quantity-section mt-4">
                    <div class="cart-quantity-controls">
                        <button class="cart-quantity-btn cart-quantity-decrease" data-cart-index="${index}">
                            <i class="fas fa-minus text-sm"></i>
                        </button>
                        <div class="cart-quantity-display">${item.quantity}</div>
                        <button class="cart-quantity-btn cart-quantity-increase" data-cart-index="${index}">
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                    
                    <div class="cart-price-section">
                        <div class="cart-item-total">${formatCurrency(item.totalPrice)}</div>
                        <div class="cart-item-unit-price">
                            ${formatCurrency(item.totalPrice / item.quantity)} per item
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    
        // Setup event listeners dengan loading animation
        this.setupCartQuantityListeners();
    }
    
    setupCartQuantityListeners() {
        // Remove existing listeners to prevent duplicates
        document.querySelectorAll('.cart-quantity-decrease').forEach(btn => {
            btn.removeEventListener('click', this.handleCartDecrease);
        });
        document.querySelectorAll('.cart-quantity-increase').forEach(btn => {
            btn.removeEventListener('click', this.handleCartIncrease);
        });
    
        // Add new listeners dengan loading animation
        document.querySelectorAll('.cart-quantity-decrease').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const button = e.currentTarget;
                const index = parseInt(button.dataset.cartIndex);
                
                // Add loading state
                this.addQuantityLoadingState(button);
                
                setTimeout(() => {
                    this.updateCartItemQuantity(index, -1);
                    this.removeQuantityLoadingState(button);
                }, 200);
            });
        });
    
        document.querySelectorAll('.cart-quantity-increase').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const button = e.currentTarget;
                const index = parseInt(button.dataset.cartIndex);
                
                // Add loading state
                this.addQuantityLoadingState(button);
                
                setTimeout(() => {
                    this.updateCartItemQuantity(index, 1);
                    this.removeQuantityLoadingState(button);
                }, 200);
            });
        });
    }
    
    // Method untuk loading animation
    addQuantityLoadingState(button) {
        const controls = button.closest('.cart-quantity-controls');
        if (controls) {
            controls.classList.add('cart-quantity-loading');
            button.style.pointerEvents = 'none';
        }
    }
    
    removeQuantityLoadingState(button) {
        const controls = button.closest('.cart-quantity-controls');
        if (controls) {
            controls.classList.remove('cart-quantity-loading');
            button.style.pointerEvents = 'auto';
        }
    }

    // Method untuk animasi quantity update
    animateQuantityChange(element) {
        element.style.transform = 'scale(1.2)';
        element.style.background = 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)';
        
        setTimeout(() => {
            element.style.transform = '';
            element.style.background = '';
        }, 300);
    }

    updateCartTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + item.totalPrice, 0);
        const taxRate = 0.11;
        const serviceRate = 0.05;
        
        let couponAmount = 0;
        let discountAmount = 0;
        
        if (this.appliedCoupon) {
            couponAmount = this.appliedCoupon.discount_amount;
        }
        
        if (this.appliedDiscount) {
            discountAmount = this.appliedDiscount.discount_amount;
        }
        
        const tax = subtotal * taxRate;
        const service = subtotal * serviceRate;
        const total = subtotal + tax + service - couponAmount - discountAmount;
    
        document.getElementById('cart-subtotal').textContent = formatCurrency(subtotal);
        
        // Show/hide coupon row
        const couponRow = document.getElementById('coupon-row');
        const cartCoupon = document.getElementById('cart-coupon');
        if (couponAmount > 0 && couponRow && cartCoupon) {
            couponRow.classList.remove('hidden');
            cartCoupon.textContent = '- ' + formatCurrency(couponAmount);
        } else if (couponRow) {
            couponRow.classList.add('hidden');
        }
        
        // Show/hide discount row
        const discountRow = document.getElementById('discount-row');
        const cartDiscount = document.getElementById('cart-discount');
        if (discountAmount > 0 && discountRow && cartDiscount) {
            discountRow.classList.remove('hidden');
            cartDiscount.textContent = '- ' + formatCurrency(discountAmount);
        } else if (discountRow) {
            discountRow.classList.add('hidden');
        }
        
        document.getElementById('cart-tax').textContent = formatCurrency(tax);
        document.getElementById('cart-service').textContent = formatCurrency(service);
        document.getElementById('cart-total').textContent = formatCurrency(total);
    }

    removeFromCart(index) {
        const item = this.cart[index];
        
        // Add removal animation
        const cartItems = document.getElementById('cart-items');
        const itemElement = cartItems.children[index];
        if (itemElement) {
            itemElement.style.transform = 'translateX(-100%) scale(0.8)';
            itemElement.style.opacity = '0';
            
            setTimeout(() => {
                this.cart.splice(index, 1);
                this.updateCartDisplay();
                this.saveToStorage(); 
                this.renderCartItems();
                this.updateCartTotals();
                
                showToast(`${item.name} removed from cart`, 'error');
                
                if (this.cart.length === 0) {
                    setTimeout(() => this.closeCartModal(), 1500);
                }
            }, 300);
        } else {
            this.cart.splice(index, 1);
            this.updateCartDisplay();
            this.saveToStorage(); 
            this.renderCartItems();
            this.updateCartTotals();
        }
    }

    // Enhanced customer info validation
    validateCustomerInfo(useModalFields = false) {
        let customerName, customerPhone;
        
        if (useModalFields) {
            // Use modal fields when validating from modal
            customerName = document.getElementById('modal-customer-name')?.value?.trim() || '';
            customerPhone = document.getElementById('modal-customer-phone')?.value?.trim() || '';
        } else {
            // Use main form fields - dengan fallback ke customerInfo jika field kosong
            const nameField = document.getElementById('customer-name');
            const phoneField = document.getElementById('customer-phone');
            
            customerName = nameField?.value?.trim() || this.customerInfo.name || '';
            customerPhone = phoneField?.value?.trim() || this.customerInfo.phone || '';
        }
        
        // Customer name is required
        if (!customerName || customerName.length < 2) {
            return {
                isValid: false,
                error: 'Customer name is required (minimum 2 characters)'
            };
        }
        
        // Phone is optional, but if provided should be valid
        if (customerPhone) {
            const phoneRegex = /^(\+62|62|0)[\s-]?8[1-9][0-9]{6,10}$/;
            if (!phoneRegex.test(customerPhone.replace(/\s|-/g, ''))) {
                return {
                    isValid: false,
                    error: 'Please enter a valid Indonesian phone number'
                };
            }
        }
        
        return {
            isValid: true,
            name: customerName,
            phone: customerPhone
        };
    }

    // Close customer info modal
    closeCustomerInfoModal() {
        const modal = document.getElementById('customer-info-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    }

    // Enhanced process order with customer validation
    async processOrder() {
        // PERBAIKAN: Immediate button disable dan visual feedback
        const processButton = document.getElementById('process-order');
        if (processButton) {
            // Check jika sudah disabled (prevent multiple clicks)
            if (processButton.disabled) {
                console.log('Button already disabled, preventing duplicate request');
                return;
            }
            
            processButton.disabled = true;
            processButton.classList.add('opacity-50', 'cursor-not-allowed');
            processButton.innerHTML = `
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Processing Order...
            `;
        }

        // Tambahkan overlay loading ke modal untuk prevent interaksi
        const modal = document.getElementById('cart-modal');
        const overlay = document.createElement('div');
        overlay.id = 'processing-overlay';
        overlay.className = 'absolute inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center rounded-3xl';
        overlay.innerHTML = `
            <div class="bg-white rounded-2xl p-6 shadow-2xl">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-2 border-orange-500 border-t-transparent"></div>
                    <span class="font-semibold text-gray-700">Processing your order...</span>
                </div>
            </div>
        `;
        
        if (modal) {
            const modalContent = modal.querySelector('.bg-white');
            if (modalContent) {
                modalContent.style.position = 'relative';
                modalContent.appendChild(overlay);
            }
        }

        try {
            // Check basic requirements
            if (!this.selectedTable) {
                this.resetProcessButton();
                this.removeProcessingOverlay();
                showToast('Please select a table first', 'error');
                this.showTableSelectionModal();
                return;
            }

            if (this.cart.length === 0) {
                this.resetProcessButton();
                this.removeProcessingOverlay();
                showToast('Cart is empty', 'error');
                return;
            }

            // Validasi customer info dengan warning saja
            const customerNameField = document.getElementById('customer-name');
            const customerName = customerNameField?.value?.trim() || this.customerInfo.name || '';
            
            if (!customerName || customerName.length < 2) {
                this.resetProcessButton();
                this.removeProcessingOverlay();
                
                if (customerNameField) {
                    customerNameField.classList.add('input-error');
                    customerNameField.focus();
                    customerNameField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                showToast('Please fill in customer name (minimum 2 characters) before processing order', 'error');
                return;
            }

            // Update customer info dari main form
            const customerPhoneField = document.getElementById('customer-phone');
            const customerPhone = customerPhoneField?.value?.trim() || this.customerInfo.phone || '';
            
            const selectedPaymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value || this.customerInfo.paymentMethod || 'cash';
            
            this.updateCustomerInfo(customerName, customerPhone, selectedPaymentMethod);
            
            if (customerNameField) {
                customerNameField.classList.remove('input-error');
                customerNameField.classList.add('input-success');
            }
            
            // Process order
            await this.processOrderWithValidation();
            
        } catch (error) {
            console.error('Error in processOrder:', error);
            this.resetProcessButton();
            this.removeProcessingOverlay();
            showToast('Failed to process order. Please try again.', 'error');
        }
    }

    // Method ini harus ada di class Anda
    resetProcessButton() {
        const processButton = document.getElementById('process-order');
        if (processButton) {
            processButton.disabled = false;
            processButton.classList.remove('opacity-50', 'cursor-not-allowed');
            processButton.innerHTML = '<i class="fas fa-check mr-2"></i>Process Order';
        }
    }

    removeProcessingOverlay() {
        const overlay = document.getElementById('processing-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    async processOrderWithValidation() {
        if (this.isProcessingOrder) {
            console.log('Order already being processed, ignoring duplicate');
            return;
        }
        
        this.isProcessingOrder = true;
    
        const orderData = {
            table_id: this.selectedTable.id,
            customer_name: this.customerInfo.name,
            customer_phone: this.customerInfo.phone,
            payment_method: this.customerInfo.paymentMethod,
            coupon_id: this.appliedCoupon?.id || null,
            discount_id: this.appliedDiscount?.id || null,
            items: this.cart.map(item => ({
                menu_item_id: item.menuItemId,
                quantity: item.quantity,
                options: item.selectedOptions.map(opt => opt.id),
                special_instructions: item.specialInstructions
            }))
        };
    
        console.log('Sending order data:', orderData);
    
        try {
            const response = await axios.post('/cashier/orders', orderData);
            
            if (response.data.success) {
                // Handle duplicate prevention response
                if (response.data.duplicate_prevented) {
                    showToast('Order was already created successfully!', 'info');
                } else {
                    showToast('Order created successfully!');
                }
                
                showNotification('Order Placed', `Order for Table ${this.selectedTable.number} has been created`);
                
                // Show success animation
                this.showOrderSuccessAnimation();
                
                // Reset everything after animation
                setTimeout(() => {
                    this.resetOrder();
                }, 2000);
                
                console.log('Order created:', response.data.order);
            } else {
                throw new Error(response.data.message || 'Failed to create order');
            }
        } catch (error) {
            this.isProcessingOrder = false;
            this.resetProcessButton();
            this.removeProcessingOverlay();
            
            console.error('Error creating order:', error);
            
            if (error.response && error.response.status === 422) {
                const errors = error.response.data.errors;
                const errorMessage = Object.values(errors).flat().join(', ');
                showToast('Validation error: ' + errorMessage, 'error');
            } else {
                showToast('Failed to create order. Please try again.', 'error');
            }
        }
    }

    showOrderSuccessAnimation() {
        const modal = document.getElementById('cart-modal');
        const content = modal.querySelector('.bg-white');
        
        // PERBAIKAN: Buat overlay success di atas modal alih-alih replace innerHTML
        const successOverlay = document.createElement('div');
        successOverlay.id = 'success-overlay';
        successOverlay.className = 'absolute inset-0 bg-white z-10 rounded-3xl';
        successOverlay.style.position = 'absolute';
        successOverlay.style.zIndex = '10';
        
        // Get payment method display text
        const paymentMethods = {
            'cash': '💵 Cash',
            'card': '💳 Card',
            'digital_wallet': '📱 Digital Wallet',
            'transfer': '🏦 Bank Transfer'
        };
        
        successOverlay.innerHTML = `
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce-gentle">
                    <i class="fas fa-check text-white text-4xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Order Placed!</h2>
                <p class="text-gray-600 text-lg mb-4">Your order has been successfully created</p>
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-4 mb-6">
                    <div class="flex items-center justify-center space-x-3 text-green-700">
                        <i class="fas fa-user"></i>
                        <span class="font-semibold">${this.customerInfo.name}</span>
                    </div>
                    ${this.customerInfo.phone ? `
                        <div class="flex items-center justify-center space-x-3 text-green-600 mt-2">
                            <i class="fas fa-phone"></i>
                            <span>${this.customerInfo.phone}</span>
                        </div>
                    ` : ''}
                    <div class="flex items-center justify-center space-x-3 text-green-600 mt-2">
                        <span>${paymentMethods[this.customerInfo.paymentMethod] || '💵 Cash'}</span>
                    </div>
                </div>
                <div class="flex items-center justify-center space-x-2 text-green-600">
                    <i class="fas fa-clock"></i>
                    <span>Preparing your order...</span>
                </div>
            </div>
        `;
        
        // Tambahkan overlay ke modal (bukan replace innerHTML)
        content.style.position = 'relative';
        content.appendChild(successOverlay);
        
        // Auto remove overlay setelah 2 detik
        setTimeout(() => {
            if (successOverlay && successOverlay.parentNode) {
                successOverlay.remove();
            }
        }, 2000);
    }

    resetOrder() {
        // Reset processing states
        this.isProcessingOrder = false;
        this.resetProcessButton();
        this.removeProcessingOverlay();
        
        // PERBAIKAN: Bersihkan success overlay jika ada
        const successOverlay = document.getElementById('success-overlay');
        if (successOverlay) {
            successOverlay.remove();
        }
        
        // PERBAIKAN: Tutup modal dengan method yang konsisten
        this.closeCartModal();
        this.closeAllModals();
        
        // Clear data dari localStorage
        this.clearAllStorage();
        
        // Reset cart, table selection, dan customer info
        this.cart = [];
        this.selectedTable = null;
        this.customerInfo = { name: '', phone: '', paymentMethod: 'cash' };
        this.appliedCoupon = null;
        this.appliedDiscount = null;

        // Reset all menu card quantities
        this.itemQuantities.clear();
        document.querySelectorAll('.quantity-controls').forEach(control => {
            control.style.display = 'none';
        });
        document.querySelectorAll('.add-item-btn').forEach(btn => {
            btn.classList.remove('hidden');
        });
        
        // Reset form fields
        this.resetFormFields();
        this.updateCartDisplay();
        
        // Reset customer fields
        const customerNameField = document.getElementById('customer-name');
        const customerPhoneField = document.getElementById('customer-phone');
        if (customerNameField) {
            customerNameField.value = '';
            customerNameField.classList.remove('input-error', 'input-success');
        }
        if (customerPhoneField) {
            customerPhoneField.value = '';
            customerPhoneField.classList.remove('input-error', 'input-success');
        }
        
        // Reset payment method
        const paymentRadios = document.querySelectorAll('input[name="payment-method"]');
        paymentRadios.forEach(radio => {
            radio.checked = false; // TAMBAHAN: Reset checked state
            const option = radio.closest('.payment-method-option');
            if (option) {
                option.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'border-blue-400');
                option.classList.add('border-blue-200');
            }
        });
        
        // Set default payment method
        const defaultPayment = document.getElementById('payment-cash');
        if (defaultPayment) {
            defaultPayment.checked = true;
            const option = defaultPayment.closest('.payment-method-option');
            if (option) {
                option.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'border-blue-400');
                option.classList.remove('border-blue-200');
            }
        }
        
        // Reset coupon and discount fields
        this.resetPromotionFields();
        
        // Reset table selection UI
        document.querySelectorAll('.table-btn').forEach(btn => {
            btn.classList.remove('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white', 'scale-105');
            btn.classList.add('bg-gradient-to-br', 'from-gray-50', 'to-gray-100');
            btn.style.transform = '';
        });
        
        // PERBAIKAN: Gunakan method yang konsisten untuk hide table info
        const currentTableInfo = document.getElementById('current-table-info');
        if (currentTableInfo && !currentTableInfo.classList.contains('hidden')) {
            currentTableInfo.style.animation = 'fadeOut 0.3s ease-in-out';
            setTimeout(() => {
                currentTableInfo.classList.add('hidden');
                currentTableInfo.style.animation = '';
            }, 300);
        }
        
        // Show table selection modal again
        setTimeout(() => {
            this.showTableSelectionModal();
        }, 1000);
    }

    // Tambahkan method helper untuk reset form
    resetFormFields() {
        // Reset coupon fields
        const couponCode = document.getElementById('coupon-code');
        if (couponCode) couponCode.value = '';
        this.hideCouponStatus();
        
        // Reset discount fields
        const discountCode = document.getElementById('discount-code');
        if (discountCode) discountCode.value = '';
        this.hideDiscountStatus();
    }

    resetPromotionFields() {
        // Reset coupon
        const couponCode = document.getElementById('coupon-code');
        if (couponCode) couponCode.value = '';
        this.hideCouponStatus();
        
        // Reset discount
        const discountCode = document.getElementById('discount-code');
        if (discountCode) discountCode.value = '';
        this.hideDiscountStatus();
        
        // Reset applied promotions
        this.appliedCoupon = null;
        this.appliedDiscount = null;
    }

    closeModal() {
        const modal = document.getElementById('options-modal');
        if (modal) {
            modal.classList.add('hidden');
            
            // Reset form
            const specialInstructionsField = document.getElementById('special-instructions');
            if (specialInstructionsField) {
                specialInstructionsField.value = '';
            }
            
            // Clear references
            this.currentItem = null;
            this.modalTriggerButton = null;
        }
    }

    closeCartModal() {
        const modal = document.getElementById('cart-modal');
        if (modal && !modal.classList.contains('hidden')) {
            modal.style.opacity = '0';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.style.opacity = '';
                modal.style.transform = '';
                
                console.log('Cart modal closed and reset');
            }, 200);
        }
    }
}

// Utility function for CSS animations
function addFadeOutAnimation() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    `;
    document.head.appendChild(style);
}

// Initialize the cashier system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    addFadeOutAnimation();
    window.cashier = new CashierSystem();
});

// Global instance for external access
if (typeof window !== 'undefined') {
    window.cashier = new CashierSystem();
}