# extract_module_links.ps1
# Helper script to extract module links from browser

Write-Host "=== DepEd Module Link Extractor ===" -ForegroundColor Green
Write-Host ""
Write-Host "INSTRUCTIONS:" -ForegroundColor Yellow
Write-Host "1. Open https://depedpuertoprincesa.ph/clas/ in your browser" -ForegroundColor White
Write-Host "2. Press F12 to open Developer Tools" -ForegroundColor White
Write-Host "3. Navigate to Grade 11 and 12 module sections" -ForegroundColor White
Write-Host "4. In Console tab, paste this JavaScript:" -ForegroundColor White
Write-Host ""
Write-Host @"
// Extract all PDF links
const links = Array.from(document.querySelectorAll('a[href*=".pdf"]'))
    .map(a => a.href)
    .filter(href => href.includes('grade-11') || href.includes('grade-12') ||
                    href.includes('Grade 11') || href.includes('Grade 12'));
console.log(links.join('\n'));
copy(links.join('\n'));
"@ -ForegroundColor Cyan
Write-Host ""
Write-Host "5. Links will be copied to clipboard automatically" -ForegroundColor White
Write-Host "6. Paste them into a text file: modules.txt" -ForegroundColor White
Write-Host "7. Run: .\download_from_list.ps1 modules.txt" -ForegroundColor White
Write-Host ""
Write-Host "Opening browser..." -ForegroundColor Green
Start-Process "https://depedpuertoprincesa.ph/clas/"
