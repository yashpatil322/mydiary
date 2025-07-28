<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome | MyDiary</title>
  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Segoe UI', sans-serif;
    }

    .background {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-image: url('imgs/evileye.jpeg'); /* Replace with your image path */
      background-size: cover;
      background-position: center;
      filter: blur(8px);
      z-index: -1;
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

    .content {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100%;
      padding: 0 20px; /* 👈 Add horizontal space */
    }

    .welcome-box {
      background-color: white;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      text-align: center;
      max-width: 400px;
      width: 100%;
    }

    .welcome-box h1 {
      color: #003049; /* Dark Blue (evil eye) */
      margin-bottom: 10px;
    }

    .welcome-box p {
      color: #555;
      margin-bottom: 30px;
    }

    .btn {
      padding: 10px 20px;
      margin: 10px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
      color: white;
    }

    .btn.signup {
      background-color: #0096c7; /* Evil Eye Blue */
    }

    .btn.login {
      background-color: #0077b6;
    }

    .btn:hover {
      opacity: 0.9;
    }

    @media (max-width: 600px) {
      .welcome-box {
        padding: 30px 20px;
      }
      .btn {
        width: 100%;
        margin: 8px 0;
      }
    }
  </style>
</head>
<body>
  <div class="background"></div>

  <div class="content">
    <div class="welcome-box">
      <h1>Welcome to MyDiary</h1>
      <p>Your private space to write, reflect, and grow.</p>
      <button class="btn signup" onclick="location.href='signup.html'">New User</button>
      <button class="btn login" onclick="location.href='login.html'">Existing User</button>
    </div>
  </div>
</body>
</html> 

 <!--Another Interface!-->

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

    .welcome-box {
      background-color: white;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      text-align: center;
      max-width: 400px;
      width: 100%;
    }

    .btn {
      padding: 10px 20px;
      margin: 10px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
      color: white;
    }

    .btn.signup {
      background-color: #0096c7; /* Evil Eye Blue */
    }

    .btn.login {
      background-color: #0077b6;
    }

    .btn:hover {
      opacity: 0.9;
    }

    @media (max-width: 600px) {
      .welcome-box {
        padding: 30px 20px;
      }
      .btn {
        width: 100%;
        margin: 8px 0;
      }
    }

    .welcome-box h1 {
      color: #003049; /* Dark Blue (evil eye) */
      margin-bottom: 10px;
    }

    .welcome-box p {
      color: #555;
      margin-bottom: 30px;
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
    <div class="welcome-box">
      <h1>Welcome to MyDiary</h1>
      <p>Your private space to write, reflect, and grow.</p>
      <button class="btn signup" onclick="location.href='signup.html'">New User</button>
      <button class="btn login" onclick="location.href='login.html'">Existing User</button>
    </div>
  </div>
</body>
</html>

