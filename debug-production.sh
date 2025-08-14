#!/bin/bash

echo "🔍 BinDay Production Diagnostics"
echo "================================"

# Check if .env file exists and show its content
echo "📁 Checking .env file..."
if [ -f "/var/www/binday/.env" ]; then
    echo "✅ .env file exists"
    echo "📋 .env file contents (masked passwords):"
    sed 's/PASSWORD=.*/PASSWORD=***MASKED***/g' /var/www/binday/.env
    echo ""
else
    echo "❌ .env file not found!"
    echo ""
fi

# Check Laravel configuration
echo "🔧 Checking Laravel configuration..."
cd /var/www/binday
php artisan config:clear
echo "✅ Config cache cleared"

# Test database connection
echo "🗄️ Testing database connection..."
php artisan db:check-config || echo "❌ Database check failed"

# Check for Laravel errors
echo "📋 Recent Laravel error logs..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 10 error lines:"
    tail -10 storage/logs/laravel.log
else
    echo "ℹ️ No Laravel log file found"
fi

# Check web server error logs
echo "🌐 Recent web server errors..."
if [ -f "/var/log/nginx/error.log" ]; then
    echo "Last 5 nginx error lines:"
    tail -5 /var/log/nginx/error.log
elif [ -f "/var/log/apache2/error.log" ]; then
    echo "Last 5 apache error lines:"
    tail -5 /var/log/apache2/error.log
else
    echo "ℹ️ No web server error logs found"
fi

# Check file permissions
echo "🔐 Checking file permissions..."
ls -la /var/www/binday/.env
ls -la /var/www/binday/storage/

# Test basic PHP
echo "🐘 Testing PHP..."
php -v | head -1

echo ""
echo "🎯 Quick Fixes to Try:"
echo "1. If .env is empty: Configure GitHub Secrets in repository"
echo "2. If database fails: Check database credentials and server"
echo "3. If permissions wrong: Run 'chmod 644 .env' and 'chmod -R 775 storage/'"
echo "4. If still failing: Run 'php artisan migrate --force' and 'php artisan production:seed --force'"
