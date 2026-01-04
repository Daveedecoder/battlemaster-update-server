<?php

/**
 * BattleMaster Update Server - Public API
 * GitHub-Ready Version - Tokens loaded from environment
 * 
 * This file is safe to push to GitHub (no hardcoded secrets)
 */

// Load environment configuration
require_once __DIR__ . '/env.php';

error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable for production

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('VERSION_FILE', __DIR__ . '/version.json');

// Get security token from environment (not hardcoded!)
$AUTH_TOKEN = env('AUTH_TOKEN', 'CHANGE_THIS_SECRET_TOKEN');

// Get request parameters
$action = $_GET['action'] ?? '';
$clientVersion = $_GET['version'] ?? '';
$license = $_GET['license'] ?? '';
$token = $_GET['token'] ?? '';
$targetVersion = $_GET['target_version'] ?? '';

// Route the request
switch ($action) {
    case 'check-update':
        handleCheckUpdate($clientVersion, $license);
        break;
    case 'list-updates':
        handleListUpdates($license);
        break;
    case 'download-update':
        handleDownloadUpdate($token, $_GET['version'] ?? '');
        break;
    case 'list-all-versions':
        handleListAllVersions();
        break;
    case 'get-version-metadata':
        handleGetVersionMetadata($targetVersion);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Invalid action']);
}

// List all available update versions from version.json
function handleListAllVersions()
{
    if (!file_exists(VERSION_FILE)) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Version file not found']);
        return;
    }

    $versionData = json_decode(file_get_contents(VERSION_FILE), true);

    if (!$versionData) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Invalid version data']);
        return;
    }

    // Get versions array from version.json
    $versions = [];
    
    if (isset($versionData['versions']) && is_array($versionData['versions'])) {
        // New format: versions array in version.json
        $versions = $versionData['versions'];
    } else {
        // Fallback: scan for individual version JSON files
        $updatesDir = __DIR__ . '/updates/';
        if (is_dir($updatesDir)) {
            $files = glob($updatesDir . '*.json');
            
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['version'])) {
                    $versions[] = [
                        'version' => $data['version'],
                        'release_date' => $data['release_date'] ?? '',
                        'release_notes' => $data['release_notes'] ?? '',
                        'download_url' => $data['download_url'] ?? '',
                        'checksum' => $data['checksum'] ?? '',
                        'file_size' => $data['file_size'] ?? 0,
                        'migration_required' => $data['migration_required'] ?? false,
                        'critical_update' => $data['critical_update'] ?? false,
                        'changelog' => $data['changelog'] ?? [],
                        'php_requirement' => $data['php_requirement'] ?? '',
                        'laravel_requirement' => $data['laravel_requirement'] ?? '',
                        'database_changes' => $data['database_changes'] ?? false
                    ];
                }
            }
        }
    }
    
    // Sort by version (descending - newest first)
    usort($versions, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });
    
    echo json_encode(['success' => true, 'versions' => $versions]);
}

// Get metadata for a specific version
function handleGetVersionMetadata($version)
{
    if (!$version || !preg_match('/^[0-9.]+$/', $version)) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Invalid or missing version']);
        exit;
    }
    $file = __DIR__ . "/updates/$version.json";
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Metadata file not found for version']);
        exit;
    }
    $data = json_decode(file_get_contents($file), true);
    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Invalid JSON in metadata file']);
        exit;
    }
    echo json_encode(['success' => true, 'metadata' => $data]);
}

function handleDownloadUpdate($token, $version)
{
    global $AUTH_TOKEN;
    
    // Validate token from environment variable
    if ($token !== $AUTH_TOKEN) {
        http_response_code(403);
        echo json_encode(['error' => true, 'message' => 'Invalid or missing token']);
        exit;
    }
    
    if (!$version || !preg_match('/^[0-9.]+$/', $version)) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Invalid or missing version']);
        exit;
    }
    
    $file = __DIR__ . "/updates/$version.zip";
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Update file not found']);
        exit;
    }
    
    // Serve the file with correct headers
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: no-cache');
    
    // Clear output buffer if any
    while (ob_get_level()) ob_end_clean();
    readfile($file);
    exit;
}

function handleCheckUpdate($clientVersion, $license)
{
    if (!file_exists(VERSION_FILE)) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Version file not found']);
        return;
    }

    $versionData = json_decode(file_get_contents(VERSION_FILE), true);

    if (!$versionData || !isset($versionData['latest_version'])) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Invalid version data']);
        return;
    }

    $latestVersion = $versionData['latest_version'];
    $updateAvailable = version_compare($latestVersion, $clientVersion, '>');

    // Find details for the latest version
    $latestVersionInfo = null;
    if (isset($versionData['versions']) && is_array($versionData['versions'])) {
        foreach ($versionData['versions'] as $version) {
            if ($version['version'] === $latestVersion) {
                $latestVersionInfo = $version;
                break;
            }
        }
    }

    // Fallback to root-level data if not found in versions array
    if (!$latestVersionInfo) {
        $latestVersionInfo = $versionData;
    }

    echo json_encode([
        'success' => true,
        'update_available' => $updateAvailable,
        'latest_version' => $latestVersion,
        'version' => $latestVersion,
        'current_version' => $clientVersion,
        'download_url' => $latestVersionInfo['download_url'] ?? '',
        'checksum' => $latestVersionInfo['checksum'] ?? '',
        'file_size' => $latestVersionInfo['file_size'] ?? 0,
        'release_notes' => $latestVersionInfo['release_notes'] ?? '',
        'minimum_version' => $versionData['min_supported_version'] ?? '2.0.0',
        'released_at' => $latestVersionInfo['release_date'] ?? date('Y-m-d H:i:s'),
        'release_date' => $latestVersionInfo['release_date'] ?? date('Y-m-d'),
        'migration_required' => $latestVersionInfo['migration_required'] ?? false,
        'critical_update' => $latestVersionInfo['critical_update'] ?? false,
        'changelog' => $latestVersionInfo['changelog'] ?? null
    ]);
}

function handleListUpdates($license)
{
    if (!file_exists(VERSION_FILE)) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Version file not found']);
        return;
    }

    $versionData = json_decode(file_get_contents(VERSION_FILE), true);

    if (!$versionData) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Invalid version data']);
        return;
    }

    // Return available updates as array
    $updates = [];
    if (isset($versionData['latest_version'])) {
        $updates[] = [
            'version' => $versionData['latest_version'],
            'download_url' => $versionData['download_url'] ?? '',
            'checksum' => $versionData['checksum'] ?? '',
            'file_size' => $versionData['file_size'] ?? 0,
            'release_notes' => $versionData['release_notes'] ?? '',
            'released_at' => $versionData['released_at'] ?? date('Y-m-d H:i:s'),
            'release_date' => $versionData['release_date'] ?? date('Y-m-d'),
            'migration_required' => $versionData['migration_required'] ?? false,
            'critical_update' => $versionData['critical_update'] ?? false
        ];
    }

    echo json_encode([
        'success' => true,
        'updates' => $updates
    ]);
}
