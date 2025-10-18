<?php
// Authentication helper: check session-based login/roles without redirecting.
class AuthHelper {
    public static function requireRole($role) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Forbidden';
            exit;
        }
    }

    // Return true if a user is logged in (session contains user_id). Does not redirect.
    public static function requireLogin() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}
