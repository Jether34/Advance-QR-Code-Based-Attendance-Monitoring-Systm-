# 📱 Mobile Testing Setup Guide

## Your Configuration

✅ **WiFi IP Address:** `192.168.1.12`
✅ **Server IP Updated:** `config.php` changed to `192.168.1.12`
✅ **Ollama API Configured:** `api_config.php` created with centralized endpoints

---

## 🔧 Before You Start

### 1. **Windows Firewall Configuration**

   Apache HTTP Server needs to accept connections from your phone:

   ```powershell
   # Run PowerShell as Administrator
   New-NetFirewallRule -DisplayName "Apache HTTP Server" `
     -Direction Inbound -Program "C:\xampp\apache\bin\apache.exe" `
     -Action Allow -Protocol TCP -LocalPort 80
   ```

   Or manually:
   - Open **Windows Defender Firewall → Advanced Settings**
   - Click **Inbound Rules → New Rule**
   - Select **Program**
   - Choose `C:\xampp\apache\bin\apache.exe`
   - Allow the connection

### 2. **Check XAMPP Services**

   Make sure Apache and MySQL are running:

   ```powershell
   # Check if services are running
   Get-Service | Where-Object {$_.Name -like "*Apache*" -or $_.Name -like "*MySQL*"}
   ```

### 3. **Check Ollama Service**

   Make sure Ollama is running on your PC:

   ```powershell
   # Check if Ollama process is running
   Get-Process | Where-Object {$_.ProcessName -like "*ollama*"}
   ```

   If not running, start it:
   ```powershell
   cd "C:\Users\YourUsername\AppData\Local\Programs\Ollama"
   .\ollama.exe serve
   ```

---

## 🌐 Network Setup

### Make Sure Both Devices Are On Same WiFi
- **PC:** Connected to WiFi (your current network)
- **Phone:** Connected to **same WiFi network**

### Find Your PC's IP on This Network

The network is likely one of these:
- **192.168.1.0/24** (most common home routers)
- **192.168.0.0/24** (older routers)

Your PC is on: **192.168.1.12**

---

## 📱 On Your Phone

### 1. **Test Basic Connectivity**

Open your phone browser and visit:
```
http://192.168.1.12/puta/
```

Expected: You should see the **PNS Attendance System** homepage

### 2. **Test Login**

Try logging in with a student account:
```
URL: http://192.168.1.12/puta/login.php
```

### 3. **Test QR Code Scanning**

- Go to **scan.php**
- Point camera at a QR code
- Should scan and process successfully

### 4. **Test AI Features**

- Go to **Student Dashboard → Review Center**
- Ask Jether AI a question
- Should get response from AI (using Ollama on your PC)

---

## 🔗 Key URLs for Testing

| Feature | URL |
|---------|-----|
| Home | `http://192.168.1.12/puta/` |
| Login | `http://192.168.1.12/puta/login.php` |
| QR Scan | `http://192.168.1.12/puta/scan.php` |
| Student Dashboard | `http://192.168.1.12/puta/student_dashboard.php` |
| Review Center | `http://192.168.1.12/puta/review_center.php` |
| Teacher Dashboard | `http://192.168.1.12/puta/teacher_dashboard.php` |

---

## ⚠️ Troubleshooting

### "Can't reach the server"
- **Check WiFi:** Both PC and phone on same network?
- **Check Firewall:** Windows Firewall blocking Apache?
- **Check IP:** Run `ipconfig` on PC - is it still 192.168.1.12?

### "Page loads but looks broken"
- Check browser console (F12)
- CSS/JS files might need full URL paths
- Clear browser cache

### "AI not responding"
- Check if Ollama is running: `Get-Process ollama`
- Ollama needs to be accessible via `192.168.1.12:11434`
- Test directly: `curl http://192.168.1.12:11434/api/tags` on PC

### "QR code not scanning"
- Camera permission granted?
- QR code quality OK?
- Try test QR codes first

---

## 🚀 Starting Your First Test

### Step 1: Start Services (on PC)
```powershell
# Make sure XAMPP is running (Apache + MySQL)
# Make sure Ollama is running
```

### Step 2: Get Your Phone on WiFi
```
Connect to: [Your WiFi SSID]
Same network as your PC
```

### Step 3: Open Browser on Phone
```
Visit: http://192.168.1.12/puta/
```

### Step 4: Login and Test
```
Create account or use existing
Test Review Center → Ask Jether AI
Test QR Scanner → Scan a code
```

---

## 📊 Current Configuration

```
PC Network IP:     192.168.1.12
WiFi Network:      192.168.1.0/24
Ollama API:        http://192.168.1.12:11434
Project Path:      /puta
Server URL:        http://192.168.1.12/puta
```

---

## 💾 Files Updated

- ✅ `config.php` - SERVER_IP changed to 192.168.1.12
- ✅ `api_config.php` - Created with centralized Ollama endpoint
- ✅ `reviewer_ai.php` - Using OLLAMA_API_URL constant
- ✅ `pdf_to_csv_converter.php` - Using OLLAMA_API_URL constant
- ✅ `developer_ai_assistant.php` - Using OLLAMA_API_URL constant

---

## 🎯 What to Test

1. **Basic Navigation** - Can you navigate the site?
2. **Login System** - Does login work?
3. **QR Scanning** - Can you scan QR codes?
4. **AI Features** - Does Jether AI respond?
5. **PDF Upload** - Can you convert PDFs?
6. **CSV Data** - Does CSV extraction work?
7. **Conversations** - Are they saved?
8. **PDF Export** - Can you export conversations?

---

## 📞 Testing Status

Once you test, check:
- ✅ All basic features working
- ✅ AI responses loading correctly
- ✅ No firewall errors
- ✅ Phone can reach all pages
- ✅ QR scanning functional

Ready to test! 🎉
