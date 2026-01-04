# Quick Setup Script for GitHub Deployment

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "BattleMaster Update Server" -ForegroundColor Cyan
Write-Host "GitHub Setup Assistant" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Check if .env exists
if (Test-Path ".env") {
    Write-Host "✓ .env file found" -ForegroundColor Green
} else {
    Write-Host "⚠ .env file not found. Creating from template..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "✓ Created .env file" -ForegroundColor Green
    Write-Host ""
    Write-Host "IMPORTANT: Edit .env and update:" -ForegroundColor Yellow
    Write-Host "  - GITHUB_USERNAME" -ForegroundColor White
    Write-Host "  - AUTH_TOKEN" -ForegroundColor White
    Write-Host "  - ADMIN_PASSWORD" -ForegroundColor White
    Write-Host ""
    Read-Host "Press Enter after updating .env file"
}

# Load environment variables from .env
Write-Host ""
Write-Host "Loading configuration..." -ForegroundColor Yellow

$envContent = Get-Content ".env"
$githubUsername = ""
$githubRepo = "battlemaster-update-server"

foreach ($line in $envContent) {
    if ($line -match "^GITHUB_USERNAME=(.+)") {
        $githubUsername = $matches[1]
    }
    if ($line -match "^GITHUB_REPO=(.+)") {
        $githubRepo = $matches[1]
    }
}

if ($githubUsername -eq "" -or $githubUsername -eq "your-github-username") {
    Write-Host "✗ Please set GITHUB_USERNAME in .env file" -ForegroundColor Red
    exit
}

Write-Host "✓ Configuration loaded" -ForegroundColor Green
Write-Host "  GitHub Username: $githubUsername" -ForegroundColor White
Write-Host "  Repository: $githubRepo" -ForegroundColor White
Write-Host ""

# Update URLs in JSON files
Write-Host "Updating GitHub URLs in JSON files..." -ForegroundColor Yellow
.\update-github-urls.ps1 -GitHubUsername $githubUsername -RepoName $githubRepo

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "Git Repository Setup" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Check if git is initialized
if (Test-Path ".git") {
    Write-Host "✓ Git repository already initialized" -ForegroundColor Green
} else {
    Write-Host "Initializing Git repository..." -ForegroundColor Yellow
    git init
    Write-Host "✓ Git initialized" -ForegroundColor Green
}

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Create GitHub repository:" -ForegroundColor White
Write-Host "   https://github.com/new" -ForegroundColor Cyan
Write-Host "   Name: $githubRepo" -ForegroundColor White
Write-Host "   Visibility: Public" -ForegroundColor White
Write-Host ""
Write-Host "2. Add files and commit:" -ForegroundColor White
Write-Host "   git add ." -ForegroundColor Cyan
Write-Host "   git commit -m 'Initial commit - BattleMaster Update Server'" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Add remote and push:" -ForegroundColor White
Write-Host "   git remote add origin https://github.com/$githubUsername/$githubRepo.git" -ForegroundColor Cyan
Write-Host "   git branch -M main" -ForegroundColor Cyan
Write-Host "   git push -u origin main" -ForegroundColor Cyan
Write-Host ""
Write-Host "4. Verify updates are accessible:" -ForegroundColor White
Write-Host "   https://raw.githubusercontent.com/$githubUsername/$githubRepo/main/version.json" -ForegroundColor Cyan
Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "Security Checklist" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Files that will NOT be pushed (protected):" -ForegroundColor Green
Write-Host "  ✓ admin.php (admin panel)" -ForegroundColor White
Write-Host "  ✓ config.json (sensitive config)" -ForegroundColor White
Write-Host "  ✓ .env (environment variables)" -ForegroundColor White
Write-Host "  ✓ debug-*.php (debug scripts)" -ForegroundColor White
Write-Host "  ✓ test-*.php (test scripts)" -ForegroundColor White
Write-Host ""
Write-Host "Files that WILL be pushed (public):" -ForegroundColor Yellow
Write-Host "  • index-github.php (safe API)" -ForegroundColor White
Write-Host "  • version.json (version data)" -ForegroundColor White
Write-Host "  • updates/*.json (metadata)" -ForegroundColor White
Write-Host "  • README files" -ForegroundColor White
Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "Ready to Deploy!" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
