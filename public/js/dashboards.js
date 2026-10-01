document.addEventListener("DOMContentLoaded", function () {
    const viewAllBtn = document.getElementById("viewAllAnnouncementsBtn");
    const announcementModal = document.getElementById("announcementModal");
    const closeAnnouncementBtn = document.getElementById(
        "closeAnnouncementModal",
    );

    // ANNOUNCEMENT MODAL
    if (viewAllBtn && announcementModal && closeAnnouncementBtn) {
        viewAllBtn.addEventListener("click", function () {
            announcementModal.classList.add("active");
            document.body.style.overflow = "hidden";
        });

        closeAnnouncementBtn.addEventListener("click", function () {
            announcementModal.classList.remove("active");
            document.body.style.overflow = "";
        });

        announcementModal.addEventListener("click", function (e) {
            if (e.target === announcementModal) {
                announcementModal.classList.remove("active");
                document.body.style.overflow = "";
            }
        });
    }
});
