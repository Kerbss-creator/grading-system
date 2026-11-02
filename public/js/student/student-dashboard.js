
document.addEventListener("DOMContentLoaded", function () {
    // Student dashboard announcements empty state
    const announcementEmpty = document.querySelector(
        ".student-announcement-empty"
    );

    if (announcementEmpty) {
        announcementEmpty.setAttribute("aria-live", "polite");
    }
});
