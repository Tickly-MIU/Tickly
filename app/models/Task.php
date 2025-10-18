<?php
// Task model: DB operations for tasks table (create, update, fetch by user).
class Task {
    protected $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAllByUser($userId) {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($userId, $title, $description = null, $category_id = null, $due_date = null) {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (user_id, title, description, category_id, due_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $title, $description, $category_id, $due_date]);
        return $this->pdo->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $title, $description = null, $category_id = null, $due_date = null, $status = null) {
        $stmt = $this->pdo->prepare('UPDATE tasks SET title = ?, description = ?, category_id = ?, due_date = ?, status = ? WHERE id = ?');
        return $stmt->execute([$title, $description, $category_id, $due_date, $status, $id]);
    }
}
