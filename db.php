<?php
$host = "localhost";     // Replace with your actual MySQL host
$user = "root";               // Your InfinityFree MySQL username
$password = "";           // Your MySQL password
$database = "mydiary";      // Your actual database name

// Step 1: Create a new connection
$conn = new mysqli($host, $user, $password, $database);

// Step 2: Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Stop script if failed
}
else{
}
?>