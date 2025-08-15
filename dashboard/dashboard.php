<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: https://mydiary.gt.tc/loginfrontend.php?error=" . urlencode("Please log in first"));
    exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Diary Dashboard</title>
  <link rel="icon" type="image/x-icon" href="icon.png">
  <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Roboto&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-blue: #003865;
      --accent-blue: #0074B7;
      --light-bg: #f2f5f8;
      --shadow-color: #001F3F;
      --white: #f8f8f8ff;
      --text-dark: #1a1a1a;
    }

    body {
      margin: 0;
      font-family: 'Quicksand', sans-serif;
      background-color: var(--light-bg);
      color: var(--text-dark);
    }

    /* Top navbar */
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

    /* Sidebar */
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

    .entry-form input{
      width: 100%;
  padding: 10px 5px;
  margin: 10px 0 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
  font-family: 'Quicksand', sans-serif;
    }
.entry-form select {
  width: 100%;
  padding: 10px;
  margin: 10px 0 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
  font-family: 'Quicksand', sans-serif;
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

    .entry-form {
         border-radius:20px;
        width: 100%;
        height: 90vh;
        margin: 0;
        padding-right: 20px;
        padding-left: 20px;
        padding-bottom: 20px;
        box-sizing: border-box;
        background-color: #fdfaf3; /* Soft off-white paper look */
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
     }

    .entry-form h3 {
      margin-bottom: 1rem;
      color: var(--primary-blue);
    }

    
.entry-form textarea {
  border-radius: 20px;
  flex-grow: 1;
  width: 100%;
  resize: none;
  font-size:bold;
  padding: 5px 20px; /* minimal top padding to align to line */
  border: none;
  font-size: 1.3rem; /* size that works across most devices */
  font-family: 'Indie Flower', 'Caveat', cursive;
  line-height: 45px; /* match background line height */
  background-image: repeating-linear-gradient(
    to bottom,
    transparent,
    transparent 44px,
    #ccc 45px
  );
  background-size: 100% 45px;
  background-repeat: repeat-y;
  background-attachment: local;
  color: #333;
  outline: none;
  box-sizing: border-box;
}
.entry-wrapper {
  width: 100%;
  height: 100vh;
  margin: 0;
  padding: 20px;
  box-sizing: border-box;
  background-color: #fdfaf3; /* off-white paper look */
  display: flex;
  justify-content: center;
  align-items: flex-start;
}


.entry-form button {
  margin-top: 20px;
  align-self: flex-end;
  padding: 12px 28px;
  font-size: 1rem;
  font-weight: bold;
  color: #ffffff;
  background-color: #00203F;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-bottom: 10px;
}

.entry-form button:hover {
  background-color: #005c8c;
}

/* 🔧 Responsive Fix */
@media (max-width: 768px) {
  .entry-form textarea {
    font-size: 1.15rem;
    line-height: 40px;
    background-image: repeating-linear-gradient(
      to bottom,
      transparent,
      transparent 39px,
      #ccc 40px
    );
    background-size: 100% 40px;
    padding-top: 4px;
  }
}

/* Image upload css*/
.image-upload-section {
  margin-top: 20px;
  text-align: center;
}

.upload-btn {
  background-color: #ffd1dc;
  border: none;
  padding: 10px 20px;
  font-weight: bold;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  transition: 0.3s;
}

.upload-btn:hover {
  background-color: #ffb6c1;
}

.preview-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 15px;
  gap: 20px;
}

.image-card {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  background-color: #fff;
  border-radius: 12px;
  padding: 10px;
  margin: 10px 0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  position: relative;
}

.image-card img {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  object-fit: cover;
  margin-right: 12px;
}
.image-description {
  flex: 1;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
  outline: none;
}

.delete-icon {
  position: absolute;
  top: 4px; /* reduced from 8px to move upward */
  right: 4px;
  background-color: #001f3f;
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 14px; /* slightly smaller to fit in the circle */
  width: 22px;
  height: 22px;
  cursor: pointer;
  line-height: 5px; /* match height for perfect vertical centering */
  text-align: center;
  transition: 0.3s ease;
  padding: 0;
}

.delete-icon:hover {
  background-color: #ff4136;
}

.progress-circle {
  display: none; /* hidden unless you enable uploads later */
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}



  </style>
</head>
<body>

  <div class="navbar">
    <div class="menu-icon" onclick="toggleSidebar()">☰</div>
    <h2><?php echo htmlspecialchars($username); ?>'s Diary</h2>
  </div>

  <div class="sidebar" id="sidebar">
    <a href="/dashboard/home.php">🏠 Home</a>
    <a href="/dashboard/dashboard.php">🧿 Write Entry</a>
    <a href="/dashboard/entries.php">📋 All Entries</a>
    <a href="/dashboard/profile.php">👤 My Profile</a>
    <!-- <a href="#">⚙️ Settings</a>
    <a href="#">🗑️ Deleted Entries</a> -->
    <a href="https://mydiary.gt.tc/logout.php">🚪 Logout</a>
  </div>

  <form action="/dashboard/save_entry.php" method="POST" class="entry-form" id="diaryForm" enctype="multipart/form-data">
  <h3>Write about your day</h3>
  <textarea name="entry" placeholder="Dear Diary..." required></textarea>
  <button type="button" onclick="showFollowUps()">Save Entry</button>
  <button type="button" id="toggleFollowUpBtn" onclick="toggleFollowUp()" style="display: none; margin-left: 10px;">
  Hide Follow-Up
</button>

  <!-- Follow-up questions (hidden initially) -->
  <div id="followUpSection" style="display: none; margin-top: 20px;">
    <h3>Tell us more...</h3>

    <label>What title would you give this entry?</label>
    <input type="text" name="title" placeholder="A peaceful day..." required />

    <label>Your mood today?</label>
    <select name="mood" required>
      <option value="">Select</option>
      <option>😊 Happy</option>
      <option>😐 Neutral</option>
      <option>😞 Sad</option>
      <option>😍 In Love</option>
      <option>😴 Tired</option>
      <option>😌 Relaxed</option>
      <option>🥳 Excited</option>
    </select>

    <label>How was the weather?</label>
    <select name="weather" required>
      <option value="">Select</option>
      <option>☀️Sunny</option>
      <option>🌧️ Rainy</option>
      <option>⛅ Cloudy</option>
      <option>🌩️ Stormy</option>
      <option>❄️ Snowy</option>
      <option>🌫️ Foggy</option>
    </select>

    <label>Your energy level?</label>
    <select name="energy" required>
      <option value="">Select</option>
      <option>🔋 Full of Energy</option>
      <option>⚡ Productive</option>
      <option>🐢 Low Energy</option>
      <option>🛌 Drained</option>
    </select>

    <label>Did you interact socially?</label>
    <select name="social" required>
      <option value="">Select</option>
      <option>👨‍👩‍👧‍👦 Spent time with family/friends</option>
      <option>👤 Alone</option>
      <option>💬 Talked with someone special</option>
      <option>🚫 Minimal interaction</option>
    </select>

    <div class="image-upload-section">
  <button type="button" onclick="document.getElementById('images').click()" class="upload-btn">
    📸 Upload Images
  </button>
  <input type="file" id="images" name="images[]" accept="image/*" multiple hidden onchange="handleImageSelection(event)">
  <div id="imagePreviewContainer" class="preview-container"></div>
</div>

  <button type="submit">Submit All</button>
</form>

<script>
function showFollowUps() {
  const section = document.getElementById("followUpSection");
  const toggleBtn = document.getElementById("toggleFollowUpBtn");
  section.style.display = "block";
  toggleBtn.style.display = "inline-block";
  toggleBtn.innerText = "Hide Follow-Up";
  section.scrollIntoView({ behavior: "smooth" });
}

  function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("show");
  }

  function toggleFollowUp() {
  const section = document.getElementById("followUpSection");
  const toggleBtn = document.getElementById("toggleFollowUpBtn");

  if (section.style.display === "none") {
    section.style.display = "block";
    toggleBtn.innerText = "Hide Follow-Up";
    section.scrollIntoView({ behavior: "smooth" });
  } else {
    section.style.display = "none";
    toggleBtn.innerText = "Unhide Follow-Up";
  }
}


  //image upload logic

  let selectedImages = [];

function handleImageSelection(event) {
  const newFiles = Array.from(event.target.files);
  const totalImages = selectedImages.length + newFiles.length;

  if (totalImages > 4) {
    alert("You can only upload up to 4 images.");
    event.target.value = ''; // reset input to avoid accidental re-selection
    return;
  }

  const previewContainer = document.getElementById("imagePreviewContainer");
  const remainingSlots = 4 - selectedImages.length;
  const filesToAdd = newFiles.slice(0, remainingSlots);

  filesToAdd.forEach((file, index) => {
    const id = `img-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
    selectedImages.push({ file, id });

    const reader = new FileReader();
    reader.onload = function (e) {
      const imgCard = document.createElement("div");
      imgCard.className = "image-card";
      imgCard.id = id;

      imgCard.innerHTML = `
        <button class="delete-icon" onclick="removeImage('${id}')">×</button>
        <img src="${e.target.result}" alt="Preview">
        <input type="text" class="image-description" placeholder="Enter caption" name="captions[]">
        <div class="progress-circle" id="${id}-progress"></div>
      `;
      previewContainer.appendChild(imgCard);
    };
    reader.readAsDataURL(file);
  });
}


function removeImage(id) {
  const index = selectedImages.findIndex(img => img.id === id);
  if (index !== -1) {
    selectedImages.splice(index, 1);
    const card = document.getElementById(id);
    if (card) card.remove();
  }
}
</script>


</body>
</html>