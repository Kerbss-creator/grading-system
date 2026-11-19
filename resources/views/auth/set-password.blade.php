
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Set Password | Forbes Academy</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="container">

        <!-- LEFT PANEL -->
        <div class="left-panel">

            <img src="{{ asset('images/forbesologo.png') }}"
                 class="logo"
                 alt="Forbes Academy Logo">

            <h2>Forbes Academy</h2>

            <p>Junior High School</p>
            <p>Grading System</p>

        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">

            <div class="login-box">

                <h1>Set Your Password</h1>

                <p>Create a secure password for your account.</p>

                <form action="{{ route('account.setup.store', ['token' => $token]) }}"
                      method="POST">

                    @csrf

                    <!-- NEW PASSWORD -->
                    <label for="password">New Password</label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your new password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <svg class="eye-icon" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>

                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <label for="password_confirmation">
                        Confirm Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm your new password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_confirmation"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <svg class="eye-icon" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>

                    </div>

                    <!-- VALIDATION ERRORS -->
                    @if ($errors->any())
                        <div class="error-message">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="login-btn">
                        Set Password
                    </button>

                </form>

            </div>

        </div>

    </div>

    <!-- SHOW / HIDE PASSWORD SCRIPT -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const toggleButtons = document.querySelectorAll(".toggle-password");

            toggleButtons.forEach(function (button) {

                button.addEventListener("click", function () {

                    const targetId = this.dataset.target;
                    const passwordInput = document.getElementById(targetId);

                    if (!passwordInput) return;

                    const isPasswordHidden =
                        passwordInput.type === "password";

                    passwordInput.type = isPasswordHidden
                        ? "text"
                        : "password";

                    this.setAttribute(
                        "aria-label",
                        isPasswordHidden ? "Hide password" : "Show password"
                    );

                    this.setAttribute(
                        "aria-pressed",
                        isPasswordHidden ? "true" : "false"
                    );

                    this.innerHTML = isPasswordHidden
                        ? `
                            <svg class="eye-icon" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M3 3l18 18"/>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.2A11 11 0 0 1 12 5c6.5 0 10 7 10 7a16 16 0 0 1-4 4.8"/>
                                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7a10 10 0 0 0 3-.5"/>
                            </svg>
                        `
                        : `
                            <svg class="eye-icon" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        `;

                });

            });

        });
    </script>

</body>
</html>
