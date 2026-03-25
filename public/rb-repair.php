<?php
/**
 * RBeverything Storage Repair Script
 * Upload to public_html/ (production) or public_html/web-sbox.rbeverything.com/ (sandbox)
 * Visit: https://rbeverything.com/rb-repair.php
 * ⚠️ DELETE THIS FILE AFTER USE!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║     RBeverything Storage Repair v1.0                ║\n";
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

// ── 1. Create all required storage directories ──────────────────
echo "═══ 1. CREATING STORAGE DIRECTORIES ═══\n";
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
        echo "  ✔ Already exists: {$shortDir}\n";
    } else {
        if (@mkdir($dir, 0775, true)) {
            echo "  ✅ Created: {$shortDir}\n";
        } else {
            echo "  ❌ FAILED to create: {$shortDir}\n";
        }
    }
}
echo "\n";

// ── 2. Set permissions to 0775 recursively on storage & bootstrap/cache ──
echo "═══ 2. FIXING PERMISSIONS ═══\n";
$fixDirs = [
    $projectDir . '/storage',
    $projectDir . '/bootstrap/cache',
];
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
        echo "  ✅ Fixed permissions for {$shortDir} ({$count} items)\n";
    }
}
echo "\n";

// ── 3. Create .gitignore files inside storage subdirectories ────
echo "═══ 3. CREATING .gitignore FILES ═══\n";
$gitignoreContent = [
    $projectDir . '/storage/app/private/.gitignore'             => "*\n!.gitignore\n",
    $projectDir . '/storage/app/public/.gitignore'              => "*\n!.gitignore\n",
    $projectDir . '/storage/framework/cache/data/.gitignore'    => "*\n!.gitignore\n",
    $projectDir . '/storage/framework/sessions/.gitignore'      => "*\n!.gitignore\n",
    $projectDir . '/storage/framework/testing/.gitignore'       => "*\n!.gitignore\n",
    $projectDir . '/storage/framework/views/.gitignore'         => "*\n!.gitignore\n",
    $projectDir . '/storage/logs/.gitignore'                    => "*\n!.gitignore\n",
    $projectDir . '/bootstrap/cache/.gitignore'                 => "*\n!.gitignore\n",
];
foreach ($gitignoreContent as $file => $content) {
    $shortFile = str_replace($baseDir, '~', $file);
    if (file_exists($file)) {
        echo "  ✔ Already exists: {$shortFile}\n";
    } else {
        if (@file_put_contents($file, $content)) {
            echo "  ✅ Created: {$shortFile}\n";
        } else {
            echo "  ❌ FAILED: {$shortFile}\n";
        }
    }
}
echo "\n";

// ── 4. Create empty laravel.log if it doesn't exist ─────────────
echo "═══ 4. CREATING laravel.log ═══\n";
$logFile = $projectDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "  ✔ Already exists (" . filesize($logFile) . " bytes)\n";
} else {
    if (@file_put_contents($logFile, '')) {
        @chmod($logFile, 0664);
        echo "  ✅ Created empty laravel.log\n";
    } else {
        echo "  ❌ FAILED to create laravel.log\n";
    }
}
echo "\n";

// ── 5. Create storage symlink in public_html ────────────────────
echo "═══ 5. STORAGE SYMLINK ═══\n";
$storageLink = $publicDir . '/storage';
$storageTarget = $projectDir . '/storage/app/public';

if (is_link($storageLink)) {
    $currentTarget = readlink($storageLink);
    echo "  ✔ Symlink exists: {$storageLink} → {$currentTarget}\n";
} elseif (is_dir($storageLink)) {
    echo "  ⚠️ 'storage' exists as a regular directory, not a symlink.\n";
    echo "     You may need to delete it and create a symlink manually.\n";
} else {
    if (@symlink($storageTarget, $storageLink)) {
        echo "  ✅ Created symlink: {$storageLink} → {$storageTarget}\n";
    } else {
        echo "  ❌ FAILED to create symlink (may need hosting support)\n";
    }
}
echo "\n";

// ── 6. Clear cached config/routes/views ─────────────────────────
echo "═══ 6. CLEARING CACHES ═══\n";
$cacheFiles = glob($projectDir . '/bootstrap/cache/*.php');
$count = 0;
foreach ($cacheFiles as $file) {
    if (basename($file) !== '.gitignore') {
        @unlink($file);
        $count++;
    }
}
echo "  ✅ Cleared {$count} cached config/route files from bootstrap/cache/\n";

$viewsDir = $projectDir . '/storage/framework/views';
if (is_dir($viewsDir)) {
    $viewFiles = glob($viewsDir . '/*.php');
    $vCount = count($viewFiles);
    foreach ($viewFiles as $vf) { @unlink($vf); }
    echo "  ✅ Cleared {$vCount} compiled view files\n";
}
echo "\n";

// ── 7. Final verification ──────────────────────────────────────
echo "═══ 7. FINAL VERIFICATION ═══\n";
$allGood = true;
foreach ($dirs as $dir) {
    if (!is_dir($dir) || !is_writable($dir)) {
        $shortDir = str_replace($baseDir, '~', $dir);
        echo "  ❌ Still broken: {$shortDir}\n";
        $allGood = false;
    }
}
if ($allGood) {
    echo "  ✅ All directories exist and are writable!\n";
    echo "\n  → Now reload your website. If still 500, run rb-diagnose.php for more details.\n";
} else {
    echo "\n  ⚠️ Some directories still have issues.\n";
    echo "  → Try fixing permissions in cPanel File Manager manually (right-click → Change Permissions → 0775).\n";
}

echo "\n";
echo "══════════════════════════════════════════════════════\n";
echo "⚠️  DELETE THIS FILE NOW!\n";
echo "══════════════════════════════════════════════════════\n";

// Self-destruct after running
// @unlink(__FILE__);
