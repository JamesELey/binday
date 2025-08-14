# 🗄️ BinDay Database Setup Guide

## ⚡ **Quick Setup for Local Development**

### **Step 1: Start XAMPP MySQL**
1. Open **XAMPP Control Panel**
2. Click **Start** next to **MySQL** 
3. Verify MySQL is running (should show green)

### **Step 2: Create Database**
1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **"New"** in the left sidebar
3. Create database named: `binday`
4. Set Collation: `utf8mb4_unicode_ci`
5. Click **Create**

### **Step 3: Run Laravel Migrations**
```powershell
# In your project directory
php artisan migrate

# If successful, you should see:
# ✅ Migration table created successfully
# ✅ Migrating: 2025_08_14_155300_create_users_table
# ✅ Migrated: 2025_08_14_155300_create_users_table
```

### **Step 4: Create Default Admin User**
```powershell
php artisan tinker

# In Tinker, run:
$admin = new App\User([
    'name' => 'Admin User',
    'email' => 'admin@binday.com',
    'password' => 'password123',
    'role' => 'admin',
    'active' => true
]);
$admin->save();

# Exit Tinker
exit
```

## 🚀 **Production VPS Configuration**

Your VPS is already configured with:
- ✅ **MariaDB running**
- ✅ **Database**: `binday`
- ✅ **User credentials** in GitHub Secrets

The migrations will run automatically on the VPS when you deploy.

## 🔧 **Current Database Configuration**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=binday
DB_USERNAME=root
DB_PASSWORD=
```

## 📋 **Database Tables Created**

### **users table:**
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `role` - admin | worker | customer
- `active` - Account status
- `assigned_area_ids` - JSON array (for workers)
- `phone` - Optional phone number
- `address` - Optional address
- `timestamps` - Created/updated dates

## 🎯 **User Roles & Permissions**

### **👑 Admin**
- Create/manage all areas
- Assign workers to areas
- View/edit all collections
- Manage user accounts
- Access admin panel

### **👷 Worker**  
- View assigned areas only
- Create/edit collections in assigned areas
- Cannot manage areas or other users
- Worker dashboard

### **👤 Customer**
- Create collections in valid areas
- Edit own collections only
- View public area information
- Customer dashboard

## ✅ **Verification Steps**

1. **Database Connected**: `php artisan migrate:status`
2. **Tables Created**: Check phpMyAdmin for `users` table
3. **Login Works**: Visit http://127.0.0.1:8000/login
4. **Registration Works**: Visit http://127.0.0.1:8000/register

---

**🎉 Once database is set up, the three-tier user system will be fully functional!**
