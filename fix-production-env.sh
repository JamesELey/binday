#!/bin/bash

echo "🚨 Emergency Production .env Fix"
echo "================================"

# Navigate to the application directory
cd /var/www/binday

# Backup the current .env file
echo "📋 Backing up current .env file..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Create a corrected .env file
echo "🔧 Creating corrected .env file..."
cat > .env << 'EOF'
APP_NAME="BinDay Collection Management"
APP_ENV=production
APP_KEY="base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw="
APP_DEBUG=false
APP_URL="http://217.154.48.34"

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_DATABASE="binday"
DB_USERNAME="binday_user"
DB_PASSWORD="your_secure_password"

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=""
MAIL_PORT=""
MAIL_USERNAME=""
MAIL_PASSWORD=""
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=""
MAIL_FROM_NAME="BinDay Collection Management"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
EOF

# Set proper permissions
echo "🔐 Setting file permissions..."
chmod 644 .env
chown deploy:www-data .env

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Test the configuration
echo "🔍 Testing .env file syntax..."
php -r "
try {
    \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    \$dotenv->load();
    echo '✅ .env file syntax is valid\n';
} catch (Exception \$e) {
    echo '❌ .env file has syntax errors: ' . \$e->getMessage() . \"\n\";
    exit(1);
}
"

# Test database connection
echo "🗄️ Testing database connection..."
php artisan db:check-config

# Run migrations if needed
echo "📋 Running migrations..."
php artisan migrate --force

# Seed demo data if no users exist
echo "🌱 Checking for demo users..."
USER_COUNT=$(php artisan tinker --execute="echo App\\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 Seeding demo users and areas..."
    php artisan db:seed --class=UserSeeder --force
    php artisan db:seed --class=AreaSeeder --force
    echo "✅ Demo data seeded"
else
    echo "ℹ️ Users already exist ($USER_COUNT users)"
fi

# Cache configuration for performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "🎯 Fix completed! Test your site:"
echo "   http://217.154.48.34"
echo "   Login: admin@binday.com / password123"
echo ""
echo "⚠️ Remember to:"
echo "1. Set up GitHub Secrets for future deployments"
echo "2. Update DB_PASSWORD in .env with your actual password"
