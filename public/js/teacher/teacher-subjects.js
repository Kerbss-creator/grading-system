document.addEventListener("DOMContentLoaded", () => {
    const schoolYearSelect = document.getElementById("schoolYearSelect");
    const termSelect = document.getElementById("termSelect");

    /**
     * Reload the Teacher My Subjects page
     * using the selected filters.
     */
    function updateSubjectFilters(changedFilter) {
        const schoolYear = schoolYearSelect?.value;
        const term = termSelect?.value;

        if (!schoolYear) {
            return;
        }

        const targetUrl = new URL(window.location.href);

        // Apply the selected school year.
        targetUrl.searchParams.set("school_year", schoolYear);

        if (changedFilter === "school_year") {
            // Let the controller use the selected year's current term.
            targetUrl.searchParams.delete("term");
        } else if (term) {
            // Apply the selected term.
            targetUrl.searchParams.set("term", term);
        }

        window.location.href = targetUrl.toString();
    }

    /**
     * Reload page when School Year changes.
     */
    schoolYearSelect?.addEventListener("change", () => {
        updateSubjectFilters("school_year");
    });

    /**
     * Reload page when Term changes.
     */
    termSelect?.addEventListener("change", () => {
        updateSubjectFilters("term");
    });
});
