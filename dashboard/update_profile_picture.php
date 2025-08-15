<?php

// Set the content-type header for a JSON response
header('Content-Type: application/json');

// Start the session to access user data.
session_start();

// Include the necessary files for encryption and database connection.
require_once __DIR__ . '/helpers/encryption.php';
require_once __DIR__ . '/../db.php';

// --- SESSION AND AUTHENTICATION CHECK ---
// Ensure the user is logged in before allowing the update.
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, return a JSON error instead of a redirect.
    $response = ['success' => false, 'message' => 'Please log in first.'];
    echo json_encode($response);
    exit();
}

// Get the authenticated username from the session.
$username = $_SESSION['username'];

// Initialize a response array with a default failure state.
$response = ['success' => false, 'message' => ''];

// Check if a file was uploaded successfully.
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'Profile picture is required and must be a valid file.';
    echo json_encode($response);
    exit();
}

// Read the raw binary content of the uploaded temporary file.
$image_content = file_get_contents($_FILES['profile_picture']['tmp_name']);

try {
    // Encrypt the image content.
    $encrypted_image_content = encryptData($image_content);

    // Correct the SQL query to use 'SET'.
    $sql = "UPDATE users SET profile_picture = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $encrypted_image_content, $username);
    
    // Execute the statement and check if it was successful.
    if ($stmt->execute()) {
        // mysqli_stmt_affected_rows returns the number of rows changed.
        // A value greater than 0 means the picture was updated.
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Profile picture updated successfully.';
        } else {
            // This case handles when the query runs successfully but no data was changed.
            $response['success'] = true;
            $response['message'] = 'Profile picture was not changed.';
        }
    } else {
        // If the execution failed, provide an error message.
        $response['message'] = 'Database query failed: ' . $stmt->error;
    }

} catch (Exception $e) {
    // Catch and handle any database-related errors
    $response['message'] = 'Database error: ' . $e->getMessage();
}

// Encode the final response array into JSON and output it.
echo json_encode($response);
exit();
?>
