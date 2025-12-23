<?php
/**
 * Cron Job Script for Sending Reminder Emails
 * 
 * IMPORTANT: This endpoint requires admin authentication.
 * For cron jobs, use one of these options:
 * 
 * Option 1: Use curl with admin session (not recommended for cron)
 * 
 * Option 2: Create a standalone script (recommended)
 * This script directly calls the controller without going through the router
 */

// Directly instantiate and call the controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/models/Reminders.php';
require_once __DIR__ . '/models/Tasks.php';
require_once __DIR__ . '/models/Users.php';
require_once __DIR__ . '/core/mailer.php';

// Start session (needed for controller)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set admin session (for cron jobs, you may want to use a different auth method)
// WARNING: This is a security risk - consider using a secret token instead
// For production, create a separate endpoint with token authentication

// For now, we'll use a direct approach
try {
    $reminderModel = new Reminders();
    $taskModel = new Tasks();
    $userModel = new Users();
    
    // Get all due reminders
    $dueReminders = $reminderModel->getDueReminders();
    
    if (empty($dueReminders)) {
        echo "No due reminders found.\n";
        exit(0);
    }
    
    echo "Found " . count($dueReminders) . " due reminders.\n";
    
    $sentCount = 0;
    $failedCount = 0;
    
    foreach ($dueReminders as $reminder) {
        // Send email logic (simplified version)
        $userEmail = $reminder['email'];
        $userName = $reminder['full_name'];
        $taskTitle = $reminder['title'];
        $deadline = $reminder['deadline'];
        
        $subject = "Reminder: Task '{$taskTitle}' - Deadline Approaching";
        $body = "Hello {$userName},\n\nThis is a reminder about your task: {$taskTitle}\nDeadline: {$deadline}\n\nPlease complete this task before the deadline.";
        
        if (Mailer::send($userEmail, $subject, $body)) {
            $reminderModel->markAsSent($reminder['reminder_id']);
            $sentCount++;
            echo "Sent email to {$userEmail}\n";
        } else {
            $failedCount++;
            echo "Failed to send email to {$userEmail}\n";
        }
    }
    
    echo "Completed: {$sentCount} sent, {$failedCount} failed\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

