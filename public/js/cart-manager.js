/**
 * Cart Manager - Handles cart operations, validation, and order processing
 * File: public/js/cart-manager.js
 */

class CartManager {
    constructor() {
        this.config = {
            TAX_RATE: 0.11,
            SERVICE_CHARGE_RATE: 0.05,
            STORAGE_KEYS: {
                CART: 'cashier_cart',
                TABLE: 'cashier_selected_table'
            }
        };
        
        this.state = {
            cart: [],
            selectedTable: null,
            customerInfo: {
                name: '',
                phone: '',
                paymentMethod: 'cash'
            },
            // TAMBAHKAN INI
            coupon: {
                code: null,
                id: null,
                name: null,
                discountAmount: 0,
                isApplied: false
            }
        };

        this.elements = {};
        this.init();
    }

    /**
     * Initialize the cart manager
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.loadInitialData();
    }

    /**
     * Cache DOM elements for performance
     */
    cacheElements() {
        this.elements = {
            // Cart elements
            cartContainer: document.getElementById('cart-items-container'),
            emptyCartMessage: document.getElementById('empty-cart-message'),
            
            // Summary elements
            subtotalAmount: document.getElementById('subtotal-amount'),
            taxAmount: document.getElementById('tax-amount'),
            serviceChargeAmount: document.getElementById('service-charge-amount'),
            totalAmount: document.getElementById('total-amount'),
            
            // Form elements
            customerForm: document.getElementById('customer-form'),
            customerName: document.getElementById('customer-name'),
            customerPhone: document.getElementById('customer-phone'),
            paymentMethods: document.getElementById('payment-methods'),
            
            // Button elements
            checkoutButton: document.getElementById('checkout-button'),
            changeTableBtn: document.getElementById('change-table-btn'),
            clearTableBtn: document.getElementById('clear-table-btn'),
            
            // Table display
            currentTableInfo: document.getElementById('current-table-info'),
            currentTableNumber: document.getElementById('current-table-number'),
            
            // Modals
            tableSelectionModal: document.getElementById('table-selection-modal'),
            validationModal: document.getElementById('validation-modal'),
            loadingModal: document.getElementById('loading-modal'),
            successModal: document.getElementById('success-modal'),
            
            // Modal content
            validationErrors: document.getElementById('validation-errors'),
            successMessage: document.getElementById('success-message'),
            
            // Containers
            notificationsContainer: document.getElementById('notifications-container'),

            // Coupon elements
            couponCode: document.getElementById('coupon-code'),
            applyCouponBtn: document.getElementById('apply-coupon-btn'),
            removeCouponBtn: document.getElementById('remove-coupon-btn'),
            couponStatus: document.getElementById('coupon-status'),
            appliedCoupon: document.getElementById('applied-coupon'),
            couponName: document.getElementById('coupon-name'),
            couponDiscountRow: document.getElementById('coupon-discount-row'),
            couponDiscountAmount: document.getElementById('coupon-discount-amount'),
        };
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Form events
        this.elements.customerName?.addEventListener('input', (e) => this.updateCustomerInfo('name', e.target.value));
        this.elements.customerPhone?.addEventListener('input', (e) => this.updateCustomerInfo('phone', e.target.value));
        
        // Quick fill buttons
        document.querySelectorAll('.quick-fill-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleQuickFill(e));
        });

        // Payment method selection
        this.elements.paymentMethods?.addEventListener('change', (e) => this.handlePaymentMethodChange(e));

        // Action buttons
        this.elements.checkoutButton?.addEventListener('click', () => this.processOrder());
        this.elements.changeTableBtn?.addEventListener('click', () => this.showTableSelectionModal());
        this.elements.clearTableBtn?.addEventListener('click', () => this.clearTable());

        // Table selection
        document.querySelectorAll('.table-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleTableSelection(e));
        });

        // Modal events
        document.getElementById('close-validation-modal')?.addEventListener('click', () => this.hideModal('validation'));
        document.getElementById('close-success-modal')?.addEventListener('click', () => this.hideModal('success'));

        // Close modal on backdrop click
        this.elements.tableSelectionModal?.addEventListener('click', (e) => {
            if (e.target === this.elements.tableSelectionModal) {
                this.hideModal('tableSelection');
            }
        });

        // Coupon events
        this.elements.applyCouponBtn?.addEventListener('click', () => this.applyCoupon());
        this.elements.removeCouponBtn?.addEventListener('click', () => this.removeCoupon());
        this.elements.couponCode?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.applyCoupon();
            }
        });

        // Table management for all screen sizes
        this.elements.changeTableBtn?.addEventListener('click', () => this.showTableSelectionModal());
        this.elements.clearTableBtn?.addEventListener('click', () => this.clearTable());

        // Tablet buttons
        document.getElementById('change-table-btn-tablet')?.addEventListener('click', () => this.showTableSelectionModal());
        document.getElementById('clear-table-btn-tablet')?.addEventListener('click', () => this.clearTable());

        // Desktop buttons  
        document.getElementById('change-table-btn-desktop')?.addEventListener('click', () => this.showTableSelectionModal());
        document.getElementById('clear-table-btn-desktop')?.addEventListener('click', () => this.clearTable());

        // ESC key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideAllModals();
            }
        });
    }

    /**
     * Load initial data from localStorage
     */
    loadInitialData() {
        this.loadCartFromStorage();
        this.loadTableFromStorage();
        this.renderCart();
        this.updateTableDisplay();
        this.updatePaymentMethodUI('cash'); // Default selection
    }

    /* ================================
       STORAGE OPERATIONS
    ================================ */

    saveCartToStorage() {
        localStorage.setItem(this.config.STORAGE_KEYS.CART, JSON.stringify(this.state.cart));
    }

    loadCartFromStorage() {
        try {
            const cart = localStorage.getItem(this.config.STORAGE_KEYS.CART);
            this.state.cart = cart ? JSON.parse(cart) : [];
        } catch (error) {
            console.error('Error loading cart from storage:', error);
            this.state.cart = [];
        }
    }

    saveTableToStorage() {
        if (this.state.selectedTable) {
            localStorage.setItem(this.config.STORAGE_KEYS.TABLE, JSON.stringify(this.state.selectedTable));
        } else {
            localStorage.removeItem(this.config.STORAGE_KEYS.TABLE);
        }
    }

    loadTableFromStorage() {
        try {
            const table = localStorage.getItem(this.config.STORAGE_KEYS.TABLE);
            this.state.selectedTable = table ? JSON.parse(table) : null;
        } catch (error) {
            console.error('Error loading table from storage:', error);
            this.state.selectedTable = null;
        }
    }


    /* ================================
    COUPON MANAGEMENT
    ================================ */

    async applyCoupon() {
        const couponCode = this.elements.couponCode?.value.trim().toUpperCase();
        
        if (!couponCode) {
            this.showCouponStatus('Please enter a coupon code', 'error');
            return;
        }

        if (this.state.cart.length === 0) {
            this.showCouponStatus('Add items to cart first', 'error');
            return;
        }

        const totals = this.calculateTotals();
        
        this.elements.applyCouponBtn.disabled = true;
        this.elements.applyCouponBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';

        try {
            const response = await fetch('/cashier/validate-coupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    coupon_code: couponCode,
                    order_amount: totals.subtotal
                })
            });

            const data = await response.json();

            if (data.success) {
                this.state.coupon = {
                    code: couponCode,
                    id: data.coupon.id,
                    name: data.coupon.name,
                    discountAmount: data.discount_amount,
                    isApplied: true
                };
                
                this.showCouponApplied();
                this.updateOrderSummary();
                this.showNotification(`Coupon "${data.coupon.name}" applied!`, 'success');
            } else {
                this.showCouponStatus(data.message, 'error');
            }
        } catch (error) {
            console.error('Error validating coupon:', error);
            this.showCouponStatus('Failed to validate coupon', 'error');
        } finally {
            this.elements.applyCouponBtn.disabled = false;
            this.elements.applyCouponBtn.innerHTML = 'Apply';
        }
    }

    removeCoupon() {
        this.state.coupon = {
            code: null,
            id: null,
            name: null,
            discountAmount: 0,
            isApplied: false
        };
        
        this.elements.couponCode.value = '';
        this.hideCouponApplied();
        this.updateOrderSummary();
        this.showNotification('Coupon removed', 'info');
    }

    showCouponStatus(message, type) {
        const statusElement = this.elements.couponStatus;
        if (!statusElement) return;

        const typeClasses = {
            'success': 'text-green-600',
            'error': 'text-red-600',
            'info': 'text-blue-600'
        };

        statusElement.textContent = message;
        statusElement.className = `mt-2 text-xs ${typeClasses[type]} block`;
        statusElement.classList.remove('hidden');

        setTimeout(() => {
            if (type !== 'success') {
                statusElement.classList.add('hidden');
            }
        }, 3000);
    }

    showCouponApplied() {
        this.elements.couponName.textContent = this.state.coupon.name;
        this.elements.appliedCoupon?.classList.remove('hidden');
        this.elements.couponCode.disabled = true;
        this.elements.applyCouponBtn.style.display = 'none';
        this.elements.couponStatus?.classList.add('hidden');
    }

    hideCouponApplied() {
        this.elements.appliedCoupon?.classList.add('hidden');
        this.elements.couponCode.disabled = false;
        this.elements.applyCouponBtn.style.display = 'block';
    }

    /* ================================
       CALCULATION UTILITIES
    ================================ */

    formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    calculateItemTotalPrice(item) {
        const basePrice = parseFloat(item.price) || 0;
        const optionsPrice = (item.selectedOptions || [])
            .reduce((acc, option) => acc + (parseFloat(option.price) || 0), 0);
        return (basePrice + optionsPrice) * (item.quantity || 1);
    }

    calculateTotals() {
        const subtotal = this.state.cart.reduce((acc, item) => acc + this.calculateItemTotalPrice(item), 0);
        const tax = subtotal * this.config.TAX_RATE;
        const serviceCharge = subtotal * this.config.SERVICE_CHARGE_RATE;
        
        // TAMBAHKAN INI
        const couponDiscount = this.state.coupon.isApplied ? this.state.coupon.discountAmount : 0;
        const total = subtotal + tax + serviceCharge - couponDiscount;
        
        return { subtotal, tax, serviceCharge, couponDiscount, total };
    }

    /* ================================
       CART MANAGEMENT
    ================================ */

    updateQuantity(itemId, newQuantity) {
        const itemIndex = this.state.cart.findIndex(item => item.id == itemId);
        
        if (itemIndex === -1) return;

        if (newQuantity <= 0) {
            this.removeItem(itemId);
        } else {
            this.state.cart[itemIndex].quantity = newQuantity;
            this.saveCartToStorage();
            this.renderCart();
        }
    }

    removeItem(itemId) {
        const initialLength = this.state.cart.length;
        this.state.cart = this.state.cart.filter(item => item.id != itemId);
        
        if (this.state.cart.length < initialLength) {
            this.saveCartToStorage();
            this.renderCart();
            this.showNotification('Item removed from cart', 'info');
        }
    }

    renderCart() {
        if (!this.elements.cartContainer) return;

        this.elements.cartContainer.innerHTML = '';
        
        if (this.state.cart.length === 0) {
            this.showEmptyCart();
        } else {
            this.showCartItems();
            this.updateOrderSummary();
        }

        this.attachCartEventListeners();
    }

    showEmptyCart() {
        this.elements.emptyCartMessage?.classList.remove('hidden');
        this.elements.checkoutButton?.setAttribute('disabled', 'true');
        this.updateOrderSummary(); // Still update to show zeros
    }

    showCartItems() {
        this.elements.emptyCartMessage?.classList.add('hidden');
        this.elements.checkoutButton?.removeAttribute('disabled');
        
        const cartHTML = this.state.cart.map(item => this.createCartItemHTML(item)).join('');
        this.elements.cartContainer.innerHTML = cartHTML;
    }

    createCartItemHTML(item) {
        const itemTotal = this.calculateItemTotalPrice(item);
        const optionsHTML = (item.selectedOptions || [])
            .map(option => `<span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs mr-1 mb-1">${option.name} (+${this.formatCurrency(option.price)})</span>`)
            .join('');

        return `
            <div class="bg-white rounded-3xl shadow-soft p-6 border border-gray-100 hover:shadow-hover transition-all" data-item-id="${item.id}">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center flex-1">
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-400 to-orange-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-utensils text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-lg text-gray-800 mb-1">${item.name}</h4>
                            <p class="text-gray-600 text-sm mb-2">${this.formatCurrency(item.price)} each</p>
                            ${optionsHTML ? `<div class="mb-2">${optionsHTML}</div>` : ''}
                            ${item.specialInstructions ? `<p class="text-gray-500 text-xs italic">"${item.specialInstructions}"</p>` : ''}
                        </div>
                    </div>
                    <button class="remove-item-btn text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-xl transition-all" data-item-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center bg-gray-100 rounded-2xl p-1">
                        <button class="quantity-btn decrease-qty w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-xl transition-all shadow-sm" data-item-id="${item.id}">
                            <i class="fas fa-minus text-sm"></i>
                        </button>
                        <span class="quantity-display px-6 font-bold text-lg text-gray-800">${item.quantity}</span>
                        <button class="quantity-btn increase-qty w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-xl transition-all shadow-sm" data-item-id="${item.id}">
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                    <div class="font-bold text-xl text-purple-600">${this.formatCurrency(itemTotal)}</div>
                </div>
            </div>
        `;
    }

    attachCartEventListeners() {
        // Quantity buttons
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const itemId = e.currentTarget.dataset.itemId;
                const isIncrease = e.currentTarget.classList.contains('increase-qty');
                const currentItem = this.state.cart.find(item => item.id == itemId);
                
                if (currentItem) {
                    const newQuantity = isIncrease ? currentItem.quantity + 1 : currentItem.quantity - 1;
                    this.updateQuantity(itemId, newQuantity);
                }
            });
        });

        // Remove buttons
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const itemId = e.currentTarget.dataset.itemId;
                this.removeItem(itemId);
            });
        });
    }

    updateOrderSummary() {
        const totals = this.calculateTotals();
        
        if (this.elements.subtotalAmount) this.elements.subtotalAmount.textContent = this.formatCurrency(totals.subtotal);
        if (this.elements.taxAmount) this.elements.taxAmount.textContent = this.formatCurrency(totals.tax);
        if (this.elements.serviceChargeAmount) this.elements.serviceChargeAmount.textContent = this.formatCurrency(totals.serviceCharge);
        if (this.elements.totalAmount) this.elements.totalAmount.textContent = this.formatCurrency(totals.total);
        
        // TAMBAHKAN INI
        if (totals.couponDiscount > 0) {
            this.elements.couponDiscountRow?.classList.remove('hidden');
            if (this.elements.couponDiscountAmount) {
                this.elements.couponDiscountAmount.textContent = '-' + this.formatCurrency(totals.couponDiscount);
            }
        } else {
            this.elements.couponDiscountRow?.classList.add('hidden');
        }
    }

    /* ================================
       CUSTOMER INFO MANAGEMENT
    ================================ */

    updateCustomerInfo(field, value) {
        this.state.customerInfo[field] = value;
    }

    handleQuickFill(e) {
        const value = e.target.dataset.value;
        if (this.elements.customerName) {
            this.elements.customerName.value = value;
            this.updateCustomerInfo('name', value);
        }
    }

    handlePaymentMethodChange(e) {
        const paymentMethod = e.target.value;
        this.state.customerInfo.paymentMethod = paymentMethod;
        this.updatePaymentMethodUI(paymentMethod);
    }

    updatePaymentMethodUI(selectedMethod) {
        document.querySelectorAll('.payment-method-option').forEach(option => {
            const input = option.querySelector('input');
            const container = option.querySelector('div');
            
            if (input.value === selectedMethod) {
                input.checked = true;
                container.classList.add('border-blue-400', 'bg-blue-50');
                container.classList.remove('border-gray-200');
            } else {
                input.checked = false;
                container.classList.remove('border-blue-400', 'bg-blue-50');
                container.classList.add('border-gray-200');
            }
        });
    }

    validateCustomerInfo() {
        const errors = [];
        const name = this.elements.customerName?.value.trim() || '';
        const phone = this.elements.customerPhone?.value.trim() || '';

        if (!name) {
            errors.push('Customer name is required');
        }

        if (name.length < 2) {
            errors.push('Customer name must be at least 2 characters');
        }

        if (phone && !/^[0-9+\-\s()]+$/.test(phone)) {
            errors.push('Please enter a valid phone number');
        }

        return {
            isValid: errors.length === 0,
            errors: errors,
            name: name,
            phone: phone
        };
    }

    /* ================================
       TABLE MANAGEMENT
    ================================ */

    handleTableSelection(e) {
        const tableId = e.currentTarget.dataset.tableId;
        const tableNumber = e.currentTarget.dataset.tableNumber;
        
        this.state.selectedTable = {
            id: tableId,
            number: tableNumber
        };
        
        this.saveTableToStorage();
        this.updateTableDisplay();
        this.hideModal('tableSelection');
        this.showNotification(`Table ${tableNumber} selected`, 'success');
    }

    updateTableDisplay() {
        if (this.state.selectedTable) {
            this.elements.currentTableInfo?.classList.remove('hidden');
            
            // Update all table number elements for different screen sizes
            const tableNumber = this.state.selectedTable.number;
            if (this.elements.currentTableNumber) {
                this.elements.currentTableNumber.textContent = tableNumber;
            }
            
            // Update tablet and desktop versions
            const tabletNumber = document.getElementById('current-table-number-tablet');
            const desktopNumber = document.getElementById('current-table-number-desktop');
            
            if (tabletNumber) tabletNumber.textContent = tableNumber;
            if (desktopNumber) desktopNumber.textContent = tableNumber;
        } else {
            this.elements.currentTableInfo?.classList.add('hidden');
        }
    }

    clearTable() {
        this.state.selectedTable = null;
        this.saveTableToStorage();
        this.updateTableDisplay();
        this.showNotification('Table cleared', 'info');
    }

    showTableSelectionModal() {
        this.showModal('tableSelection');
    }

    /* ================================
       MODAL MANAGEMENT
    ================================ */

    showModal(type) {
        const modalMap = {
            'tableSelection': this.elements.tableSelectionModal,
            'validation': this.elements.validationModal,
            'loading': this.elements.loadingModal,
            'success': this.elements.successModal
        };

        const modal = modalMap[type];
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    hideModal(type) {
        const modalMap = {
            'tableSelection': this.elements.tableSelectionModal,
            'validation': this.elements.validationModal,
            'loading': this.elements.loadingModal,
            'success': this.elements.successModal
        };

        const modal = modalMap[type];
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    hideAllModals() {
        Object.keys({
            'tableSelection': this.elements.tableSelectionModal,
            'validation': this.elements.validationModal,
            'loading': this.elements.loadingModal,
            'success': this.elements.successModal
        }).forEach(type => this.hideModal(type));
    }

    /* ================================
       ORDER PROCESSING
    ================================ */

    async processOrder() {
        // Check basic requirements
        if (!this.state.selectedTable) {
            this.showNotification('Please select a table first', 'error');
            this.showTableSelectionModal();
            return;
        }

        if (this.state.cart.length === 0) {
            this.showNotification('Cart is empty', 'error');
            return;
        }

        // Validate customer information
        const validation = this.validateCustomerInfo();
        if (!validation.isValid) {
            this.showValidationErrors(validation.errors);
            return;
        }

        // Update customer info
        this.state.customerInfo.name = validation.name;
        this.state.customerInfo.phone = validation.phone;

        // Process the order
        await this.processOrderWithValidation();
    }

    async processOrderWithValidation() {
        this.showModal('loading');

        const orderData = {
            table_id: this.state.selectedTable.id,
            customer_name: this.state.customerInfo.name,
            customer_phone: this.state.customerInfo.phone,
            payment_method: this.state.customerInfo.paymentMethod,
            coupon_id: this.state.coupon.isApplied ? this.state.coupon.id : null,
            items: this.state.cart.map(item => ({
                menu_item_id: item.menuItemId || item.id,
                quantity: item.quantity,
                options: (item.selectedOptions || []).map(opt => opt.id),
                special_instructions: item.specialInstructions || ''
            }))
        };

        console.log('Sending order data:', orderData);

        try {
            // Simulate API call if axios is not available
            let response;
            if (typeof axios !== 'undefined') {
                response = await axios.post('/cashier/orders', orderData);
            } else {
                // Fallback for demo/testing
                response = await this.simulateOrderSubmission(orderData);
            }

            this.hideModal('loading');

            if (response.data.success) {
                this.showOrderSuccess(response.data);
                
                // Reset everything after a delay
                setTimeout(() => {
                    this.resetOrder();
                }, 3000);
            } else {
                this.showNotification('Failed to create order: ' + response.data.message, 'error');
            }
        } catch (error) {
            this.hideModal('loading');
            console.error('Error creating order:', error);
            
            if (error.response && error.response.status === 422) {
                const errors = error.response.data.errors;
                const errorMessage = Object.values(errors).flat().join(', ');
                this.showNotification('Validation error: ' + errorMessage, 'error');
            } else {
                this.showNotification('Failed to create order. Please try again.', 'error');
            }
        }
    }

    async simulateOrderSubmission(orderData) {
        // Simulate network delay
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Simulate successful response
        return {
            data: {
                success: true,
                order: {
                    id: Math.floor(Math.random() * 1000),
                    table_number: this.state.selectedTable.number,
                    customer_name: orderData.customer_name,
                    total_amount: this.calculateTotals().total
                },
                message: 'Order created successfully'
            }
        };
    }

    showValidationErrors(errors) {
        if (this.elements.validationErrors) {
            this.elements.validationErrors.innerHTML = errors.map(error => 
                `<div class="flex items-center mb-2"><i class="fas fa-exclamation-circle mr-2"></i>${error}</div>`
            ).join('');
        }
        this.showModal('validation');
    }

    showOrderSuccess(data) {
        if (this.elements.successMessage) {
            const totals = this.calculateTotals();
            this.elements.successMessage.innerHTML = `
                <div class="text-center">
                    <div class="font-semibold mb-2">Order #${data.order?.id || 'N/A'}</div>
                    <div class="text-sm text-gray-600 mb-2">Table ${this.state.selectedTable.number}</div>
                    <div class="text-sm text-gray-600 mb-2">Customer: ${this.state.customerInfo.name}</div>
                    <div class="font-bold text-lg text-green-600">Total: ${this.formatCurrency(totals.total)}</div>
                </div>
            `;
        }
        this.showModal('success');
    }

    resetOrder() {
        this.state.cart = [];
        this.state.selectedTable = null;
        this.state.customerInfo = {
            name: '',
            phone: '',
            paymentMethod: 'cash'
        };

        // Clear form
        if (this.elements.customerName) this.elements.customerName.value = '';
        if (this.elements.customerPhone) this.elements.customerPhone.value = '';
        
        // Reset payment method
        this.updatePaymentMethodUI('cash');

        // Clear storage
        this.saveCartToStorage();
        this.saveTableToStorage();

        // Update UI
        this.renderCart();
        this.updateTableDisplay();
        
        this.showNotification('Order completed successfully!', 'success');
    }

    /* ================================
       NOTIFICATION SYSTEM
    ================================ */

    showNotification(message, type = 'info') {
        if (!this.elements.notificationsContainer) return;

        const notification = document.createElement('div');
        const typeClasses = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'info': 'bg-blue-500',
            'warning': 'bg-yellow-500'
        };

        const typeIcons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'info': 'fas fa-info-circle',
            'warning': 'fas fa-exclamation-triangle'
        };

        notification.className = `${typeClasses[type]} text-white px-6 py-4 rounded-2xl shadow-lg flex items-center animate-slide-in-right max-w-sm`;
        notification.innerHTML = `
            <i class="${typeIcons[type]} mr-3"></i>
            <span class="font-medium">${message}</span>
            <button class="ml-4 hover:bg-black hover:bg-opacity-20 rounded-lg p-1 transition-all" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        this.elements.notificationsContainer.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.transform = 'translateX(100%)';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    /* ================================
       PUBLIC API METHODS
    ================================ */

    // Method to add item from menu page
    addToCart(item) {
        const existingItem = this.state.cart.find(cartItem => 
            cartItem.id === item.id && 
            JSON.stringify(cartItem.selectedOptions) === JSON.stringify(item.selectedOptions)
        );

        if (existingItem) {
            existingItem.quantity += item.quantity;
        } else {
            this.state.cart.push({...item});
        }

        this.saveCartToStorage();
        this.renderCart();
        this.showNotification(`${item.name} added to cart`, 'success');
    }

    // Method to get current cart count
    getCartCount() {
        return this.state.cart.reduce((total, item) => total + item.quantity, 0);
    }

    // Method to get current cart total
    getCartTotal() {
        return this.calculateTotals().total;
    }

    // Method to clear entire cart
    clearCart() {
        if (this.state.cart.length === 0) return;

        if (confirm('Are you sure you want to clear the entire cart?')) {
            this.state.cart = [];
            this.saveCartToStorage();
            this.renderCart();
            this.showNotification('Cart cleared', 'info');
        }
    }
}

// Initialize cart manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.cartManager = new CartManager();
});

// Add some CSS animations via JavaScript since we can't modify CSS files
const style = document.createElement('style');
style.textContent = `
    .animate-slide-in-right {
        animation: slideInRight 0.3s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .shadow-soft {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .shadow-hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
`;
document.head.appendChild(style);