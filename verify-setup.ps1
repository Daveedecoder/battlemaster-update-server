# Pre-Push Verification Script
# Run this before pushing to GitHub to ensure everything is configured correctly

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "GitHub Pre-Push Verification" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

$errors = 0
$warnings = 0

# Check 1: .env exists
Write-Host "Checking environment configuration..." -ForegroundColor Yellow
if (Test-Path ".env") {
    Write-Host "  ✓ .env file exists" -ForegroundColor Green
    
    # Read .env and check critical values
    $envContent = Get-Content ".env" -Raw
    
    if ($envContent -match "GITHUB_USERNAME=(.+)") {
        $username = $matches[1].Trim()
        if ($username -eq "your-github-username" -or $username -eq "") {
            Write-Host "  ✗ GITHUB_USERNAME not configured in .env" -ForegroundColor Red
            $errors++
        } else {
            Write-Host "  ✓ GITHUB_USERNAME: $username" -ForegroundColor Green
        }
    }
    
    if ($envContent -match "AUTH_TOKEN=(.+)") {
        $token = $matches[1].Trim()
        if ($token -eq "CHANGE_THIS_SECRET_TOKEN" -or $token.Length -lt 20) {
            Write-Host "  ⚠ AUTH_TOKEN looks weak or unchanged" -ForegroundColor Yellow
            $warnings++
        } else {
            Write-Host "  ✓ AUTH_TOKEN configured" -ForegroundColor Green
        }
    }
    
    if ($envContent -match "ADMIN_PASSWORD=(.+)") {
        $password = $matches[1].Trim()
        if ($password -eq "change_this_password" -or $password.Length -lt 8) {
            Write-Host "  ⚠ ADMIN_PASSWORD looks weak or unchanged" -ForegroundColor Yellow
            $warnings++
        } else {
            Write-Host "  ✓ ADMIN_PASSWORD configured" -ForegroundColor Green
        }
    }
    
} else {
    Write-Host "  ✗ .env file not found!" -ForegroundColor Red
    Write-Host "    Run: cp .env.example .env" -ForegroundColor Yellow
    $errors++
}

Write-Host ""

# Check 2: Sensitive files will NOT be pushed
Write-Host "Checking file protection..." -ForegroundColor Yellow
$sensitiveFiles = @("admin.php", "config.json", ".env", "index.php")
foreach ($file in $sensitiveFiles) {
    if (Test-Path $file) {
        $ignored = git check-ignore $file 2>$null
        if ($ignored) {
            Write-Host "  ✓ $file is protected (ignored)" -ForegroundColor Green
        } else {
            Write-Host "  ✗ $file will be pushed to GitHub!" -ForegroundColor Red
            $errors++
        }
    }
}

Write-Host ""

# Check 3: Required public files exist
Write-Host "Checking required files..." -ForegroundColor Yellow
$requiredFiles = @(
    "index-github.php",
    "version.json",
    ".gitignore",
    ".env.example",
    "README-GITHUB.md",
    "GITHUB_SETUP.md"
)

foreach ($file in $requiredFiles) {
    if (Test-Path $file) {
        Write-Host "  ✓ $file exists" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $file missing!" -ForegroundColor Red
        $errors++
    }
}

Write-Host ""

# Check 4: Git configuration
Write-Host "Checking Git configuration..." -ForegroundColor Yellow
if (Test-Path ".git") {
    Write-Host "  ✓ Git repository initialized" -ForegroundColor Green
    
    $gitUser = git config user.name 2>$null
    $gitEmail = git config user.email 2>$null
    
    if ($gitUser) {
        Write-Host "  ✓ Git user.name: $gitUser" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ Git user.name not set" -ForegroundColor Yellow
        Write-Host "    Run: git config --global user.name 'Your Name'" -ForegroundColor Gray
        $warnings++
    }
    
    if ($gitEmail) {
        Write-Host "  ✓ Git user.email: $gitEmail" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ Git user.email not set" -ForegroundColor Yellow
        Write-Host "    Run: git config --global user.email 'your@email.com'" -ForegroundColor Gray
        $warnings++
    }
    
} else {
    Write-Host "  ⚠ Git not initialized" -ForegroundColor Yellow
    Write-Host "    Run: git init" -ForegroundColor Gray
    $warnings++
}

Write-Host ""

# Check 5: URLs in version.json
Write-Host "Checking version.json URLs..." -ForegroundColor Yellow
if (Test-Path "version.json") {
    $versionContent = Get-Content "version.json" -Raw
    
    if ($versionContent -match "localhost") {
        Write-Host "  ⚠ version.json still contains 'localhost' URLs" -ForegroundColor Yellow
        Write-Host "    Run: .\update-github-urls.ps1 -GitHubUsername YOUR-USERNAME" -ForegroundColor Gray
        $warnings++
    } elseif ($versionContent -match "raw.githubusercontent.com") {
        Write-Host "  ✓ version.json uses GitHub URLs" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ Cannot determine URL type in version.json" -ForegroundColor Yellow
        $warnings++
    }
} else {
    Write-Host "  ✗ version.json not found!" -ForegroundColor Red
    $errors++
}

Write-Host ""

# Check 6: Updates directory
Write-Host "Checking updates directory..." -ForegroundColor Yellow
if (Test-Path "updates") {
    $jsonFiles = (Get-ChildItem "updates" -Filter "*.json").Count
    $zipFiles = (Get-ChildItem "updates" -Filter "*.zip").Count
    
    Write-Host "  ✓ Updates directory exists" -ForegroundColor Green
    Write-Host "    - $jsonFiles JSON metadata files" -ForegroundColor Gray
    Write-Host "    - $zipFiles ZIP update packages" -ForegroundColor Gray
    
    if ($zipFiles -gt 0) {
        $totalSize = (Get-ChildItem "updates" -Filter "*.zip" | Measure-Object -Property Length -Sum).Sum
        $sizeMB = [math]::Round($totalSize / 1MB, 2)
        
        if ($sizeMB -gt 100) {
            Write-Host "  ⚠ Total ZIP size: ${sizeMB}MB (consider using GitHub Releases for large files)" -ForegroundColor Yellow
            $warnings++
        } else {
            Write-Host "  ✓ Total ZIP size: ${sizeMB}MB (ok to commit)" -ForegroundColor Green
        }
    }
} else {
    Write-Host "  ✗ updates directory not found!" -ForegroundColor Red
    $errors++
}

Write-Host ""

# Summary
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "Verification Summary" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

if ($errors -eq 0 -and $warnings -eq 0) {
    Write-Host "✓ Perfect! Everything is configured correctly." -ForegroundColor Green
    Write-Host ""
    Write-Host "You're ready to push to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. Create GitHub repository (if not done)" -ForegroundColor White
    Write-Host "2. git add ." -ForegroundColor Cyan
    Write-Host "3. git commit -m 'Initial commit - BattleMaster Update Server'" -ForegroundColor Cyan
    Write-Host "4. git remote add origin https://github.com/YOUR-USERNAME/battlemaster-update-server.git" -ForegroundColor Cyan
    Write-Host "5. git push -u origin main" -ForegroundColor Cyan
    
} elseif ($errors -eq 0) {
    Write-Host "⚠ $warnings warning(s) found" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "You can proceed, but review warnings above." -ForegroundColor Yellow
    
} else {
    Write-Host "✗ $errors error(s) found" -ForegroundColor Red
    if ($warnings -gt 0) {
        Write-Host "⚠ $warnings warning(s) found" -ForegroundColor Yellow
    }
    Write-Host ""
    Write-Host "Please fix errors before pushing to GitHub." -ForegroundColor Red
}

Write-Host ""

# Show what will be committed
Write-Host "Files that will be committed:" -ForegroundColor Yellow
git status --short 2>$null | ForEach-Object {
    $status = $_.Substring(0, 2)
    $file = $_.Substring(3)
    
    if ($status -match "^[A|M|\?]") {
        $color = "Green"
        if ($file -match "admin.php|config.json|\.env$|index.php$") {
            $color = "Red"
            Write-Host "  $_ (SHOULD BE IGNORED!)" -ForegroundColor $color
        } else {
            Write-Host "  $_" -ForegroundColor $color
        }
    }
}

Write-Host ""
