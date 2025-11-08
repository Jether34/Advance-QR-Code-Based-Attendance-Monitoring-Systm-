# 📱 Mobile QR Code Setup Guide

## 🚨 IMPORTANT: Fixing "Can't Connect" Error

If your phone shows **"Safari can't open the page because it couldn't connect to the server"**, follow these steps:

### ✅ **Step 1: Check Network Connection**
1. **Both devices must be on the same Wi-Fi network**
   - Your computer and phone must connect to the same router
   - Check Wi-Fi settings on both devices

2. **Test basic connectivity**
   - On your phone, try visiting: `http://192.168.254.124/puta/test_connection.php`
   - If this works, your network is configured correctly

### ✅ **Step 2: Configure Windows Firewall**

**Option A: Quick Fix (Recommended)**
1. Open **Windows Security** (search in Start menu)
2. Go to **Firewall & network protection**
3. Click **Allow an app through firewall**
4. Click **Change Settings**
5. Find **Apache HTTP Server** and check both boxes (Private and Public)
6. If not found, click **Allow another app** → Browse to `C:\xampp\apache\bin\httpd.exe`

**Option B: Create Firewall Rule**
1. Open **Windows Defender Firewall with Advanced Security**
2. Click **Inbound Rules** → **New Rule**
3. Select **Port** → **TCP** → **Specific local ports: 80**
4. Allow the connection for all profiles
5. Name it "XAMPP Apache HTTP"

### ✅ **Step 3: Update IP Address Configuration**

1. **Find your current IP address:**
   ```powershell
   ipconfig
   ```
   Look for "IPv4 Address" under your active Wi-Fi connection

2. **Update config.php file:**
   - Open `c:\xampp\htdocs\puta\config.php`
   - Change `SERVER_IP` to your actual IP address
   - Save the file

### ✅ **Step 4: Test the Setup**

1. **Test from phone browser:**
   - Visit: `http://YOUR_IP_ADDRESS/puta/test_connection.php`
   - Should show "Connection Successful!" message

2. **Test QR code scanning:**
   - Generate a new QR code from `card.php`
   - Scan with your phone's camera
   - Should open student information page

### 🔧 **Troubleshooting Common Issues**

**Problem: "This site can't be reached"**
- Solution: Check Windows Firewall settings above

**Problem: QR code shows localhost URL**
- Solution: Update IP address in config.php file

**Problem: Phone and computer on different networks**
- Solution: Connect both to same Wi-Fi network

**Problem: Router blocking device communication**
- Solution: Disable "AP Isolation" in router settings (advanced)

### 📱 **Quick Test URLs**

Replace `192.168.254.124` with your actual IP address:

- **Connection Test:** `http://192.168.254.124/puta/test_connection.php`
- **Home Page:** `http://192.168.254.124/puta/index.php`
- **Sample Student:** `http://192.168.254.124/puta/student_info.php?id=SAMPLE123`
- **QR Redirect:** `http://192.168.254.124/puta/qr.php?s=SAMPLE123`

### ✅ **Success Indicators**

When everything works correctly:
- ✅ Phone can access test_connection.php
- ✅ QR codes open student information pages
- ✅ No "can't connect" errors
- ✅ Beautiful student info displays on mobile

---

**Need help?** Make sure XAMPP Apache is running and both devices are on the same network!