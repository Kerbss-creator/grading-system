
// LOGGINGIN
const loginBtn = document.getElementById("loginBtn");

loginBtn.addEventListener("click", function () {

    document.body.classList.add("fade-out");

    setTimeout(function () {
        window.location.href = "dashboard-admin.html";
    }, 700);

});