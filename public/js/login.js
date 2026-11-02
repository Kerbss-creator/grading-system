

const loginForm = document.getElementById("loginForm");
const loginBtn = document.getElementById("loginBtn");
const loginLoader = document.getElementById("loginLoader");

if (loginForm) {

    loginForm.addEventListener("submit", function () {

        if (loginLoader) {
            loginLoader.classList.add("show");
        }

        if (loginBtn) {
            loginBtn.disabled = true;
            loginBtn.textContent = "Logging in...";
        }

    });

}
