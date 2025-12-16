<?php
require_once __DIR__ . '/../config/database.php';

class Category
{
    private $conn;

    public function __construct()
    {
        // mysqli connection like Tasks model
        $this->conn = (new Database())->connect();
    }

    // Create a category
    public function create($data)
    {
        $query = "INSERT INTO categories (category_name, user_id) VALUES (?, ?)";
        $stmt  = $this->conn->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param("si", $data['category_name'], $data['user_id']);
        return $stmt->execute();
    }

    // Get all categories
    public function getAll()
    {
        $query = "SELECT * FROM categories";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get single category by ID
    public function getById($data)
    {
        $query = "SELECT * FROM categories WHERE category_id = ? LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $data['category_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }

    // Update a category
    public function update($data)
    {
        $existing = $this->getById($data);
        if (!$existing) return false;

        $category_name = $data['category_name'] ?? $existing['category_name'];

        $query = "UPDATE categories SET category_name = ? WHERE category_id = ?";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("si", $category_name, $data['category_id']);

        return $stmt->execute();
    }

    // Delete a category
    public function delete($data)
    {
        $query = "DELETE FROM categories WHERE category_id = ?";
        $stmt  = $this->conn->prepare($query);
        $stmt->bind_param("i", $data['category_id']);

        return $stmt->execute();
    }
}
