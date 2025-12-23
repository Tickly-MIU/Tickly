<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';
require_once __DIR__ . '/../core/mailer.php';

class RemindersController extends Controller
{
    private $reminderModel;
    private $taskModel;
    private $userModel;

    public function __construct()
    {
        AuthMiddleware::requireLogin();
        $this->reminderModel = $this->model("Reminders");
        $this->taskModel = $this->model("Tasks");
        $this->userModel = $this->model("Users");
    }

    // ---------------------------
    // Create Reminder
    // ---------------------------
    public function create($data = [])
    {
        $task_id = $data['task_id'] ?? null;
        $reminder_time = $data['reminder_time'] ?? null;

        if (!$task_id || !$reminder_time) {
            return Response::json(false, "Task ID and reminder time are required");
        }

        // Verify task belongs to user
        $task = $this->taskModel->getById((int)$task_id);
        if (!$task) {
            return Response::json(false, "Task not found");
        }

        if ($task['user_id'] != $_SESSION['user_id']) {
            return Response::json(false, "Unauthorized");
        }

        // Verify reminder_time is in the future
        if (strtotime($reminder_time) <= time()) {
            return Response::json(false, "Reminder time must be in the future");
        }

        $created = $this->reminderModel->create([
            'task_id' => (int)$task_id,
            'reminder_time' => $reminder_time
        ]);

        if ($created) {
            $this->logActivity($_SESSION['user_id'], "Created reminder for task ID: {$task_id}");
            return Response::json(true, "Reminder created successfully");
        }

        return Response::json(false, "Failed to create reminder");
    }

    // ---------------------------
    // Get Reminders for User
    // ---------------------------
    public function getMyReminders($data = [])
    {
        $user_id = $_SESSION['user_id'];
        $reminders = $this->reminderModel->getByUserId($user_id);

        return Response::json(true, "Reminders fetched successfully", $reminders);
    }

    // ---------------------------
    // Get Reminders for Task
    // ---------------------------
    public function getByTask($data = [])
    {
        $task_id = $data['task_id'] ?? null;
        if (!$task_id) {
            return Response::json(false, "Task ID is required");
        }

        // Verify task belongs to user
        $task = $this->taskModel->getById((int)$task_id);
        if (!$task) {
            return Response::json(false, "Task not found");
        }

        if ($task['user_id'] != $_SESSION['user_id']) {
            return Response::json(false, "Unauthorized");
        }

        $reminders = $this->reminderModel->getByTaskId((int)$task_id);

        return Response::json(true, "Reminders fetched successfully", $reminders);
    }

    // ---------------------------
    // Update Reminder
    // ---------------------------
    public function update($data = [])
    {
        $reminder_id = $data['reminder_id'] ?? null;
        $reminder_time = $data['reminder_time'] ?? null;

        if (!$reminder_id || !$reminder_time) {
            return Response::json(false, "Reminder ID and reminder time are required");
        }

        $reminder = $this->reminderModel->getById((int)$reminder_id);
        if (!$reminder) {
            return Response::json(false, "Reminder not found");
        }

        // Verify task belongs to user
        $task = $this->taskModel->getById($reminder['task_id']);
        if (!$task || $task['user_id'] != $_SESSION['user_id']) {
            return Response::json(false, "Unauthorized");
        }

        // Verify reminder_time is in the future
        if (strtotime($reminder_time) <= time()) {
            return Response::json(false, "Reminder time must be in the future");
        }

        $updated = $this->reminderModel->update((int)$reminder_id, [
            'reminder_time' => $reminder_time
        ]);

        if ($updated) {
            $this->logActivity($_SESSION['user_id'], "Updated reminder ID: {$reminder_id}");
            return Response::json(true, "Reminder updated successfully");
        }

        return Response::json(false, "Failed to update reminder");
    }

    // ---------------------------
    // Delete Reminder
    // ---------------------------
    public function delete($data = [])
    {
        $reminder_id = $data['reminder_id'] ?? null;
        if (!$reminder_id) {
            return Response::json(false, "Reminder ID is required");
        }

        $reminder = $this->reminderModel->getById((int)$reminder_id);
        if (!$reminder) {
            return Response::json(false, "Reminder not found");
        }

        // Verify task belongs to user
        $task = $this->taskModel->getById($reminder['task_id']);
        if (!$task || $task['user_id'] != $_SESSION['user_id']) {
            return Response::json(false, "Unauthorized");
        }

        $deleted = $this->reminderModel->delete((int)$reminder_id);

        if ($deleted) {
            $this->logActivity($_SESSION['user_id'], "Deleted reminder ID: {$reminder_id}");
            return Response::json(true, "Reminder deleted successfully");
        }

        return Response::json(false, "Failed to delete reminder");
    }

    // ---------------------------
    // Send Reminder Notifications (for cron job or manual trigger)
    // ---------------------------
    public function sendNotifications($data = [])
    {
        // This can be called by admin or via cron job
        // Get all due reminders
        $dueReminders = $this->reminderModel->getDueReminders();

        if (empty($dueReminders)) {
            return Response::json(true, "No due reminders found", [
                'sent' => 0,
                'failed' => 0,
                'total' => 0
            ]);
        }

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($dueReminders as $reminder) {
            try {
                $emailSent = $this->sendReminderEmail($reminder);
                
                if ($emailSent) {
                    $this->reminderModel->markAsSent($reminder['reminder_id']);
                    $sentCount++;
                } else {
                    $failedCount++;
                    $errors[] = "Failed to send to: " . ($reminder['email'] ?? 'unknown');
                }
            } catch (Exception $e) {
                $failedCount++;
                $errors[] = "Error sending to {$reminder['email']}: " . $e->getMessage();
                error_log("RemindersController: Error processing reminder ID {$reminder['reminder_id']}: " . $e->getMessage());
            }
        }

        return Response::json(true, "Notification processing completed", [
            'sent' => $sentCount,
            'failed' => $failedCount,
            'total' => count($dueReminders),
            'errors' => $errors
        ]);
    }

    // ---------------------------
    // Send Email Notification for Upcoming Deadline
    // ---------------------------
    public function sendDeadlineNotifications($data = [])
    {
        // Get tasks with upcoming deadlines (next 24 hours by default)
        $hoursAhead = isset($data['hours_ahead']) ? (int)$data['hours_ahead'] : 24;
        $tasks = $this->taskModel->getTasksWithUpcomingDeadlines($hoursAhead);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($tasks as $task) {
            $emailSent = $this->sendDeadlineEmail($task);
            
            if ($emailSent) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        return Response::json(true, "Deadline notifications sent", [
            'sent' => $sentCount,
            'failed' => $failedCount,
            'total' => count($tasks)
        ]);
    }

    // ---------------------------
    // Private: Send Reminder Email
    // ---------------------------
    private function sendReminderEmail($reminder)
    {
        try {
            $userEmail = $reminder['email'] ?? null;
            $userName = htmlspecialchars($reminder['full_name'] ?? 'User');
            $taskTitle = htmlspecialchars($reminder['title'] ?? 'Task');
            $deadline = $reminder['deadline'] ?? null;
            $reminderTime = $reminder['reminder_time'] ?? null;
            $description = htmlspecialchars($reminder['description'] ?? '');
            $priority = htmlspecialchars($reminder['priority'] ?? 'medium');

            if (!$userEmail || !$deadline || !$reminderTime) {
                error_log("RemindersController: Missing required fields for reminder email");
                return false;
            }

            $subject = "Reminder: Task '{$taskTitle}' - Deadline Approaching";

            $deadlineFormatted = date('F j, Y g:i A', strtotime($deadline));
            $reminderTimeFormatted = date('F j, Y g:i A', strtotime($reminderTime));

            $descriptionHtml = !empty($description) ? "<p>" . nl2br($description) . "</p>" : "";

            $body = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border-radius: 0 0 5px 5px; }
        .task-info { background-color: white; padding: 20px; margin: 20px 0; border-left: 4px solid #4F46E5; border-radius: 4px; }
        .deadline { color: #DC2626; font-weight: bold; font-size: 18px; }
        .priority { display: inline-block; padding: 5px 10px; border-radius: 4px; margin-top: 10px; }
        .priority-high { background-color: #FEE2E2; color: #991B1B; }
        .priority-medium { background-color: #FEF3C7; color: #92400E; }
        .priority-low { background-color: #D1FAE5; color: #065F46; }
        .footer { text-align: center; margin-top: 30px; color: #6B7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>📋 Task Reminder</h1>
        </div>
        <div class='content'>
            <p>Hello {$userName},</p>
            <p>This is a reminder about your upcoming task deadline:</p>
            
            <div class='task-info'>
                <h2>{$taskTitle}</h2>
                {$descriptionHtml}
                <p class='deadline'>⏰ Deadline: {$deadlineFormatted}</p>
                <p>Reminder Time: {$reminderTimeFormatted}</p>
                <span class='priority priority-{$priority}'>Priority: " . ucfirst($priority) . "</span>
            </div>
            
            <p>Please make sure to complete this task before the deadline.</p>
            
            <div class='footer'>
                <p>This is an automated reminder from Tickly Task Management System.</p>
            </div>
        </div>
    </div>
</body>
</html>";

            $result = Mailer::send($userEmail, $subject, $body);
            
            if (!$result) {
                error_log("RemindersController: Failed to send reminder email to {$userEmail} for task: {$taskTitle}");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("RemindersController: Exception in sendReminderEmail: " . $e->getMessage());
            return false;
        }
    }

    // ---------------------------
    // Private: Send Deadline Email
    // ---------------------------
    private function sendDeadlineEmail($task)
    {
        try {
            $userEmail = $task['email'] ?? null;
            $userName = htmlspecialchars($task['full_name'] ?? 'User');
            $taskTitle = htmlspecialchars($task['title'] ?? 'Task');
            $deadline = $task['deadline'] ?? null;
            $description = htmlspecialchars($task['description'] ?? '');
            $priority = htmlspecialchars($task['priority'] ?? 'medium');

            if (!$userEmail || !$deadline) {
                error_log("RemindersController: Missing required fields for deadline email");
                return false;
            }

            $subject = "⚠️ Task Deadline Approaching: '{$taskTitle}'";

            $deadlineFormatted = date('F j, Y g:i A', strtotime($deadline));
            $timeRemaining = htmlspecialchars($this->getTimeRemaining($deadline));

            $descriptionHtml = !empty($description) ? "<p>" . nl2br($description) . "</p>" : "";

            $body = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #DC2626; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border-radius: 0 0 5px 5px; }
        .task-info { background-color: white; padding: 20px; margin: 20px 0; border-left: 4px solid #DC2626; border-radius: 4px; }
        .deadline { color: #DC2626; font-weight: bold; font-size: 18px; }
        .time-remaining { background-color: #FEE2E2; padding: 15px; border-radius: 4px; margin: 15px 0; text-align: center; font-size: 16px; font-weight: bold; }
        .priority { display: inline-block; padding: 5px 10px; border-radius: 4px; margin-top: 10px; }
        .priority-high { background-color: #FEE2E2; color: #991B1B; }
        .priority-medium { background-color: #FEF3C7; color: #92400E; }
        .priority-low { background-color: #D1FAE5; color: #065F46; }
        .footer { text-align: center; margin-top: 30px; color: #6B7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>⚠️ Deadline Approaching</h1>
        </div>
        <div class='content'>
            <p>Hello {$userName},</p>
            <p>You have a task with an approaching deadline:</p>
            
            <div class='task-info'>
                <h2>{$taskTitle}</h2>
                {$descriptionHtml}
                <p class='deadline'>⏰ Deadline: {$deadlineFormatted}</p>
                <div class='time-remaining'>{$timeRemaining}</div>
                <span class='priority priority-{$priority}'>Priority: " . ucfirst($priority) . "</span>
            </div>
            
            <p>Please make sure to complete this task before the deadline.</p>
            
            <div class='footer'>
                <p>This is an automated notification from Tickly Task Management System.</p>
            </div>
        </div>
    </div>
</body>
</html>";

            $result = Mailer::send($userEmail, $subject, $body);
            
            if (!$result) {
                error_log("RemindersController: Failed to send deadline email to {$userEmail} for task: {$taskTitle}");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("RemindersController: Exception in sendDeadlineEmail: " . $e->getMessage());
            return false;
        }
    }

    // ---------------------------
    // Private: Get Time Remaining
    // ---------------------------
    private function getTimeRemaining($deadline)
    {
        $deadlineTime = strtotime($deadline);
        $currentTime = time();
        $diff = $deadlineTime - $currentTime;

        if ($diff <= 0) {
            return "⚠️ Deadline has passed!";
        }

        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);

        if ($hours > 24) {
            $days = floor($hours / 24);
            return "Time remaining: {$days} day(s)";
        } elseif ($hours > 0) {
            return "Time remaining: {$hours} hour(s) and {$minutes} minute(s)";
        } else {
            return "Time remaining: {$minutes} minute(s)";
        }
    }
}

