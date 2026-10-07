// GRADE LEVEL MANAGEMENT

// Elements
const gradeGrid = document.getElementById("gradeGrid");
const gradeModal = document.getElementById("gradeModal");
const viewModal = document.getElementById("viewModal");

const addGradeBtn = document.getElementById("addGradeBtn");
const closeGradeModalBtn = document.getElementById("closeGradeModal");
const cancelGradeBtn = document.getElementById("cancelGrade");
const saveGradeBtn = document.getElementById("saveGrade");

const closeViewModalBtn = document.getElementById("closeViewModal");
const closeViewButton = document.getElementById("closeViewButton");

const gradeLevel = document.getElementById("gradeLevel");
const gradeStatus = document.getElementById("gradeStatus");

const modalTitle = document.getElementById("modalTitle");
const modalDescription = document.getElementById("modalDescription");
const saveButtonText = document.getElementById("saveButtonText");

// Current editing card
let editingGradeId = null;

// Generate unique grade ID
function generateGradeId() {
  return "grade-" + Date.now() + "-" + Math.floor(Math.random() * 10000);
}

// Open Add Grade Modal
addGradeBtn.addEventListener("click", function () {
  editingGradeId = null;

  modalTitle.textContent = "Add Grade Level";
  modalDescription.textContent = "Create a new grade level.";
  saveButtonText.textContent = "Save Grade Level";

  gradeLevel.value = "";
  gradeStatus.value = "Active";

  gradeModal.classList.add("show");
});

// Close Grade Modal
function closeGradeModal() {
  gradeModal.classList.remove("show");
  editingGradeId = null;
}

closeGradeModalBtn.addEventListener("click", closeGradeModal);
cancelGradeBtn.addEventListener("click", closeGradeModal);

// View Grade
function viewGrade(gradeId) {
  const card = document.querySelector(
    `.grade-card[data-id="${gradeId}"]`
  );

  if (!card) {
    return;
  }

  const grade = card
    .querySelector(".grade-info h2")
    .textContent
    .trim();

  const sectionCount = card
    .querySelector(".section-count")
    .textContent;

  const studentCount = card
    .querySelector(".student-count")
    .textContent;

  const status = card
    .querySelector(".grade-status")
    .textContent
    .trim();

  document.getElementById("viewGradeTitle").textContent = grade;
  document.getElementById("viewGradeName").textContent = grade;
  document.getElementById("viewSectionCount").textContent = sectionCount;
  document.getElementById("viewStudentCount").textContent = studentCount;

  const viewStatus = document.getElementById("viewStatus");

  viewStatus.textContent = status;
  viewStatus.className =
    status === "Active"
      ? "view-active"
      : "view-inactive";

  viewModal.classList.add("show");
}

// Close View Modal
function closeViewModal() {
  viewModal.classList.remove("show");
}

closeViewModalBtn.addEventListener("click", closeViewModal);
closeViewButton.addEventListener("click", closeViewModal);

// Edit Grade
function editGrade(gradeId) {
  const card = document.querySelector(
    `.grade-card[data-id="${gradeId}"]`
  );

  if (!card) {
    return;
  }

  const grade = card
    .querySelector(".grade-info h2")
    .textContent
    .trim();

  const status = card
    .querySelector(".grade-status")
    .textContent
    .trim();

  editingGradeId = gradeId;

  modalTitle.textContent = "Edit Grade Level";
  modalDescription.textContent = "Update grade level information.";
  saveButtonText.textContent = "Update Grade Level";

  gradeLevel.value = grade;
  gradeStatus.value = status;

  gradeModal.classList.add("show");
}

// Save / Update Grade
saveGradeBtn.addEventListener("click", function () {
  const selectedGrade = gradeLevel.value.trim();
  const selectedStatus = gradeStatus.value;

  // Validation
  if (selectedGrade === "") {
    alert("Please select a grade level.");
    return;
  }

  // Edit existing grade
  if (editingGradeId !== null) {
    const card = document.querySelector(
      `.grade-card[data-id="${editingGradeId}"]`
    );

    if (!card) {
      return;
    }

    // Update grade name
    card.querySelector(".grade-info h2").textContent = selectedGrade;

    // Update grade icon number
    const gradeNumber = selectedGrade.replace("Grade ", "");

    card.querySelector(".grade-icon span").textContent = gradeNumber;

    // Update status
    const statusElement = card.querySelector(".grade-status");

    statusElement.textContent = selectedStatus;
    statusElement.className =
      selectedStatus === "Active"
        ? "grade-status active"
        : "grade-status inactive";

    alert(selectedGrade + " has been updated successfully.");
  } else {
    // Add new grade
    createGradeCard(selectedGrade, selectedStatus);

    alert(selectedGrade + " has been added successfully.");
  }

  closeGradeModal();
  updateSummary();
});

// Create New Grade Card
function createGradeCard(grade, status) {
  const gradeId = generateGradeId();
  const gradeNumber = grade.replace("Grade ", "");

  const card = document.createElement("div");

  card.className = "grade-card";
  card.dataset.id = gradeId;
  card.dataset.grade = grade;

  card.innerHTML = `
    <div class="grade-card-top">
      <div class="grade-icon">
        <span>${gradeNumber}</span>
      </div>

      <span class="grade-status ${
        status === "Active" ? "active" : "inactive"
      }">
        ${status}
      </span>
    </div>

    <div class="grade-info">
      <h2>${grade}</h2>
      <p>Junior High School</p>
    </div>

    <div class="grade-details">
      <div class="detail-item">
        <i class="fa-solid fa-layer-group"></i>

        <div>
          <span>Sections</span>
          <strong class="section-count">0</strong>
        </div>
      </div>

      <div class="detail-item">
        <i class="fa-solid fa-user-graduate"></i>

        <div>
          <span>Students</span>
          <strong class="student-count">0</strong>
        </div>
      </div>
    </div>

    <div class="grade-footer">
      <button class="view-btn" type="button">
        <i class="fa-solid fa-eye"></i>
        View
      </button>

      <button class="edit-btn" type="button">
        <i class="fa-solid fa-pen"></i>
        Edit
      </button>

      <button class="delete-btn" type="button">
        <i class="fa-solid fa-trash"></i>
        Delete
      </button>
    </div>
  `;

  // View button
  card.querySelector(".view-btn").addEventListener("click", function () {
    viewGrade(gradeId);
  });

  // Edit button
  card.querySelector(".edit-btn").addEventListener("click", function () {
    editGrade(gradeId);
  });

  // Delete button
  card.querySelector(".delete-btn").addEventListener("click", function () {
    deleteGrade(gradeId);
  });

  gradeGrid.appendChild(card);
}

// Delete Grade Card
function deleteGrade(gradeId) {
  const card = document.querySelector(
    `.grade-card[data-id="${gradeId}"]`
  );

  if (!card) {
    return;
  }

  const grade = card
    .querySelector(".grade-info h2")
    .textContent
    .trim();

  const studentCount =
    parseInt(
      card.querySelector(".student-count").textContent
    ) || 0;

  const sectionCount =
    parseInt(
      card.querySelector(".section-count").textContent
    ) || 0;

  let message;

  if (studentCount > 0 || sectionCount > 0) {
    message =
      `${grade} currently has ${sectionCount} section(s) ` +
      `and ${studentCount} student(s) assigned.\n\n` +
      `Are you sure you want to delete this grade level?`;
  } else {
    message = `Are you sure you want to delete ${grade}?`;
  }

  const confirmed = confirm(message);

  if (!confirmed) {
    return;
  }

  card.remove();

  alert(grade + " has been deleted successfully.");

  updateSummary();
}

// Update Summary
function updateSummary() {
  const cards = document.querySelectorAll(".grade-card");

  let totalSections = 0;
  let totalStudents = 0;

  cards.forEach(function (card) {
    const sections =
      parseInt(
        card.querySelector(".section-count").textContent
      ) || 0;

    const students =
      parseInt(
        card.querySelector(".student-count").textContent
      ) || 0;

    totalSections += sections;
    totalStudents += students;
  });

  const totalGrades = document.getElementById("totalGrades");
  const totalSectionsElement =
    document.getElementById("totalSections");
  const totalStudentsElement =
    document.getElementById("totalStudents");

  if (totalGrades) {
    totalGrades.textContent = cards.length;
  }

  if (totalSectionsElement) {
    totalSectionsElement.textContent = totalSections;
  }

  if (totalStudentsElement) {
    totalStudentsElement.textContent = totalStudents;
  }
}

// Initialize Existing Cards
function initializeExistingCards() {
  const cards = document.querySelectorAll(".grade-card");

  cards.forEach(function (card) {
    if (!card.dataset.id) {
      card.dataset.id = generateGradeId();
    }

    const gradeId = card.dataset.id;

    // View button
    const viewButton = card.querySelector(".view-btn");

    if (viewButton) {
      viewButton.addEventListener("click", function () {
        viewGrade(gradeId);
      });
    }

    // Edit button
    const editButton = card.querySelector(".edit-btn");

    if (editButton) {
      editButton.addEventListener("click", function () {
        editGrade(gradeId);
      });
    }

    // Delete button
    const deleteButton = card.querySelector(".delete-btn");

    if (deleteButton) {
      deleteButton.addEventListener("click", function () {
        deleteGrade(gradeId);
      });
    }
  });
}

// Close Modals When Clicking Outside
gradeModal.addEventListener("click", function (event) {
  if (event.target === gradeModal) {
    closeGradeModal();
  }
});

viewModal.addEventListener("click", function (event) {
  if (event.target === viewModal) {
    closeViewModal();
  }
});

// Close Modals With Escape Key
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeGradeModal();
    closeViewModal();
  }
});

// Initialize
initializeExistingCards();
updateSummary();
