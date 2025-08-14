# 🔐 GitHub Secrets Setup for Production Environment

## Why This is Needed
The deployment workflow now automatically creates the `.env` file from GitHub Secrets on every deployment. This ensures your production environment is always properly configured without manual intervention.

## 🔧 Required GitHub Secrets

Go to your GitHub repository → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Add these secrets (one by one):

### **Application Secrets**
| Secret Name | Value | Description |
|-------------|-------|-------------|
| `APP_NAME` | `"BinDay Collection Management"` | Application name |
| `APP_KEY` | `base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=` | Laravel encryption key |
| `APP_URL` | `http://217.154.48.34` | Your production domain (with http://) |

### **Database Secrets** 
| Secret Name | Value | Description |
|-------------|-------|-------------|
| `DB_HOST` | `127.0.0.1` | Database host (usually localhost) |
| `DB_PORT` | `3306` | Database port (3306 for MySQL/MariaDB) |
| `DB_DATABASE` | `binday` | Your database name |
| `DB_USERNAME` | `binday_user` | Your database username |
| `DB_PASSWORD` | `binday123` | Your database password |

### **Email Secrets (Optional)**
| Secret Name | Value | Description |
|-------------|-------|-------------|
| `MAIL_HOST` | `smtp.fasthosts.co.uk` | SMTP server |
| `MAIL_PORT` | `587` | SMTP port |
| `MAIL_USERNAME` | `your_email@thebinday.co.uk` | Email username |
| `MAIL_PASSWORD` | `your_email_password` | Email password |
| `MAIL_FROM_ADDRESS` | `noreply@thebinday.co.uk` | From email address |

### **Existing Deployment Secrets (Should Already Exist)**
| Secret Name | Description |
|-------------|-------------|
| `SSH_PRIVATE_KEY` | Your SSH private key |
| `VPS_HOST` | Your server IP/domain |
| `VPS_USER` | Your server username |
| `DEPLOY_PATH` | Deployment path on server |
| `BACKUP_PATH` | Backup path on server |

## 🚀 How to Set Up Secrets

### **Step 1: Generate App Key (if needed)**
If you need a new app key:
```bash
# On your local machine
php artisan key:generate --show
```
Copy the output (starts with `base64:`) and use it for `APP_KEY`

### **Step 2: Add Secrets to GitHub**

1. Go to your repository on GitHub
2. Click **Settings** (top menu)
3. Click **Secrets and variables** → **Actions** (left sidebar)
4. Click **New repository secret**
5. Add each secret from the tables above

### **Step 3: Example Secret Values**

Based on your working production setup, your secrets should be:

```
APP_NAME = "BinDay Collection Management"
APP_KEY = base64:DjhYykpLrjYkxLLWb7ZBHCqR9XIsQT8p41Z1Qq7iQcw=
APP_URL = http://217.154.48.34

DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_DATABASE = binday
DB_USERNAME = binday_user
DB_PASSWORD = binday123

MAIL_HOST = smtp.fasthosts.co.uk
MAIL_PORT = 587
MAIL_USERNAME = [your email username]
MAIL_PASSWORD = [your email password]
MAIL_FROM_ADDRESS = noreply@thebinday.co.uk
```

## ✅ After Adding Secrets

### **Step 4: Test the Setup**

1. **Commit and push** any changes to trigger deployment
2. **GitHub Actions** will automatically:
   - Create `.env` file from your secrets
   - Configure database connection
   - Run migrations and seeding
   - Your app will work immediately after deployment

### **Step 5: Verify it Worked**

After deployment:
- Visit your site: `http://217.154.48.34/login`
- Login with: `admin@binday.com` / `password123`
- Should work without any manual configuration!

## 🔒 Security Benefits

- ✅ **No sensitive data** in your code repository
- ✅ **Automatic environment setup** on every deployment
- ✅ **No manual configuration** needed after deployment
- ✅ **Encrypted storage** of credentials in GitHub
- ✅ **Easy updates** - just change the secret value

## 🐛 Troubleshooting

### **If deployment fails after adding secrets:**
1. Check that all required secrets are added
2. Verify secret names match exactly (case-sensitive)
3. Check GitHub Actions logs for specific errors

### **If database connection still fails:**
1. Verify `DB_*` secrets match your actual database setup
2. Test database connection manually on server
3. Check `.env` file was created correctly: `cat /path/to/app/.env`

### **Missing secrets error:**
If you see empty values in `.env`, it means the secret wasn't found. Double-check:
- Secret name spelling
- Secret value is not empty
- Secret is in the correct repository

## 🎯 Result

After setting up these secrets, every deployment will:
1. Automatically create a properly configured `.env` file
2. Connect to your database immediately
3. Run migrations and seeding
4. Be ready for login without manual intervention

**Your production environment will be fully automated!** 🚀
