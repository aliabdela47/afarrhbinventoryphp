    <!-- Sidebar -->
    <nav class="app-sidebar" id="sidebar">
        <!-- Dashboard -->
        <div class="sidebar-section"><?php echo __('dashboard'); ?></div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
                   href="<?php echo baseUrl('index.php'); ?>">
                    <i class="bi bi-speedometer2"></i> <?php echo __('dashboard'); ?>
                </a>
            </li>
        </ul>
        
        <!-- Inventory Management -->
        <div class="sidebar-section"><?php echo __('items'); ?></div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/items/list.php'); ?>">
                    <i class="bi bi-box"></i> <?php echo __('items'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/warehouses/list.php'); ?>">
                    <i class="bi bi-building"></i> <?php echo __('warehouses'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/categories/list.php'); ?>">
                    <i class="bi bi-tags"></i> <?php echo __('categories'); ?>
                </a>
            </li>
        </ul>
        
        <!-- Requests & Issuances -->
        <div class="sidebar-section"><?php echo __('requests'); ?> & <?php echo __('issuances'); ?></div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/requests/list.php'); ?>">
                    <i class="bi bi-file-text"></i> <?php echo __('requests'); ?> (Model-20)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/issuances/list.php'); ?>">
                    <i class="bi bi-file-earmark-check"></i> <?php echo __('issuances'); ?> (Model-22)
                </a>
            </li>
        </ul>
        
        <!-- People -->
        <div class="sidebar-section"><?php echo __('employees'); ?> & <?php echo __('customers'); ?></div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/employees/list.php'); ?>">
                    <i class="bi bi-people"></i> <?php echo __('employees'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/customers/list.php'); ?>">
                    <i class="bi bi-person-badge"></i> <?php echo __('customers'); ?>
                </a>
            </li>
        </ul>
        
        <!-- Documents & Reports -->
        <div class="sidebar-section"><?php echo __('documents'); ?> & <?php echo __('reports'); ?></div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/documents/list.php'); ?>">
                    <i class="bi bi-file-earmark-pdf"></i> <?php echo __('documents'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/reports/index.php'); ?>">
                    <i class="bi bi-graph-up"></i> <?php echo __('reports'); ?>
                </a>
            </li>
        </ul>
        
        <!-- Admin -->
        <?php if (isAdmin()): ?>
        <div class="sidebar-section"><?php echo __('admin'); ?></div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/admin/users.php'); ?>">
                    <i class="bi bi-person-gear"></i> <?php echo __('users'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo baseUrl('modules/admin/audit.php'); ?>">
                    <i class="bi bi-clock-history"></i> <?php echo __('audit_log'); ?>
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </nav>
    
    <!-- Main Content -->
    <main class="app-main" id="mainContent">
