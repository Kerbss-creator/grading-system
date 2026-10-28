/* ==========================================
   GRADE LEVEL MANAGEMENT
   Forbes Academy Admin
========================================== */

document.addEventListener("DOMContentLoaded", () => {
    const gradeGrid = document.getElementById("gradeGrid");
    const gradeDirectory = document.getElementById("gradeLevelDirectory");
    const gradeDetailsView = document.getElementById("gradeDetailsView");
    const backToGradesButton = document.getElementById("backToGrades");

    const detailsGradeTitle = document.getElementById("detailsGradeTitle");
    const detailsStudentCount = document.getElementById("detailsStudentCount");
    const academicRecordsGradeLabel = document.getElementById(
        "academicRecordsGradeLabel",
    );

    /* ==========================================
       CHECK REQUIRED ELEMENTS
    ========================================== */

    if (
        !gradeGrid ||
        !gradeDirectory ||
        !gradeDetailsView ||
        !backToGradesButton ||
        !detailsGradeTitle ||
        !detailsStudentCount
    ) {
        console.warn(
            "Grade Level Management: Required HTML elements were not found.",
        );
        return;
    }

    const gradeCards = Array.from(gradeGrid.querySelectorAll(".grade-card"));

    /* ==========================================
       INITIALIZE DIRECTORY HISTORY STATE
    ========================================== */

    // Set the current page as the Grade Level Directory.
    // This prevents an old or unexpected history state
    // from interfering with the Back button.
    if (!window.history.state?.gradeDetailsOpen) {
        window.history.replaceState(
            { gradeDetailsOpen: false },
            "",
            window.location.pathname + window.location.search,
        );
    }

    /* ==========================================
       GRADE DETAILS ANIMATION
    ========================================== */

    const animateGradeDetails = () => {
        gradeDetailsView.classList.remove("grade-details-content");

        // Restart the CSS animation.
        void gradeDetailsView.offsetWidth;

        gradeDetailsView.classList.add("grade-details-content");
    };

    /* ==========================================
       SHOW GRADE DETAILS
    ========================================== */

    const showGradeDetails = (gradeCard, updateHistory = true) => {
        const selectedGrade = gradeCard.dataset.grade;
        const studentCount = gradeCard.dataset.students;

        if (!selectedGrade) {
            return;
        }

        detailsGradeTitle.textContent = selectedGrade;
        detailsStudentCount.textContent = studentCount || "—";

        if (academicRecordsGradeLabel) {
            academicRecordsGradeLabel.textContent = selectedGrade;
        }

        gradeDirectory.hidden = true;
        gradeDetailsView.hidden = false;

        animateGradeDetails();

        if (updateHistory) {
            const currentState = window.history.state;

            const nextState = {
                gradeDetailsOpen: true,
                selectedGrade: selectedGrade,
            };

            // Update the existing details entry when already
            // viewing details. Avoid duplicate history entries.
            if (currentState?.gradeDetailsOpen) {
                window.history.replaceState(nextState, "", "#grade-details");
            } else {
                window.history.pushState(nextState, "", "#grade-details");
            }
        }

        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    /* ==========================================
       SHOW GRADE LEVEL DIRECTORY
    ========================================== */

    const showGradeDirectory = () => {
        gradeDetailsView.hidden = true;
        gradeDirectory.hidden = false;

        gradeDetailsView.classList.remove("grade-details-content");

        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    /* ==========================================
       GRADE CARD CLICK EVENTS
    ========================================== */

    gradeCards.forEach((gradeCard) => {
        gradeCard.addEventListener("click", (event) => {
            event.preventDefault();
            showGradeDetails(gradeCard);
        });
    });

    /* ==========================================
       BACK TO GRADE LEVEL DIRECTORY
    ========================================== */

    backToGradesButton.addEventListener("click", (event) => {
        event.preventDefault();

        if (window.history.state?.gradeDetailsOpen) {
            window.history.back();
            return;
        }

        // Fallback if the current history entry is not
        // a Grade Details entry.
        showGradeDirectory();
    });

    /* ==========================================
       BROWSER BACK / FORWARD NAVIGATION
    ========================================== */

    window.addEventListener("popstate", (event) => {
        const state = event.state;

        if (state?.gradeDetailsOpen && state.selectedGrade) {
            const matchingCard = gradeCards.find(
                (gradeCard) => gradeCard.dataset.grade === state.selectedGrade,
            );

            if (matchingCard) {
                showGradeDetails(matchingCard, false);
                return;
            }
        }

        showGradeDirectory();
    });

    /* ==========================================
       ACADEMIC RECORDS - BY TERM DROPDOWN
    ========================================== */

    const academicRecordsTable = document.querySelector(".records-table");
    const byTermFilter = document.getElementById("byTermFilter");

    if (academicRecordsTable && byTermFilter) {
        const termColumns =
            academicRecordsTable.querySelectorAll("[data-term-column]");

        const emptyStateCell = academicRecordsTable.querySelector(
            ".academic-records-empty",
        );

        const validSelections = ["overall", "term1", "term2", "term3"];

        const setSelectedTerm = (selectedTerm) => {
            if (!validSelections.includes(selectedTerm)) {
                return;
            }

            termColumns.forEach((column) => {
                const isSelected = column.dataset.termColumn === selectedTerm;

                // Overall displays all term columns.
                // A specific term displays only that term.
                const shouldShow = selectedTerm === "overall" || isSelected;

                column.hidden = !shouldShow;
                column.classList.toggle(
                    "active-term-column",
                    selectedTerm !== "overall" && isSelected,
                );
            });

            // Update the empty-state cell to match the
            // number of currently visible table columns.
            if (emptyStateCell) {
                const visibleColumns = academicRecordsTable.querySelectorAll(
                    "thead th:not([hidden])",
                ).length;

                emptyStateCell.colSpan = visibleColumns;
            }
        };

        byTermFilter.addEventListener("change", () => {
            setSelectedTerm(byTermFilter.value);
        });

        // Default: show all terms and the overall columns.
        setSelectedTerm(byTermFilter.value || "overall");
    }
});
