# 🧪 BinDay Deployment Health Checks

This directory contains automated tests to verify successful deployment and site functionality.

## 📋 Available Health Checks

### **1. Quick Health Check (Shell Script)**
**File:** `quick-health-check.sh`

**Purpose:** Fast, lightweight health check using curl/wget
- ✅ Tests site accessibility
- ✅ Verifies all main application pages
- ✅ Checks response times
- ✅ Basic asset verification

**Usage:**
```bash
# Run from project root
./tests/quick-health-check.sh http://your-site.com

# Or with environment variable
SITE_URL=http://your-site.com ./tests/quick-health-check.sh
```

### **2. Comprehensive Health Check (Node.js)**
**File:** `deployment-health-check.js`

**Purpose:** Detailed health check with comprehensive testing
- ✅ All features of quick check, plus:
- ✅ Response time analysis
- ✅ Laravel framework detection
- ✅ Database connectivity testing
- ✅ Asset loading verification
- ✅ Detailed error reporting

**Usage:**
```bash
# Run from project root
node tests/deployment-health-check.js http://your-site.com

# Or with environment variable
SITE_URL=http://your-site.com node tests/deployment-health-check.js
```

## 🚀 Automated Testing in GitHub Actions

Both health checks run automatically after each deployment:

1. **Basic Health Check**: Always runs using shell script
2. **Advanced Health Check**: Runs if Node.js is available (optional)

### **Test Results in GitHub Actions:**

```
🧪 Running automated health checks...
🔍 Testing deployment health...

🌐 Testing Site Accessibility...
   ✅ Home page (200, 0.123s)

🗺️ Testing Application Pages...
   ✅ Bin Map page (200, 0.456s)
   ✅ Collections page (200, 0.234s)
   ✅ Areas page (200, 0.345s)
   ✅ Admin Seed page (200, 0.567s)

🎉 ALL TESTS PASSED! Deployment is working correctly! 🚀
```

## 📊 What Gets Tested

### **Core Application Pages:**
- **Home page** (`/`) - Main landing page
- **Bin Map** (`/bins/map`) - Interactive map functionality
- **Collections** (`/collections`) - Collection management
- **Areas** (`/areas`) - Geofenced area management
- **Admin Seed** (`/admin/seed`) - Admin data seeding interface

### **Technical Health:**
- **Response Times** - Page load performance
- **HTTP Status Codes** - Proper server responses
- **Laravel Framework** - Framework detection
- **Database Connectivity** - Database-dependent pages
- **Asset Loading** - CSS/JS resource availability

## 🔧 Manual Testing

You can also run these tests manually for troubleshooting:

```bash
# Test your deployed site
cd /path/to/binday
./tests/quick-health-check.sh http://217.154.48.34

# Or run the comprehensive version
node tests/deployment-health-check.js http://217.154.48.34
```

## ⚠️ Troubleshooting Failed Tests

If health checks fail, check:

1. **Web Server Status**: `sudo systemctl status nginx`
2. **PHP-FPM Status**: `sudo systemctl status php8.3-fpm`
3. **Site Configuration**: `/etc/nginx/sites-available/binday`
4. **Error Logs**: `/var/log/nginx/error.log`
5. **Laravel Logs**: `/var/www/binday/storage/logs/laravel.log`
6. **File Permissions**: `ls -la /var/www/binday/storage`

## 🎯 Success Criteria

Health checks pass when:
- ✅ All pages return HTTP 200 status
- ✅ Response times under 5 seconds
- ✅ No server errors (500/502/503)
- ✅ Laravel framework detected
- ✅ Database connectivity confirmed

## 🌐 Integration with Deployment

These tests automatically run as the final step of every deployment:

```yaml
- name: 🧪 Health Check - Deployment Verification
  run: |
    ./tests/quick-health-check.sh "http://${{ secrets.VPS_HOST }}"
```

This ensures every deployment is verified and working before completion! 🚀
