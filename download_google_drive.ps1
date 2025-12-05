# download_google_drive.ps1
# Downloads files from Google Drive sharing links

param(
    [Parameter(Mandatory=$false)]
    [string]$driveUrl,
    [Parameter(Mandatory=$false)]
    [string]$outputName
)

$dataDir = "C:\xampp\htdocs\puta\data"

if (!(Test-Path $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir | Out-Null
}

function Get-GoogleDriveFileId {
    param([string]$url)
    
    if ($url -match '/d/([a-zA-Z0-9_-]+)') {
        return $matches[1]
    }
    elseif ($url -match '[?&]id=([a-zA-Z0-9_-]+)') {
        return $matches[1]
    }
    return $null
}

function Download-GoogleDriveFile {
    param(
        [string]$fileId,
        [string]$outputPath
    )
    
    $directUrl = "https://drive.google.com/uc?export=download&id=$fileId"
    
    Write-Host "Downloading Google Drive file: $fileId" -ForegroundColor Cyan
    
    try {
        # First attempt - small files work directly
        $response = Invoke-WebRequest -Uri $directUrl -SessionVariable session -UseBasicParsing
        
        # Check if we got a confirmation page (for large files)
        if ($response.Content -match 'download_warning.*?href="(.*?)"') {
            $confirmUrl = "https://drive.google.com" + ($matches[1] -replace '&amp;', '&')
            Write-Host "  Large file detected, getting confirmation..." -ForegroundColor Yellow
            Invoke-WebRequest -Uri $confirmUrl -WebSession $session -OutFile $outputPath -UseBasicParsing
        }
        else {
            # Direct download worked
            $response.Content | Set-Content -Path $outputPath -Encoding Byte
        }
        
        if (Test-Path $outputPath) {
            $sizeKB = [math]::Round((Get-Item $outputPath).Length / 1KB, 2)
            Write-Host "  ✓ Downloaded successfully ($sizeKB KB)" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "  ✗ Download failed: $($_.Exception.Message)" -ForegroundColor Red
        
        Write-Host ""
        Write-Host "ALTERNATIVE METHOD:" -ForegroundColor Yellow
        Write-Host "1. Open this link in browser: https://drive.google.com/file/d/$fileId/view" -ForegroundColor White
        Write-Host "2. Click 'Download' button" -ForegroundColor White
        Write-Host "3. Save to: $dataDir" -ForegroundColor White
        
        return $false
    }
}

# Example usage
if ($driveUrl) {
    $fileId = Get-GoogleDriveFileId -url $driveUrl
    
    if (!$fileId) {
        Write-Host "ERROR: Could not extract file ID from URL" -ForegroundColor Red
        Write-Host "URL format should be: https://drive.google.com/file/d/FILE_ID/view" -ForegroundColor Yellow
        exit
    }
    
    if (!$outputName) {
        $outputName = "google_drive_file_$fileId.pdf"
    }
    
    $outputPath = Join-Path $dataDir $outputName
    Download-GoogleDriveFile -fileId $fileId -outputPath $outputPath
}
else {
    Write-Host "=== Google Drive File Downloader ===" -ForegroundColor Green
    Write-Host ""
    Write-Host "USAGE:" -ForegroundColor Yellow
    Write-Host "  .\download_google_drive.ps1 -driveUrl 'GOOGLE_DRIVE_LINK' -outputName 'filename.pdf'" -ForegroundColor White
    Write-Host ""
    Write-Host "EXAMPLE:" -ForegroundColor Yellow
    Write-Host "  .\download_google_drive.ps1 -driveUrl 'https://drive.google.com/file/d/1Av5a__ExEhklNe-YNbM9UxfBltpBbYO-/view' -outputName 'module.pdf'" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Trying your example now..." -ForegroundColor Cyan
    Write-Host ""
    
    # Try the example URL
    $exampleId = "1Av5a__ExEhklNe-YNbM9UxfBltpBbYO-"
    $exampleOutput = Join-Path $dataDir "downloaded_module.pdf"
    Download-GoogleDriveFile -fileId $exampleId -outputPath $exampleOutput
}
