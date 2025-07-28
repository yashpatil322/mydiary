<?php
session_start();
require_once 'helpers/encryption.php';
require_once '../db.php'; // your DB connection file

if (!isset($_SESSION['username'])) {
    header("Location: ../loginfrontend.php?error=" . urlencode("Please log in first"));
    exit();
}

$username = $_SESSION['username'];

// Fetch all entries for this user
$sql = "SELECT * FROM diary_entries WHERE username = ? ORDER BY entry_date DESC, entry_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    // Decrypt each field using encryption helper
    $iv = base64_decode($row['iv']);
    $entries[] = [
        'id' => $row['id'],
        'date' => $row['entry_date'],
        'time' => $row['entry_time'],
        'title' => decryptData($row['encrypted_title'], $iv),
        'mood' => decryptData($row['encrypted_mood'], $iv),
        'weather' => decryptData($row['encrypted_weather'], $iv),
        'energy' => decryptData($row['encrypted_energy_level'], $iv),
        'social' => decryptData($row['encrypted_social_interaction'], $iv),
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Diary Entries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #fefaf6;
        }

        .heading {
            text-align: center;
            color: #000000ff;
            margin-bottom: 30px;
        }

        .username {
            text-align: center;
            color: #ffffffff;
            margin-bottom: 30px;
        }

        .entries-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 800px;
            margin: auto;
        }

        .entry-card {
            background-color: #fff;
            border: 2px solid #e4dcc4;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .entry-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .entry-title {
            font-size: 22px;
            font-weight: bold;
            color: #3c5a6f;
            margin-bottom: 10px;
        }

        .entry-meta {
            font-size: 15px;
            color: #7b7b7b;
            margin-bottom: 10px;
        }

        .entry-tags {
            font-size: 14px;
            color: #555;
        }


        :root {
  --primary-blue: #003865;
  --accent-blue: #0074B7;
  --light-bg: #f2f5f8;
  --shadow-color: #ffffffff;
  --white: #ffffff;
  --text-dark: #1a1a1a;
}

.navbar {
  background-color: var(--primary-blue);
  color: var(--white);
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.navbar h2 {
  margin: 0;
  font-weight: 600;
}

.menu-icon {
  font-size: 24px;
  cursor: pointer;
  display: block;
}

.sidebar {
  position: fixed;
  top: 0;
  left: -250px;
  width: 250px;
  height: 100%;
  background-color: var(--primary-blue);
  padding-top: 60px;
  transition: left 0.3s ease;
  box-shadow: 2px 0 10px rgba(0,0,0,0.2);
  z-index: 999;
}

.sidebar a {
  display: block;
  padding: 1rem 1.5rem;
  color: var(--white);
  text-decoration: none;
  font-weight: 500;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar a:hover {
  background-color: var(--accent-blue);
  color: var(--white);
}

.sidebar.show {
  left: 0;
}

.content {
  padding-top: 1rem;
  padding-left: 1rem;
  padding-right: 1rem;
}

        @media (max-width: 600px) {
            .entry-card {
                padding: 28px;
            }

            .entry-title {
                font-size: 24px;
            }

            .entry-meta,
            .entry-tags {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
  <div class="navbar">
    <div class="menu-icon" onclick="toggleSidebar()">☰</div>
    <h2 class="username" ><?php echo htmlspecialchars($username); ?>'s Diary</h2>
  </div>

  <div class="sidebar" id="sidebar">
    <a href="dashboard.php">🧿 Dashboard</a>
    <a href="entries.php">📋 All Entries</a>
    <!-- <a href="#">⚙️ Settings</a>
    <a href="#">🗑️ Deleted Entries</a> -->
    <a href="../logout.php">🚪 Logout</a>
  </div>

  <div class="content">
    <h2 class="heading">Your Diary Entries</h2>
    <div class="entries-container">
      <?php foreach ($entries as $entry): ?>
          <div class="entry-card" onclick="location.href='paper.php?entry_id=<?= $entry['id'] ?>'">
              <div class="entry-title"><?= htmlspecialchars($entry['title']) ?></div>
              <div class="entry-meta"><?= htmlspecialchars($entry['date']) ?> | <?= htmlspecialchars($entry['time']) ?></div>
              <div class="entry-tags">
                  Mood: <?= htmlspecialchars($entry['mood']) ?> |
                  Weather: <?= htmlspecialchars($entry['weather']) ?> |
                  Energy: <?= htmlspecialchars($entry['energy']) ?> |
                  Social: <?= htmlspecialchars($entry['social']) ?>
              </div>
          </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("show");
    }
  </script>
</body>
</html>
