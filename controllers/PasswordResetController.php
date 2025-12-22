<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';

class PasswordResetController extends Controller
{
    private $userModel;
    private $resetModel;

    public function __construct()
    {
        $this->userModel = $this->model("User");
        $this->resetModel = $this->model("PasswordReset");
    }

    public function requestReset($data = [])
    {
        if (empty($data['email'])) {
            return Response::json(false, 'Email is required');
        }

        $email = trim($data['email']);
        
        // Check if user exists
        if (!$this->userModel->exists($email)) {
            // Rate limiting or obfuscation could be here, but for now we return error
            return Response::json(false, 'User with this email does not exist', [], 404);
        }

        // Generate Token
        $token = bin2hex(random_bytes(32));

        // Save token
        if ($this->resetModel->createToken($email, $token)) {
            require_once __DIR__ . '/../core/mailer.php';
            
            $resetLink = "https://tickly-3f3fb62f8bf7.herokuapp.com/reset-password?token=" . $token . "&email=" . urlencode($email);
            $message = "<p>Hello,</p><p>You requested a password reset. Click the link below to reset your password:</p>";
            $message .= "<a href='" . $resetLink . "'>" . $resetLink . "</a>";
            $message .= "<p>If you did not request this, please ignore this email.</p>";

            if (Mailer::send($email, 'Password Reset Request', $message)) {
                return Response::json(true, 'Password reset link sent to your email');
            } else {
                return Response::json(false, 'Failed to send email. Please try again later.', [], 500);
            }
        }

        return Response::json(false, 'Failed to generate reset token', [], 500);
    }

    public function resetPassword($data = [])
    {
        if (empty($data['email']) || empty($data['token']) || empty($data['new_password'])) {
            return Response::json(false, 'Email, token, and new password are required');
        }

        $email = trim($data['email']);
        $token = trim($data['token']);
        $newPassword = trim($data['new_password']);

        // Verify Token
        $resetRecord = $this->resetModel->verifyToken($email, $token);
        if (!$resetRecord) {
            return Response::json(false, 'Invalid or expired token', [], 400);
        }

        // Check Expiration (e.g., 1 hour)
        $createdAt = strtotime($resetRecord['created_at']);
        if (time() - $createdAt > 3600) { // 3600 seconds = 1 hour
            // Optionally delete the expired token
             $this->resetModel->deleteToken($email);
            return Response::json(false, 'Token has expired', [], 400);
        }

        // Validate password strength (matches AuthController):
        // - At least 1 Capital letter
        // - At least 1 Number
        // - At least 1 Special Character
        // - Minimum 8 Characters
        if (strlen($newPassword) < 8) {
            return Response::json(false, 'Password must be at least 8 characters');
        }
        // Check for at least 1 capital letter
        if (!preg_match('/[A-Z]/', $newPassword)) {
            return Response::json(false, 'Password must contain at least 1 capital letter');
        }
        // Check for at least 1 number
        if (!preg_match('/[0-9]/', $newPassword)) {
            return Response::json(false, 'Password must contain at least 1 number');
        }
        // Check for at least 1 special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\'":\\|,.<>\/?~`]/', $newPassword)) {
            return Response::json(false, 'Password must contain at least 1 special character');
        }

        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update User Password
        if ($this->userModel->updatePassword($email, $newPasswordHash)) {
            // Delete the token record from the database using the email address
            $this->resetModel->deleteToken($email);
            return Response::json(true, 'Password has been successfully updated');
        }

        return Response::json(false, 'Failed to update password', [], 500);
    }
}
?>
