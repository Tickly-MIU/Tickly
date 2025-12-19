<?php
class Database {
    private $host = "sql104.infinityfree.com";
    private $user = "if0_40715852";
    private $pass = "tickly2025";
    private $dbname = "if0_40715852_tickly";
    private $port = 3306;

    public function __construct() {}

    public function connect() {
        // Set connection timeout
        $conn = @new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
        
        if ($conn->connect_error) {
            $error = "Connection failed: " . $conn->connect_error;
            // Provide helpful error message
            if (strpos($conn->connect_error, 'getaddrinfo') !== false || 
                strpos($conn->connect_error, 'No such host') !== false) {
                $error .= "\n\n⚠️  DNS/Hostname Error: The database hostname '{$this->host}' cannot be resolved.\n";
                $error .= "Please check your InfinityFree control panel for the correct database hostname.\n";
                $error .= "Common InfinityFree hostnames: sqlXXX.infinityfree.com (where XXX is your server number)";
            }
            throw new Exception($error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    }
}
