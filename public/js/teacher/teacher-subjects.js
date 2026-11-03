document.addEventListener("DOMContentLoaded", () => {
    const schoolYearSelect = document.getElementById("schoolYearSelect");
    const termSelect = document.getElementById("termSelect");
    const subjectLinks = document.querySelectorAll(".subject-card-link");

    /**
     * Update every subject card URL based on the selected filters.
     */
    function updateSubjectLinks() {
        const schoolYear = schoolYearSelect?.value || "2026-2027";
        const term = termSelect?.value || "1st-term";

        subjectLinks.forEach((link) => {
            const assignmentId = link.dataset.assignmentId;

            if (!assignmentId) {
                return;
            }

            const targetUrl = new URL(
                "/teacher/grade-management",
                window.location.origin,
            );

            targetUrl.searchParams.set("assignment", assignmentId);
            targetUrl.searchParams.set("school_year", schoolYear);
            targetUrl.searchParams.set("term", term);

            link.href = `${targetUrl.pathname}${targetUrl.search}`;
        });
    }

    if (schoolYearSelect) {
        schoolYearSelect.addEventListener("change", updateSubjectLinks);
    }

    if (termSelect) {
        termSelect.addEventListener("change", updateSubjectLinks);
    }

    updateSubjectLinks();
});
