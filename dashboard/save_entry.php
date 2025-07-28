<?php
session_start();
require_once '../db.php';
require_once 'helpers/encryption.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_SESSION['username'];

    // Sanitize inputs
    $entryText = trim($_POST['entry']);
    $entryText;
    $title = trim($_POST['title']);
    $title;
    $mood = $_POST['mood'];
    $weather = $_POST['weather'];
    $energy = $_POST['energy'];
    $social = $_POST['social'];

    // Encrypt each field
    $encEntry = encryptData($entryText);
    $encTitle = encryptData($title);
    $encMood = encryptData($mood);
    $encWeather = encryptData($weather);
    $encEnergy = encryptData($energy);
    $encSocial = encryptData($social);

    // Get IV from the encrypted entry
    $iv = substr(base64_decode($encEntry), 0, 16);
    $ivBase64 = base64_encode($iv);

    // Insert into diary_entries
    $stmt = $conn->prepare("INSERT INTO diary_entries 
        (username, encrypted_entry, encrypted_title, encrypted_mood, encrypted_weather, encrypted_energy_level, encrypted_social_interaction, iv) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", 
        $username, 
        $encEntry, 
        $encTitle, 
        $encMood, 
        $encWeather, 
        $encEnergy, 
        $encSocial, 
        $ivBase64
    );

    if ($stmt->execute()) {
        $entryId = $stmt->insert_id; // Get ID of newly inserted diary entry

// Handle image uploads
if (!empty($_FILES['images']['name'][0])) {
    $captions = $_POST['captions']; // Captions from form input

    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        if (is_uploaded_file($tmpName)) {
            $imageData = file_get_contents($tmpName);
            $imageEnc = encryptData($imageData); // Encrypt image binary
            
            // Fix: Properly assign the trimmed caption
            $caption = isset($captions[$index]) ? trim($captions[$index]) : '';
            
            // Debug: Uncomment to check caption content
            // error_log("Caption for image $index: '$caption'");
            
            $encCaption = encryptData($caption);
            
            $ivImage = substr(base64_decode($imageEnc), 0, 16);
            $ivImageBase64 = base64_encode($ivImage);

            // Insert image into entry_images table
            $imgStmt = $conn->prepare("INSERT INTO entry_images 
                (entry_id, username, encrypted_image, encrypted_caption, iv) 
                VALUES (?, ?, ?, ?, ?)");
            $imgStmt->bind_param("issss", 
                $entryId, 
                $username, 
                $imageEnc, 
                $encCaption, 
                $ivImageBase64
            );
            $imgStmt->execute();
            $imgStmt->close();
        }
    }
}
header("Location: entries.php");
exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
