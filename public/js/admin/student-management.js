// STUDENT MODAL

const studentModal = document.getElementById("studentModal");
const addStudentBtn = document.getElementById("addStudentBtn");
const closeStudentModal = document.getElementById("closeStudentModal");
const cancelStudentModal = document.getElementById("cancelStudentModal");

// OPEN MODAL

function openStudentModal() {
  studentModal.classList.add("show");
}

// CLOSE MODAL

function closeModal() {
  studentModal.classList.remove("show");
}

// ADD STUDENT BUTTON

addStudentBtn.addEventListener("click", () => {
  openStudentModal();
});

// CLOSE BUTTON

closeStudentModal.addEventListener("click", () => {
  closeModal();
});

// CANCEL BUTTON

cancelStudentModal.addEventListener("click", () => {
  closeModal();
});

// CLICK OUTSIDE MODAL

studentModal.addEventListener("click", (event) => {
  if (event.target === studentModal) {
    closeModal();
  }
});

// ESCAPE KEY

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeModal();
  }
});

// STUDENT SEARCH AND FILTER

const studentSearch = document.getElementById("studentSearch");
const gradeFilter = document.getElementById("gradeFilter");
const sectionFilter = document.getElementById("genderFilter");
const studentTableBody = document.getElementById("studentTableBody");

// FILTER STUDENTS

function filterStudents() {
  const searchValue = studentSearch.value.toLowerCase().trim();

  const selectedGrade = gradeFilter.value;

  const selectedSection = sectionFilter.value;

  const rows = studentTableBody.querySelectorAll("tr");

  rows.forEach((row) => {
    const studentId = row.cells[0]
      ? row.cells[0].textContent.toLowerCase().trim()
      : "";

    const studentName = row.cells[1]
      ? row.cells[1].textContent.toLowerCase().trim()
      : "";

    const rowGrade = row.cells[2]
      ? row.cells[2].textContent.trim()
      : "";

    const rowSection = row.cells[3]
      ? row.cells[3].textContent.trim()
      : "";

    const matchesSearch =
      studentId.includes(searchValue) ||
      studentName.includes(searchValue);

    const matchesGrade =
      selectedGrade === "all" ||
      rowGrade === selectedGrade;

    const matchesSection =
      selectedSection === "all" ||
      rowSection === selectedSection;

    if (matchesSearch && matchesGrade && matchesSection) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

// SEARCH WHILE TYPING

studentSearch.addEventListener("input", filterStudents);

// GRADE FILTER

gradeFilter.addEventListener("change", filterStudents);

// SECTION FILTER

sectionFilter.addEventListener("change", filterStudents);