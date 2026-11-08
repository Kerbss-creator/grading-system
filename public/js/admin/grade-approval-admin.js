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

/* Get the actual table row from a clicked button */

function getApprovalRow(element) {
    return element instanceof Element ? element.closest(".approval-row") : null;
}

/* Build the correct Laravel endpoint */

function getApprovalUrl(row, action) {
    if (!row) {
        return null;
    }

    const assignmentId = row.dataset.assignment;
    const schoolYear = row.dataset.schoolYear;
    const term = row.dataset.term;

    if (!assignmentId || !schoolYear || !term) {
        alert(
            "Unable to identify this grade submission. Please refresh the page.",
        );
        return null;
    }

    const baseUrl =
        approveForm?.dataset.baseUrl ||
        returnForm?.dataset.baseUrl ||
        "/admin/grade-approval";

    return `${baseUrl}/${encodeURIComponent(assignmentId)}/${encodeURIComponent(schoolYear)}/${encodeURIComponent(term)}/${action}`;
}

/* Update summary counts */

function updateSummary() {
    const rows = document.querySelectorAll(".approval-row");

    let pending = 0;
    let approved = 0;
    let returned = 0;

    rows.forEach((row) => {
        const status = row.dataset.status;

        if (status === "Pending") pending++;
        if (status === "Approved") approved++;
        if (status === "Returned") returned++;
    });

    const pendingCount = document.getElementById("pendingCount");
    const approvedCount = document.getElementById("approvedCount");
    const returnedCount = document.getElementById("returnedCount");

    if (pendingCount) pendingCount.textContent = pending;
    if (approvedCount) approvedCount.textContent = approved;
    if (returnedCount) returnedCount.textContent = returned;
}

/* Open review modal */

function openReview(element) {
    const row = getApprovalRow(element);

    if (!row || !reviewModal) return;

    selectedRow = row;

    const grade = row.dataset.grade || "N/A";
    const subject = row.dataset.subject || "N/A";
    const teacher = row.dataset.teacher || "N/A";
    const students = row.dataset.students || "0";
    const schoolYear = row.dataset.schoolYear || "N/A";
    const submitted = row.dataset.submitted || "N/A";
    const status = row.dataset.status || "N/A";

    document.getElementById("reviewGrade").textContent = `Grade ${grade}`;
    document.getElementById("reviewSubject").textContent = subject;
    document.getElementById("reviewTeacher").textContent = teacher;
    document.getElementById("reviewStudents").textContent = students;
    document.getElementById("reviewSchoolYear").textContent = schoolYear;
    document.getElementById("reviewDate").textContent = submitted;

    const reviewStatus = document.getElementById("reviewStatus");

    reviewStatus.textContent = status;

    if (status === "Approved") {
        reviewStatus.style.color = "#087a3e";
    } else if (status === "Returned") {
        reviewStatus.style.color = "#c0392b";
    } else {
        reviewStatus.style.color = "#c88800";
    }

    const isPending = status === "Pending";

    if (reviewApproveBtn) {
        reviewApproveBtn.style.display = isPending ? "" : "none";
    }

    if (reviewReturnBtn) {
        reviewReturnBtn.style.display = isPending ? "" : "none";
    }

    reviewModal.classList.add("show");
}

/* Close review modal */

function closeReview() {
    reviewModal?.classList.remove("show");
}

/* Open approve confirmation modal */

function openApproveModal(element) {
    const row = getApprovalRow(element);

    if (!row || !approveModal) return;

    if (row.dataset.status !== "Pending") {
        alert("Only pending submissions can be approved.");
        return;
    }

    selectedRow = row;

    document.getElementById("approveGradeName").textContent =
        `Grade ${row.dataset.grade || "N/A"}`;

    document.getElementById("approveSubject").textContent =
        row.dataset.subject || "N/A";

    document.getElementById("approveTeacher").textContent =
        row.dataset.teacher || "N/A";

    document.getElementById("approveStudents").textContent =
        row.dataset.students || "0";

    approveModal.classList.add("show");
}

/* Close approve confirmation modal */

function closeApproveModal() {
    approveModal?.classList.remove("show");
}

/* Open return modal */

function openReturn(element) {
    const row = getApprovalRow(element);

    if (!row || !returnModal) return;

    if (row.dataset.status !== "Pending") {
        alert("Only pending submissions can be returned.");
        return;
    }

    selectedRow = row;

    if (returnReason) {
        returnReason.value = "";
    }

    returnModal.classList.add("show");
}

/* Close return modal */

function closeReturn() {
    returnModal?.classList.remove("show");

    if (returnReason) {
        returnReason.value = "";
    }
}

/* Submit approval to Laravel */

function approveGrade() {
    if (!selectedRow || !approveForm) return;

    if (selectedRow.dataset.status !== "Pending") {
        alert("This submission is no longer pending.");
        return;
    }

    const actionUrl = getApprovalUrl(selectedRow, "approve");

    if (!actionUrl) return;

    approveForm.action = actionUrl;
    confirmApproveBtn.disabled = true;
    confirmApproveBtn.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    approveForm.submit();
}

/* Submit return request to Laravel */

function returnGrade() {
    if (!selectedRow || !returnForm || !returnReason || !returnReasonInput) {
        return;
    }

    if (selectedRow.dataset.status !== "Pending") {
        alert("This submission is no longer pending.");
        return;
    }

    const reason = returnReason.value.trim();

    if (!reason) {
        alert("Please provide a reason for returning the grades.");
        returnReason.focus();
        return;
    }

    const actionUrl = getApprovalUrl(selectedRow, "return");

    if (!actionUrl) return;

    returnReasonInput.value = reason;
    returnForm.action = actionUrl;

    submitReturnBtn.disabled = true;
    submitReturnBtn.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    returnForm.submit();
}

/* Search and filter */

function filterRows() {
    if (!searchApproval || !statusFilter) return;

    const search = searchApproval.value.toLowerCase().trim();
    const selectedStatus = statusFilter.value;

    document.querySelectorAll(".approval-row").forEach((row) => {
        const grade = (row.dataset.grade || "").toLowerCase();
        const subject = (row.dataset.subject || "").toLowerCase();
        const teacher = (row.dataset.teacher || "").toLowerCase();
        const status = row.dataset.status || "";

        const matchesSearch =
            grade.includes(search) ||
            subject.includes(search) ||
            teacher.includes(search);

        const matchesStatus =
            selectedStatus === "all" || status === selectedStatus;

        row.style.display = matchesSearch && matchesStatus ? "" : "none";
    });
}

/* Search */

searchApproval?.addEventListener("input", filterRows);

/* Status filter */

statusFilter?.addEventListener("change", filterRows);

/* Approve from review modal */

reviewApproveBtn?.addEventListener("click", () => {
    if (!selectedRow) return;

    const row = selectedRow;

    closeReview();
    openApproveModal(row);
});

/* Return from review modal */

reviewReturnBtn?.addEventListener("click", () => {
    if (!selectedRow) return;

    const row = selectedRow;

    closeReview();
    openReturn(row);
});

/* Confirm approval */

confirmApproveBtn?.addEventListener("click", approveGrade);

/* Cancel approval */

cancelApproveBtn?.addEventListener("click", closeApproveModal);
closeApproveModalBtn?.addEventListener("click", closeApproveModal);

/* Submit return */

submitReturnBtn?.addEventListener("click", returnGrade);

/* Close review modal */

closeReviewModalBtn?.addEventListener("click", closeReview);
closeReviewButton?.addEventListener("click", closeReview);

/* Close return modal */

closeReturnModalBtn?.addEventListener("click", closeReturn);
cancelReturnBtn?.addEventListener("click", closeReturn);

/* Click outside modals */

reviewModal?.addEventListener("click", (event) => {
    if (event.target === reviewModal) closeReview();
});

returnModal?.addEventListener("click", (event) => {
    if (event.target === returnModal) closeReturn();
});

approveModal?.addEventListener("click", (event) => {
    if (event.target === approveModal) closeApproveModal();
});

/* Escape key */

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeReview();
        closeReturn();
        closeApproveModal();
    }
});

/* Initialize page */

updateSummary();
filterRows();
