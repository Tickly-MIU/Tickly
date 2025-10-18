<?php
// ActivityLog model: record and retrieve activity log entries.
class ActivityLog {
    protected $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function log($userId, $action, $meta = null) {
        $stmt = $this->pdo->prepare('INSERT INTO activity_logs (user_id, action, meta, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$userId, $action, $meta]);
        return $this->pdo->lastInsertId();
    }

    public function recent($limit = 50) {
        $stmt = $this->pdo->prepare('SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
