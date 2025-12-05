<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// echo $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']; exit; // Debug line

require_once __DIR__ . '/../core/Router.php';

$router = new Router();
require_once __DIR__ . '/../routes/api.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

$router->post('/api/register', 'AuthController@register');
$router->post('/api/login', 'AuthController@login');

