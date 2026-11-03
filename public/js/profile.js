document.addEventListener("DOMContentLoaded", function () {
    const profileBtn = document.getElementById("profileBtn");
    const profileWrapper = document.querySelector(".profile-wrapper");
    const notificationPanel = document.getElementById("notificationPanel");

    const logoutButtons = document.querySelectorAll(".js-logout");
    const logoutForm = document.getElementById("logoutForm");
    const logoutLoader = document.getElementById("logoutLoader");

    // =========================================
    // OPEN / CLOSE PROFILE
    // =========================================

    if (profileBtn && profileWrapper) {
        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation();

            if (notificationPanel) {
                notificationPanel.classList.remove("show");
            }

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
    }

    // =========================================
    // LOGOUT WITH LOADING SCREEN
    // =========================================

    if (logoutButtons.length && logoutForm && logoutLoader) {
        logoutButtons.forEach(function (button) {
            button.addEventListener("click", function (event) {
                // PREVENT DEFAULT LINK / BUTTON ACTION
                event.preventDefault();

                // PREVENT MULTIPLE LOGOUT REQUESTS
                if (logoutForm.dataset.submitting === "true") {
                    return;
                }

                logoutForm.dataset.submitting = "true";

                // CLOSE PROFILE DROPDOWN
                if (profileWrapper) {
                    profileWrapper.classList.remove("active");
                }

                // SHOW LOADING SCREEN
                logoutLoader.classList.add("show");

                // PREVENT REPEATED CLICKS
                logoutButtons.forEach(function (btn) {
                    btn.style.pointerEvents = "none";
                    btn.setAttribute("aria-disabled", "true");
                });

                // WAIT 3 SECONDS BEFORE LOGOUT
                setTimeout(function () {
                    logoutForm.requestSubmit();
                }, 1500);
            });
        });
    }
});
