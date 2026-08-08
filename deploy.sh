#!/bin/bash
# ==============================================================================
# Sroor Coffee ERP - Automated Deployment Script for Hostinger
# ==============================================================================

set -e

echo "🚀 Starting Deployment Process on Hostinger Server..."

# Ensure correct PHP version (use php8.2 or php8.3 if available on Hostinger CLI)
PHP_BIN=$(which php || echo "/usr/bin/php")
COMPOSER_BIN=$(which composer || echo "/usr/local/bin/composer")

echo "📦 Installing / Updating Composer Dependencies..."
$COMPOSER_BIN install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "🔑 Ensuring APP_KEY is generated if missing..."
if ! grep -q "APP_KEY=base64" .env 2>/dev/null; then
    $PHP_BIN artisan key:generate --force
fi

echo "🗄️ Running Database Migrations & Seeders..."
$PHP_BIN artisan migrate --force --seed

echo "⚡ Optimizing Laravel Caches (Config, Routes, Views)..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "🔗 Ensuring Storage Symlink..."
$PHP_BIN artisan storage:link || true

echo "🔒 Setting Permissions on Storage & Cache..."
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment Completed Successfully! System is Live and Ready."
