

document.addEventListener("DOMContentLoaded", function () {
  const profileBtn = document.getElementById("profileBtn");

  const profileWrapper = document.querySelector(".profile-wrapper");

  const notificationPanel = document.getElementById("notificationPanel");

  const logoutBtn = document.getElementById("logoutBtn");

  if (!profileBtn || !profileWrapper) {
    console.log("Profile elements not found.");
    return;
  }

  profileBtn.addEventListener("click", function (event) {
    event.stopPropagation();

    if (notificationPanel) {
      notificationPanel.classList.remove("show");
    }

    profileWrapper.classList.toggle("active");
  });

  document.addEventListener("click", function (event) {
    if (!profileWrapper.contains(event.target)) {
      profileWrapper.classList.remove("active");
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      profileWrapper.classList.remove("active");
    }
  });
});

if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      window.location.href = "login.html";
    });
  }
