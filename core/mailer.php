<?php
// Removed dependency on vendor/autoload.php

class Mailer
{
    public static function send($to, $subject, $body)
    {
        $config = require __DIR__ . '/../config/mail.php';
        $smtp = $config['smtp'];

        $host = $smtp['host'];
        $port = $smtp['port'];
        $username = $smtp['username'];
        $password = $smtp['password']; // This should be the App Password
        $fromEmail = $smtp['from_email'];
        $fromName = $smtp['from_name'];

        try {
            // Open socket
            // For Gmail TLS on 587, we usually connect with tcp first then upgrade, or use tls:// wrapper if 465.
            // But standard simple behavior for 587 is STARTTLS. 
            // Let's try flexible connection.
            
            $socket = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 30);
            if (!$socket) {
                throw new Exception("Could not connect to SMTP host: $errstr ($errno)");
            }

            self::readResponse($socket); // banner (220)

            self::sendCommand($socket, "EHLO " . gethostname(), 250);
            self::sendCommand($socket, "STARTTLS", 220);
            
            // Upgrade to TLS
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable crypto (TLS)");
            }

            self::sendCommand($socket, "EHLO " . gethostname(), 250);
            self::sendCommand($socket, "AUTH LOGIN", 334);
            self::sendCommand($socket, base64_encode($username), 334);
            $authResponse = self::sendCommand($socket, base64_encode($password));
            if (strpos($authResponse, '235') === false) {
                throw new Exception("SMTP authentication failed. Response: " . trim($authResponse));
            }

            self::sendCommand($socket, "MAIL FROM: <{$fromEmail}>", 250);
            self::sendCommand($socket, "RCPT TO: <{$to}>", 250);
            
            self::sendCommand($socket, "DATA", 354);

            // Headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: {$subject}\r\n";

            // Send Body (ensure proper line endings)
            $message = $headers . "\r\n\r\n" . $body . "\r\n.\r\n";
            fwrite($socket, $message);
            // Read response after sending data (should be 250)
            $dataResponse = self::readResponse($socket);
            if (strpos($dataResponse, '250') === false) {
                throw new Exception("SMTP DATA command failed. Response: " . trim($dataResponse));
            }

            self::sendCommand($socket, "QUIT", 221);
            fclose($socket);

            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }

    private static function sendCommand($socket, $command, $expectedCode = null)
    {
        fwrite($socket, $command . "\r\n");
        $response = self::readResponse($socket);
        
        // Validate response code if expected code is provided
        if ($expectedCode !== null && !empty($response)) {
            $code = (int)substr($response, 0, 3);
            if ($code !== $expectedCode) {
                throw new Exception("SMTP command failed. Expected {$expectedCode}, got {$code}. Response: " . trim($response));
            }
        }
        
        return $response;
    }

    private static function readResponse($socket)
    {
        $response = "";
        $timeout = 30; // 30 second timeout
        $startTime = time();
        
        while (true) {
            if (time() - $startTime > $timeout) {
                throw new Exception("SMTP response timeout");
            }
            
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            
            $response .= $line;
            // Check if this is the last line of the response (space after 3 digits)
            if (strlen($line) >= 4 && substr($line, 3, 1) == " ") {
                break;
            }
        }
        
        if (empty($response)) {
            throw new Exception("No response from SMTP server");
        }
        
        return $response;
    }
}
?>