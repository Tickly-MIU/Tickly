<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

class UsersController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model("Users");
    }

    // ---------------------------
    // Register User
    // ---------------------------
    public function register($data = [])
    {
        if (empty($data["full_name"]) || empty($data["email"]) || empty($data["password"])) {
            return Response::json(false, "All fields are required");
        }

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            return Response::json(false, "Invalid email format");
        }

        if ($this->userModel->exists($data["email"])) {
            return Response::json(false, "Email already registered");
        }

        $userData = [
            "full_name"    => htmlspecialchars($data["full_name"]),
            "email"        => strtolower($data["email"]),
            "password_hash"=> password_hash($data["password"], PASSWORD_BCRYPT),
            "role"         => "user"
        ];

        $created = $this->userModel->register($userData);

        if ($created) {
            return Response::json(true, "Registration successful");
        }

        return Response::json(false, "Failed to register user");
    }

    // ---------------------------
    // Login User
    // ---------------------------
    public function login($data = [])
    {
        if (empty($data["email"]) || empty($data["password"])) {
            return Response::json(false, "Email and password are required");
        }

        $user = $this->userModel->getByEmail($data["email"]);

        if (!$user) {
            return Response::json(false, "Invalid credentials");
        }

        if (!password_verify($data["password"], $user["password_hash"])) {
            return Response::json(false, "Invalid credentials");
        }

        // create session
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["role"] = $user["role"];

        return Response::json(true, "Login successful", [
            "user_id" => $user["user_id"],
            "name"    => $user["full_name"],
            "role"    => $user["role"]
        ]);
    }

    // ---------------------------
    // Logout User (Note: This method is not used, logout is handled by AuthController)
    // ---------------------------
    public function logout($data = [])
    {
        // Clear all session variables
        $_SESSION = [];
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        return Response::json(true, "Logged out successfully");
    }

    // ---------------------------
    // Fetch Profile
    // ---------------------------
    public function profile($data = [])
    {
        AuthMiddleware::requireLogin();

        $user_id = $_SESSION["user_id"];
        $profile = $this->userModel->getById($user_id);

        if (!$profile) {
            return Response::json(false, "User not found");
        }

        unset($profile["password_hash"]); // Hide password hash

        return Response::json(true, "Profile loaded", $profile);
    }

    // ---------------------------
    // Session Check (for debugging and validation)
    // ---------------------------
    public function sessionCheck($data = [])
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if session is active and has user data
        $isAuthenticated = !empty($_SESSION["user_id"]);
        $sessionId = session_id();
        $sessionStatus = session_status();
        
        // Get session data (sanitized - don't expose sensitive info)
        $sessionData = [
            "user_id" => $_SESSION["user_id"] ?? null,
            "role" => $_SESSION["role"] ?? null,
            "email" => $_SESSION["email"] ?? null,
        ];
        
        // Check if cookies are being sent
        $hasSessionCookie = isset($_COOKIE[session_name()]);
        $sessionCookieValue = $_COOKIE[session_name()] ?? null;
        
        // Debug info
        $allCookies = $_COOKIE ?? [];
        $allSessionVars = $_SESSION ?? [];
        
        return Response::json(
            $isAuthenticated, 
            $isAuthenticated ? "Session is valid" : "No active session or session expired",
            [
                "session_id" => $sessionId ?: null,
                "user_id" => $_SESSION["user_id"] ?? null,
                "role" => $_SESSION["role"] ?? null,
                "session_status" => $sessionStatus,
                "is_authenticated" => $isAuthenticated,
                "has_session_cookie" => $hasSessionCookie,
                "session_name" => session_name(),
                "session_data" => $sessionData,
                "debug" => [
                    "cookie_matches_session" => $sessionCookieValue === $sessionId,
                    "session_vars_count" => count($allSessionVars),
                    "cookies_received" => array_keys($allCookies)
                ]
            ]
        );
    }
}
