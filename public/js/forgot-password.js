
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("forgotPasswordForm");
    const resetBtn = document.getElementById("resetBtn");
    const backLogin = document.getElementById("backLogin");

    // FORM SUBMIT

    if (form) {
        form.addEventListener("submit", function () {

            resetBtn.disabled = true;

            resetBtn.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Sending...</span>
            `;

            document.body.classList.add("fade-out");
        });
    }

    // BACK TO LOGIN

    if (backLogin) {
        backLogin.addEventListener("click", function (event) {

            event.preventDefault();

            document.body.classList.add("fade-out");

            setTimeout(function () {
                window.location.href = backLogin.href;
            }, 500);

        });
    }

});

