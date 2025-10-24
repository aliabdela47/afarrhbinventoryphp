    </main>
    
    <!-- Footer -->
    <footer class="mt-5 py-3 border-top text-center text-muted" style="margin-left: var(--sidebar-width);">
        <div class="container-fluid">
            <small>
                &copy; <?php echo date('Y'); ?> <?php echo __('app_name'); ?> v<?php echo APP_VERSION; ?>
                | Afar Regional Health Bureau
            </small>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('expanded');
        });
        
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            
            // Update icon
            const icon = themeToggle.querySelector('i');
            icon.className = newTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
            
            // Save preference
            fetch('<?php echo baseUrl('api/set-theme.php'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({theme: newTheme})
            });
        });
        
        // Notifications
        document.getElementById('notificationsBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '<?php echo __('notifications'); ?>',
                html: `
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">New request pending</h6>
                                <small>3 mins ago</small>
                            </div>
                            <p class="mb-1">Request #20240001 awaits approval</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Low stock alert</h6>
                                <small>1 hour ago</small>
                            </div>
                            <p class="mb-1">Item "Medical Gloves" running low</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Issuance completed</h6>
                                <small>2 hours ago</small>
                            </div>
                            <p class="mb-1">Issuance #22240001 has been completed</p>
                        </div>
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: false,
                width: 600
            });
        });
        
        // Global Search
        document.getElementById('globalSearch')?.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const query = this.value;
                if (query.length > 0) {
                    window.location.href = '<?php echo baseUrl('search.php'); ?>?q=' + encodeURIComponent(query);
                }
            }
        });
        
        // Flash Messages
        <?php
        $success = getFlash('success');
        $error = getFlash('error');
        $warning = getFlash('warning');
        $info = getFlash('info');
        ?>
        
        <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: '<?php echo __('success'); ?>',
            text: '<?php echo e($success); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        <?php endif; ?>
        
        <?php if ($error): ?>
        Swal.fire({
            icon: 'error',
            title: '<?php echo __('error'); ?>',
            text: '<?php echo e($error); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        <?php endif; ?>
        
        <?php if ($warning): ?>
        Swal.fire({
            icon: 'warning',
            title: '<?php echo __('warning'); ?>',
            text: '<?php echo e($warning); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        <?php endif; ?>
        
        <?php if ($info): ?>
        Swal.fire({
            icon: 'info',
            title: '<?php echo __('info'); ?>',
            text: '<?php echo e($info); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        <?php endif; ?>
        
        // Confirm Delete
        function confirmDelete(url, message = '<?php echo __('confirm_delete'); ?>') {
            Swal.fire({
                title: '<?php echo __('confirm'); ?>',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?php echo __('yes'); ?>',
                cancelButtonText: '<?php echo __('cancel'); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
</body>
</html>
