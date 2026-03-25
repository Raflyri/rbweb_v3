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
$envFile = $coreDir . '/.env';

// Read DEPLOY_UNZIP_KEY from the server's .env file securely
$validKey = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'DEPLOY_UNZIP_KEY=') === 0) {
            $validKey = trim(substr(trim($line), 17), '"\' ');
            break;
        }
    }
}

$providedKey = $_GET['key'] ?? '';

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

echo $output;

// Self-destruct after running for immediate security.
// The CI/CD process will upload this file again on the next deploy.
@unlink(__FILE__);
