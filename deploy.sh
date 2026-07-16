#!/bin/bash
set -e

VPS_HOST="208.110.72.153"
VPS_PORT="10060"
VPS_USER="administrator"
TARGET_DIR="/var/www/curasys"

echo "====================================="
echo "  Deploying CuraSys to VPS           "
echo "====================================="

echo "1. Building frontend assets..."
npm install
npm run build

echo "2. Syncing files to VPS..."
# We use rsync to efficiently copy files, excluding unnecessary directories
rsync -avz --delete -e "ssh -p $VPS_PORT" \
  --exclude node_modules \
  --exclude vendor \
  --exclude .git \
  --exclude .env \
  --exclude public/hot \
  --exclude tests \
  --exclude storage/logs/* \
  --exclude storage/framework/cache/* \
  --exclude storage/framework/views/* \
  --exclude storage/framework/sessions/* \
  --exclude bootstrap/cache/*.php \
  ./ $VPS_USER@$VPS_HOST:$TARGET_DIR

echo "3. Executing deployment commands on VPS..."
ssh -p $VPS_PORT $VPS_USER@$VPS_HOST << EOF
  # Clean up any other projects to keep only curasys
  cd /var/www
  for d in */; do
    if [ "\$d" != "curasys/" ] && [ "\$d" != "html/" ]; then
      echo "Nexcart@26" | sudo -S rm -rf "\$d"
    fi
  done

  cd $TARGET_DIR
  
  # Remove synced vendor to prevent PHP 8.4 local conflicts
  rm -rf vendor
  
  # Delete old cache files and hot-reload files that were incorrectly synced before
  rm -f bootstrap/cache/*.php
  rm -f public/hot
  
  # Install PHP dependencies
  composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist --optimize-autoloader

  # Run Laravel commands
  php artisan optimize:clear
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan migrate --force

  # Ensure correct permissions
  echo "Nexcart@26" | sudo -S chown -R administrator:www-data /var/www/curasys
  echo "Nexcart@26" | sudo -S chmod -R 775 /var/www/curasys/storage /var/www/curasys/bootstrap/cache
EOF

echo "====================================="
echo "  Deployment Complete! 🎉           "
echo "====================================="
