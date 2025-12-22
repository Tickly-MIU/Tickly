<?php
require_once __DIR__ . '/../config/database.php';

class Tasks
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function create($data)
    {
        $query = "INSERT INTO tasks (user_id, category_id, title, description, priority, deadline, status, created_at, updated_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($query);

        $category_id = $data['category_id'] ?? null;
        $description = $data['description'] ?? null;
        $priority    = $data['priority'] ?? 'medium';
        $deadline    = $data['deadline'] ?? null;
        $status      = $data['status'] ?? 'pending';

        $stmt->bind_param(
            "iisssss",
            $data['user_id'],
            $category_id,
            $data['title'],
            $description,
            $priority,
            $deadline,
            $status
        );

        return $stmt->execute();
    }

    public function getAllByUser($user_id)
    {
        $query = "SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($task_id)
    {
        $query = "SELECT * FROM tasks WHERE task_id = ? LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }

    public function update($task_id, $data)
    {
        $existing = $this->getById($task_id);
        if (!$existing) {
            return false;
        }

        $category_id = $data['category_id'] ?? $existing['category_id'];
        $title       = $data['title']       ?? $existing['title'];
        $description = $data['description'] ?? $existing['description'];
        $priority    = $data['priority']    ?? $existing['priority'];
        $deadline    = $data['deadline']    ?? $existing['deadline'];
        $status      = $data['status']      ?? $existing['status'];

        $query = "UPDATE tasks
                  SET category_id = ?, title = ?, description = ?, priority = ?, deadline = ?, status = ?, updated_at = NOW()
                  WHERE task_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isssssi", $category_id, $title, $description, $priority, $deadline, $status, $task_id);

        return $stmt->execute();
    }

    public function delete($task_id)
    {
        $query = "DELETE FROM tasks WHERE task_id = ?";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $task_id);

        return $stmt->execute();
    }
}
?>