#!/bin/bash

echo "🔑 Database User Fix Script"
echo "=========================="

echo "🔍 Step 1: Testing current database setup..."

# Test if MariaDB/MySQL is running
if systemctl is-active --quiet mariadb; then
    echo "✅ MariaDB service is running"
elif systemctl is-active --quiet mysql; then
    echo "✅ MySQL service is running"
else
    echo "❌ Database service not running. Starting MariaDB..."
    systemctl start mariadb
fi

# Show current databases
echo ""
echo "📋 Current databases:"
mysql -u root -p -e "SHOW DATABASES;" 2>/dev/null || echo "❌ Could not connect as root"

echo ""
echo "🔧 Step 2: Recreating binday_user..."
echo "Enter your MySQL root password when prompted:"

# Recreate the user and database
mysql -u root -p << 'EOSQL'
-- Remove existing user if exists
DROP USER IF EXISTS 'binday_user'@'localhost';

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS binday;

-- Create new user with password
CREATE USER 'binday_user'@'localhost' IDENTIFIED BY 'binday123';

-- Grant all privileges on the binday database
GRANT ALL PRIVILEGES ON binday.* TO 'binday_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Show the user was created
SELECT User, Host FROM mysql.user WHERE User = 'binday_user';

-- Show databases accessible to the user
SHOW GRANTS FOR 'binday_user'@'localhost';
EOSQL

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database user created successfully!"
    echo ""
    echo "🔧 Step 3: Updating .env file..."
    
    # Update the .env file with the new password
    cd /var/www/binday
    
    # Update DB_PASSWORD
    sed -i 's/DB_PASSWORD=".*"/DB_PASSWORD="binday123"/' .env
    
    echo "✅ .env file updated with new password"
    
    echo ""
    echo "🔍 Step 4: Testing database connection..."
    
    # Test the connection
    php artisan db:check-config
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "🗃️ Step 5: Running migrations and seeding..."
        
        # Run migrations
        php artisan migrate --force
        
        # Seed demo data
        php artisan db:seed --class=UserSeeder --force
        php artisan db:seed --class=AreaSeeder --force
        
        echo ""
        echo "⚡ Step 6: Clearing caches..."
        php artisan config:clear
        php artisan cache:clear
        php artisan config:cache
        
        echo ""
        echo "🎯 SETUP COMPLETE!"
        echo "=================="
        echo "✅ Database user: binday_user"
        echo "✅ Database password: binday123"
        echo "✅ Database name: binday" 
        echo "✅ Tables created and seeded"
        echo ""
        echo "🌐 Test your site: http://217.154.48.34"
        echo "🔑 Login: admin@binday.com / password123"
        echo ""
        echo "📋 GitHub Secret to add:"
        echo "   DB_PASSWORD = binday123"
        
    else
        echo "❌ Database connection still failing. Check the error messages above."
    fi
    
else
    echo "❌ Failed to create database user. Check your root password."
fi
