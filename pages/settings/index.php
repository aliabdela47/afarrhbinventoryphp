<?php
/**
 * Settings Page
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireRole('admin');

$pageTitle = t('settings') . ' - ' . APP_NAME;

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="mb-4">
            <h2><i class="bi bi-gear"></i> <?php echo e(t('settings')); ?></h2>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo e(t('language')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Current Language: <strong><?php echo $_SESSION['lang'] === 'en' ? 'English' : 'አማርኛ'; ?></strong></p>
                        <div class="btn-group">
                            <button onclick="changeLanguage('en')" class="btn btn-outline-primary <?php echo $_SESSION['lang'] === 'en' ? 'active' : ''; ?>">
                                English
                            </button>
                            <button onclick="changeLanguage('am')" class="btn btn-outline-primary <?php echo $_SESSION['lang'] === 'am' ? 'active' : ''; ?>">
                                አማርኛ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo e(t('calendar_mode')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Current Calendar: <strong><?php echo ucfirst($_SESSION['calendar'] ?? 'gregorian'); ?></strong></p>
                        <div class="btn-group">
                            <button onclick="changeCalendar('gregorian')" class="btn btn-outline-primary <?php echo ($_SESSION['calendar'] ?? 'gregorian') === 'gregorian' ? 'active' : ''; ?>">
                                Gregorian
                            </button>
                            <button onclick="changeCalendar('ethiopian')" class="btn btn-outline-primary <?php echo ($_SESSION['calendar'] ?? '') === 'ethiopian' ? 'active' : ''; ?>">
                                Ethiopian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo e(t('theme')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Theme settings</p>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-sun"></i> <?php echo e(t('light_mode')); ?>
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-moon"></i> <?php echo e(t('dark_mode')); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Application Info</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>Version:</th>
                                <td><?php echo APP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <th>PHP Version:</th>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <th>Database:</th>
                                <td>MySQL</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
