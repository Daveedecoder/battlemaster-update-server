# PowerShell Script: Create New BattleMaster Update (2.3.8)

# Set your new version
$version = "2.3.8"
$sourceDir = "c:\xampp\htdocs\battlemaster"
$tempDir = "c:\temp\battlemaster-update-$version"
$outputZip = "c:\xampp\htdocs\battlemaster-update-server\updates\$version.zip"
$jsonPath = "c:\xampp\htdocs\battlemaster-update-server\updates\$version.json"

# 1. Create temp directory
New-Item -ItemType Directory -Path $tempDir -Force

# 2. Copy changed files (edit these lines for your update)
# Example:
# Copy-Item "$sourceDir\app\Http\Controllers\NewController.php" "$tempDir\app\Http\Controllers\" -Force
# Copy-Item "$sourceDir\resources\views\new-feature.blade.php" "$tempDir\resources\views\" -Force

# 3. Create ZIP
Compress-Archive -Path "$tempDir\*" -DestinationPath $outputZip -Force

# 4. Calculate checksum and file size
$checksum = (Get-FileHash -Path $outputZip -Algorithm MD5).Hash.ToLower()
$fileSize = (Get-Item $outputZip).Length

# 5. Create JSON metadata
$json = @"{
    "version": "$version",
    "release_date": "$(Get-Date -Format 'yyyy-MM-dd')",
    "release_notes": "Describe the new features, fixes, or changes here.",
    "download_url": "http://localhost/battlemaster-update-server/index.php?action=download-update&token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=$version",
    "checksum": "$checksum",
    "file_size": $fileSize,
    "migration_required": false,
    "backup_required": true,
    "critical_update": false,
    "changelog": [
        "Feature: ...",
        "Fix: ..."
    ],
    "breaking_changes": false,
    "php_requirement": "^8.2",
    "laravel_requirement": "^11.0",
    "database_changes": false
}"@

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($jsonPath, $json, $utf8NoBom)

# 6. Cleanup temp directory
Remove-Item $tempDir -Recurse -Force

Write-Host "Update package and JSON created for version $version!" -ForegroundColor Green
