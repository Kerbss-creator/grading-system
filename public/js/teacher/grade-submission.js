document.addEventListener("DOMContentLoaded", function () {
    // Submission Modal
    const modal = document.getElementById("submissionModal");
    const closeModal = document.getElementById("closeSubmissionModal");
    const cancelSubmission = document.getElementById("cancelSubmission");
    const confirmSubmission = document.getElementById("confirmSubmission");

    const modalOverlay = document.querySelector(
        "#submissionModal .modal-overlay",
    );

    const modalClassName = document.getElementById("modalClassName");
    const modalSubjectName = document.getElementById("modalSubjectName");

    // Submission Form
    const submissionForm = document.getElementById("gradeSubmissionForm");
    const assignmentInput = document.getElementById("submissionAssignmentId");
    const schoolYearInput = document.getElementById("submissionSchoolYear");
    const termInput = document.getElementById("submissionTerm");

    // Submission Buttons
    const submitButtons = document.querySelectorAll(".submit-btn");
    const reviewButtons = document.querySelectorAll(".review-btn");

    // Status Filter
    const statusFilter = document.getElementById("statusFilter");
    const submissionCards = document.querySelectorAll(".submission-card");
    const filterEmptyState = document.getElementById("filterEmptyState");

    // Open Submission Modal
    submitButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            if (!modal || !submissionForm) return;

            const className = button.dataset.class;
            const subjectName = button.dataset.subject;

            if (modalClassName) {
                modalClassName.textContent = className;
            }

            if (modalSubjectName) {
                modalSubjectName.textContent = subjectName;
            }

            if (assignmentInput) {
                assignmentInput.value = button.dataset.assignmentId;
            }

            if (schoolYearInput) {
                schoolYearInput.value = button.dataset.schoolYear;
            }

            if (termInput) {
                termInput.value = button.dataset.term;
            }

            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        });
    });

    // Close Submission Modal
    function closeSubmissionModal() {
        if (modal) {
            modal.classList.remove("active");
        }

        document.body.style.overflow = "";
    }

    if (closeModal) {
        closeModal.addEventListener("click", closeSubmissionModal);
    }

    if (cancelSubmission) {
        cancelSubmission.addEventListener("click", closeSubmissionModal);
    }

    if (modalOverlay) {
        modalOverlay.addEventListener("click", closeSubmissionModal);
    }

    // Confirm Submission
    if (confirmSubmission && submissionForm) {
        submissionForm.addEventListener("submit", function () {
            confirmSubmission.disabled = true;

            confirmSubmission.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Submitting...
            `;
        });
    }

    // Review Grades - Navigate to separate page
    reviewButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const reviewUrl = button.dataset.reviewUrl;

            if (reviewUrl) {
                window.location.href = reviewUrl;
            }
        });
    });

    // Status Filter
    if (statusFilter) {
        statusFilter.addEventListener("change", function () {
            const selectedStatus = statusFilter.value;
            let visibleCards = 0;

            submissionCards.forEach(function (card) {
                const cardStatus = card.dataset.status;

                const shouldShow =
                    selectedStatus === "all" || selectedStatus === cardStatus;

                card.style.display = shouldShow ? "" : "none";

                if (shouldShow) {
                    visibleCards++;
                }
            });

            if (filterEmptyState) {
                filterEmptyState.style.display =
                    visibleCards === 0 ? "flex" : "none";
            }
        });
    }

    // Escape Key
    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        if (modal && modal.classList.contains("active")) {
            closeSubmissionModal();
        }
    });
});
