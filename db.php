<?php
// Database connection
$host = 'localhost';
$username = 'root'; // Default username for XAMPP
$password = '';     // Default password for XAMPP
$dbname = 'topup_app'; // Your database name

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>



