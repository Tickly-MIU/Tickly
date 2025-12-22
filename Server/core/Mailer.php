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

            self::readResponse($socket); // banner

            self::sendCommand($socket, "EHLO " . gethostname());
            self::sendCommand($socket, "STARTTLS");
            
            // Upgrade to TLS
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable crypto (TLS)");
            }

            self::sendCommand($socket, "EHLO " . gethostname());
            self::sendCommand($socket, "AUTH LOGIN");
            self::sendCommand($socket, base64_encode($username));
            self::sendCommand($socket, base64_encode($password));

            self::sendCommand($socket, "MAIL FROM: <{$fromEmail}>");
            self::sendCommand($socket, "RCPT TO: <{$to}>");
            
            self::sendCommand($socket, "DATA");

            // Headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: {$subject}\r\n";

            // Send Body
            fwrite($socket, "$headers\r\n$body\r\n.\r\n");
            self::readResponse($socket);

            self::sendCommand($socket, "QUIT");
            fclose($socket);

            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }

    private static function sendCommand($socket, $command)
    {
        fwrite($socket, $command . "\r\n");
        return self::readResponse($socket);
    }

    private static function readResponse($socket)
    {
        $response = "";
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) == " ") {
                break;
            }
        }
        return $response;
    }
}
?>
