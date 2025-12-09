# Oracle Cloud VM Creation - Step-by-Step Guide

## Prerequisites
- Oracle Cloud free account (create at https://www.oracle.com/cloud/free/)
- Email verified
- Phone verified
- Payment method on file (won't be charged)

---

## Step 1: Set Spending Limit to $0 (IMPORTANT!)

This prevents accidental charges if you exceed free tier.

1. Log into Oracle Cloud Console
2. Click your profile icon (top right)
3. Select **"Billing & Cost Management"**
4. Click **"Cost Management"** on left sidebar
5. Click **"Spending Limits"**
6. Toggle **"Enable Spending Limit"** to ON
7. Set limit to **$0.00**
8. Click **"Save"**

---

## Step 2: Navigate to Compute

1. Log into Oracle Cloud Console (https://www.oracle.com/cloud/sign-in/)
2. In the top search bar, search for **"Compute"** or **"Instances"**
3. Click **"Instances"** under Compute

---

## Step 3: Create Instance

### 3a. Click "Create Instance" Button
- Orange button on the Instances page

### 3b. Configure Basic Settings

**Name:**
```
tapin-server
```

**Image:** (Click "Change Image")
1. Select **"Canonical Ubuntu"**
2. Choose **"Ubuntu 22.04"** (free tier eligible)
3. Click **"Select Image"**

**Image Build:**
- Keep default latest

---

## Step 4: Configure Shape (CPU & RAM)

### 4a. Click "Change Shape"

**Shape Series:** Select **"Ampere (ARM)"**
- This is the free tier eligible option

**Shape Name:** Select **"VM.Standard.A1.Flex"**
- Always free tier
- Supports up to 4 OCPUs and 24GB RAM

### 4b. Set Resources

**OCPUs:** Set to **2** (free tier allows up to 4)

**Memory (GB):** Set to **12** (free tier allows up to 24GB)

**Network Bandwidth (Gbps):** Keep default

**GPU:** None

Click **"Save Shape"**

---

## Step 5: Configure Networking

### 5a. Virtual Cloud Network (VCN)
- Select **"Create new virtual cloud network"**
- Name: `tapin-vcn` (or auto-generated is fine)

### 5b. Subnet
- Select **"Create new subnet"**
- Name: `tapin-subnet` (or auto-generated is fine)
- Defaults are fine

### 5c. Public IP Address
- **VERY IMPORTANT:** Select **"Assign a public IPv4 address"**
- You need this to access the web server from the internet

---

## Step 6: Add SSH Key (CRITICAL!)

### 6a. SSH Key Options

**Option 1: Generate Key Pair (Recommended)**
1. Select **"Generate a key pair for me"**
2. Two files will be available to download:
   - `tapin-server_00.key` (private key - save securely!)
   - `tapin-server_00.pub` (public key)
3. Click **"Download private key"** - save it somewhere safe!

**Option 2: Use Existing Key (if you have one)**
1. Select **"Paste public key"**
2. Paste your public key (`id_rsa.pub` content)

### 6b. Save the Private Key Securely
```
Windows: C:\Users\YourName\.ssh\tapin-server_00.key
Mac/Linux: ~/.ssh/tapin-server_00.key
```

**IMPORTANT:** If you lose this key, you cannot access the VM!

---

## Step 7: Storage Configuration (Optional)

**Boot Volume:**
- Size: 50 GB (free tier allows this)
- Keep defaults

---

## Step 8: Review & Create

1. Scroll to bottom
2. Review all settings:
   - Image: Ubuntu 22.04 ✅
   - Shape: VM.Standard.A1.Flex ✅
   - OCPUs: 2 ✅
   - RAM: 12 GB ✅
   - Public IP: Assigned ✅
   - SSH Key: Selected/Generated ✅

3. Click **"Create"** (orange button)

---

## Step 9: Wait for VM to Start

1. You'll see a provisioning screen
2. Status will show: **"PROVISIONING"** → **"STARTING"** → **"RUNNING"**
3. Wait 2-3 minutes for VM to fully boot
4. Once status is **"RUNNING"** in green, note the **Public IP Address**

Example:
```
Public IP: 150.230.45.123
```

---

## Step 10: Get SSH Connection Details

Once VM is running:

1. Find the **"Public IP Address"** displayed on the instance details
2. Username: **`ubuntu`** (always this for Ubuntu images)
3. Private Key File: The `.key` file you downloaded in Step 6

---

## Step 11: Connect via SSH

### Windows (PowerShell):

```powershell
# Set proper permissions on key file
icacls "C:\Users\YourName\.ssh\tapin-server_00.key" /inheritance:r /grant:r "$env:USERNAME:(R)"

# Connect to VM
ssh -i "C:\Users\YourName\.ssh\tapin-server_00.key" ubuntu@150.230.45.123
```

Replace `150.230.45.123` with YOUR actual public IP!

### Mac/Linux (Terminal):

```bash
# Set proper permissions
chmod 600 ~/.ssh/tapin-server_00.key

# Connect to VM
ssh -i ~/.ssh/tapin-server_00.key ubuntu@150.230.45.123
```

---

## Step 12: Verify SSH Connection

Once you SSH in, you should see:

```
Welcome to Ubuntu 22.04.1 LTS (GNU/Linux 5.15.0-1234-oracle aarch64)

ubuntu@tapin-server:~$
```

You're now on the Oracle Cloud VM! ✅

---

## Next Step: Run Deployment Script

Once SSH is connected, run:

```bash
cd /tmp
wget https://raw.githubusercontent.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-/update-2025-11-dev-qr/quick_oracle_deploy.sh
chmod +x quick_oracle_deploy.sh
./quick_oracle_deploy.sh
```

This will automatically install everything (Apache, PHP, MySQL, Ollama, TapIn).

---

## Troubleshooting

### "Connection refused" or "Permission denied"
- Check SSH key file has proper permissions: `icacls "key.key" /inheritance:r /grant:r "$env:USERNAME:(R)"`
- Verify IP address is correct
- Wait a bit longer for VM to fully boot (might take 3-5 min)

### "No public IP assigned"
- Edit instance
- Click "Attached VNICs"
- Make sure "Assign public IPv4 address" is enabled

### "Compute limit exceeded"
- Free tier allows 2 instances
- Delete an old instance or request a limit increase

### Lost SSH Key
- You can't recover it
- Delete the instance and create a new one

---

## Important Notes

✅ **Free Tier:**
- 2 Always Free instances (you're using 1)
- 12 GB RAM per instance
- 2 OCPUs per instance
- No cost

⚠️ **Keep Account Active:**
- Log in at least once every 30 days
- Account will be suspended if inactive

✅ **Security:**
- Spending limit already set to $0
- SSH key protects your VM
- Never share private SSH key

---

## Summary

| Item | Value |
|------|-------|
| **Image** | Ubuntu 22.04 |
| **Shape** | VM.Standard.A1.Flex |
| **OCPUs** | 2 |
| **RAM** | 12 GB |
| **Storage** | 50 GB |
| **Public IP** | Assigned |
| **Cost** | FREE |
| **Time to Create** | 3-5 minutes |

Once VM is running and you SSH in, you're ready to run the deployment script!

Need help? Let me know!
