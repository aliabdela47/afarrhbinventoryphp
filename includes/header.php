<?php
if (!isLoggedIn()) {
    redirect(baseUrl('login.php'));
}

$currentUser = currentUser();
?>
<!DOCTYPE html>
<html lang="<?php echo currentLang(); ?>" data-bs-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? __('dashboard'); ?> - <?php echo __('app_name'); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Header */
        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .app-header .navbar-brand {
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .app-header .navbar-brand:hover {
            color: rgba(255,255,255,0.9);
        }
        
        .app-header .nav-link,
        .app-header .btn {
            color: white;
        }
        
        .app-header .nav-link:hover,
        .app-header .btn:hover {
            color: rgba(255,255,255,0.8);
        }
        
        .app-header .dropdown-menu {
            color: initial;
        }
        
        /* Sidebar */
        .app-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--bs-body-bg);
            border-right: 1px solid var(--bs-border-color);
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
        }
        
        .app-sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        .app-sidebar .nav-link {
            color: var(--bs-body-color);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0.5rem;
            transition: all 0.2s;
        }
        
        .app-sidebar .nav-link:hover {
            background-color: var(--bs-secondary-bg);
        }
        
        .app-sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .app-sidebar .nav-link i {
            width: 1.5rem;
            text-align: center;
        }
        
        .sidebar-section {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--bs-secondary-color);
            margin-top: 1rem;
        }
        
        /* Main Content */
        .app-main {
            margin-top: var(--header-height);
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease;
        }
        
        .app-main.expanded {
            margin-left: 0;
        }
        
        /* Cards */
        .metric-card {
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Tables */
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        /* Search Bar */
        .search-bar {
            max-width: 400px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            
            .app-sidebar.show {
                transform: translateX(0);
            }
            
            .app-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg app-header">
        <div class="container-fluid">
            <button class="btn btn-link text-white" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            
            <a class="navbar-brand ms-2" href="<?php echo baseUrl('index.php'); ?>">
                <i class="bi bi-box-seam"></i> <?php echo __('app_name'); ?>
            </a>
            
            <!-- Search -->
            <form class="d-none d-md-flex mx-auto search-bar" role="search">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="<?php echo __('search'); ?>..." 
                           aria-label="Search" id="globalSearch">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Right Menu -->
            <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <!-- Language Toggle -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-translate"></i>
                        <span class="d-none d-lg-inline ms-1">
                            <?php echo currentLang() === 'en' ? __('english') : __('amharic'); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="?set_lang=en">
                                <i class="bi bi-globe"></i> English
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?set_lang=am">
                                <i class="bi bi-globe"></i> አማርኛ
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Calendar Toggle -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-calendar"></i>
                        <span class="d-none d-lg-inline ms-1">
                            <?php echo __(currentCalendar()); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="?set_calendar=gregorian">
                                <i class="bi bi-calendar"></i> <?php echo __('gregorian'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?set_calendar=ethiopian">
                                <i class="bi bi-calendar"></i> <?php echo __('ethiopian'); ?>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Theme Toggle -->
                <li class="nav-item me-2">
                    <button class="btn btn-link nav-link" id="themeToggle">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item me-2">
                    <a class="nav-link" href="#" id="notificationsBtn">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;">3</span>
                    </a>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-lg-inline ms-1"><?php echo e($currentUser['name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <?php echo e($currentUser['name']); ?><br>
                                <small class="text-muted"><?php echo e($currentUser['email']); ?></small>
                            </h6>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person"></i> <?php echo __('profile'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear"></i> <?php echo __('settings'); ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo baseUrl('logout.php'); ?>">
                                <i class="bi bi-box-arrow-right"></i> <?php echo __('logout'); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Handle language and calendar changes -->
    <?php
    if (isset($_GET['set_lang'])) {
        setLang($_GET['set_lang']);
        redirect($_SERVER['PHP_SELF']);
    }
    
    if (isset($_GET['set_calendar'])) {
        setCalendar($_GET['set_calendar']);
        redirect($_SERVER['PHP_SELF']);
    }
    ?>
