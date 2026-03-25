<?php
/**
 * RBeverything Path Diagnostic
 * Upload to public_html/web-sbox.rbeverything.com/ to debug index.php path resolution
 * ⚠️ DELETE AFTER USE!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║     RBeverything Path Diagnostic                    ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

$publicDir = __DIR__;
$parentDir = dirname($publicDir);

echo "═══ CURRENT index.php PATH RESOLUTION ═══\n";
echo "__DIR__              : {$publicDir}\n";
echo "__DIR__.'/../'       : " . realpath($publicDir . '/../') . "\n";
echo "\n";

echo "═══ WHERE index.php IS LOOKING FOR FILES ═══\n";
$paths = [
    'maintenance.php' => $publicDir . '/../storage/framework/maintenance.php',
    'autoload.php'    => $publicDir . '/../vendor/autoload.php',
    'bootstrap/app'   => $publicDir . '/../bootstrap/app.php',
];

foreach ($paths as $label => $path) {
    $resolved = realpath(dirname($path)) ? realpath(dirname($path)) . '/' . basename($path) : $path;
    $exists = file_exists($path) ? '✅ EXISTS' : '❌ NOT FOUND';
    echo "  {$label}:\n";
    echo "    Path   : {$path}\n";
    echo "    Actual : {$resolved}\n";
    echo "    Status : {$exists}\n\n";
}

echo "═══ WHERE FILES ACTUALLY ARE ═══\n";
$host = $_SERVER['HTTP_HOST'] ?? '';
$isSandbox = (strpos($host, 'sbox') !== false || strpos($host, 'sandbox') !== false);
$env = $isSandbox ? 'sandbox' : 'production';

// Find the home directory
$homeDir = dirname($publicDir);
while ($homeDir && !file_exists($homeDir . '/projects') && strlen($homeDir) > 1) {
    if (basename($homeDir) === 'public_html') {
        $homeDir = dirname($homeDir);
        break;
    }
    $homeDir = dirname($homeDir);
}

$correctBase = $homeDir . '/projects/' . $env;
echo "  Home directory     : {$homeDir}\n";
echo "  Correct project dir: {$correctBase}\n";
echo "  autoload.php       : " . (file_exists($correctBase . '/vendor/autoload.php') ? '✅ EXISTS' : '❌') . "\n";
echo "  bootstrap/app.php  : " . (file_exists($correctBase . '/bootstrap/app.php') ? '✅ EXISTS' : '❌') . "\n";
echo "\n";

echo "═══ 🔑 DIAGNOSIS ═══\n";
if (!file_exists($publicDir . '/../vendor/autoload.php') && file_exists($correctBase . '/vendor/autoload.php')) {
    echo "  🚨 CONFIRMED: index.php uses relative path '../' which resolves to:\n";
    echo "     " . realpath($publicDir . '/../') . "\n";
    echo "     But your Laravel app is at:\n";
    echo "     {$correctBase}\n\n";
    echo "  ✏️  FIX: Your index.php needs to point to the correct project directory.\n";
    echo "     Replace the relative paths in index.php with:\n\n";
    echo "     define('APP_BASE_PATH', '{$correctBase}');\n\n";
    echo "     Then change all __DIR__.'/../' references to APP_BASE_PATH.'/'\n";
} else {
    echo "  Paths appear correct.\n";
}

echo "\n⚠️  DELETE THIS FILE NOW!\n";
