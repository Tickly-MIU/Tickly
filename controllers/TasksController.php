<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

class TasksController extends Controller
{
    private $taskModel;

    public function __construct()
    {
        AuthMiddleware::requireLogin();
        $this->taskModel = $this->model("Tasks");
    }

    // ---------------------------
    // Create Task
    // ---------------------------
    public function create($data = [])
    {
        if (empty($data["title"])) {
            return Response::json(false, "Task title is required");
        }

        $task = [
            "user_id"     => $_SESSION["user_id"],
            "title"       => htmlspecialchars($data["title"]),
            "description" => htmlspecialchars($data["description"] ?? ""),
            "priority"    => $data["priority"] ?? "medium",
            "category_id" => $data["category_id"] ?? null,
            "deadline"    => $data["deadline"] ?? null
        ];

        $created = $this->taskModel->create($task);

        if ($created) {
            return Response::json(true, "Task created successfully");
        }

        return Response::json(false, "Failed to create task");
    }

    // ---------------------------
    // Read All User Tasks
    // ---------------------------
    public function read($data = [])
    {
        $user_id = $_SESSION["user_id"];
        $tasks = $this->taskModel->getAllByUser($user_id);

        return Response::json(true, "Tasks fetched", $tasks);
    }

    // ---------------------------
    // Read Single Task
    // ---------------------------
    public function readSingle($data = [])
    {
        $task_id = $data['task_id'] ?? $data['id'] ?? null;
        if (!$task_id) {
            return Response::json(false, "Task id is required");
        }

        $task = $this->taskModel->getById((int)$task_id);

        if (!$task) {
            return Response::json(false, "Task not found");
        }

        if ($task["user_id"] != $_SESSION["user_id"]) {
            return Response::json(false, "Unauthorized");
        }

        return Response::json(true, "Task loaded", $task);
    }

    // ---------------------------
    // Update Task
    // ---------------------------
    public function update($data = [])
    {
        $task_id = $data['task_id'] ?? $data['id'] ?? null;
        if (!$task_id) {
            return Response::json(false, "Task id is required");
        }

        $task = $this->taskModel->getById((int)$task_id);

        if (!$task) {
            return Response::json(false, "Task not found");
        }

        if ($task["user_id"] != $_SESSION["user_id"]) {
            return Response::json(false, "Unauthorized");
        }

        $updated = $this->taskModel->update((int)$task_id, $data);

        if ($updated) {
            return Response::json(true, "Task updated successfully");
        }

        return Response::json(false, "Failed to update task");
    }

    // ---------------------------
    // Delete Task
    // ---------------------------
    public function delete($data = [])
    {
        $task_id = $data['task_id'] ?? $data['id'] ?? null;
        if (!$task_id) {
            return Response::json(false, "Task id is required");
        }

        $task = $this->taskModel->getById((int)$task_id);

        if (!$task) {
            return Response::json(false, "Task not found");
        }

        if ($task["user_id"] != $_SESSION["user_id"]) {
            return Response::json(false, "Unauthorized");
        }

        $deleted = $this->taskModel->delete((int)$task_id);

        if ($deleted) {
            return Response::json(true, "Task deleted successfully");
        }

        return Response::json(false, "Failed to delete task");
    }
}
