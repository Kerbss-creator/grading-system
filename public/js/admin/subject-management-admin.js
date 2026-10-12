// =========================
// SUBJECT MODAL
// =========================

const subjectModal = document.getElementById("subjectModal");
const addSubjectBtn = document.querySelector(".add-subject-btn");
const closeSubjectBtn = subjectModal.querySelector(".modal-close");
const cancelSubjectBtn = subjectModal.querySelector(".cancel-btn");
const subjectForm = document.getElementById("subjectForm");

// OPEN MODAL
addSubjectBtn.addEventListener("click", () => {
  subjectModal.classList.add("show");
});

// CLOSE MODAL
closeSubjectBtn.addEventListener("click", () => {
  subjectModal.classList.remove("show");
});

// CANCEL
cancelSubjectBtn.addEventListener("click", () => {
  subjectModal.classList.remove("show");
});

// CLOSE WHEN CLICKING OUTSIDE
subjectModal.addEventListener("click", (e) => {
  if (e.target === subjectModal) {
    subjectModal.classList.remove("show");
  }
});

// SAVE SUBJECT
subjectForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const subjectName = document.getElementById("subjectName").value;
  const gradeLevel = document.getElementById("gradeLevel").value;
  const schoolYear = document.getElementById("schoolYear").value;
  const subjectStatus = document.getElementById("subjectStatus").value;

  console.log("Subject Name:", subjectName);
  console.log("Grade Level:", gradeLevel);
  console.log("School Year:", schoolYear);
  console.log("Status:", subjectStatus);

  // CLOSE MODAL
  subjectModal.classList.remove("show");

  // CLEAR FORM
  subjectForm.reset();
});
