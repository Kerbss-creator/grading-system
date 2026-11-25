document.addEventListener("DOMContentLoaded", function () {
    // ==========================================
    // SUBMISSION MODAL ELEMENTS
    // ==========================================

    const modal = document.getElementById("submissionModal");
    const closeModal = document.getElementById("closeSubmissionModal");
    const cancelSubmission = document.getElementById("cancelSubmission");
    const confirmSubmission = document.getElementById("confirmSubmission");

    const modalOverlay = document.querySelector(
        "#submissionModal .modal-overlay",
    );

    const modalClassName = document.getElementById("modalClassName");
    const modalSubjectName = document.getElementById("modalSubjectName");

    const modalTitle = document.getElementById("submissionModalTitle");
    const modalMessage = document.getElementById("submissionModalMessage");
    const confirmSubmissionText = document.getElementById(
        "confirmSubmissionText",
    );

    // ==========================================
    // SUBMISSION FORM
    // ==========================================

    const submissionForm = document.getElementById("gradeSubmissionForm");

    const assignmentInput = document.getElementById("submissionAssignmentId");

    const schoolYearInput = document.getElementById("submissionSchoolYear");

    const termInput = document.getElementById("submissionTerm");

    // ==========================================
    // SUBMISSION BUTTONS
    // ==========================================

    const submitButtons = document.querySelectorAll(".submit-btn");
    const reviewButtons = document.querySelectorAll(".review-btn");

    // ==========================================
    // STATUS FILTER
    // ==========================================

    const statusFilter = document.getElementById("statusFilter");
    const submissionCards = document.querySelectorAll(".submission-card");
    const filterEmptyState = document.getElementById("filterEmptyState");

    // ==========================================
    // MODAL DEFAULT SETTINGS
    // ==========================================

    const defaultModalTitle = "Submit Final Grades?";

    const defaultModalMessage =
        "Please make sure all grades and remarks are correct before submitting.";

    const defaultConfirmText = "Confirm Submission";

    // ==========================================
    // RESET SUBMISSION MODAL
    // ==========================================

    function resetSubmissionModal() {
        if (modalTitle) {
            modalTitle.textContent = defaultModalTitle;
        }

        if (modalMessage) {
            modalMessage.textContent = defaultModalMessage;
        }

        if (confirmSubmissionText) {
            confirmSubmissionText.textContent = defaultConfirmText;
        }

        if (confirmSubmission) {
            confirmSubmission.disabled = false;

            confirmSubmission.innerHTML = `
                <i class="fa-solid fa-paper-plane"></i>
                <span id="confirmSubmissionText">${defaultConfirmText}</span>
            `;
        }
    }

    // ==========================================
    // OPEN SUBMISSION MODAL
    // ==========================================

    submitButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            if (!modal || !submissionForm) return;

            const className = button.dataset.class || "";
            const subjectName = button.dataset.subject || "";
            const assignmentId = button.dataset.assignmentId || "";
            const schoolYear = button.dataset.schoolYear || "";
            const term = button.dataset.term || "";

            const submissionStatus = button.dataset.submissionStatus || "draft";

            const isResubmission = submissionStatus === "returned";

            // Populate class information.
            if (modalClassName) {
                modalClassName.textContent = className;
            }

            if (modalSubjectName) {
                modalSubjectName.textContent = subjectName;
            }

            // Populate hidden form fields.
            if (assignmentInput) {
                assignmentInput.value = assignmentId;
            }

            if (schoolYearInput) {
                schoolYearInput.value = schoolYear;
            }

            if (termInput) {
                termInput.value = term;
            }

            // Reset previous modal state.
            resetSubmissionModal();

            // Change modal content for resubmission.
            if (isResubmission) {
                if (modalTitle) {
                    modalTitle.textContent = "Resubmit Returned Grades?";
                }

                if (modalMessage) {
                    modalMessage.textContent =
                        "Please review and correct the returned grades before sending them back to your Adviser.";
                }

                if (confirmSubmissionText) {
                    confirmSubmissionText.textContent = "Confirm Resubmission";
                }
            }

            // Open modal.
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        });
    });

    // ==========================================
    // CLOSE SUBMISSION MODAL
    // ==========================================

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

    // ==========================================
    // CONFIRM SUBMISSION
    // ==========================================

    if (confirmSubmission && submissionForm) {
        submissionForm.addEventListener("submit", function () {
            confirmSubmission.disabled = true;

            if (confirmSubmissionText) {
                confirmSubmissionText.textContent = "Submitting...";
            } else {
                confirmSubmission.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Submitting...
                `;
            }
        });
    }

    // ==========================================
    // REVIEW GRADES
    // Navigate to Teacher Grade Review
    // ==========================================

    reviewButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const reviewUrl = button.dataset.reviewUrl;

            if (reviewUrl) {
                window.location.href = reviewUrl;
            }
        });
    });

    // ==========================================
    // STATUS FILTER
    // ==========================================

    function applyStatusFilter() {
        if (!statusFilter) return;

        const selectedStatus = statusFilter.value;

        let visibleCards = 0;

        submissionCards.forEach(function (card) {
            const cardStatus = card.dataset.status || "";

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
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", applyStatusFilter);
    }

    // ==========================================
    // ESCAPE KEY
    // ==========================================

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        if (modal && modal.classList.contains("active")) {
            closeSubmissionModal();
        }
    });
});
