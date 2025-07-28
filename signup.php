<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up | My Diary</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body, html {
      height: 100%;
    }

    .background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url("imgs/evileye.jpeg") no-repeat center center/cover;
      filter: blur(10px);
      z-index: -1;
    }

    .container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .signup-box {
      background: #ffffff;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
      width: 100%;
      max-width: 450px;
      color: #333;
    }

    .signup-box h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #003366; /* Evil eye dark blue */
    }

    .signup-box input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
    }

    .signup-box button {
      width: 100%;
      padding: 12px;
      border: none;
      background: #003366; /* Evil eye blue */
      color: #fff;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
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

    .signup-box button:hover {
      background: #002244;
    }

    @media (max-width: 600px) {
      .signup-box {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="background"></div>

  <div class="container">
    <form class="signup-box" action="php/signup.php" method="POST">
      <h2>Sign Up</h2>
      <input type="text" name="username" placeholder="Username" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Create Account</button>
      <div class="login-link">
      Already have an account? <a href="index.html">Login</a>
    </div>
    </form>
  </div>
</body>
</html>


  <!--another interface -->
 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up | MyDiary</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      background-image: url(imgs/evileye.jpeg);
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--beige);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
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
      width: 350px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .signup-container h2 {
      text-align: center;
      color: var(--deep-blue);
      margin-bottom: 1.5rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 1rem;
      border: 1px solid var(--deep-blue);
      border-radius: 8px;
    }

    button {
      width: 100%;
      background-color: var(--light-blue);
      color: var(--white);
      border: none;
      padding: 10px;
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
  </style>
</head>
<body>
  <div class="signup-container">
    <h2>Create an Account 🧿</h2>

    <!-- Custom alert box -->
    <div id="alert-box" class="alert-box" style="display: none;"></div>

    <form action="register.php" method="POST" class="form-container" onsubmit="return validateForm()"> 
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required>

      <button type="submit">Sign Up</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="loginfrontend.php">Login</a>
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
        alert("Passwords do not match!");
        return false;
      }
      return true;
    }
  </script>

  <style>
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
  </style>
</body>
</html>
