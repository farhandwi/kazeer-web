class OrderTracking {
    constructor(sessionToken, orderNumber) {
        this.sessionToken = sessionToken;
        this.orderNumber = orderNumber;
        this.pusher = new Pusher(window.pusherKey, {
            cluster: window.pusherCluster,
            encrypted: true
        });
        
        this.channel = this.pusher.subscribe(`table.${sessionToken}`);
        this.bindEvents();
    }

    bindEvents() {
        // Order status updates
        this.channel.bind('order.status.updated', (data) => {
            this.updateOrderStatus(data.order);
            this.updateProgress(data.progress);
            this.updateTimeline(data.order.timeline);
        });

        // Item status updates
        this.channel.bind('order.item.updated', (data) => {
            this.updateItemStatus(data.order_item);
            this.updateProgress(data.progress);
        });

        // Real-time progress updates
        this.channel.bind('order.progress', (data) => {
            this.updateAllProgress(data);
        });
    }

    updateOrderStatus(order) {
        const statusElement = document.getElementById('order-status');
        const statusBadge = statusElement.querySelector('.status-badge');
        
        statusBadge.textContent = this.formatStatus(order.status);
        statusBadge.className = `status-badge status-${order.status}`;
        
        // Update estimated time
        if (order.queue && order.queue.length > 0) {
            const queue = order.queue[0];
            document.getElementById('queue-number').textContent = queue.queue_number;
            document.getElementById('estimated-time').textContent = 
                this.formatEstimatedTime(queue.estimated_wait_time);
        }
    }

    updateProgress(progress) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        
        progressBar.style.width = `${progress.progress_percentage}%`;
        progressText.textContent = 
            `${progress.completed_items}/${progress.total_items} items ready`;
        
        // Update item statuses
        document.querySelectorAll('.order-item').forEach(item => {
            const itemId = item.dataset.itemId;
            const statusElement = item.querySelector('.item-status');
            
            // Find item status from order data
            // This would be updated based on the actual item data received
        });
    }

    updateItemStatus(orderItem) {
        const itemElement = document.querySelector(`[data-item-id="${orderItem.id}"]`);
        if (itemElement) {
            const statusElement = itemElement.querySelector('.item-status');
            statusElement.textContent = this.formatStatus(orderItem.status);
            statusElement.className = `item-status status-${orderItem.status}`;
            
            // Update stations status if available
            if (orderItem.stations) {
                orderItem.stations.forEach(station => {
                    const stationElement = itemElement.querySelector(
                        `[data-station-id="${station.station_id}"]`
                    );
                    if (stationElement) {
                        stationElement.className = `station-status status-${station.status}`;
                    }
                });
            }
        }
    }

    updateTimeline(timeline) {
        const timelineContainer = document.getElementById('order-timeline');
        timelineContainer.innerHTML = '';
        
        timeline.forEach(event => {
            const eventElement = document.createElement('div');
            eventElement.className = 'timeline-event';
            eventElement.innerHTML = `
                <div class="timeline-time">${this.formatTime(event.created_at)}</div>
                <div class="timeline-content">
                    <h4>${event.title}</h4>
                    <p>${event.description}</p>
                </div>
            `;
            timelineContainer.appendChild(eventElement);
        });
    }

    updateAllProgress(data) {
        // Update queue position
        if (data.queue_position > 0) {
            document.getElementById('queue-position').textContent = data.queue_position;
        }
        
        // Update estimated completion time
        if (data.estimated_completion) {
            document.getElementById('estimated-completion').textContent = 
                this.formatDateTime(data.estimated_completion);
        }
        
        // Update detailed item progress
        data.items_status.forEach(item => {
            this.updateDetailedItemStatus(item);
        });
    }

    updateDetailedItemStatus(item) {
        const itemElement = document.querySelector(`[data-item-id="${item.id}"]`);
        if (itemElement) {
            const stationsContainer = itemElement.querySelector('.stations-progress');
            if (stationsContainer && item.stations) {
                stationsContainer.innerHTML = '';
                
                item.stations.forEach(station => {
                    const stationElement = document.createElement('div');
                    stationElement.className = `station-progress status-${station.status}`;
                    stationElement.innerHTML = `
                        <span class="station-name">${station.station_name}</span>
                        <span class="station-status">${this.formatStatus(station.status)}</span>
                    `;
                    stationsContainer.appendChild(stationElement);
                });
            }
        }
    }

    formatStatus(status) {
        const statusMap = {
            'pending': 'Menunggu',
            'confirmed': 'Dikonfirmasi', 
            'preparing': 'Sedang Dimasak',
            'ready': 'Siap Disajikan',
            'served': 'Sudah Disajikan',
            'completed': 'Selesai',
            'in_progress': 'Sedang Dikerjakan'
        };
        return statusMap[status] || status;
    }

    formatTime(datetime) {
        return new Date(datetime).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatDateTime(datetime) {
        return new Date(datetime).toLocaleString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            day: '2-digit',
            month: '2-digit'
        });
    }

    formatEstimatedTime(minutes) {
        if (minutes < 60) {
            return `${minutes} menit`;
        }
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return `${hours} jam ${remainingMinutes} menit`;
    }
}