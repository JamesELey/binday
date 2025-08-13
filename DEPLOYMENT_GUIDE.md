# 🚀 **BinDay Application - Fasthost Deployment Guide**

## 📋 **Pre-Deployment Checklist**

### 1. **Create Environment File**
Create a `.env` file in your project root with the following content:

```env
APP_NAME="BinDay Collection Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://thebinday.co.uk

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.fasthosts.co.uk
MAIL_PORT=587
MAIL_USERNAME=your_email@thebinday.co.uk
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@thebinday.co.uk"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. **Generate Application Key**
Run this command locally to generate an application key:
```bash
php artisan key:generate
```

### 3. **Optimize for Production**
Run these commands locally before deployment:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🚀 **Deployment Steps**

### **Method 1: Using Existing FTP Script (Recommended)**

Your existing `deploy_ftp.ps1` script is already configured for Fasthost. Simply run:

```powershell
.\deploy_ftp.ps1
```

**What the script does:**
- Uploads `root_index.php` as the main domain entry point
- Uploads your Laravel app to `/htdocs/binday/` folder
- Handles FTP timeouts and retries automatically
- Excludes unnecessary files (git, node_modules, cache)

### **Method 2: Manual FTP Upload**

If you prefer manual upload:

1. **Zip your project** (exclude: `.git`, `node_modules`, `storage/framework/cache`)
2. **Access Fasthost File Manager** via your control panel
3. **Upload and extract** the zip to `/htdocs/binday/`
4. **Upload `root_index.php`** to `/htdocs/index.php`

## 🗄️ **Database Setup**

### 1. **Create Database in Fasthost Control Panel**
- Go to "MySQL Databases" in your Fasthost control panel
- Create a new database and user
- Note the database name, username, and password

### 2. **Update .env File**
Update these values in your production `.env` file:
```env
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_username
DB_PASSWORD=your_actual_password
```

### 3. **Import Your Data**
Since this app uses JSON file storage, your data is already included in:
- `storage/app/collections.json`
- `storage/app/allowed_areas.json`

## 🔧 **Post-Deployment Configuration**

### 1. **Set File Permissions**
If you have SSH access, run:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

If no SSH access, use Fasthost File Manager to set folder permissions to 755.

### 2. **Clear Production Cache**
If you have SSH access:
```bash
cd /htdocs/binday
php artisan config:clear
php artisan config:cache
```

### 3. **Test the Application**
Visit your domain: `https://thebinday.co.uk`

Expected URLs:
- **Home**: `https://thebinday.co.uk/`
- **Map**: `https://thebinday.co.uk/bins/map`
- **Collections**: `https://thebinday.co.uk/collections`
- **Areas**: `https://thebinday.co.uk/areas`

## 🔒 **Security Considerations**

### 1. **Protect Sensitive Files**
Create/update `.htaccess` in `/htdocs/binday/` root:
```apache
# Protect sensitive files
<Files .env>
    Order allow,deny
    Deny from all
</Files>

<Files composer.json>
    Order allow,deny
    Deny from all
</Files>

<Files composer.lock>
    Order allow,deny
    Deny from all
</Files>

# Protect storage directory
RewriteEngine On
RewriteRule ^storage/.* - [F,L]
```

### 2. **Laravel Security Settings**
Ensure these are set in production `.env`:
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

## 🔍 **Troubleshooting**

### **Issue: "500 Internal Server Error"**
**Solution:**
1. Check error logs in Fasthost control panel
2. Verify file permissions (755 for directories, 644 for files)
3. Ensure `.env` file exists with correct database credentials

### **Issue: "Database Connection Error"**
**Solution:**
1. Verify database credentials in `.env`
2. Check database server hostname (might be `localhost` instead of `127.0.0.1`)
3. Ensure database user has proper permissions

### **Issue: "Route Not Found"**
**Solution:**
1. Clear route cache: `php artisan route:clear`
2. Verify `.htaccess` file exists in `/htdocs/binday/public/`

### **Issue: "Storage Not Writable"**
**Solution:**
1. Set storage permissions: `chmod -R 755 storage`
2. Check storage/logs/ directory permissions

## 📊 **Application-Specific Notes**

### **JSON Data Storage**
This application uses JSON files instead of a database:
- Collections: `storage/app/collections.json`
- Areas: `storage/app/allowed_areas.json`

These files are automatically included in the deployment.

### **Map Functionality**
- Uses OpenStreetMap (no API key required)
- Leaflet.js loads from CDN
- Should work immediately after deployment

### **Demo Data**
- Access seeding interface: `https://thebinday.co.uk/admin/seed`
- Can seed/delete demo data as needed
- Includes Eccleshall area and sample collections

## 📞 **Support**

### **Fasthost Support**
- Phone: 0330 043 2050
- Email: via control panel
- Live Chat: Available in control panel

### **Common Fasthost Requirements**
- **PHP Version**: Ensure PHP 8.1+ is selected in control panel
- **Max Execution Time**: May need to increase for large operations
- **Memory Limit**: Laravel requires at least 128MB

## ✅ **Deployment Success Checklist**

- [ ] `.env` file created with production settings
- [ ] Application key generated
- [ ] Production optimizations run (config:cache, route:cache, view:cache)
- [ ] Files uploaded via FTP script or manual upload
- [ ] File permissions set (755 for storage and bootstrap/cache)
- [ ] Database configured (if using MySQL later)
- [ ] Domain points to application
- [ ] Security `.htaccess` rules in place
- [ ] Application accessible and functional
- [ ] Map and collection features working
- [ ] Admin seeding interface accessible

---

**🎉 Your BinDay application should now be live on Fasthost!**
