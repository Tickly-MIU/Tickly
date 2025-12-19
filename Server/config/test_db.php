<?php
require_once 'Database.php';

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "✅ Database connected successfully";
}
