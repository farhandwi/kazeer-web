class KitchenDashboard {
    constructor(restaurantId, stationId = null) {
        this.restaurantId = restaurantId;
        this.stationId = stationId;
        this.pusher = new Pusher(window.pusherKey, {
            cluster: window.pusherCluster,
            encrypted: true
        });
        
        const channelName = stationId ? 
            `kitchen.${restaurantId}.station.${stationId}` : 
            `kitchen.${restaurantId}`;
            
        this.channel = this.pusher.subscribe(channelName);
        this.bindEvents();
        this.startAutoRefresh();
    }

    bindEvents() {
        // New order received
        this.channel.bind('order.received', (data) => {
            this.addNewOrder(data.order);
            this.showNotification('New Order', `Order #${data.order.order_number} from Table ${data.order.table.table_number}`);
        });

        // Order status updates
        this.channel.bind('order.status.updated', (data) => {
            this.updateOrderInQueue(data.order);
        });

        // Kitchen queue updates
        this.channel.bind('kitchen.queue.updated', (data) => {
            this.refreshQueue(data.queue);
        });

        // Item status updates
        this.channel.bind('kitchen.item.updated', (data) => {
            this.updateItemInQueue(data.order_item);
        });
    }

    addNewOrder(order) {
        const queueContainer = document.getElementById('kitchen-queue');
        order.items.forEach(item => {
            if (this.shouldShowItem(item)) {
                const itemElement = this.createItemElement(item, order);
                queueContainer.insertBefore(itemElement, queueContainer.firstChild);
            }
        });
        
        // Auto-scroll to new items
        queueContainer.scrollTop = 0;
    }

    updateOrderInQueue(order) {
        // Remove completed orders
        if (['served', 'completed'].includes(order.status)) {
            document.querySelectorAll(`[data-order-id="${order.id}"]`).forEach(el => {
                el.remove();
            });
        }
    }

    updateItemInQueue(orderItem) {
        const itemElement = document.querySelector(`[data-item-id="${orderItem.id}"]`);
        if (itemElement) {
            const statusButton = itemElement.querySelector('.status-button');
            const nextAction = this.getNextAction(orderItem.status);
            
            statusButton.textContent = nextAction.label;
            statusButton.className = `status-button ${nextAction.class}`;
            statusButton.onclick = () => nextAction.action(orderItem.id);
            
            // Update visual status
            itemElement.className = `queue-item status-${orderItem.status}`;
            
            // Remove if completed
            if (orderItem.status === 'served') {
                setTimeout(() => itemElement.remove(), 2000);
            }
        }
    }

    createItemElement(item, order) {
        const div = document.createElement('div');
        div.className = `queue-item status-${item.status}`;
        div.dataset.itemId = item.id;
        div.dataset.orderId = order.id;
        
        const nextAction = this.getNextAction(item.status);
        const estimatedTime = this.calculateEstimatedTime(item, order);
        
        div.innerHTML = `
            <div class="item-header">
                <span class="order-number">#${order.order_number}</span>
                <span class="table-number">Table ${order.table.table_number}</span>
                <span class="time-elapsed">${estimatedTime}</span>
            </div>
            <div class="item-details">
                <h4 class="item-name">${item.menu_item.name}</h4>
                <span class="quantity">Qty: ${item.quantity}</span>
                ${item.special_instructions ? `<p class="instructions">${item.special_instructions}</p>` : ''}
                ${item.variants ? this.renderVariants(item.variants) : ''}
            </div>
            <div class="item-actions">
                <button class="status-button ${nextAction.class}" 
                        onclick="kitchenDashboard.${nextAction.action}('${item.id}')">
                    ${nextAction.label}
                </button>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${this.getProgressWidth(item.status)}%"></div>
            </div>
        `;
        
        return div;
    }

    getNextAction(status) {
        const actions = {
            'pending': {
                label: 'Start Cooking',
                class: 'btn-start',
                action: 'startItem'
            },
            'preparing': {
                label: 'Mark Ready', 
                class: 'btn-complete',
                action: 'completeItem'
            },
            'ready': {
                label: 'Served',
                class: 'btn-serve',
                action: 'serveItem'
            }
        };
        
        return actions[status] || { label: 'Update', class: 'btn-update', action: 'updateItem' };
    }

    async startItem(itemId) {
        try {
            const response = await fetch(`/api/v1/staff/kitchen/items/${itemId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${window.authToken}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    station_id: this.stationId
                })
            });
            
            if (!response.ok) throw new Error('Failed to start item');
            
            const result = await response.json();
            this.showNotification('Success', result.message);
        } catch (error) {
            this.showNotification('Error', error.message, 'error');
        }
    }

    async completeItem(itemId) {
        try {
            const response = await fetch(`/api/v1/staff/kitchen/items/${itemId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${window.authToken}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    station_id: this.stationId
                })
            });
            
            if (!response.ok) throw new Error('Failed to complete item');
            
            const result = await response.json();
            this.showNotification('Success', result.message);
        } catch (error) {
            this.showNotification('Error', error.message, 'error');
        }
    }

    shouldShowItem(item) {
        // Filter items based on station if specified
        if (this.stationId) {
            return item.stations && item.stations.some(s => s.kitchen_station_id == this.stationId);
        }
        return true;
    }

    startAutoRefresh() {
        // Refresh queue every 30 seconds as fallback
        setInterval(async () => {
            await this.refreshQueueFromAPI();
        }, 30000);
    }

    async refreshQueueFromAPI() {
        try {
            const url = this.stationId ? 
                `/api/v1/staff/kitchen/queue?station_id=${this.stationId}` : 
                '/api/v1/staff/kitchen/queue';
                
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${window.authToken}`
                }
            });
            
            if (!response.ok) throw new Error('Failed to refresh queue');
            
            const result = await response.json();
            this.refreshQueue(result.data);
        } catch (error) {
            console.error('Failed to refresh queue:', error);
        }
    }

    refreshQueue(queueData) {
        const queueContainer = document.getElementById('kitchen-queue');
        queueContainer.innerHTML = '';
        
        queueData.forEach(item => {
            const itemElement = this.createItemElement(item, item.order);
            queueContainer.appendChild(itemElement);
        });
    }

    showNotification(title, message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-header">
                <strong>${title}</strong>
                <button class="notification-close">&times;</button>
            </div>
            <div class="notification-body">${message}</div>
        `;
        
        // Add to page
        const container = document.getElementById('notifications') || document.body;
        container.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => notification.remove(), 5000);
        
        // Manual close
        notification.querySelector('.notification-close').onclick = () => notification.remove();
    }

    calculateEstimatedTime(item, order) {
        const createdAt = new Date(order.created_at);
        const now = new Date();
        const elapsedMinutes = Math.floor((now - createdAt) / (1000 * 60));
        return `${elapsedMinutes}m ago`;
    }

    getProgressWidth(status) {
        const progressMap = {
            'pending': 0,
            'preparing': 50,
            'ready': 100,
            'served': 100
        };
        return progressMap[status] || 0;
    }

    renderVariants(variants) {
        if (!variants || variants.length === 0) return '';
        
        return `<div class="variants">
            ${variants.map(v => `<span class="variant">${v.name}: ${v.value}</span>`).join('')}
        </div>`;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize based on page type
    if (window.pageType === 'order-tracking' && window.sessionToken) {
        window.orderTracking = new OrderTracking(window.sessionToken, window.orderNumber);
    }
    
    if (window.pageType === 'kitchen-dashboard' && window.restaurantId) {
        window.kitchenDashboard = new KitchenDashboard(window.restaurantId, window.stationId);
    }
});