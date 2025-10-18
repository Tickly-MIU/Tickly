<?php
require_once __DIR__ . '../core/database.php';

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($full_name, $email, $password_hash) {
        $stmt = $this->conn->prepare("
            INSERT INTO users (full_name, email, password_hash, role, created_at)
            VALUES (?, ?, ?, 'user', NOW())
        ");
        $stmt->execute([$full_name, $email, $password_hash]);
        return $this->conn->lastInsertId();
    }
}
