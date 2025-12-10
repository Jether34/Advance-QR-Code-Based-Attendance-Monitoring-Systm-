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

**IMPORTANT:** Use the automated `quick_oracle_deploy.sh` script instead. It handles everything including Ollama setup.

SSH into your server and run:
```bash
cd /tmp
wget https://raw.githubusercontent.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-/update-2025-11-dev-qr/quick_oracle_deploy.sh
chmod +x quick_oracle_deploy.sh
./quick_oracle_deploy.sh
```

This single script will:
- ✅ Install Apache 2.4 & PHP 8.2
- ✅ Install MySQL Server  
- ✅ Install Ollama AI engine (Llama 3.2 model)
- ✅ Clone TapIn from GitHub
- ✅ Configure Apache VirtualHost
- ✅ Set up MySQL database
- ✅ Display credentials and next steps

**Note:** Ollama model download (~5-10 min on first run) happens automatically in background if needed.

---

## Step 5: Configure TapIn (5 minutes)

SSH into your server:

```bash
# Edit config.php
sudo nano /var/www/puta/config.php
```

Update these lines (use credentials from script output):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tapin_db');
define('DB_USER', 'tapin_user');
// NOTE: Do NOT store real passwords in this file. Retrieve the generated password
// from the deploy script's credentials file on the server:
//   sudo cat /root/.tapin_credentials
// and copy the value for DB_PASS into `config.php`, or fetch it from your secret store.
define('DB_PASS', 'REPLACE_WITH_SECURE_PASSWORD_FROM_SERVER_OR_VAULT');
```

Also update `api_config.php` for Ollama:
```bash
sudo nano /var/www/puta/api_config.php
```

Change this line to localhost (for cloud deployment):
```php
define('OLLAMA_API_URL', 'http://localhost:11434/api/generate');
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
# Upload file via SCP
scp -i your_key.key tapin_backup.sql ubuntu@YOUR_PUBLIC_IP:/home/ubuntu/

# SSH and import
ssh -i your_key.key ubuntu@YOUR_PUBLIC_IP
mysql -u tapin_user -p tapin_db < /home/ubuntu/tapin_backup.sql
# Enter the password from deployment script when prompted
```

---

## Step 7: Verify AI Engine is Running

```bash
# Check Ollama service
sudo systemctl status ollama

# Test Ollama endpoint
curl http://localhost:11434/api/tags

# If needed, restart Ollama
sudo systemctl restart ollama
```

---

## Step 8: Set Up HTTPS (SSL) - Free with Let's Encrypt (5 minutes)

```bash
sudo certbot --apache -d your-custom-domain.com
# Follow prompts, choose automatic redirect to HTTPS
```

---

## Step 9: Auto-Deploy from GitHub (Optional but Recommended)

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

## Step 10: Verify & Access

Open in browser:
```
http://YOUR_PUBLIC_IP/puta
```

Or once HTTPS is set up:
```
https://your-custom-domain.com/puta
```

You should see:
- ✅ TapIn landing page with logos
- ✅ "No pen, no paper, no problem" tagline
- ✅ Login/Sign Up buttons functional
- ✅ Review Center & AI Assistant available (Ollama running)

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

**Check Ollama (AI Engine):**
```bash
sudo systemctl status ollama
curl http://localhost:11434/api/tags
# Should show llama3.2 in available models
```

**Check PHP:**
```bash
php -v
php -m | grep -E "mysql|pdo"
```

**If Ollama model needs pulling:**
```bash
ollama pull llama3.2
```

---

## Summary

✅ Free Oracle Cloud VM (always free)
✅ PHP 8.2 + Apache + MySQL
✅ Ollama AI Engine (Llama 3.2) - Full AI features enabled
✅ HTTPS/SSL enabled
✅ Auto-deploy from GitHub
✅ Custom domain ready

**Total setup time:** ~45 minutes (includes Ollama model download)
**Monthly cost:** $0 (always free tier)
**AI Features:** Fully functional (Review Center, Developer Dashboard, Jether AI Assistant)

Need help with any step? Ask!
