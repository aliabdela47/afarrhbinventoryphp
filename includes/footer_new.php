                    <!-- Page Content Ends Here -->
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="content-footer" style="padding: 16px 24px; border-top: 1px solid #f0f0f0; margin-top: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #999;">
                    <div>
                        &copy; <?php echo date('Y'); ?> <strong><?php echo e(APP_NAME); ?></strong> v<?php echo e(APP_VERSION); ?>
                    </div>
                    <div>
                        <?php echo t('developed_by'); ?> <strong>Afar RHB IT Team</strong>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Core JavaScript Libraries -->
    <script src="<?php echo APP_URL; ?>/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/vendor/js/helpers.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/vendor/libs/bootstrap/bootstrap.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/config.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <!-- Charts library if needed -->
    <?php if (isset($includeCharts) && $includeCharts): ?>
    <script src="<?php echo APP_URL; ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <?php endif; ?>
    
    <!-- DataTables if needed -->
    <?php if (isset($includeDatatables) && $includeDatatables): ?>
    <script src="<?php echo APP_URL; ?>/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.js"></script>
    <?php endif; ?>
    
    <!-- Leaflet for maps -->
    <?php if (isset($includeLeaflet) && $includeLeaflet): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>
    
    <!-- Language and Calendar change functions -->
    <script>
        function changeLanguage(lang) {
            fetch('<?php echo APP_URL; ?>/api/change-language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'lang=' + lang + '&csrf_token=<?php echo generateCsrfToken(); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
        
        function changeCalendar(calendar) {
            fetch('<?php echo APP_URL; ?>/api/change-calendar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'calendar=' + calendar + '&csrf_token=<?php echo generateCsrfToken(); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    </script>
    
    <!-- Page specific scripts -->
    <?php if (isset($pageScripts)): echo $pageScripts; endif; ?>
    
    <!-- Display flash messages with SweetAlert-style toast -->
    <?php if ($flash = getFlash()): ?>
    <script>
        (function() {
            const type = '<?php echo e($flash['type']); ?>';
            const message = '<?php echo addslashes($flash['message']); ?>';
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 16px 24px; background: #fff; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; max-width: 300px; opacity: 0; transition: opacity 0.3s;';
            
            let bgColor = '#26a69a';
            if (type === 'danger' || type === 'error') bgColor = '#ef5350';
            else if (type === 'warning') bgColor = '#ffa726';
            else if (type === 'info') bgColor = '#29b6f6';
            
            toast.innerHTML = '<div style="display: flex; align-items: center; gap: 12px;"><div style="width: 4px; height: 40px; background: ' + bgColor + '; border-radius: 2px;"></div><div style="flex: 1;">' + message + '</div></div>';
            
            document.body.appendChild(toast);
            
            setTimeout(() => { toast.style.opacity = '1'; }, 100);
            setTimeout(() => { 
                toast.style.opacity = '0'; 
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        })();
    </script>
    <?php endif; ?>
</body>
</html>
