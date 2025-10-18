<?php
// User model: DB operations for users table (lookup/create).
class User {
    protected $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($name, $email, $passwordHash) {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
        $role = 'user';
        $stmt->execute([$name, $email, $passwordHash, $role]);
        return $this->pdo->lastInsertId();
    }
}
