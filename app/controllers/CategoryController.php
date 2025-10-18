<?php
class CategoryController {
    public function index() {
        if (!AuthHelper::requireLogin()) {
            echo '<p>User sessions available but sign-in UI removed. Cannot show categories.</p>';
            return;
        }
        $pdo = Database::getInstance();
        $stmt = $pdo->query('SELECT * FROM categories');
        $categories = $stmt->fetchAll();
        require_once __DIR__ . '/../views/layouts/header.php';
        // simple inline view
        echo '<h2>Categories</h2>';
        echo '<ul>';
        foreach ($categories as $c) {
            echo '<li>' . htmlspecialchars($c['name']) . '</li>';
        }
        echo '</ul>';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
