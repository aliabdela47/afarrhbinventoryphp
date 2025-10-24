<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

logout();
setFlash('success', __('logout_success'));
redirect(baseUrl('login.php'));
