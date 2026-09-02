// TEACHER MODAL

const teacherModal = document.getElementById("teacherModal");
const addTeacherBtn = document.querySelector(".add-teacher-btn");
const closeTeacherModal = document.getElementById("closeTeacherModal");
const cancelTeacherModal = document.getElementById("cancelTeacherModal");

// Open Modal
addTeacherBtn.addEventListener("click", () => {
  teacherModal.classList.add("show");
});

// Close Modal
closeTeacherModal.addEventListener("click", () => {
  teacherModal.classList.remove("show");
});

cancelTeacherModal.addEventListener("click", () => {
  teacherModal.classList.remove("show");
});

// Click Outside Modal
teacherModal.addEventListener("click", (event) => {
  if (event.target === teacherModal) {
    teacherModal.classList.remove("show");
  }
});

// TEACHER SEARCH + FILTER

const teacherSearch = document.getElementById("teacherSearch");
const subjectFilter = document.getElementById("subjectFilter");
const statusFilter = document.getElementById("statusFilter");
const teacherTableBody = document.getElementById("teacherTableBody");

// Filter Teachers
function filterTeachers() {
  const searchValue = teacherSearch.value.toLowerCase().trim();

  const selectedSubject = subjectFilter.value;
  const selectedStatus = statusFilter.value;

  const rows = teacherTableBody.querySelectorAll("tr");

  rows.forEach((row) => {
    // Teacher ID - Column 0
    const teacherId = row.cells[0]
      ? row.cells[0].textContent.toLowerCase().trim()
      : "";

    // Teacher Name - Column 1
    const teacherName = row.cells[1]
      ? row.cells[1].textContent.toLowerCase().trim()
      : "";

    // Subject - Column 3
    const rowSubject = row.cells[3] ? row.cells[3].textContent.trim() : "";

    // Status - Column 5
    const rowStatus = row.cells[5]
      ? row.cells[5].textContent.toLowerCase().trim()
      : "";

    // Search
    const matchesSearch =
      teacherId.includes(searchValue) || teacherName.includes(searchValue);

    // Subject Filter
    const matchesSubject =
      selectedSubject === "" || rowSubject === selectedSubject;

    // Status Filter
    const matchesStatus = selectedStatus === "" || rowStatus === selectedStatus;

    // Show / Hide Row
    if (matchesSearch && matchesSubject && matchesStatus) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

// Search While Typing
teacherSearch.addEventListener("input", filterTeachers);

// Subject Filter
subjectFilter.addEventListener("change", filterTeachers);

// Status Filter
statusFilter.addEventListener("change", filterTeachers);
