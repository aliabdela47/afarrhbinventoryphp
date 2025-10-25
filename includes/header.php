<?php
/**
 * Page Header
 * AfarRHB Inventory Management System
 */

if (!isAuthenticated()) {
    redirect('index.php');
}

$currentUser = getUserFullName();
$currentRole = getUserRole();
$currentLang = $_SESSION['lang'] ?? 'en';
$currentCalendar = $_SESSION['calendar'] ?? 'gregorian';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? APP_NAME); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Header */
        .main-header {
            height: var(--header-height);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .brand {
            font-size: 1.25rem;
            font-weight: 600;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .brand:hover {
            color: white;
        }
        
        .header-search {
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
        }
        
        .header-search input {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }
        
        .header-search input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .header-search input:focus {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            color: white;
        }
        
        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .header-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        .notification-badge {
            position: relative;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #2c3e50;
            color: white;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1020;
        }
        
        .sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            background: #f8f9fa;
            transition: margin-left 0.3s;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Cards */
        .metric-card {
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .metric-card.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .metric-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .metric-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .metric-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .metric-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        /* Footer */
        .main-footer {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            text-align: center;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s;
        }
        
        .main-footer.expanded {
            margin-left: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content,
            .main-footer {
                margin-left: 0;
            }
            
            .header-search {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <button class="btn header-btn" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <a href="dashboard.php" class="brand ms-3">
            <i class="bi bi-box-seam"></i>
            <span><?php echo e(APP_NAME); ?></span>
        </a>
        
        <div class="header-search">
            <input type="text" class="form-control" placeholder="<?php echo e(t('search')); ?>..." id="globalSearch">
        </div>
        
        <div class="header-controls">
            <!-- Language Toggle -->
            <div class="dropdown">
                <button class="btn header-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-translate"></i>
                    <span><?php echo $currentLang === 'en' ? 'EN' : 'አማ'; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="changeLanguage('en')">English</a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeLanguage('am')">አማርኛ</a></li>
                </ul>
            </div>
            
            <!-- Calendar Toggle -->
            <div class="dropdown">
                <button class="btn header-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar3"></i>
                    <span><?php echo ucfirst($currentCalendar); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="changeCalendar('gregorian')">Gregorian</a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeCalendar('ethiopian')">Ethiopian</a></li>
                </ul>
            </div>
            
            <!-- Notifications -->
            <button class="btn header-btn notification-badge" type="button">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger">3</span>
            </button>
            
            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn header-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span><?php echo e($currentUser); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> <?php echo e(t('profile')); ?></a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> <?php echo e(t('settings')); ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> <?php echo e(t('logout')); ?></a></li>
                </ul>
            </div>
        </div>
    </header>
