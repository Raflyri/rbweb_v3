<?php
/**
 * RBeverything Deployment Unzip Script
 * Automatically created to handle CI/CD deployments.
 */

ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');
ini_set('display_errors', 0);

$host = $_SERVER['HTTP_HOST'] ?? '';
$isSandbox = (strpos($host, 'sbox') !== false || strpos($host, 'sandbox') !== false);

$publicDir = __DIR__;

// Traverse up to find the root folder alongside `public_html/` where `projects/` sits.
// Typically cPanel has paths like /home/username/public_html
// So we want to find /home/username/projects
$baseDir = dirname($publicDir);
while ($baseDir && !file_exists($baseDir . '/projects') && strlen($baseDir) > 1) {
    if (basename($baseDir) === 'public_html') {
        $baseDir = dirname($baseDir);
        break;
    }
    $baseDir = dirname($baseDir);
}

if (!file_exists($baseDir . '/projects')) {
    // Fallback static paths if directory traversal failed
    if ($isSandbox) {
        $coreDir = dirname(dirname(__DIR__)) . '/projects/sandbox';
    } else {
        $coreDir = dirname(__DIR__) . '/projects/production';
    }
} else {
    $coreDir = $baseDir . '/projects/' . ($isSandbox ? 'sandbox' : 'production');
}

$coreZip = $coreDir . '/core.zip';
$publicZip = $publicDir . '/public.zip';

// Read DEPLOY_UNZIP_KEY from a persistent key file placed manually on the server once.
// This avoids a chicken-and-egg problem where the .env doesn't exist before first extraction.
// To set up: create the file with: echo "your-secret-key" > ~/deploy.key && chmod 600 ~/deploy.key
$validKey = '';
$keyFilePaths = [
    dirname(dirname($publicDir)) . '/deploy.key',          // e.g. /home/user/deploy.key (via public_html/subdomain)
    dirname($publicDir) . '/deploy.key',                   // e.g. /home/user/deploy.key (via public_html directly)
    getenv('HOME') ? getenv('HOME') . '/deploy.key' : '',  // Fallback via HOME env var
];
foreach ($keyFilePaths as $keyFilePath) {
    if ($keyFilePath && file_exists($keyFilePath)) {
        $validKey = trim(file_get_contents($keyFilePath));
        break;
    }
}

$providedKey = $_GET['key'] ?? '';
$isDebug = ($_GET['debug'] ?? '') === '1';

// ------ TEMPORARY DEBUG MODE (remove after troubleshooting) ------
if ($isDebug) {
    header('Content-Type: text/plain');
    echo "=== deploy-unzip.php DEBUG ===\n";
    echo "publicDir : {$publicDir}\n";
    echo "baseDir   : {$baseDir}\n";
    echo "coreDir   : {$coreDir}\n";
    echo "isSandbox : " . ($isSandbox ? 'true' : 'false') . "\n";
    echo "\nKey file candidates:\n";
    foreach ($keyFilePaths as $p) {
        $exists = $p && file_exists($p) ? 'EXISTS ✓' : 'NOT FOUND';
        echo "  [{$exists}] {$p}\n";
    }
    echo "\nvalidKey found: " . (empty($validKey) ? 'NO (empty)' : 'YES (' . strlen($validKey) . ' chars)') . "\n";
    echo "providedKey  : " . (empty($providedKey) ? '(empty)' : '*** (' . strlen($providedKey) . ' chars)') . "\n";
    exit;
}
// ------ END DEBUG ------

// Validate key
if (empty($validKey) || !hash_equals($validKey, $providedKey)) {
    http_response_code(403);
    die('Unauthorized access.');
}

// Function to safely extract a zip archive
function extractZip($zipPath, $extractTo) {
    if (!file_exists($zipPath)) {
        return "Missing: {$zipPath}";
    }
    
    $zip = new ZipArchive;
    $res = $zip->open($zipPath);
    if ($res === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        @unlink($zipPath); // Delete the zip file immediately after successfully unzipping
        return "Successfully Extracted to: {$extractTo}";
    }
    return "Failed Extracting (ZipArchive error code {$res}): {$zipPath}";
}

$output = "Deploy Unzip Log\n----------------\n";
$output .= "Core Arch: " . extractZip($coreZip, $coreDir) . "\n";
$output .= "Pub  Arch: " . extractZip($publicZip, $publicDir) . "\n";

// Execute migrations directly via CLI to bypass Laravel HTTP Kernel boot issues (like MissingSettings exceptions)
$output .= "\nSystem Updates Logs\n----------------\n";
if (file_exists($coreDir . '/artisan')) {
    $cmdMigrate = 'cd ' . escapeshellarg($coreDir) . ' && php artisan migrate --force 2>&1';
    $output .= "Migrate Output:\n" . shell_exec($cmdMigrate) . "\n";
    
    $cmdCache = 'cd ' . escapeshellarg($coreDir) . ' && php artisan optimize:clear 2>&1';
    $output .= "Cache Output:\n" . shell_exec($cmdCache) . "\n";
} else {
    $output .= "Warning: Artisan not found at {$coreDir}/artisan\n";
}

echo $output;

// Self-destruct after running for immediate security.
// The CI/CD process will upload this file again on the next deploy.
@unlink(__FILE__);
