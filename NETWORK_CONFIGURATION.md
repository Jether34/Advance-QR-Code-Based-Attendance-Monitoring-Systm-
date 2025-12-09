# Network Configuration Guide

## 🌐 Multi-Network Support

The QR Attendance System now supports **automatic network detection** and configuration for multiple network environments.

---

## 📡 Supported Networks

### Network A (Original Configuration)
- **IP Range:** `192.168.1.0/24`
- **Server IP:** `192.168.1.12`
- **Ollama Endpoint:** `http://192.168.1.12:11434`
- **Usage:** Primary school network

### Network B (New Configuration)
- **IP Range:** `192.168.254.0/24`
- **Server IP:** `192.168.254.254`
- **Ollama Endpoint:** `http://192.168.254.254:11434`
- **Usage:** Alternative school network / Mobile hotspot

### Localhost (Development)
- **IP:** `127.0.0.1`
- **Ollama Endpoint:** `http://localhost:11434`
- **Usage:** Local development and testing

---

## ⚙️ Automatic Network Detection

The system automatically detects which network it's connected to and configures endpoints accordingly:

```php
// In api_config.php
if (strpos($server_ip, '192.168.254.') === 0) {
    // Network B: 192.168.254.x
    define('OLLAMA_API_URL', 'http://192.168.254.254:11434/api/generate');
} elseif (strpos($server_ip, '192.168.1.') === 0) {
    // Network A: 192.168.1.x
    define('OLLAMA_API_URL', 'http://192.168.1.12:11434/api/generate');
} else {
    // Localhost fallback
    define('OLLAMA_API_URL', 'http://localhost:11434/api/generate');
}
```

---

## 🔧 Setup Instructions

### For Network A (192.168.1.x)

1. **Connect PC to Network A**
   - Router IP: `192.168.1.1`
   - Server IP: `192.168.1.12` (static or DHCP reservation)

2. **Start Services**
   ```powershell
   # Start XAMPP
   C:\xampp\xampp-control.exe
   
   # Start Ollama
   ollama serve
   ```

3. **Access URLs**
   - Web Interface: `http://192.168.1.12/puta`
   - Ollama API: `http://192.168.1.12:11434`

4. **Mobile Devices**
   - Connect to same network
   - Access: `http://192.168.1.12/puta`

---

### For Network B (192.168.254.x)

1. **Connect PC to Network B**
   - Router IP: `192.168.254.1`
   - Server IP: `192.168.254.254` (static or DHCP reservation)

2. **Configure Static IP (Windows)**
   ```powershell
   # Open Network Settings
   ncpa.cpl
   
   # Or use PowerShell to set static IP
   New-NetIPAddress -InterfaceAlias "Ethernet" -IPAddress 192.168.254.254 -PrefixLength 24 -DefaultGateway 192.168.254.1
   Set-DnsClientServerAddress -InterfaceAlias "Ethernet" -ServerAddresses ("8.8.8.8","8.8.4.4")
   ```

3. **Start Services**
   ```powershell
   # Start XAMPP
   C:\xampp\xampp-control.exe
   
   # Start Ollama (will bind to 192.168.254.254)
   ollama serve
   ```

4. **Access URLs**
   - Web Interface: `http://192.168.254.254/puta`
   - Ollama API: `http://192.168.254.254:11434`

5. **Mobile Devices**
   - Connect to same network
   - Access: `http://192.168.254.254/puta`

---

## 🧪 Testing Network Configuration

### Test Endpoint
Visit: `http://[SERVER-IP]/puta/test_network_setup.php`

**Example Outputs:**

**Network A:**
```json
{
  "timestamp": "2025-12-09 14:30:00",
  "server_ip": "192.168.1.12",
  "network_detected": "192.168.1.x (Network A - Original)",
  "ollama_endpoint": "http://192.168.1.12:11434",
  "tests": {
    "database": "CONNECTED",
    "ollama_local": "RUNNING"
  }
}
```

**Network B:**
```json
{
  "timestamp": "2025-12-09 14:30:00",
  "server_ip": "192.168.254.254",
  "network_detected": "192.168.254.x (Network B)",
  "ollama_endpoint": "http://192.168.254.254:11434",
  "tests": {
    "database": "CONNECTED",
    "ollama_local": "RUNNING"
  }
}
```

---

## 🔥 Firewall Configuration

Allow incoming connections on both networks:

```powershell
# Allow Apache (HTTP)
New-NetFirewallRule -DisplayName "XAMPP Apache - Network A" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow -RemoteAddress 192.168.1.0/24
New-NetFirewallRule -DisplayName "XAMPP Apache - Network B" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow -RemoteAddress 192.168.254.0/24

# Allow MySQL
New-NetFirewallRule -DisplayName "XAMPP MySQL - Network A" -Direction Inbound -LocalPort 3306 -Protocol TCP -Action Allow -RemoteAddress 192.168.1.0/24
New-NetFirewallRule -DisplayName "XAMPP MySQL - Network B" -Direction Inbound -LocalPort 3306 -Protocol TCP -Action Allow -RemoteAddress 192.168.254.0/24

# Allow Ollama
New-NetFirewallRule -DisplayName "Ollama AI - Network A" -Direction Inbound -LocalPort 11434 -Protocol TCP -Action Allow -RemoteAddress 192.168.1.0/24
New-NetFirewallRule -DisplayName "Ollama AI - Network B" -Direction Inbound -LocalPort 11434 -Protocol TCP -Action Allow -RemoteAddress 192.168.254.0/24
```

---

## 🔍 Troubleshooting

### Issue: "Cannot connect to server"

**Check Network:**
```powershell
ipconfig
```
Verify your PC has the correct IP (192.168.1.12 or 192.168.254.254)

**Check Services:**
```powershell
# Check Apache
netstat -an | findstr ":80"

# Check MySQL
netstat -an | findstr ":3306"

# Check Ollama
netstat -an | findstr ":11434"
```

### Issue: "Ollama not responding"

**Restart Ollama:**
```powershell
# Kill existing process
taskkill /F /IM ollama.exe

# Start fresh
ollama serve
```

**Check Ollama Status:**
```powershell
# From PC
curl http://localhost:11434/api/tags

# From mobile device (Network A)
curl http://192.168.1.12:11434/api/tags

# From mobile device (Network B)
curl http://192.168.254.254:11434/api/tags
```

### Issue: "Wrong Ollama endpoint detected"

The system auto-detects based on `$_SERVER['SERVER_ADDR']`.

**Manual Override (if needed):**
Edit `api_config.php`:
```php
// Force specific endpoint (for testing)
define('OLLAMA_API_URL', 'http://192.168.254.254:11434/api/generate');
```

---

## 📱 Mobile Device Setup

### Android/iOS Devices

1. **Connect to WiFi**
   - Network A: Connect to school WiFi (192.168.1.x range)
   - Network B: Connect to alternative network (192.168.254.x range)

2. **Open Browser**
   - Network A: `http://192.168.1.12/puta`
   - Network B: `http://192.168.254.254/puta`

3. **Login**
   - Student: Use LRN and password
   - Teacher: Use email and password

4. **Test AI Features**
   - Review Center should work automatically
   - AI Assistant should respond
   - PDF Converter should process files

---

## 🔄 Switching Between Networks

The system **automatically adapts** when you switch networks:

1. **Disconnect from Network A**
2. **Connect to Network B**
3. **Restart XAMPP** (Apache will bind to new IP)
4. **Restart Ollama** (will bind to new IP)
5. **Access via new IP:** `http://192.168.254.254/puta`

No code changes needed! 🎉

---

## 📊 Network Comparison

| Feature | Network A (192.168.1.x) | Network B (192.168.254.x) |
|---------|-------------------------|---------------------------|
| Server IP | 192.168.1.12 | 192.168.254.254 |
| Ollama Endpoint | :11434 | :11434 |
| Auto-Detection | ✅ Yes | ✅ Yes |
| Mobile Support | ✅ Yes | ✅ Yes |
| AI Features | ✅ Full | ✅ Full |
| Attendance | ✅ Full | ✅ Full |

---

## 🚀 Production Deployment

### Recommended: Network B (192.168.254.254)

**Advantages:**
- Higher IP address (less common conflicts)
- Easier to remember (.254.254)
- Can coexist with existing school network

**Setup Checklist:**
- [ ] Configure static IP: 192.168.254.254
- [ ] Set router gateway: 192.168.254.1
- [ ] Configure DNS: 8.8.8.8, 8.8.4.4
- [ ] Enable firewall rules
- [ ] Start XAMPP services
- [ ] Start Ollama service
- [ ] Test from mobile device
- [ ] Verify AI features working

---

## 📝 Configuration Files

### Modified Files:
- ✅ `api_config.php` - Auto-detection logic added
- ✅ `test_network_setup.php` - Network detection reporting

### No Changes Needed:
- `db.php` - Database uses localhost
- `config.php` - Session configuration unchanged
- PHP scripts - Use `OLLAMA_API_URL` constant

---

## 🎯 Quick Reference

### Network A URLs:
```
System:    http://192.168.1.12/puta
Ollama:    http://192.168.1.12:11434
Test:      http://192.168.1.12/puta/test_network_setup.php
```

### Network B URLs:
```
System:    http://192.168.254.254/puta
Ollama:    http://192.168.254.254:11434
Test:      http://192.168.254.254/puta/test_network_setup.php
```

---

## 📞 Support

For network configuration issues, check:
1. Test endpoint: `/puta/test_network_setup.php`
2. Server IP: `ipconfig` in PowerShell
3. Firewall rules: Windows Defender Firewall
4. Service status: XAMPP Control Panel

---

**Last Updated:** December 9, 2025  
**System Version:** 2025.12  
**Documentation:** Network Configuration Guide
