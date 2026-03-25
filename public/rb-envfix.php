<?php
/**
 * RBeverything .ENV Fixer
 * Fixes encoding issues (BOM, CRLF, trailing whitespace) in the server's .env file.
 * Upload to public_html/ or public_html/web-sbox.rbeverything.com/
 * ⚠️ DELETE THIS FILE AFTER USE!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║     RBeverything .ENV Fixer v1.0                    ║\n";
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
$envFile = $projectDir . '/.env';

echo "Environment: " . ($isSandbox ? 'SANDBOX' : 'PRODUCTION') . "\n";
echo ".env path  : {$envFile}\n\n";

if (!file_exists($envFile)) {
    echo "❌ .env file not found!\n";
    exit;
}

// Read the raw file
$raw = file_get_contents($envFile);
$origSize = strlen($raw);

echo "═══ BEFORE FIX ═══\n";
echo "  File size    : {$origSize} bytes\n";

// Detect issues
$hasBOM = (substr($raw, 0, 3) === "\xEF\xBB\xBF");
$hasCRLF = (strpos($raw, "\r\n") !== false);
$hasCR = (strpos($raw, "\r") !== false);

echo "  BOM present  : " . ($hasBOM ? '⚠️ YES' : '✅ No') . "\n";
echo "  CRLF endings : " . ($hasCRLF ? '⚠️ YES (Windows)' : '✅ No') . "\n";

// Count lines with trailing whitespace
$lines = explode("\n", $raw);
$trailingWS = 0;
foreach ($lines as $line) {
    $cleaned = rtrim($line, "\r \t");
    if ($cleaned !== $line) $trailingWS++;
}
echo "  Lines with trailing whitespace: " . ($trailingWS > 0 ? "⚠️ {$trailingWS}" : '✅ 0') . "\n\n";

if (!$hasBOM && !$hasCRLF && !$hasCR && $trailingWS === 0) {
    echo "✅ .env file looks clean! No fixes needed.\n";
    echo "   If you're still having DB issues, the problem is the VALUES in .env, not encoding.\n";
    exit;
}

// ── Apply fixes ─────────────────────────────────────────────────
echo "═══ APPLYING FIXES ═══\n";

$fixed = $raw;

// 1. Remove BOM
if ($hasBOM) {
    $fixed = substr($fixed, 3);
    echo "  ✅ Removed BOM\n";
}

// 2. Convert CRLF → LF
if ($hasCRLF) {
    $fixed = str_replace("\r\n", "\n", $fixed);
    echo "  ✅ Converted CRLF → LF\n";
}

// 3. Remove any remaining standalone \r
if (strpos($fixed, "\r") !== false) {
    $fixed = str_replace("\r", "", $fixed);
    echo "  ✅ Removed standalone CR characters\n";
}

// 4. Trim trailing whitespace from each line
$fixedLines = explode("\n", $fixed);
$trimmedCount = 0;
foreach ($fixedLines as &$line) {
    $trimmed = rtrim($line, " \t");
    if ($trimmed !== $line) {
        $line = $trimmed;
        $trimmedCount++;
    }
}
unset($line);
if ($trimmedCount > 0) {
    $fixed = implode("\n", $fixedLines);
    echo "  ✅ Trimmed trailing whitespace from {$trimmedCount} lines\n";
}

// 5. Ensure file ends with a single newline
$fixed = rtrim($fixed, "\n") . "\n";

$newSize = strlen($fixed);
echo "\n═══ AFTER FIX ═══\n";
echo "  File size: {$newSize} bytes (was {$origSize})\n";

// Create backup
$backupFile = $envFile . '.bak.' . date('Ymd_His');
if (@copy($envFile, $backupFile)) {
    echo "  Backup: {$backupFile}\n";
} else {
    echo "  ⚠️ Could not create backup (continuing anyway)\n";
}

// Write fixed content
if (@file_put_contents($envFile, $fixed)) {
    echo "\n  ✅ .env file FIXED and saved!\n";
} else {
    echo "\n  ❌ FAILED to write fixed .env!\n";
    exit;
}

// ── Verify DB connection with fixed values ──────────────────────
echo "\n═══ DB CONNECTION TEST (with fixed .env) ═══\n";
$dbVars = [];
foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    preg_match("/^{$key}=(.*)$/m", $fixed, $m);
    $dbVars[$key] = isset($m[1]) ? trim($m[1]) : '';
}

$dbHost = $dbVars['DB_HOST'] ?: 'localhost';
$dbPort = $dbVars['DB_PORT'] ?: '3306';
$db     = $dbVars['DB_DATABASE'];
$user   = $dbVars['DB_USERNAME'];
$pass   = $dbVars['DB_PASSWORD'];

echo "  DB_HOST     : [{$dbHost}]\n";
echo "  DB_DATABASE : [{$db}]\n";
echo "  DB_USERNAME : [{$user}]\n\n";

if (!empty($db) && !empty($user)) {
    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$db}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        echo "  ✅ Database connection SUCCESSFUL!\n";
        echo "  MySQL Version: {$ver}\n";
        echo "  Tables found : " . count($tables) . "\n";
        $pdo = null;
    } catch (PDOException $e) {
        echo "  ❌ Still failing: " . $e->getMessage() . "\n";
        echo "  → The encoding was fixed, but the DB credentials may be wrong.\n";
        echo "  → Check DB_HOST, DB_USERNAME, DB_PASSWORD in cPanel File Manager.\n";
    }
}

echo "\n══════════════════════════════════════════════════════\n";
echo "⚠️  DELETE THIS FILE NOW!\n";
echo "══════════════════════════════════════════════════════\n";
