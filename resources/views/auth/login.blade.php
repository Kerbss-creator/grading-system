<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forbes Academy Login</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <!-- LOGIN LOADING SCREEN -->
    <div id="loginLoader" class="login-loader">
        <div class="loader-content">
            <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">

            <div class="spinner"></div>

            <p>Logging in...</p>
        </div>
    </div>

    <div class="container">

        <!-- LEFT PANEL -->
        <div class="left-panel">

            <img src="{{ asset('IMAGES/forbesologo.png') }}" class="logo" alt="Forbes Academy Logo">

            <h2>Forbes Academy</h2>

            <p>Junior High School</p>
            <p>Grading System</p>

        </div>


        <!-- RIGHT PANEL -->
        <div class="right-panel">

            <div class="login-box">

                <h1>Welcome Back!</h1>

                <p>Login to access the grading system</p>


                <form action="#" method="POST">

                    @csrf

                    <label for="email">Email Address</label>

                    <input type="email" id="email" name="email" placeholder="Enter your email" required>


                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>


                    <a href="{{ route('password.request') }}" class="forgot">
                        Forgot password?
                    </a>


                    <button type="submit" class="login-btn" id="loginBtn">
                        Login
                    </button>

                </form>

            </div>

        </div>

    </div>


    <script src="{{ asset('JS/login.js') }}"></script>

</body>

</html>