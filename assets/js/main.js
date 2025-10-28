/**
 * Main JavaScript - Template initialization and utilities
 * TODO: Replace with original template main.js when available
 */

(function() {
    'use strict';
    
    // Initialize template on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        initSidebar();
        initDropdowns();
        initTooltips();
        initModals();
        initDataTables();
        initCharts();
    }
    
    // Sidebar toggle for mobile
    function initSidebar() {
        const menuToggle = document.querySelector('[data-toggle="sidebar"]');
        const sidebar = document.querySelector('.layout-menu');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
            });
        }
        
        // Close sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
        
        // Set active menu item
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.menu-link');
        
        menuLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
                
                // Expand parent menu if exists
                const parentMenu = link.closest('.menu-item.has-sub');
                if (parentMenu) {
                    parentMenu.classList.add('open');
                }
            }
        });
    }
    
    // Dropdown menus
    function initDropdowns() {
        const dropdowns = document.querySelectorAll('[data-toggle="dropdown"]');
        
        dropdowns.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('show');
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.matches('[data-toggle="dropdown"]')) {
                const dropdownMenus = document.querySelectorAll('.dropdown-menu.show');
                dropdownMenus.forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }
    
    // Tooltips
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
        
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const text = this.getAttribute('title');
                if (text) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = text;
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                    tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
                    
                    this._tooltip = tooltip;
                }
            });
            
            element.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    }
    
    // Modals
    function initModals() {
        const modalTriggers = document.querySelectorAll('[data-toggle="modal"]');
        
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                const modal = document.querySelector(target);
                
                if (modal) {
                    modal.classList.add('active');
                }
            });
        });
        
        // Close modal buttons
        const modalCloses = document.querySelectorAll('[data-dismiss="modal"]');
        modalCloses.forEach(close => {
            close.addEventListener('click', function() {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.classList.remove('active');
                }
            });
        });
        
        // Close modal when clicking overlay
        const modalOverlays = document.querySelectorAll('.modal-overlay');
        modalOverlays.forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    }
    
    // DataTables initialization
    function initDataTables() {
        const tables = document.querySelectorAll('.datatable');
        
        tables.forEach(table => {
            // Simple client-side search
            const searchInput = document.querySelector('[data-table-search="' + table.id + '"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }
        });
    }
    
    // Charts initialization
    function initCharts() {
        // Charts will be initialized by specific page scripts
        // This is just a placeholder for chart utilities
    }
    
    // Utility functions
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };
    
    window.confirmDelete = function(message) {
        return confirm(message || 'Are you sure you want to delete this item?');
    };
    
})();
