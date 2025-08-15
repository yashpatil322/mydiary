<?php
// Set the content type to JSON to ensure the browser understands the response.
header('Content-Type: application/json');

// Start the session to access user data.
session_start();

// Include the necessary files for encryption and database connection.
require_once __DIR__ . '/helpers/encryption.php'; // Inside dashboard/helpers/
require_once __DIR__ . '/../db.php'; // One level up to root

// --- SESSION AND AUTHENTICATION CHECK ---
// Ensure the user is logged in before allowing the update.
if (!isset($_SESSION['username'])) {
    // If the username is not in the session, redirect to the login page.
    header("Location: https://mydiary.gt.tc/loginfrontend.php?error=" . urlencode("Please log in first"));
    exit();
}

// Get the authenticated username from the session.
$username = $_SESSION['username'];

// --- GET DATA FROM THE JAVASCRIPT FETCH REQUEST ---
// Read the raw JSON data sent by the client.
$json_data = file_get_contents('php://input');

// Decode the JSON data into a PHP associative array.
$data = json_decode($json_data, true);

// Check if the 'aim' field exists in the decoded data.
if (!isset($data['aim'])) {
    echo json_encode(['success' => false, 'message' => 'No aim data received.']);
    exit();
}

// Store the new aim and encrypt it for security.
$newAim = $data['aim'];
$encrypted_aim = encryptData($newAim);

// --- UPDATE THE DATABASE ---
try {
    // SQL query to update both the current_aim and the aim_last_set_date.
    // The `SET` keyword is crucial for updating column values.
    // We use `NOW()` to automatically set the current timestamp.
    $sql = "UPDATE users SET current_aim = ?, aim_last_set_date = NOW() WHERE username = ?";

    // Prepare the SQL statement to prevent SQL injection.
    $stmt = $conn->prepare($sql);

    // Bind the encrypted aim (string) and the username (string) to the statement.
    // The 'ss' indicates that we are binding two strings.
    $stmt->bind_param("ss", $encrypted_aim, $username);

     // Execute the prepared statement.
    $stmt->execute();

    // Check if the update was successful.
     if ($stmt->affected_rows > 0) {
    // Send a success response.
     echo json_encode(['success' => true, 'message' => 'Aim updated successfully!']);
     } else {
        // This could mean the user's aim was already the same or the username was not found.
        echo json_encode(['success' => false, 'message' => 'Aim is already up to date or user not found.']);
    }

    // Close the statement.
    $stmt->close();

} catch (Exception $e) {
    // If an error occurs, catch the exception and return a failure message.
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}

// Close the database connection.
$conn->close();

?>
