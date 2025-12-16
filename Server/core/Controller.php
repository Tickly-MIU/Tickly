<?php
class Controller {
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Basic model loader by class name.
     */
    protected function model($name) {
        $candidates = [
            __DIR__ . "/../models/{$name}.php"
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists($name)) {
                    return new $name();
                }
            }
        }

        throw new Exception("Model {$name} not found");
    }
    
// Log activity
protected function logActivity($user_id, $action)
{
    require_once __DIR__ . '/../models/ActivityLog.php';
    $log = new ActivityLog();
    $log->create([
        'user_id' => $user_id,
        'action'  => $action
    ]);
}


}



?>