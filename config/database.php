<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $port;

    public function __construct() {
        // Use environment variables (Heroku/Aiven) or fallback to defaults (local development)
        $this->host = getenv('DB_HOST');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASS');
        $this->dbname = getenv('DB_NAME');
        $this->port = getenv('DB_PORT');
    }

    public function connect() {
        // Aiven MySQL requires SSL connection
        // Initialize mysqli with SSL options
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            $conn = new mysqli();
            
            // Set SSL options for Aiven (skip certificate verification for now)
            // For production, you should download and use the CA certificate from Aiven
            $conn->ssl_set(
                null,  // key (not needed for basic SSL)
                null,  // cert (not needed for basic SSL)
                null,  // ca (certificate authority - should be set in production)
                null,  // capath
                null   // cipher
            );
            
            // Connect with SSL
            $conn->real_connect($this->host, $this->user, $this->pass, $this->dbname, $this->port, null, MYSQLI_CLIENT_SSL);
            
            // Verify SSL connection
            $result = $conn->query("SHOW STATUS LIKE 'Ssl_cipher'");
            if ($result) {
                $row = $result->fetch_assoc();
                if (empty($row['Value'])) {
                    error_log("Warning: SSL connection not established with Aiven database");
                }
            }
            
        } catch (mysqli_sql_exception $e) {
            $error = "Database connection failed: " . $e->getMessage();
            
            // Provide helpful error messages
            if (strpos($e->getMessage(), 'getaddrinfo') !== false || 
                strpos($e->getMessage(), 'No such host') !== false) {
                $error .= "\n\n⚠️  DNS/Hostname Error: The database hostname '{$this->host}' cannot be resolved.\n";
                $error .= "Please check your Aiven console for the correct database hostname and connection details.\n";
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                $error .= "\n\n⚠️  Authentication Error: Invalid database credentials.\n";
                $error .= "Please verify your Aiven database username and password.\n";
            } elseif (strpos($e->getMessage(), 'SSL') !== false) {
                $error .= "\n\n⚠️  SSL Connection Error: Aiven requires SSL connections.\n";
                $error .= "Please ensure SSL is properly configured.\n";
            }
            
            throw new Exception($error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    }
}
