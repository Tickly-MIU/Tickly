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
$uri = str_replace('/Tickly/public', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        
        if (isset($this->routes[$method][$uri])) {
            [$controllerName, $methodName] = explode('@', $this->routes[$method][$uri]);
            require_once __DIR__ . "/../controllers/$controllerName.php";
            $controller = new $controllerName();
            $controller->$methodName();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Route not found']);
        }
    }
}
