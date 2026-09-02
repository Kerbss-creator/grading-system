// NOTIFICATION

document.addEventListener("DOMContentLoaded", function () {
  const notificationBtn = document.getElementById("notificationBtn");

  const notificationPanel = document.getElementById("notificationPanel");

  const notificationClose = document.getElementById("notificationClose");

  const profileWrapper = document.querySelector(".profile-wrapper");

  if (!notificationBtn || !notificationPanel) {
    console.log("Notification elements not found.");
    return;
  }

  // OPEN / CLOSE NOTIFICATION

  notificationBtn.addEventListener("click", function (event) {
    event.stopPropagation();

    // Close admin profile
    if (profileWrapper) {
      profileWrapper.classList.remove("active");
    }

    // Toggle notification
    notificationPanel.classList.toggle("show");
  });

  // CLOSE BUTTON

  if (notificationClose) {
    notificationClose.addEventListener("click", function (event) {
      event.stopPropagation();

      notificationPanel.classList.remove("show");
    });
  }

  // CLICK OUTSIDE NOTIFICATION

  document.addEventListener("click", function (event) {
    if (
      !notificationPanel.contains(event.target) &&
      !notificationBtn.contains(event.target)
    ) {
      notificationPanel.classList.remove("show");
    }
  });

  // ESC KEY

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      notificationPanel.classList.remove("show");
    }
  });
});
