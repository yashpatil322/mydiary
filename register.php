<?php
include("db.php");

// Get form data
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Check if username already exists
$username_check = $conn->prepare("SELECT * FROM users WHERE username = ?");
$username_check->bind_param("s", $username);
$username_check->execute();
$username_result = $username_check->get_result();

// Username exists
if ($username_result->num_rows > 0) {
    header("Location:signup.php?error=Username already exists");
    exit();
}

$email_check=$conn->prepare("SELECT * FROM users WHERE email=?");
$email_check->bind_param("s",$email);
$email_check->execute();
$email_result=$email_check->get_result();

// Email exists
if ($email_result->num_rows > 0) {
    header("Location:signup.php?error=Email already registered");
    exit();
}
// Insert new user
$password_hash=password_hash($password,PASSWORD_DEFAULT);
$insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$insert->bind_param("sss", $username, $email, $password_hash); 

if ($insert->execute()) {
    echo "<script>
        window.location.href = 'loginfrontend.php?registered=true';
    </script>";
} else {
    echo "<script>
        alert('⚠️ Registration failed. Please try again.');
        window.history.back();
    </script>";
}
?>
