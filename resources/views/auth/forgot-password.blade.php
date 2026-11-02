<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password | Forbes Academy</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-modal.css') }}">
</head>

<body>

    <div class="forgot-container">

        <div class="forgot-card">

            <div class="logo-container">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

            <h1>Forgot Password?</h1>

            <p class="description">
                Enter your registered email address and we'll send you
                a link to reset your password.
            </p>

            @if (session('status'))
                <div class="success-message">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('status') }}
                </div>
            @endif

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

            <a href="{{ route('login') }}" class="back-login" id="backLogin">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Login
            </a>

        </div>

    </div>

    <script src="{{ asset('js/forgot-password.js') }}">
</script>

    @if($errors->any())<div data-flash-error hidden>{{ implode(PHP_EOL, $errors->all()) }}</div>@endif
    @if(session('success'))<div data-flash-success hidden>{{ session('success') }}</div>@endif
    <script src="{{ asset('js/custom-modal.js') }}?v=11"></script>
</body>

</html>
