const approvalTable = document.getElementById("approvalGrid");

const reviewModal = document.getElementById("reviewModal");
const returnModal = document.getElementById("returnModal");
const approveModal = document.getElementById("approveModal");

const searchApproval = document.getElementById("searchApproval");
const statusFilter = document.getElementById("statusFilter");

const closeReviewModalBtn = document.getElementById("closeReviewModal");
const closeReviewButton = document.getElementById("closeReviewButton");

const closeReturnModalBtn = document.getElementById("closeReturnModal");
const cancelReturnBtn = document.getElementById("cancelReturn");
const submitReturnBtn = document.getElementById("submitReturn");

const closeApproveModalBtn = document.getElementById("closeApproveModal");
const cancelApproveBtn = document.getElementById("cancelApprove");
const confirmApproveBtn = document.getElementById("confirmApprove");

const reviewApproveBtn = document.getElementById("reviewApproveBtn");
const reviewReturnBtn = document.getElementById("reviewReturnBtn");

const returnReason = document.getElementById("returnReason");

const approveForm = document.getElementById("approveForm");
const returnForm = document.getElementById("returnForm");
const returnReasonInput = document.getElementById("returnReasonInput");

let selectedRow = null;

/* =========================================
   MODAL SCROLL LOCK
========================================= */

function setModalScrollLock(isLocked) {
    document.body.classList.toggle("modal-open", isLocked);
}

/* =========================================
   HELPER FUNCTIONS
========================================= */

function getApprovalRow(element) {
    return element instanceof Element ? element.closest(".approval-row") : null;
}

function getApprovalUrl(row, action) {
    if (!row) return null;

    const assignmentId = row.dataset.assignment;
    const schoolYear = row.dataset.schoolYear;
    const term = row.dataset.term;

    if (!assignmentId || !schoolYear || !term) {
        return null;
    }

    const baseUrl =
        approveForm?.dataset.baseUrl ||
        returnForm?.dataset.baseUrl ||
        "/admin/grade-approval";

    return `${baseUrl}/${encodeURIComponent(assignmentId)}/${encodeURIComponent(schoolYear)}/${encodeURIComponent(term)}/${action}`;
}

function setTextContent(elementId, value) {
    const element = document.getElementById(elementId);

    if (element) {
        element.textContent = value;
    }
}

/* =========================================
   REVIEW GRADE MODAL
========================================= */

function openReview(element) {
    const row = getApprovalRow(element);

    if (!row || !reviewModal) return;

    selectedRow = row;

    const grade = row.dataset.grade || "N/A";
    const subject = row.dataset.subject || "N/A";
    const teacher = row.dataset.teacher || "N/A";
    const students = row.dataset.students || "0";
    const schoolYear = row.dataset.schoolYear || "N/A";
    const term = row.dataset.term || "N/A";
    const submitted = row.dataset.submitted || "N/A";
    const status = row.dataset.status || "N/A";
    const reason = row.dataset.reason || "";

    const publicationStatus = row.dataset.publicationStatus || "Not Applicable";

    setTextContent("reviewGrade", `Grade ${grade}`);
    setTextContent("reviewSubject", subject);
    setTextContent("reviewTeacher", teacher);
    setTextContent("reviewStudents", students);
    setTextContent("reviewSchoolYear", schoolYear);
    setTextContent("reviewTerm", `Term ${term}`);
    setTextContent("reviewDate", submitted);

    /* Approval Status */

    const reviewStatus = document.getElementById("reviewStatus");

    if (reviewStatus) {
        reviewStatus.textContent = status;

        if (status === "Approved") {
            reviewStatus.style.color = "#087a3e";
        } else if (status === "Returned" || status === "Needs Review") {
            reviewStatus.style.color = "#c0392b";
        } else {
            reviewStatus.style.color = "#c88800";
        }
    }

    /* Publication Status */

    const reviewPublicationStatus = document.getElementById(
        "reviewPublicationStatus",
    );

    if (reviewPublicationStatus) {
        reviewPublicationStatus.textContent = publicationStatus;

        if (publicationStatus === "Published") {
            reviewPublicationStatus.style.color = "#087a3e";
        } else if (publicationStatus === "Not Published") {
            reviewPublicationStatus.style.color = "#c88800";
        } else {
            reviewPublicationStatus.style.color = "#7b8580";
        }
    }

    /* Return Reason */

    const reasonBox = document.getElementById("reviewReturnReasonBox");

    const reasonText = document.getElementById("reviewReturnReason");

    if (reasonBox && reasonText) {
        reasonText.textContent = reason;
        reasonBox.style.display = reason.trim() ? "block" : "none";
    }

    /* Only Pending submissions can be approved or returned */

    const isPending = status === "Pending";

    if (reviewApproveBtn) {
        reviewApproveBtn.style.display = isPending ? "" : "none";
    }

    if (reviewReturnBtn) {
        reviewReturnBtn.style.display = isPending ? "" : "none";
    }

    reviewModal.classList.add("show");
    setModalScrollLock(true);
}

function closeReview() {
    reviewModal?.classList.remove("show");
    setModalScrollLock(false);
}

/* =========================================
   APPROVE CONFIRMATION MODAL
========================================= */

function openApproveModal(element) {
    const row = getApprovalRow(element);

    if (!row || !approveModal) return;

    if (row.dataset.status !== "Pending") return;

    selectedRow = row;

    setTextContent("approveGradeName", `Grade ${row.dataset.grade || "N/A"}`);

    setTextContent("approveSubject", row.dataset.subject || "N/A");

    setTextContent("approveTeacher", row.dataset.teacher || "N/A");

    setTextContent("approveStudents", row.dataset.students || "0");

    setTextContent("approveTerm", `Term ${row.dataset.term || "N/A"}`);

    approveModal.classList.add("show");
    setModalScrollLock(true);
}

function closeApproveModal() {
    approveModal?.classList.remove("show");
    setModalScrollLock(false);
}

/* =========================================
   RETURN GRADE MODAL
========================================= */

function openReturn(element) {
    const row = getApprovalRow(element);

    if (!row || !returnModal) return;

    if (row.dataset.status !== "Pending") return;

    selectedRow = row;

    if (returnReason) {
        returnReason.value = "";
        returnReason.setCustomValidity("");
    }

    returnModal.classList.add("show");
    setModalScrollLock(true);
}

function closeReturn() {
    returnModal?.classList.remove("show");
    setModalScrollLock(false);

    if (returnReason) {
        returnReason.value = "";
        returnReason.setCustomValidity("");
    }
}

/* =========================================
   APPROVE GRADE SUBMISSION
========================================= */

function approveGrade() {
    if (!selectedRow || !approveForm || !confirmApproveBtn) {
        return;
    }

    if (selectedRow.dataset.status !== "Pending") {
        return;
    }

    const actionUrl = getApprovalUrl(selectedRow, "approve");

    if (!actionUrl) {
        return;
    }

    approveForm.action = actionUrl;

    confirmApproveBtn.disabled = true;

    confirmApproveBtn.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    approveForm.submit();
}

/* =========================================
   RETURN GRADE SUBMISSION
========================================= */

function returnGrade() {
    if (
        !selectedRow ||
        !returnForm ||
        !returnReason ||
        !returnReasonInput ||
        !submitReturnBtn
    ) {
        return;
    }

    if (selectedRow.dataset.status !== "Pending") {
        return;
    }

    const reason = returnReason.value.trim();

    if (!reason) {
        returnReason.setCustomValidity(
            "Please provide a reason for returning the grades.",
        );

        returnReason.reportValidity();
        returnReason.focus();

        return;
    }

    returnReason.setCustomValidity("");

    const actionUrl = getApprovalUrl(selectedRow, "return");

    if (!actionUrl) {
        return;
    }

    returnReasonInput.value = reason;
    returnForm.action = actionUrl;

    submitReturnBtn.disabled = true;

    submitReturnBtn.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    returnForm.submit();
}

/* =========================================
   RETURN REASON VALIDATION
========================================= */

returnReason?.addEventListener("input", () => {
    returnReason.setCustomValidity("");
});

/* =========================================
   SEARCH AND STATUS FILTER
========================================= */

function filterRows() {
    if (!searchApproval || !statusFilter) return;

    const search = searchApproval.value.toLowerCase().trim();
    const selectedStatus = statusFilter.value;

    document.querySelectorAll(".approval-row").forEach((row) => {
        const grade = (row.dataset.grade || "").toLowerCase();
        const subject = (row.dataset.subject || "").toLowerCase();
        const teacher = (row.dataset.teacher || "").toLowerCase();
        const schoolYear = (row.dataset.schoolYear || "").toLowerCase();
        const term = (row.dataset.term || "").toLowerCase();

        const status = row.dataset.status || "";

        const matchesSearch =
            grade.includes(search) ||
            subject.includes(search) ||
            teacher.includes(search) ||
            schoolYear.includes(search) ||
            term.includes(search);

        const matchesStatus =
            selectedStatus === "all" || status === selectedStatus;

        row.style.display = matchesSearch && matchesStatus ? "" : "none";
    });
}

/* =========================================
   EVENT LISTENERS
========================================= */

/* Search */

searchApproval?.addEventListener("input", filterRows);

/* Status Filter */

statusFilter?.addEventListener("change", filterRows);

/* Approve from Review Modal */

reviewApproveBtn?.addEventListener("click", () => {
    if (!selectedRow) return;

    const row = selectedRow;

    closeReview();
    openApproveModal(row);
});

/* Return from Review Modal */

reviewReturnBtn?.addEventListener("click", () => {
    if (!selectedRow) return;

    const row = selectedRow;

    closeReview();
    openReturn(row);
});

/* Confirm Approval */

confirmApproveBtn?.addEventListener("click", approveGrade);

/* Cancel Approval */

cancelApproveBtn?.addEventListener("click", closeApproveModal);

closeApproveModalBtn?.addEventListener("click", closeApproveModal);

/* Submit Return */

submitReturnBtn?.addEventListener("click", returnGrade);

/* Close Review */

closeReviewModalBtn?.addEventListener("click", closeReview);

closeReviewButton?.addEventListener("click", closeReview);

/* Close Return */

closeReturnModalBtn?.addEventListener("click", closeReturn);

cancelReturnBtn?.addEventListener("click", closeReturn);

/* =========================================
   CLICK OUTSIDE MODALS
========================================= */

reviewModal?.addEventListener("click", (event) => {
    if (event.target === reviewModal) {
        closeReview();
    }
});

returnModal?.addEventListener("click", (event) => {
    if (event.target === returnModal) {
        closeReturn();
    }
});

approveModal?.addEventListener("click", (event) => {
    if (event.target === approveModal) {
        closeApproveModal();
    }
});

/* =========================================
   ESCAPE KEY
========================================= */

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeReview();
        closeReturn();
        closeApproveModal();
    }
});

/* =========================================
   INITIALIZE PAGE
========================================= */

filterRows();
