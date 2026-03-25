<?php
/**
 * RBeverything Storage Repair Script v2
 * ⚠️ DELETE THIS FILE AFTER USE!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║     RBeverything Storage Repair v2.0                ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

$host = $_SERVER['HTTP_HOST'] ?? 'unknown';
$isSandbox = (strpos($host, 'sbox') !== false || strpos($host, 'sandbox') !== false);
$publicDir = __DIR__;

$baseDir = dirname($publicDir);
while ($baseDir && !file_exists($baseDir . '/projects') && strlen($baseDir) > 1) {
    if (basename($baseDir) === 'public_html') {
        $baseDir = dirname($baseDir);
        break;
    }
    $baseDir = dirname($baseDir);
}
$projectDir = $baseDir . '/projects/' . ($isSandbox ? 'sandbox' : 'production');

echo "Environment: " . ($isSandbox ? 'SANDBOX' : 'PRODUCTION') . "\n";
echo "Project Dir: {$projectDir}\n\n";

// ── 0. DISK SPACE CHECK (do this FIRST) ─────────────────────────
echo "═══ 0. DISK SPACE CHECK ═══\n";
$freeBytes = @disk_free_space($baseDir);
$totalBytes = @disk_total_space($baseDir);
if ($freeBytes !== false && $totalBytes !== false) {
    $usedBytes = $totalBytes - $freeBytes;
    $freeMB = round($freeBytes / 1024 / 1024, 1);
    $totalMB = round($totalBytes / 1024 / 1024, 1);
    $usedMB = round($usedBytes / 1024 / 1024, 1);
    $pctUsed = round(($usedBytes / $totalBytes) * 100, 1);
    echo "  Total : {$totalMB} MB\n";
    echo "  Used  : {$usedMB} MB ({$pctUsed}%)\n";
    echo "  Free  : {$freeMB} MB\n";
    if ($freeBytes < 1024 * 1024) {
        echo "\n  🚨🚨🚨 DISK IS FULL! THIS IS YOUR PROBLEM! 🚨🚨🚨\n";
        echo "  You have less than 1MB free. Laravel cannot write logs, cache, or sessions.\n";
        echo "  → See cleanup instructions at the bottom of this output.\n";
    } elseif ($freeBytes < 50 * 1024 * 1024) {
        echo "  ⚠️ Disk space is low (< 50MB free).\n";
    } else {
        echo "  ✅ Disk space looks OK.\n";
    }
} else {
    echo "  ⚠️ Could not determine disk space.\n";
    echo "  → Check cPanel sidebar → 'Disk Space Usage' manually.\n";
}
echo "\n";

// ── 1. WRITE TEST (can we actually write files?) ────────────────
echo "═══ 1. WRITE PERMISSION TEST ═══\n";
$testLocations = [
    $projectDir . '/storage/logs'           => 'storage/logs/',
    $projectDir . '/storage/framework/cache' => 'storage/framework/cache/',
    $projectDir . '/bootstrap/cache'        => 'bootstrap/cache/',
    sys_get_temp_dir()                       => 'PHP temp dir',
];
$canWriteAnywhere = false;
foreach ($testLocations as $dir => $label) {
    if (!is_dir($dir)) {
        echo "  ❌ {$label} — directory MISSING\n";
        continue;
    }
    $testFile = $dir . '/.write_test_' . time();
    $result = @file_put_contents($testFile, 'test');
    if ($result !== false) {
        @unlink($testFile);
        echo "  ✅ {$label} — writable\n";
        $canWriteAnywhere = true;
    } else {
        $err = error_get_last();
        $errMsg = $err ? $err['message'] : 'unknown error';
        echo "  ❌ {$label} — NOT writable: {$errMsg}\n";
    }
}
echo "\n";

if (!$canWriteAnywhere) {
    echo "  🚨 CANNOT WRITE TO ANY DIRECTORY!\n";
    echo "  Possible causes:\n";
    echo "  1. Disk quota is FULL (most likely if both sites died at once)\n";
    echo "  2. File ownership mismatch\n";
    echo "  3. open_basedir restriction\n\n";
}

// ── 2. Create storage directories (if missing) ─────────────────
echo "═══ 2. CREATING STORAGE DIRECTORIES ═══\n";
$dirs = [
    $projectDir . '/storage',
    $projectDir . '/storage/app',
    $projectDir . '/storage/app/public',
    $projectDir . '/storage/app/private',
    $projectDir . '/storage/framework',
    $projectDir . '/storage/framework/cache',
    $projectDir . '/storage/framework/cache/data',
    $projectDir . '/storage/framework/sessions',
    $projectDir . '/storage/framework/testing',
    $projectDir . '/storage/framework/views',
    $projectDir . '/storage/logs',
    $projectDir . '/bootstrap/cache',
];
foreach ($dirs as $dir) {
    $shortDir = str_replace($baseDir, '~', $dir);
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? 'writable' : '⚠️ NOT writable';
        echo "  ✔ {$shortDir} [{$perms}] ({$writable})\n";
    } else {
        if (@mkdir($dir, 0775, true)) {
            echo "  ✅ Created: {$shortDir}\n";
        } else {
            echo "  ❌ FAILED: {$shortDir}\n";
        }
    }
}
echo "\n";

// ── 3. Fix permissions ──────────────────────────────────────────
echo "═══ 3. FIXING PERMISSIONS ═══\n";
$fixDirs = [$projectDir . '/storage', $projectDir . '/bootstrap/cache'];
foreach ($fixDirs as $dir) {
    if (is_dir($dir)) {
        @chmod($dir, 0775);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $count = 0;
        foreach ($iterator as $item) {
            @chmod($item->getRealPath(), $item->isDir() ? 0775 : 0664);
            $count++;
        }
        $shortDir = str_replace($baseDir, '~', $dir);
        echo "  ✅ Fixed: {$shortDir} ({$count} items)\n";
    }
}
echo "\n";

// ── 4. Create laravel.log ───────────────────────────────────────
echo "═══ 4. CREATING laravel.log ═══\n";
$logFile = $projectDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "  ✔ Already exists (" . filesize($logFile) . " bytes)\n";
} else {
    $result = @file_put_contents($logFile, '');
    if ($result !== false) {
        @chmod($logFile, 0664);
        echo "  ✅ Created!\n";
    } else {
        $err = error_get_last();
        echo "  ❌ FAILED: " . ($err ? $err['message'] : 'unknown error') . "\n";
        echo "  → This confirms a file-write issue (disk full or permissions).\n";
    }
}
echo "\n";

// ── 5. Storage symlink (safe check) ─────────────────────────────
echo "═══ 5. STORAGE SYMLINK ═══\n";
$storageLink = $publicDir . '/storage';
if (is_link($storageLink)) {
    echo "  ✔ Symlink exists: → " . readlink($storageLink) . "\n";
} elseif (is_dir($storageLink)) {
    echo "  ⚠️ 'storage' exists as a regular directory, not a symlink.\n";
} else {
    // Check if symlink() function is available
    if (function_exists('symlink')) {
        $storageTarget = $projectDir . '/storage/app/public';
        if (@symlink($storageTarget, $storageLink)) {
            echo "  ✅ Created symlink\n";
        } else {
            echo "  ❌ FAILED to create symlink\n";
            echo "  → symlink() exists but failed. Ask hosting support to create it.\n";
        }
    } else {
        echo "  ⚠️ symlink() is DISABLED by your hosting provider.\n";
        echo "  → This is common on shared hosting.\n";
        echo "  → Contact Hosting Support and ask them to run:\n";
        echo "     ln -s {$projectDir}/storage/app/public {$storageLink}\n";
        echo "  → OR use cPanel Terminal (if available under 'Advanced').\n";
    }
}
echo "\n";

// ── 6. Clear caches ─────────────────────────────────────────────
echo "═══ 6. CLEARING CACHES ═══\n";
$cacheFiles = glob($projectDir . '/bootstrap/cache/*.php') ?: [];
$count = 0;
foreach ($cacheFiles as $file) {
    @unlink($file);
    $count++;
}
echo "  ✅ Cleared {$count} cached files from bootstrap/cache/\n";

$viewsDir = $projectDir . '/storage/framework/views';
if (is_dir($viewsDir)) {
    $viewFiles = glob($viewsDir . '/*.php') ?: [];
    $vCount = count($viewFiles);
    foreach ($viewFiles as $vf) { @unlink($vf); }
    echo "  ✅ Cleared {$vCount} compiled views\n";
}
echo "\n";

// ── 7. Find large files eating disk space ───────────────────────
echo "═══ 7. LARGE FILES (potential cleanup) ═══\n";
$scanDirs = [
    $baseDir . '/projects/production/storage/logs',
    $baseDir . '/projects/sandbox/storage/logs',
    $baseDir . '/public_html',
];
$largeFiles = [];
foreach ($scanDirs as $scanDir) {
    if (!is_dir($scanDir)) continue;
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if ($file->isFile() && $file->getSize() > 1024 * 1024) { // > 1MB
            $largeFiles[] = [
                'path' => str_replace($baseDir, '~', $file->getRealPath()),
                'size' => round($file->getSize() / 1024 / 1024, 1),
            ];
        }
    }
}

// Also check for leftover zip files
$zipLocations = [
    $baseDir . '/projects/production/core.zip',
    $baseDir . '/projects/sandbox/core.zip',
    $baseDir . '/public_html/core.zip',
    $baseDir . '/public_html/public.zip',
    $baseDir . '/public_html/web-sbox.rbeverything.com/core.zip',
    $baseDir . '/public_html/web-sbox.rbeverything.com/public.zip',
];
foreach ($zipLocations as $zip) {
    if (file_exists($zip)) {
        $largeFiles[] = [
            'path' => str_replace($baseDir, '~', $zip),
            'size' => round(filesize($zip) / 1024 / 1024, 1),
        ];
    }
}

usort($largeFiles, function($a, $b) { return $b['size'] <=> $a['size']; });

if (empty($largeFiles)) {
    echo "  No files > 1MB found in scanned directories.\n";
} else {
    echo "  Files > 1MB (candidates for deletion/cleanup):\n";
    foreach (array_slice($largeFiles, 0, 20) as $f) {
        echo "  📁 {$f['size']} MB — {$f['path']}\n";
    }
}
echo "\n";

// ── 8. ATTEMPT LARAVEL BOOT ─────────────────────────────────────
echo "═══ 8. LARAVEL BOOT TEST ═══\n";
$autoload = $projectDir . '/vendor/autoload.php';
$bootstrap = $projectDir . '/bootstrap/app.php';
if (file_exists($autoload) && file_exists($bootstrap)) {
    try {
        chdir($projectDir);
        require $autoload;
        $app = require $bootstrap;
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        echo "  ✅ Laravel booted successfully!\n";
    } catch (\Throwable $e) {
        echo "  ❌ Laravel FAILED to boot!\n";
        echo "  Exception: " . get_class($e) . "\n";
        echo "  Message  : " . $e->getMessage() . "\n";
        echo "  File     : " . str_replace($baseDir, '~', $e->getFile()) . "\n";
        echo "  Line     : " . $e->getLine() . "\n\n";
        echo "  Stack trace (top 8):\n";
        foreach (array_slice($e->getTrace(), 0, 8) as $i => $f) {
            $file = isset($f['file']) ? str_replace($baseDir, '~', $f['file']) : '(internal)';
            $line = $f['line'] ?? '?';
            $func = ($f['class'] ?? '') . ($f['type'] ?? '') . ($f['function'] ?? '?');
            echo "    #{$i} {$file}:{$line} → {$func}()\n";
        }
    }
} else {
    echo "  ❌ Missing autoload.php or bootstrap/app.php\n";
}

echo "\n══════════════════════════════════════════════════════\n";
echo "⚠️  DELETE THIS FILE NOW!\n";
echo "══════════════════════════════════════════════════════\n";
