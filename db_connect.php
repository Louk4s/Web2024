<?php
$servername = "127.0.0.1";
$db_username = "root"; 
$db_password = ""; 
$dbname = "disaster_database"; 
$port = "3306"; // Specified port for MySQL

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
