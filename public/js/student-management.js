const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const studentModal = document.getElementById("studentModal");
const addStudentBtn = document.getElementById("addStudentBtn");
const closeStudentModal = document.getElementById("closeStudentModal");
const cancelStudentModal = document.getElementById("cancelStudentModal");
const saveStudentBtn = document.getElementById("saveStudentBtn");

function openStudentModal() {
  if (studentModal) studentModal.classList.add("show");
}

function closeModal() {
  if (studentModal) studentModal.classList.remove("show");
}

if (addStudentBtn) addStudentBtn.addEventListener("click", openStudentModal);
if (closeStudentModal) closeStudentModal.addEventListener("click", closeModal);
if (cancelStudentModal) cancelStudentModal.addEventListener("click", closeModal);

if (studentModal) {
  studentModal.addEventListener("click", (event) => {
    if (event.target === studentModal) closeModal();
  });
}

const closeEditModal = document.getElementById("closeEditModal");
const cancelEditModal = document.getElementById("cancelEditModal");
const editStudentModal = document.getElementById("editStudentModal");

if (closeEditModal) {
  closeEditModal.addEventListener("click", () => {
    editStudentModal.classList.remove("show");
  });
}

if (cancelEditModal) {
  cancelEditModal.addEventListener("click", () => {
    editStudentModal.classList.remove("show");
  });
}

if (editStudentModal) {
  editStudentModal.addEventListener("click", (event) => {
    if (event.target === editStudentModal) {
      editStudentModal.classList.remove("show");
    }
  });
}

const closeDeleteModal = document.getElementById("closeDeleteModal");
const cancelDeleteModal = document.getElementById("cancelDeleteModal");
const deleteStudentModal = document.getElementById("deleteStudentModal");

if (closeDeleteModal) {
  closeDeleteModal.addEventListener("click", () => {
    deleteStudentModal.classList.remove("show");
  });
}

if (cancelDeleteModal) {
  cancelDeleteModal.addEventListener("click", () => {
    deleteStudentModal.classList.remove("show");
  });
}

if (deleteStudentModal) {
  deleteStudentModal.addEventListener("click", (event) => {
    if (event.target === deleteStudentModal) {
      deleteStudentModal.classList.remove("show");
    }
  });
}

const closeViewModal = document.getElementById("closeViewModal");
const closeViewModalBtn = document.getElementById("closeViewModalBtn");
const viewStudentModal = document.getElementById("viewStudentModal");

if (closeViewModal) {
  closeViewModal.addEventListener("click", () => {
    viewStudentModal.classList.remove("show");
  });
}

if (closeViewModalBtn) {
  closeViewModalBtn.addEventListener("click", () => {
    viewStudentModal.classList.remove("show");
  });
}

if (viewStudentModal) {
  viewStudentModal.addEventListener("click", (event) => {
    if (event.target === viewStudentModal) {
      viewStudentModal.classList.remove("show");
    }
  });
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeModal();
    if (editStudentModal) editStudentModal.classList.remove("show");
    if (deleteStudentModal) deleteStudentModal.classList.remove("show");
    if (viewStudentModal) viewStudentModal.classList.remove("show");
  }
});

if (saveStudentBtn) {
  saveStudentBtn.addEventListener("click", async function (e) {
    e.preventDefault();

    const formData = {
      student_id: document.getElementById("studentId").value.trim(),
      first_name: document.getElementById("firstName").value.trim(),
      middle_name: document.getElementById("middleName").value.trim(),
      last_name: document.getElementById("lastName").value.trim(),
      grade_level: document.getElementById("gradeLevel").value,
      sex: document.getElementById("sex").value,
      school_year: document.getElementById("schoolYear").value,
      email: document.getElementById("email").value.trim(),
      status: document.getElementById("studentStatus").value,
    };

    try {
      const response = await fetch("/admin/students", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (response.ok && data.success) {
        showModal(data.message || "Student added successfully!", 'success');
        closeModal();
        document.getElementById("studentId").value = "";
        document.getElementById("firstName").value = "";
        document.getElementById("middleName").value = "";
        document.getElementById("lastName").value = "";
        document.getElementById("gradeLevel").value = "";
        document.getElementById("sex").value = "";
        document.getElementById("schoolYear").value = "";
        document.getElementById("email").value = "";
        document.getElementById("studentStatus").value = "active";

        setTimeout(() => { window.location.reload(); }, 1500);
      } else {
        let errorMsg = "Error saving student:\n";
        if (data.errors) {
          for (const field in data.errors) {
            const fieldName = field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            errorMsg += `- ${fieldName}: ${data.errors[field][0]}\n`;
          }
        } else {
          errorMsg += data.message || "Check required fields.";
        }
        showModal(errorMsg, 'error');
      }
    } catch (error) {
      console.error("Fetch error:", error);
      showModal("An unexpected error occurred while saving.", 'error');
    }
  });
}

window.viewStudent = function (id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;

  const setId = document.getElementById("viewStudentId");
  const setName = document.getElementById("viewStudentName");
  const setGrade = document.getElementById("viewGradeLevel");
  const setSex = document.getElementById("viewSex");
  const setYear = document.getElementById("viewSchoolYear");
  const setGmail = document.getElementById("viewGmail");

  if (setId) setId.textContent = row.dataset.studentId || "-";
  if (setName) setName.textContent = `${row.dataset.firstName || ""} ${row.dataset.middleName || ""} ${row.dataset.lastName || ""}`.trim() || "-";
  if (setGrade) setGrade.textContent = row.dataset.gradeLevel || "-";
  if (setSex) setSex.textContent = row.dataset.sex || "-";
  if (setYear) setYear.textContent = row.dataset.schoolYear || "-";
  if (setGmail) setGmail.textContent = row.dataset.email || "-";

  if (viewStudentModal) viewStudentModal.classList.add("show");
};

window.editStudent = function (id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;

  const setId = document.getElementById("editStudentId");
  const setName = document.getElementById("editStudentName");
  const setGrade = document.getElementById("editGradeLevel");
  const setSex = document.getElementById("editSex");
  const setYear = document.getElementById("editSchoolYear");
  const setGmail = document.getElementById("editGmail");

  if (setId) setId.value = row.dataset.studentId || "";
  if (setName) setName.value = `${row.dataset.firstName || ""} ${row.dataset.middleName || ""} ${row.dataset.lastName || ""}`.trim();
  if (setGrade) setGrade.value = row.dataset.gradeLevel || "";
  if (setSex) setSex.value = row.dataset.sex || "";
  if (setYear) setYear.value = row.dataset.schoolYear || "";
  if (setGmail) setGmail.value = row.dataset.email || "";

  if (editStudentModal) editStudentModal.classList.add("show");

  const saveEditBtn = document.getElementById("saveEditStudent");
  if (saveEditBtn) {
    const newBtn = saveEditBtn.cloneNode(true);
    saveEditBtn.parentNode.replaceChild(newBtn, saveEditBtn);

    newBtn.addEventListener("click", async function () {
      const fullName = document.getElementById("editStudentName").value.split(" ");
      const updateData = {
        student_id: document.getElementById("editStudentId").value.trim(),
        first_name: fullName[0] || "",
        middle_name: fullName.length > 2 ? fullName.slice(1, -1).join(" ") : "",
        last_name: fullName[fullName.length - 1] || "",
        grade_level: document.getElementById("editGradeLevel").value,
        sex: document.getElementById("editSex").value,
        school_year: document.getElementById("editSchoolYear").value,
        email: document.getElementById("editGmail").value.trim(),
        status: row.dataset.status || "active",
        _method: "PUT",
      };

      try {
        const response = await fetch(`/admin/students/${id}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
          },
          body: JSON.stringify(updateData),
        });

        const data = await response.json();
        if (response.ok && data.success) {
          showModal(data.message || "Student updated successfully!", 'success');
          if (editStudentModal) editStudentModal.classList.remove("show");

          setTimeout(() => { window.location.reload(); }, 1500);
        } else {
          let errorMsg = "Error updating student:\n";
          if (data.errors) {
            for (const field in data.errors) {
              errorMsg += `- ${field}: ${data.errors[field][0]}\n`;
            }
          }
          showModal(errorMsg, 'error');
        }
      } catch (error) {
        console.error("Fetch error:", error);
        showModal("An unexpected error occurred while updating.", 'error');
      }
    });
  }
};

window.deleteStudent = function (id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;

  const studentName = `${row.dataset.firstName || ""} ${row.dataset.lastName || ""}`.trim();
  document.getElementById("deleteStudentName").textContent = studentName || "this student";
  if (deleteStudentModal) deleteStudentModal.classList.add("show");

  const confirmDeleteBtn = document.getElementById("confirmDeleteStudent");
  if (confirmDeleteBtn) {
    const newConfirmBtn = confirmDeleteBtn.cloneNode(true);
    confirmDeleteBtn.parentNode.replaceChild(newConfirmBtn, confirmDeleteBtn);

    newConfirmBtn.addEventListener("click", async function () {
      try {
        const response = await fetch(`/admin/students/${id}`, {
          method: "DELETE",
          headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
          },
        });

        const data = await response.json();
        if (response.ok && data.success) {
          showModal(data.message || "Student deleted successfully!", 'success');
          if (deleteStudentModal) deleteStudentModal.classList.remove("show");

          setTimeout(() => { window.location.reload(); }, 1500);
        } else {
          showModal("Error deleting student.", 'error');
        }
      } catch (error) {
        console.error("Fetch error:", error);
        showModal("An unexpected error occurred while deleting.", 'error');
      }
    });
  }
};

window.toggleStatus = async function (id) {
  try {
    const response = await fetch(`/admin/students/${id}/toggle-status`, {
      method: "PATCH",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
    });

    const data = await response.json();
    if (response.ok && data.success) {
      showModal(`Student is now ${data.status}.`, 'success');

      setTimeout(() => { window.location.reload(); }, 1500);
    } else {
      showModal("Error updating status.", 'error');
    }
  } catch (error) {
    console.error("Fetch error:", error);
    showModal("An unexpected error occurred while updating status.", 'error');
  }
};

const studentSearch = document.getElementById("studentSearch");
const gradeFilter = document.getElementById("gradeFilter");
const sectionFilter = document.getElementById("sectionFilter");
const studentStatusFilter = document.getElementById("studentStatusFilter");
const studentTableBody = document.getElementById("studentTableBody");

function filterStudents() {
  const searchValue = studentSearch ? studentSearch.value.toLowerCase().trim() : "";
  const selectedGrade = gradeFilter ? gradeFilter.value : "all";
  const selectedSex = sectionFilter ? sectionFilter.value : "all";
  const selectedStatus = studentStatusFilter ? studentStatusFilter.value : "all";

  const rows = studentTableBody ? studentTableBody.querySelectorAll("tr") : [];

  rows.forEach((row) => {
    const studentId = (row.dataset.studentId || "").toLowerCase();
    const studentName = `${row.dataset.firstName || ""} ${row.dataset.lastName || ""}`.toLowerCase();
    const rowGrade = row.dataset.gradeLevel || "";
    const rowSex = row.dataset.sex || "";
    const rowStatus = row.dataset.status || "";

    const matchesSearch = searchValue === "" || studentId.includes(searchValue) || studentName.includes(searchValue);
    const matchesGrade = selectedGrade === "all" || rowGrade === selectedGrade;
    const matchesSex = selectedSex === "all" || rowSex === selectedSex;

    row.style.display = (matchesSearch && matchesGrade && matchesSex && (selectedStatus === "all" || rowStatus === selectedStatus)) ? "" : "none";
  });
}

if (studentSearch) studentSearch.addEventListener("input", filterStudents);
if (gradeFilter) gradeFilter.addEventListener("change", filterStudents);
if (sectionFilter) sectionFilter.addEventListener("change", filterStudents);
if (studentStatusFilter) studentStatusFilter.addEventListener("change", filterStudents);

// Support links from the dashboard grade distribution.
if (gradeFilter) {
  const requestedGrade = new URLSearchParams(window.location.search).get('grade');
  if (requestedGrade && [...gradeFilter.options].some(option => option.value === requestedGrade)) {
    gradeFilter.value = requestedGrade;
    filterStudents();
  }
}
