param(
    [int]$Port = 80,
    [string]$HostHeader = "localhost"
)

# start_ngrok.ps1 - Launches an ngrok HTTP tunnel for local PWA/iOS testing
# Usage: powershell -ExecutionPolicy Bypass -File tools/start_ngrok.ps1
# If ngrok is missing, this script will auto-download the Windows amd64 binary locally.

$ErrorActionPreference = "Stop"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$localNgrok = Join-Path $scriptDir "ngrok.exe"

function Get-NgrokPath {
    $ng = Get-Command ngrok -ErrorAction SilentlyContinue
    if ($ng) { return $ng.Path }
    if (Test-Path $localNgrok) { return $localNgrok }
    return $null
}

function Install-NgrokLocal {
    $zipUrl = "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip"
    $zipPath = Join-Path $env:TEMP "ngrok-win64.zip"
    Write-Host "Downloading ngrok (Windows amd64)..." -ForegroundColor Cyan
    Invoke-WebRequest -Uri $zipUrl -OutFile $zipPath
    Write-Host "Extracting..." -ForegroundColor Cyan
    Expand-Archive -LiteralPath $zipPath -DestinationPath $scriptDir -Force
    Remove-Item $zipPath -Force
    if (-not (Test-Path $localNgrok)) {
        Write-Host "Failed to place ngrok.exe" -ForegroundColor Red
        exit 1
    }
    Write-Host "ngrok installed locally at $localNgrok" -ForegroundColor Green
    return $localNgrok
}

$ngrokPath = Get-NgrokPath
if (-not $ngrokPath) {
    $ngrokPath = Install-NgrokLocal
}

# Configure authtoken if provided via environment variable (prevents ERR_NGROK_4018)
if ($env:NGROK_AUTHTOKEN) {
    Write-Host "Applying NGROK_AUTHTOKEN from environment..." -ForegroundColor Cyan
    & $ngrokPath 'config' 'add-authtoken' $env:NGROK_AUTHTOKEN | Out-Null
}

Write-Host "Starting ngrok tunnel on http://localhost:$Port (Host header: $HostHeader) ..." -ForegroundColor Cyan
Write-Host "Using ngrok: $ngrokPath" -ForegroundColor DarkGray
$cmdParts = @($ngrokPath, 'http', $Port, '--host-header=' + $HostHeader)
Write-Host "Command: $($cmdParts -join ' ')" -ForegroundColor DarkGray
Write-Host "When ngrok starts, copy the HTTPS forwarding URL (e.g., https://<random>.ngrok.io) and open it in Safari on iOS, then Add to Home Screen." -ForegroundColor Green

# Start ngrok in the current console so the user can see the forwarding URL
& $ngrokPath 'http' $Port ('--host-header=' + $HostHeader)
