# download_deped_modules.ps1
# Downloads Grade 11 & 12 modules from DepEd Puerto Princesa CLAS

$dataDir = "C:\xampp\htdocs\puta\data"

# Create data directory if it doesn't exist
if (!(Test-Path $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir | Out-Null
}

Write-Host "DepEd Module Downloader for Grade 11 & 12" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# You'll need to manually inspect https://depedpuertoprincesa.ph/clas/ to find the actual module links
# This is a template - replace the URLs below with actual module download links

# Example URLs (you need to replace these with actual links from the website):
$moduleUrls = @(
    # Grade 11 modules - ADD ACTUAL URLs HERE
    # "https://depedpuertoprincesa.ph/path/to/grade11_module1.pdf",
    # "https://depedpuertoprincesa.ph/path/to/grade11_module2.pdf",

    # Grade 12 modules - ADD ACTUAL URLs HERE
    # "https://depedpuertoprincesa.ph/path/to/grade12_module1.pdf",
    # "https://depedpuertoprincesa.ph/path/to/grade12_module2.pdf"
)

if ($moduleUrls.Count -eq 0) {
    Write-Host "ERROR: No module URLs configured!" -ForegroundColor Red
    Write-Host ""
    Write-Host "INSTRUCTIONS:" -ForegroundColor Yellow
    Write-Host "1. Visit https://depedpuertoprincesa.ph/clas/" -ForegroundColor Yellow
    Write-Host "2. Find the Grade 11 and 12 module download links" -ForegroundColor Yellow
    Write-Host "3. Right-click each PDF link and copy the link address" -ForegroundColor Yellow
    Write-Host "4. Edit this script and add URLs to the `$moduleUrls array" -ForegroundColor Yellow
    Write-Host "5. Run this script again" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Opening the website in your browser..." -ForegroundColor Cyan
    Start-Process "https://depedpuertoprincesa.ph/clas/"
    exit
}

$downloaded = 0
$failed = 0

foreach ($url in $moduleUrls) {
    try {
        $fileName = [System.IO.Path]::GetFileName($url)
        $filePath = Join-Path $dataDir $fileName

        Write-Host "Downloading: $fileName" -ForegroundColor Cyan
        Invoke-WebRequest -Uri $url -OutFile $filePath -TimeoutSec 60

        if (Test-Path $filePath) {
            $sizeKB = [math]::Round((Get-Item $filePath).Length / 1KB, 2)
            Write-Host "  ✓ Saved: $filePath ($sizeKB KB)" -ForegroundColor Green
            $downloaded++
        }
    }
    catch {
        Write-Host "  ✗ Failed: $fileName - $($_.Exception.Message)" -ForegroundColor Red
        $failed++
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "Download Summary:" -ForegroundColor Green
Write-Host "  Downloaded: $downloaded" -ForegroundColor Green
Write-Host "  Failed: $failed" -ForegroundColor $(if ($failed -gt 0) { "Red" } else { "Green" })
Write-Host "  Location: $dataDir" -ForegroundColor Cyan
Write-Host ""
