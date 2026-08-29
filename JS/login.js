
// ANIMATION
const buttons = document.querySelectorAll(".role");
const container = document.querySelector(".container");

buttons.forEach((button) => {

  button.addEventListener("click", function () {

    // Remove active from all buttons
    buttons.forEach((btn) => {
      btn.classList.remove("active");
    });

    // Add active to clicked button
    this.classList.add("active");

    // Get selected role
    const role = this.dataset.role;

    // Student = slide
    if (role === "student") {
      container.classList.add("student-mode");
    } 
    
    // Teacher/Admin = original position
    else {
      container.classList.remove("student-mode");
    }

  });

});

// LOGGINGIN
const loginBtn = document.getElementById("loginBtn");

loginBtn.addEventListener("click", function () {

    document.body.classList.add("fade-out");

    setTimeout(function () {
        window.location.href = "dashboard-admin.html";
    }, 700);

});