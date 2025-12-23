<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';
require_once __DIR__ . '/../config/database.php';

class AdminController extends Controller
{
    private $userModel;
    private $activityLogModel;
    private $userStatisticsModel;
    private $taskModel;

    public function __construct()
    {
        AuthMiddleware::requireLogin();
        $this->requireAdmin();
        
        $this->userModel = $this->model("Users");
        $this->activityLogModel = $this->model("ActivityLog");
        $this->userStatisticsModel = $this->model("UserStatistics");
        $this->taskModel = $this->model("Tasks");
    }

    // Check if current user is admin
    private function requireAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            Response::json(false, 'Admin access required', [], 403);
        }
    }

    // ---------------------------
    // Get All Users
    // ---------------------------
    public function getAllUsers($data = [])
    {
        // Get all users (without password hashes) with their statistics
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT u.user_id, u.full_name, u.email, u.role, u.created_at,
                         COALESCE(us.total_tasks, 0) as total_tasks,
                         COALESCE(us.completed_tasks, 0) as completed_tasks,
                         us.last_login
                  FROM users u
                  LEFT JOIN user_statistics us ON u.user_id = us.user_id
                  ORDER BY u.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        // Format the response with additional computed fields
        $formattedUsers = [];
        foreach ($users as $user) {
            $formattedUser = [
                'user_id' => (int)$user['user_id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'created_at' => $user['created_at'],
                'statistics' => [
                    'total_tasks' => (int)$user['total_tasks'],
                    'completed_tasks' => (int)$user['completed_tasks'],
                    'last_login' => $user['last_login']
                ]
            ];
            
            // Calculate completion rate
            $completionRate = $user['total_tasks'] > 0 
                ? round(($user['completed_tasks'] / $user['total_tasks']) * 100, 2) 
                : 0;
            $formattedUser['statistics']['completion_rate'] = $completionRate;
            
            $formattedUsers[] = $formattedUser;
        }

        return Response::json(true, "Users fetched successfully", $formattedUsers);
    }

    // ---------------------------
    // Get User Statistics
    // ---------------------------
    public function getUserStatistics($data = [])
    {
        $statistics = $this->userStatisticsModel->getAll();

        // Join with user information
        $db = new Database();
        $conn = $db->connect();
        
        $result = [];
        foreach ($statistics as $stat) {
            $user = $this->userModel->getById($stat['user_id']);
            if ($user) {
                unset($user['password_hash']);
                $result[] = array_merge($stat, ['user' => $user]);
            }
        }

        return Response::json(true, "Statistics fetched successfully", $result);
    }

    // ---------------------------
    // Get Activity Logs
    // ---------------------------
    public function getActivityLogs($data = [])
    {
        $limit = isset($data['limit']) ? (int)$data['limit'] : 100;
        $user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;

        if ($user_id) {
            $logs = $this->activityLogModel->getByUser($user_id);
        } else {
            $logs = $this->activityLogModel->getAll();
        }

        // Limit results
        $logs = array_slice($logs, 0, $limit);

        // Join with user information
        $db = new Database();
        $conn = $db->connect();
        
        $result = [];
        foreach ($logs as $log) {
            $user = $this->userModel->getById($log['user_id']);
            if ($user) {
                unset($user['password_hash']);
                $result[] = array_merge($log, ['user' => $user]);
            }
        }

        return Response::json(true, "Activity logs fetched successfully", $result);
    }

    // ---------------------------
    // Get System Overview
    // ---------------------------
    public function getSystemOverview($data = [])
    {
        $db = new Database();
        $conn = $db->connect();

        // Total users
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalUsers = $result->fetch_assoc()['total'];

        // Total tasks
        $query = "SELECT COUNT(*) as total FROM tasks";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalTasks = $result->fetch_assoc()['total'];

        // Completed tasks
        $query = "SELECT COUNT(*) as total FROM tasks WHERE status = 'completed'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $completedTasks = $result->fetch_assoc()['total'];

        // Pending tasks
        $query = "SELECT COUNT(*) as total FROM tasks WHERE status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingTasks = $result->fetch_assoc()['total'];

        // Total categories
        $query = "SELECT COUNT(*) as total FROM categories";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalCategories = $result->fetch_assoc()['total'];

        // Tasks with upcoming deadlines (next 24 hours)
        $upcomingTasks = $this->taskModel->getTasksWithUpcomingDeadlines(24);

        // Overdue tasks
        $overdueTasks = $this->taskModel->getOverdueTasks();

        $overview = [
            'users' => [
                'total' => (int)$totalUsers
            ],
            'tasks' => [
                'total' => (int)$totalTasks,
                'completed' => (int)$completedTasks,
                'pending' => (int)$pendingTasks,
                'upcoming_deadlines_24h' => count($upcomingTasks),
                'overdue' => count($overdueTasks)
            ],
            'categories' => [
                'total' => (int)$totalCategories
            ]
        ];

        return Response::json(true, "System overview fetched successfully", $overview);
    }

    // ---------------------------
    // Delete User
    // ---------------------------
    public function deleteUser($data = [])
    {
        $user_id = $data['user_id'] ?? null;
        if (!$user_id) {
            return Response::json(false, "User ID is required");
        }

        // Prevent deleting yourself
        if ((int)$user_id === $_SESSION['user_id']) {
            return Response::json(false, "Cannot delete your own account");
        }

        $deleted = $this->userModel->delete((int)$user_id);

        if ($deleted) {
            $this->logActivity($_SESSION['user_id'], "Deleted user ID: {$user_id}");
            return Response::json(true, "User deleted successfully");
        }

        return Response::json(false, "Failed to delete user");
    }

    // ---------------------------
    // Update User Role
    // ---------------------------
    public function updateUserRole($data = [])
    {
        $user_id = $data['user_id'] ?? null;
        $role = $data['role'] ?? null;

        if (!$user_id || !$role) {
            return Response::json(false, "User ID and role are required");
        }

        // Validate role
        if (!in_array($role, ['user', 'admin'])) {
            return Response::json(false, "Invalid role. Must be 'user' or 'admin'");
        }

        $user = $this->userModel->getById((int)$user_id);
        if (!$user) {
            return Response::json(false, "User not found");
        }

        $updated = $this->userModel->update((int)$user_id, [
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
            'role' => $role
        ]);

        if ($updated) {
            $this->logActivity($_SESSION['user_id'], "Updated user ID {$user_id} role to {$role}");
            return Response::json(true, "User role updated successfully");
        }

        return Response::json(false, "Failed to update user role");
    }

    // ---------------------------
    // Add New Admin
    // ---------------------------
    public function addNewAdmin($data = [])
    {
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = isset($data['password']) ? trim($data['password']) : null;
        $full_name = isset($data['full_name']) ? trim($data['full_name']) : null;

        // Validate required fields
        if (empty($email) || empty($password)) {
            return Response::json(false, "Email and password are required");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::json(false, "Invalid email format");
        }

        // Validate password (same requirements as regular registration)
        if (strlen($password) < 8) {
            return Response::json(false, "Password must be at least 8 characters");
        }
        // Check for at least 1 capital letter
        if (!preg_match('/[A-Z]/', $password)) {
            return Response::json(false, "Password must contain at least 1 capital letter");
        }
        // Check for at least 1 number
        if (!preg_match('/[0-9]/', $password)) {
            return Response::json(false, "Password must contain at least 1 number");
        }
        // Check for at least 1 special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\'":\\|,.<>\/?~`]/', $password)) {
            return Response::json(false, "Password must contain at least 1 special character");
        }

        // Check if email already exists
        if ($this->userModel->exists($email)) {
            return Response::json(false, "Email already registered");
        }

        // Set default full_name if not provided
        if (empty($full_name)) {
            // Extract name from email (part before @) or use a default
            $full_name = explode('@', $email)[0];
            $full_name = ucfirst($full_name); // Capitalize first letter
        }

        // Validate full name length
        if (strlen($full_name) < 2) {
            return Response::json(false, "Full Name must be at least 2 characters");
        }
        if (strlen($full_name) > 50) {
            return Response::json(false, "Full Name must be less than 50 characters");
        }

        // Create admin user
        $userData = [
            'full_name'     => htmlspecialchars($full_name),
            'email'         => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role'          => 'admin',
        ];

        $created = $this->userModel->register($userData);

        if ($created) {
            // Initialize user statistics for the new admin
            $user = $this->userModel->getByEmail($userData['email']);
            if ($user) {
                $this->userStatisticsModel->create($user['user_id']);
            }

            $this->logActivity($_SESSION['user_id'], "Created new admin user: {$email}");
            return Response::json(true, "Admin user created successfully", [
                'user_id' => $user['user_id'] ?? null,
                'email' => $userData['email'],
                'full_name' => $userData['full_name'],
                'role' => 'admin'
            ]);
        }

        return Response::json(false, "Failed to create admin user");
    }
}

