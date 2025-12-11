# Auto-detect and update SERVER_IP when network changes
# Usage: powershell -ExecutionPolicy Bypass -File tools/watch_network.ps1
# This script monitors network changes and auto-updates config.php

param(
    [int]$CheckIntervalSeconds = 30,
    [string]$PhpExe = "C:\xampp\php\php.exe"
)

function Get-CurrentLanIp {
    $output = ipconfig | Select-String "IPv4 Address" | Select-Object -Last 1
    if ($output -match '(\d+\.\d+\.\d+\.\d+)') {
        return $matches[1]
    }
    return $null
}

function Is-ValidIp {
    param($ip)
    return $ip -match '^(\d{1,3}\.){3}\d{1,3}$'
}

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$lastKnownIp = $null

Write-Host "Network Monitor Started - checking every $CheckIntervalSeconds seconds" -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop" -ForegroundColor Cyan

while ($true) {
    $currentIp = Get-CurrentLanIp

    if (Is-ValidIp $currentIp) {
        if ($currentIp -ne $lastKnownIp) {
            Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Network change detected: $lastKnownIp -> $currentIp" -ForegroundColor Yellow

            # Run auto_detect_ip.php
            $phpScript = Join-Path $scriptDir "auto_detect_ip.php"
            Write-Host "Updating config.php..." -ForegroundColor Cyan

            & $PhpExe $phpScript 2>&1

            if ($LASTEXITCODE -eq 0) {
                Write-Host "✓ Config updated successfully" -ForegroundColor Green
                $lastKnownIp = $currentIp
            } else {
                Write-Host "✗ Config update failed" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Could not detect valid IP" -ForegroundColor Red
    }

    Start-Sleep -Seconds $CheckIntervalSeconds
}
