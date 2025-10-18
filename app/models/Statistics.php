<?php
// Statistics model: compute simple aggregate metrics for admin views.
class Statistics {
    protected $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function totalUsers() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as cnt FROM users');
        $r = $stmt->fetch();
        return $r['cnt'] ?? 0;
    }
}
