<?php
/**
 * Sidebar Navigation
 * AfarRHB Inventory Management System
 */

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span><?php echo e(t('dashboard')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/items/list.php" class="<?php echo strpos($currentPage, 'items') !== false ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>
                <span><?php echo e(t('items')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/categories/list.php" class="<?php echo strpos($currentPage, 'categories') !== false ? 'active' : ''; ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span><?php echo e(t('categories')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/warehouses/list.php" class="<?php echo strpos($currentPage, 'warehouses') !== false ? 'active' : ''; ?>">
                <i class="bi bi-building"></i>
                <span><?php echo e(t('warehouses')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/requests/list.php" class="<?php echo strpos($currentPage, 'requests') !== false ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-check"></i>
                <span><?php echo e(t('requests')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/issuances/list.php" class="<?php echo strpos($currentPage, 'issuances') !== false ? 'active' : ''; ?>">
                <i class="bi bi-arrow-right-circle"></i>
                <span><?php echo e(t('issuances')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/customers/list.php" class="<?php echo strpos($currentPage, 'customers') !== false ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span><?php echo e(t('customers')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/employees/list.php" class="<?php echo strpos($currentPage, 'employees') !== false ? 'active' : ''; ?>">
                <i class="bi bi-person-badge"></i>
                <span><?php echo e(t('employees')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/reports/index.php" class="<?php echo strpos($currentPage, 'reports') !== false ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span><?php echo e(t('reports')); ?></span>
            </a>
        </li>
        
        <?php if (hasRole('admin')): ?>
        <li>
            <a href="pages/audit_logs/list.php" class="<?php echo strpos($currentPage, 'audit') !== false ? 'active' : ''; ?>">
                <i class="bi bi-shield-check"></i>
                <span><?php echo e(t('audit_logs')); ?></span>
            </a>
        </li>
        
        <li>
            <a href="pages/settings/index.php" class="<?php echo strpos($currentPage, 'settings') !== false ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span><?php echo e(t('settings')); ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>
