# 🚀 Quick SSH Deployment Test Steps

Since your VPS is already set up, here's how to test the SSH deployment:

## 🔑 Step 1: Configure GitHub Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions

Add these **required secrets**:

```
SSH_PRIVATE_KEY = Your SSH private key (entire content of ~/.ssh/id_rsa)
VPS_HOST        = Your VPS IP address or domain name
VPS_USER        = Your deployment username (e.g., deploy)
DEPLOY_PATH     = /var/www/binday
BACKUP_PATH     = /var/backups/binday

APP_KEY         = base64:your-laravel-app-key
DB_CONNECTION   = mysql
DB_HOST         = localhost
DB_PORT         = 3306
DB_DATABASE     = binday_production
DB_USERNAME     = your-database-username
DB_PASSWORD     = your-database-password
```

## 🧪 Step 2: Test SSH Connection

Before running the workflow, test SSH connection manually:

```bash
ssh your-username@your-vps-ip
```

Make sure:
- ✅ SSH connection works
- ✅ Deployment directories exist: `/var/www/binday` and `/var/backups/binday`
- ✅ User has write permissions to these directories
- ✅ PHP and Composer are installed

## 🚀 Step 3: Trigger Deployment

Once secrets are configured:

### Option A: Automatic (push to main)
```bash
# Make any small change
echo "# Test deployment" >> README.md
git add README.md
git commit -m "test: Trigger SSH deployment"
git push origin main
```

### Option B: Manual trigger
1. Go to GitHub → Actions
2. Select "Deploy to VPS via SSH"
3. Click "Run workflow"
4. Click "Run workflow" button

## 📊 Step 4: Monitor Deployment

1. **Watch GitHub Actions**:
   - Go to Actions tab in your repository
   - Click on the running workflow
   - Monitor real-time logs

2. **Expected workflow steps**:
   - ✅ Checkout code
   - ✅ Setup PHP
   - ✅ Install dependencies
   - ✅ Create deployment package
   - ✅ Setup SSH key
   - ✅ Deploy to VPS
   - ✅ Install vendor dependencies on server
   - ✅ Post-deployment optimization

## 🔍 Step 5: Verify Deployment

After successful deployment:

1. **Check VPS**:
   ```bash
   ssh your-username@your-vps-ip
   ls -la /var/www/binday
   ls -la /var/backups/binday
   ```

2. **Test website** (if web server configured):
   - Visit your domain
   - Check application functionality

## ⚠️ Troubleshooting

**If deployment fails**:

1. **Check GitHub Actions logs** for specific error
2. **Common issues**:
   - SSH key format wrong (must be complete private key)
   - VPS directories don't exist
   - Permission issues
   - Missing PHP/Composer on VPS

3. **Debug SSH connection**:
   ```bash
   ssh-keyscan -H your-vps-ip >> ~/.ssh/known_hosts
   ssh -v your-username@your-vps-ip
   ```

## 🎯 Expected Results

- **Deployment time**: 2-5 minutes
- **Vendor install**: Automatic on server
- **Backup created**: Previous deployment backed up
- **Application ready**: Laravel optimized for production

Ready to test! 🚀
