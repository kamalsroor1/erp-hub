<?php

// Secure Deployment Webhook for Sroor ERP
$secretToken = 'sroor_secure_deploy_token_2026_ks';

if (($_GET['token'] ?? '') !== $secretToken && ($_POST['token'] ?? '') !== $secretToken) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Forbidden: Invalid token.']));
}

header('Content-Type: application/json; charset=utf-8');

$phpBin = '/opt/alt/php84/usr/bin/php';
$output = [];

// 1. Pull latest git code
exec('git fetch --all 2>&1', $output);
exec('git reset --hard origin/main 2>&1', $output);

// 2. Clear & Optimize caches
exec("{$phpBin} artisan migrate --force 2>&1", $output);
exec("{$phpBin} artisan config:cache 2>&1", $output);
exec("{$phpBin} artisan route:cache 2>&1", $output);
exec("{$phpBin} artisan view:cache 2>&1", $output);

echo json_encode([
    'status' => 'success',
    'message' => 'Sroor ERP deployed successfully via secure webhook!',
    'timestamp' => date('Y-m-d H:i:s'),
    'output' => $output
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
