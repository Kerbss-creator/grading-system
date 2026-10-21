document.addEventListener("DOMContentLoaded", function () {
    const profileBtn = document.getElementById("profileBtn");
    const profileWrapper = document.querySelector(".profile-wrapper");
    const notificationPanel = document.getElementById("notificationPanel");

    // Stop if profile elements are missing
    if (!profileBtn || !profileWrapper) {
        return;
    }

    // OPEN / CLOSE PROFILE
    profileBtn.addEventListener("click", function (event) {
        event.stopPropagation();

        // Close notification panel
        if (notificationPanel) {
            notificationPanel.classList.remove("show");
        }

        // Toggle profile dropdown
        profileWrapper.classList.toggle("active");
    });

    // CLOSE PROFILE WHEN CLICKING OUTSIDE
    document.addEventListener("click", function (event) {
        if (!profileWrapper.contains(event.target)) {
            profileWrapper.classList.remove("active");
        }
    });

    // CLOSE PROFILE WHEN PRESSING ESCAPE
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            profileWrapper.classList.remove("active");
        }
    });
});
