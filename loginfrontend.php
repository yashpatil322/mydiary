<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | My Diary</title>
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

    .login-box {
      background: #ffffff; /* Solid white */
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
      width: 100%;
      max-width: 400px;
      color: #333;
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #003366; /* Evil eye dark blue */
    }

    .login-box input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
    }

    .login-box button {
      width: 100%;
      padding: 12px;
      border: none;
      background: #003366; /* Evil eye deep blue */
      color: #fff;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .signup-link {
      text-align: center;
      margin-top: 1rem;
    }

     .signup-link a {
      color: var(--navy);
      text-decoration: none;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }

    .login-box button:hover {
      background: #002244;
    }

    @media (max-width: 600px) {
      .login-box {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="background"></div>

  <div class="container">
    <form class="login-box" action="php/login.php" method="POST">
      <h2>Login</h2>
      <input type="text" name="username" placeholder="Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
       <div class="signup-link">
      Don't have an account? <a href="signup.html">Sign up</a>
    </div>
    </form>
  </div>
</body>
</html>


<!-- Another interface -->
 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | MyDiary</title>
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

    .login-container {
      background-color: var(--white);
      border: 2px solid var(--navy);
      border-radius: 12px;
      padding: 2rem;
      width: 320px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .login-container h2 {
      text-align: center;
      color: var(--deep-blue);
      margin-bottom: 1.5rem;
    }

    .custom-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #d4edda;
    color: #155724;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
  }

  .custom-alert .check {
    font-size: 20px;
  }

  .custom-alert .progress-bar {
    margin-top: 5px;
    height: 5px;
    width: 100%;
    background-color: #c3e6cb;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
  }

  .custom-alert .progress-bar::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 100%;
    background: #28a745;
    animation: shrink 5s linear forwards;
  }

  @keyframes shrink {
    from { width: 100%; }
    to { width: 0%; }
  }

@keyframes slide-down {
  from { opacity: 0; transform: translate(-50%, -20px); }
  to { opacity: 1; transform: translate(-50%, 0); }
}

.fade-out {
  opacity: 0;
  transition: opacity 1s ease-in-out;
}

    input[type="text"],
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

    .signup-link {
      text-align: center;
      margin-top: 1rem;
    }

    .signup-link a {
      color: var(--navy);
      text-decoration: none;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }
      #alert-box {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #d1f2eb;
    border-left: 6px solid #3498db;
    color: #1e4d6b;
    padding: 12px 20px;
    border-radius: 10px;
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .progress-bar {
    height: 4px;
    background: #3498db;
    width: 100%;
    margin-top: 5px;
    transition: width 0.1s linear;
    border-radius: 2px;
  }
  </style>
</head>
<body>
  <div class="login-container">
    <form action="../backend/login.php" method="POST" class="form-container">
  <h2>Login to Your Diary 🧿</h2>

  <label for="username">Username</label>
  <input type="text" id="username" name="username" required>

  <label for="password">Password</label>
  <input type="password" id="password" name="password" required>

  <button type="submit">Login</button>

  <div class="signup-link">
    Don't have an account? <a href="signup.html">Sign up</a>
  </div>
</form>
  </div>

<div id="alert-box" style="display: none;" class="custom-alert">
  <div class="check" id="alert-icon">✔️</div>
  <div>
    <span id="alert-msg"></span>
    <div class="progress-bar"></div>
  </div>
</div>

<script>
  const params = new URLSearchParams(window.location.search);
  const alertBox = document.getElementById("alert-box");
  const alertMsg = document.getElementById("alert-msg");
  const alertIcon = document.getElementById("alert-icon");

  // Handle registration success
  if (params.get("registered") === "true") {
    alertMsg.innerText = "Registration successful! You can now log in.";
    alertIcon.innerText = "✔️"; // Success icon
    alertBox.style.display = "block";
  }

  // Handle login errors
  if (params.has("error")) {
    const msg = decodeURIComponent(params.get("error"));
    alertMsg.innerText = msg;
    alertIcon.innerText = "⚠️"; // Error icon
    alertBox.style.display = "block";
  }

  // Animate alert box disappearing
  if (alertBox.style.display === "block") {
    const progress = alertBox.querySelector(".progress-bar");
    let width = 100;
    const interval = setInterval(() => {
      width -= 2;
      progress.style.width = width + "%";
      if (width <= 0) {
        clearInterval(interval);
        alertBox.style.display = "none";
      }
    }, 100);
  }
</script>


</body>
</html>
