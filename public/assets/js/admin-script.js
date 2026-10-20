/**
 * BAUK UNIMOF - Admin JavaScript
 * Dashboard interactions & functionality
 * ES6+ Compatible - PHP 8.2+
 */

'use strict';

// =====================================================
// DOM READY
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initAdminHeader();
    initDataTables();
    initForms();
    initAlerts();
    initTooltips();
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
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
}

// =====================================================
// ADMIN HEADER
// =====================================================
function initAdminHeader() {
    // User dropdown toggle on click (mobile friendly)
    const userMenu = document.querySelector('.user-menu');
    
    if (userMenu) {
        const trigger = userMenu.querySelector('.user-menu-trigger');
        
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
    
    // Global search functionality
    const searchInput = document.getElementById('globalSearch');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            if (query.length < 2) return;
            
            // Search through nav items
            const navItems = document.querySelectorAll('.nav-item');
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
}

// =====================================================
// DATA TABLES
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
        });
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
}

// =====================================================
// FORMS
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
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">`;
                } else {
                    preview.innerHTML = `<div class="file-preview"><i class="bi bi-file-earmark"></i> ${file.name}</div>`;
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
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
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
}

// =====================================================
// ALERTS
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
// TOOLTIPS (Simple implementation)
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
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
                z-index: 9999;
                pointer-events: none;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - 30) + 'px';
            
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
// MODAL
// =====================================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
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
// CHARTS (Simple chart placeholder)
// =====================================================
function initCharts() {
    const chartContainers = document.querySelectorAll('[data-chart]');
    
    chartContainers.forEach(container => {
        const type = container.getAttribute('data-chart');
        // Placeholder for chart library integration (Chart.js, ApexCharts, etc.)
        console.log(`Chart type: ${type} - Integrate with preferred chart library`);
    });
}

// =====================================================
// NOTIFICATIONS
// =====================================================
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `admin-notification notification-${type}`;
    notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'}"></i>
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
    `;
    
    document.body.appendChild(notification);
    
    notification.querySelector('.notif-close').addEventListener('click', function() {
        notification.remove();
    });
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

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

// =====================================================
// KEYBOARD SHORTCUTS
// =====================================================
document.addEventListener('keydown', function(e) {
    // Ctrl + K = Focus search
    if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        const search = document.getElementById('globalSearch');
        if (search) search.focus();
    }
    
    // Ctrl + Shift + D = Dashboard
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        window.location.href = '/admin';
    }
});

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
// CONSOLE BRANDING
// =====================================================
console.log('%c BAUK UNIMOF Admin Panel ', 'background: #0d2f4a; color: #e67e22; font-size: 16px; font-weight: bold; padding: 10px; border-radius: 5px;');
console.log('%c ⚠️ Warning: This is a browser feature intended for administrative use only. ', 'color: #ef4444; font-size: 12px;');
console.log('%c Sistem Informasi BAUK - Universitas Muhammadiyah Maumere ', 'color: #64748b; font-size: 11px;');