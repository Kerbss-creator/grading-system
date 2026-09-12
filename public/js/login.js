// LOGIN

const loginBtn = document.getElementById("loginBtn");
const loginLoader = document.getElementById("loginLoader");

if (loginBtn) {
    loginBtn.addEventListener("click", function () {

        // Show loading screen
        if (loginLoader) {
            loginLoader.classList.add("show");
        }

        // Prevent clicking again
        loginBtn.disabled = true;

        // Go to Laravel dashboard
        setTimeout(function () {
            window.location.href = "/admin/dashboard";
        }, 800);

    });
}
