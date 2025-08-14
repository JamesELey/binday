# 🚀 BinDay - Modern SSH Deployment

**BinDay** is a Laravel application for managing bin collection schedules with geofencing capabilities. This repository uses modern SSH-based deployment for fast, reliable updates.

## ⚡ Quick Deployment

### **Prerequisites**
- VPS with Ubuntu/Debian
- SSH access configured
- PHP 8.3+ and Composer installed on VPS

### **GitHub Secrets Required**
```
SSH_PRIVATE_KEY = Your SSH private key
VPS_HOST = Your VPS IP address
VPS_USER = deploy
DEPLOY_PATH = /var/www/binday
BACKUP_PATH = /var/backups/binday
APP_KEY = Your Laravel app key
DB_* = Database credentials
```

### **Deploy**
```bash
git push origin main
# 🚀 Automatic deployment starts
# ⏱️ Completes in 2-5 minutes
# ✅ Application live!
```

## 📊 Deployment Features

- ⚡ **2-5 minute deployments**
- 🔐 **SSH key authentication**
- 📦 **Vendor dependencies installed on server**
- 💾 **Automatic backup before each deployment**
- 🔄 **Rollback capability**
- 📊 **Real-time monitoring via GitHub Actions**

## 🌐 Application Features

- 🗺️ **Interactive bin collection map**
- 📅 **Collection schedule management**
- 🏘️ **Geofenced area management**
- 📱 **Mobile-responsive interface**
- ⚙️ **Admin seeding interface**

## 📋 Setup Guide

**Full setup instructions:** [VPS_SSH_DEPLOYMENT_GUIDE.md](VPS_SSH_DEPLOYMENT_GUIDE.md)

**VPS setup script:** [setup-vps.sh](setup-vps.sh)

## 🔧 Local Development

```bash
# Clone repository
git clone <repository-url>
cd binday

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Start local server
php artisan serve
# Visit: http://localhost:8000
```

## 🎯 Application URLs

- **Home**: `/`
- **Bin Map**: `/bins/map`
- **Collections**: `/collections`
- **Areas**: `/areas`
- **Admin**: `/admin/seed`

---

**🎉 Modern, professional deployment achieved!** From 80-minute FTP deployments to 5-minute SSH deployments! 🚀
