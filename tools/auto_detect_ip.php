<?php
// auto_detect_ip.php - Auto-detect and update SERVER_IP in config.php based on current LAN IP
// Usage: php tools/auto_detect_ip.php
// Can be run periodically (e.g., via cron or Windows Task Scheduler) to keep SERVER_IP current

// Prevent this utility from being executed via the web to avoid remote command execution
if (PHP_SAPI !== 'cli') {
    echo "This script is CLI-only. Do not run it via a web request.\n";
    exit(1);
}

function get_lan_ip() {
    // Method 1: Try to get the primary IPv4 from ipconfig (Windows)
    if (PHP_OS_FAMILY === 'Windows') {
        $output = shell_exec('ipconfig 2>&1');
        if (preg_match('/IPv4 Address[\.\s]+: ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $output, $matches)) {
            return $matches[1];
        }
    } else {
        // Method 2: Linux/Mac - use hostname -I or ifconfig
        $output = shell_exec('hostname -I 2>/dev/null || ifconfig 2>/dev/null | grep -oP "(?<=inet\s)\d+\.\d+\.\d+\.\d+" 2>/dev/null | head -1');
        $ip = trim($output);
        if (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return null;
}

function update_config_ip($new_ip) {
    $config_file = __DIR__ . '/../config.php';
    if (!file_exists($config_file)) {
        echo "Error: config.php not found at $config_file\n";
        return false;
    }

    $content = file_get_contents($config_file);
    
    // Pattern to match: define('SERVER_IP', getenv('SERVER_IP') ?: 'xxx.xxx.xxx.xxx');
    $pattern = "/define\('SERVER_IP',\s*getenv\('SERVER_IP'\)\s*\?:\s*'[0-9.]+'\)/";
    $replacement = "define('SERVER_IP', getenv('SERVER_IP') ?: '" . addslashes($new_ip) . "')";
    
    $new_content = preg_replace($pattern, $replacement, $content);
    
    if ($new_content === $content) {
        echo "Warning: Could not find SERVER_IP pattern in config.php. Trying alternative pattern...\n";
        // Try alternative without comment
        $pattern = "/define\('SERVER_IP',\s*getenv\('SERVER_IP'\)\s*\?:\s*'[0-9.]+'\);/";
        $new_content = preg_replace($pattern, $replacement . ';', $content);
    }
    
    if ($new_content === $content) {
        echo "Error: Could not update SERVER_IP in config.php\n";
        return false;
    }
    
    if (file_put_contents($config_file, $new_content) === false) {
        echo "Error: Could not write to config.php\n";
        return false;
    }
    
    return true;
}

// Main
echo "Auto-detecting LAN IP...\n";
$lan_ip = get_lan_ip();

if (empty($lan_ip)) {
    echo "Error: Could not detect LAN IP address.\n";
    exit(1);
}

echo "Detected LAN IP: $lan_ip\n";

if (update_config_ip($lan_ip)) {
    echo "✓ Successfully updated SERVER_IP to $lan_ip in config.php\n";
    exit(0);
} else {
    echo "✗ Failed to update SERVER_IP\n";
    exit(1);
}
?>
