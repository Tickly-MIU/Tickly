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
        $path = rtrim($path, '/') ?: '/';
        $path = str_replace('/Tickly/public', '', $path);

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

        if (isset($this->routes[$method][$path])) {
            [$controllerName, $methodName] = explode('@', $this->routes[$method][$path]);
            require_once __DIR__ . "/../controllers/$controllerName.php";
            $controller = new $controllerName();
            $controller->$methodName($payload);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Route not found']);
        }
    }
}
