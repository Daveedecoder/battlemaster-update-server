<?php

/**
 * BattleMaster Update Packager
 * 
 * This script helps you create update ZIP packages from your main project
 * Run this script to automatically package updates for distribution
 * 
 * COPILOT INSTRUCTIONS:
 * ----------------------
 * To create a new update package:
 * 1. Modify the $sourceProjectPath to point to your main project
 * 2. Update the $version variable
 * 3. Run: php package-update.php
 * 4. The ZIP will be created in the /updates directory
 * 5. Update version.json with the new version information
 * 
 * @author DaveeDeCoder
 */

// Configuration
$sourceProjectPath = 'C:/xampp/htdocs/battlemaster'; // Path to main BattleMaster project
$version = '1.0.5'; // Version to package
$updateServerPath = __DIR__;
$outputDir = $updateServerPath . '/updates';
$outputFile = $outputDir . '/' . $version . '.zip';

// Directories to include in update
$includeDirs = [
    'app',
    'config',
    'database/migrations',
    'resources',
    'routes',
    'public',
    'bootstrap'
];

// Files to include from root
$includeRootFiles = [
    'artisan',
    'composer.json',
    'package.json',
    'vite.config.js',
    'tailwind.config.js',
    'postcss.config.js'
];

// Paths to EXCLUDE (never include these in updates)
$excludePaths = [
    '.env',
    '.env.example',
    'storage/*',
    'vendor/*',
    'node_modules/*',
    'public/storage',
    '.git/*',
    '.gitignore',
    'tests/*',
    'phpunit.xml',
    '*.log',
    '*.cache',
    'storage/logs/*',
    'storage/framework/cache/*',
    'storage/framework/sessions/*',
    'storage/framework/views/*',
    'storage/app/temp/*',
    'storage/app/updates/*',
    'storage/app/backups/*'
];

echo "===========================================\n";
echo "  BattleMaster Update Packager\n";
echo "===========================================\n\n";

// Validate source project
if (!is_dir($sourceProjectPath)) {
    die("ERROR: Source project not found at: $sourceProjectPath\n");
}

echo "Source Project: $sourceProjectPath\n";
echo "Version: $version\n";
echo "Output: $outputFile\n\n";

// Create updates directory if not exists
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✓ Created updates directory\n";
}

// Check if output file already exists
if (file_exists($outputFile)) {
    echo "WARNING: File already exists: $outputFile\n";
    echo "Do you want to overwrite? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    if (strtolower($line) !== 'yes') {
        die("Cancelled.\n");
    }
    unlink($outputFile);
}

// Create ZIP archive
$zip = new ZipArchive();
if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("ERROR: Cannot create ZIP file\n");
}

echo "\n📦 Packaging files...\n";

$fileCount = 0;
$totalSize = 0;

// Helper function to check if path should be excluded
function shouldExclude($path, $excludePaths)
{
    foreach ($excludePaths as $exclude) {
        $exclude = str_replace('*', '.*', $exclude);
        if (preg_match('#' . $exclude . '#', $path)) {
            return true;
        }
    }
    return false;
}

// Helper function to add directory to ZIP
function addDirectoryToZip($zip, $sourceDir, $baseDir, $zipPath, $excludePaths, &$fileCount, &$totalSize)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($baseDir) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);

        // Check if should exclude
        if (shouldExclude($relativePath, $excludePaths)) {
            continue;
        }

        $zipFilePath = $zipPath . '/' . $relativePath;

        if ($file->isDir()) {
            $zip->addEmptyDir($zipFilePath);
        } else {
            $zip->addFile($filePath, $zipFilePath);
            $fileCount++;
            $totalSize += filesize($filePath);

            if ($fileCount % 50 === 0) {
                echo "  Added $fileCount files...\n";
            }
        }
    }
}

// Add included directories
foreach ($includeDirs as $dir) {
    $sourcePath = $sourceProjectPath . '/' . $dir;
    if (is_dir($sourcePath)) {
        echo "  Adding directory: $dir\n";
        addDirectoryToZip($zip, $sourcePath, $sourceProjectPath, '', $excludePaths, $fileCount, $totalSize);
    }
}

// Add root files
foreach ($includeRootFiles as $file) {
    $sourcePath = $sourceProjectPath . '/' . $file;
    if (file_exists($sourcePath)) {
        $zip->addFile($sourcePath, $file);
        $fileCount++;
        $totalSize += filesize($sourcePath);
        echo "  Added root file: $file\n";
    }
}

// Add update metadata file
$metadata = [
    'version' => $version,
    'packaged_at' => date('Y-m-d H:i:s'),
    'files_count' => $fileCount,
    'package_size' => $totalSize,
    'requires_migration' => true,
    'requires_backup' => true,
    'protected_paths' => $excludePaths
];
$zip->addFromString('update-metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

// Close ZIP
$zip->close();

echo "\n✓ Package created successfully!\n\n";
echo "Statistics:\n";
echo "  Files: $fileCount\n";
echo "  Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";
echo "  Compressed: " . number_format(filesize($outputFile) / 1024 / 1024, 2) . " MB\n";
echo "  Location: $outputFile\n\n";

// Generate checksum
$checksum = md5_file($outputFile);
echo "Checksum (MD5): $checksum\n\n";

// Update version.json
$versionFile = $updateServerPath . '/version.json';
if (file_exists($versionFile)) {
    $versionData = json_decode(file_get_contents($versionFile), true);
    $versionData['latest_version'] = $version;
    $versionData['checksum'] = $checksum;
    $versionData['file_size'] = filesize($outputFile);
    $versionData['release_date'] = date('Y-m-d');
    $versionData['download_url'] = "http://localhost/battlemaster-update-server/api/v1/download-update?token=BATTLEMASTER_SECRET_UPDATE_KEY_2025&version=$version";

    file_put_contents($versionFile, json_encode($versionData, JSON_PRETTY_PRINT));
    echo "✓ Updated version.json\n\n";
}

echo "===========================================\n";
echo "Next steps:\n";
echo "1. Test the update on a clean installation\n";
echo "2. Update changelog.json with release notes\n";
echo "3. Enable 'Global Update' in admin.php\n";
echo "4. Push to GitHub repository\n";
echo "===========================================\n";