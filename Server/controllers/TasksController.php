<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

class TasksController extends Controller
{
    private $taskModel;
    private $userStatisticsModel;
    private $reminderModel;

    public function __construct()
    {
        AuthMiddleware::requireLogin();
        $this->taskModel = $this->model("Tasks");
        $this->userStatisticsModel = $this->model("UserStatistics");
        $this->reminderModel = $this->model("Reminders");
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

        $task_id = $this->taskModel->create($task);

        if ($task_id) {
            // Initialize user statistics if not exists
            $this->userStatisticsModel->create($task["user_id"]);
            // Update total tasks count
            $this->userStatisticsModel->incrementTotalTasks($task["user_id"]);
            
            // Create automatic reminder 24 hours before deadline if deadline is set
            if (!empty($task["deadline"])) {
                $this->createAutomaticReminder($task_id, $task["deadline"]);
            }
            
            $this->logActivity($task["user_id"], "Created task: " . htmlspecialchars($task["title"]));
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

        $oldStatus = $task['status'];
        $newStatus = $data['status'] ?? $oldStatus;
        $oldDeadline = $task['deadline'];
        $newDeadline = $data['deadline'] ?? $oldDeadline;

        $updated = $this->taskModel->update((int)$task_id, $data);

        if ($updated) {
            // Update statistics if status changed to/from completed
            if ($oldStatus !== $newStatus) {
                if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                    // Task was unmarked as completed
                    $this->userStatisticsModel->decrementCompletedTasks($task['user_id']);
                } elseif ($oldStatus !== 'completed' && $newStatus === 'completed') {
                    // Task was marked as completed
                    $this->userStatisticsModel->incrementCompletedTasks($task['user_id']);
                }
            }
            
            // Handle reminder updates when deadline changes
            if ($oldDeadline !== $newDeadline) {
                if (!empty($newDeadline)) {
                    // Deadline was added or changed - create/update reminder
                    $this->createAutomaticReminder((int)$task_id, $newDeadline);
                } else {
                    // Deadline was removed - delete existing reminders
                    $this->reminderModel->deleteByTaskId((int)$task_id);
                }
            }
            
            $this->logActivity($_SESSION["user_id"], "Updated task ID: {$task_id}");
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
            // Delete all reminders associated with this task
            $this->reminderModel->deleteByTaskId((int)$task_id);
            
            // Update statistics
            $this->userStatisticsModel->decrementTotalTasks($task['user_id']);
            if ($task['status'] === 'completed') {
                $this->userStatisticsModel->decrementCompletedTasks($task['user_id']);
            }
            
            $this->logActivity($_SESSION["user_id"], "Deleted task ID: {$task_id}");
            return Response::json(true, "Task deleted successfully");
        }

        return Response::json(false, "Failed to delete task");
    }

    // ---------------------------
    // Private: Create Automatic Reminder (24 hours before deadline)
    // ---------------------------
    private function createAutomaticReminder($task_id, $deadline)
    {
        // Calculate reminder time: 24 hours before deadline
        $deadlineTimestamp = strtotime($deadline);
        
        if ($deadlineTimestamp === false) {
            error_log("TasksController: Invalid deadline format: {$deadline}");
            return;
        }
        
        $reminderTimestamp = $deadlineTimestamp - (24 * 60 * 60); // Subtract 24 hours
        $currentTime = time();
        
        // Only create reminder if reminder time is in the future (or very recent past, within 5 minutes)
        // This handles cases where there's a small delay between task creation and reminder creation
        if ($reminderTimestamp > ($currentTime - 300)) { // Allow up to 5 minutes past
            $reminderTime = date('Y-m-d H:i:s', $reminderTimestamp);
            
            // Delete any existing reminders for this task (to avoid duplicates)
            $this->reminderModel->deleteByTaskId($task_id);
            
            // Create new reminder
            $created = $this->reminderModel->create([
                'task_id' => $task_id,
                'reminder_time' => $reminderTime
            ]);
            
            if ($created) {
                error_log("TasksController: Created reminder for task {$task_id} at {$reminderTime} (deadline: {$deadline})");
            } else {
                error_log("TasksController: Failed to create reminder for task {$task_id}");
            }
        } else {
            error_log("TasksController: Reminder time {$reminderTimestamp} is too far in the past (current: {$currentTime}) for task {$task_id}");
        }
    }
}
