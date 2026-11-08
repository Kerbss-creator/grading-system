document.addEventListener("DOMContentLoaded", () => {
    const subjectSearch = document.getElementById("subjectSearch");
    const gradeFilter = document.getElementById("gradeFilter");
    const statusFilter = document.getElementById("statusFilter");
    const subjectTableBody = document.getElementById("subjectTableBody");
    const subjectEmptyState = document.getElementById("subjectEmptyState");
    const subjectCount = document.getElementById("subjectCount");

    if (!subjectTableBody) {
        console.error("Subject table body not found.");
        return;
    }

    // Subject name to database subject code
    const subjectCodes = {
        Mathematics: "MATH",
        Science: "SCI",
        English: "ENG",
        Filipino: "FIL",
        "Araling Panlipunan": "AP",
        TLE: "TLE",
        MAPEH: "MAPEH",
        "Values Education": "VE",
        Computer: "COMP",
    };

    const rows = Array.from(subjectTableBody.querySelectorAll("tr"));

    // Get subject name from row
    const getSubjectName = (row) => {
        return (
            row.dataset.subject ||
            row.querySelector(".view-btn")?.dataset.subject ||
            row
                .querySelector(".subject-name span:last-child")
                ?.textContent.trim() ||
            ""
        );
    };

    // Filter subjects
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

            if (isVisible) visibleCount++;
        });

        if (subjectEmptyState) {
            subjectEmptyState.hidden = visibleCount !== 0;
        }

        if (subjectCount) {
            subjectCount.textContent = `Showing ${visibleCount} of ${rows.length} subjects`;
        }
    };

    // Navigate to Subject Details
    const openSubjectDetails = (subjectName) => {
        const subjectCode = subjectCodes[subjectName];

        if (!subjectCode) {
            console.error("Subject code not found for:", subjectName);
            return;
        }

        const destination = new URL(
            "/admin/subject-details",
            window.location.origin,
        );

        destination.searchParams.set("code", subjectCode);

        window.location.href = destination.href;
    };

    // Search and filters
    subjectSearch?.addEventListener("input", filterSubjects);
    gradeFilter?.addEventListener("change", filterSubjects);
    statusFilter?.addEventListener("change", filterSubjects);

    // Subject row and View button click
    subjectTableBody.addEventListener("click", (event) => {
        const row = event.target.closest("tr");

        if (!row || row.hidden) return;

        const subjectName =
            event.target.closest(".view-btn")?.dataset.subject ||
            getSubjectName(row);

        openSubjectDetails(subjectName);
    });

    // Keyboard accessibility
    subjectTableBody.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;

        const row = event.target.closest("tr");

        if (!row || row.hidden) return;

        if (event.target.closest("button, a, input, select")) return;

        event.preventDefault();
        openSubjectDetails(getSubjectName(row));
    });

    // Make subject rows keyboard accessible
    rows.forEach((row) => {
        row.tabIndex = 0;
        row.style.cursor = "pointer";

        row.setAttribute("aria-label", `View ${getSubjectName(row)} details`);
    });

    // Initial display
    filterSubjects();
});
