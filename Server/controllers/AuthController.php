<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model("Users");
    }

    public function register($data = [])
    {
        if (empty($data['full_name']) || empty($data['email']) || empty($data['password'])) {
            return Response::json(false, 'All fields are required');
        }

        // Validate full name (matches frontend: minLength 2, maxLength 50)
        $fullName = trim($data['full_name']);
        if (strlen($fullName) < 2) {
            return Response::json(false, 'Full Name must be at least 2 characters');
        }
        if (strlen($fullName) > 50) {
            return Response::json(false, 'Full Name must be less than 50 characters');
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Response::json(false, 'Invalid email format');
        }

        // Validate password:
        // - At least 1 Capital letter
        // - At least 1 Number
        // - At least 1 Special Character
        // - Minimum 8 Characters
        $password = trim($data['password']);
        if (strlen($password) < 8) {
            return Response::json(false, 'Password must be at least 8 characters');
        }
        // Check for at least 1 capital letter
        if (!preg_match('/[A-Z]/', $password)) {
            return Response::json(false, 'Password must contain at least 1 capital letter');
        }
        // Check for at least 1 number
        if (!preg_match('/[0-9]/', $password)) {
            return Response::json(false, 'Password must contain at least 1 number');
        }
        // Check for at least 1 special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\'":\\|,.<>\/?~`]/', $password)) {
            return Response::json(false, 'Password must contain at least 1 special character');
        }

        if ($this->userModel->exists($data['email'])) {
            return Response::json(false, 'Email already registered');
        }

        $userData = [
            'full_name'     => htmlspecialchars($fullName),
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role'          => 'user',
        ];

        $created = $this->userModel->register($userData);

        if ($created) {
            return Response::json(true, 'User registered successfully');
        }

        return Response::json(false, 'Error registering user', [], 500);
    }

    public function login($data = [])
    {
        if (empty($data['email']) || empty($data['password'])) {
            return Response::json(false, 'Email and password required');
        }

        // Validate password (matches frontend: required, minLength 6)
        // Note: No pattern validation for login since we're verifying against hash
        $password = trim($data['password']);
        if (strlen($password) < 6) {
            return Response::json(false, 'Password must be at least 6 characters');
        }

        $user = $this->userModel->getByEmail(trim($data['email']));

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return Response::json(false, 'Invalid email or password', [], 401);
        }

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role']    = $user['role'];
        
        // Regenerate session ID for security
        // This creates a new session ID and invalidates the old one
        session_regenerate_id(true);

        return Response::json(true, 'Login successful', [
            'user' => [
                'id'    => $user['user_id'],
                'name'  => $user['full_name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
            'session_id' => session_id() // Include session ID in response for debugging
        ]);
    }

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
        
        return Response::json(true, 'Logged out successfully');
    }
}
