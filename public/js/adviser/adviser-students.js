document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("studentSearch");
    const genderFilter = document.getElementById("genderFilter");
    const tableBody = document.getElementById("studentsTableBody");
    const emptyMessage = document.getElementById("studentsEmpty");
    const countLabel = document.getElementById("studentCount");

    if (!searchInput || !genderFilter || !tableBody) return;

    const studentRows = Array.from(
        tableBody.querySelectorAll(".academic-student-row"),
    );

    function filterStudents() {
        const searchValue = searchInput.value.trim().toLowerCase();
        const selectedGender = genderFilter.value.toLowerCase();

        let visibleCount = 0;

        studentRows.forEach((row) => {
            const searchText = row.dataset.search || "";
            const gender = (row.dataset.gender || "").toLowerCase();

            const matchesSearch = searchText.includes(searchValue);
            const matchesGender =
                selectedGender === "" || gender === selectedGender;

            const shouldShow = matchesSearch && matchesGender;

            row.style.display = shouldShow ? "" : "none";

            if (shouldShow) {
                visibleCount++;
            }
        });

        if (emptyMessage) {
            emptyMessage.hidden = visibleCount !== 0;
        }

        if (countLabel) {
            countLabel.textContent =
                "Showing " +
                visibleCount +
                (visibleCount === 1 ? " student" : " students");
        }
    }

    searchInput.addEventListener("input", filterStudents);
    genderFilter.addEventListener("change", filterStudents);

    filterStudents();
});
