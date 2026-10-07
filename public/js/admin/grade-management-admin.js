// ==================================================
// GRADE MANAGEMENT - ADMIN
// ==================================================


// ==================================================
// ELEMENTS
// ==================================================

const gradeRecordGrid =
  document.getElementById("gradeRecordGrid");

const gradeModal =
  document.getElementById("gradeModal");

const viewModal =
  document.getElementById("viewModal");

const addGradeBtn =
  document.getElementById("addGradeBtn");

const closeGradeModalBtn =
  document.getElementById("closeGradeModal");

const cancelGradeBtn =
  document.getElementById("cancelGrade");

const saveGradeBtn =
  document.getElementById("saveGrade");

const closeViewModalBtn =
  document.getElementById("closeViewModal");

const closeViewButton =
  document.getElementById("closeViewButton");


// Form fields

const studentName =
  document.getElementById("studentName");

const studentId =
  document.getElementById("studentId");

const gradeLevel =
  document.getElementById("gradeLevel");

const section =
  document.getElementById("section");

const subject =
  document.getElementById("subject");

const finalGrade =
  document.getElementById("finalGrade");

const remarks =
  document.getElementById("remarks");


// Filters

const gradeFilter =
  document.getElementById("gradeFilter");

const sectionFilter =
  document.getElementById("sectionFilter");

const subjectFilter =
  document.getElementById("subjectFilter");

const searchStudent =
  document.getElementById("searchStudent");


// Modal text

const modalTitle =
  document.getElementById("modalTitle");

const modalDescription =
  document.getElementById("modalDescription");

const saveButtonText =
  document.getElementById("saveButtonText");


// ==================================================
// EDITING RECORD
// ==================================================

let editingRecordId = null;


// ==================================================
// GENERATE UNIQUE ID
// ==================================================

function generateRecordId() {

  return (
    "GR-" +
    Date.now() +
    "-" +
    Math.floor(
      Math.random() * 10000
    )
  );

}


// ==================================================
// OPEN ADD MODAL
// ==================================================

addGradeBtn.addEventListener(
  "click",
  function () {

    editingRecordId = null;

    modalTitle.textContent =
      "Add Grade Record";

    modalDescription.textContent =
      "Enter student final grade information.";

    saveButtonText.textContent =
      "Save Grade Record";


    studentName.value = "";

    studentId.value = "";

    gradeLevel.value = "";

    section.value = "";

    subject.value = "";

    finalGrade.value = "";

    remarks.value = "Passed";


    gradeModal.classList.add(
      "show"
    );

  }
);


// ==================================================
// CLOSE ADD / EDIT MODAL
// ==================================================

function closeGradeModal() {

  gradeModal.classList.remove(
    "show"
  );

  editingRecordId = null;

}


closeGradeModalBtn.addEventListener(
  "click",
  closeGradeModal
);


cancelGradeBtn.addEventListener(
  "click",
  closeGradeModal
);


// ==================================================
// EDIT RECORD
// ==================================================

function editGrade(recordId) {

  const card =
    document.querySelector(
      `.grade-record-card[data-id="${recordId}"]`
    );


  if (!card) {
    return;
  }


  editingRecordId =
    recordId;


  // Get information

  const name =
    card.querySelector(
      ".student-profile h3"
    ).textContent.trim();


  const id =
    card.querySelector(
      ".student-profile span"
    ).textContent
      .replace(
        "Student ID:",
        ""
      )
      .trim();


  const grade =
    card.dataset.grade;


  const sectionValue =
    card.dataset.section;


  const subjectValue =
    card.dataset.subject;


  const gradeValue =
    card.querySelector(
      ".final-grade h2"
    ).textContent.trim();


  const remark =
    card.querySelector(
      ".remark-box strong"
    ).textContent.trim();


  // Put information into form

  studentName.value =
    name;

  studentId.value =
    id;

  gradeLevel.value =
    grade;

  section.value =
    sectionValue;

  subject.value =
    subjectValue;


  if (
    gradeValue !== "—"
  ) {

    finalGrade.value =
      gradeValue;

  }

  else {

    finalGrade.value =
      "";

  }


  remarks.value =
    remark;


  // Change modal

  modalTitle.textContent =
    "Edit Grade Record";

  modalDescription.textContent =
    "Update student grade information.";

  saveButtonText.textContent =
    "Update Grade Record";


  gradeModal.classList.add(
    "show"
  );

}


// ==================================================
// SAVE / UPDATE
// ==================================================

saveGradeBtn.addEventListener(
  "click",
  function () {

    const name =
      studentName.value.trim();

    const id =
      studentId.value.trim();

    const grade =
      gradeLevel.value;

    const sectionValue =
      section.value;

    const subjectValue =
      subject.value;

    const gradeValue =
      finalGrade.value;

    const remark =
      remarks.value;


    // ==================================================
    // VALIDATION
    // ==================================================

    if (
      name === "" ||
      id === "" ||
      grade === "" ||
      sectionValue === "" ||
      subjectValue === ""
    ) {

      alert(
        "Please complete all required fields."
      );

      return;

    }


    // ==================================================
    // VALIDATE FINAL GRADE
    // ==================================================

    if (
      gradeValue !== "" &&
      (
        gradeValue < 0 ||
        gradeValue > 100
      )
    ) {

      alert(
        "Final grade must be between 0 and 100."
      );

      return;

    }


    // ==================================================
    // UPDATE EXISTING RECORD
    // ==================================================

    if (
      editingRecordId !== null
    ) {

      updateGradeRecord(
        editingRecordId,
        name,
        id,
        grade,
        sectionValue,
        subjectValue,
        gradeValue,
        remark
      );


      alert(
        "Grade record updated successfully."
      );

    }


    // ==================================================
    // CREATE NEW RECORD
    // ==================================================

    else {

      createGradeRecord(
        name,
        id,
        grade,
        sectionValue,
        subjectValue,
        gradeValue,
        remark
      );


      alert(
        "Grade record added successfully."
      );

    }


    closeGradeModal();

    updateSummary();

    applyFilters();

  }
);


// ==================================================
// CREATE NEW GRADE RECORD
// ==================================================

function createGradeRecord(
  name,
  id,
  grade,
  sectionValue,
  subjectValue,
  gradeValue,
  remark
) {

  const recordId =
    generateRecordId();


  const card =
    document.createElement(
      "div"
    );


  card.className =
    "grade-record-card";


  card.dataset.id =
    recordId;


  card.dataset.grade =
    grade;


  card.dataset.section =
    sectionValue;


  card.dataset.subject =
    subjectValue;


  // Get initials

  const initials =
    getInitials(name);


  // Grade display

  const displayedGrade =
    gradeValue === ""
      ? "—"
      : gradeValue;


  const status =
    gradeValue === ""
      ? "Pending"
      : "Submitted";


  const statusClass =
    gradeValue === ""
      ? "pending"
      : "submitted";


  const remarkClass =
    remark === "Passed"
      ? "passed"
      : remark === "Failed"
        ? "failed"
        : "pending-text";


  card.innerHTML = `

    <div class="record-top">

      <div class="student-profile">

        <div class="student-avatar">

          ${initials}

        </div>

        <div>

          <h3>
            ${name}
          </h3>

          <span>
            Student ID: ${id}
          </span>

        </div>

      </div>


      <span class="grade-status ${statusClass}">

        ${status}

      </span>

    </div>


    <div class="record-info">

      <div>

        <span>
          Grade Level
        </span>

        <strong>
          ${grade}
        </strong>

      </div>


      <div>

        <span>
          Section
        </span>

        <strong>
          ${sectionValue}
        </strong>

      </div>


      <div>

        <span>
          Subject
        </span>

        <strong>
          ${subjectValue}
        </strong>

      </div>

    </div>


    <div class="final-grade">

      <div>

        <span>
          Final Grade
        </span>

        <h2 class="${
          displayedGrade === "—"
            ? "no-grade"
            : ""
        }">

          ${displayedGrade}

        </h2>

      </div>


      <div class="remark-box">

        <span>
          Remarks
        </span>

        <strong class="${remarkClass}">

          ${remark}

        </strong>

      </div>

    </div>


    <div class="record-footer">

      <button
        class="view-btn"
        type="button"
      >

        <i class="fa-solid fa-eye"></i>

        View

      </button>


      <button
        class="edit-btn"
        type="button"
      >

        <i class="fa-solid fa-pen"></i>

        Edit

      </button>


      <button
        class="delete-btn"
        type="button"
      >

        <i class="fa-solid fa-trash"></i>

        Delete

      </button>

    </div>

  `;


  attachCardEvents(
    card
  );


  gradeRecordGrid.appendChild(
    card
  );

}


// ==================================================
// UPDATE RECORD
// ==================================================

function updateGradeRecord(
  recordId,
  name,
  id,
  grade,
  sectionValue,
  subjectValue,
  gradeValue,
  remark
) {

  const card =
    document.querySelector(
      `.grade-record-card[data-id="${recordId}"]`
    );


  if (!card) {
    return;
  }


  // Update dataset

  card.dataset.grade =
    grade;

  card.dataset.section =
    sectionValue;

  card.dataset.subject =
    subjectValue;


  // Update student

  card.querySelector(
    ".student-profile h3"
  ).textContent =
    name;


  card.querySelector(
    ".student-profile span"
  ).textContent =
    "Student ID: " + id;


  card.querySelector(
    ".student-avatar"
  ).textContent =
    getInitials(name);


  // Update record information

  const info =
    card.querySelectorAll(
      ".record-info strong"
    );


  info[0].textContent =
    grade;

  info[1].textContent =
    sectionValue;

  info[2].textContent =
    subjectValue;


  // Update grade

  const gradeElement =
    card.querySelector(
      ".final-grade h2"
    );


  gradeElement.textContent =
    gradeValue === ""
      ? "—"
      : gradeValue;


  gradeElement.className =
    gradeValue === ""
      ? "no-grade"
      : "";


  // Update status

  const statusElement =
    card.querySelector(
      ".grade-status"
    );


  if (
    gradeValue === ""
  ) {

    statusElement.textContent =
      "Pending";

    statusElement.className =
      "grade-status pending";

  }

  else {

    statusElement.textContent =
      "Submitted";

    statusElement.className =
      "grade-status submitted";

  }


  // Update remark

  const remarkElement =
    card.querySelector(
      ".remark-box strong"
    );


  remarkElement.textContent =
    remark;


  remarkElement.className =
    remark === "Passed"
      ? "passed"
      : remark === "Failed"
        ? "failed"
        : "pending-text";

}


// ==================================================
// VIEW RECORD
// ==================================================

function viewGrade(recordId) {

  const card =
    document.querySelector(
      `.grade-record-card[data-id="${recordId}"]`
    );


  if (!card) {
    return;
  }


  const name =
    card.querySelector(
      ".student-profile h3"
    ).textContent.trim();


  const id =
    card.querySelector(
      ".student-profile span"
    ).textContent.trim();


  const grade =
    card.dataset.grade;


  const sectionValue =
    card.dataset.section;


  const subjectValue =
    card.dataset.subject;


  const gradeValue =
    card.querySelector(
      ".final-grade h2"
    ).textContent.trim();


  const remark =
    card.querySelector(
      ".remark-box strong"
    ).textContent.trim();


  document.getElementById(
    "viewAvatar"
  ).textContent =
    getInitials(name);


  document.getElementById(
    "viewStudentName"
  ).textContent =
    name;


  document.getElementById(
    "viewStudentId"
  ).textContent =
    id;


  document.getElementById(
    "viewGrade"
  ).textContent =
    grade;


  document.getElementById(
    "viewSection"
  ).textContent =
    sectionValue;


  document.getElementById(
    "viewSubject"
  ).textContent =
    subjectValue;


  document.getElementById(
    "viewFinalGrade"
  ).textContent =
    gradeValue;


  document.getElementById(
    "viewRemarks"
  ).textContent =
    remark;


  viewModal.classList.add(
    "show"
  );

}


// ==================================================
// DELETE RECORD
// ==================================================

function deleteGrade(recordId) {

  const card =
    document.querySelector(
      `.grade-record-card[data-id="${recordId}"]`
    );


  if (!card) {
    return;
  }


  const name =
    card.querySelector(
      ".student-profile h3"
    ).textContent.trim();


  const confirmed =
    confirm(
      `Are you sure you want to delete the grade record of ${name}?`
    );


  if (!confirmed) {
    return;
  }


  card.remove();


  alert(
    "Grade record deleted successfully."
  );


  updateSummary();

  applyFilters();

}


// ==================================================
// GET INITIALS
// ==================================================

function getInitials(name) {

  return name
    .split(" ")
    .map(
      word =>
        word.charAt(0)
    )
    .join("")
    .substring(0, 2)
    .toUpperCase();

}


// ==================================================
// ATTACH CARD EVENTS
// ==================================================

function attachCardEvents(card) {

  const recordId =
    card.dataset.id;


  const viewButton =
    card.querySelector(
      ".view-btn"
    );


  const editButton =
    card.querySelector(
      ".edit-btn"
    );


  const deleteButton =
    card.querySelector(
      ".delete-btn"
    );


  if (viewButton) {

    viewButton.addEventListener(
      "click",
      function () {

        viewGrade(
          recordId
        );

      }
    );

  }


  if (editButton) {

    editButton.addEventListener(
      "click",
      function () {

        editGrade(
          recordId
        );

      }
    );

  }


  if (deleteButton) {

    deleteButton.addEventListener(
      "click",
      function () {

        deleteGrade(
          recordId
        );

      }
    );

  }

}


// ==================================================
// FILTER RECORDS
// ==================================================

function applyFilters() {

  const selectedGrade =
    gradeFilter.value;


  const selectedSection =
    sectionFilter.value;


  const selectedSubject =
    subjectFilter.value;


  const search =
    searchStudent.value
      .toLowerCase()
      .trim();


  const cards =
    document.querySelectorAll(
      ".grade-record-card"
    );


  let visibleCount = 0;


  cards.forEach(
    function (card) {

      const grade =
        card.dataset.grade;


      const sectionValue =
        card.dataset.section;


      const subjectValue =
        card.dataset.subject;


      const name =
        card.querySelector(
          ".student-profile h3"
        ).textContent
          .toLowerCase();


      const id =
        card.querySelector(
          ".student-profile span"
        ).textContent
          .toLowerCase();


      const gradeMatch =
        selectedGrade === "all" ||
        grade === selectedGrade;


      const sectionMatch =
        selectedSection === "all" ||
        sectionValue === selectedSection;


      const subjectMatch =
        selectedSubject === "all" ||
        subjectValue === selectedSubject;


      const searchMatch =
        search === "" ||
        name.includes(search) ||
        id.includes(search);


      if (
        gradeMatch &&
        sectionMatch &&
        subjectMatch &&
        searchMatch
      ) {

        card.style.display =
          "block";

        visibleCount++;

      }

      else {

        card.style.display =
          "none";

      }

    }
  );


  document.getElementById(
    "recordCount"
  ).textContent =
    visibleCount +
    " student record" +
    (
      visibleCount === 1
        ? ""
        : "s"
    );

}


// ==================================================
// SUMMARY
// ==================================================

function updateSummary() {

  const cards =
    document.querySelectorAll(
      ".grade-record-card"
    );


  let total =
    cards.length;


  let submitted =
    0;


  let pending =
    0;


  let passing =
    0;


  cards.forEach(
    function (card) {

      const grade =
        card.querySelector(
          ".final-grade h2"
        ).textContent.trim();


      if (
        grade === "—"
      ) {

        pending++;

      }

      else {

        submitted++;

        const numericGrade =
          parseFloat(grade);


        if (
          numericGrade >= 75
        ) {

          passing++;

        }

      }

    }
  );


  let rate =
    submitted > 0
      ? (
          passing /
          submitted *
          100
        ).toFixed(1)
      : "0";


  document.getElementById(
    "totalStudents"
  ).textContent =
    total;


  document.getElementById(
    "submittedGrades"
  ).textContent =
    submitted;


  document.getElementById(
    "pendingGrades"
  ).textContent =
    pending;


  document.getElementById(
    "passingRate"
  ).textContent =
    rate + "%";

}


// ==================================================
// FILTER EVENTS
// ==================================================

gradeFilter.addEventListener(
  "change",
  applyFilters
);


sectionFilter.addEventListener(
  "change",
  applyFilters
);


subjectFilter.addEventListener(
  "change",
  applyFilters
);


searchStudent.addEventListener(
  "input",
  applyFilters
);


// ==================================================
// VIEW MODAL CLOSE
// ==================================================

function closeViewModal() {

  viewModal.classList.remove(
    "show"
  );

}


closeViewModalBtn.addEventListener(
  "click",
  closeViewModal
);


closeViewButton.addEventListener(
  "click",
  closeViewModal
);


// ==================================================
// CLICK OUTSIDE MODAL
// ==================================================

gradeModal.addEventListener(
  "click",
  function (event) {

    if (
      event.target ===
      gradeModal
    ) {

      closeGradeModal();

    }

  }
);


viewModal.addEventListener(
  "click",
  function (event) {

    if (
      event.target ===
      viewModal
    ) {

      closeViewModal();

    }

  }
);


// ==================================================
// ESC KEY
// ==================================================

document.addEventListener(
  "keydown",
  function (event) {

    if (
      event.key === "Escape"
    ) {

      closeGradeModal();

      closeViewModal();

    }

  }
);


// ==================================================
// IMPORT / EXPORT
// ==================================================

document.getElementById(
  "importBtn"
).addEventListener(
  "click",
  function () {

    alert(
      "Excel import feature can be connected to your database/backend."
    );

  }
);


document.getElementById(
  "exportBtn"
).addEventListener(
  "click",
  function () {

    alert(
      "Excel export feature can be connected to your database/backend."
    );

  }
);


// ==================================================
// INITIALIZE EXISTING CARDS
// ==================================================

document
  .querySelectorAll(
    ".grade-record-card"
  )
  .forEach(
    function (card) {

      attachCardEvents(
        card
      );

    }
  );


updateSummary();

applyFilters();