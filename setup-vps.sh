#!/bin/bash

# 🚀 VPS Setup Script for Laravel Deployment
# This script automates the VPS setup for SSH-based deployment

set -e

echo "🚀 BinDay VPS Setup Script"
echo "=========================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper functions
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

echo "🔧 Starting VPS setup for Laravel deployment..."
echo ""

# Update system packages
echo "📦 Updating system packages..."
apt update && apt upgrade -y
print_status "System packages updated"

# Install PHP 8.1 and required extensions
echo "🐘 Installing PHP 8.1 and extensions..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
    php8.1 \
    php8.1-cli \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-xml \
    php8.1-curl \
    php8.1-zip \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-intl \
    php8.1-bcmath \
    php8.1-sqlite3 \
    unzip \
    git

print_status "PHP 8.1 and extensions installed"

# Install Composer
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
print_status "Composer installed"

# Install Nginx
echo "🌐 Installing Nginx..."
apt install -y nginx
systemctl enable nginx
systemctl start nginx
print_status "Nginx installed and started"

# Install MySQL (optional)
read -p "📊 Do you want to install MySQL? (y/n): " install_mysql
if [ "$install_mysql" = "y" ] || [ "$install_mysql" = "Y" ]; then
    echo "🗄️ Installing MySQL..."
    apt install -y mysql-server
    systemctl enable mysql
    systemctl start mysql
    print_status "MySQL installed and started"
    print_warning "Remember to run 'mysql_secure_installation' after this script"
fi

# Create deployment user
echo "👤 Creating deployment user..."
if id "deploy" &>/dev/null; then
    print_warning "User 'deploy' already exists"
else
    adduser --disabled-password --gecos "" deploy
    usermod -aG www-data deploy
    print_status "User 'deploy' created"
fi

# Create deployment directories
echo "📁 Creating deployment directories..."
mkdir -p /var/www/binday
mkdir -p /var/backups/binday
chown -R deploy:www-data /var/www/binday
chown -R deploy:www-data /var/backups/binday
chmod -R 755 /var/www/binday
chmod -R 755 /var/backups/binday
print_status "Deployment directories created"

# Set up SSH directory for deploy user
echo "🔑 Setting up SSH for deployment user..."
sudo -u deploy mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chown deploy:deploy /home/deploy/.ssh
print_status "SSH directory created for deploy user"

# Create Nginx site configuration
echo "⚙️ Creating Nginx site configuration..."
cat > /etc/nginx/sites-available/binday << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/binday/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html index.htm;

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
}
EOF

# Enable the site
ln -sf /etc/nginx/sites-available/binday /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
print_status "Nginx site configuration created and enabled"

# Configure PHP settings for production
echo "🔧 Optimizing PHP settings..."
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.1/cli/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.1/fpm/php.ini
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' /etc/php/8.1/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 64M/' /etc/php/8.1/fpm/php.ini
systemctl reload php8.1-fpm
print_status "PHP settings optimized"

# Set up log rotation for Laravel
echo "📜 Setting up log rotation..."
cat > /etc/logrotate.d/binday << 'EOF'
/var/www/binday/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 deploy www-data
}
EOF
print_status "Log rotation configured"

# Install UFW firewall
echo "🔥 Setting up firewall..."
apt install -y ufw
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw --force enable
print_status "Firewall configured"

# Display next steps
echo ""
echo "🎉 VPS setup completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "=============="
echo ""
print_warning "1. Add your SSH public key to deploy user:"
echo "   sudo -u deploy nano /home/deploy/.ssh/authorized_keys"
echo "   (Paste your public key and save)"
echo ""
print_warning "2. Set proper permissions for SSH key:"
echo "   sudo -u deploy chmod 600 /home/deploy/.ssh/authorized_keys"
echo ""
print_warning "3. Test SSH connection:"
echo "   ssh deploy@$(hostname -I | awk '{print $1}')"
echo ""
print_warning "4. Configure GitHub Secrets with these values:"
echo "   VPS_HOST: $(hostname -I | awk '{print $1}')"
echo "   VPS_USER: deploy"
echo "   DEPLOY_PATH: /var/www/binday"
echo "   BACKUP_PATH: /var/backups/binday"
echo ""
print_warning "5. If you installed MySQL, secure it:"
echo "   mysql_secure_installation"
echo ""
print_warning "6. Create database and user (if using MySQL):"
echo "   sudo mysql"
echo "   CREATE DATABASE binday_production;"
echo "   CREATE USER 'binday_user'@'localhost' IDENTIFIED BY 'your_password';"
echo "   GRANT ALL ON binday_production.* TO 'binday_user'@'localhost';"
echo "   FLUSH PRIVILEGES;"
echo "   EXIT;"
echo ""
print_warning "7. Update your domain's DNS to point to this server"
echo ""
print_warning "8. After first deployment, set up SSL:"
echo "   sudo apt install certbot python3-certbot-nginx"
echo "   sudo certbot --nginx -d yourdomain.com"
echo ""
print_status "Your VPS is ready for SSH deployment! 🚀"
