/**
 * BAUK UNIMOF - Admin JavaScript (ENHANCED EDITION)
 * Dashboard interactions & functionality
 * ES6+ Compatible - PHP 8.2+
 * 
 * Features:
 * - Error handling yang robust
 * - Dark mode toggle
 * - Real-time clock
 * - Advanced notifications
 * - Keyboard shortcuts
 * - Auto-save forms
 * - Session management
 * - Performance monitoring
 */

'use strict';

// =====================================================
// GLOBAL STATE
// =====================================================
const AppState = {
    isDarkMode: false,
    sessionTimeout: 30 * 60 * 1000, // 30 minutes
    lastActivity: Date.now(),
    autoSaveInterval: null,
    notifications: [],
    isOnline: navigator.onLine
};

// =====================================================
// DOM READY
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    try {
        initSidebar();
        initAdminHeader();
        initDataTables();
        initForms();
        initAlerts();
        initTooltips();
        initDarkMode();
        initRealTimeClock();
        initSessionManager();
        initOnlineStatus();
        initKeyboardShortcuts();
        initPerformanceMonitor();
        
        console.log('%c✅ BAUK UNIMOF Admin Panel Loaded Successfully', 'color: #10b981; font-weight: bold;');
    } catch (error) {
        console.error('%c❌ Error initializing admin panel:', 'color: #ef4444;', error);
        showNotification('Terjadi kesalahan saat memuat panel admin', 'error');
    }
});

// =====================================================
// SIDEBAR
// =====================================================
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const close = document.getElementById('sidebarClose');
    
    if (!sidebar) return;
    
    // Toggle sidebar
    if (toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            localStorage.setItem('sidebarActive', sidebar.classList.contains('active'));
        });
    }
    
    // Close sidebar on mobile
    if (close) {
        close.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
    
    // Close sidebar on outside click (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && (!toggle || !toggle.contains(e.target))) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Restore sidebar state
    const savedState = localStorage.getItem('sidebarActive');
    if (savedState === 'true' && window.innerWidth > 768) {
        sidebar.classList.add('active');
    }
}

// =====================================================
// ADMIN HEADER (FIXED & ENHANCED)
// =====================================================
function initAdminHeader() {
    // User dropdown toggle on click (mobile friendly)
    const userMenu = document.querySelector('.user-menu');
    
    if (userMenu) {
        const trigger = userMenu.querySelector('.user-menu-trigger');
        
        if (trigger) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('active');
            });
            
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target)) {
                    userMenu.classList.remove('active');
                }
            });
        }
    }
    
    // Global search functionality
    const searchInput = document.getElementById('globalSearch');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            if (query.length < 2) return;
            
            // Search through nav items
            const navItems = document.querySelectorAll('.nav-item, .nav-link');
            navItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = '';
                    item.style.background = '#1a5276';
                    item.style.color = 'white';
                } else {
                    item.style.display = '';
                    item.style.background = '';
                    item.style.color = '';
                }
            });
        }, 300));
    }
    
    // Notification bell
    const notificationBell = document.querySelector('.notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', function() {
            showNotificationPanel();
        });
    }
}

// =====================================================
// DARK MODE TOGGLE
// =====================================================
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const savedMode = localStorage.getItem('darkMode');
    
    if (savedMode === 'true') {
        enableDarkMode();
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            AppState.isDarkMode = !AppState.isDarkMode;
            localStorage.setItem('darkMode', AppState.isDarkMode);
            
            if (AppState.isDarkMode) {
                enableDarkMode();
                showNotification('Mode gelap diaktifkan', 'success');
            } else {
                disableDarkMode();
                showNotification('Mode terang diaktifkan', 'success');
            }
        });
    }
}

function enableDarkMode() {
    document.body.classList.add('dark-mode');
    document.documentElement.style.setProperty('--admin-bg', '#0f172a');
    document.documentElement.style.setProperty('--admin-surface', '#1e293b');
    document.documentElement.style.setProperty('--admin-text', '#f1f5f9');
    document.documentElement.style.setProperty('--admin-text-secondary', '#94a3b8');
    document.documentElement.style.setProperty('--admin-border', '#334155');
}

function disableDarkMode() {
    document.body.classList.remove('dark-mode');
    document.documentElement.style.setProperty('--admin-bg', '#f8fafc');
    document.documentElement.style.setProperty('--admin-surface', '#ffffff');
    document.documentElement.style.setProperty('--admin-text', '#0f172a');
    document.documentElement.style.setProperty('--admin-text-secondary', '#64748b');
    document.documentElement.style.setProperty('--admin-border', '#e2e8f0');
}

// =====================================================
// REAL-TIME CLOCK
// =====================================================
function initRealTimeClock() {
    const clockElement = document.getElementById('realTimeClock');
    
    if (clockElement) {
        updateClock();
        setInterval(updateClock, 1000);
    }
    
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZoneName: 'short'
        };
        const timeString = now.toLocaleDateString('id-ID', options);
        
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }
}

// =====================================================
// SESSION MANAGER
// =====================================================
function initSessionManager() {
    // Track user activity
    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => {
            AppState.lastActivity = Date.now();
        }, true);
    });
    
    // Check session timeout
    setInterval(() => {
        const inactiveTime = Date.now() - AppState.lastActivity;
        
        if (inactiveTime > AppState.sessionTimeout) {
            showSessionTimeoutWarning();
        }
    }, 60000); // Check every minute
}

function showSessionTimeoutWarning() {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3><i class="bi bi-clock-fill" style="color: #f59e0b;"></i> Sesi Akan Berakhir</h3>
            </div>
            <div class="modal-body">
                <p>Sesi Anda akan segera berakhir karena tidak ada aktivitas. Apakah Anda ingin tetap login?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="logout()">Logout</button>
                <button class="btn btn-primary" onclick="extendSession()">Tetap Login</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.extendSession = function() {
        AppState.lastActivity = Date.now();
        modal.remove();
        showNotification('Sesi diperpanjang', 'success');
    };
    
    window.logout = function() {
        window.location.href = '/admin/logout';
    };
}

// =====================================================
// ONLINE/OFFLINE STATUS
// =====================================================
function initOnlineStatus() {
    updateOnlineStatus();
    
    window.addEventListener('online', () => {
        AppState.isOnline = true;
        updateOnlineStatus();
        showNotification('Koneksi internet tersedia', 'success');
    });
    
    window.addEventListener('offline', () => {
        AppState.isOnline = false;
        updateOnlineStatus();
        showNotification('Koneksi internet terputus', 'error');
    });
}

function updateOnlineStatus() {
    const statusIndicator = document.getElementById('onlineStatus');
    
    if (statusIndicator) {
        statusIndicator.className = AppState.isOnline ? 'online' : 'offline';
        statusIndicator.title = AppState.isOnline ? 'Online' : 'Offline';
    }
}

// =====================================================
// KEYBOARD SHORTCUTS
// =====================================================
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + K = Focus search
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            const search = document.getElementById('globalSearch');
            if (search) search.focus();
        }
        
        // Ctrl + Shift + D = Toggle Dark Mode
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            const darkModeToggle = document.getElementById('darkModeToggle');
            if (darkModeToggle) darkModeToggle.click();
        }
        
        // Ctrl + Shift + N = New (context dependent)
        if (e.ctrlKey && e.shiftKey && e.key === 'N') {
            e.preventDefault();
            const newButton = document.querySelector('.btn-primary[href*="tambah"]');
            if (newButton) newButton.click();
        }
        
        // Escape = Close modals
        if (e.key === 'Escape') {
            const activeModals = document.querySelectorAll('.modal.active');
            activeModals.forEach(modal => {
                modal.classList.remove('active');
            });
            document.body.style.overflow = '';
        }
        
        // F5 with confirmation if form is dirty
        if (e.key === 'F5') {
            const dirtyForms = document.querySelectorAll('form[data-dirty]');
            if (dirtyForms.length > 0) {
                e.preventDefault();
                if (confirm('Ada perubahan yang belum disimpan. Yakin ingin refresh?')) {
                    location.reload();
                }
            }
        }
    });
}

// =====================================================
// PERFORMANCE MONITOR
// =====================================================
function initPerformanceMonitor() {
    // Monitor page load time
    window.addEventListener('load', function() {
        if (window.performance) {
            const timing = window.performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            
            if (loadTime > 3000) {
                console.warn(`%c⚠️ Page load time: ${loadTime}ms (slow)`, 'color: #f59e0b');
            } else {
                console.log(`%c✅ Page load time: ${loadTime}ms`, 'color: #10b981');
            }
        }
    });
    
    // Monitor errors
    window.addEventListener('error', function(e) {
        console.error('%c❌ Global error:', 'color: #ef4444;', e.error);
        logErrorToServer(e.error);
    });
    
    // Monitor unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
        console.error('%c❌ Unhandled promise rejection:', 'color: #ef4444;', e.reason);
        logErrorToServer(e.reason);
    });
}

function logErrorToServer(error) {
    // Send error to server for logging
    fetch('/admin/log-error', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            message: error.message,
            stack: error.stack,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        })
    }).catch(() => {});
}

// =====================================================
// DATA TABLES (ENHANCED)
// =====================================================
function initDataTables() {
    // Table search
    const searchInputs = document.querySelectorAll('[data-table-search]');
    
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        input.addEventListener('keyup', debounce(function() {
            const query = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }, 300));
    });
    
    // Select all checkboxes
    const selectAllCheckboxes = document.querySelectorAll('.select-all');
    
    selectAllCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const table = this.closest('table');
            const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
            
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            
            updateBulkActions();
        });
    });
    
    // Individual checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.matches('tbody input[type="checkbox"]')) {
            updateBulkActions();
        }
    });
    
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete-action');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const message = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini?';
            
            if (confirm(message)) {
                const form = this.closest('form');
                if (form) form.submit();
            }
        });
    });
    
    // Table sorting
    initTableSorting();
}

function initTableSorting() {
    const sortableTables = document.querySelectorAll('table[data-sortable]');
    
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, index, this.getAttribute('data-sort') || 'string');
            });
        });
    });
}

function sortTable(table, column, type) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[column].textContent.trim();
        const bValue = b.cells[column].textContent.trim();
        
        if (type === 'number') {
            return parseFloat(aValue) - parseFloat(bValue);
        } else if (type === 'date') {
            return new Date(aValue) - new Date(bValue);
        } else {
            return aValue.localeCompare(bValue);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

function updateBulkActions() {
    const selectedCount = document.querySelectorAll('tbody input[type="checkbox"]:checked').length;
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        bulkActions.style.display = selectedCount > 0 ? 'flex' : 'none';
        bulkActions.querySelector('.selected-count').textContent = selectedCount;
    }
}

// =====================================================
// FORMS (ENHANCED)
// =====================================================
function initForms() {
    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (!preview || !this.files[0]) return;
            
            const file = this.files[0];
            
            // Validate file size
            const maxSize = this.getAttribute('data-max-size') || 2 * 1024 * 1024; // 2MB default
            if (file.size > maxSize) {
                showNotification(`Ukuran file terlalu besar. Maksimal ${formatFileSize(maxSize)}`, 'error');
                this.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = this.getAttribute('data-accept') || '';
            if (allowedTypes && !allowedTypes.split(',').some(type => file.type.includes(type.trim()))) {
                showNotification('Tipe file tidak diizinkan', 'error');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">`;
                } else {
                    preview.innerHTML = `<div class="file-preview"><i class="bi bi-file-earmark"></i> ${file.name} (${formatFileSize(file.size)})</div>`;
                }
            };
            
            reader.readAsDataURL(file);
        });
    });
    
    // Auto-resize textarea
    const textareas = document.querySelectorAll('textarea[data-autoresize]');
    
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Form submission loading
    const forms = document.querySelectorAll('form[data-loading]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
                submitBtn.dataset.originalText = originalText;
            }
        });
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="phone"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    });
    
    // Number formatting (currency)
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    
    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/[^0-9.]/g, ''));
            if (!isNaN(value)) {
                this.value = new Intl.NumberFormat('id-ID').format(value);
            }
        });
        
        input.addEventListener('focus', function() {
            this.value = this.value.replace(/\./g, '').replace(/,/g, '.');
        });
    });
    
    // Auto-save forms
    const autoSaveForms = document.querySelectorAll('form[data-auto-save]');
    
    autoSaveForms.forEach(form => {
        enableAutoSave(form);
    });
    
    // Form validation
    const validatedForms = document.querySelectorAll('form[data-validate]');
    
    validatedForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Mohon lengkapi semua field yang wajib diisi', 'error');
            }
        });
    });
}

function enableAutoSave(form) {
    const interval = parseInt(form.getAttribute('data-auto-save')) || 30000; // 30 seconds default
    
    AppState.autoSaveInterval = setInterval(() => {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        localStorage.setItem('form_auto_save_' + form.id, JSON.stringify(data));
        showNotification('Form disimpan otomatis', 'success');
    }, interval);
    
    // Clear auto-save on successful submit
    form.addEventListener('submit', function() {
        localStorage.removeItem('form_auto_save_' + form.id);
    });
    
    // Restore auto-saved data
    const savedData = localStorage.getItem('form_auto_save_' + form.id);
    if (savedData) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const input = form.querySelector('[name="' + key + '"]');
            if (input) {
                input.value = data[key];
            }
        });
        showNotification('Data form yang tersimpan berhasil dipulihkan', 'info');
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
            
            // Show error message
            const errorMsg = document.createElement('div');
            errorMsg.className = 'field-error';
            errorMsg.textContent = 'Field ini wajib diisi';
            errorMsg.style.cssText = 'color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;';
            
            if (!field.nextElementSibling?.classList.contains('field-error')) {
                field.parentNode.insertBefore(errorMsg, field.nextSibling);
            }
        } else {
            field.classList.remove('error');
            const existingError = field.nextElementSibling;
            if (existingError?.classList.contains('field-error')) {
                existingError.remove();
            }
        }
    });
    
    return isValid;
}

// =====================================================
// ALERTS (ENHANCED)
// =====================================================
function initAlerts() {
    // Auto dismiss alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentElement) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    });
}

// =====================================================
// TOOLTIPS (ENHANCED)
// =====================================================
function initTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    
    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function(e) {
            const text = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'admin-tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: absolute;
                background: #1e293b;
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 13px;
                white-space: nowrap;
                z-index: 9999;
                pointer-events: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            // Position tooltip
            let top = rect.top - tooltipRect.height - 8;
            let left = rect.left + (rect.width - tooltipRect.width) / 2;
            
            // Adjust if out of viewport
            if (top < 0) top = rect.bottom + 8;
            if (left < 0) left = 8;
            if (left + tooltipRect.width > window.innerWidth) {
                left = window.innerWidth - tooltipRect.width - 8;
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            
            this._tooltip = tooltip;
        });
        
        trigger.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });
}

// =====================================================
// NOTIFICATIONS (ENHANCED)
// =====================================================
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `admin-notification notification-${type}`;
    
    const icons = {
        success: 'check-circle-fill',
        error: 'exclamation-circle-fill',
        warning: 'exclamation-triangle-fill',
        info: 'info-circle-fill'
    };
    
    notification.innerHTML = `
        <i class="bi bi-${icons[type] || icons.info}"></i>
        <span>${message}</span>
        <button class="notif-close">&times;</button>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        border-left: 4px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
    `;
    
    document.body.appendChild(notification);
    
    notification.querySelector('.notif-close').addEventListener('click', function() {
        notification.remove();
    });
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function showNotificationPanel() {
    const panel = document.createElement('div');
    panel.className = 'notification-panel';
    panel.innerHTML = `
        <div class="notification-panel-header">
            <h3>Notifikasi</h3>
            <button class="close-panel">&times;</button>
        </div>
        <div class="notification-panel-body">
            <p>Belum ada notifikasi</p>
        </div>
    `;
    
    panel.style.cssText = `
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 25px rgba(0,0,0,0.15);
        z-index: 10001;
        transition: right 0.3s ease;
    `;
    
    document.body.appendChild(panel);
    
    setTimeout(() => {
        panel.style.right = '0';
    }, 10);
    
    panel.querySelector('.close-panel').addEventListener('click', function() {
        panel.style.right = '-400px';
        setTimeout(() => panel.remove(), 300);
    });
}

// =====================================================
// MODAL (ENHANCED)
// =====================================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModals = document.querySelectorAll('.modal.active');
        activeModals.forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.closest('.modal').classList.remove('active');
        document.body.style.overflow = '';
    }
});

// =====================================================
// UTILITY FUNCTIONS
// =====================================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function formatDate(dateString) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(new Date(dateString));
}

function formatDateTime(dateString) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(dateString));
}

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

// =====================================================
// EXPORT FUNCTIONS
// =====================================================
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        cols.forEach(col => {
            rowData.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
        });
        
        csv.push(rowData.join(','));
    });
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

function printElement(elementId) {
    const content = document.getElementById(elementId).innerHTML;
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Cetak - BAUK UNIMOF</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #f2f2f2; }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// =====================================================
// AUTO-REFRESH (for real-time updates)
// =====================================================
function startAutoRefresh(url, interval, callback) {
    const timer = setInterval(async function() {
        try {
            const response = await fetch(url);
            const data = await response.json();
            callback(data);
        } catch (error) {
            console.error('Auto-refresh error:', error);
        }
    }, interval * 1000);
    
    return timer;
}

// =====================================================
// CONFIRMATION DIALOG (ENHANCED)
// =====================================================
function confirmAction(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3><i class="bi bi-question-circle-fill" style="color: #f59e0b;"></i> Konfirmasi</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">Batal</button>
                <button class="btn btn-danger" id="confirmActionBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        modal.remove();
        if (callback) callback();
    });
}

// =====================================================
// PROGRESS BAR ANIMATION
// =====================================================
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar[data-value]');
    
    progressBars.forEach(bar => {
        const value = parseInt(bar.getAttribute('data-value'));
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease';
            bar.style.width = value + '%';
        }, 100);
    });
}

// Run on load
window.addEventListener('load', animateProgressBars);

// =====================================================
// CONSOLE BRANDING
// =====================================================
console.log('%c BAUK UNIMOF Admin Panel ', 'background: #0d2f4a; color: #e67e22; font-size: 16px; font-weight: bold; padding: 10px; border-radius: 5px;');
console.log('%c ️ Warning: This is a browser feature intended for administrative use only. ', 'color: #ef4444; font-size: 12px;');
console.log('%c Sistem Informasi BAUK - Universitas Muhammadiyah Maumere ', 'color: #64748b; font-size: 11px;');