<?php
include(__DIR__ . '/db.php'); 
require_once __DIR__ . '/dashboard/helpers/encryption.php';
// Get form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirm_password = trim($_POST['confirm_password']);
$auspicious_day_info = trim($_POST['auspicious_day_info']);
$current_aim = trim($_POST['current_aim']);
$encrypted_auspicious_day = encryptData($auspicious_day_info);
$encrypted_aim = encryptData($current_aim);

$encrypted_profile_picture_data = null;
    
    // Check if a file was uploaded and there were no errors.
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        header("Location: https://mydiary.gt.tc/signup.php?error=" . urlencode("Profile picture is required."));
        exit();
    }

// Read the file content
$image_content = file_get_contents($_FILES['profile_picture']['tmp_name']);
    
// Encrypt the image data using the function
$encrypted_profile_picture_data = encryptData($image_content);
$aim_last_set_date = date('Y-m-d H:i:s');

// Check if username already exists
$username_check = $conn->prepare("SELECT * FROM users WHERE username = ?");
$username_check->bind_param("s", $username);
$username_check->execute();
$username_result = $username_check->get_result();

// Username exists
if ($username_result->num_rows > 0) {
    header("Location:https://mydiary.gt.tc/signup.php?error=Username already exists");
    exit();
}

$email_check=$conn->prepare("SELECT * FROM users WHERE email=?");
$email_check->bind_param("s",$email);
$email_check->execute();
$email_result=$email_check->get_result();

// Email exists
if ($email_result->num_rows > 0) {
    header("Location:https://mydiary.gt.tc/signup.php?error=Email already registered");
    exit();
}
// Insert new user
$password_hash=password_hash($password,PASSWORD_DEFAULT);
$insert = $conn->prepare("INSERT INTO users (username, email, profile_picture, auspicious_day_info, current_aim, aim_last_set_date,password) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("sssssss", $username, $email, $encrypted_profile_picture_data, $encrypted_auspicious_day, $encrypted_aim, $aim_last_set_date, $password_hash); 

if ($insert->execute()) {
    header("Location:https://mydiary.gt.tc/loginfrontend.php?registered=true");
} else {
    echo "<script>
        alert('⚠️ Registration failed. Please try again.');
        window.history.back();
    </script>";
}
?>
