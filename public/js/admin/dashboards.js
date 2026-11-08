document.addEventListener("DOMContentLoaded", function () {
    // ANNOUNCEMENT MODAL ELEMENTS
    const viewAllBtn = document.getElementById("viewAllAnnouncementsBtn");
    const announcementModal = document.getElementById("announcementModal");
    const closeAnnouncementBtn = document.getElementById(
        "closeAnnouncementModal",
    );

    // OPEN MODAL
    function openModal() {
        if (!announcementModal) return;

        announcementModal.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    // CLOSE MODAL
    function closeModal() {
        if (!announcementModal) return;

        announcementModal.classList.remove("active");
        document.body.style.overflow = "";
    }

    // VIEW ALL ANNOUNCEMENTS
    if (viewAllBtn && announcementModal) {
        viewAllBtn.addEventListener("click", function () {
            openModal();
        });
    }

    // CLOSE BUTTON
    if (closeAnnouncementBtn && announcementModal) {
        closeAnnouncementBtn.addEventListener("click", function () {
            closeModal();
        });
    }

    // CLOSE WHEN CLICKING OUTSIDE MODAL
    if (announcementModal) {
        announcementModal.addEventListener("click", function (event) {
            if (event.target === announcementModal) {
                closeModal();
            }
        });
    }

    // CLOSE WHEN PRESSING ESCAPE
    document.addEventListener("keydown", function (event) {
        if (
            event.key === "Escape" &&
            announcementModal &&
            announcementModal.classList.contains("active")
        ) {
            closeModal();
        }
    });
});
