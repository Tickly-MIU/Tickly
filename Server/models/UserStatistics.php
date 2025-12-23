<?php
require_once __DIR__ . '/../config/database.php';

class UserStatistics
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // Create or initialize user statistics
    public function create($user_id)
    {
        // Check if statistics already exist
        $existing = $this->getByUserId($user_id);
        if ($existing) {
            return true;
        }

        $query = "INSERT INTO user_statistics (user_id, total_tasks, completed_tasks, last_login) VALUES (?, 0, 0, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // Get statistics by user_id
    public function getByUserId($user_id)
    {
        $query = "SELECT * FROM user_statistics WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }

    // Update total_tasks count
    public function incrementTotalTasks($user_id)
    {
        $query = "UPDATE user_statistics SET total_tasks = total_tasks + 1 WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // Update completed_tasks count
    public function incrementCompletedTasks($user_id)
    {
        $query = "UPDATE user_statistics SET completed_tasks = completed_tasks + 1 WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // Decrement completed_tasks count (when task status changes from completed)
    public function decrementCompletedTasks($user_id)
    {
        $query = "UPDATE user_statistics SET completed_tasks = GREATEST(completed_tasks - 1, 0) WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // Decrement total_tasks count (when task is deleted)
    public function decrementTotalTasks($user_id)
    {
        $query = "UPDATE user_statistics SET total_tasks = GREATEST(total_tasks - 1, 0) WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // Update last_login
    public function updateLastLogin($user_id)
    {
        $query = "UPDATE user_statistics SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // Get all statistics (for admin)
    public function getAll()
    {
        $query = "SELECT * FROM user_statistics ORDER BY total_tasks DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Sync statistics based on actual task counts (helper method)
    public function syncStatistics($user_id)
    {
        // Get actual counts from tasks table
        $countQuery = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                      FROM tasks 
                      WHERE user_id = ?";
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->bind_param("i", $user_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $counts = $countResult->fetch_assoc();

        // Update statistics
        $query = "UPDATE user_statistics 
                  SET total_tasks = ?, completed_tasks = ? 
                  WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $counts['total'], $counts['completed'], $user_id);

        return $stmt->execute();
    }
}
?>

