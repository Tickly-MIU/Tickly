<?php
require_once 'database.php';

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

// Check if database was just created
$just_created = isset($_GET['wait']) ? true : false;

if ($just_created) {
    echo "⏳ Testing connection (database may need time to propagate)...\n\n";
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "✅ Database connected successfully\n";
        echo "📊 Connection Info:\n";
        echo "   - Host: " . $conn->host_info . "\n";
        echo "   - Server Info: " . $conn->server_info . "\n";
        echo "   - Protocol: " . $conn->protocol_version . "\n";
        
        // Test a simple query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            echo "✅ Query test passed\n";
            $row = $result->fetch_assoc();
            echo "   - Test query result: " . $row['test'] . "\n";
        } else {
            echo "⚠️  Query test failed: " . $conn->error . "\n";
        }
        
        // Get database name
        $result = $conn->query("SELECT DATABASE() as dbname");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   - Current database: " . $row['dbname'] . "\n";
        }
        
        $conn->close();
        echo "\n✅ Connection closed successfully\n";
    } else {
        echo "❌ Failed to establish database connection\n";
    }
} catch (Exception $e) {
    $error_msg = $e->getMessage();
    echo "❌ Error: " . $error_msg . "\n";
    echo "\n";
    
    // Check if it's a DNS/hostname error
    if (strpos($error_msg, 'getaddrinfo') !== false || strpos($error_msg, 'No such host') !== false) {
        echo "⏰ Database Propagation Information:\n";
        echo "   - New databases on InfinityFree can take 15-60 minutes to become available\n";
        echo "   - DNS propagation may take additional time\n";
        echo "   - If you just created the database, wait a bit and try again\n";
        echo "\n";
        echo "📋 Steps to Fix:\n";
        echo "1. Wait 15-30 minutes if you just created the database\n";
        echo "2. Log in to your InfinityFree control panel at https://dash.infinityfree.com\n";
        echo "3. Go to 'MySQL Databases' section\n";
        echo "4. Verify your database exists and is active\n";
        echo "5. Check the 'Hostname' field - it should show the correct server\n";
        echo "6. Update the 'host' value in Server/config/database.php with the exact hostname\n";
        echo "7. Common format: sqlXXX.infinityfree.com (where XXX is your server number)\n";
        echo "\n";
        echo "💡 Tip: You can also check phpMyAdmin in InfinityFree - the hostname is usually shown there\n";
        echo "   Or look at the connection string/example code in the database details page.\n";
    } else {
        echo "📋 How to Fix:\n";
        echo "1. Log in to your InfinityFree control panel at https://dash.infinityfree.com\n";
        echo "2. Go to 'MySQL Databases' section\n";
        echo "3. Find your database and check the 'Hostname' field\n";
        echo "4. Verify your database credentials (username, password, database name)\n";
        echo "5. Update Server/config/database.php with the correct information\n";
    }
}

echo "</pre>";
