document.addEventListener("DOMContentLoaded", () => {
    const subjectSearch = document.getElementById("subjectSearch");
    const gradeFilter = document.getElementById("gradeFilter");
    const statusFilter = document.getElementById("statusFilter");
    const subjectTableBody = document.getElementById("subjectTableBody");
    const subjectEmptyState = document.getElementById("subjectEmptyState");
    const subjectCount = document.getElementById("subjectCount");

    if (!subjectTableBody) {
        return;
    }

    const rows = Array.from(subjectTableBody.querySelectorAll("tr"));

    const getSubjectName = (row) => {
        return (
            row.dataset.subject ||
            row.querySelector(".subject-name")?.textContent.trim() ||
            ""
        );
    };

    const filterSubjects = () => {
        const searchValue = subjectSearch?.value.trim().toLowerCase() || "";
        const selectedGrade = gradeFilter?.value || "";
        const selectedStatus = statusFilter?.value.toLowerCase() || "";

        let visibleCount = 0;

        rows.forEach((row) => {
            const subjectName = getSubjectName(row).toLowerCase();
            const grades = (row.dataset.grades || "").split(",");
            const status = (row.dataset.status || "").toLowerCase();

            const matchesSearch = subjectName.includes(searchValue);
            const matchesGrade =
                !selectedGrade || grades.includes(selectedGrade);
            const matchesStatus = !selectedStatus || status === selectedStatus;

            const isVisible = matchesSearch && matchesGrade && matchesStatus;

            row.hidden = !isVisible;

            if (isVisible) {
                visibleCount++;
            }
        });

        if (subjectEmptyState) {
            subjectEmptyState.hidden = visibleCount !== 0;
        }

        if (subjectCount) {
            subjectCount.textContent = `Showing ${visibleCount} of ${rows.length} subjects`;
        }
    };

    const openSubjectDetails = (subjectName) => {
        if (!subjectName) {
            return;
        }

        const destination = new URL(
            "/admin/subject-details",
            window.location.origin,
        );

        destination.searchParams.set("subject", subjectName);
        window.location.href = destination.href;
    };

    subjectSearch?.addEventListener("input", filterSubjects);
    gradeFilter?.addEventListener("change", filterSubjects);
    statusFilter?.addEventListener("change", filterSubjects);

    subjectTableBody.addEventListener("click", (event) => {
        const row = event.target.closest("tr");

        if (!row || row.hidden) {
            return;
        }

        const subjectName = getSubjectName(row);
        const clickedButton = event.target.closest(".view-btn");

        if (clickedButton) {
            openSubjectDetails(clickedButton.dataset.subject || subjectName);
            return;
        }

        openSubjectDetails(subjectName);
    });

    subjectTableBody.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") {
            return;
        }

        const row = event.target.closest("tr");

        if (
            !row ||
            row.hidden ||
            event.target.closest("button, a, input, select")
        ) {
            return;
        }

        event.preventDefault();
        openSubjectDetails(getSubjectName(row));
    });

    rows.forEach((row) => {
        row.tabIndex = 0;
        row.setAttribute("aria-label", `View ${getSubjectName(row)} details`);
    });

    filterSubjects();
});
