<?php
/**
 * Page Header - Materialize Template
 * AfarRHB Inventory Management System
 */

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirect(APP_URL . '/index.php');
}

$currentUser = getUserFullName();
$currentRole = getUserRole();
$currentLang = $_SESSION['lang'] ?? 'en';
$currentCalendar = $_SESSION['calendar'] ?? 'gregorian';
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="<?php echo e($currentLang); ?>" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo e($pageTitle); ?> - <?php echo e(APP_NAME); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>/assets/img/favicon/favicon.ico">
    
    <!-- Core CSS - Materialize Template -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/vendor/css/core.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/demo.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/afarrh_custom.css">
    
    <!-- Leaflet for maps -->
    <?php if (isset($includeLeaflet) && $includeLeaflet): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>
    
    <!-- Page specific styles -->
    <?php if (isset($pageStyles)): echo $pageStyles; endif; ?>
    
    <!-- Pass PHP config to JavaScript -->
    <script>
        window.APP_URL = '<?php echo APP_URL; ?>';
        window.APP_NAME = '<?php echo e(APP_NAME); ?>';
        window.USER_LANG = '<?php echo e($currentLang); ?>';
        window.USER_CALENDAR = '<?php echo e($currentCalendar); ?>';
    </script>
</head>
<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <?php include BASE_PATH . '/includes/sidebar.php'; ?>
        
        <!-- Layout container -->
        <div class="layout-container">
            <!-- Navbar -->
            <nav class="layout-navbar">
                <div class="navbar-content">
                    <!-- Menu Toggle for Mobile -->
                    <button class="btn btn-icon" data-toggle="sidebar" style="display: none;">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <!-- Brand (visible on mobile) -->
                    <div class="navbar-brand" style="display: none;">
                        <?php echo e(APP_NAME); ?>
                    </div>
                    
                    <!-- Search -->
                    <div class="header-search" style="flex: 1; max-width: 400px;">
                        <form action="<?php echo APP_URL; ?>/pages/search.php" method="GET">
                            <input type="text" name="q" class="form-control" placeholder="<?php echo t('search_placeholder'); ?>" value="<?php echo e($_GET['q'] ?? ''); ?>">
                        </form>
                    </div>
                    
                    <!-- Right side items -->
                    <ul class="navbar-nav">
                        <!-- Language Switcher -->
                        <li class="nav-item">
                            <div class="language-switcher">
                                <button class="<?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">EN</button>
                                <button class="<?php echo $currentLang === 'am' ? 'active' : ''; ?>" onclick="changeLanguage('am')">አማ</button>
                            </div>
                        </li>
                        
                        <!-- Calendar Toggle -->
                        <li class="nav-item">
                            <div class="calendar-toggle">
                                <label>
                                    <input type="checkbox" id="calendarToggle" <?php echo $currentCalendar === 'ethiopian' ? 'checked' : ''; ?> onchange="changeCalendar(this.checked ? 'ethiopian' : 'gregorian')">
                                    <?php echo t('ethiopian_calendar'); ?>
                                </label>
                            </div>
                        </li>
                        
                        <!-- Notifications -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" title="<?php echo t('notifications'); ?>">
                                <i class="bi bi-bell"></i>
                                <span class="badge badge-danger" style="position: absolute; top: 8px; right: 8px; font-size: 9px;">3</span>
                            </a>
                        </li>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item">
                            <div style="position: relative;">
                                <a href="#" class="nav-link" data-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i>
                                    <span><?php echo e($currentUser); ?></span>
                                    <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                                </a>
                                <div class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: #fff; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 200px; z-index: 1000;">
                                    <a href="<?php echo APP_URL; ?>/pages/profile.php" style="display: block; padding: 10px 16px; color: #333; text-decoration: none;">
                                        <i class="bi bi-person"></i> <?php echo t('profile'); ?>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/pages/settings.php" style="display: block; padding: 10px 16px; color: #333; text-decoration: none;">
                                        <i class="bi bi-gear"></i> <?php echo t('settings'); ?>
                                    </a>
                                    <div style="border-top: 1px solid #f0f0f0; margin: 4px 0;"></div>
                                    <a href="<?php echo APP_URL; ?>/logout.php" style="display: block; padding: 10px 16px; color: #ef5350; text-decoration: none;">
                                        <i class="bi bi-box-arrow-right"></i> <?php echo t('logout'); ?>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Content wrapper -->
            <div class="layout-page" style="margin-top: 64px;">
                <div class="content-wrapper">
                    <!-- Flash Messages -->
                    <?php 
                    $flash = getFlash();
                    if ($flash): 
                    ?>
                    <div class="alert alert-<?php echo e($flash['type']); ?>" style="margin-bottom: 20px;">
                        <?php echo e($flash['message']); ?>
                    </div>
                    <script>
                        // Auto-hide flash message after 5 seconds
                        setTimeout(function() {
                            const alert = document.querySelector('.alert');
                            if (alert) {
                                alert.style.opacity = '0';
                                alert.style.transition = 'opacity 0.3s';
                                setTimeout(function() { alert.remove(); }, 300);
                            }
                        }, 5000);
                    </script>
                    <?php endif; ?>
                    
                    <!-- Page Content Starts Here -->
