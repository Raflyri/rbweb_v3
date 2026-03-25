<?php
/**
 * RBeverything Server Diagnostics Script
 * Upload to public_html/ (production) or public_html/web-sbox.rbeverything.com/ (sandbox)
 * Visit: https://rbeverything.com/rb-diagnose.php
 * ⚠️ DELETE THIS FILE AFTER USE — it exposes server info!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║     RBeverything Server Diagnostics v1.0            ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

// ── Detect environment ──────────────────────────────────────────
$host = $_SERVER['HTTP_HOST'] ?? 'unknown';
$isSandbox = (strpos($host, 'sbox') !== false || strpos($host, 'sandbox') !== false);
$publicDir = __DIR__;

// Find project root
$baseDir = dirname($publicDir);
while ($baseDir && !file_exists($baseDir . '/projects') && strlen($baseDir) > 1) {
    if (basename($baseDir) === 'public_html') {
        $baseDir = dirname($baseDir);
        break;
    }
    $baseDir = dirname($baseDir);
}

$projectDir = $baseDir . '/projects/' . ($isSandbox ? 'sandbox' : 'production');

echo "Host       : {$host}\n";
echo "Environment: " . ($isSandbox ? 'SANDBOX' : 'PRODUCTION') . "\n";
echo "Public Dir : {$publicDir}\n";
echo "Project Dir: {$projectDir}\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Time: " . date('Y-m-d H:i:s T') . "\n";
echo "\n";

// ── 1. PHP INFO ────────────────────────────────────────────────
echo "═══ 1. PHP CONFIGURATION ═══\n";
echo "PHP Version     : " . phpversion() . "\n";
echo "Memory Limit    : " . ini_get('memory_limit') . "\n";
echo "Max Exec Time   : " . ini_get('max_execution_time') . "s\n";
echo "Display Errors  : " . ini_get('display_errors') . "\n";
echo "Error Reporting : " . ini_get('error_reporting') . "\n";
echo "Open Basedir    : " . (ini_get('open_basedir') ?: '(none)') . "\n";
echo "Disable Functions: " . (ini_get('disable_functions') ?: '(none)') . "\n";
echo "\n";

// ── 2. REQUIRED PHP EXTENSIONS ─────────────────────────────────
echo "═══ 2. PHP EXTENSIONS ═══\n";
$required = [
    'pdo', 'pdo_mysql', 'mysqlnd', 'mysqli',
    'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json',
    'fileinfo', 'bcmath', 'gd', 'curl', 'zip', 'dom', 'intl',
];
$missingExt = [];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo ($loaded ? '  ✅' : '  ❌') . " {$ext}\n";
    if (!$loaded) $missingExt[] = $ext;
}
if (!empty($missingExt)) {
    echo "\n  ⚠️  MISSING EXTENSIONS: " . implode(', ', $missingExt) . "\n";
    echo "  → Fix: cPanel → Software → MultiPHP Extensions → enable them\n";
}
echo "\n";

// ── 3. DIRECTORY STRUCTURE ─────────────────────────────────────
echo "═══ 3. DIRECTORY STRUCTURE CHECK ═══\n";
$requiredDirs = [
    $projectDir,
    $projectDir . '/storage',
    $projectDir . '/storage/app',
    $projectDir . '/storage/app/public',
    $projectDir . '/storage/framework',
    $projectDir . '/storage/framework/cache',
    $projectDir . '/storage/framework/cache/data',
    $projectDir . '/storage/framework/sessions',
    $projectDir . '/storage/framework/testing',
    $projectDir . '/storage/framework/views',
    $projectDir . '/storage/logs',
    $projectDir . '/bootstrap/cache',
];

$missingDirs = [];
foreach ($requiredDirs as $dir) {
    $shortDir = str_replace($baseDir, '~', $dir);
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? 'writable' : '⚠️ NOT WRITABLE';
        echo "  ✅ {$shortDir} [{$perms}] ({$writable})\n";
    } else {
        echo "  ❌ {$shortDir} — MISSING!\n";
        $missingDirs[] = $dir;
    }
}
echo "\n";

// ── 4. CRITICAL FILES ──────────────────────────────────────────
echo "═══ 4. CRITICAL FILES CHECK ═══\n";
$requiredFiles = [
    $projectDir . '/.env' => '.env',
    $projectDir . '/vendor/autoload.php' => 'vendor/autoload.php',
    $projectDir . '/artisan' => 'artisan',
    $projectDir . '/composer.json' => 'composer.json',
    $projectDir . '/bootstrap/app.php' => 'bootstrap/app.php',
    $publicDir . '/.htaccess' => 'public/.htaccess',
    $publicDir . '/index.php' => 'public/index.php',
];
foreach ($requiredFiles as $path => $label) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "  ✅ {$label} ({$size} bytes)\n";
    } else {
        echo "  ❌ {$label} — MISSING!\n";
    }
}
echo "\n";

// ── 5. .ENV VALIDATION ─────────────────────────────────────────
echo "═══ 5. .ENV VALIDATION (safe keys only) ═══\n";
$envFile = $projectDir . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $safeKeys = [
        'APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_TIMEZONE',
        'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE',
        'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION',
        'LOG_CHANNEL', 'LOG_STACK', 'LOG_LEVEL',
    ];
    foreach ($safeKeys as $key) {
        if (preg_match("/^{$key}=(.*)$/m", $envContent, $m)) {
            echo "  {$key} = {$m[1]}\n";
        } else {
            echo "  {$key} = (NOT SET)\n";
        }
    }
    // Check DB_USERNAME and DB_PASSWORD exist (without showing values)
    echo "  DB_USERNAME = " . (preg_match("/^DB_USERNAME=(.+)$/m", $envContent) ? '(set)' : '⚠️ EMPTY OR MISSING') . "\n";
    echo "  DB_PASSWORD = " . (preg_match("/^DB_PASSWORD=(.+)$/m", $envContent) ? '(set)' : '⚠️ EMPTY OR MISSING') . "\n";
} else {
    echo "  ❌ .env file not found!\n";
}
echo "\n";

// ── 6. DATABASE CONNECTION TEST ────────────────────────────────
echo "═══ 6. DATABASE CONNECTION TEST ═══\n";
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $dbVars = [];
    foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
        preg_match("/^{$key}=(.*)$/m", $envContent, $m);
        $dbVars[$key] = $m[1] ?? '';
    }

    $host = $dbVars['DB_HOST'] ?: '127.0.0.1';
    $port = $dbVars['DB_PORT'] ?: '3306';
    $db   = $dbVars['DB_DATABASE'];
    $user = $dbVars['DB_USERNAME'];
    $pass = $dbVars['DB_PASSWORD'];

    if (empty($db) || empty($user)) {
        echo "  ⚠️ DB_DATABASE or DB_USERNAME is empty in .env — cannot test connection.\n";
    } else {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$db}";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            echo "  ✅ Connection successful!\n";
            echo "  MySQL Version : {$ver}\n";
            echo "  Database      : {$db}\n";
            echo "  Tables found  : " . count($tables) . "\n";
            if (count($tables) === 0) {
                echo "  ⚠️ Database exists but has NO TABLES — migrations may not have run.\n";
            }
            $pdo = null;
        } catch (PDOException $e) {
            echo "  ❌ Connection FAILED!\n";
            echo "  Error: " . $e->getMessage() . "\n";
            echo "  DSN: mysql:host={$host};port={$port};dbname={$db}\n";
            echo "  User: {$user}\n";
        }
    }
} else {
    echo "  ⚠️ Cannot test — .env file missing.\n";
}
echo "\n";

// ── 7. DISK SPACE ──────────────────────────────────────────────
echo "═══ 7. DISK SPACE ═══\n";
$freeBytes = @disk_free_space($baseDir);
$totalBytes = @disk_total_space($baseDir);
if ($freeBytes !== false && $totalBytes !== false) {
    $usedBytes = $totalBytes - $freeBytes;
    $pctUsed = round(($usedBytes / $totalBytes) * 100, 1);
    echo "  Total : " . round($totalBytes / 1024 / 1024, 1) . " MB\n";
    echo "  Used  : " . round($usedBytes / 1024 / 1024, 1) . " MB ({$pctUsed}%)\n";
    echo "  Free  : " . round($freeBytes / 1024 / 1024, 1) . " MB\n";
    if ($pctUsed > 95) {
        echo "  ⚠️ DISK IS ALMOST FULL — this will cause 500 errors!\n";
    }
} else {
    echo "  ⚠️ Could not determine disk space (open_basedir restriction?)\n";
}
echo "\n";

// ── 8. TRY LARAVEL BOOTSTRAP ───────────────────────────────────
echo "═══ 8. LARAVEL BOOTSTRAP TEST ═══\n";
echo "  Attempting to boot Laravel...\n";
$bootstrap = $projectDir . '/bootstrap/app.php';
$autoload = $projectDir . '/vendor/autoload.php';

if (file_exists($autoload) && file_exists($bootstrap)) {
    try {
        // Change to project directory
        chdir($projectDir);

        require $autoload;
        $app = require $bootstrap;
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        echo "  ✅ Laravel bootstrapped successfully!\n";
        echo "  App Name: " . config('app.name', '(unknown)') . "\n";
        echo "  App Env : " . config('app.env', '(unknown)') . "\n";

    } catch (\Throwable $e) {
        echo "  ❌ Laravel FAILED to boot!\n";
        echo "  Exception : " . get_class($e) . "\n";
        echo "  Message   : " . $e->getMessage() . "\n";
        echo "  File      : " . str_replace($baseDir, '~', $e->getFile()) . "\n";
        echo "  Line      : " . $e->getLine() . "\n";
        echo "\n  Stack trace (last 5 frames):\n";
        $trace = $e->getTrace();
        foreach (array_slice($trace, 0, 5) as $i => $frame) {
            $file = isset($frame['file']) ? str_replace($baseDir, '~', $frame['file']) : '(internal)';
            $line = $frame['line'] ?? '?';
            $func = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '?');
            echo "    #{$i} {$file}:{$line} → {$func}()\n";
        }
    }
} else {
    echo "  ❌ Cannot boot — missing autoload.php or bootstrap/app.php\n";
}

echo "\n";
echo "══════════════════════════════════════════════════════\n";
echo "⚠️  DELETE THIS FILE NOW: rm rb-diagnose.php\n";
echo "══════════════════════════════════════════════════════\n";
