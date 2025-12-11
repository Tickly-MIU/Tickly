<?php
require_once __DIR__ . '/Response.php';

class AuthMiddleware
{
    public static function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            Response::json(false, 'Unauthorized', [], 401);
        }
    }
}
?>

