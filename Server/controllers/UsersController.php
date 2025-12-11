<?php
require_once __DIR__ . '/../core/Controller.php';

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
    // Logout User
    // ---------------------------
    public function logout($data = [])
    {
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
}
