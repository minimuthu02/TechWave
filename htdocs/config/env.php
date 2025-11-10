<?php
/**
 * Environment Variable Loader - Bulletproof version
 */

function loadEnv($path = null) {
    // Find .env file
    if ($path === null) {
        $possiblePaths = [
            __DIR__ . '/../.env',
            dirname(__DIR__) . '/.env',
            $_SERVER['DOCUMENT_ROOT'] . '/.env',
        ];
        
        $path = null;
        foreach ($possiblePaths as $possiblePath) {
            if (file_exists($possiblePath) && is_readable($possiblePath)) {
                $path = $possiblePath;
                break;
            }
        }
        
        if ($path === null) {
            throw new Exception('.env file not found in any expected location');
        }
    }
    
    if (!file_exists($path)) {
        throw new Exception('.env file not found at: ' . $path);
    }
    
    if (!is_readable($path)) {
        throw new Exception('.env file is not readable at: ' . $path);
    }

    // Read file
    $content = file_get_contents($path);
    if ($content === false) {
        throw new Exception('Failed to read .env file');
    }
    
    // Parse line by line
    $lines = explode("\n", $content);
    $loaded = 0;
    
    foreach ($lines as $line) {
        // Trim whitespace
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        // Check if line contains =
        if (strpos($line, '=') === false) {
            continue;
        }
        
        // Split into key and value
        list($key, $value) = explode('=', $line, 2);
        
        // Trim key and value
        $key = trim($key);
        $value = trim($value);
        
        // Remove surrounding quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        // Set in all three places for maximum compatibility
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        
        $loaded++;
    }
    
    if ($loaded === 0) {
        throw new Exception('No variables loaded from .env file');
    }
    
    return $loaded;
}

function env($key, $default = null) {
    // Check all possible sources
    $value = null;
    
    if (isset($_ENV[$key])) {
        $value = $_ENV[$key];
    } elseif (isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    } else {
        $value = getenv($key);
        if ($value === false) {
            $value = null;
        }
    }
    
    // Return default if not found
    if ($value === null) {
        return $default;
    }

    // Convert string representations of boolean
    $lower = strtolower($value);
    if ($lower === 'true' || $lower === '(true)') {
        return true;
    }
    if ($lower === 'false' || $lower === '(false)') {
        return false;
    }
    if ($lower === 'null' || $lower === '(null)') {
        return null;
    }
    if ($lower === 'empty' || $lower === '(empty)') {
        return '';
    }

    return $value;
}

// Auto-load on include
try {
    $varsLoaded = loadEnv();
    // Store count for debugging
    define('ENV_VARS_LOADED', $varsLoaded);
} catch (Exception $e) {
    die('Configuration Error: ' . $e->getMessage() . '<br><br>Please ensure your .env file exists and is readable.');
}
?>