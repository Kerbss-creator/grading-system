document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("subjectSearch");
    const termFilter = document.getElementById("termFilter");
    const schoolYearFilter = document.getElementById("schoolYearFilter");
    const tableBody = document.getElementById("gradesTableBody");
    const emptyState = document.getElementById("gradesEmptyState");
    const totalSubjects = document.getElementById("totalSubjects");
    const summaryTerm = document.getElementById("summaryTerm");
    const summarySchoolYear = document.getElementById("summarySchoolYear");
    const visibleSubjects = document.getElementById("visibleSubjects");

    if (!searchInput || !termFilter || !schoolYearFilter || !tableBody) {
        return;
    }

    const rows = Array.from(tableBody.querySelectorAll("tr[data-subject]"));

    const termNames = {
        1: "1st Term",
        2: "2nd Term",
        3: "3rd Term",
    };

    function updateGrades() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const selectedTerm = termFilter.value;
        const selectedSchoolYear = schoolYearFilter.value;
        let visibleCount = 0;

        rows.forEach(function (row) {
            const subjectName = row.dataset.subject.toLowerCase();
            const matchesSearch = subjectName.includes(searchTerm);

            // Sample UI data only. Replace with approved database records later.
            const grade = row.dataset["term" + selectedTerm];
            const gradeCell = row.querySelector(".grade-value");
            const remarks = row.querySelector(".remarks-text");
            const status = row.querySelector(".grade-status");

            if (grade && gradeCell && remarks && status) {
                gradeCell.textContent = grade;
                remarks.textContent = Number(grade) >= 75 ? "Passed" : "Failed";
                status.textContent = "Released";
            } else if (gradeCell && remarks && status) {
                gradeCell.textContent = "—";
                remarks.textContent = "Not available";
                status.textContent = "No grade";
            }

            // School year is displayed in the summary.
            // Actual year-based filtering requires database records.
            row.hidden = !matchesSearch;
            if (matchesSearch) {
                visibleCount++;
            }
        });

        summaryTerm.textContent = termNames[selectedTerm] || "Selected Term";
        summarySchoolYear.textContent =
            "School Year " + selectedSchoolYear.replace("-", "–");

        totalSubjects.textContent = rows.length;
        visibleSubjects.textContent =
            "Showing " +
            visibleCount +
            (visibleCount === 1 ? " subject" : " subjects");

        emptyState.hidden = visibleCount !== 0;
        tableBody.hidden = visibleCount === 0;
    }

    searchInput.addEventListener("input", updateGrades);
    termFilter.addEventListener("change", updateGrades);
    schoolYearFilter.addEventListener("change", updateGrades);

    updateGrades();
});
