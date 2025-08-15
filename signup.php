<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up | MyDiary</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="/dashboard/icon.png">
  <style>
    :root {
      --deep-blue: #1E2A78;
      --light-blue: #5DA9E9;
      --navy: #0B1A4A;
      --beige: #F2E8CF;
      --white: #FFFFFF;
      --black: #000000;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--beige);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      padding: 1rem;
    }

    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background: rgba(0,0,0,0.5); /* dark overlay */
      z-index: -1;
    }

    .signup-container {
      background-color: var(--white);
      border: 2px solid var(--navy);
      border-radius: 12px;
      padding: 2rem;
      width: 100%;
      max-width: 400px; /* Increased max-width for better desktop view */
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .signup-container h2 {
      text-align: center;
      color: var(--deep-blue);
      margin-bottom: 1.5rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 12px; /* Increased padding for better touch targets */
      margin-bottom: 1rem;
      border: 1px solid var(--deep-blue);
      border-radius: 8px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      color: var(--navy);
      font-weight: 600;
    }

    button {
      width: 100%;
      background-color: var(--light-blue);
      color: var(--white);
      border: none;
      padding: 12px; /* Increased padding */
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: var(--deep-blue);
    }

    .login-link {
      text-align: center;
      margin-top: 1rem;
    }

    .login-link a {
      color: var(--navy);
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    .small-note {
      display: block;
      font-size: 0.75rem;
      color: #666;
      margin-top: -0.75rem;
      margin-bottom: 1rem;
      font-style: italic;
    }

    .alert-box {
      background-color: #ffcccc;
      color: #a10000;
      padding: 10px 15px;
      border: 1px solid #ff5c5c;
      border-radius: 5px;
      text-align: center;
      margin-bottom: 15px;
      animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    
    /* Responsive styles */
    @media (max-width: 480px) {
      .signup-container {
        padding: 1.5rem;
      }
      body {
        padding: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <h2>Create an Account 🧿</h2>

    <div id="alert-box" class="alert-box" style="display: none;"></div>

    <form action="https://mydiary.gt.tc/register.php" method="POST" class="form-container" onsubmit="return validateForm()" enctype="multipart/form-data"> 
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required>
      
      <label for="auspicious_day_info">Auspicious Day (Why are you starting today?)</label>
      <input type="text" id="auspicious_day_info" name="auspicious_day_info" required>
      
      <label for="profile_picture">Profile Picture</label>
      <input type="file" id="profile_picture" name="profile_picture" required>

      <label for="current_aim">Current Aim</label>
      <input type="text" id="current_aim" name="current_aim" required>
      <span class="small-note">(This aim cannot be changed for 1 month)</span>

      <button type="submit">Sign Up</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="https://mydiary.gt.tc/loginfrontend.php">Login</a>
    </div>
  </div>

  <script>
    // Show custom alert from URL
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
      const alertBox = document.getElementById('alert-box');
      alertBox.style.display = 'block';
      alertBox.textContent = error;

      // Optional: auto-hide after few seconds
      setTimeout(() => {
        alertBox.style.display = 'none';
      }, 4000);
    }

    function validateForm() {
      const password = document.getElementById("password").value;
      const confirm = document.getElementById("confirm_password").value;
      if (password !== confirm) {
        // Using a custom alert box instead of the browser's alert()
        const alertBox = document.getElementById('alert-box');
        alertBox.style.display = 'block';
        alertBox.textContent = "Passwords do not match!";
        setTimeout(() => {
          alertBox.style.display = 'none';
        }, 4000);
        return false;
      }
      return true;
    }
  </script>

  <script>
  const bgImages = [
    'imgs/evileye1.jpeg',
    'imgs/evileye2.jpeg',
    'imgs/evileye3.jpeg',
    'imgs/evileye4.jpeg',
    'imgs/evileye5.jpeg',
    'imgs/evileye6.jpeg',
    'imgs/evileye7.jpeg',
    'imgs/evileye8.jpeg'
  ];

  const randomIndex = Math.floor(Math.random() * bgImages.length);
  document.body.style.backgroundImage = `url('${bgImages[randomIndex]}')`;
</script>
</body>
</html>
