# 🗄️ DATABASE CONFIGURATION FIX

## Error Diagnosis
`Database connection [] not configured` means your production server is missing or has an incorrect `.env` file with database settings.

## 🔧 IMMEDIATE FIX

### Step 1: SSH to Your Production Server
```bash
ssh your_username@your_server_ip
cd /path/to/your/binday/application
```

### Step 2: Check if .env File Exists
```bash
ls -la .env
```

If it doesn't exist or is empty, continue to Step 3.

### Step 3: Create/Update .env File
```bash
# Copy the production example
cp production.env.example .env

# Edit with your database details
nano .env
```

### Step 4: Configure Database Settings
Update these lines in your `.env` file with your actual database credentials:

```env
# Database Configuration (REQUIRED)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=binday
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

# Application Settings
APP_NAME="BinDay Collection Management"
APP_ENV=production
APP_KEY=base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=
APP_DEBUG=false
APP_URL=https://your-domain.com

# Session Configuration (for MariaDB)
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Step 5: Test Database Connection
```bash
# Test the database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';"
```

### Step 6: Run Migrations and Seeding
```bash
# Run migrations to create tables
php artisan migrate --force

# Seed demo users
php artisan production:seed --force

# Clear and cache config
php artisan config:clear
php artisan config:cache
```

## 🗄️ Database Setup (If Database Doesn't Exist)

### For MariaDB/MySQL:
```sql
-- Connect to MariaDB as root
mysql -u root -p

-- Create database
CREATE DATABASE binday CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace with your preferred credentials)
CREATE USER 'binday_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant permissions
GRANT ALL PRIVILEGES ON binday.* TO 'binday_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

Then update your `.env` file:
```env
DB_DATABASE=binday
DB_USERNAME=binday_user
DB_PASSWORD=secure_password_here
```

## 🔧 Alternative Quick Fix Script

Create and run this script on your server:

```bash
#!/bin/bash
# save as fix-database-config.sh

echo "🗄️ Fixing Database Configuration..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📁 Creating .env from production template..."
    cp production.env.example .env
    echo "✅ .env file created"
else
    echo "📁 .env file already exists"
fi

# Prompt for database details
echo ""
echo "🔧 Database Configuration Required:"
read -p "Database name (default: binday): " DB_NAME
read -p "Database username: " DB_USER
read -s -p "Database password: " DB_PASS
echo ""
read -p "Database host (default: 127.0.0.1): " DB_HOST

# Set defaults
DB_NAME=${DB_NAME:-binday}
DB_HOST=${DB_HOST:-127.0.0.1}

# Update .env file
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env  
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env

echo "✅ Database configuration updated"

# Test connection
echo "🔍 Testing database connection..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Success';" 2>/dev/null; then
    echo "✅ Database connection successful!"
    
    # Run migrations and seeding
    echo "🗄️ Running migrations..."
    php artisan migrate --force
    
    echo "🌱 Seeding demo data..."
    php artisan production:seed --force
    
    echo "🧹 Clearing cache..."
    php artisan config:clear
    php artisan config:cache
    
    echo ""
    echo "🎉 Database setup complete!"
    echo "🔑 Login: admin@binday.com / password123"
else
    echo "❌ Database connection failed. Please check your credentials."
fi
```

## 🎯 Expected Results After Fix
- ✅ Database connection working
- ✅ All migrations run successfully  
- ✅ Demo users created
- ✅ Login page accessible
- ✅ Admin dashboard working

## 🔍 Troubleshooting

### If Database Connection Still Fails:
```bash
# Check MariaDB service is running
systemctl status mariadb

# Check if database exists
mysql -u root -p -e "SHOW DATABASES;"

# Check user permissions
mysql -u root -p -e "SELECT User, Host FROM mysql.user;"
```

### If Migrations Fail:
```bash
# Check migration status
php artisan migrate:status

# Reset and migrate fresh (⚠️ DESTROYS DATA)
php artisan migrate:fresh --force
```

The key is ensuring your `.env` file has the correct database credentials that match your MariaDB setup!
