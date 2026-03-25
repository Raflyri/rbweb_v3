<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Resolve the Laravel Application Base Path
|--------------------------------------------------------------------------
|
| Standard Laravel uses __DIR__.'/../' which works when public/ is a direct
| child of the project root. On cPanel shared hosting, the directory layout
| is split: public files in public_html/ and app code in ~/projects/.
|
| This logic auto-detects the correct path for both environments:
|   - Local dev:  __DIR__/../  (standard Laravel)
|   - cPanel:     ~/projects/{production|sandbox}/
|
*/

$basePath = realpath(__DIR__ . '/../');

// Check if we're in a cPanel split-directory setup
// (i.e., vendor/autoload.php does NOT exist at the standard relative path)
if (!file_exists($basePath . '/vendor/autoload.php')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isSandbox = (strpos($host, 'sbox') !== false || strpos($host, 'sandbox') !== false);

    // Walk up from public dir to find the home directory containing 'projects/'
    $searchDir = dirname(__DIR__);
    while ($searchDir && strlen($searchDir) > 1) {
        if (basename($searchDir) === 'public_html') {
            $searchDir = dirname($searchDir);
            break;
        }
        if (file_exists($searchDir . '/projects')) {
            break;
        }
        $searchDir = dirname($searchDir);
    }

    $basePath = $searchDir . '/projects/' . ($isSandbox ? 'sandbox' : 'production');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
