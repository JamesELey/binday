# 🚀 **GitHub Actions Deployment Guide for Fasthost**

## 🎯 **Overview**

GitHub Actions provides a much more reliable deployment strategy than manual FTP uploads, especially after experiencing 80-minute timeouts. Two workflows are available:

1. **📦 Package Creator** (Recommended) - Creates optimized zip packages for manual upload
2. **🚀 Direct Deploy** - Attempts automated FTP deployment

## 📦 **Method 1: Package Creator Workflow (Recommended)**

### **Advantages:**
- ✅ **No timeout issues** - Creates optimized packages for quick upload
- ✅ **Reliable** - Uses GitHub's infrastructure
- ✅ **Flexible** - Can include/exclude vendor directory
- ✅ **Fast** - 5-10 minute deployments instead of 80+ minutes
- ✅ **Resumable** - Can retry upload if interrupted

### **Setup:**

1. **🔧 Configure Repository Secrets** (Optional for package method):
   ```
   Settings → Secrets and variables → Actions → New repository secret
   ```
   - `APP_KEY` - Your Laravel application key (optional - can generate manually)

2. **🚀 Trigger Deployment:**
   - **Automatic**: Push to `main` branch
   - **Manual**: Go to Actions → "Create Deployment Package" → Run workflow

3. **📥 Download Package:**
   - Go to Actions → Latest workflow run
   - Download "binday-deployment-package" artifact
   - Download "deployment-instructions" artifact

4. **📤 Upload to Fasthost:**
   - Extract the deployment package
   - Upload via Fasthost File Manager
   - Follow deployment instructions

### **Workflow Options:**
- **Include Vendor**: Complete package (~20-30MB) - ready to use
- **Exclude Vendor**: Minimal package (~5-10MB) - requires `composer install` on server

## 🚀 **Method 2: Direct FTP Deployment**

### **Advantages:**
- ✅ **Fully automated** - No manual upload required
- ✅ **One-click deployment** - Push to main = automatic deploy
- ✅ **Professional CI/CD** - Industry standard practice

### **Setup:**

1. **🔧 Configure Repository Secrets:**
   ```
   Settings → Secrets and variables → Actions → New repository secret
   ```
   
   **Required Secrets:**
   - `FTP_USERNAME` - Your Fasthost FTP username (bindayadmin)
   - `FTP_PASSWORD` - Your Fasthost FTP password
   - `APP_KEY` - Your Laravel application key
   - `DB_DATABASE` - Your database name (if using MySQL)
   - `DB_USERNAME` - Your database username
   - `DB_PASSWORD` - Your database password

2. **🚀 Deploy:**
   - Push to `main` branch for automatic deployment
   - Or manually trigger via Actions → "Deploy to Fasthost" → Run workflow

### **Potential Issues:**
- May still experience FTP timeouts (though less likely with GitHub's infrastructure)
- Requires FTP credentials in repository secrets
- Less flexible than package method

## ⚡ **Timeout Prevention in GitHub Actions**

### **Optimizations Applied:**
1. **Composer Cache** - Faster dependency installation
2. **Production Dependencies Only** - Smaller package size
3. **Excluded Large Files** - No git history, node_modules, etc.
4. **Optimized Laravel** - Cached configs and routes
5. **FTPS Protocol** - More reliable than FTP
6. **Increased Timeouts** - 5-minute timeout per operation

### **Fallback Strategy:**
If FTP still times out, the workflow automatically:
1. Creates deployment package as artifact
2. Provides manual upload instructions
3. Includes troubleshooting guidance

## 🔧 **Setting Up GitHub Actions**

### **Step 1: Repository Secrets**

Navigate to: `Settings → Secrets and variables → Actions`

**For Package Method (Minimal):**
```
APP_KEY=base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=
```

**For Direct FTP Method (Complete):**
```
FTP_USERNAME=bindayadmin
FTP_PASSWORD=9XZwda@2SqZxXzk
APP_KEY=base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username  
DB_PASSWORD=your_database_password
```

### **Step 2: Enable Actions**

1. Go to your repository
2. Click "Actions" tab
3. If disabled, click "Enable Actions"

### **Step 3: First Deployment**

**Package Method:**
1. Push code to main branch
2. Go to Actions → "Create Deployment Package"
3. Wait for completion (~2-3 minutes)
4. Download artifacts
5. Upload to Fasthost

**Direct FTP Method:**
1. Configure all secrets
2. Push code to main branch
3. Monitor Actions → "Deploy to Fasthost"
4. Check deployment logs

## 📊 **Comparison: Manual vs GitHub Actions**

| Method | Time | Reliability | Setup Complexity | Automation |
|--------|------|-------------|------------------|------------|
| **Manual FTP Script** | 80+ minutes | ❌ Poor (timeouts) | ✅ Low | ❌ None |
| **Manual File Manager** | 10-15 minutes | ✅ Good | ✅ Low | ❌ None |
| **GitHub Actions Package** | 5-10 minutes | ✅ Excellent | ⚡ Medium | ⚡ Semi-auto |
| **GitHub Actions FTP** | 3-5 minutes | ⚡ Good | ❌ High | ✅ Full auto |

## 🔄 **Workflow Files Included**

### **`.github/workflows/create-deployment-package.yml`**
- **Trigger**: Push to main or manual dispatch
- **Purpose**: Creates optimized deployment packages
- **Output**: Downloadable zip files + instructions
- **Best for**: Reliable, timeout-free deployments

### **`.github/workflows/deploy-to-fasthost.yml`**
- **Trigger**: Push to main or manual dispatch  
- **Purpose**: Direct FTP deployment to Fasthost
- **Output**: Live application deployment
- **Best for**: Fully automated CI/CD pipeline

## 🎯 **Recommended Workflow**

### **For Initial Setup:**
1. Use **Package Creator** method for first deployment
2. Test everything works correctly
3. Then switch to **Direct FTP** for updates (if desired)

### **For Regular Updates:**
1. **Small changes**: Use **Direct FTP** workflow
2. **Large changes**: Use **Package Creator** for safety
3. **Emergency fixes**: Manual File Manager upload

## 🔍 **Monitoring Deployments**

### **GitHub Actions Logs:**
- Real-time deployment progress
- Error messages and troubleshooting
- Deployment artifacts and downloads
- Success/failure notifications

### **Fasthost Verification:**
After deployment, check:
- ✅ Website loads: https://thebinday.co.uk
- ✅ Map functionality works
- ✅ Collections management accessible
- ✅ Admin seeding interface: https://thebinday.co.uk/admin/seed

## 🛠️ **Troubleshooting GitHub Actions**

### **Issue: "Secrets not found"**
**Solution**: Verify all required secrets are set in repository settings

### **Issue: "FTP timeout in GitHub Actions"**
**Solution**: Use Package Creator method instead of Direct FTP

### **Issue: "Deployment package too large"**
**Solution**: Use "Exclude Vendor" option and run `composer install` on server

### **Issue: "Actions not triggering"**
**Solution**: Check Actions are enabled and workflow files are in `main` branch

---

**🎉 GitHub Actions provides a modern, reliable deployment solution that eliminates the 80-minute timeout issues while providing professional CI/CD capabilities!**
