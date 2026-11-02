<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forbes Academy Login</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom-modal.css') }}">
</head>

<body>

    <div id="loginLoader" class="login-loader">
        <div class="loader-content">
            <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">

            <div class="spinner"></div>

            <p>Logging in...</p>
        </div>
    </div>

    <div class="container">

        <div class="left-panel">

            <img src="{{ asset('IMAGES/forbesologo.png') }}" class="logo" alt="Forbes Academy Logo">

            <h2>Forbes Academy</h2>

            <p>Junior High School</p>
            <p>Grading System</p>

        </div>

        <div class="right-panel">

            <div class="login-box">

                <h1>Welcome Back!</h1>

                <p>Login to access the grading system</p>

                @if ($errors->any())
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fecaca;">
                        <ul style="list-style-type: disc; padding-left: 1.5rem; margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" id="loginForm">

                    @csrf

                    <label for="email">Email Address / Username</label>

                    <input type="text" id="email" name="email" placeholder="Enter your email or username" required>

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

    <script src="{{ asset('JS/login.js') }}">
</script>

    @if($errors->any())<div data-flash-error hidden>{{ implode(PHP_EOL, $errors->all()) }}</div>@endif
    @if(session('success'))<div data-flash-success hidden>{{ session('success') }}</div>@endif
    <script src="{{ asset('js/custom-modal.js') }}?v=11"></script>
</body>

</html>
