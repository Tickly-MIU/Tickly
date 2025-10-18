<?php
/**
 * App configuration: start session, load environment, set paths and autoloader.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('BASE_PATH', realpath(__DIR__ . '/..'));
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load environment from .env if present (simple parser)
$envFile = realpath(__DIR__ . '/../../.env');
if ($envFile && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
    // strip surrounding single or double quotes
    $value = preg_replace('/^["\']|["\']$/', '', $value);
        if (!getenv($name)) putenv("$name=$value");
        // also populate superglobals for convenience
        if (!isset($_ENV[$name])) $_ENV[$name] = $value;
        if (!isset($_SERVER[$name])) $_SERVER[$name] = $value;
    }
}

// Base URL detection with CLI fallback
if (!defined('BASE_URL')) {
    if (php_sapi_name() === 'cli') {
        define('BASE_URL', 'http://localhost:8000');
    } else {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('BASE_URL', "$proto://$host");
    }
}

// Very small class autoloader for controllers/models/helpers
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../controllers/' . $class . '.php',
        __DIR__ . '/../models/' . $class . '.php',
        __DIR__ . '/../helpers/' . $class . '.php',
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            require_once $p;
            return;
        }
    }
});
