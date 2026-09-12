<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password | Forbes Academy</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Forgot Password CSS -->
    <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}">
</head>

<body>

    <div class="forgot-container">

        <div class="forgot-card">

            <!-- Logo -->
            <div class="logo-container">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

            <!-- Heading -->
            <h1>Forgot Password?</h1>

            <p class="description">
                Enter your registered email address and we'll send you
                a link to reset your password.
            </p>

            <!-- Success Message -->
            @if (session('status'))
                <div class="success-message">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('status') }}
                </div>
            @endif

            <!-- Error Message -->
            @if ($errors->any())
                <div class="error-message">
                    <i class="fa-solid fa-circle-exclamation"></i>

                    <div>
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Forgot Password Form -->
            <form method="POST" id="forgotPasswordForm">

                @csrf

                <div class="input-group">

                    <label for="email">Email Address</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>

                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="Enter your email address" required autocomplete="email">
                    </div>

                </div>

                <button type="submit" id="resetBtn">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Reset Link</span>
                </button>

            </form>

            <!-- Back to Login -->
            <a href="{{ route('login') }}" class="back-login" id="backLogin">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Login
            </a>

        </div>

    </div>

    <!-- Forgot Password JS -->
    <script src="{{ asset('js/forgot-password.js') }}"></script>

</body>

</html>
