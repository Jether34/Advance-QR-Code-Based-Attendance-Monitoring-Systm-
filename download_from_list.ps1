# download_from_list.ps1
# Downloads modules from a text file list of URLs

param(
    [Parameter(Mandatory=$false)]
    [string]$listFile = "modules.txt"
)

$dataDir = "C:\xampp\htdocs\puta\data"

if (!(Test-Path $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir | Out-Null
}

if (!(Test-Path $listFile)) {
    Write-Host "ERROR: $listFile not found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Create $listFile with one URL per line, example:" -ForegroundColor Yellow
    Write-Host "https://example.com/grade11_math.pdf" -ForegroundColor Gray
    Write-Host "https://example.com/grade12_science.pdf" -ForegroundColor Gray
    exit
}

$urls = Get-Content $listFile | Where-Object { $_.Trim() -ne "" -and $_.StartsWith("http") }

if ($urls.Count -eq 0) {
    Write-Host "ERROR: No valid URLs found in $listFile" -ForegroundColor Red
    exit
}

Write-Host "=== DepEd Module Downloader ===" -ForegroundColor Green
Write-Host "Found $($urls.Count) module(s) to download" -ForegroundColor Cyan
Write-Host ""

$downloaded = 0
$failed = 0

foreach ($url in $urls) {
    try {
        $fileName = [System.IO.Path]::GetFileName($url.Split('?')[0])
        $fileName = $fileName -replace '[^\w\-\.]', '_'
        $filePath = Join-Path $dataDir $fileName
        
        Write-Host "Downloading: $fileName" -ForegroundColor Cyan
        Invoke-WebRequest -Uri $url -OutFile $filePath -TimeoutSec 120 -UserAgent "Mozilla/5.0"
        
        if (Test-Path $filePath) {
            $sizeKB = [math]::Round((Get-Item $filePath).Length / 1KB, 2)
            Write-Host "  ✓ Saved ($sizeKB KB)" -ForegroundColor Green
            $downloaded++
        }
    }
    catch {
        Write-Host "  ✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
        $failed++
    }
    
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "==============================" -ForegroundColor Green
Write-Host "Downloaded: $downloaded" -ForegroundColor Green
Write-Host "Failed: $failed" -ForegroundColor $(if ($failed -gt 0) { "Red" } else { "Green" })
Write-Host "Location: $dataDir" -ForegroundColor Cyan
