<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

require_once 'helpers/encryption.php';
require_once '../db.php';

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Roboto&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;``
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
      font-family: 'Indie Flower', cursive;
      color: #3f3f3f;
      white-space: pre-wrap;
      margin-bottom: 4vw;
    }

    .entry-tags {
      font-size: 4.5vw;
      font-family: 'Indie Flower', cursive;
      color: #4d4d4d;
    }

    .entry-tags div {
      margin-bottom: 2vw;
    }

    @media (min-width: 768px) {
      body {
        padding: 40px;
      }

      .entry-title {
        font-size: 2.2rem;
      }

      .entry-meta {
        font-size: 1rem;
      }

      .entry-text {
        font-size: 1.3rem;
      }

      .entry-tags {
        font-size: 1.2rem;
      }

      .entry-tags div {
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="entry-card">
    <div class="entry-title">📓 <?php echo htmlspecialchars($decryptedTitle); ?></div>
    <div class="entry-meta">Date: <?php echo htmlspecialchars($entryDate); ?> | Time: <?php echo htmlspecialchars($entryTime); ?></div>
    <div class="entry-text"><?php echo nl2br(htmlspecialchars($decryptedEntry)); ?></div>
    <div class="entry-tags">
      <div><?php echo htmlspecialchars($decryptedMood); ?></div>
      <div><?php echo htmlspecialchars($decryptedWeather); ?></div>
      <div><?php echo htmlspecialchars($decryptedEnergy); ?></div>
      <div><?php echo htmlspecialchars($decryptedSocial); ?></div>
    </div>
  </div>
<!-- redirecting button!! -->
  <div style="text-align: center; margin-top: 30px;">
    <a href="paper2.php?entry_id=<?php echo $entryId; ?>">
      <button style="
        padding: 12px 24px;
        background-color: #653affff;
        color: white;
        font-size: 1rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-family: 'Indie Flower', cursive;
      ">
        ✨ View Collage
      </button>
    </a>
  </div>
  <!-- back to entries button -->
    <div style="text-align: center; margin-top: 30px;">
    <a href="entries.php">
      <button style="
        padding: 12px 24px;
        background-color: #653affff;
        color: white;
        font-size: 1rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-family: 'Indie Flower', cursive;
      ">
        ✒️ View Entries
      </button>
    </a>
  </div>

</body>
</html>