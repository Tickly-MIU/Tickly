<?php
class AuthController {

    public function register() {
        header("Content-Type: application/json");
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['full_name']) || !isset($input['email']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $full_name = trim($input['full_name']);
        $email = trim($input['email']);
        $password = password_hash(trim($input['password']), PASSWORD_DEFAULT);
        $role = 'user';

        require_once "../config/database.php";
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $full_name, $email, $password, $role);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User registered successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error registering user']);
        }
    }

    public function login() {
        header("Content-Type: application/json");
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['email']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }

        $email = trim($input['email']);
        $password = trim($input['password']);

        require_once "../config/database.php";
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['user_id'],
                'name' => $user['full_name'],
                'email' => $user['email']
            ]
        ]);
    }
}
