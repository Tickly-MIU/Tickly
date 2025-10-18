<?php
// Controller for task CRUD operations. Uses session user_id for scoping.
class TaskController {
    public function index() {
        if (!AuthHelper::requireLogin()) {
            echo '<p>User sessions available but sign-in UI removed. Create sessions via other means.</p>';
            return;
        }
        $taskModel = new Task();
        $tasks = $taskModel->getAllByUser($_SESSION['user_id']);
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/tasks/list.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    public function create() {
        if (!AuthHelper::requireLogin()) {
            echo '<p>User sessions available but sign-in UI removed. Cannot create tasks without a session.</p>';
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? null;
            $taskModel = new Task();
            $taskModel->create($_SESSION['user_id'], $title, $description);
            header('Location: ' . BASE_URL . '/task/index');
            exit;
        }
        require_once __DIR__ . '/../views/tasks/add.php';
    }

    public function edit($id = null) {
        if (!AuthHelper::requireLogin()) {
            echo '<p>User sessions available but sign-in UI removed. Cannot edit tasks without a session.</p>';
            return;
        }
        $taskModel = new Task();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? null;
            $status = $_POST['status'] ?? null;
            $taskModel->update($id, $title, $description, null, null, $status);
            header('Location: ' . BASE_URL . '/task/index');
            exit;
        }
        $task = $taskModel->findById($id);
        require_once __DIR__ . '/../views/tasks/edit.php';
    }
}
