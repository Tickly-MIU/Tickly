<?php
// Controller for the main dashboard page.
class DashboardController {
    public function index() {
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/home.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
