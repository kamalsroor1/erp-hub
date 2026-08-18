<?php

// Secure Deployment Webhook for Sroor ERP
$secretToken = 'sroor_secure_deploy_token_2026_ks';

if (($_GET['token'] ?? '') !== $secretToken && ($_POST['token'] ?? '') !== $secretToken) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Forbidden: Invalid token.']));
}

header('Content-Type: application/json; charset=utf-8');

// Find available PHP CLI binary
$phpBin = 'php';
if (file_exists('/opt/alt/php84/usr/bin/php')) {
    $phpBin = '/opt/alt/php84/usr/bin/php';
} elseif (file_exists('/opt/alt/php83/usr/bin/php')) {
    $phpBin = '/opt/alt/php83/usr/bin/php';
} elseif (file_exists('/usr/bin/php8.4')) {
    $phpBin = '/usr/bin/php8.4';
} elseif (file_exists('/usr/bin/php8.3')) {
    $phpBin = '/usr/bin/php8.3';
}

$output = [];
$projectRoot = __DIR__;
if (file_exists($projectRoot . '/artisan')) {
    chdir($projectRoot);
}

// 1. Pull latest git code
exec('git fetch --all 2>&1', $output);
exec('git reset --hard origin/main 2>&1', $output);

// 2. Clear & Optimize caches & migrate
exec("{$phpBin} artisan migrate --force 2>&1", $output);
exec("{$phpBin} artisan optimize:clear 2>&1", $output);

echo json_encode([
    'status' => 'success',
    'message' => 'Sroor ERP deployed successfully via secure webhook!',
    'php_bin' => $phpBin,
    'timestamp' => date('Y-m-d H:i:s'),
    'output' => $output
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
