<?php
/**
 * Sidebar Navigation - Materialize Template
 * AfarRHB Inventory Management System
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page, $dir = null) {
    global $currentPage, $currentDir;
    if ($dir) {
        return $currentDir === $dir ? 'active' : '';
    }
    return $currentPage === $page ? 'active' : '';
}
?>

<!-- Sidebar Menu -->
<aside class="layout-menu">
    <!-- Brand -->
    <div class="app-brand">
        <a href="<?php echo APP_URL; ?>/dashboard.php" class="app-brand-link">
            <span class="app-brand-text"><?php echo e(APP_NAME); ?></span>
        </a>
    </div>
    
    <!-- Menu -->
    <ul class="menu">
        <!-- Dashboard -->
        <li class="menu-item <?php echo isActive('dashboard.php'); ?>">
            <a href="<?php echo APP_URL; ?>/dashboard.php" class="menu-link">
                <i class="menu-icon bi bi-speedometer2"></i>
                <span><?php echo t('dashboard'); ?></span>
            </a>
        </li>
        
        <!-- Inventory Section -->
        <li class="menu-header">
            <?php echo t('inventory'); ?>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'items'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/items/list.php" class="menu-link">
                <i class="menu-icon bi bi-box-seam"></i>
                <span><?php echo t('items'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'categories'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/categories/list.php" class="menu-link">
                <i class="menu-icon bi bi-grid"></i>
                <span><?php echo t('categories'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'warehouses'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/warehouses/list.php" class="menu-link">
                <i class="menu-icon bi bi-building"></i>
                <span><?php echo t('warehouses'); ?></span>
            </a>
        </li>
        
        <!-- Transactions Section -->
        <li class="menu-header">
            <?php echo t('transactions'); ?>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'requests'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/requests/list.php" class="menu-link">
                <i class="menu-icon bi bi-clipboard-check"></i>
                <span><?php echo t('requests'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'issuances'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/issuances/list.php" class="menu-link">
                <i class="menu-icon bi bi-send"></i>
                <span><?php echo t('issuances'); ?></span>
            </a>
        </li>
        
        <!-- People Section -->
        <li class="menu-header">
            <?php echo t('people'); ?>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'employees'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/employees/list.php" class="menu-link">
                <i class="menu-icon bi bi-people"></i>
                <span><?php echo t('employees'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'customers'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/customers/list.php" class="menu-link">
                <i class="menu-icon bi bi-person-badge"></i>
                <span><?php echo t('customers'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'directorates'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/directorates/list.php" class="menu-link">
                <i class="menu-icon bi bi-diagram-3"></i>
                <span><?php echo t('directorates'); ?></span>
            </a>
        </li>
        
        <!-- Vehicles Section -->
        <li class="menu-header">
            <?php echo t('vehicles'); ?>
        </li>
        
        <li class="menu-item <?php echo $currentDir === 'vehicles' && !in_array(basename(dirname($_SERVER['PHP_SELF'])), ['assignments', 'services', 'garages']) ? 'active' : ''; ?>">
            <a href="<?php echo APP_URL; ?>/pages/vehicles/list.php" class="menu-link">
                <i class="menu-icon bi bi-truck"></i>
                <span><?php echo t('vehicles'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'assignments'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/vehicles/assignments/list.php" class="menu-link">
                <i class="menu-icon bi bi-calendar-check"></i>
                <span><?php echo t('assignments'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'services'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/vehicles/services/list.php" class="menu-link">
                <i class="menu-icon bi bi-wrench"></i>
                <span><?php echo t('services'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'garages'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/vehicles/garages/list.php" class="menu-link">
                <i class="menu-icon bi bi-shop"></i>
                <span><?php echo t('garages'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('checkin.php') || isActive('view_map.php'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/vehicles/tracking/checkin.php" class="menu-link">
                <i class="menu-icon bi bi-geo-alt"></i>
                <span><?php echo t('tracking'); ?></span>
            </a>
        </li>
        
        <!-- Reports Section -->
        <li class="menu-header">
            <?php echo t('reports'); ?>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'reports'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/reports/index.php" class="menu-link">
                <i class="menu-icon bi bi-file-earmark-text"></i>
                <span><?php echo t('reports'); ?></span>
            </a>
        </li>
        
        <!-- Admin Section -->
        <?php if (hasRole('admin')): ?>
        <li class="menu-header">
            <?php echo t('administration'); ?>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'audit_logs'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/audit_logs/list.php" class="menu-link">
                <i class="menu-icon bi bi-shield-check"></i>
                <span><?php echo t('audit_logs'); ?></span>
            </a>
        </li>
        
        <li class="menu-item <?php echo isActive('', 'users'); ?>">
            <a href="<?php echo APP_URL; ?>/pages/users/list.php" class="menu-link">
                <i class="menu-icon bi bi-person-gear"></i>
                <span><?php echo t('users'); ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<style>
/* Mobile responsiveness */
@media (max-width: 768px) {
    .layout-menu {
        transform: translateX(-100%);
        transition: transform 0.3s;
    }
    
    .layout-menu.active {
        transform: translateX(0);
    }
    
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
    
    .navbar-brand,
    button[data-toggle="sidebar"] {
        display: block !important;
    }
}
</style>
