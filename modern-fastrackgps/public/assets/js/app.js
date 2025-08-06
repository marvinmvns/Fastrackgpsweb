/**
 * FastrackGPS - Modern JavaScript Application
 */

class FastrackGPS {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.setupRealTimeUpdates();
    }

    init() {
        console.log('FastrackGPS App Initialized');
        this.showLoadingSpinner = this.showLoadingSpinner.bind(this);
        this.hideLoadingSpinner = this.hideLoadingSpinner.bind(this);
        this.showNotification = this.showNotification.bind(this);
    }

    setupEventListeners() {
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(alert => {
                    const closeBtn = alert.querySelector('.btn-close');
                    if (closeBtn) {
                        closeBtn.click();
                    }
                });
            }, 5000);
        });

        // Form validation
        this.setupFormValidation();

        // Auto-refresh functionality
        this.setupAutoRefresh();

        // Keyboard shortcuts
        this.setupKeyboardShortcuts();
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    setupAutoRefresh() {
        const autoRefreshElements = document.querySelectorAll('[data-auto-refresh]');
        autoRefreshElements.forEach(element => {
            const interval = parseInt(element.dataset.autoRefresh) || 30000;
            const url = element.dataset.refreshUrl;
            
            if (url) {
                setInterval(() => {
                    this.refreshElement(element, url);
                }, interval);
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+R for refresh
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                location.reload();
            }

            // Ctrl+D for dashboard
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                window.location.href = '/dashboard';
            }

            // Ctrl+V for vehicles
            if (e.ctrlKey && e.key === 'v') {
                e.preventDefault();
                window.location.href = '/vehicles';
            }

            // Ctrl+A for alerts
            if (e.ctrlKey && e.key === 'a' && !e.shiftKey) {
                e.preventDefault();
                window.location.href = '/alerts';
            }
        });
    }

    setupRealTimeUpdates() {
        // Setup WebSocket connection for real-time updates
        if (typeof WebSocket !== 'undefined') {
            this.setupWebSocket();
        } else {
            // Fallback to polling
            this.setupPolling();
        }
    }

    setupWebSocket() {
        // WebSocket implementation for real-time updates
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws`;
        
        try {
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
            };
            
            this.ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleRealTimeUpdate(data);
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected, attempting to reconnect...');
                setTimeout(() => this.setupWebSocket(), 5000);
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        } catch (error) {
            console.warn('WebSocket not available, falling back to polling');
            this.setupPolling();
        }
    }

    setupPolling() {
        // Polling fallback for real-time updates
        setInterval(() => {
            this.pollForUpdates();
        }, 30000);
    }

    async pollForUpdates() {
        try {
            const response = await fetch('/api/updates');
            const data = await response.json();
            
            if (data.success) {
                this.handleRealTimeUpdate(data);
            }
        } catch (error) {
            console.error('Error polling for updates:', error);
        }
    }

    handleRealTimeUpdate(data) {
        switch (data.type) {
            case 'vehicle_status':
                this.updateVehicleStatus(data.vehicle);
                break;
            case 'new_alert':
                this.showNewAlert(data.alert);
                break;
            case 'position_update':
                this.updateVehiclePosition(data.position);
                break;
        }
    }

    updateVehicleStatus(vehicle) {
        const vehicleElements = document.querySelectorAll(`[data-vehicle-id="${vehicle.id}"]`);
        vehicleElements.forEach(element => {
            const statusBadge = element.querySelector('.status-badge');
            if (statusBadge) {
                statusBadge.className = `badge bg-${vehicle.is_online ? 'success' : 'danger'}`;
                statusBadge.textContent = vehicle.is_online ? 'Online' : 'Offline';
            }
        });
    }

    showNewAlert(alert) {
        this.showNotification(`Nova alerta: ${alert.title}`, 'warning');
        
        // Update alert counter
        const alertCounter = document.querySelector('.alert-counter');
        if (alertCounter) {
            alertCounter.textContent = parseInt(alertCounter.textContent) + 1;
        }
    }

    updateVehiclePosition(position) {
        // Update map if it exists
        if (window.vehicleMap && window.vehicleMarkers) {
            const marker = window.vehicleMarkers[position.vehicle_id];
            if (marker) {
                marker.setLatLng([position.latitude, position.longitude]);
            }
        }
    }

    async refreshElement(element, url) {
        try {
            this.showLoadingSpinner();
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success && data.html) {
                element.innerHTML = data.html;
            }
        } catch (error) {
            console.error('Error refreshing element:', error);
        } finally {
            this.hideLoadingSpinner();
        }
    }

    showLoadingSpinner() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.style.display = 'flex';
        }
    }

    hideLoadingSpinner() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.style.display = 'none';
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }

    // API Helper Methods
    async apiRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const finalOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, finalOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            this.showNotification(`Erro: ${error.message}`, 'danger');
            throw error;
        }
    }

    async get(url) {
        return this.apiRequest(url);
    }

    async post(url, data) {
        return this.apiRequest(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async put(url, data) {
        return this.apiRequest(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async delete(url) {
        return this.apiRequest(url, {
            method: 'DELETE'
        });
    }
}

// Map Helper Functions
const MapUtils = {
    createMap(elementId, center = [-23.5505, -46.6333], zoom = 10) {
        const map = L.map(elementId).setView(center, zoom);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        return map;
    },

    addVehicleMarker(map, vehicle) {
        if (!vehicle.last_position) return null;

        const icon = L.divIcon({
            html: `<i class="fas fa-car" style="color: ${vehicle.is_online ? '#28a745' : '#dc3545'}"></i>`,
            iconSize: [20, 20],
            className: 'vehicle-marker'
        });

        const marker = L.marker([
            vehicle.last_position.latitude,
            vehicle.last_position.longitude
        ], { icon }).addTo(map);

        marker.bindPopup(`
            <div class="popup-content">
                <h6>${vehicle.name}</h6>
                <p><strong>IMEI:</strong> ${vehicle.imei}</p>
                <p><strong>Status:</strong> <span class="badge bg-${vehicle.is_online ? 'success' : 'danger'}">${vehicle.is_online ? 'Online' : 'Offline'}</span></p>
                <p><strong>Velocidade:</strong> ${vehicle.last_position.speed} km/h</p>
                <p><strong>Última atualização:</strong> ${new Date(vehicle.last_position.timestamp).toLocaleString()}</p>
            </div>
        `);

        return marker;
    },

    addGeofence(map, geofence) {
        let layer;

        if (geofence.type === 'circle') {
            layer = L.circle([
                geofence.coordinates[0].latitude,
                geofence.coordinates[0].longitude
            ], {
                radius: geofence.radius,
                color: geofence.color,
                fillOpacity: 0.2
            }).addTo(map);
        } else {
            const latLngs = geofence.coordinates.map(coord => [coord.latitude, coord.longitude]);
            layer = L.polygon(latLngs, {
                color: geofence.color,
                fillOpacity: 0.2
            }).addTo(map);
        }

        layer.bindPopup(`
            <div class="popup-content">
                <h6>${geofence.name}</h6>
                <p>${geofence.description}</p>
                <p><strong>Tipo:</strong> ${geofence.type}</p>
                <p><strong>Status:</strong> <span class="badge bg-${geofence.is_active ? 'success' : 'secondary'}">${geofence.is_active ? 'Ativo' : 'Inativo'}</span></p>
            </div>
        `);

        return layer;
    }
};

// Form Helper Functions
const FormUtils = {
    serialize(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    },

    validate(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    },

    reset(form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        form.classList.remove('was-validated');
    }
};

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    window.FastrackGPS = new FastrackGPS();
    
    // Make utilities available globally
    window.MapUtils = MapUtils;
    window.FormUtils = FormUtils;
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FastrackGPS, MapUtils, FormUtils };
}