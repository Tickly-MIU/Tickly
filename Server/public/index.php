<?php
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
    'https://tickly.page.gd',           // Production domain
    'https://tickly-3f3fb62f8bf7.herokuapp.com', // Heroku (old)
    'https://tickly-backend-a247ddfb7eba.herokuapp.com', // Heroku (current)
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

// Debug: Uncomment to see the actual REQUEST_URI
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Path: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . "\n";
exit;

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

// Register routes before dispatching the current request
$router = new Router();
require_once __DIR__ . '/../routes/api.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

