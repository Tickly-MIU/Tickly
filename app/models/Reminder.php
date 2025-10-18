<?php
class Reminder {
    protected $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAllByUser($userId) {
        $stmt = $this->pdo->prepare('SELECT * FROM reminders WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
