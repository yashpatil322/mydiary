<?php
session_start();
require_once __DIR__ . '/helpers/encryption.php';   // Inside dashboard/helpers/
require_once __DIR__ . '/../db.php';                // One level up to root

if (!isset($_SESSION['username'])) {
    header("Location: https://mydiary.gt.tc/loginfrontend.php?error=" . urlencode("Please log in first"));
    exit();
}
$username = $_SESSION['username'];

//obtaining email
$email_check=$conn->prepare("SELECT email FROM users WHERE username=?");
$email_check->bind_param("s",$username);
$email_check->execute();
$email_result=$email_check->get_result();
$email_row=$email_result->fetch_assoc();
$user_email=$email_row["email"];

//obtaining first entry date 
// The MIN() function finds the smallest value in a set.
$sql_query = "SELECT MIN(entry_date) AS first_entry_date FROM diary_entries WHERE username = ?";
$stmt_first = $conn->prepare($sql_query);
$stmt_first->bind_param("s", $username);
$stmt_first->execute();
$result_first = $stmt_first->get_result();
$row_first = $result_first->fetch_assoc();
$started_diary_at = $row_first['first_entry_date'];
if(is_null($started_diary_at)){
    $started_diary_at = "Not Started";
}

//auspicious day data fetching
$day_query="SELECT auspicious_day_info FROM users WHERE username=?";
$stmt_second=$conn->prepare($day_query);
$stmt_second->bind_param("s",$username);
$stmt_second->execute();
$result_second=$stmt_second->get_result();
$row_second=$result_second->fetch_assoc();
$encrypted_auspicious_day_info=$row_second['auspicious_day_info'];
$auspicious_day_info=decryptData($encrypted_auspicious_day_info);

//last entry fetch
// --- 1. Fetch the last_entry data from the database ---
$sql = "SELECT last_entry FROM users WHERE username = ?";
$stmt_third = $conn->prepare($sql);
$stmt_third->bind_param("s", $username);
$stmt_third->execute();
$result_third = $stmt_third->get_result();

if ($result_third->num_rows > 0) {
    $row = $result_third->fetch_assoc();
    $last_entry_timestamp = $row['last_entry'];

    // --- 2. Check if a last_entry timestamp exists ---
    if ($last_entry_timestamp) {

        // --- 3. Create DateTime objects for comparison ---
        $last_entry_date = new DateTime($last_entry_timestamp);
        $today = new DateTime();
        $yesterday = new DateTime('yesterday');

        // --- 4. Compare dates and format the output ---
        if ($last_entry_date->format('Y-m-d') == $today->format('Y-m-d')) {
            // If the date is today, show "Today at [Time]"
            $formatted_entry_time = 'Today at ' . $last_entry_date->format('g:i A');
        } elseif ($last_entry_date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
            // If the date is yesterday, show "Yesterday at [Time]"
            $formatted_entry_time = 'Yesterday at ' . $last_entry_date->format('g:i A');
        } else {
            // For any other date, show the full date and time
            $formatted_entry_time = $last_entry_date->format('F j, Y \a\t g:i A');
        }

    } else {
        $formatted_entry_time="No Entry Found";
    }
}

// Fetch user aim and last aim change date
$sql_aim = "SELECT * FROM users WHERE username = ?";
$stmt_aim = $conn->prepare($sql_aim);
$stmt_aim->bind_param("s", $username);
$stmt_aim->execute();
$result_aim = $stmt_aim->get_result();
$aim= $result_aim->fetch_assoc();
$encrypted_aim=$aim["current_aim"];
$decrypted_aim=decryptData($encrypted_aim);
$last_aim_change_date=$aim["aim_last_set_date"];

//obtaining user pfp
$pfp_fetch=$conn->prepare("SELECT profile_picture FROM users WHERE username=?");
$pfp_fetch->bind_param("s",$username);
$pfp_fetch->execute();
$pfp_result=$pfp_fetch->get_result();
$pfp_row=$pfp_result->fetch_assoc();
$encrypted_user_pfp=$pfp_row["profile_picture"];
$decrypted_user_pfp=decryptData($encrypted_user_pfp);
$base64_image = base64_encode($decrypted_user_pfp);
$image_src = 'data:image/jpeg;base64,' . $base64_image;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Diary</title>
    <link rel="icon" type="image/x-icon" href="icon.png">
    <!--
      The Inter font is used for a consistent, clean look across the site.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Evil Eye Color Palette & General Styling (Consistent with home.php) */
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
            padding: 1rem;
        }
        
        /* Ensure the content is responsive and doesn't overflow */
        main {
            padding: 1rem;
        }

        /* Styling for the new profile photo section */
        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        /* Profile photo container with hover effect for clickability */
        .profile-photo-container {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--card-bg);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }

        .profile-photo-container:hover {
            transform: scale(1.05);
        }

        .profile-photo-container:after {
            content: "Update";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40%;
            background: rgba(0, 0, 0, 0.5);
            color: var(--card-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        
        .profile-photo-container:hover:after {
            opacity: 1;
        }

        .profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color-primary);
            margin: 0.75rem 0 0.25rem;
            letter-spacing: -0.05em;
        }

        .profile-header p {
            font-size: 0.9rem;
            color: var(--text-color-secondary);
            margin: 0;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color-primary);
            margin-bottom: 1rem;
        }

        /* Profile Card Grid */
        .profile-grid {
            display: grid;
            gap: 1rem;
            /* Default to a single column on mobile */
            grid-template-columns: 1fr;
        }
        
        /* Two columns on larger screens (min-width: 640px) */
        @media (min-width: 640px) {
            .content {
                padding: 2rem;
            }
            .profile-header {
                margin-bottom: 2.5rem;
            }
            .profile-photo-container {
                width: 120px;
                height: 120px;
            }
            .profile-header h1 {
                font-size: 2.5rem;
                margin: 1rem 0 0.5rem;
            }
            .profile-header p {
                font-size: 1rem;
            }
            .section-title {
                font-size: 1.75rem;
            }
            .profile-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
            }
        }

        /* Style for the profile information cards */
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
            /* Ensure text wraps correctly */
            word-wrap: break-word;
            overflow-wrap: break-word;
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
            width: 2rem;
            height: 2rem;
        }
        
        @media (min-width: 640px) {
            .card {
                padding: 1.5rem;
            }
            .card-header .icon {
                width: 2.25rem;
                height: 2.25rem;
            }
        }

        .card-header .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-color-secondary);
            text-transform: uppercase;
        }

        .card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-color-primary);
            line-height: 1.2;
        }
        
        @media (min-width: 640px) {
            .card h3 {
                font-size: 2rem;
            }
        }

        .card h3 span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-color-secondary);
        }
        
        .icon-light-blue { color: var(--light-blue-accent); }
        .icon-dark-blue { color: var(--dark-blue-accent); }

        .aim-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .aim-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-color-primary);
        }

        .aim-section p {
            font-size: 0.9rem;
            color: var(--text-color-secondary);
            margin: 0;
        }
        
        @media (min-width: 640px) {
            .aim-section {
                margin-top: 2rem;
            }
            .aim-section h3 {
                font-size: 1.5rem;
            }
            .aim-section p {
                font-size: 1rem;
            }
        }

        .change-aim-btn {
            background-color: var(--dark-blue-accent);
            color: var(--card-bg);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .change-aim-btn:hover {
            background-color: var(--text-color-primary);
            transform: translateY(-2px);
        }

        /* Modal styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-overlay.show-modal {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 400px;
            position: relative;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .modal-content input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }
        
        .modal-content input[type="file"] {
            display: none; /* Hide the file input */
        }

        .modal-content .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color-secondary);
        }

        .modal-message {
            text-align: center;
            font-weight: 600;
            color: var(--dark-blue-accent);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="menu-icon" onclick="toggleSidebar()">☰</div>
        <!-- Navbar brand text, now dynamically updated with the user's name -->
        <div class="navbar-brand" id="navbar-brand-text"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="https://mydiary.gt.tc/dashboard/home.php">🏠 Home</a>
        <a href="https://mydiary.gt.tc/dashboard/dashboard.php">🧿 Write New Page</a>
        <a href="https://mydiary.gt.tc/dashboard/entries.php">📋 All Entries</a>
        <a href="https://mydiary.gt.tc/dashboard/profile.php">👤 My Profile</a>
        <a href="https://mydiary.gt.tc/logout.php">🚪 Logout</a>
    </div>

    <div class="content">
        <main>
            <!-- Profile Photo Section -->
            <section class="profile-header">
                <!-- Wrapper for the profile photo and file input -->
                <div class="profile-photo-container" onclick="document.getElementById('profile-pic-input').click()">
                    <!-- The profile picture that will be updated -->
                    <img src="<?php echo $image_src ?>" alt="Profile Photo" class="profile-photo" id="profile-photo" onerror="this.onerror=null;this.src='https://placehold.co/120x120/99CCFF/003366?text=User'">
                </div>
                <!-- Hidden file input for updating the image -->
                <input type="file" id="profile-pic-input" accept="image/*" style="display: none;">
                
                <h1 id="user-profile-name">John Doe</h1>
                <p>Your journey with Our Diary.</p>
            </section>
            
            <section>
                <h2 class="section-title">Account Details</h2>
                <div class="profile-grid">
                    <!-- Username Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span class="label">Username</span>
                        </div>
                        <h3 id="username-text">Loading...</h3>
                    </div>

                    <!-- Email Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-light-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                               <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <span class="label">Email</span>
                        </div>
                        <h4 id="user-email">Loading...</h4>
                    </div>

                    <!-- Started Diary At Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M10 14h4"/><path d="M12 12v4"/>
                            </svg>
                            <span class="label">Started Diary At</span>
                        </div>
                        <h3 id="started-date">Loading...</h3>
                    </div>

                    <!-- Started on Auspicious Day Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-light-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                               <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <span class="label">Auspicious Day</span>
                        </div>
                        <h3 id="auspicious-day">Loading...</h3>
                    </div>

                    <!-- Last Login Card -->
                    <div class="card">
                        <div class="card-header">
                            <svg class="icon icon-dark-blue" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                <path d="M15.5 13.5l-3.37 2.25A1 1 0 0 1 11.5 15V9a1 1 0 0 1 1.63-.75l3.37 2.25a1 1 0 0 1 0 1.5z"/>
                            </svg>
                            <span class="label">Last Entry</span>
                        </div>
                        <h3 id="last-login">Loading...</h3>
                    </div>
                </div>
            </section>

            <!-- Aim Section -->
            <section class="aim-section">
                <h3>Your Current Aim</h3>
                <p id="current-aim-text">Write for 10 days this month.</p>
                <button class="change-aim-btn" onclick="openAimModal()">Change My Aim</button>
            </section>
        </main>
    </div>

    <!-- Modal for Changing Aim -->
    <div class="modal-overlay" id="aim-modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeAimModal()">&times;</button>
            <h3 id="modal-title">Change Your Aim</h3>
            <p class="modal-message" id="modal-message"></p>
            <form id="aim-form" style="display: none;">
                <input type="text" id="new-aim-input" placeholder="e.g., 'Write 200 words a day'" required>
                <button type="submit" class="change-aim-btn">Save New Aim</button>
            </form>
        </div>
    </div>

<script>
    // --- Sidebar toggle function ---
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("show");
    }

    // --- Modal functions ---
    function openAimModal() {
        const modalOverlay = document.getElementById('aim-modal-overlay');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const aimForm = document.getElementById('aim-form');

        // Dummy data for the "once per month" check
        // In a real application, this date would be fetched from your backend.
        const lastChangedDate = new Date(dummyData.lastAimChange);
        const currentDate = new Date();
        const daysSinceChange = (currentDate - lastChangedDate) / (1000 * 60 * 60 * 24);

        if (daysSinceChange >= 30) {
            modalTitle.textContent = "Change Your Aim";
            modalMessage.textContent = "";
            aimForm.style.display = 'flex';
        } else {
            const daysLeft = Math.ceil(30 - daysSinceChange);
            modalTitle.textContent = "Hold On!";
            modalMessage.textContent = `You can only change your aim once a month. You can change it again in ${daysLeft} days.`;
            aimForm.style.display = 'none';
        }

        modalOverlay.classList.add('show-modal');
    }

    function closeAimModal() {
        const modalOverlay = document.getElementById('aim-modal-overlay');
        modalOverlay.classList.remove('show-modal');
    }

    // --- Handle the form submission (for the modal) ---
document.getElementById('aim-form').addEventListener('submit', async function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    // Get the new aim from the input field
    const newAim = document.getElementById('new-aim-input').value;

    if (newAim) {
        try {
            // Use the fetch API to send the data to a PHP script
            const response = await fetch('update_aim.php', {
                method: 'POST', // Use the POST method to send data
                headers: {
                    'Content-Type': 'application/json',
                },
                // Convert the data to a JSON string
                body: JSON.stringify({ aim: newAim })
            });

            // Parse the response from the server as JSON
            const result = await response.json();

            // Check if the server responded with a success message
            if (result.success) {
                // Update the aim on the page with the value from the database
                document.getElementById('current-aim-text').textContent = result.newAim;

                // Display a confirmation message
                const confirmationMessage = document.getElementById('modal-message');
                confirmationMessage.textContent = "Your aim has been updated successfully!";
                confirmationMessage.style.color = "green";
                document.getElementById('aim-form').style.display = 'none';

                // Automatically close the modal after a few seconds
                setTimeout(closeAimModal, 3000);
            } else {
                // Handle the case where the server returned an error
                console.error('Server error:', result.message);
                const errorMessage = document.getElementById('modal-message');
                errorMessage.textContent = "There was an error updating your aim.";
                errorMessage.style.color = "red";
            }
        } catch (error) {
            // Handle any network or parsing errors
            console.error('Fetch error:', error);
            const errorMessage = document.getElementById('modal-message');
            errorMessage.textContent = "Could not connect to the server.";
            errorMessage.style.color = "red";
        }
    }
});
    
// --- Profile Picture Update Logic ---
document.getElementById('profile-pic-input').addEventListener('change', async function(event) {
    const file = event.target.files[0];
    if (file) {
        // Step 1: The original code for client-side preview remains the same
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-photo').src = e.target.result;
            // The console log message is now slightly different to reflect the new functionality
            console.log('Profile picture updated successfully on the client side. Now sending to server...');
        };
        reader.readAsDataURL(file);

        // --- Step 2: Add the logic for server-side upload ---
        // Create a new FormData object to package the file for a POST request.
        // This is a standard way to handle file uploads with fetch.
        const formData = new FormData();
        // The first argument 'profile_picture' is the key that your PHP script will
        // use to access the file from the $_FILES superglobal.
        formData.append('profile_picture', file);

        try {
            // Use the fetch API to send the file data to your PHP script.
            const response = await fetch('update_profile_picture.php', {
                method: 'POST',
                body: formData
            });

            // Parse the JSON response from the PHP script.
            const result = await response.json();

            // Check if the server's response indicates success.
            if (response.ok && result.success) {
                console.log('Server response:', result.message);
                // You can add logic here to show a success message to the user.
                // For example:
                alert('Profile picture updated!');
            } else {
                // If the upload failed, log the error message from the server.
                console.error('Server upload failed:', result.message);
                // You can add logic to show an error message.
                // For example:
                alert('Error: ' + result.message);
            }
        } catch (error) {
            // Handle any network or other errors that might occur during the fetch.
            console.error('Network or fetch error:', error);
            alert('A network error occurred. Please try again.');
        }
    }
});

    // --- Dummy Data (Replace with your PHP/MySQL data) ---
    const dummyData = {
        username: "<?php echo $username ?>", // New field
        email: "<?php echo $user_email ?>", // New field
        startedDate: "<?php echo $started_diary_at ?>",
        auspiciousDay: "<?php echo $auspicious_day_info ?>",
        lastEntry: "<?php echo $formatted_entry_time ?>",
        currentAim: "<?php echo $decrypted_aim ?>",
        lastAimChange: "<?php echo $last_aim_change_date ?>" // YYYY-MM-DD format for the date check
    };

    // --- Inject Dummy Data into the HTML ---
    document.getElementById('user-profile-name').textContent = dummyData.username;
    document.getElementById('username-text').textContent = dummyData.username;
    document.getElementById('user-email').textContent = dummyData.email;
    document.getElementById('started-date').textContent = dummyData.startedDate;
    document.getElementById('auspicious-day').textContent = dummyData.auspiciousDay;
    document.getElementById('last-login').textContent = dummyData.lastEntry;
    document.getElementById('current-aim-text').textContent = dummyData.currentAim;

    // --- Start the process on page load ---
    window.onload = function() {
      // This is where you would fetch and display real user data
      // For now, we'll just use the dummy data
      document.getElementById('navbar-brand-text').textContent = dummyData.username + "'s diary";
      console.log('Profile page loaded.');
    };
</script>
</body>
</html>
