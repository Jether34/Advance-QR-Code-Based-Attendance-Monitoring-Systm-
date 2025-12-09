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

## Step 3: Run Automated Setup (15 min)
This installs: Apache, PHP 8.2, MySQL, **Ollama AI Engine**, and TapIn

Paste this into SSH terminal:
```bash
cd /tmp
wget https://raw.githubusercontent.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-/update-2025-11-dev-qr/quick_oracle_deploy.sh
chmod +x quick_oracle_deploy.sh
./quick_oracle_deploy.sh
```

**What gets installed:**
- ✅ Apache 2.4 & PHP 8.2
- ✅ MySQL Database Server
- ✅ Ollama AI Engine (Llama 3.2 model)
- ✅ TapIn application
- ✅ Automated configuration

**Output will show:**
- Database password (save this!)
- Ollama status (running on localhost:11434)
- Next steps

## Step 4: Configure TapIn (5 min)

Update database config:
```bash
sudo nano /var/www/puta/config.php
```

Replace DB credentials with values from script output.

Also update API config for Ollama:
```bash
sudo nano /var/www/puta/api_config.php
```

Ensure this line exists:
```php
define('OLLAMA_API_URL', 'http://localhost:11434/api/generate');
```

## Step 5: Verify Ollama is Running (2 min)
```bash
sudo systemctl status ollama
curl http://localhost:11434/api/tags
# Should show llama3.2 available
```

## Step 6: Import Your Database (5 min)
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
# Enter password from script output
```

## Step 7: Test Access (2 min)
Open browser:
```
http://YOUR_PUBLIC_IP/puta
```

Should see:
- ✅ TapIn landing page
- ✅ Logo images loaded
- ✅ Login/Sign Up buttons
- ✅ "No pen, no paper, no problem" tagline

## Step 8: Test AI Features (2 min)
- [ ] Login as teacher/student
- [ ] Try Review Center (should chat with Ollama)
- [ ] Try Developer Dashboard AI Assistant (if developer)
- [ ] Verify AI responses appear (Ollama running)

## Step 9: Set Up HTTPS (Optional but Recommended) (5 min)
```bash
sudo certbot --apache
# Follow prompts
```

## Step 10: Add Custom Domain (Optional) (10 min)
1. Point your domain DNS to `YOUR_PUBLIC_IP`
2. Update Oracle Networking firewall rules if needed
3. Re-run certbot to add domain certificate

## Post-Deployment
- [ ] Test login/signup functionality
- [ ] Test QR scanner
- [ ] Verify database operations
- [ ] **Test AI features** (Review Center, Developer Dashboard)
- [ ] Test Ollama connectivity (`curl http://localhost:11434/api/tags`)
- [ ] Set up automated backups (optional)
- [ ] Enable monitoring (optional)

## Important Notes
- ✅ Setup is fully automated - ~45 minutes total (includes Ollama model download)
- ✅ Everything is free forever on Always Free tier
- ✅ **AI features fully operational** (Ollama Llama 3.2 included)
- ⚠️ Remember to log in every 30 days to keep account active
- ⚠️ Set spending limit to $0 to prevent unexpected charges
- ✅ Supports HTTPS, custom domains, cron jobs

## Troubleshooting

**General Status:**
```bash
# Check all services
sudo systemctl status apache2
sudo systemctl status mysql
sudo systemctl status ollama
```

**Apache Issues:**
```bash
sudo systemctl status apache2
sudo tail -f /var/log/apache2/error.log
```

**MySQL Issues:**
```bash
sudo systemctl status mysql
mysql -u tapin_user -p -e "USE tapin_db; SHOW TABLES;"
```

**Ollama/AI Issues:**
```bash
sudo systemctl status ollama
curl http://localhost:11434/api/tags

# If model missing:
ollama pull llama3.2

# If Ollama won't start:
sudo systemctl restart ollama
```

**PHP Issues:**
```bash
php -v
php -m | grep -E "mysql|pdo|curl"
```
php -v
php -m
```

## Support
For detailed guide, see: `ORACLE_CLOUD_DEPLOYMENT.md` in the repository

Good luck! 🚀
