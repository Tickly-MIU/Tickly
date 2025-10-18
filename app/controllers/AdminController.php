<?php
// AdminController: admin-only pages (requires role check).
class AdminController {
    public function dashboard() {
        if (!AuthHelper::requireLogin()) {
            echo '<p>User sessions available but sign-in UI removed. Admin dashboard unavailable.</p>';
            return;
        }
        // only allow admin role
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $stats = new Statistics();
        $activity = new ActivityLog();
        $data = [
            'totalUsers' => $stats->totalUsers(),
            'recentLogs' => $activity->recent(20),
        ];

        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/admin/dashboard.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
