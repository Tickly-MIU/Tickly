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
        
        // List of base paths to strip to get to the "relative" API path
        $pathReplacements = [
            '/Tickly/Server/public', // Direct path via XAMPP
            '/Tickly/api',           // Rewritten path via root .htaccess
            '/Tickly',               // Project root
            '/api',                  // Just /api
        ];
        
        $normalizedPath = $path;
        foreach ($pathReplacements as $replacement) {
            if (strpos($normalizedPath, $replacement) === 0) {
                $normalizedCandidate = substr($normalizedPath, strlen($replacement));
                // Only accept if it results in a meaningful path or if we want to preserve /api
                // If the route definition includes /api, we should be careful about stripping it
                if ($normalizedCandidate === '' || $normalizedCandidate[0] === '/') {
                    $normalizedPath = $normalizedCandidate;
                    break;
                }
            }
        }
        
        $path = $normalizedPath ?: '/';

        // Ensure path starts with / for routing if not already (and if not empty)
        if ($path !== '/' && $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Special case: if the route exists WITH /api but we stripped it, or vice versa
        // Let's log what we found
        error_log("Router Debug - Method: $method, Calculated Path: $path, Original URI: $uri");

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
