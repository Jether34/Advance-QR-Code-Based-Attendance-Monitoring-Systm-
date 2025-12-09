# Oracle Cloud Automated Deployment Guide for TapIn

## Step 1: Create Oracle Cloud Account (5 minutes)
1. Go to https://www.oracle.com/cloud/free/
2. Click "Start for free"
3. Fill in details (use school email)
4. Verify email and phone
5. Set up payment method (won't be charged if you stay in free tier)
6. **Important:** After account creation, go to Billing → Cost Management → Set spending limit to $0

## Step 2: Create Compute VM (10 minutes)

### 2a. Navigate to Compute
- Oracle Cloud Console → Compute → Instances
- Click "Create Instance"

### 2b. Configure VM
```
Name: tapin-server
Image: Ubuntu 22.04 (Free tier eligible)
Shape: Ampere (ARM) - VM.Standard.A1.Flex (Always free)
  - OCPUs: 2
  - RAM: 12GB (within free tier)
Virtual Cloud Network: Create new (default is fine)
Subnet: Create new subnet (default is fine)
Public IP: Assign (required for web access)
SSH Key: Download and save the key pair securely
```

### 2c. Create & Wait
- Click "Create"
- Wait 2-3 minutes for VM to start
- Note the **Public IP Address** (you'll need this)

---

## Step 3: Connect to VM via SSH (5 minutes)

### Windows (PowerShell):
```powershell
# Set SSH key permissions
icacls "C:\path\to\your\key.key" /inheritance:r /grant:r "$env:USERNAME:(R)"

# Connect
ssh -i "C:\path\to\your\key.key" ubuntu@YOUR_PUBLIC_IP
```

### Mac/Linux:
```bash
chmod 600 ~/Downloads/your_key.key
ssh -i ~/Downloads/your_key.key ubuntu@YOUR_PUBLIC_IP
```

---

## Step 4: Automated Server Setup (copy-paste into SSH terminal)

Run this single script to install everything:

```bash
#!/bin/bash
set -e

echo "=== TapIn Server Setup on Oracle Cloud ==="
echo "Step 1: Update system..."
sudo apt update && sudo apt upgrade -y

echo "Step 2: Install Apache..."
sudo apt install -y apache2 apache2-utils libapache2-mod-php php-mysql php-cli php-curl php-gd php-json php-mbstring php-xml git curl wget

echo "Step 3: Enable Apache modules..."
sudo a2enmod rewrite
sudo a2enmod deflate
sudo a2enmod expires
sudo systemctl restart apache2

echo "Step 4: Install MySQL..."
sudo apt install -y mysql-server

echo "Step 5: Install Certbot (Let's Encrypt SSL)..."
sudo apt install -y certbot python3-certbot-apache

echo "Step 6: Clone TapIn repository..."
cd /var/www
sudo git clone https://github.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-.git puta
cd puta
sudo git checkout update-2025-11-dev-qr
sudo chown -R www-data:www-data /var/www/puta
sudo chmod -R 755 /var/www/puta

echo "Step 7: Configure Apache VirtualHost..."
sudo tee /etc/apache2/sites-available/tapin.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName tapin.up.oracle.cloud
    ServerAlias YOUR_DOMAIN_HERE
    DocumentRoot /var/www/puta
    <Directory /var/www/puta>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/tapin_error.log
    CustomLog \${APACHE_LOG_DIR}/tapin_access.log combined
</VirtualHost>
EOF

sudo a2ensite tapin
sudo a2dissite 000-default
sudo apache2ctl configtest
sudo systemctl restart apache2

echo "Step 8: Set up MySQL database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS tapin_db;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'tapin_user'@'localhost' IDENTIFIED BY 'TapIn2025Secure!';"
sudo mysql -e "GRANT ALL PRIVILEGES ON tapin_db.* TO 'tapin_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "=== Setup Complete! ==="
echo "Next steps:"
echo "1. Update /var/www/puta/config.php with your DB credentials"
echo "2. Update /var/www/puta/.env with environment variables"
echo "3. Import your database: mysql -u tapin_user -p tapin_db < your_db_backup.sql"
echo "4. Set up HTTPS: sudo certbot --apache -d YOUR_DOMAIN"
```

---

## Step 5: Configure TapIn (5 minutes)

SSH into your server:

```bash
# Edit config.php
sudo nano /var/www/puta/config.php
```

Update these lines:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tapin_db');
define('DB_USER', 'tapin_user');
define('DB_PASS', 'TapIn2025Secure!');
```

---

## Step 6: Import Your Database (5 minutes)

Export from local XAMPP:
```bash
# On your PC
mysqldump -u root tapin_db > tapin_backup.sql
```

Import to Oracle Cloud:
```bash
# Upload file via SCP or paste via console
scp -i your_key.key tapin_backup.sql ubuntu@YOUR_PUBLIC_IP:/home/ubuntu/
ssh -i your_key.key ubuntu@YOUR_PUBLIC_IP
mysql -u tapin_user -p tapin_db < /home/ubuntu/tapin_backup.sql
```

---

## Step 7: Set Up HTTPS (SSL) - Free with Let's Encrypt (5 minutes)

```bash
sudo certbot --apache -d tapin.up.oracle.cloud -d your-custom-domain.com
# Follow prompts, choose automatic redirect to HTTPS
```

---

## Step 8: Auto-Deploy from GitHub (Optional but Recommended)

Create `/var/www/puta/deploy.sh`:
```bash
#!/bin/bash
cd /var/www/puta
git fetch origin
git checkout update-2025-11-dev-qr
git pull origin update-2025-11-dev-qr
sudo systemctl restart apache2
echo "Deploy complete at $(date)" >> /var/log/tapin_deploy.log
```

Set up a GitHub webhook to trigger auto-deploys on push.

---

## Step 9: Set Up Auto-IP Detection Cron Job (Optional)

Add to crontab:
```bash
sudo crontab -e
# Add this line:
0 * * * * /usr/bin/php /var/www/puta/tools/auto_detect_ip.php >> /var/log/tapin_ip_detect.log 2>&1
```

---

## Step 10: Verify & Access

Open in browser:
```
https://YOUR_PUBLIC_IP/puta
```

You should see the TapIn landing page with logos and the "No pen, no paper, no problem" tagline!

---

## Troubleshooting

**Check Apache status:**
```bash
sudo systemctl status apache2
sudo tail -f /var/log/apache2/error.log
```

**Check MySQL:**
```bash
sudo systemctl status mysql
mysql -u tapin_user -p -e "USE tapin_db; SHOW TABLES;"
```

**Check PHP:**
```bash
php -v
php -m | grep -E "mysql|pdo"
```

---

## Summary

✅ Free Oracle Cloud VM (always free)
✅ PHP 8.2 + Apache + MySQL
✅ HTTPS/SSL enabled
✅ Auto-deploy from GitHub
✅ Custom domain ready
✅ Cron jobs for automation

**Total setup time:** ~30 minutes
**Monthly cost:** $0 (always)

Need help with any step? Ask!
