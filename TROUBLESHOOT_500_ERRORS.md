# 🚨 Troubleshooting HTTP 500 Errors

## 🔍 Current Issue
Your deployment is returning HTTP 500 errors instead of working properly. This typically indicates server-side configuration issues.

## 🎯 Most Likely Causes

### 1. **Missing GitHub Secrets** ⚠️
If GitHub Secrets aren't configured, your `.env` file will have empty values:

```env
APP_NAME=
APP_KEY=
DB_HOST=
DB_PASSWORD=
```

**Fix:** Configure GitHub Secrets in your repository.

### 2. **Invalid Database Credentials** 🗄️
The `.env` file might have wrong database settings.

**Fix:** Verify your database credentials match your server setup.

### 3. **Missing Database Tables** 📋
Tables might not exist if migrations haven't run.

**Fix:** Run migrations manually.

## 🔧 Step-by-Step Diagnosis

### **Step 1: Check if GitHub Secrets are Configured**

Go to your GitHub repository:
1. Settings → Secrets and variables → Actions
2. Verify these secrets exist:
   - `APP_NAME`
   - `APP_KEY` 
   - `APP_URL`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE` 
   - `DB_USERNAME`
   - `DB_PASSWORD`

### **Step 2: Run Diagnostics on Server**

Upload and run the diagnostic script:

```bash
# Upload the debug script
scp debug-production.sh user@yourserver:/tmp/

# Run diagnostics
ssh user@yourserver
chmod +x /tmp/debug-production.sh
sudo /tmp/debug-production.sh
```

### **Step 3: Check .env File on Server**

```bash
# Check if .env exists and has content
cat /var/www/binday/.env

# Look for empty values (indicates missing secrets)
grep "=" /var/www/binday/.env | grep "^[^=]*=$"
```

### **Step 4: Test Database Connection**

```bash
cd /var/www/binday
php artisan db:check-config
```

### **Step 5: Check Error Logs**

```bash
# Laravel errors
tail -20 /var/www/binday/storage/logs/laravel.log

# Web server errors  
tail -10 /var/log/nginx/error.log
# OR
tail -10 /var/log/apache2/error.log
```

## 🚀 Quick Fixes

### **If .env file is empty or has missing values:**

1. **Add missing GitHub Secrets** in your repository
2. **Redeploy** to regenerate .env file

### **If database connection fails:**

```bash
# Test database manually
mysql -h 127.0.0.1 -u binday_user -p binday

# If fails, recreate database user
mysql -u root -p
DROP USER 'binday_user'@'localhost';
CREATE USER 'binday_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON binday.* TO 'binday_user'@'localhost';
FLUSH PRIVILEGES;
```

### **If missing database tables:**

```bash
cd /var/www/binday
php artisan migrate --force
php artisan production:seed --force
```

### **If file permissions issues:**

```bash
cd /var/www/binday
chmod 644 .env
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

## 📋 Required GitHub Secrets Values

Set these in GitHub → Settings → Secrets and variables → Actions:

```
APP_NAME = BinDay Collection Management
APP_KEY = base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=
APP_URL = http://217.154.48.34

DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_DATABASE = binday
DB_USERNAME = binday_user
DB_PASSWORD = [your actual database password]

MAIL_HOST = smtp.fasthosts.co.uk
MAIL_PORT = 587
MAIL_USERNAME = [your email username]
MAIL_PASSWORD = [your email password]
MAIL_FROM_ADDRESS = noreply@thebinday.co.uk
```

## 🎯 Expected Results

After fixing the issues:
- ✅ HTTP 200 responses instead of 500
- ✅ Login page accessible 
- ✅ Database connection working
- ✅ Health checks passing

## 🆘 Emergency Manual Fix

If all else fails, manually create a working `.env` file:

```bash
# Create .env manually
cat > /var/www/binday/.env << 'EOF'
APP_NAME=BinDay Collection Management
APP_ENV=production
APP_KEY=base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=
APP_DEBUG=false
APP_URL="http://217.154.48.34"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=binday
DB_USERNAME=binday_user
DB_PASSWORD=your_actual_password

LOG_CHANNEL=stack
SESSION_DRIVER=database
EOF

# Set permissions
chmod 644 /var/www/binday/.env

# Clear cache and test
cd /var/www/binday
php artisan config:clear
php artisan migrate --force
php artisan production:seed --force
```

**Run the diagnostic script first to identify the exact issue!**
