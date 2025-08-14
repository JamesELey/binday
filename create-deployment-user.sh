#!/bin/bash

# 🚀 Create Deployment User for SSH-based GitHub Actions
# Run this script as root on your VPS

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

echo "🚀 Creating deployment user for GitHub Actions..."
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

# Create deployment user
USERNAME="deploy"
echo "👤 Creating user: $USERNAME"

if id "$USERNAME" &>/dev/null; then
    print_warning "User '$USERNAME' already exists"
else
    # Create user with home directory
    useradd -m -s /bin/bash "$USERNAME"
    print_status "User '$USERNAME' created"
fi

# Add user to www-data group for web server permissions
usermod -aG www-data "$USERNAME"
print_status "Added '$USERNAME' to www-data group"

# Create SSH directory
echo "🔑 Setting up SSH access..."
sudo -u "$USERNAME" mkdir -p /home/"$USERNAME"/.ssh
chmod 700 /home/"$USERNAME"/.ssh
chown "$USERNAME":"$USERNAME" /home/"$USERNAME"/.ssh

# Create authorized_keys file
touch /home/"$USERNAME"/.ssh/authorized_keys
chmod 600 /home/"$USERNAME"/.ssh/authorized_keys
chown "$USERNAME":"$USERNAME" /home/"$USERNAME"/.ssh/authorized_keys

print_status "SSH directory created"

# Create deployment directories
echo "📁 Creating deployment directories..."
mkdir -p /var/www/binday
mkdir -p /var/backups/binday

# Set ownership and permissions
chown -R "$USERNAME":www-data /var/www/binday
chown -R "$USERNAME":www-data /var/backups/binday
chmod -R 755 /var/www/binday
chmod -R 755 /var/backups/binday

print_status "Deployment directories created"

# Give deploy user sudo permissions for specific commands (optional)
echo "🔐 Setting up sudo permissions..."
cat > /etc/sudoers.d/deploy << EOF
# Allow deploy user to restart services and manage file permissions
deploy ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx
deploy ALL=(ALL) NOPASSWD: /bin/systemctl restart php*-fpm
deploy ALL=(ALL) NOPASSWD: /bin/chown
deploy ALL=(ALL) NOPASSWD: /bin/chmod
EOF

chmod 440 /etc/sudoers.d/deploy
print_status "Sudo permissions configured"

# Display next steps
echo ""
echo "🎉 Deployment user setup completed!"
echo ""
echo "📋 Next Steps:"
echo "=============="
echo ""
print_warning "1. Add your SSH public key to the deploy user:"
echo "   Run this command to edit the authorized_keys file:"
echo "   sudo nano /home/deploy/.ssh/authorized_keys"
echo ""
echo "   Then paste your PUBLIC key (id_rsa.pub content) into the file."
echo ""
print_warning "2. Test SSH connection:"
echo "   ssh deploy@$(hostname -I | awk '{print $1}')"
echo ""
print_warning "3. GitHub Secrets to use:"
echo "   VPS_USER: deploy"
echo "   VPS_HOST: $(hostname -I | awk '{print $1}')"
echo "   DEPLOY_PATH: /var/www/binday"
echo "   BACKUP_PATH: /var/backups/binday"
echo ""
print_warning "4. Your SSH private key goes in GitHub Secret: SSH_PRIVATE_KEY"
echo "   Use the PRIVATE key (id_rsa content) for this secret."
echo ""
print_status "Deploy user is ready for GitHub Actions! 🚀"
