<?php
session_start();
require_once '/helpers/encryption.php';   // Inside dashboard/helpers/
require_once '/../db.php';                // One level up to root

if (!isset($_SESSION['username'])) {
    header("Location: ../loginfrontend.php?error=" . urlencode("Please log in first"));
    exit();
}
$username = $_SESSION['username'];

// Fetch all entries for this user
$sql = "SELECT * FROM diary_entries WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$numberOfEntries= $result->num_rows;

// Get the number of entries for the current month
$sql_monthly = "SELECT COUNT(*) as monthlyTotal FROM diary_entries WHERE username = ? AND YEAR(entry_date) = YEAR(CURDATE()) AND MONTH(entry_date) = MONTH(CURDATE())";
$stmt_monthly = $conn->prepare($sql_monthly);
$stmt_monthly->bind_param("s", $username);
$stmt_monthly->execute();
$result_monthly = $stmt_monthly->get_result();
$row_monthly = $result_monthly->fetch_assoc();
$monthlyEntries = $row_monthly['monthlyTotal'];

// Get all unique entry dates for the user, ordered by most recent
$sql_streak = "SELECT DISTINCT DATE(entry_date) as entry_date FROM diary_entries WHERE username = ? ORDER BY entry_date DESC";
$stmt_streak = $conn->prepare($sql_streak);
$stmt_streak->bind_param("s", $username);
$stmt_streak->execute();
$result_streak = $stmt_streak->get_result();
$entryDates = [];
while ($row = $result_streak->fetch_assoc()) {
    $entryDates[] = $row['entry_date'];
}
// Encode the PHP array into a JSON string for JavaScript
$jsonEntryDates = json_encode($entryDates);


// Fetch user aim
$sql_aim = "SELECT * FROM users WHERE username = ?";
$stmt_aim = $conn->prepare($sql_aim);
$stmt_aim->bind_param("s", $username);
$stmt_aim->execute();
$result_aim = $stmt_aim->get_result();
$aim= $result_aim->fetch_assoc();
$encrypted_aim=$aim["current_aim"];
$decrypted_aim=decryptData($encrypted_aim);


?>
<?php
// --- New PHP code to find the most common mood for the month ---
$sql_encrypted_moods = "SELECT encrypted_mood FROM diary_entries WHERE username = ? AND YEAR(entry_date) = YEAR(CURDATE()) AND MONTH(entry_date) = MONTH(CURDATE())";
$stmt_encrypted_moods = $conn->prepare($sql_encrypted_moods);
$stmt_encrypted_moods->bind_param("s", $username);
$stmt_encrypted_moods->execute();
$result_encrypted_moods = $stmt_encrypted_moods->get_result();

$decryptedMoods = [];
while ($row = $result_encrypted_moods->fetch_assoc()) {
    // Decrypt each mood entry and add it to our array
    $decryptedMoods[] = decryptData($row['encrypted_mood']);
}

$peakMood = "N/A"; // Default value
if (!empty($decryptedMoods)) {
    // Count the occurrences of each decrypted mood
    $moodCounts = array_count_values($decryptedMoods);
    
    // Find the mood with the highest count
    arsort($moodCounts);
    $peakMood = key($moodCounts);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Diary - Home</title>
    <link rel="icon" type="image/x-icon" href="icon.png">
    <!--
      The Inter font is a great choice for clean, modern typography.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Evil Eye Color Palette & General Styling */
        :root {
            --bg-color: #e6f2ff;       /* Very light blue */
            --card-bg: #ffffff;
            --text-color-primary: #003366;  /* Dark navy blue */
            --text-color-secondary: #336699;  /* Muted medium blue */
            --light-blue-accent: #99CCFF; /* Light blue accent */
            --dark-blue-accent: #194680;   /* Darker blue for highlights */
            --border-color: #cce0ff;   /* Soft blue border */
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 15px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -3px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color-primary);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-image: radial-gradient(circle, #f0f8ff, #e6f2ff);
            position: relative;
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--dark-blue-accent);
            color: var(--card-bg);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.05em;
            color: var(--card-bg);
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
            background-color: var(--text-color-primary);
            padding-top: 60px;
            transition: left 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            z-index: 999;
        }

        .sidebar a {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--card-bg);
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background-color 0.2s ease;
        }

        .sidebar a:hover {
            background-color: var(--dark-blue-accent);
        }

        .sidebar.show {
            left: 0;
        }

        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            padding-bottom: 6rem; /* Space for the fixed button */
        }

        header {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color-primary);
            margin: 0;
            letter-spacing: -0.05em;
        }

        header p {
            font-size: 1rem;
            color: var(--text-color-secondary);
            margin-top: 0.5rem;
        }

        .happy-thought {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            margin-bottom: 2.5rem;
            text-align: center;
            color: var(--text-color-secondary);
        }
        
        .happy-thought p {
            font-size: 1.25rem;
            font-weight: 600;
            font-style: italic;
            margin: 0;
        }

        .stats-grid, .weather-details-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
        
        /* Updated styling for weather details grid for better responsiveness */
        .weather-details-grid {
            grid-template-columns: 1fr; /* Default to one column on small screens */
            gap: 1.5rem;
        }
        
        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
            }
            /* Two columns for tablets */
            .weather-details-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (min-width: 1024px) {
            /* Three columns for desktops */
            .weather-details-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
        }

        .card {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .card-header .icon {
            width: 2.25rem;
            height: 2.25rem;
        }

        .card-header .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-color-secondary);
            text-transform: uppercase;
        }

        .card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-color-primary);
            line-height: 1;
        }
        
        .card h3.main-temp {
            font-size: 3rem;
            line-height: 1;
        }

        .card h3 span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-color-secondary);
        }

        .weather-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            border-radius: 1.5rem;
            background-color: var(--card-bg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            margin-bottom: 2.5rem;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .weather-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .weather-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            width: 100%;
        }

        .weather-icon {
            width: 4rem;
            height: 4rem;
            color: var(--light-blue-accent);
        }

        .weather-temp {
            font-size: 3rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .weather-desc {
            font-size: 1rem;
            color: var(--text-color-secondary);
            text-transform: capitalize;
            margin: 0;
        }

        .weather-location {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 0.5rem;
            gap: 0.25rem;
        }

        .weather-location .city-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color-primary);
            margin: 0;
        }

        .weather-location .full-address {
            font-size: 0.875rem;
            color: var(--text-color-secondary);
            margin: 0;
        }
        
        .section-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-color-primary);
            margin-bottom: 1rem;
        }

        .ideas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .idea-card {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .idea-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .idea-card h4 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            color: var(--text-color-primary);
        }
        
        .idea-card p {
            font-size: 0.875rem;
            color: var(--text-color-secondary);
            margin: 0;
        }

        .icon-light-blue { color: var(--light-blue-accent); }
        .icon-dark-blue { color: var(--dark-blue-accent); }
        
        .add-entry-btn {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background-color: var(--dark-blue-accent);
            border: none;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-entry-btn:hover {
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
            transform: translateX(-50%) scale(1.05);
        }

        .add-entry-btn svg {
            color: var(--card-bg);
            width: 2.5rem;
            height: 2.5rem;
            stroke-width: 2.5;
        }


        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            /* display: flex; Use flexbox for centering */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        .close-button {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        /* New Custom CSS for the Coming Soon Modal */
        .modal-coming-soon {
            text-align: center;
        }

        .modal-heading {
            font-size: 1.25rem; /* Equivalent to text-xl */
            font-weight: 600; /* Equivalent to font-semibold */
            margin-bottom: 1rem; /* Equivalent to mb-4 */
            color: #1f2937; /* Equivalent to text-gray-800 */
        }

        .modal-message {
            color: #4b5563; /* Equivalent to text-gray-600 */
        }
        
        .modal-ok-button {
            margin-top: 1rem; /* Equivalent to mt-4 */
            padding: 0.5rem 1rem; /* Equivalent to px-4 py-2 */
            background-color: #3b82f6; /* Equivalent to bg-blue-500 */
            color: #ffffff; /* Equivalent to text-white */
            border-radius: 0.5rem; /* Equivalent to rounded-lg */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); /* Equivalent to shadow */
            transition: background-color 0.15s ease-in-out; /* Equivalent to transition */
        }

        .modal-ok-button:hover {
            background-color: #2563eb; /* Equivalent to hover:bg-blue-600 */
        }
                /* Responsive adjustments for smaller screens */
        @media (max-width: 600px) {
            .modal-content {
                width: 90%;
                margin: 20% auto;
                padding: 1.5rem;
            }
        }

    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="menu-icon" onclick="toggleSidebar()">☰</div>
        <div class="navbar-brand" id="navbar-brand-text"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="/dashboard/home.php">🏠 Home</a>
        <a href="/dashboard/dashboard.php">🧿 Write New Page</a>
        <a href="/dashboard/entries.php">📋 All Entries</a>
        <a href="/dashboard/profile.php">👤 My Profile</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="content">
        <header>
            <h1 style="color: #3b82f6;">Little Secrets 🧿</h1>
            <p>A little place for our memories.</p>
        </header>

        <main>
            <!-- Happy Thought Section -->
            <section class="happy-thought">
                <p id="daily-thought">Loading a happy thought...</p>
            </section>

            <!-- Stats at a Glance -->
            <section>
                <h2 class="section-title">Your Stats at a Glance</h2>
                <div class="stats-grid">
                    <!-- Total Entries Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-light-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                            </svg>
                            <span class="label">Total Memories</span>
                        </div>
                        <h3 id="total-entries">0</h3>
                    </div>

                    <!-- This Month's Entries Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
                            </svg>
                            <span class="label">New This Month</span>
                        </div>
                        <h3 id="monthly-entries">0</h3>
                    </div>

                    <!-- Diary Streak Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1-1.5 1.5-3.33 2.5-4C14 5.27 16 2 16 2c0 2.22-.5 7.27-2 10-.5 1.5-1 2-1 3a2.5 2.5 0 0 1-2.5 2.5c-1.38 0-2-2-2-3Z"/><path d="M12 15c0 1-.5 1.5-1 2-1 1-1 2-2 2-1.29 0-2.5-.48-3.5-2-.73-1.28-.73-2.59-.26-4.01C7 9.61 8.5 7 10 5.5c.33.66-.5 1.63-1 2-1 1-1.45 2.3-1.45 3.5a2.5 2.5 0 0 0 2.5 2.5Z"/>
                            </svg>
                            <span class="label">Current Streak</span>
                        </div>
                        <h3 id="writing-streak">0 <span>days</span></h3>
                    </div>

                    <!-- Peak Mood Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-light-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                            </svg>
                            <span class="label">Top Mood</span>
                        </div>
                        <h3 id="peak-mood" class="capitalize">N/A</h3>
                    </div>
                </div>
            </section>

            <!-- Current Weather Section -->
            <section>
                <h2 class="section-title">Current Weather</h2>
                <div class="weather-card">
                    <div class="weather-info">
                        <!-- The SVG icon will remain static for now -->
                        <svg class="weather-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 9a4 4 0 0 0-2-3.46"/><path d="M12 3v1"/><path d="M12 21v1"/><path d="M22 12h-1"/><path d="M3 12h1"/><path d="M4.93 4.93l.7.7"/><path d="M18.36 18.36l.7.7"/><path d="M3.46 20.54l.7-.7"/><path d="M18.36 5.64l.7-.7"/><path d="M8 14v-4a4 4 0 1 1 8 0v4a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2Z"/>
                        </svg>
                        <div>
                            <h3 class="weather-temp"><span id="weather-temp">--</span>°C</h3>
                            <p class="weather-desc" id="weather-description">Loading...</p>
                        </div>
                    </div>
                    <!-- The city name is now bigger and bolder for better visibility -->
                    <div class="weather-location" id="weather-location">
                        <span class="city-name">Loading City...</span>
                        <span class="full-address"></span>
                    </div>
                </div>
                
                <!-- New section for detailed weather metrics -->
                <h2 class="section-title">More Weather Details</h2>
                <div class="weather-details-grid">
                    <!-- Feels Like Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 14v-4a4 4 0 1 1 8 0v4a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2Z" />
                                <path d="M8 10a4 4 0 1 1 8 0" />
                                <path d="M12 18v2" />
                            </svg>
                            <span class="label">Feels Like</span>
                        </div>
                        <h3 id="feels-like-temp">--°C</h3>
                    </div>

                    <!-- Wind Speed Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-light-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.5 2H10a4 4 0 0 0-4 4v.5"/><path d="M10 12H8a4 4 0 0 0-4 4v.5"/><path d="M15 22h2.5a4 4 0 0 0 4-4v-.5"/><path d="M17 12h2.5a4 4 0 0 0 4-4v-.5"/>
                            </svg>
                            <span class="label">Wind Speed</span>
                        </div>
                        <h3 id="wind-speed">-- <span>km/h</span></h3>
                    </div>

                    <!-- Humidity Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20"/><path d="M18 10h-6"/><path d="M18 14h-6"/><path d="M18 18h-6"/><path d="M6 10h6"/><path d="M6 14h6"/><path d="M6 18h6"/>
                            </svg>
                            <span class="label">Humidity</span>
                        </div>
                        <h3 id="humidity">--<span>%</span></h3>
                    </div>
                </div>
            </section>

            <!-- Writing Ideas and Goals Section -->
            <section>
                <h2 class="section-title">A Little Inspiration</h2>
                <div class="ideas-grid">

                        <!-- Writing Prompt Card -->
                    <!-- <a href="writing.html" class="block">
                        <div class="idea-card">
                            <h4>Start a New Page</h4>
                            <p id="writing-prompt">What's one thing you're grateful for today?</p>
                        </div>
                    </a> -->
                    <!-- Writing Prompt Card -->
                    <a href="dashboard.php" style="text-decoration: none;">
                        <div class="idea-card">
                            <h4>Start a New Page</h4>
                            <p id="writing-prompt">What's one thing you're grateful for today?</p>
                        </div>
                    </a>
                    
                    <!-- Set a Goal Card -->
                    <a href="profile.php" style="text-decoration: none;">
                        <div class="idea-card">
                        <h4>My Goal</h4>
                        <p id="user-goal-text">Your goal will appear here!</p>
                    </div>
                    </a>

                    <!-- Another Idea Card -->
                    <div class="idea-card" onclick="openSoonModal()">
                        <h4>Mood Calendar</h4>
                        <p>Visualize your moods over time with a color-coded calendar.</p>
                    </div>
    <!-- Coming Soon Modal -->
    <div id="coming-soon-modal" class="modal">
        <div class="modal-content modal-coming-soon">
            <span class="close-button" onclick="closeSoonModal()">&times;</span>
            <h4 class="modal-heading">Coming Soon!</h4>
            <p class="modal-message">This feature will be implemented soon. Stay tuned!</p>
            <button onclick="closeSoonModal()" class="modal-ok-button">OK</button>
        </div>
    </div>
    </div>
                </div>
            </section>
        </main>

        <!-- Fixed "Add Entry" Button -->
        <button class="add-entry-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14"/><path d="M12 5v14"/>
            </svg>
        </button>
    </div>

<script>

            // --- Writing Prompt Section Logic ---
        function displayRandomWritingPrompt() {
            const writingPrompts = [
                "What's one thing you're grateful for today?",
                "Describe a memory that makes you smile.",
                "Write about a small act of kindness you witnessed or performed.",
                "What's one goal you want to achieve this week?",
                "If you could have a superpower, what would it be and why?",
                "Write a short story about an old key you found.",
                "What is one thing that you are proud of yourself for accomplishing recently?"
            ];

            const writingPromptElement = document.getElementById('writing-prompt');
            const randomIndex = Math.floor(Math.random() * writingPrompts.length);
            writingPromptElement.textContent = writingPrompts[randomIndex];
        }


    // --- Weather Fetching Logic with Open-Meteo API ---
    async function fetchWeather(latitude, longitude) {
        // Step 1: Fetch reverse geocoding data to get the city name and full address
        try {
            const geoUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`;
            const geoResponse = await fetch(geoUrl);
            if (!geoResponse.ok) throw new Error('Geocoding failed');
            const geoData = await geoResponse.json();
            
            let cityPart = '';
            let addressPart = '';

            // Improved logic to prioritize specific locations before defaulting to a state
            if (geoData.address.suburb) {
                cityPart = geoData.address.suburb;
            } else if (geoData.address.city) {
                cityPart = geoData.address.city;
            } else if (geoData.address.town) {
                cityPart = geoData.address.town;
            } else if (geoData.address.village) {
                cityPart = geoData.address.village;
            } else if (geoData.address.state) {
                cityPart = geoData.address.state;
            }
            
            // Build the full address string from available components.
            const addressComponents = [];
            // Only add the city part to the full address if it's not already the main "cityPart"
            if (geoData.address.city && geoData.address.city !== cityPart) {
                addressComponents.push(geoData.address.city);
            }
            if (geoData.address.county) addressComponents.push(geoData.address.county);
            if (geoData.address.postcode) addressComponents.push(geoData.address.postcode);
            if (geoData.address.state) addressComponents.push(geoData.address.state);
            if (geoData.address.country) addressComponents.push(geoData.address.country);

            // Filter out empty or null values and join them.
            addressPart = addressComponents.filter(Boolean).join(', ');

            document.querySelector('.weather-location .city-name').textContent = cityPart || 'Unknown Location';
            document.querySelector('.weather-location .full-address').textContent = addressPart;

        } catch (e) {
            console.error('Failed to get location name:', e);
            document.querySelector('.weather-location .city-name').textContent = 'Location not found';
            document.querySelector('.weather-location .full-address').textContent = '';
        }

        // Step 2: Fetch detailed weather data from the Open-Meteo API.
        const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,wind_speed_10m,weather_code&forecast_days=1`;
        
        try {
            const response = await fetch(weatherUrl);
            if (!response.ok) {
                throw new Error('Weather API request failed');
            }
            const data = await response.json();
            
            if (data.current) {
                const current = data.current;
                const temperature = current.temperature_2m;
                const feelsLike = current.apparent_temperature;
                const humidity = current.relative_humidity_2m;
                const windSpeed = current.wind_speed_10m;
                const weathercode = current.weather_code;
                
                // Map Open-Meteo weather codes to a descriptive text.
                const weatherDescriptions = {
                    0: "Clear Sky",
                    1: "Mainly Clear",
                    2: "Partly Cloudy",
                    3: "Overcast",
                    45: "Fog",
                    48: "Depositing Rime Fog",
                    51: "Drizzle",
                    53: "Drizzle",
                    55: "Drizzle",
                    56: "Freezing Drizzle",
                    57: "Freezing Drizzle",
                    61: "Rain",
                    63: "Rain",
                    65: "Heavy Rain",
                    66: "Freezing Rain",
                    67: "Freezing Rain",
                    71: "Snow Fall",
                    73: "Snow Fall",
                    75: "Heavy Snow Fall",
                    77: "Snow Grains",
                    80: "Rain Showers",
                    81: "Rain Showers",
                    82: "Heavy Rain Showers",
                    85: "Snow Showers",
                    86: "Snow Showers",
                    95: "Thunderstorm",
                    96: "Thunderstorm with Hail",
                    99: "Thunderstorm with Hail"
                };
                
                const description = weatherDescriptions[weathercode] || "Unknown Weather";
                
                // Update the UI
                document.getElementById('weather-temp').textContent = Math.round(temperature);
                document.getElementById('weather-description').textContent = description;
                document.getElementById('feels-like-temp').textContent = `${Math.round(feelsLike)}°C`;
                document.getElementById('wind-speed').textContent = Math.round(windSpeed);
                document.getElementById('humidity').textContent = humidity;
            } else {
                throw new Error('Current weather data not found in response');
            }
        } catch (error) {
            console.error("Error fetching weather data:", error);
            document.getElementById('weather-description').textContent = "Failed to load weather";
        }
    }

    // --- Geolocation and Main Logic ---
    function getLocationAndWeather() {
        if ("geolocation" in navigator) {
            document.getElementById('weather-description').textContent = "Fetching location...";
            document.querySelector('.weather-location .city-name').textContent = 'Loading...';
            document.querySelector('.weather-location .full-address').textContent = '';

            navigator.geolocation.getCurrentPosition(position => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                fetchWeather(lat, lon);
            }, (error) => {
                console.error("Geolocation error:", error);
                document.getElementById('weather-description').textContent = "Please allow location access.";
                document.querySelector('.weather-location .city-name').textContent = 'Location Blocked';
                document.querySelector('.weather-location .full-address').textContent = 'Cannot fetch address details.';
            });
        } else {
            console.log("Geolocation is not available in your browser.");
            document.getElementById('weather-description').textContent = "Geolocation not supported.";
            document.querySelector('.weather-location .city-name').textContent = 'Geolocation Unavailable';
            document.querySelector('.weather-location .full-address').textContent = '';
        }
    }

    // --- Streak Calculation Function ---
    function calculateWritingStreak(dates) {
        if (dates.length === 0) {
            return 0;
        }

        let streak = 0;
        let today = new Date();
        today.setHours(0, 0, 0, 0);

        // Check if the most recent entry is today. If not, check yesterday.
        let isStreakActive = false;
        const mostRecentEntryDate = new Date(dates[0]);
        if (today.getTime() === mostRecentEntryDate.getTime()) {
            isStreakActive = true;
            streak = 1;
        } else {
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            if (yesterday.getTime() === mostRecentEntryDate.getTime()) {
                isStreakActive = true;
                streak = 1;
            }
        }

        if (!isStreakActive) {
            return 0; // Streak is broken if no entry today or yesterday
        }

        // Loop through the rest of the dates to extend the streak
        for (let i = 1; i < dates.length; i++) {
            const currentDate = new Date(dates[i-1]);
            const nextDate = new Date(dates[i]);

            const oneDay = 24 * 60 * 60 * 1000;
            const diffDays = Math.round(Math.abs((currentDate - nextDate) / oneDay));

            // Check if the previous day is consecutive
            if (diffDays === 1) {
                streak++;
            } else {
                break; // Stop the loop if the streak is broken
            }
        }
        
        return streak;
    }

    const entryDates = <?php echo $jsonEntryDates; ?>;
    const writingStreak = calculateWritingStreak(entryDates);
    
    // --- Dummy Data (Replace with your PHP/MySQL data) ---
    const dummyData = {
        username: "<?php echo $username ?>",
        totalEntries: <?php echo $numberOfEntries ?>,
        monthlyEntries: <?php echo $monthlyEntries ?>,
        writingStreak: writingStreak,
        peakMood: "<?php echo $peakMood ?>",
        userGoal: "<?php echo $decrypted_aim ?>"
    };

    // --- Inject Dummy Data into the HTML ---
    document.getElementById('navbar-brand-text').textContent = `${dummyData.username}'s Diary`;
    document.getElementById('total-entries').textContent = dummyData.totalEntries;
    document.getElementById('monthly-entries').textContent = dummyData.monthlyEntries;
    document.getElementById('writing-streak').textContent = dummyData.writingStreak;
    document.getElementById('peak-mood').textContent = dummyData.peakMood;
    document.getElementById('user-goal-text').textContent = dummyData.userGoal;

    // --- Add a simple event listener for the buttons ---
    document.querySelector('.add-entry-btn').addEventListener('click', () => {
        window.location.href = 'https://mydiary.gt.tc/dashboard/dashboard.php';
    });

    // --- Sidebar toggle function ---
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("show");
    }

    // --- Happy Thought Section Logic ---
    function displayRandomHappyThought() {
        const happyThoughts = [
            "Today is a gift, that's why it's called the present.",
            "The best way to predict the future is to create it.",
            "Happiness is a journey, not a destination.",
            "The only time you should look back is to see how far you've come.",
            "Believe you can and you're halfway there.",
            "The best is yet to come.",
            "Be the reason someone smiles today.",
            "What you think, you become. What you feel, you attract. What you imagine, you create."
        ];

        // Get the paragraph element by its ID
        const dailyThoughtElement = document.getElementById('daily-thought');

        // Generate a random index number
        const randomIndex = Math.floor(Math.random() * happyThoughts.length);

        // Set the text content of the paragraph to a random thought from the array
        dailyThoughtElement.textContent = happyThoughts[randomIndex];
    }

        // --- Coming Soon Modal Logic (New) ---
        function openSoonModal() {
            document.getElementById('coming-soon-modal').style.display = 'flex';
        }
        function closeSoonModal() {
            document.getElementById('coming-soon-modal').style.display = 'none';
        }


    // --- Start the process on page load ---
    window.addEventListener('load', () => {
        getLocationAndWeather();
        displayRandomHappyThought();
        displayRandomWritingPrompt();
    });
</script>
</body>
</html>
