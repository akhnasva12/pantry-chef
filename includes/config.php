<?php
// includes/config.php

// ---- Error display ----
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ---- BASE PATH DETECTION ----
if (!defined('BASE_PATH')) {

    // __DIR__ gives the absolute filesystem path to /includes folder
    // DOCUMENT_ROOT gives the webserver root
    // Difference = the web path to our project

    $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $projRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

    // Web path = filesystem path minus doc root
    $basePath = str_replace($docRoot, '', $projRoot);
    $basePath = rtrim($basePath, '/');

    // Fallback: use SCRIPT_NAME method if above gives empty or wrong result
    if (empty($basePath) || $basePath === $docRoot) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        foreach (['/ajax/', '/creator/', '/admin/', '/components/', '/includes/'] as $sub) {
            $pos = strpos($script, $sub);
            if ($pos !== false) { $script = substr($script, 0, $pos); break; }
        }
        if (substr($script, -4) === '.php') $script = dirname($script);
        $basePath = rtrim($script, '/');
    }

    define('BASE_PATH', $basePath);
    define('BASE_URL',  $basePath . '/');
    define('ASSETS',    $basePath . '/assets');
    define('UPLOADS',   $basePath . '/uploads');
}

// ---- Settings ----
define('SITE_NAME',       'PantryChef');
define('SITE_VERSION',    '1.0.0');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('UPLOAD_AVATAR_SIZE', 2 * 1024 * 1024);
define('ALLOWED_IMG_TYPES',  ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_RECIPE_DIR',  __DIR__ . '/../uploads/recipes/');
define('UPLOAD_CREATOR_DIR', __DIR__ . '/../uploads/creators/');
define('RECIPES_PER_PAGE', 8);
define('CURRENCY_SYMBOL', '₹');