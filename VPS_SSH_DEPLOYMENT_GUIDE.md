git status# 🚀 VPS SSH Deployment Guide

## 🎯 Overview

This guide covers the complete setup for deploying your Laravel application to a VPS using SSH and GitHub Actions. This method is much more reliable and faster than FTP deployment.

## 🏗️ Prerequisites

- A VPS server (Ubuntu/Debian recommended)
- SSH access to your VPS
- PHP 8.1+ installed on the VPS
- Composer installed on the VPS
- Web server (Nginx/Apache) configured
- MySQL/PostgreSQL database (if needed)

## 🔧 VPS Server Setup

### 1. Connect to Your VPS

```bash
ssh your-username@your-vps-ip
```

### 2. Install Required Software

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1 and extensions
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-mbstring php8.1-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Nginx (or Apache)
sudo apt install -y nginx

# Install MySQL (if needed)
sudo apt install -y mysql-server
```

### 3. Create Deployment User (Recommended)

```bash
# Create deployment user
sudo adduser deploy
sudo usermod -aG www-data deploy

# Create deployment directories
sudo mkdir -p /var/www/binday
sudo mkdir -p /var/backups/binday
sudo chown -R deploy:www-data /var/www/binday
sudo chown -R deploy:www-data /var/backups/binday
```

### 4. Configure SSH Key Authentication

On your local machine:

```bash
# Generate SSH key pair (if you don't have one)
ssh-keygen -t rsa -b 4096 -C "deployment@binday"

# Display the public key
cat ~/.ssh/id_rsa.pub
```

On your VPS:

```bash
# Switch to deployment user
sudo su - deploy

# Create SSH directory
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Add your public key
nano ~/.ssh/authorized_keys
# Paste your public key here

# Set proper permissions
chmod 600 ~/.ssh/authorized_keys
```

Test SSH access:

```bash
# From your local machine
ssh deploy@your-vps-ip
```

## 🔑 GitHub Repository Secrets Setup

Navigate to your GitHub repository → Settings → Secrets and variables → Actions

Add the following secrets:

### Required Secrets:

```
SSH_PRIVATE_KEY     = Your private SSH key content (entire file content of ~/.ssh/id_rsa)
VPS_HOST           = Your VPS IP address or domain
VPS_USER           = deploy (or your deployment username)
DEPLOY_PATH        = /var/www/binday
BACKUP_PATH        = /var/backups/binday

# Laravel Configuration
APP_KEY            = base64:your-app-key-here
DB_CONNECTION      = mysql
DB_HOST            = localhost
DB_PORT            = 3306
DB_DATABASE        = binday_production
DB_USERNAME        = your-db-username
DB_PASSWORD        = your-db-password
```

### Optional Secrets:

```
# If using different database
DB_CONNECTION      = pgsql (for PostgreSQL)
DB_PORT            = 5432 (for PostgreSQL)

# If using external services
MAIL_HOST          = your-mail-host
MAIL_USERNAME      = your-mail-username
MAIL_PASSWORD      = your-mail-password
```

## 🌐 Web Server Configuration

### Nginx Configuration

Create site configuration:

```bash
sudo nano /etc/nginx/sites-available/binday
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/binday/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/binday /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache Configuration (Alternative)

Create site configuration:

```bash
sudo nano /etc/apache2/sites-available/binday.conf
```

Add the following:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/binday/public

    <Directory /var/www/binday/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/binday_error.log
    CustomLog ${APACHE_LOG_DIR}/binday_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite binday.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

## 🗄️ Database Setup

### MySQL Setup

```bash
sudo mysql

-- Create database and user
CREATE DATABASE binday_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'binday_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON binday_production.* TO 'binday_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 🚀 First Deployment

### 1. Update Your Code

Make sure your `production.env.example` file has the correct configuration:

```env
APP_NAME=BinDay
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# Add other production settings as needed
```

### 2. Trigger Deployment

**Option A: Automatic (on push to main)**
```bash
git add .
git commit -m "Setup SSH deployment"
git push origin main
```

**Option B: Manual**
1. Go to your GitHub repository
2. Click "Actions" tab
3. Select "Deploy to VPS via SSH"
4. Click "Run workflow"

### 3. Monitor Deployment

Watch the GitHub Actions logs for real-time deployment progress. The deployment will:

1. ✅ Prepare the application files
2. ✅ Create a backup of existing deployment
3. ✅ Upload files to your VPS
4. ✅ Install Composer dependencies on the server
5. ✅ Optimize Laravel for production
6. ✅ Run database migrations
7. ✅ Set proper file permissions

## 🔧 Post-Deployment Steps

### 1. Set Up SSL (Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 2. Set Up Laravel Scheduler (if using scheduled tasks)

```bash
# Edit crontab for deployment user
sudo crontab -u deploy -e

# Add Laravel scheduler
* * * * * cd /var/www/binday && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Configure Log Rotation

```bash
sudo nano /etc/logrotate.d/binday
```

Add:

```
/var/www/binday/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 deploy www-data
}
```

## 🔍 Verification & Testing

After deployment, verify:

1. **Website Access**: Visit your domain
2. **PHP Info**: Create temporary `info.php` with `<?php phpinfo(); ?>`
3. **Database**: Check database connection works
4. **Logs**: Monitor `/var/www/binday/storage/logs/laravel.log`
5. **Permissions**: Ensure storage and cache directories are writable

## 🚨 Troubleshooting

### Common Issues

**1. Permission Errors**
```bash
sudo chown -R deploy:www-data /var/www/binday
sudo chmod -R 775 /var/www/binday/storage
sudo chmod -R 775 /var/www/binday/bootstrap/cache
```

**2. Composer Memory Issues**
```bash
# Increase PHP memory limit
sudo nano /etc/php/8.1/cli/php.ini
# Set: memory_limit = 512M
```

**3. Database Connection Issues**
- Check database credentials in GitHub secrets
- Verify database user has proper permissions
- Test connection manually: `php artisan tinker` then `DB::connection()->getPdo()`

**4. SSH Authentication Issues**
- Verify SSH key is correct in GitHub secrets
- Check SSH key permissions on VPS
- Test SSH connection manually

### Deployment Rollback

If needed, rollback to previous version:

```bash
ssh deploy@your-vps-ip

# List available backups
ls -la /var/backups/binday/

# Restore from backup
sudo rm -rf /var/www/binday
sudo cp -r /var/backups/binday/backup-YYYYMMDD-HHMMSS /var/www/binday
sudo chown -R deploy:www-data /var/www/binday
```

## 📊 Deployment Comparison

| Method | Speed | Reliability | Setup | Vendor Install |
|--------|-------|-------------|--------|----------------|
| **SSH Deploy** | ⚡ 2-5 min | ✅ Excellent | 🔧 Medium | ✅ On Server |
| **FTP Deploy** | 🐌 80+ min | ❌ Poor | ✅ Low | ❌ Manual |

## 🎉 Benefits of SSH Deployment

- ✅ **Fast**: 2-5 minute deployments
- ✅ **Reliable**: No timeout issues
- ✅ **Automated**: One-click deployments
- ✅ **Secure**: SSH key authentication
- ✅ **Professional**: Industry standard CI/CD
- ✅ **Backups**: Automatic backup before deploy
- ✅ **Optimized**: Server-side vendor installation
- ✅ **Monitoring**: Real-time deployment logs

---

Your Laravel application is now ready for modern, reliable SSH-based deployments! 🚀
