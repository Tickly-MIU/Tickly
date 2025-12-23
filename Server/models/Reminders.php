<?php
require_once __DIR__ . '/../config/database.php';

class Reminders
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // Create a reminder
    public function create($data)
    {
        $query = "INSERT INTO reminders (task_id, reminder_time, sent) VALUES (?, ?, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $data['task_id'], $data['reminder_time']);

        return $stmt->execute();
    }

    // Get reminder by ID
    public function getById($reminder_id)
    {
        $query = "SELECT * FROM reminders WHERE reminder_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $reminder_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }

    // Get all reminders for a task
    public function getByTaskId($task_id)
    {
        $query = "SELECT * FROM reminders WHERE task_id = ? ORDER BY reminder_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get all reminders for a user (via task user_id)
    public function getByUserId($user_id)
    {
        $query = "SELECT r.*, t.title, t.deadline, t.status, u.email, u.full_name
                  FROM reminders r
                  INNER JOIN tasks t ON r.task_id = t.task_id
                  INNER JOIN users u ON t.user_id = u.user_id
                  WHERE t.user_id = ?
                  ORDER BY r.reminder_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get reminders that are due to be sent (reminder_time <= NOW() and sent = 0)
    public function getDueReminders()
    {
        $query = "SELECT r.*, t.title, t.deadline, t.status, t.description, t.priority,
                         u.email, u.full_name, u.user_id
                  FROM reminders r
                  INNER JOIN tasks t ON r.task_id = t.task_id
                  INNER JOIN users u ON t.user_id = u.user_id
                  WHERE r.reminder_time <= NOW() 
                  AND r.sent = 0
                  AND t.status != 'completed'
                  ORDER BY r.reminder_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Mark reminder as sent
    public function markAsSent($reminder_id)
    {
        $query = "UPDATE reminders SET sent = 1 WHERE reminder_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $reminder_id);

        return $stmt->execute();
    }

    // Update reminder
    public function update($reminder_id, $data)
    {
        $existing = $this->getById($reminder_id);
        if (!$existing) {
            return false;
        }

        $reminder_time = $data['reminder_time'] ?? $existing['reminder_time'];
        $sent = $data['sent'] ?? $existing['sent'];

        $query = "UPDATE reminders SET reminder_time = ?, sent = ? WHERE reminder_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $reminder_time, $sent, $reminder_id);

        return $stmt->execute();
    }

    // Delete reminder
    public function delete($reminder_id)
    {
        $query = "DELETE FROM reminders WHERE reminder_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $reminder_id);

        return $stmt->execute();
    }

    // Delete all reminders for a task
    public function deleteByTaskId($task_id)
    {
        $query = "DELETE FROM reminders WHERE task_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $task_id);

        return $stmt->execute();
    }
}
?>

