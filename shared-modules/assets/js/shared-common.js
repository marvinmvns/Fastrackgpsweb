/**
 * JavaScript compartilhado entre sistemas Legacy e Moderno
 * Namespace: SharedModules
 * Compatibilidade: ES5 para suportar sistema legacy
 */

// Namespace global para módulos compartilhados
window.SharedModules = window.SharedModules || {};

(function() {
    'use strict';

    // Utilitários comuns
    SharedModules.Utils = {
        
        /**
         * Debounce function para otimizar eventos
         */
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this;
                var args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        /**
         * Throttle function para limitar execuções
         */
        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var args = arguments;
                var context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() {
                        inThrottle = false;
                    }, limit);
                }
            };
        },

        /**
         * Formatação de data
         */
        formatDate: function(date, format) {
            if (!(date instanceof Date)) {
                date = new Date(date);
            }
            
            format = format || 'dd/mm/yyyy';
            
            var day = ('0' + date.getDate()).slice(-2);
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var year = date.getFullYear();
            var hours = ('0' + date.getHours()).slice(-2);
            var minutes = ('0' + date.getMinutes()).slice(-2);
            var seconds = ('0' + date.getSeconds()).slice(-2);
            
            return format
                .replace('dd', day)
                .replace('mm', month)
                .replace('yyyy', year)
                .replace('hh', hours)
                .replace('ii', minutes)
                .replace('ss', seconds);
        },

        /**
         * Sanitização básica de string
         */
        sanitizeString: function(str) {
            if (typeof str !== 'string') return '';
            
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#x27;');
        },

        /**
         * Validação de email
         */
        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    };

    // Ajax helper compatível com ambos os sistemas
    SharedModules.Ajax = {
        
        /**
         * Request GET
         */
        get: function(url, callback, errorCallback) {
            this.request('GET', url, null, callback, errorCallback);
        },

        /**
         * Request POST
         */
        post: function(url, data, callback, errorCallback) {
            this.request('POST', url, data, callback, errorCallback);
        },

        /**
         * Request genérica
         */
        request: function(method, url, data, callback, errorCallback) {
            var xhr = new XMLHttpRequest();
            
            xhr.open(method, url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (callback) callback(response);
                        } catch (e) {
                            if (callback) callback(xhr.responseText);
                        }
                    } else {
                        if (errorCallback) {
                            errorCallback(xhr.status, xhr.statusText);
                        }
                    }
                }
            };
            
            if (data && typeof data === 'object') {
                var params = [];
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
                    }
                }
                data = params.join('&');
            }
            
            xhr.send(data);
        }
    };

    // Sistema de notificações comum
    SharedModules.Notifications = {
        
        container: null,
        
        init: function() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'shared-notifications-container';
                this.container.style.cssText = 
                    'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                document.body.appendChild(this.container);
            }
        },

        show: function(message, type, duration) {
            this.init();
            
            type = type || 'info';
            duration = duration || 5000;
            
            var notification = document.createElement('div');
            notification.className = 'shared-alert shared-alert-' + type;
            notification.style.cssText = 
                'margin-bottom: 10px; opacity: 0; transition: opacity 0.3s ease;';
            notification.innerHTML = SharedModules.Utils.sanitizeString(message);
            
            this.container.appendChild(notification);
            
            // Fade in
            setTimeout(function() {
                notification.style.opacity = '1';
            }, 10);
            
            // Auto remove
            setTimeout(function() {
                notification.style.opacity = '0';
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, duration);
        },

        success: function(message, duration) {
            this.show(message, 'success', duration);
        },

        error: function(message, duration) {
            this.show(message, 'danger', duration);
        },

        warning: function(message, duration) {
            this.show(message, 'warning', duration);
        },

        info: function(message, duration) {
            this.show(message, 'info', duration);
        }
    };

    // Loading overlay compartilhado
    SharedModules.Loading = {
        
        overlay: null,
        
        show: function(message) {
            if (!this.overlay) {
                this.overlay = document.createElement('div');
                this.overlay.style.cssText = 
                    'position: fixed; top: 0; left: 0; width: 100%; height: 100%; ' +
                    'background: rgba(0,0,0,0.5); z-index: 10000; display: flex; ' +
                    'align-items: center; justify-content: center;';
                
                var content = document.createElement('div');
                content.style.cssText = 
                    'background: white; padding: 20px; border-radius: 4px; text-align: center;';
                content.innerHTML = 
                    '<div style="margin-bottom: 10px;">Carregando...</div>' +
                    '<div class="shared-spinner" style="width: 30px; height: 30px; border: 3px solid #f3f3f3; ' +
                    'border-top: 3px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>';
                
                this.overlay.appendChild(content);
                
                // CSS animation for spinner
                if (!document.getElementById('shared-spinner-css')) {
                    var style = document.createElement('style');
                    style.id = 'shared-spinner-css';
                    style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                    document.head.appendChild(style);
                }
            }
            
            if (message) {
                this.overlay.querySelector('div').textContent = message;
            }
            
            document.body.appendChild(this.overlay);
        },
        
        hide: function() {
            if (this.overlay && this.overlay.parentNode) {
                this.overlay.parentNode.removeChild(this.overlay);
            }
        }
    };

    // Inicialização automática
    document.addEventListener('DOMContentLoaded', function() {
        SharedModules.Notifications.init();
    });

    // Para compatibilidade com sistemas que não usam DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            SharedModules.Notifications.init();
        });
    } else {
        SharedModules.Notifications.init();
    }

})();

// Expor globalmente para compatibilidade com sistema legacy
window.showNotification = function(message, type, duration) {
    SharedModules.Notifications.show(message, type, duration);
};

window.showLoading = function(message) {
    SharedModules.Loading.show(message);
};

window.hideLoading = function() {
    SharedModules.Loading.hide();
};