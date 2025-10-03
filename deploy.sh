#!/bin/bash
set -e

APP_DIR="/home/rjpl43t4e04n/repositories/ngo"
BRANCH="main"
PHP_BIN="/opt/cpanel/ea-php82/root/usr/bin/php" 
NVM_DIR="$HOME/.nvm"

echo "🚀 Starting deployment..."

cd "$APP_DIR"

echo "📦 Pulling latest code from $BRANCH..."
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

echo "📂 Installing dependencies..."
$PHP_BIN composer install --no-dev --optimize-autoloader

echo "⚙️ Running migrations..."
$PHP_BIN artisan migrate --force

echo "🧹 Clearing caches..."
$PHP_BIN artisan cache:clear
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear

echo "🔧 Rebuilding config cache..."
$PHP_BIN artisan config:cache

echo "💡 Restarting queues and daemons via PM2..."
. "$NVM_DIR/nvm.sh"
pm2 restart horizon || pm2 start "php artisan horizon" --name horizon
pm2 restart nightwatch || pm2 start "php artisan nightwatch:agent" --name nightwatch
pm2 save

echo "✅ Deployment completed successfully!"
