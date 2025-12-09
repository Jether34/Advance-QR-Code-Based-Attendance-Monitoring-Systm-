# Oracle Cloud Deployment - Quick Start Checklist

## Pre-Deployment (Do This Now)
- [ ] Create Oracle Cloud free account (https://www.oracle.com/cloud/free/)
- [ ] Verify email and phone
- [ ] Set spending limit to $0 in Billing settings
- [ ] Download SSH key pair and save securely

## Step 1: Create VM (10 min)
- [ ] Oracle Console → Compute → Instances
- [ ] Create Instance with:
  - Image: Ubuntu 22.04
  - Shape: Ampere (ARM) VM.Standard.A1.Flex
  - OCPUs: 2, RAM: 12GB
  - Download SSH key
- [ ] **Copy your Public IP address**

## Step 2: Connect via SSH (5 min)
```powershell
# Windows PowerShell
ssh -i "C:\path\to\key.key" ubuntu@YOUR_PUBLIC_IP
```

## Step 3: Run Automated Setup (10 min)
Paste this into SSH terminal:
```bash
wget https://raw.githubusercontent.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-/update-2025-11-dev-qr/quick_oracle_deploy.sh
chmod +x quick_oracle_deploy.sh
./quick_oracle_deploy.sh
```

## Step 4: Configure Database (5 min)
After setup completes:
```bash
sudo nano /var/www/puta/config.php
```

Update with credentials shown at end of script.

## Step 5: Import Your Database (5 min)
From your local PC, export:
```bash
mysqldump -u root tapin_db > tapin_backup.sql
```

Copy to server:
```bash
scp -i "key.key" tapin_backup.sql ubuntu@YOUR_PUBLIC_IP:/home/ubuntu/
```

Import on server:
```bash
mysql -u tapin_user -p tapin_db < /home/ubuntu/tapin_backup.sql
```

## Step 6: Test Access (2 min)
Open browser:
```
http://YOUR_PUBLIC_IP/puta
```

Should see TapIn landing page ✅

## Step 7: Set Up HTTPS (Optional but Recommended) (5 min)
```bash
sudo certbot --apache
# Follow prompts
```

## Step 8: Add Custom Domain (Optional) (10 min)
1. Point your domain DNS to `YOUR_PUBLIC_IP`
2. Update Oracle Networking firewall rules if needed
3. Re-run certbot to add domain certificate

## Post-Deployment
- [ ] Test login/signup functionality
- [ ] Test QR scanner
- [ ] Verify database operations
- [ ] Set up automated backups (optional)
- [ ] Enable monitoring (optional)

## Important Notes
- ✅ Setup is fully automated - ~30 minutes total
- ✅ Everything is free forever on Always Free tier
- ⚠️ Remember to log in every 30 days to keep account active
- ⚠️ Set spending limit to $0 to prevent unexpected charges
- ✅ Supports HTTPS, custom domains, cron jobs

## Troubleshooting
If something goes wrong:
```bash
# Check Apache
sudo systemctl status apache2
sudo tail -f /var/log/apache2/error.log

# Check MySQL
sudo systemctl status mysql
mysql -u root -e "USE tapin_db; SHOW TABLES;"

# Check PHP
php -v
php -m
```

## Support
For detailed guide, see: `ORACLE_CLOUD_DEPLOYMENT.md` in the repository

Good luck! 🚀
