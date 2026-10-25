
console.log("STUDENT MANAGEMENT JS LOADED");
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
console.log("CSRF token found:", Boolean(csrfToken));

// ==========================================
// MODAL LOGIC (Add Teacher)
// ==========================================
const teacherModal = document.getElementById("teacherModal");
const addTeacherBtn = document.getElementById("addTeacherBtn");
const closeTeacherModal = document.getElementById("closeTeacherModal");
const cancelTeacherModal = document.getElementById("cancelTeacherModal");
const saveTeacherBtn = document.getElementById("saveTeacherBtn");

function openTeacherModal() { 
  if (teacherModal) teacherModal.classList.add("show"); 
}

function closeTeacherModalFn() { 
  if (teacherModal) teacherModal.classList.remove("show"); 
}

if (addTeacherBtn) addTeacherBtn.addEventListener("click", openTeacherModal);
if (closeTeacherModal) closeTeacherModal.addEventListener("click", closeTeacherModalFn);
if (cancelTeacherModal) cancelTeacherModal.addEventListener("click", closeTeacherModalFn);

if (teacherModal) {
  teacherModal.addEventListener("click", (event) => {
    if (event.target === teacherModal) closeTeacherModalFn();
  });
}

// ==========================================
// EDIT MODAL CLOSE/CANCEL
// ==========================================
const closeEditModal = document.getElementById("closeEditModal");
const cancelEditModal = document.getElementById("cancelEditModal");
const editTeacherModal = document.getElementById("editTeacherModal");

if (closeEditModal) {
  closeEditModal.addEventListener("click", () => {
    editTeacherModal.classList.remove("show");
  });
}

if (cancelEditModal) {
  cancelEditModal.addEventListener("click", () => {
    editTeacherModal.classList.remove("show");
  });
}

if (editTeacherModal) {
  editTeacherModal.addEventListener("click", (event) => {
    if (event.target === editTeacherModal) {
      editTeacherModal.classList.remove("show");
    }
  });
}

// ==========================================
// DELETE MODAL CLOSE/CANCEL
// ==========================================
const closeDeleteModal = document.getElementById("closeDeleteModal");
const cancelDeleteModal = document.getElementById("cancelDeleteModal");
const deleteTeacherModal = document.getElementById("deleteTeacherModal");

if (closeDeleteModal) {
  closeDeleteModal.addEventListener("click", () => {
    deleteTeacherModal.classList.remove("show");
  });
}

if (cancelDeleteModal) {
  cancelDeleteModal.addEventListener("click", () => {
    deleteTeacherModal.classList.remove("show");
  });
}

if (deleteTeacherModal) {
  deleteTeacherModal.addEventListener("click", (event) => {
    if (event.target === deleteTeacherModal) {
      deleteTeacherModal.classList.remove("show");
    }
  });
}

// ==========================================
// VIEW MODAL CLOSE/CANCEL
// ==========================================
const closeViewModal = document.getElementById("closeViewModal");
const closeViewModalBtn = document.getElementById("closeViewModalBtn");
const viewTeacherModal = document.getElementById("viewTeacherModal");

if (closeViewModal) {
  closeViewModal.addEventListener("click", () => {
    viewTeacherModal.classList.remove("show");
  });
}

if (closeViewModalBtn) {
  closeViewModalBtn.addEventListener("click", () => {
    viewTeacherModal.classList.remove("show");
  });
}

if (viewTeacherModal) {
  viewTeacherModal.addEventListener("click", (event) => {
    if (event.target === viewTeacherModal) {
      viewTeacherModal.classList.remove("show");
    }
  });
}

// ==========================================
// GLOBAL ESCAPE KEY
// ==========================================
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeTeacherModalFn();
    if (editTeacherModal) editTeacherModal.classList.remove("show");
    if (deleteTeacherModal) deleteTeacherModal.classList.remove("show");
    if (viewTeacherModal) viewTeacherModal.classList.remove("show");
  }
});

// ==========================================
// ADD TEACHER
// ==========================================
if (saveTeacherBtn) {
  saveTeacherBtn.addEventListener("click", async function (e) {
    e.preventDefault();

    const formData = {
      teacher_id: document.getElementById("teacherId").value.trim(),
      first_name: document.getElementById("teacherFirstName").value.trim(),
      middle_name: document.getElementById("teacherMiddleName").value.trim(),
      last_name: document.getElementById("teacherLastName").value.trim(),
      email: document.getElementById("teacherEmail").value.trim(),
      subject: document.getElementById("teacherSubject").value,
      school_year: document.getElementById("teacherSchoolYear").value,
      status: document.getElementById("teacherStatus").value,
    };

    try {
      const response = await fetch("/admin/teachers", {
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
        // ✅ CHANGED: alert() to showModal() with 'success' type
        showModal(data.message || "Teacher added successfully!", 'success');
        closeTeacherModalFn();
        // Clear form
        document.getElementById("teacherId").value = "";
        document.getElementById("teacherFirstName").value = "";
        document.getElementById("teacherMiddleName").value = "";
        document.getElementById("teacherLastName").value = "";
        document.getElementById("teacherEmail").value = "";
        document.getElementById("teacherSubject").value = "";
        document.getElementById("teacherSchoolYear").value = "";
        document.getElementById("teacherStatus").value = "active";
        
        // ✅ CHANGED: Delay reload so user can see the modal
        setTimeout(() => { window.location.reload(); }, 1500);
      } else {
        let errorMsg = "Error saving teacher:\n";
        if (data.errors) {
          for (const field in data.errors) {
            const fieldName = field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            errorMsg += `- ${fieldName}: ${data.errors[field][0]}\n`;
          }
        } else {
          errorMsg += data.message || "Check required fields.";
        }
        // ✅ CHANGED: alert() to showModal() with 'error' type
        showModal(errorMsg, 'error');
        console.error("Validation errors:", data.errors);
      }
    } catch (error) {
      console.error("Fetch error:", error);
      // ✅ CHANGED: alert() to showModal() with 'error' type
      showModal("An unexpected error occurred while saving.", 'error');
    }
  }); 
}

// ==========================================
// VIEW TEACHER
// ==========================================
window.viewTeacher = function (id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;

  const setId = document.getElementById("viewTeacherId");
  const setName = document.getElementById("viewTeacherName");
  const setEmail = document.getElementById("viewEmail");
  const setSubject = document.getElementById("viewSubject");
  const setYear = document.getElementById("viewSchoolYear");
  const setStatus = document.getElementById("viewStatus");

  if (setId) setId.textContent = row.dataset.teacherId || "-";
  if (setName) setName.textContent = `${row.dataset.firstName || ""} ${row.dataset.middleName || ""} ${row.dataset.lastName || ""}`.trim() || "-";
  if (setEmail) setEmail.textContent = row.dataset.email || "-";
  if (setSubject) setSubject.textContent = row.dataset.subject || "-";
  if (setYear) setYear.textContent = row.dataset.schoolYear || "-";
  if (setStatus) setStatus.textContent = (row.dataset.status || "-").charAt(0).toUpperCase() + (row.dataset.status || "").slice(1);

  if (viewTeacherModal) viewTeacherModal.classList.add("show");
};

// ==========================================
// EDIT TEACHER
// ==========================================
window.editTeacher = function (id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;

  const setId = document.getElementById("editTeacherId");
  const setName = document.getElementById("editTeacherName");
  const setEmail = document.getElementById("editEmail");
  const setSubject = document.getElementById("editSubject");
  const setYear = document.getElementById("editSchoolYear");

  if (setId) setId.value = row.dataset.teacherId || "";
  if (setName) setName.value = `${row.dataset.firstName || ""} ${row.dataset.middleName || ""} ${row.dataset.lastName || ""}`.trim();
  if (setEmail) setEmail.value = row.dataset.email || "";
  if (setSubject) setSubject.value = row.dataset.subject || "";
  if (setYear) setYear.value = row.dataset.schoolYear || "";

  if (editTeacherModal) editTeacherModal.classList.add("show");

  const saveEditBtn = document.getElementById("saveEditTeacher");
  if (saveEditBtn) {
    const newBtn = saveEditBtn.cloneNode(true);
    saveEditBtn.parentNode.replaceChild(newBtn, saveEditBtn);

    newBtn.addEventListener("click", async function () {
      const fullName = document.getElementById("editTeacherName").value.split(" ");
      const updateData = {
        teacher_id: document.getElementById("editTeacherId").value.trim(),
        first_name: fullName[0] || "",
        middle_name: fullName.length > 2 ? fullName.slice(1, -1).join(" ") : "",
        last_name: fullName[fullName.length - 1] || "",
        email: document.getElementById("editEmail").value.trim(),
        subject: document.getElementById("editSubject").value,
        school_year: document.getElementById("editSchoolYear").value,
        status: "active",
        _method: "PUT",
      };

      try {
        const response = await fetch(`/admin/teachers/${id}`, {
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
          // ✅ CHANGED: alert() to showModal() with 'success' type
          showModal(data.message || "Teacher updated successfully!", 'success');
          if (editTeacherModal) editTeacherModal.classList.remove("show");
          
          // ✅ CHANGED: Delay reload so user can see the modal
          setTimeout(() => { window.location.reload(); }, 1500);
        } else {
          let errorMsg = "Error updating teacher:\n";
          if (data.errors) {
            for (const field in data.errors) {
              errorMsg += `- ${field}: ${data.errors[field][0]}\n`;
            }
          }
          // ✅ CHANGED: alert() to showModal() with 'error' type
          showModal(errorMsg, 'error');
        }
      } catch (error) {
        console.error("Fetch error:", error);
        // ✅ CHANGED: alert() to showModal() with 'error' type
        showModal("An unexpected error occurred while updating.", 'error');
      }
    });
  }
};

// ==========================================
// DELETE TEACHER
// ==========================================
window.deleteTeacher = function (id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) return;

  const teacherName = `${row.dataset.firstName || ""} ${row.dataset.lastName || ""}`.trim();
  document.getElementById("deleteTeacherName").textContent = teacherName || "this teacher";
  if (deleteTeacherModal) deleteTeacherModal.classList.add("show");

  const confirmDeleteBtn = document.getElementById("confirmDeleteTeacher");
  if (confirmDeleteBtn) {
    const newConfirmBtn = confirmDeleteBtn.cloneNode(true);
    confirmDeleteBtn.parentNode.replaceChild(newConfirmBtn, confirmDeleteBtn);

    newConfirmBtn.addEventListener("click", async function () {
      try {
        const response = await fetch(`/admin/teachers/${id}`, {
          method: "DELETE",
          headers: { 
            "X-CSRF-TOKEN": csrfToken, 
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
          },
        });

        const data = await response.json();
        if (response.ok && data.success) {
          // ✅ CHANGED: alert() to showModal() with 'success' type
          showModal(data.message || "Teacher deleted successfully!", 'success');
          if (deleteTeacherModal) deleteTeacherModal.classList.remove("show");
          
          // ✅ CHANGED: Delay reload so user can see the modal
          setTimeout(() => { window.location.reload(); }, 1500);
        } else {
          // ✅ CHANGED: alert() to showModal() with 'error' type
          showModal("Error deleting teacher.", 'error');
        }
      } catch (error) {
        console.error("Fetch error:", error);
        // ✅ CHANGED: alert() to showModal() with 'error' type
        showModal("An unexpected error occurred while deleting.", 'error');
      }
    });
  }
};

// ==========================================
// HIDE / UNHIDE (Toggle Status)
// ==========================================
window.toggleStatus = async function (id) {
  try {
    const response = await fetch(`/admin/teachers/${id}/toggle-status`, {
      method: "PATCH",
      headers: { 
        "X-CSRF-TOKEN": csrfToken, 
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
    });

    const data = await response.json();
    if (response.ok && data.success) {
      // ✅ CHANGED: alert() to showModal() with 'success' type
      showModal(`Teacher is now ${data.status}.`, 'success');
      
      // ✅ CHANGED: Delay reload so user can see the modal
      setTimeout(() => { window.location.reload(); }, 1500);
    } else {
      // ✅ CHANGED: alert() to showModal() with 'error' type
      showModal("Error updating status.", 'error');
    }
  } catch (error) {
    console.error("Fetch error:", error);
    // ✅ CHANGED: alert() to showModal() with 'error' type
    showModal("An unexpected error occurred while updating status.", 'error');
  }
};

// ==========================================
// SEARCH AND FILTERS
// ==========================================
const teacherSearch = document.getElementById("teacherSearch");
const subjectFilter = document.getElementById("subjectFilter");
const statusFilter = document.getElementById("statusFilter"); 
const teacherTableBody = document.getElementById("teacherTableBody");

function filterTeachers() {
  const searchValue = teacherSearch ? teacherSearch.value.toLowerCase().trim() : "";
  const selectedSubject = subjectFilter ? subjectFilter.value : "all";
  const selectedStatus = statusFilter ? statusFilter.value : "all";

  const rows = teacherTableBody ? teacherTableBody.querySelectorAll("tr") : [];

  rows.forEach((row) => {
    const teacherId = (row.dataset.teacherId || "").toLowerCase();
    const teacherName = `${row.dataset.firstName || ""} ${row.dataset.lastName || ""}`.toLowerCase();
    const rowSubject = row.dataset.subject || "";
    const rowStatus = row.dataset.status || "";

    const matchesSearch = searchValue === "" || teacherId.includes(searchValue) || teacherName.includes(searchValue);
    const matchesSubject = selectedSubject === "all" || rowSubject === selectedSubject;
    const matchesStatus = selectedStatus === "all" || rowStatus === selectedStatus;

    row.style.display = (matchesSearch && matchesSubject && matchesStatus) ? "" : "none";
  });
}

if (teacherSearch) teacherSearch.addEventListener("input", filterTeachers);
if (subjectFilter) subjectFilter.addEventListener("change", filterTeachers);
if (statusFilter) statusFilter.addEventListener("change", filterTeachers);``