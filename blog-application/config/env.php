<?php
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        throw new Exception('.env file not found. Please create one from .env.example');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    return $value;
}

try {
    loadEnv();
} catch (Exception $e) {
    die('Configuration Error: ' . $e->getMessage());
}
?>