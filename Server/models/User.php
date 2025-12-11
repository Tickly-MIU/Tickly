<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // Check if a user already exists by email
    public function exists($email)
    {
        $query = "SELECT 1 FROM users WHERE email = ? LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result && $result->num_rows > 0;
    }

    // Register User (expects password_hash already hashed)
    public function register($data)
    {
        $query = "INSERT INTO users (full_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $data['full_name'], $data['email'], $data['password_hash'], $data['role']);

        return $stmt->execute();
    }

    public function getByEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }

    public function update($id, $data)
    {
        $query = "UPDATE users SET full_name = ?, email = ?, password_hash = ?, role = ? WHERE user_id = ?";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $data['full_name'], $data['email'], $data['password_hash'], $data['role'], $id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM users WHERE user_id = ?";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
?>
