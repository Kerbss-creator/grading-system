document.addEventListener("DOMContentLoaded", function () {
    // Get all announcement cards
    const announcementCards = document.querySelectorAll(".announcement-card");

    // Get the empty announcement message
    const emptyAnnouncements = document.getElementById("emptyAnnouncements");

    // Show empty message if there are no announcements
    if (announcementCards.length === 0 && emptyAnnouncements) {
        emptyAnnouncements.style.display = "block";
    }
});
document.addEventListener("DOMContentLoaded", function () {

    // Announcement elements
    const announcementsSection = document.querySelector(
        ".announcements-section"
    );

    const emptyAnnouncements = document.getElementById(
        "emptyAnnouncements"
    );

    // Check announcement cards
    function checkAnnouncements() {

        const announcementCards =
            announcementsSection.querySelectorAll(
                ".announcement-card"
            );

        if (announcementCards.length === 0) {

            emptyAnnouncements.style.display = "flex";

        } else {

            emptyAnnouncements.style.display = "none";

        }
    }

    // Initial check
    checkAnnouncements();

});