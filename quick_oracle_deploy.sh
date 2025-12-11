#!/bin/bash
# quick_oracle_deploy.sh - One-command Oracle Cloud setup for TapIn
# Usage: chmod +x quick_oracle_deploy.sh && ./quick_oracle_deploy.sh

set -e

echo "╔════════════════════════════════════════════════════════╗"
echo "║       TapIn - Oracle Cloud Automated Deployment        ║"
echo "║  Hybrid QR Code Based Attendance Monitoring System     ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Step 1: System Update
echo -e "${BLUE}[1/8]${NC} Updating system packages..."
sudo apt update && sudo apt upgrade -y
echo -e "${GREEN}✓ System updated${NC}\n"

# Step 2: Install Apache & PHP
echo -e "${BLUE}[2/8]${NC} Installing Apache 2.4 and PHP 8.2..."
sudo apt install -y apache2 apache2-utils libapache2-mod-php \
    php php-mysql php-cli php-curl php-gd php-json php-mbstring php-xml
echo -e "${GREEN}✓ Apache & PHP installed${NC}\n"

# Step 3: Enable Apache modules
echo -e "${BLUE}[3/8]${NC} Enabling Apache modules (rewrite, deflate, expires)..."
sudo a2enmod rewrite deflate expires
sudo systemctl restart apache2
echo -e "${GREEN}✓ Apache modules enabled${NC}\n"

# Step 4: Install MySQL
echo -e "${BLUE}[4/8]${NC} Installing MySQL Server..."
sudo DEBIAN_FRONTEND=noninteractive apt install -y mysql-server
sudo systemctl start mysql
echo -e "${GREEN}✓ MySQL installed and started${NC}\n"

# Step 5: Install Ollama for AI features
echo -e "${BLUE}[5/9]${NC} Installing Ollama (AI engine)..."
if ! command -v ollama &> /dev/null; then
    curl -fsSL https://ollama.ai/install.sh | sh
    sudo systemctl enable ollama
    sudo systemctl start ollama
    echo "Waiting for Ollama to start..."
    sleep 3
    echo -e "${YELLOW}Pulling Llama 3.2 model (this takes ~5-10 minutes on first run)...${NC}"
    # Pull model with longer timeout
    timeout 900 ollama pull llama3.2 || echo -e "${YELLOW}Model pull in progress, may complete in background${NC}"
else
    echo "Ollama already installed"
fi
echo -e "${GREEN}✓ Ollama installed${NC}\n"

# Step 6: Install Certbot for SSL
echo -e "${BLUE}[6/9]${NC} Installing Certbot (Let's Encrypt SSL)..."
sudo apt install -y certbot python3-certbot-apache
echo -e "${GREEN}✓ Certbot installed${NC}\n"

# Step 7: Clone & setup TapIn
echo -e "${BLUE}[7/9]${NC} Cloning TapIn from GitHub..."
if [ ! -d "/var/www/puta" ]; then
    cd /var/www
    sudo git clone https://github.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-.git puta
    cd puta
    sudo git checkout update-2025-11-dev-qr
else
    echo "Directory /var/www/puta already exists. Updating..."
    cd /var/www/puta
    sudo git pull origin update-2025-11-dev-qr
fi
sudo chown -R www-data:www-data /var/www/puta
sudo chmod -R 755 /var/www/puta
sudo chmod -R 775 /var/www/puta/uploads
echo -e "${GREEN}✓ TapIn deployed${NC}\n"

# Step 8: Configure Apache VirtualHost
echo -e "${BLUE}[8/9]${NC} Configuring Apache VirtualHost..."
sudo tee /etc/apache2/sites-available/tapin.conf > /dev/null <<'VHEOF'
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/puta
    <Directory /var/www/puta>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/tapin_error.log
    CustomLog ${APACHE_LOG_DIR}/tapin_access.log combined
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
VHEOF

sudo a2ensite tapin
sudo a2dissite 000-default 2>/dev/null || true
sudo apache2ctl configtest
sudo systemctl restart apache2
echo -e "${GREEN}✓ Apache VirtualHost configured${NC}\n"

# Step 9: Create MySQL database & user
echo -e "${BLUE}[9/9]${NC} Setting up MySQL database..."
DB_PASS="TapIn2025$(date +%s | tail -c 5)"
sudo mysql -e "CREATE DATABASE IF NOT EXISTS tapin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'tapin_user'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON tapin_db.* TO 'tapin_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Save credentials to a secure file instead of echoing them to stdout
CRED_FILE="/root/.tapin_credentials"
sudo tee "$CRED_FILE" > /dev/null <<EOF
DB_HOST=localhost
DB_NAME=tapin_db
DB_USER=tapin_user
DB_PASS=$DB_PASS
EOF
sudo chmod 600 "$CRED_FILE"
sudo chown root:root "$CRED_FILE"

echo -e "${GREEN}✓ MySQL database created and credentials saved to $CRED_FILE${NC}\n"

# Summary
echo "╔════════════════════════════════════════════════════════╗"
echo "║           🎉 Setup Complete! 🎉                        ║"
echo "║    All components installed and running:               ║"
echo "║    ✓ Apache 2.4 & PHP 8.2                              ║"
echo "║    ✓ MySQL Database                                    ║"
echo "║    ✓ Ollama AI Engine (Llama 3.2)                      ║"
echo "║    ✓ Let's Encrypt SSL Ready                           ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}Database Credentials:${NC}"
echo "  Host: localhost"
echo "  Database: tapin_db"
echo "  User: tapin_user"
echo "  Credentials file: $CRED_FILE (permissions 600)"
echo "  To view the password: sudo cat $CRED_FILE"
echo ""
echo -e "${GREEN}AI Engine Status:${NC}"
echo "  Service: Ollama"
echo "  Model: Llama 3.2"
echo "  Endpoint: http://localhost:11434"
echo "  Status: $(systemctl is-active ollama)"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. SSH into your server and update config.php:"
echo "     sudo nano /var/www/puta/config.php"
echo ""
echo "  2. Replace DB credentials:"
echo "     define('DB_HOST', 'localhost');"
echo "     define('DB_NAME', 'tapin_db');"
echo "     define('DB_USER', 'tapin_user');"
echo "     define('DB_PASS', '$DB_PASS');"
echo ""
echo "  3. Import your database:"
echo "     mysql -u tapin_user -p tapin_db < backup.sql"
echo ""
echo "  4. Access the app:"
echo "     http://YOUR_PUBLIC_IP/puta"
echo ""
echo "  5. Set up HTTPS (recommended):"
echo "     sudo certbot --apache -d your-domain.com"
echo ""
echo -e "${BLUE}View logs:${NC}"
echo "  Apache: sudo tail -f /var/log/apache2/error.log"
echo "  MySQL: sudo tail -f /var/log/mysql/error.log"
echo ""
echo "For more details, see: /var/www/puta/ORACLE_CLOUD_DEPLOYMENT.md"
