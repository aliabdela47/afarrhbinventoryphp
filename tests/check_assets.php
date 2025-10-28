<?php
/**
 * Asset Checker Test
 * Verifies that all assets referenced in header.php exist
 */

// Configuration
define('BASE_PATH', dirname(__DIR__));
define('TESTS_PASSED', 0);
define('TESTS_FAILED', 1);

$errors = [];
$warnings = [];
$success = [];

echo "==========================================\n";
echo "Asset Checker Test\n";
echo "==========================================\n\n";

// Check if header file exists
$headerFile = BASE_PATH . '/includes/header_new.php';
if (!file_exists($headerFile)) {
    $errors[] = "Header file not found: $headerFile";
} else {
    echo "✓ Header file found\n";
    $headerContent = file_get_contents($headerFile);
    
    // Extract asset URLs from header
    $assetPattern = '/(?:href|src)=["\']([^"\']+\.(?:css|js|ico|png|jpg|jpeg|gif|svg))["\']/' ;
    preg_match_all($assetPattern, $headerContent, $matches);
    
    $assetUrls = $matches[1];
    
    echo "\nChecking " . count($assetUrls) . " asset references...\n\n";
    
    foreach ($assetUrls as $url) {
        // Skip external CDN URLs
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            echo "⊙ Skipping external URL: $url\n";
            continue;
        }
        
        // Skip PHP variables
        if (strpos($url, '<?php') !== false) {
            continue;
        }
        
        // Convert URL to file path
        $filePath = BASE_PATH . '/' . ltrim(str_replace('<?php echo APP_URL; ?>/', '', $url), '/');
        $filePath = preg_replace('/\?.*$/', '', $filePath); // Remove query strings
        
        if (file_exists($filePath)) {
            $success[] = $filePath;
            echo "✓ Asset exists: $filePath\n";
        } else {
            $errors[] = "Asset missing: $filePath (referenced in header as: $url)";
            echo "✗ Asset MISSING: $filePath\n";
        }
    }
}

// Check critical asset directories
echo "\n\nChecking critical asset directories...\n\n";

$criticalDirs = [
    '/assets/vendor/css',
    '/assets/vendor/js',
    '/assets/vendor/libs/jquery',
    '/assets/vendor/libs/apex-charts',
    '/assets/vendor/libs/datatables-bs5',
    '/assets/css',
    '/assets/js',
    '/assets/img/favicon'
];

foreach ($criticalDirs as $dir) {
    $dirPath = BASE_PATH . $dir;
    if (is_dir($dirPath)) {
        $fileCount = count(glob($dirPath . '/*'));
        echo "✓ Directory exists: $dir (contains $fileCount files)\n";
        $success[] = $dirPath;
    } else {
        $errors[] = "Critical directory missing: $dir";
        echo "✗ Directory MISSING: $dir\n";
    }
}

// Check specific critical files
echo "\n\nChecking critical asset files...\n\n";

$criticalFiles = [
    '/assets/vendor/css/core.css',
    '/assets/css/demo.css',
    '/assets/css/afarrh_custom.css',
    '/assets/js/main.js',
    '/assets/js/config.js',
    '/assets/vendor/js/helpers.js',
    '/assets/vendor/libs/jquery/jquery.js',
    '/assets/vendor/libs/apex-charts/apexcharts.js',
    '/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.js'
];

foreach ($criticalFiles as $file) {
    $filePath = BASE_PATH . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        echo "✓ File exists: $file (" . round($size/1024, 2) . " KB)\n";
        $success[] = $filePath;
        
        // Check if file contains TODO comments (indicating shims)
        $content = file_get_contents($filePath);
        if (strpos($content, 'TODO') !== false) {
            $warnings[] = "File contains TODO comments (likely a shim): $file";
        }
    } else {
        $errors[] = "Critical file missing: $file";
        echo "✗ File MISSING: $file\n";
    }
}

// Summary
echo "\n\n==========================================\n";
echo "Test Summary\n";
echo "==========================================\n";
echo "✓ Passed: " . count($success) . "\n";
echo "⚠ Warnings: " . count($warnings) . "\n";
echo "✗ Failed: " . count($errors) . "\n\n";

if (count($warnings) > 0) {
    echo "Warnings:\n";
    foreach ($warnings as $warning) {
        echo "  ⚠ $warning\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo "  ✗ $error\n";
    }
    echo "\n";
    echo "RESULT: FAILED\n";
    exit(TESTS_FAILED);
}

echo "RESULT: PASSED\n";
echo "\nNote: Some assets are shimmed (contain TODO comments).\n";
echo "Replace these with original template files when available.\n";
exit(TESTS_PASSED);
