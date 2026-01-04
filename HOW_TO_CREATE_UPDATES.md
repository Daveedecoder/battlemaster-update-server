# How to Create Updates for BattleMaster

This guide explains how to create and deploy updates for the BattleMaster application using the auto-update system.

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Update System Architecture](#update-system-architecture)
3. [Step-by-Step Guide](#step-by-step-guide)
4. [Version Numbering](#version-numbering)
5. [Creating Update Packages](#creating-update-packages)
6. [Testing Updates](#testing-updates)
7. [Troubleshooting](#troubleshooting)
8. [Advanced Configuration](#advanced-configuration)

---

## Quick Start

### Prerequisites
- XAMPP with Apache and PHP 8.2+ running
- Access to the update server directory: `c:\xampp\htdocs\battlemaster-update-server\`
- PowerShell or Command Prompt

### Create an Update in 5 Minutes

```powershell
# 1. Navigate to updates directory
cd c:\xampp\htdocs\battlemaster-update-server\updates

# 2. Create a dummy update package (replace 1.0.8 with your version)
echo "Update package" > test.txt
powershell -command "Compress-Archive -Path test.txt -DestinationPath 1.0.8.zip -Force"
del test.txt

# 3. Calculate MD5 checksum
powershell -command "(Get-FileHash -Path '1.0.8.zip' -Algorithm MD5).Hash.ToLower()"
# Copy the output checksum

# 4. Update version.json (see template below)
# Replace VERSION, CHECKSUM, and DATE

# 5. Test the update
# Go to http://127.0.0.1:8000/admin/system/update and click "Check for Updates"
```

---

## Update System Architecture

### Components

```
battlemaster-update-server/          # Update Server (Author Side)
├── index.php                        # API endpoint handler
├── admin.php                        # Admin control panel
├── version.json                     # Current version metadata
├── config.json                      # Server configuration
├── changelog.json                   # All version changelogs
├── .htaccess                        # URL rewriting (optional)
└── updates/                         # Update packages directory
    ├── 1.0.5.zip
    ├── 1.0.6.zip
    └── 1.0.7.zip

battlemaster/                        # Main Application (Buyer Side)
├── app/Http/Controllers/Admin/
│   └── UpdateController.php         # Handles update process
├── config/update.php                # Update configuration
├── resources/views/admin/system/
│   └── update.blade.php            # Update UI
└── routes/admin.php                 # Update routes
```

### How It Works

1. **Check for Updates**: Main app requests version info from update server
2. **Download**: If newer version exists, downloads ZIP package
3. **Verify**: Validates checksum to ensure file integrity
4. **Backup**: Creates rollback backup of current files
5. **Extract**: Extracts update files to application directory
6. **Migrate**: Runs database migrations if needed
7. **Complete**: Updates version in .env file and clears caches

---

## Step-by-Step Guide

### Step 1: Prepare Your Code Changes

Make all your code changes in your development copy of BattleMaster:
- Fix bugs
- Add new features
- Update dependencies
- Modify database schema

### Step 2: Determine Version Number

Use Semantic Versioning (MAJOR.MINOR.PATCH):
- **MAJOR** (1.x.x): Breaking changes
- **MINOR** (x.1.x): New features, backward compatible
- **PATCH** (x.x.1): Bug fixes, backward compatible

Example: If current version is `1.0.7`, next could be:
- `1.0.8` - Bug fixes
- `1.1.0` - New features
- `2.0.0` - Major rewrite

### Step 3: Create Update Package

#### Option A: Using PowerShell (Recommended)

```powershell
# Set your new version
$version = "1.0.8"

# Navigate to updates directory
cd c:\xampp\htdocs\battlemaster-update-server\updates

# Create ZIP package from your BattleMaster files
# IMPORTANT: Only include changed files, not entire application
$sourceFiles = @(
    "c:\xampp\htdocs\battlemaster\app\Http\Controllers\SomeController.php",
    "c:\xampp\htdocs\battlemaster\resources\views\some-view.blade.php",
    "c:\xampp\htdocs\battlemaster\database\migrations\2025_12_13_new_migration.php"
)

Compress-Archive -Path $sourceFiles -DestinationPath "$version.zip" -Force

# Or create a test package
echo "Update package v$version" > test.txt
Compress-Archive -Path test.txt -DestinationPath "$version.zip" -Force
del test.txt
```

#### Option B: Manual ZIP Creation

1. Create a folder with only the files you want to update
2. Right-click → Send to → Compressed (zipped) folder
3. Rename to `1.0.8.zip`
4. Move to `c:\xampp\htdocs\battlemaster-update-server\updates\`

### Step 4: Calculate Checksum

```powershell
# Get MD5 hash of your ZIP file
$version = "1.0.8"
cd c:\xampp\htdocs\battlemaster-update-server\updates
(Get-FileHash -Path "$version.zip" -Algorithm MD5).Hash.ToLower()

# Example output: a1b2c3d4e5f6789012345678901234567
```

**Copy this checksum** - you'll need it for version.json

### Step 5: Update version.json

Edit `c:\xampp\htdocs\battlemaster-update-server\version.json`:

```json
{
    "latest_version": "1.0.8",
    "min_supported_version": "1.0.0",
    "release_date": "2025-12-13",
    "release_notes": "Brief description of changes",
    "update_available": true,
    "download_url": "http://localhost/battlemaster-update-server/index.php?action=download-update&token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=1.0.8",
    "migration_required": false,
    "backup_required": true,
    "critical_update": false,
    "checksum": "YOUR_MD5_CHECKSUM_HERE",
    "file_size": 154,
    "changelog": [
        "Fixed critical security vulnerability",
        "Improved dashboard performance",
        "Added new tournament features",
        "Bug fixes and stability improvements"
    ],
    "breaking_changes": false,
    "php_requirement": "^8.2",
    "laravel_requirement": "^11.0",
    "database_changes": false
}
```

**Important Fields:**
- `latest_version`: Your new version number
- `release_date`: Today's date (YYYY-MM-DD)
- `download_url`: Update the version number in the URL
- `checksum`: The MD5 hash you calculated
- `file_size`: Size of ZIP in bytes (get with `(Get-Item "1.0.8.zip").Length`)
- `migration_required`: Set to `true` if you have database migrations
- `database_changes`: Set to `true` if schema changed
- `critical_update`: Set to `true` for urgent security fixes

#### PowerShell Helper to Update version.json

```powershell
# Quick script to update version.json
$version = "1.0.8"
$checksum = "YOUR_CHECKSUM_HERE"
$fileSize = (Get-Item "c:\xampp\htdocs\battlemaster-update-server\updates\$version.zip").Length

$json = @"
{
    "latest_version": "$version",
    "min_supported_version": "1.0.0",
    "release_date": "$(Get-Date -Format 'yyyy-MM-dd')",
    "release_notes": "Security patches, bug fixes, and performance improvements.",
    "update_available": true,
    "download_url": "http://localhost/battlemaster-update-server/index.php?action=download-update&token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=$version",
    "migration_required": false,
    "backup_required": true,
    "critical_update": false,
    "checksum": "$checksum",
    "file_size": $fileSize,
    "changelog": [
        "Feature: Added new functionality",
        "Fix: Resolved critical bug",
        "Performance: Improved loading speed",
        "Security: Enhanced authentication"
    ],
    "breaking_changes": false,
    "php_requirement": "^8.2",
    "laravel_requirement": "^11.0",
    "database_changes": false
}
"@

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText("c:\xampp\htdocs\battlemaster-update-server\version.json", $json, $utf8NoBom)

Write-Host "version.json updated successfully!" -ForegroundColor Green
```

### Step 6: Test the Update

#### Test API Endpoints

```powershell
# Test check-update endpoint
Invoke-WebRequest -Uri "http://localhost/battlemaster-update-server/index.php?action=check-update&version=1.0.7&license=TEST" -UseBasicParsing

# Expected output: JSON with latest_version: "1.0.8", update_available: true

# Test download endpoint
Invoke-WebRequest -Uri "http://localhost/battlemaster-update-server/index.php?action=download-update&token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=1.0.8" -UseBasicParsing

# Expected output: StatusCode 200, ZIP file downloaded
```

#### Test from Admin Panel

1. Open: `http://127.0.0.1:8000/admin/system/update`
2. Click **"Check for Updates"**
3. Should show: "Update Available: Version 1.0.8"
4. Click **"Install Update"**
5. Watch progress bar complete all steps
6. Page should refresh and show updated version

---

## Version Numbering

### Semantic Versioning Format

```
MAJOR.MINOR.PATCH
  1  .  0  .  8

1 = Major version (breaking changes)
0 = Minor version (new features)
8 = Patch version (bug fixes)
```

### When to Increment

| Change Type | Example | Version Change |
|------------|---------|----------------|
| Bug fix | Fixed payment gateway error | 1.0.7 → 1.0.8 |
| New feature (compatible) | Added tournament brackets | 1.0.8 → 1.1.0 |
| Breaking change | Removed old API | 1.1.0 → 2.0.0 |
| Security patch | Fixed XSS vulnerability | 1.0.8 → 1.0.9 |

---

## Creating Update Packages

### What to Include

**✅ Include:**
- Modified PHP files
- New/updated Blade templates
- New database migrations
- Updated assets (CSS, JS, images)
- New configuration files
- New routes

**❌ Never Include:**
- `.env` file (protected)
- `storage/` directory
- `vendor/` directory
- `node_modules/`
- User uploads
- Database files

### File Structure in ZIP

```
1.0.8.zip
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── NewController.php
│   └── Models/
│       └── UpdatedModel.php
├── resources/
│   └── views/
│       └── new-feature.blade.php
├── database/
│   └── migrations/
│       └── 2025_12_13_add_new_table.php
└── public/
    └── css/
        └── new-styles.css
```

### Creating Real Update Package

```powershell
# Example: Creating update with actual files
$version = "1.0.8"
$sourceDir = "c:\xampp\htdocs\battlemaster"
$tempDir = "c:\temp\battlemaster-update-$version"
$outputZip = "c:\xampp\htdocs\battlemaster-update-server\updates\$version.zip"

# Create temp directory
New-Item -ItemType Directory -Path $tempDir -Force

# Copy changed files (example)
Copy-Item "$sourceDir\app\Http\Controllers\PaymentController.php" "$tempDir\app\Http\Controllers\" -Force
Copy-Item "$sourceDir\resources\views\payment\*" "$tempDir\resources\views\payment\" -Recurse -Force
Copy-Item "$sourceDir\database\migrations\2025_12_13_*" "$tempDir\database\migrations\" -Force

# Create ZIP
Compress-Archive -Path "$tempDir\*" -DestinationPath $outputZip -Force

# Cleanup
Remove-Item $tempDir -Recurse -Force

Write-Host "Update package created: $outputZip" -ForegroundColor Green
```

---

## Testing Updates

### Pre-Release Checklist

- [ ] Code changes tested locally
- [ ] Database migrations tested
- [ ] Version number incremented correctly
- [ ] Checksum calculated and verified
- [ ] version.json updated with correct info
- [ ] ZIP package contains only necessary files
- [ ] No .env or sensitive files in package
- [ ] Backup created of production files

### Testing Process

1. **Test on Localhost First**
   ```
   http://127.0.0.1:8000/admin/system/update
   ```

2. **Verify Update Detection**
   - Click "Check for Updates"
   - Confirm correct version shown
   - Verify changelog displayed

3. **Install Update**
   - Click "Install Update"
   - Watch progress: Downloading → Verifying → Backing up → Extracting → Migrating
   - Should complete with "Update Successful!"

4. **Verify Installation**
   - Page refreshes automatically
   - Check version number updated
   - Test affected features
   - Check logs: `storage/logs/laravel.log`

5. **Test Rollback** (if needed)
   - Rollback option available in update UI
   - Restores previous version from backup

### Common Test Scenarios

```powershell
# Test 1: Check API response
Invoke-WebRequest -Uri "http://localhost/battlemaster-update-server/index.php?action=check-update&version=1.0.0&license=TEST" -UseBasicParsing | Select-Object -ExpandProperty Content

# Test 2: Verify checksum matches
$checksum = (Get-FileHash -Path "c:\xampp\htdocs\battlemaster-update-server\updates\1.0.8.zip" -Algorithm MD5).Hash.ToLower()
$versionJson = Get-Content "c:\xampp\htdocs\battlemaster-update-server\version.json" | ConvertFrom-Json
if ($checksum -eq $versionJson.checksum) {
    Write-Host "✓ Checksum matches!" -ForegroundColor Green
} else {
    Write-Host "✗ Checksum mismatch!" -ForegroundColor Red
}

# Test 3: Download endpoint
$response = Invoke-WebRequest -Uri "http://localhost/battlemaster-update-server/index.php?action=download-update&token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=1.0.8" -UseBasicParsing
Write-Host "Status: $($response.StatusCode) | Size: $($response.RawContentLength) bytes"
```

---

## Troubleshooting

### Common Issues and Solutions

#### Issue: "Update package not found for version X"

**Cause:** ZIP file doesn't exist or version mismatch

**Solution:**
```powershell
# Verify ZIP exists
dir c:\xampp\htdocs\battlemaster-update-server\updates\*.zip

# Check version.json matches
Get-Content c:\xampp\htdocs\battlemaster-update-server\version.json | ConvertFrom-Json | Select-Object latest_version
```

#### Issue: "Checksum verification failed"

**Cause:** Checksum in version.json doesn't match actual file

**Solution:**
```powershell
# Recalculate checksum
$version = "1.0.8"
$actualChecksum = (Get-FileHash -Path "c:\xampp\htdocs\battlemaster-update-server\updates\$version.zip" -Algorithm MD5).Hash.ToLower()

# Update version.json with correct checksum
Write-Host "Use this checksum: $actualChecksum"
```

#### Issue: "Failed to connect to update server: 404"

**Cause:** Apache not serving update server or URL incorrect

**Solution:**
```powershell
# Test if server is accessible
Invoke-WebRequest -Uri "http://localhost/battlemaster-update-server/index.php" -UseBasicParsing

# Check Apache is running
Get-Process -Name httpd -ErrorAction SilentlyContinue
```

#### Issue: Redirect to /install after update

**Cause:** INSTALLED=true flag missing from .env

**Solution:**
```powershell
# Verify INSTALLED flag
findstr /C:"INSTALLED" c:\xampp\htdocs\battlemaster\.env

# Add if missing
Add-Content c:\xampp\htdocs\battlemaster\.env "`nINSTALLED=true"

# Clear Laravel cache
php c:\xampp\htdocs\battlemaster\artisan config:clear
php c:\xampp\htdocs\battlemaster\artisan cache:clear
```

#### Issue: "Version data not found on server"

**Cause:** version.json has UTF-8 BOM or invalid JSON

**Solution:**
```powershell
# Recreate version.json without BOM
$json = Get-Content "c:\xampp\htdocs\battlemaster-update-server\version.json" -Raw
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText("c:\xampp\htdocs\battlemaster-update-server\version.json", $json, $utf8NoBom)
```

### Debug Mode

Enable detailed logging in UpdateController:

```php
// In app/Http/Controllers/Admin/UpdateController.php
Log::info('Download URL', ['url' => $downloadUrl]);
Log::info('Response Status', ['status' => $response->status()]);
```

Check logs:
```powershell
Get-Content c:\xampp\htdocs\battlemaster\storage\logs\laravel.log -Tail 50
```

---

## Advanced Configuration

### Customizing Update Settings

Edit `config/update.php` in main application:

```php
return [
    'api_url' => env('UPDATE_API_URL', 'http://localhost/battlemaster-update-server/index.php'),
    'auth_token' => env('UPDATE_AUTH_TOKEN', 'BATTLEMASTER_SECRET_UPDATE_KEY_2025'),
    'current_version' => env('APP_VERSION', '1.0.0'),
    'verify_checksum' => true,  // Set false to skip checksum verification
    'enable_maintenance' => true, // Enable maintenance mode during update
    'backup_enabled' => true,
    'max_backups' => 5, // Keep last 5 backups
];
```

### Production Deployment

When deploying to production:

1. **Change Update Server URL**
   ```bash
   # In .env
   UPDATE_API_URL=https://yourdomain.com/updates/index.php
   ```

2. **Change Auth Token**
   ```bash
   # Generate secure token
   UPDATE_AUTH_TOKEN=your-secure-random-token-here
   
   # Update in update server index.php
   define('AUTH_TOKEN', 'your-secure-random-token-here');
   ```

3. **Enable HTTPS**
   Update download_url in version.json to use https://

4. **Secure Update Directory**
   Add to `.htaccess` in update server:
   ```apache
   # Deny direct access to ZIP files
   <FilesMatch "\.zip$">
       Order Deny,Allow
       Deny from all
   </FilesMatch>
   
   # Allow only through index.php API
   ```

### Global Update Toggle

Use admin.php to enable/disable updates:

1. Access: `http://localhost/battlemaster-update-server/admin.php`
2. Toggle "Global Updates Enabled"
3. When disabled, no clients can download updates

---

## Quick Reference Commands

### Create Update Package
```powershell
$v = "1.0.8"
cd c:\xampp\htdocs\battlemaster-update-server\updates
echo "Update" > test.txt
Compress-Archive -Path test.txt -DestinationPath "$v.zip" -Force
del test.txt
(Get-FileHash -Path "$v.zip" -Algorithm MD5).Hash.ToLower()
```

### Update version.json Template
```powershell
$version = "1.0.8"
$checksum = "YOUR_CHECKSUM"
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText("c:\xampp\htdocs\battlemaster-update-server\version.json", "{`"latest_version`":`"$version`",`"checksum`":`"$checksum`",`"download_url`":`"http://localhost/battlemaster-update-server/index.php?action=download-update&token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=$version`"}", $utf8NoBom)
```

### Test Update
```powershell
Invoke-WebRequest -Uri "http://localhost/battlemaster-update-server/index.php?action=check-update&version=1.0.0&license=TEST" -UseBasicParsing
```

---

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Verify all files in correct locations
3. Test API endpoints manually
4. Review this documentation

---

**Last Updated:** December 13, 2025  
**System Version:** 1.0.7+  
**Author:** DaveeDeCoder
