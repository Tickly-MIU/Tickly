<?php
// Configure CORS + session so cookies can be sent with API requests
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:4200',
    'http://localhost:5173',
    'http://127.0.0.1:4200',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:3000',
];

if ($origin !== '*' && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Configure session for cross-origin requests on localhost
if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie parameters
    // Use '/' as path for localhost - works for both dev server (port 4200) and production (port 80)
    // The cookie will be sent with all requests on localhost regardless of port
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/', // Root path works for all localhost requests
            'domain' => '', // Empty domain works for localhost
            'secure' => false, // false for localhost (http), true for production (https)
            'httponly' => true,
            'samesite' => 'Lax' // Lax works for same-site requests
        ]);
    } else {
        session_set_cookie_params(86400, '/', '', false, true);
    }
    
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Debug: Log request info to PHP error log
error_log("API Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

// Register routes before dispatching the current request
$router = new Router();
require_once __DIR__ . '/../routes/api.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

