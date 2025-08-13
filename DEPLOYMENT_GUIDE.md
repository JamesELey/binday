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

### **Method 1: Using Existing FTP Script (For Small Updates)**

⚠️ **TIMEOUT WARNING**: Full deployment can take 80+ minutes and may timeout on slower connections.

Your existing `deploy_ftp.ps1` script is configured for Fasthost but may timeout on initial full deployment:

```powershell
.\deploy_ftp.ps1
```

**Script Configuration:**
- Timeout: 15 minutes per file
- Retries: 3 attempts per file
- Buffer: 64KB chunks
- Handles passive/active mode switching

**Best for:** Updates and small changes after initial deployment

### **Method 2: Fasthost File Manager Upload (Recommended for Initial Deployment)**

**⭐ Best for large initial deployments to avoid timeouts:**

1. **Create deployment package:**
   ```powershell
   # Exclude large/unnecessary directories
   $exclude = @('.git', 'node_modules', 'storage/framework/cache', 'storage/logs', 'vendor')
   Compress-Archive -Path * -DestinationPath binday-deploy.zip -Force
   ```

2. **Upload via Fasthost Control Panel:**
   - Login to your Fasthost control panel
   - Go to "File Manager" 
   - Navigate to `/htdocs/`
   - Upload `binday-deploy.zip` (much faster than individual files)
   - Extract the zip file in File Manager
   - Rename extracted folder to `binday`

3. **Upload root entry point:**
   - Upload `root_index.php` as `/htdocs/index.php`

**Advantages:**
- ✅ Single file upload (faster, less prone to timeout)
- ✅ Fasthost File Manager is more reliable than FTP
- ✅ Can resume if interrupted
- ✅ No complex FTP configuration needed

### **Method 3: Hybrid Approach (Recommended)**

**Best overall strategy:**

1. **Initial deployment**: Use Method 2 (File Manager)
2. **Future updates**: Use Method 1 (FTP script for changed files only)

### **Method 4: Manual FTP with Chunked Upload**

If you must use FTP but want to avoid timeouts:

1. **Upload core Laravel files first:**
   - `public/index.php`
   - `bootstrap/app.php`
   - `artisan`
   - Essential config files

2. **Upload in batches:**
   - Upload `app/` directory
   - Upload `config/` directory
   - Upload `resources/` directory
   - Upload `routes/` directory
   - Upload `storage/` directory (but exclude `framework/cache`)

3. **Skip vendor directory initially** (can cause timeouts)
4. **Run composer install via SSH** if available

## ⚡ **Timeout Prevention Strategies**

### **1. Optimize Before Upload**
```powershell
# Clear all caches to reduce file count
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Remove development dependencies (reduces vendor size by ~50%)
composer install --no-dev --optimize-autoloader
```

### **2. Exclude Large Directories**
Temporarily exclude these from upload:
- `vendor/` (69MB+) - Can reinstall via composer on server
- `node_modules/` (if present)
- `storage/framework/cache/`
- `storage/logs/`
- `.git/`

### **3. FTP Script Modifications for Better Reliability**
If using the FTP script, consider increasing timeouts:

```powershell
# In deploy_ftp.ps1, increase these values:
$ftpTimeoutMs = 1800000           # 30 minutes (was 15)
$ftpReadWriteTimeoutMs = 1800000  # 30 minutes (was 15)
$ftpMaxRetries = 5                # 5 attempts (was 3)
```

### **4. Monitor Upload Progress**
Watch the FTP script output for:
- ✅ Files uploading successfully
- ⚠️ Retry attempts
- ❌ Permanent failures
- 🕐 Time taken per file

If it gets stuck on vendor files, stop and use File Manager method.

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

### **Issue: "FTP Upload Timeout / Takes 80+ Minutes"**
**Solution:**
1. **STOP the FTP script immediately** - don't let it run for hours
2. **Use File Manager method instead** (Method 2 above)
3. **If you must use FTP:**
   - Increase timeouts in `deploy_ftp.ps1` (see timeout prevention strategies)
   - Exclude vendor directory: add `$_.FullName -notmatch '\\vendor\\' -and` to the filter
   - Upload vendor separately or run `composer install` on server
4. **For future updates:** Use FTP script only for small changes

### **Issue: "FTP Script Hangs on Specific Files"**
**Solution:**
1. **Note which file caused the hang** (usually in vendor directory)
2. **Kill the PowerShell process** (Ctrl+C or close terminal)
3. **Exclude problematic directory:**
   ```powershell
   # Add to deploy_ftp.ps1 filter:
   $_.FullName -notmatch '\\vendor\\problematic-package\\' -and
   ```
4. **Use File Manager for large files**

### **Issue: "Partial Upload - Some Files Missing"**
**Solution:**
1. **Check FTP script output** for failed files
2. **Re-run script** - it will resume uploading missing files
3. **Or upload missing files manually** via File Manager
4. **Verify critical files exist:**
   - `/htdocs/index.php` (root redirect)
   - `/htdocs/binday/public/index.php` (Laravel entry point)
   - `/htdocs/binday/.env` (environment config)

### **Issue: "500 Internal Server Error"**
**Solution:**
1. Check error logs in Fasthost control panel
2. Verify file permissions (755 for directories, 644 for files)
3. Ensure `.env` file exists with correct database credentials
4. **If after timeout recovery:** Check if vendor directory uploaded completely

### **Issue: "Database Connection Error"**
**Solution:**
1. Verify database credentials in `.env`
2. Check database server hostname (might be `localhost` instead of `127.0.0.1`)
3. Ensure database user has proper permissions

### **Issue: "Route Not Found"**
**Solution:**
1. Clear route cache: `php artisan route:clear`
2. Verify `.htaccess` file exists in `/htdocs/binday/public/`
3. **If after incomplete upload:** Check if all route files uploaded

### **Issue: "Storage Not Writable"**
**Solution:**
1. Set storage permissions: `chmod -R 755 storage`
2. Check storage/logs/ directory permissions
3. **If using File Manager:** Set permissions via control panel interface

### **Issue: "Composer Dependencies Missing"**
**Solution:**
1. **If vendor directory didn't upload:** SSH to server and run `composer install --no-dev`
2. **No SSH access:** Upload vendor directory separately via File Manager
3. **Or:** Create a separate zip of just vendor directory and upload/extract

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

## 🎯 **Quick Deployment Summary (After 80-Minute Timeout)**

### **Recommended Approach:**

1. **🚀 Create Optimized Package:**
   ```powershell
   .\create-deployment-package.ps1
   ```
   This creates `binday-deploy.zip` (~15-20MB instead of 100MB+)

2. **📤 Upload via File Manager:**
   - Login to Fasthost control panel
   - File Manager → `/htdocs/`
   - Upload `binday-deploy.zip`
   - Extract → Rename to `binday`
   - Upload `root_index.php` as `index.php`

3. **⚙️ Configure Environment:**
   - Edit `/htdocs/binday/.env` with database credentials
   - Set file permissions if needed

**Total time: ~10-15 minutes instead of 80+ minutes!**

### **For Future Updates:**
- Use FTP script for small changes only
- Or create new deployment packages for major updates

---

**🎉 Your BinDay application should now be live on Fasthost!**
