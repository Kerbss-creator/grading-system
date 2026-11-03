// =====================================
// FORBES ACADEMY INTRO
// =====================================

const introScreen = document.getElementById("introScreen");

if (introScreen) {
    window.addEventListener("load", function () {
        setTimeout(function () {
            introScreen.classList.add("hide");
        }, 2500);
    });
}

// =====================================
// LOGIN LOADING SCREEN
// =====================================

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
