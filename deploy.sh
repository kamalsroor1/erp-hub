#!/usr/bin/env bash
set -e

PHP84="/opt/alt/php84/usr/bin/php"
TARGET_DIR="/home/u910151740/domains/sroor.baraa-solutions.com/public_html"

echo "🚀 Connecting to Hostinger Server..."
mkdir -p "$TARGET_DIR"
cd "$TARGET_DIR"

if [ -d ".git" ]; then
    echo "📥 Pulling latest updates from GitHub..."
    git fetch --all
    git reset --hard origin/main
else
    echo "📥 Cloning repository for the first time..."
    git clone https://github.com/kamalsroor1/sroor-cofe-erp.git .
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
chmod -R 775 storage bootstrap/cache

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

echo "📦 Running Composer Install with PHP 8.4..."
$PHP84 $(which composer) install --no-dev --prefer-dist --optimize-autoloader --no-interaction || true

echo "🗄️ Running Migrations & Seeders..."
$PHP84 artisan key:generate --force || true
$PHP84 artisan migrate --force --seed || true

echo "⚡ Caching Config, Routes, and Views..."
$PHP84 artisan config:cache || true
$PHP84 artisan route:cache || true
$PHP84 artisan view:cache || true
$PHP84 artisan storage:link || true

cat << 'EOF' > index.php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
EOF

cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L,QSA]
</IfModule>
EOF

chmod -R 775 storage bootstrap/cache
chmod 644 index.php .htaccess .env

echo "🎉 Deployment to Hostinger Finished Successfully!"
