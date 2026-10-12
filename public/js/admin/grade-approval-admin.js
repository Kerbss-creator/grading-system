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

let selectedRow = null;

/* Update summary counts */

function updateSummary() {
  const rows = document.querySelectorAll(".approval-row");

  let pending = 0;
  let approved = 0;
  let returned = 0;

  rows.forEach((row) => {
    const status = row.dataset.status;

    if (status === "Pending") {
      pending++;
    }

    if (status === "Approved") {
      approved++;
    }

    if (status === "Returned") {
      returned++;
    }
  });

  const pendingCount = document.getElementById("pendingCount");
  const approvedCount = document.getElementById("approvedCount");
  const returnedCount = document.getElementById("returnedCount");

  if (pendingCount) {
    pendingCount.textContent = pending;
  }

  if (approvedCount) {
    approvedCount.textContent = approved;
  }

  if (returnedCount) {
    returnedCount.textContent = returned;
  }
}

/* Open review modal */

function openReview(row) {
  if (!row) {
    return;
  }

  selectedRow = row;

  const grade = row.dataset.grade || "";
  const subject = row.dataset.subject || "";
  const teacher = row.dataset.teacher || "";
  const students = row.dataset.students || "";
  const schoolYear = row.dataset.schoolYear || "";
  const status = row.dataset.status || "";

  document.getElementById("reviewGrade").textContent = grade;
  document.getElementById("reviewSubject").textContent = subject;
  document.getElementById("reviewTeacher").textContent = teacher;
  document.getElementById("reviewStudents").textContent = students;
  document.getElementById("reviewStatus").textContent = status;

  const schoolYearElement = document.querySelector(
    ".review-details div:nth-child(3) strong"
  );

  if (schoolYearElement) {
    schoolYearElement.textContent = schoolYear;
  }

  if (status === "Pending") {
    reviewApproveBtn.style.display = "";
    reviewReturnBtn.style.display = "";
  } else {
    reviewApproveBtn.style.display = "none";
    reviewReturnBtn.style.display = "none";
  }

  if (status === "Approved") {
    document.getElementById("reviewStatus").style.color = "#087a3e";
  } else if (status === "Returned") {
    document.getElementById("reviewStatus").style.color = "#c0392b";
  } else {
    document.getElementById("reviewStatus").style.color = "#c88800";
  }

  reviewModal.classList.add("show");
}

/* Close review modal */

function closeReview() {
  if (!reviewModal) {
    return;
  }

  reviewModal.classList.remove("show");
}

/* Open approve confirmation modal */

function openApproveModal(row) {
  if (!row || !approveModal) {
    return;
  }

  selectedRow = row;

  const grade = row.dataset.grade || "";

  const approveGradeName = document.getElementById("approveGradeName");

  if (approveGradeName) {
    approveGradeName.textContent = grade;
  }

  approveModal.classList.add("show");
}

/* Close approve confirmation modal */

function closeApproveModal() {
  if (!approveModal) {
    return;
  }

  approveModal.classList.remove("show");
}

/* Open return modal */

function openReturn(row) {
  if (!row) {
    return;
  }

  selectedRow = row;

  returnReason.value = "";

  returnModal.classList.add("show");
}

/* Close return modal */

function closeReturn() {
  if (!returnModal) {
    return;
  }

  returnModal.classList.remove("show");

  returnReason.value = "";
}

/* Approve grade */

function approveGrade(row) {
  if (!row) {
    return;
  }

  const grade = row.dataset.grade || "";

  row.dataset.status = "Approved";

  const status = row.querySelector(".status");

  if (status) {
    status.textContent = "Approved";
    status.className = "status approved";
  }

  const icon = row.querySelector(".table-icon");

  if (icon) {
    icon.innerHTML = '<i class="fa-solid fa-file-circle-check"></i>';
    icon.classList.remove("returned-table-icon");
    icon.classList.add("approved-table-icon");
  }

  const actions = row.querySelector(".table-actions");

  if (actions) {
    actions.innerHTML = `
      <button class="review-btn" title="View">
        <i class="fa-solid fa-eye"></i>
      </button>
    `;
  }

  addRowEvents(row);

  updateSummary();
  filterRows();

  selectedRow = null;

  alert(`${grade} grades have been approved successfully.`);
}

/* Return grade */

function returnGrade(row) {
  if (!row) {
    return;
  }

  const reason = returnReason.value.trim();

  if (reason === "") {
    alert("Please provide a reason for returning the grades.");
    return;
  }

  const grade = row.dataset.grade || "";

  row.dataset.status = "Returned";
  row.dataset.returnReason = reason;

  const status = row.querySelector(".status");

  if (status) {
    status.textContent = "Returned";
    status.className = "status returned";
  }

  const icon = row.querySelector(".table-icon");

  if (icon) {
    icon.innerHTML = '<i class="fa-solid fa-file-circle-xmark"></i>';
    icon.classList.remove("approved-table-icon");
    icon.classList.add("returned-table-icon");
  }

  const actions = row.querySelector(".table-actions");

  if (actions) {
    actions.innerHTML = `
      <button class="review-btn" title="View">
        <i class="fa-solid fa-eye"></i>
      </button>
    `;
  }

  addRowEvents(row);

  closeReturn();

  updateSummary();
  filterRows();

  selectedRow = null;

  alert(`${grade} has been returned to the teacher.`);
}

/* Add row button events */

function addRowEvents(row) {
  if (!row) {
    return;
  }

  const reviewBtn = row.querySelector(".review-btn");

  if (reviewBtn) {
    reviewBtn.onclick = () => {
      openReview(row);
    };
  }

  const approveBtn = row.querySelector(".approve-btn");

  if (approveBtn) {
    approveBtn.onclick = () => {
      openApproveModal(row);
    };
  }

  const returnBtn = row.querySelector(".return-btn");

  if (returnBtn) {
    returnBtn.onclick = () => {
      openReturn(row);
    };
  }
}

/* Search and filter */

function filterRows() {
  const search = searchApproval.value.toLowerCase().trim();
  const selectedStatus = statusFilter.value;

  const rows = document.querySelectorAll(".approval-row");

  rows.forEach((row) => {
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

if (searchApproval) {
  searchApproval.addEventListener("input", filterRows);
}

/* Status filter */

if (statusFilter) {
  statusFilter.addEventListener("change", filterRows);
}

/* Approve from review modal */

if (reviewApproveBtn) {
  reviewApproveBtn.addEventListener("click", () => {
    if (!selectedRow) {
      return;
    }

    const row = selectedRow;

    closeReview();
    openApproveModal(row);
  });
}

/* Return from review modal */

if (reviewReturnBtn) {
  reviewReturnBtn.addEventListener("click", () => {
    if (!selectedRow) {
      return;
    }

    const row = selectedRow;

    closeReview();
    openReturn(row);
  });
}

/* Confirm approval */

if (confirmApproveBtn) {
  confirmApproveBtn.addEventListener("click", () => {
    if (!selectedRow) {
      return;
    }

    const row = selectedRow;

    closeApproveModal();
    approveGrade(row);
  });
}

/* Cancel approval */

if (cancelApproveBtn) {
  cancelApproveBtn.addEventListener("click", closeApproveModal);
}

/* Close approval modal */

if (closeApproveModalBtn) {
  closeApproveModalBtn.addEventListener("click", closeApproveModal);
}

/* Submit return */

if (submitReturnBtn) {
  submitReturnBtn.addEventListener("click", () => {
    if (selectedRow) {
      returnGrade(selectedRow);
    }
  });
}

/* Close review modal */

if (closeReviewModalBtn) {
  closeReviewModalBtn.addEventListener("click", closeReview);
}

if (closeReviewButton) {
  closeReviewButton.addEventListener("click", closeReview);
}

/* Close return modal */

if (closeReturnModalBtn) {
  closeReturnModalBtn.addEventListener("click", closeReturn);
}

if (cancelReturnBtn) {
  cancelReturnBtn.addEventListener("click", closeReturn);
}

/* Click outside modals */

if (reviewModal) {
  reviewModal.addEventListener("click", (event) => {
    if (event.target === reviewModal) {
      closeReview();
    }
  });
}

if (returnModal) {
  returnModal.addEventListener("click", (event) => {
    if (event.target === returnModal) {
      closeReturn();
    }
  });
}

if (approveModal) {
  approveModal.addEventListener("click", (event) => {
    if (event.target === approveModal) {
      closeApproveModal();
    }
  });
}

/* Escape key */

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") {
    return;
  }

  closeReview();
  closeReturn();
  closeApproveModal();
});

/* Initialize table rows */

document.querySelectorAll(".approval-row").forEach((row) => {
  addRowEvents(row);
});

/* Initialize summary */

updateSummary();

/* Initialize filters */

filterRows();