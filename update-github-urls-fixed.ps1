# GitHub URL Updater for BattleMaster Update Server
# This script updates all localhost URLs to GitHub URLs

param(
    [Parameter(Mandatory=$true)]
    [string]$GitHubUsername,
    
    [Parameter(Mandatory=$false)]
    [string]$RepoName = "battlemaster-update-server",
    
    [Parameter(Mandatory=$false)]
    [string]$Branch = "main"
)

$baseUrl = "https://raw.githubusercontent.com/$GitHubUsername/$RepoName/$Branch"

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "GitHub URL Updater" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "GitHub Base URL: $baseUrl" -ForegroundColor Green
Write-Host ""

# Update version.json
$versionFile = "version.json"
if (Test-Path $versionFile) {
    Write-Host "Updating $versionFile..." -ForegroundColor Yellow
    
    $content = Get-Content $versionFile -Raw
    
    # Replace localhost URLs with GitHub URLs
    $content = $content -replace 'http://localhost/battlemaster-update-server', $baseUrl
    $content = $content -replace 'http://127.0.0.1/battlemaster-update-server', $baseUrl
    $content = $content -replace 'http://localhost:8000', $baseUrl
    
    # Save updated content
    $content | Set-Content $versionFile -NoNewline
    
    Write-Host "✓ Updated $versionFile" -ForegroundColor Green
} else {
    Write-Host "✗ $versionFile not found" -ForegroundColor Red
}

# Update all JSON files in updates directory
$updatesDir = "updates"
if (Test-Path $updatesDir) {
    $jsonFiles = Get-ChildItem -Path $updatesDir -Filter "*.json"
    
    Write-Host ""
    Write-Host "Updating JSON files in $updatesDir..." -ForegroundColor Yellow
    
    foreach ($file in $jsonFiles) {
        $content = Get-Content $file.FullName -Raw
        
        # Replace localhost URLs with GitHub URLs
        $content = $content -replace 'http://localhost/battlemaster-update-server', $baseUrl
        $content = $content -replace 'http://127.0.0.1/battlemaster-update-server', $baseUrl
        $content = $content -replace 'http://localhost:8000', $baseUrl
        
        # Save updated content
        $content | Set-Content $file.FullName -NoNewline
        
        Write-Host "  ✓ Updated $($file.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Update Complete!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Review the updated URLs in version.json and updates/*.json"
Write-Host "2. Commit and push to GitHub:"
Write-Host "   git add ." -ForegroundColor Cyan
Write-Host "   git commit -m 'Update URLs to GitHub'" -ForegroundColor Cyan
Write-Host "   git push origin main" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Test by accessing:" -ForegroundColor Yellow
Write-Host "   $baseUrl/version.json" -ForegroundColor Cyan
Write-Host ""
