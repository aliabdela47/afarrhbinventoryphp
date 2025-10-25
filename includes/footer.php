    <!-- Footer -->
    <footer class="main-footer" id="mainFooter">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 text-md-start text-center">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo e(APP_NAME); ?>. <?php echo e(t('all_rights_reserved')); ?></small>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <small><?php echo e(t('version')); ?>: <?php echo e(APP_VERSION); ?></small>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
            document.getElementById('mainFooter').classList.toggle('expanded');
        });
        
        // Change language
        function changeLanguage(lang) {
            fetch('api/change-language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ lang: lang })
            }).then(() => {
                location.reload();
            });
        }
        
        // Change calendar
        function changeCalendar(calendar) {
            fetch('api/change-calendar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ calendar: calendar })
            }).then(() => {
                location.reload();
            });
        }
        
        // Global search
        document.getElementById('globalSearch').addEventListener('input', function(e) {
            const query = e.target.value;
            if (query.length >= 3) {
                // Implement search functionality
                console.log('Searching for:', query);
            }
        });
        
        // Show flash message
        <?php
        $flash = getFlash();
        if ($flash):
        ?>
        Swal.fire({
            icon: '<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>',
            title: '<?php echo $flash['type'] === 'error' ? 'Error' : 'Success'; ?>',
            text: '<?php echo e($flash['message']); ?>',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        <?php endif; ?>
        
        // Confirm delete
        function confirmDelete(url, message) {
            Swal.fire({
                title: '<?php echo e(t('delete_confirm')); ?>',
                text: message || '<?php echo e(t('delete_confirm')); ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?php echo e(t('delete')); ?>',
                cancelButtonText: '<?php echo e(t('cancel')); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            return false;
        }
    </script>
</body>
</html>
