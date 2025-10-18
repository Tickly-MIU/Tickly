<?php
class ReminderController {
    public function index() {
        if (!AuthHelper::requireLogin()) {
            echo '<p>User sessions available but sign-in UI removed. Cannot show reminders.</p>';
            return;
        }
        $rem = new Reminder();
        $reminders = $rem->getAllByUser($_SESSION['user_id']);
        require_once __DIR__ . '/../views/layouts/header.php';
        echo '<h2>Reminders</h2>';
        echo '<ul>';
        foreach ($reminders as $r) {
            echo '<li>' . htmlspecialchars($r['title'] ?? $r['id']) . '</li>';
        }
        echo '</ul>';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
