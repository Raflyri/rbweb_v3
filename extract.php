<?php

$key = $_GET['key'] ?? '';
$expectedKey = '{{DEPLOY_KEY}}';

if ($key !== $expectedKey) {
    http_response_code(401);
    die('Unauthorized');
}

$success = true;

// 1. Extract Core
$coreZip = new ZipArchive;
if ($coreZip->open('core.zip') === TRUE) {
    $coreDir = '../rbeverything-core';
    if (!is_dir($coreDir)) {
        mkdir($coreDir, 0755, true);
    }
    $coreZip->extractTo($coreDir . '/');
    $coreZip->close();
    unlink('core.zip');
    echo "Core extracted successfully.\n";
} else {
    echo "Failed to open core.zip.\n";
    $success = false;
}

// 2. Extract Public
$publicZip = new ZipArchive;
if ($publicZip->open('public.zip') === TRUE) {
    $publicZip->extractTo('./');
    $publicZip->close();
    unlink('public.zip');
    echo "Public extracted successfully.\n";
} else {
    echo "Failed to open public.zip.\n";
    $success = false;
}

// 3. Update paths in index.php
$indexFile = './index.php';
if (file_exists($indexFile)) {
    $content = file_get_contents($indexFile);
    $content = str_replace(
        "__DIR__.'/../bootstrap/app.php'",
        "__DIR__.'/../rbeverything-core/bootstrap/app.php'",
        $content
    );
    $content = str_replace(
        "__DIR__.'/../vendor/autoload.php'",
        "__DIR__.'/../rbeverything-core/vendor/autoload.php'",
        $content
    );
    file_put_contents($indexFile, $content);
    echo "index.php paths adjusted for shared hosting.\n";
}

// 4. Clean up extract script
if ($success) {
    unlink(__FILE__);
    echo "Deployment tasks finished. Extract script removed.\n";
}
