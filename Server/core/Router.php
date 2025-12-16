<?php
class Router {
    private $routes = [];

    public function post($path, $action) {
        $this->routes['POST'][$path] = $action;
    }

    public function get($path, $action) {
        $this->routes['GET'][$path] = $action;
    }

    public function dispatch($method, $uri) {
        // Normalize the request path and strip the public subdirectory if present
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slashes except for root
        $path = rtrim($path, '/') ?: '/';
        
        // Adjust this to match the actual URL path where your public folder is served.
        // The API can be accessed via:
        //   1. Direct: http://localhost/Tickly/Server/public/api/...
        //   2. Via .htaccess rewrite: http://localhost/Tickly/api/... (rewritten to Server/public/index.php)
        // Handle various path formats
        $pathReplacements = [
            '/Tickly/api',           // New path via .htaccess rewrite
            '/Tickly/api/',          // New path via .htaccess rewrite (with trailing slash)
            '/Tickly/Server/public', // Old direct path
            '/Tickly/Server/public/',// Old direct path (with trailing slash)
            'Tickly/api',            // Without leading slash
            'Tickly/Server/public',  // Without leading slash
            '/api',                  // Just /api (if base is different)
            'api'                    // Just api
        ];
        
        foreach ($pathReplacements as $replacement) {
            if (strpos($path, $replacement) === 0) {
                $path = substr($path, strlen($replacement));
                break;
            }
        }
        
        // Ensure path starts with / for API routes
        if ($path !== '/' && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Remove trailing slashes again after replacement
        $path = rtrim($path, '/') ?: '/';
        
        // Debug: Uncomment to see what path is being matched
        // error_log("Router Debug - Method: $method, Path: $path, URI: $uri");

        // Collect request data to pass into controller methods
        $payload = null;
        if ($method === 'POST') {
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);
            if (!is_array($payload)) {
                $payload = $_POST ?? [];
            }
        } else {
            $payload = $_GET ?? [];
        }

        // Try exact match first
        if (isset($this->routes[$method][$path])) {
            [$controllerName, $methodName] = explode('@', $this->routes[$method][$path]);
            require_once __DIR__ . "/../controllers/$controllerName.php";
            $controller = new $controllerName();
            $controller->$methodName($payload);
            return;
        }
        
        // Try with leading slash if path doesn't have one
        if ($path[0] !== '/' && isset($this->routes[$method]['/' . $path])) {
            [$controllerName, $methodName] = explode('@', $this->routes[$method]['/' . $path]);
            require_once __DIR__ . "/../controllers/$controllerName.php";
            $controller = new $controllerName();
            $controller->$methodName($payload);
            return;
        }
        
        // Try without leading slash if path has one
        if ($path[0] === '/' && strlen($path) > 1 && isset($this->routes[$method][substr($path, 1)])) {
            [$controllerName, $methodName] = explode('@', $this->routes[$method][substr($path, 1)]);
            require_once __DIR__ . "/../controllers/$controllerName.php";
            $controller = new $controllerName();
            $controller->$methodName($payload);
            return;
        }
        
        // Route not found - return debug info
        $availableRoutes = [];
        if (isset($this->routes[$method])) {
            $availableRoutes = array_keys($this->routes[$method]);
        }
        
        // Also show all routes for better debugging
        $allRoutes = [];
        foreach ($this->routes as $routeMethod => $routes) {
            foreach ($routes as $routePath => $action) {
                $allRoutes[] = "$routeMethod $routePath";
            }
        }
        
        // Use Response class for consistent error format
        require_once __DIR__ . '/Response.php';
        $errorMsg = "Route not found. Method: $method, Path: '$path', URI: '$uri'.";
        if (!empty($availableRoutes)) {
            $errorMsg .= " Available $method routes: " . implode(', ', $availableRoutes) . ".";
        }
        $errorMsg .= " All routes: " . implode(', ', $allRoutes);
        Response::json(false, $errorMsg, [], 404);
    }
}
