<?php
/**
 * Login Page
 * AfarRHB Inventory Management System
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isAuthenticated()) {
    redirect('dashboard.php');
}

// Get flash message
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(t('login')); ?> - <?php echo e(APP_NAME); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h3 class="mb-0">
                    <i class="bi bi-box-seam"></i> <?php echo e(APP_NAME); ?>
                </h3>
                <p class="mb-0 mt-2 opacity-75"><?php echo e(t('welcome')); ?></p>
            </div>
            <div class="login-body">
                <form action="login.php" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> <?php echo e(t('email')); ?>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="admin@example.com" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> <?php echo e(t('password')); ?>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="••••••••" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="bi bi-box-arrow-in-right"></i> <?php echo e(t('login')); ?>
                    </button>
                </form>
                
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        Default credentials: admin@example.com / Admin@123
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 text-white">
            <small>
                &copy; <?php echo date('Y'); ?> <?php echo e(APP_NAME); ?>. <?php echo e(t('all_rights_reserved')); ?>
            </small>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Show flash message if exists
        <?php if ($flash): ?>
        Swal.fire({
            icon: '<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>',
            title: '<?php echo $flash['type'] === 'error' ? 'Error' : 'Success'; ?>',
            text: '<?php echo e($flash['message']); ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php endif; ?>
    </script>
</body>
</html>
