
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invalid Setup Link | Forbes Academy</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

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

                <h1>Link Unavailable</h1>

                <p>
                    This password setup link is invalid, expired, or has already been used.
                </p>

                <p>
                    Please contact your school administrator for a new setup link.
                </p>

                <a href="{{ route('login') }}" class="login-btn"
                    style="display:block; text-align:center; text-decoration:none;">
                    Back to Login
                </a>

            </div>

        </div>

    </div>

</body>

</html>
