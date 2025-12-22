<?php
require_once __DIR__ . '/Response.php';

class AuthMiddleware
{
    public static function requireLogin()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['user_id'])) {
            Response::json(false, 'Unauthorized', [], 401);
            // Response::json() already calls exit, so execution stops here
        }
    }
}

