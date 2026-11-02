document.addEventListener("DOMContentLoaded", function () {
    const academicToggle = document.getElementById("academicRecordsToggle");
    const academicDropdown = academicToggle?.closest(".adviser-nav-dropdown");

    if (!academicToggle || !academicDropdown) {
        return;
    }

    academicToggle.addEventListener("click", function () {
        const isOpen = academicDropdown.classList.toggle("open");

        academicToggle.setAttribute("aria-expanded", String(isOpen));
    });

    // Highlight the selected subject based on the URL.
    const params = new URLSearchParams(window.location.search);
    const selectedSubject = params.get("subject");

    if (selectedSubject) {
        document.querySelectorAll(".adviser-sub-link").forEach(function (link) {
            const linkUrl = new URL(link.href);
            const linkSubject = linkUrl.searchParams.get("subject");

            if (linkSubject === selectedSubject) {
                link.classList.add("active");
            }
        });
    }
});
