<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forbes Academy Login</title>
    <link rel="stylesheet" href="/css/login.css" />
  </head>
  <body>
    <div class="container">
      <!-- LEFT PANEL -->
      <div class="left-panel">
        <img src="/IMAGES/forbesologo.png" class="logo" />

        <h2>Forbes Academy</h2>
        <p>Junior High School</p>
        <p>Grading System</p>
      </div>

      <!-- RIGHT PANEL -->
      <div class="right-panel">
        <div class="login-box">
          <h1>Welcome Back!</h1>
          <p>Login to access the grading system</p>


          <form>
            <label>Email Address</label>
            <input type="email" placeholder="Enter your email" />

            <label>Password</label>
            <input type="password"  placeholder="Enter your password" />

            <a href="#NEEDMODALFORTHIS" class="forgot">Forgot password?</a>

            <button type="button" class="login-btn" id="loginBtn">Login</button>
          </form>
        </div>
      </div>
    </div>

    <script src="/JS/login.js"></script>
  </body>
</html>
