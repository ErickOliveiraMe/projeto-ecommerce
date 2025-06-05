<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Adjust if your MySQL has a password
$dbname = 'nexcommerce';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>