<?php
session_start();
include '../backend/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    
    if (password_verify($password, $row['password'])) {
        // Correct login
        $_SESSION['username']=$username;
        header("Location: ../backend/dashboard/dashboard.php");
        exit();
    } else {
        // Wrong password
        $error = urlencode("Incorrect password. Please try again.");
        header("Location: ../frontend/login.html?error=$error");
        exit();
    }
} else {
    // Username not found
    $error = urlencode("Username not found. Please sign up.");
    header("Location: ../frontend/login.html?error=$error");
    exit();
}
?>
