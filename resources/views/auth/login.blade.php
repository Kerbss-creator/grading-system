<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forbes Academy Login</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>


    <!-- FORBES ACADEMY PREMIUM INTRO -->
    <div id="introScreen" class="intro-screen">

        <div class="intro-content">

            <div class="intro-logo-wrapper">
                <img src="{{ asset('images/forbesologo.png') }}" class="intro-logo" alt="Forbes Academy Logo">
            </div>

            <p class="intro-overline">WELCOME TO</p>

            <h1 class="intro-title">
                FORBES ACADEMY
            </h1>

            <p class="intro-subtitle">
                JUNIOR HIGH SCHOOL
            </p>

            <div class="intro-divider">
                <span></span>
            </div>

            <h2 class="intro-portal">
                ACADEMIC RECORDS PORTAL
            </h2>

            <p class="intro-description">
                WEB-BASED GRADING SYSTEM
            </p>

            <div class="intro-footer">
                <span>EXCELLENCE IN EDUCATION</span>
            </div>

        </div>

    </div>



    <!-- PREMIUM LOGIN LOADING SCREEN -->

    <div id="loginLoader" class="login-loader">

        <div class="loader-content">

            <div class="loader-logo-wrapper">

                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">

            </div>

            <h2 class="loader-school-name">
                FORBES ACADEMY
            </h2>

            <p class="loader-school-level">
                JUNIOR HIGH SCHOOL
            </p>

            <div class="loader-divider"></div>

            <p>SIGNING YOU IN</p>

            <span class="loader-description">
                Please wait while we prepare your account...
            </span>

            <div class="spinner"></div>

            <div class="loader-progress">
                <span></span>
            </div>

            <div class="loader-footer">
                EXCELLENCE IN EDUCATION
            </div>

        </div>

    </div>


    <!-- LOGIN CONTAINER -->
    <div class="container">

        <!-- LEFT PANEL -->
        <div class="left-panel">

            <img src="{{ asset('images/forbesologo.png') }}" class="logo" alt="Forbes Academy Logo">

            <h2>Forbes Academy</h2>

            <p>Junior High School</p>
            <p>Grading System</p>

        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">

            <div class="login-box">

                <h1>Welcome!</h1>

                <p>Login to access the grading system</p>

                <form action="{{ route('login') }}" method="POST" id="loginForm">

                    @csrf

                    <label for="email">Email Address</label>

                    <input type="email" id="email" name="email" placeholder="Enter your email"
                        value="{{ old('email') }}" required>

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

    <script src="{{ asset('js/login.js') }}"></script>

</body>

</html>