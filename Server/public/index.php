<?php
// Error handling - catch all errors and return JSON
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    require_once __DIR__ . '/../core/Response.php';
    Response::json(false, "PHP Error: $message in $file on line $line", [], 500);
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        require_once __DIR__ . '/../core/Response.php';
        Response::json(false, "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}", [], 500);
    }
});

// Configure CORS + session so cookies can be sent with API requests
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Detect if request is from Postman or similar API testing tools
$isPostman = (
    strpos($userAgent, 'PostmanRuntime') !== false ||
    strpos($userAgent, 'Postman') !== false ||
    strpos($userAgent, 'insomnia') !== false ||
    strpos($userAgent, 'curl') !== false ||
    strpos($userAgent, 'HTTPie') !== false ||
    empty($origin) // Postman often doesn't send Origin header
);

$allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:4200',
    'http://localhost:5173',
    'http://127.0.0.1:4200',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:3000',
    'https://tickly-miu.netlify.app',
    'https://dash.infinityfree.com',     // InfinityFree dashboard
    'https://www.dash.infinityfree.com', // InfinityFree dashboard (www)
];

// Allow requests from Postman and API testing tools
if ($isPostman || empty($origin)) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: false"); // Can't use credentials with wildcard
} elseif (in_array($origin, $allowedOrigins, true)) {
    // Specific allowed origin - can use credentials
    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
} else {
    // Unknown origin - allow for API testing (Postman can send various origins)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: false");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, User-Agent");
header("Access-Control-Max-Age: 86400"); // Cache preflight for 24 hours

// Handle preflight OPTIONS request immediately - must return before any other processing
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configure session for cross-origin requests
if (session_status() === PHP_SESSION_NONE) {
    // Detect if running on HTTPS (production)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    // Detect if localhost
    $isLocalhost = (
        strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false
    );
    
    // Configure session cookie parameters
    // For cross-origin with credentials: SameSite=None requires Secure=true
    // For localhost: SameSite=Lax works with Secure=false
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/', // Root path
            'domain' => '', // Empty domain works for all
            'secure' => $isHttps, // true for HTTPS (production), false for HTTP (localhost)
            'httponly' => true,
            'samesite' => ($isHttps && !$isLocalhost) ? 'None' : 'Lax' // None for cross-origin HTTPS, Lax for localhost
        ]);
    } else {
        session_set_cookie_params(86400, '/', '', $isHttps, true);
    }
    
    session_start();
}

// Wrap everything in try-catch for proper error handling
try {
    require_once __DIR__ . '/../core/Router.php';
    require_once __DIR__ . '/../core/Response.php';
    require_once __DIR__ . '/../core/AuthMiddleware.php';

    // Register routes before dispatching the current request
    $router = new Router();
    require_once __DIR__ . '/../routes/api.php';

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    require_once __DIR__ . '/../core/Response.php';
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    Response::json(false, "Server Error: " . $e->getMessage(), [], 500);
} catch (Error $e) {
    require_once __DIR__ . '/../core/Response.php';
    error_log("Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    Response::json(false, "Server Error: " . $e->getMessage(), [], 500);
}

