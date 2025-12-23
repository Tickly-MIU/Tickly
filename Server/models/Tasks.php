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

        if ($stmt->execute()) {
            return $this->conn->insert_id; // Return the task_id
        }
        
        return false;
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

    // Get tasks with upcoming deadlines (within specified hours)
    public function getTasksWithUpcomingDeadlines($hoursAhead = 24)
    {
        $query = "SELECT t.*, u.email, u.full_name 
                  FROM tasks t
                  INNER JOIN users u ON t.user_id = u.user_id
                  WHERE t.deadline IS NOT NULL 
                  AND t.deadline <= DATE_ADD(NOW(), INTERVAL ? HOUR)
                  AND t.deadline >= NOW()
                  AND t.status != 'completed'
                  ORDER BY t.deadline ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $hoursAhead);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get all tasks with deadlines (for admin)
    public function getAllWithDeadlines()
    {
        $query = "SELECT t.*, u.email, u.full_name, c.category_name
                  FROM tasks t
                  INNER JOIN users u ON t.user_id = u.user_id
                  LEFT JOIN categories c ON t.category_id = c.category_id
                  WHERE t.deadline IS NOT NULL
                  ORDER BY t.deadline ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get overdue tasks
    public function getOverdueTasks($user_id = null)
    {
        $query = "SELECT t.*, u.email, u.full_name 
                  FROM tasks t
                  INNER JOIN users u ON t.user_id = u.user_id
                  WHERE t.deadline IS NOT NULL 
                  AND t.deadline < NOW()
                  AND t.status != 'completed'";
        
        if ($user_id) {
            $query .= " AND t.user_id = ?";
        }
        
        $query .= " ORDER BY t.deadline ASC";
        
        $stmt = $this->conn->prepare($query);
        if ($user_id) {
            $stmt->bind_param("i", $user_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>