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
    const detailsAdviserSummary = document.getElementById(
        "detailsAdviserSummary",
    );
    const detailsSchoolYear = document.getElementById("detailsSchoolYear");

    const subjectAssignmentsList = document.getElementById(
        "subjectAssignmentsList",
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
        const adviser = gradeCard.dataset.adviser || "Not assigned";
        const schoolYear = gradeCard.dataset.schoolYear || "Not Set";

        let subjects = [];

        try {
            subjects = JSON.parse(gradeCard.dataset.subjects || "[]");
        } catch (error) {
            console.error("Unable to read subject assignments:", error);
        }

        if (!selectedGrade) {
            return;
        }

        detailsGradeTitle.textContent = selectedGrade;
        detailsStudentCount.textContent = studentCount || "0";

        // Assigned Adviser
        if (detailsAdviserSummary) {
            detailsAdviserSummary.textContent =
                adviser.trim() || "Not assigned";
        }

        // School year
        if (detailsSchoolYear) {
            detailsSchoolYear.textContent = schoolYear;
        }

        /* ==========================================
           SUBJECT ASSIGNMENTS
        ========================================== */

        if (subjectAssignmentsList) {
            subjectAssignmentsList.replaceChildren();

            if (subjects.length === 0) {
                subjectAssignmentsList.innerHTML = `
                    <div class="empty-state compact-empty-state">
                        <span class="empty-state-icon">
                            <i class="fa-regular fa-folder-open"></i>
                        </span>

                        <h4>No Subject Assignments Yet</h4>

                        <p>
                            Subject and teacher assignments will appear here
                            once the records are available.
                        </p>
                    </div>
                `;
            } else {
                subjects.forEach((subject) => {
                    const subjectCard = document.createElement("div");
                    subjectCard.className = "subject-assignment-card";

                    const subjectInfo = document.createElement("div");
                    subjectInfo.className = "subject-assignment-info";

                    const subjectTitle = document.createElement("h4");
                    const subjectName =
                        subject.subject_name || "Unnamed Subject";

                    subjectTitle.textContent =
                        subject.component && subject.component !== "General"
                            ? `${subjectName} - ${subject.component}`
                            : subjectName;

                    const teacherName = document.createElement("p");
                    teacherName.textContent =
                        subject.teacher || "A subject teacher has not yet been assigned to this class.";

                    const viewLink = document.createElement("a");
                    viewLink.className = "subject-gradebook-link";
                    viewLink.href = subject.gradebook_url || "#";
                    viewLink.textContent = "View Gradebook";

                    if (!subject.gradebook_url) {
                        viewLink.setAttribute("aria-disabled", "true");
                        viewLink.addEventListener("click", (event) => {
                            event.preventDefault();
                        });
                    }

                    subjectInfo.append(subjectTitle, teacherName);
                    subjectCard.append(subjectInfo, viewLink);

                    subjectAssignmentsList.appendChild(subjectCard);
                });
            }
        }

        /* ==========================================
           SWITCH TO GRADE DETAILS
        ========================================== */

        gradeDirectory.hidden = true;
        gradeDetailsView.hidden = false;

        animateGradeDetails();

        /* ==========================================
           BROWSER HISTORY
        ========================================== */

        if (updateHistory) {
            const currentState = window.history.state;

            const nextState = {
                gradeDetailsOpen: true,
                selectedGrade: selectedGrade,
            };

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
