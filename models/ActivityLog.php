<?php
require_once __DIR__ . '/../config/database.php';

class ActivityLog
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // Insert a log entry
    public function create($data)
    {
        $query = "INSERT INTO activity_logs (user_id, action, log_time) VALUES (?, ?, NOW())";
        $stmt  = $this->conn->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param("is", $data['user_id'], $data['action']);
        return $stmt->execute();
    }

    // Optional: get logs for a specific user
    public function getByUser($user_id)
    {
        $query = "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY log_time DESC";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Optional: get all logs
    public function getAll()
    {
        $query = "SELECT * FROM activity_logs ORDER BY log_time DESC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
