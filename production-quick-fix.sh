#!/bin/bash

# 🚨 Production Quick Fix Script
# Run this on your VPS to immediately fix login issues

echo "🔧 BinDay Production Quick Fix"
echo "=============================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this from your Laravel application root directory."
    echo "   Example: cd /path/to/your/binday/app && bash production-quick-fix.sh"
    exit 1
fi

echo "📍 Current directory: $(pwd)"
echo "🐘 PHP Version: $(php -v | head -n 1)"
echo ""

# Check database connection
echo "🔍 Testing database connection..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" 2>/dev/null; then
    echo "✅ Database connection working"
else
    echo "❌ Database connection failed. Please check your .env file and database configuration."
    exit 1
fi

# Check if users exist
echo ""
echo "👥 Checking existing users..."
USER_COUNT=$(php artisan tinker --execute="echo App\\User::count();" 2>/dev/null)
echo "   Current user count: $USER_COUNT"

if [ "$USER_COUNT" -eq "0" ]; then
    echo "🌱 No users found. Creating demo users..."
    
    # Run the production seeder
    if php artisan production:seed --force; then
        echo "✅ Demo users created successfully!"
        echo ""
        echo "🔑 Login Credentials:"
        echo "   👑 Admin: admin@binday.com / password123"
        echo "   👷 Worker: worker@binday.com / password123"
        echo "   👤 Customer: customer@binday.com / password123"
    else
        echo "❌ Failed to create users automatically. Trying manual creation..."
        
        # Manual user creation as fallback
        if php artisan admin:create-user --email="admin@binday.com" --password="password123" --name="Admin"; then
            echo "✅ Admin user created manually!"
            echo "🔑 Login: admin@binday.com / password123"
        else
            echo "❌ Failed to create admin user. Please check the logs."
            exit 1
        fi
    fi
else
    echo "ℹ️  Users already exist ($USER_COUNT users). Login should work."
    echo ""
    echo "🔑 Try these default credentials:"
    echo "   👑 Admin: admin@binday.com / password123"
    echo "   👷 Worker: worker@binday.com / password123"
    echo "   👤 Customer: customer@binday.com / password123"
fi

# Check areas
echo ""
echo "🏘️  Checking service areas..."
AREA_COUNT=$(php artisan tinker --execute="echo App\\Area::count();" 2>/dev/null)
echo "   Current area count: $AREA_COUNT"

if [ "$AREA_COUNT" -eq "0" ]; then
    echo "🌱 No areas found. Creating demo areas..."
    php artisan db:seed --class=AreaSeeder
    echo "✅ Demo areas created!"
fi

# Clear caches
echo ""
echo "🧹 Clearing application caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "🎉 Quick fix completed!"
echo ""
echo "🌐 Your site should now be accessible at: http://$(hostname -I | awk '{print $1}')"
echo "🔑 Login with: admin@binday.com / password123"
echo ""
echo "⚠️  Remember to change default passwords after first login!"
echo ""
echo "🔍 To verify the fix worked, try:"
echo "   curl -I http://localhost/ | grep HTTP"
echo "   (Should show 302 redirect to login page, which is correct)"
