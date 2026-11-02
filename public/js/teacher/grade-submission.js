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

    // View Submission Modal

    const viewSubmissionModal = document.getElementById("viewSubmissionModal");

    const closeViewSubmissionModal = document.getElementById(
        "closeViewSubmissionModal",
    );

    const viewCloseButton = document.getElementById("viewCloseButton");

    const viewModalOverlay = document.querySelector(
        "#viewSubmissionModal .view-modal-overlay",
    );

    const viewClassName = document.getElementById("viewClassName");
    const viewSubjectName = document.getElementById("viewSubjectName");

    // Submission Buttons

    const submitButtons = document.querySelectorAll(".submit-btn");
    const reviewButtons = document.querySelectorAll(".review-btn");

    // Status Filter

    const statusFilter = document.getElementById("statusFilter");
    const submissionCards = document.querySelectorAll(".submission-card");

    // Open Submission Modal

    submitButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const className = button.dataset.class;
            const subjectName = button.dataset.subject;

            if (modalClassName) {
                modalClassName.textContent = className;
            }

            if (modalSubjectName) {
                modalSubjectName.textContent = subjectName;
            }

            if (modal) {
                modal.classList.add("active");
                document.body.style.overflow = "hidden";
            }
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

    // Confirm Grade Submission

    if (confirmSubmission) {
        confirmSubmission.addEventListener("click", function () {
            const className = modalClassName ? modalClassName.textContent : "";

            const subjectName = modalSubjectName
                ? modalSubjectName.textContent
                : "";

            // UI demonstration only
            // This will later send the submission request to Laravel

            alert(
                "Grades for " +
                    className +
                    " - " +
                    subjectName +
                    " have been submitted successfully.",
            );

            closeSubmissionModal();
        });
    }

    // Open View Submission Modal

    reviewButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const className = button.dataset.class;
            const subjectName = button.dataset.subject;

            if (viewClassName) {
                viewClassName.textContent = className;
            }

            if (viewSubjectName) {
                viewSubjectName.textContent = subjectName;
            }

            if (viewSubmissionModal) {
                viewSubmissionModal.classList.add("active");
                document.body.style.overflow = "hidden";
            }
        });
    });

    // Close View Submission Modal

    function closeViewSubmission() {
        if (viewSubmissionModal) {
            viewSubmissionModal.classList.remove("active");
        }

        document.body.style.overflow = "";
    }

    if (closeViewSubmissionModal) {
        closeViewSubmissionModal.addEventListener("click", closeViewSubmission);
    }

    if (viewCloseButton) {
        viewCloseButton.addEventListener("click", closeViewSubmission);
    }

    if (viewModalOverlay) {
        viewModalOverlay.addEventListener("click", closeViewSubmission);
    }

    // Status Filter

    if (statusFilter) {
        statusFilter.addEventListener("change", function () {
            const selectedStatus = statusFilter.value;

            submissionCards.forEach(function (card) {
                const cardStatus = card.dataset.status;

                if (selectedStatus === "all" || selectedStatus === cardStatus) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    }

    // Escape Key

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") {
            return;
        }

        // Close Submission Modal

        if (modal && modal.classList.contains("active")) {
            closeSubmissionModal();
        }

        // Close View Submission Modal

        if (
            viewSubmissionModal &&
            viewSubmissionModal.classList.contains("active")
        ) {
            closeViewSubmission();
        }
    });
});
