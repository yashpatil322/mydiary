<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../loginfrontend.php?error=" . urlencode("Please log in first"));
    exit();
}

require_once '/helpers/encryption.php'; // Inside dashboard/helpers/
require_once '/../db.php'; // One level up to root

$username = $_SESSION['username'];
$entryId = isset($_GET['entry_id']) ? intval($_GET['entry_id']) : 0;

// Fetch entry from DB
$sql = "SELECT * FROM diary_entries WHERE id = ? AND username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $entryId, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Entry not found!";
    echo $username."<br>";
    echo $entryId;
    exit();
}

$row = $result->fetch_assoc();

// Decrypt all fields
$iv = substr(base64_decode($row['iv'] . str_repeat('=', 2 - strlen($row['iv']) % 4)), 0, 16);
$decryptedTitle = decryptData($row['encrypted_title']);
$decryptedEntry = decryptData($row['encrypted_entry']);
$decryptedMood = decryptData($row['encrypted_mood']);
$decryptedWeather = decryptData($row['encrypted_weather']);
$decryptedEnergy = decryptData($row['encrypted_energy_level']);
$decryptedSocial = decryptData($row['encrypted_social_interaction']);
$entryDate = $row['entry_date'];
$entryTime = $row['entry_time'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Diary Entries</title>
  <link rel="icon" type="image/x-icon" href="icon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Roboto&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background: #f4f1ee url('https://www.transparenttextures.com/patterns/paper-fibers.png');
      background-size: auto;
      padding: 5vw;
    }

    .entry-card {
      background: #fffefb;
      border-radius: 20px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      padding: 5vw;
      border: 1px solid #e4dcd1;
      max-width: 900px;
      margin: auto;
    }

    .entry-title {
      font-size: 6vw;
      font-weight: bold;
      margin-bottom: 2vw;
      font-family: 'Indie Flower', cursive;
      color: #5a4e3c;
    }

    .entry-meta {
      font-size: 4vw;
      color: #777;
      margin-bottom: 3vw;
      font-family: 'Indie Flower', cursive;
    }

    .entry-text {
      font-size: 5vw;
      line-height: 1.7;
      font-family: 'Indie Flower',cursive;
      color: #3f3f3f;
      white-space: pre-wrap;
      margin-bottom: 4vw;
      overflow-wrap: break-word;
      word-wrap: break-word;
    }

    .entry-tags {
      font-size: 4.5vw;
      font-family: 'Indie Flower', cursive;
      color: #4d4d4d;
    }

    .entry-tags div {
      margin-bottom: 2vw;
    }

    /* Responsive adjustments for larger screens (PC) */
    @media (min-width: 768px) {
       body {
         padding: 40px;
       }

      .entry-card {
         padding: 40px; /* Adjust padding for desktop */
      }

      .entry-title {
        font-size: 2.2rem; /* Use a fixed rem size for desktop */
        margin-bottom: 1.5rem; /* Adjust margin for desktop */
      }

    .entry-meta {
       font-size: 1rem;
       margin-bottom: 2rem;
      }

      .entry-text {
        font-size: 1.3rem;
        margin-bottom: 2rem;
       }

      .entry-tags {
         font-size: 1.2rem;
      }

      .entry-tags div {
       margin-bottom: 10px;
      }
    }

/* Styling for the buttons to make them responsive */
    .button-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 30px;
    }

    @media (min-width: 768px) {
      .button-container {
        flex-direction: row;
        gap: 20px;
       }
    }

    .button-style {
      padding: 12px 24px;
      background-color: #653affff;
      color: white;
      font-size: 1rem;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-family: 'Indie Flower', cursive;
      text-decoration: none; /* Remove underline from anchor tag */
      margin-bottom: 10px; /* Spacing between buttons on mobile */
      display: flex; /* Make anchor tags behave like block elements */
      margin-right: 10px;
    }

    @media (min-width: 768px) {
      .button-style {
        margin-bottom: 0; /* Remove mobile spacing */
      }
    }
    .button-container a{
      text-decoration: none;
    }
</style>
</head>
<body>
  <div class="entry-card">
    <div class="entry-title">📓 <?php echo htmlspecialchars($decryptedTitle); ?></div>
    <div class="entry-meta">Date: <?php echo htmlspecialchars($entryDate); ?> | Time: <?php echo htmlspecialchars($entryTime); ?></div>
    <div class="entry-text"><?php echo nl2br(htmlspecialchars($decryptedEntry)); ?></div>
    <div class="entry-tags">
       <div>Mood: <?php echo htmlspecialchars($decryptedMood); ?></div>
      <div>Weather: <?php echo htmlspecialchars($decryptedWeather); ?></div>
      <div>Energy: <?php echo htmlspecialchars($decryptedEnergy); ?></div>
      <div>Social: <?php echo htmlspecialchars($decryptedSocial); ?></div>
    </div>
  </div>

  <!-- Button Container -->
  <div class="button-container">
    <a href="/dashboard/paper2.php?entry_id=<?php echo $entryId; ?>">
      <button class="button-style">
        ✨ View Collage
      </button>
    </a>
    <a href="/dashboard/entries.php">
      <button class="button-style">
         ✒️ View Entries
      </button>
    </a>
  </div>
</body>
</html>