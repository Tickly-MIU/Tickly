<?php
require_once __DIR__ . '/../config/database.php';

class PasswordReset
{
    private $conn;
    private $table = 'password_resets';

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function createToken($email, $token)
    {
        // First delete any existing tokens for this email
        $this->deleteToken($email);

        $query = "INSERT INTO " . $this->table . " (email, token) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $email, $token);
        
        return $stmt->execute();
    }

    public function verifyToken($email, $token)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? AND token = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function deleteToken($email)
    {
        $query = "DELETE FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }
}
?>