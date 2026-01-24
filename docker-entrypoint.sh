#!/bin/bash

# 1. Clear and Cache Configs (Speed Boost)
echo "🚀 Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Start Supervisor (which starts Apache + Queue Worker)
echo "🔥 Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf