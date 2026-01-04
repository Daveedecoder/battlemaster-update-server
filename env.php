<?php

/**
 * BattleMaster Update Server - Environment Configuration Loader
 * 
 * This file loads environment variables from .env file
 * Keep your .env file private and never commit it to GitHub
 */

function loadEnv($filePath = __DIR__ . '/.env')
{
    if (!file_exists($filePath)) {
        return [];
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            $env[$key] = $value;
            
            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    return $env;
}

/**
 * Get environment variable with fallback
 */
function env($key, $default = null)
{
    $value = getenv($key);
    
    if ($value === false) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    
    // Convert string boolean to actual boolean
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }
    }
    
    return $value;
}

// Load environment variables
loadEnv();
