// console.log("TEACHER MANAGEMENT JS LOADED");

// const csrfToken = document
//     .querySelector('meta[name="csrf-token"]')
//     ?.getAttribute("content");

// const teacherModal = document.getElementById("teacherModal");
// const editTeacherModal = document.getElementById("editTeacherModal");
// const viewTeacherModal = document.getElementById("viewTeacherModal");
// const deleteTeacherModal = document.getElementById("deleteTeacherModal");

// const teacherTableBody = document.getElementById("teacherTableBody");
// const teacherSearch = document.getElementById("teacherSearch");
// const subjectFilter = document.getElementById("subjectFilter");
// const statusFilter = document.getElementById("statusFilter");

// const addTeacherBtn = document.getElementById("addTeacherBtn");
// const saveTeacherBtn = document.getElementById("saveTeacherBtn");

// let editingTeacherId = null;
// let deletingTeacherId = null;

// /* NOTIFICATIONS */

// function notifyUser(message, type = "info") {
//     if (typeof window.showModal === "function") {
//         window.showModal(message, type);
//     } else {
//         window.alert(message);
//     }
// }

// /* MODAL HELPERS */

// function openModal(modal) {
//     if (modal) {
//         modal.classList.add("show");
//         document.body.style.overflow = "hidden";
//     }
// }

// function closeModal(modal) {
//     if (modal) {
//         modal.classList.remove("show");
//     }

//     const anyModalOpen = document.querySelector(".modal-overlay.show");

//     if (!anyModalOpen) {
//         document.body.style.overflow = "";
//     }
// }

// function closeAllModals() {
//     [
//         teacherModal,
//         editTeacherModal,
//         viewTeacherModal,
//         deleteTeacherModal,
//     ].forEach(closeModal);
// }

// async function sendRequest(url, options = {}) {
//     const response = await fetch(url, {
//         ...options,
//         headers: {
//             Accept: "application/json",
//             "X-Requested-With": "XMLHttpRequest",
//             ...(csrfToken ? { "X-CSRF-TOKEN": csrfToken } : {}),
//             ...(options.headers || {}),
//         },
//     });

//     let data;

//     try {
//         data = await response.json();
//     } catch {
//         throw new Error(
//             `The server returned an unexpected response (${response.status}).`,
//         );
//     }

//     if (!response.ok) {
//         const errors = data.errors
//             ? Object.values(data.errors).flat().join("\n")
//             : "";

//         throw new Error(
//             errors || data.message || `Request failed (${response.status}).`,
//         );
//     }

//     return data;
// }

// function getValue(id) {
//     return document.getElementById(id)?.value.trim() ?? "";
// }

// function setValue(id, value) {
//     const element = document.getElementById(id);

//     if (element) {
//         element.value = value ?? "";
//     }
// }

// function getTeacherRow(id) {
//     return document.querySelector(
//         `#teacherTableBody tr[data-id="${CSS.escape(String(id))}"]`,
//     );
// }

// function getTeacherFullName(row) {
//     return [row.dataset.firstName, row.dataset.middleName, row.dataset.lastName]
//         .filter(Boolean)
//         .join(" ");
// }

// function formatStatus(status) {
//     const value = String(status || "");

//     return value ? value.charAt(0).toUpperCase() + value.slice(1) : "-";
// }

// /* ADD TEACHER MODAL */

// function resetAddTeacherForm() {
//     [
//         "teacherId",
//         "teacherFirstName",
//         "teacherMiddleName",
//         "teacherLastName",
//         "teacherEmail",
//         "teacherSubject",
//         "teacherSchoolYear",
//     ].forEach((id) => setValue(id, ""));

//     setValue("teacherStatus", "active");
// }

// if (addTeacherBtn) {
//     addTeacherBtn.addEventListener("click", () => {
//         resetAddTeacherForm();
//         openModal(teacherModal);
//     });
// }

// document
//     .getElementById("closeTeacherModal")
//     ?.addEventListener("click", () => closeModal(teacherModal));

// document
//     .getElementById("cancelTeacherModal")
//     ?.addEventListener("click", () => closeModal(teacherModal));

// if (teacherModal) {
//     teacherModal.addEventListener("click", (event) => {
//         if (event.target === teacherModal) {
//             closeModal(teacherModal);
//         }
//     });
// }

// /* SAVE NEW TEACHER */

// if (saveTeacherBtn) {
//     saveTeacherBtn.addEventListener("click", async () => {
//         const formData = {
//             teacher_id: getValue("teacherId"),
//             first_name: getValue("teacherFirstName"),
//             middle_name: getValue("teacherMiddleName"),
//             last_name: getValue("teacherLastName"),
//             email: getValue("teacherEmail"),
//             subject: getValue("teacherSubject"),
//             school_year: getValue("teacherSchoolYear"),
//             status: getValue("teacherStatus"),
//         };

//         const requiredFields = [
//             ["teacher_id", "Teacher ID"],
//             ["first_name", "First name"],
//             ["last_name", "Last name"],
//             ["email", "Email address"],
//             ["subject", "Subject"],
//             ["school_year", "School year"],
//         ];

//         for (const [field, label] of requiredFields) {
//             if (!formData[field]) {
//                 notifyUser(`${label} is required.`, "warning");
//                 return;
//             }
//         }

//         if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
//             notifyUser("Please enter a valid email address.", "warning");
//             return;
//         }

//         saveTeacherBtn.disabled = true;

//         try {
//             const data = await sendRequest("/admin/teachers", {
//                 method: "POST",
//                 headers: {
//                     "Content-Type": "application/json",
//                 },
//                 body: JSON.stringify(formData),
//             });

//             closeModal(teacherModal);
//             notifyUser(
//                 data.message || "Teacher added successfully!",
//                 "success",
//             );

//             setTimeout(() => window.location.reload(), 1200);
//         } catch (error) {
//             console.error("Add teacher error:", error);
//             notifyUser(error.message || "Unable to add teacher.", "error");
//         } finally {
//             saveTeacherBtn.disabled = false;
//         }
//     });
// }

// /* CLOSE EDIT MODAL */

// document
//     .getElementById("closeEditModal")
//     ?.addEventListener("click", () => closeModal(editTeacherModal));

// document
//     .getElementById("cancelEditModal")
//     ?.addEventListener("click", () => closeModal(editTeacherModal));

// if (editTeacherModal) {
//     editTeacherModal.addEventListener("click", (event) => {
//         if (event.target === editTeacherModal) {
//             closeModal(editTeacherModal);
//         }
//     });
// }

// /* VIEW TEACHER */

// window.viewTeacher = function (id) {
//     const row = getTeacherRow(id);

//     if (!row) {
//         notifyUser("Teacher record not found.", "error");
//         return;
//     }

//     setValue("viewTeacherId", row.dataset.teacherId);
//     const nameElement = document.getElementById("viewTeacherName");
//     const emailElement = document.getElementById("viewEmail");
//     const subjectElement = document.getElementById("viewSubject");
//     const yearElement = document.getElementById("viewSchoolYear");
//     const statusElement = document.getElementById("viewStatus");

//     if (nameElement) nameElement.textContent = getTeacherFullName(row) || "-";
//     if (emailElement) emailElement.textContent = row.dataset.email || "-";
//     if (subjectElement) subjectElement.textContent = row.dataset.subject || "-";
//     if (yearElement) yearElement.textContent = row.dataset.schoolYear || "-";
//     if (statusElement)
//         statusElement.textContent = formatStatus(row.dataset.status);

//     openModal(viewTeacherModal);
// };

// document
//     .getElementById("closeViewModal")
//     ?.addEventListener("click", () => closeModal(viewTeacherModal));

// document
//     .getElementById("closeViewModalBtn")
//     ?.addEventListener("click", () => closeModal(viewTeacherModal));

// if (viewTeacherModal) {
//     viewTeacherModal.addEventListener("click", (event) => {
//         if (event.target === viewTeacherModal) {
//             closeModal(viewTeacherModal);
//         }
//     });
// }

// /* EDIT TEACHER */

// window.editTeacher = function (id) {
//     const row = getTeacherRow(id);

//     if (!row) {
//         notifyUser("Teacher record not found.", "error");
//         return;
//     }

//     editingTeacherId = id;

//     setValue("editTeacherId", row.dataset.teacherId);
//     setValue("editTeacherName", getTeacherFullName(row));
//     setValue("editEmail", row.dataset.email);
//     setValue("editSubject", row.dataset.subject);
//     setValue("editSchoolYear", row.dataset.schoolYear);

//     openModal(editTeacherModal);
// };

// const saveEditBtn = document.getElementById("saveEditTeacher");

// if (saveEditBtn) {
//     saveEditBtn.addEventListener("click", async () => {
//         if (editingTeacherId === null) return;

//         const fullName = getValue("editTeacherName")
//             .split(/\s+/)
//             .filter(Boolean);

//         if (fullName.length < 2) {
//             notifyUser("Please enter the teacher's full name.", "warning");
//             return;
//         }

//         const updateData = {
//             teacher_id: getValue("editTeacherId"),
//             first_name: fullName[0],
//             middle_name:
//                 fullName.length > 2 ? fullName.slice(1, -1).join(" ") : "",
//             last_name: fullName[fullName.length - 1],
//             email: getValue("editEmail"),
//             subject: getValue("editSubject"),
//             school_year: getValue("editSchoolYear"),
//             _method: "PUT",
//         };

//         if (
//             !updateData.teacher_id ||
//             !updateData.email ||
//             !updateData.subject ||
//             !updateData.school_year
//         ) {
//             notifyUser("Please complete all required fields.", "warning");
//             return;
//         }

//         if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(updateData.email)) {
//             notifyUser("Please enter a valid email address.", "warning");
//             return;
//         }

//         saveEditBtn.disabled = true;

//         try {
//             const data = await sendRequest(
//                 `/admin/teachers/${editingTeacherId}`,
//                 {
//                     method: "POST",
//                     headers: {
//                         "Content-Type": "application/json",
//                     },
//                     body: JSON.stringify(updateData),
//                 },
//             );

//             closeModal(editTeacherModal);
//             notifyUser(
//                 data.message || "Teacher updated successfully!",
//                 "success",
//             );

//             setTimeout(() => window.location.reload(), 1200);
//         } catch (error) {
//             console.error("Update teacher error:", error);
//             notifyUser(error.message || "Unable to update teacher.", "error");
//         } finally {
//             saveEditBtn.disabled = false;
//         }
//     });
// }

// /* CLOSE DELETE MODAL */

// document
//     .getElementById("closeDeleteModal")
//     ?.addEventListener("click", () => closeModal(deleteTeacherModal));

// document
//     .getElementById("cancelDeleteModal")
//     ?.addEventListener("click", () => closeModal(deleteTeacherModal));

// if (deleteTeacherModal) {
//     deleteTeacherModal.addEventListener("click", (event) => {
//         if (event.target === deleteTeacherModal) {
//             closeModal(deleteTeacherModal);
//         }
//     });
// }

// /* DELETE TEACHER */

// window.deleteTeacher = function (id) {
//     const row = getTeacherRow(id);

//     if (!row) {
//         notifyUser("Teacher record not found.", "error");
//         return;
//     }

//     deletingTeacherId = id;

//     const nameElement = document.getElementById("deleteTeacherName");

//     if (nameElement) {
//         nameElement.textContent = getTeacherFullName(row) || "this teacher";
//     }

//     openModal(deleteTeacherModal);
// };

// const confirmDeleteBtn = document.getElementById("confirmDeleteTeacher");

// if (confirmDeleteBtn) {
//     confirmDeleteBtn.addEventListener("click", async () => {
//         if (deletingTeacherId === null) return;

//         confirmDeleteBtn.disabled = true;

//         try {
//             const data = await sendRequest(
//                 `/admin/teachers/${deletingTeacherId}`,
//                 {
//                     method: "DELETE",
//                 },
//             );

//             closeModal(deleteTeacherModal);
//             notifyUser(
//                 data.message || "Teacher deleted successfully!",
//                 "success",
//             );

//             setTimeout(() => window.location.reload(), 1200);
//         } catch (error) {
//             console.error("Delete teacher error:", error);
//             notifyUser(error.message || "Unable to delete teacher.", "error");
//         } finally {
//             confirmDeleteBtn.disabled = false;
//         }
//     });
// }

// /* TOGGLE TEACHER STATUS */

// window.toggleStatus = async function (id) {
//     const row = getTeacherRow(id);

//     if (!row) {
//         notifyUser("Teacher record not found.", "error");
//         return;
//     }

//     try {
//         const data = await sendRequest(`/admin/teachers/${id}/toggle-status`, {
//             method: "PATCH",
//         });

//         notifyUser(
//             data.message ||
//                 `Teacher status updated to ${data.status || "updated"}.`,
//             "success",
//         );

//         setTimeout(() => window.location.reload(), 1200);
//     } catch (error) {
//         console.error("Toggle teacher status error:", error);
//         notifyUser(
//             error.message || "Unable to update teacher status.",
//             "error",
//         );
//     }
// };

// /* SEARCH AND FILTERS */

// function filterTeachers() {
//     const searchValue = teacherSearch?.value.toLowerCase().trim() || "";
//     const selectedSubject = subjectFilter?.value || "";
//     const selectedStatus = statusFilter?.value || "";

//     const rows = teacherTableBody
//         ? teacherTableBody.querySelectorAll("tr[data-id]")
//         : [];

//     let visibleCount = 0;

//     rows.forEach((row) => {
//         const teacherId = (row.dataset.teacherId || "").toLowerCase();
//         const teacherName = getTeacherFullName(row).toLowerCase();
//         const email = (row.dataset.email || "").toLowerCase();
//         const subject = row.dataset.subject || "";
//         const status = (row.dataset.status || "").toLowerCase();

//         const matchesSearch =
//             !searchValue ||
//             teacherId.includes(searchValue) ||
//             teacherName.includes(searchValue) ||
//             email.includes(searchValue);

//         const matchesSubject = !selectedSubject || subject === selectedSubject;

//         const matchesStatus =
//             !selectedStatus || status === selectedStatus.toLowerCase();

//         const visible = matchesSearch && matchesSubject && matchesStatus;

//         row.style.display = visible ? "" : "none";

//         if (visible) visibleCount++;
//     });

//     const emptyRow = teacherTableBody?.querySelector(".teacher-empty-row");

//     if (emptyRow) {
//         emptyRow.style.display = rows.length === 0 ? "" : "none";
//     }

//     let noResultsRow = teacherTableBody?.querySelector(".teacher-no-results");

//     if (teacherTableBody && rows.length > 0 && visibleCount === 0) {
//         if (!noResultsRow) {
//             noResultsRow = document.createElement("tr");
//             noResultsRow.className = "teacher-no-results";
//             noResultsRow.innerHTML = `
//                 <td colspan="7" class="teacher-no-results-cell">
//                     No matching teacher records found.
//                 </td>
//             `;
//             teacherTableBody.appendChild(noResultsRow);
//         }

//         noResultsRow.style.display = "";
//     } else if (noResultsRow) {
//         noResultsRow.style.display = "none";
//     }
// }

// teacherSearch?.addEventListener("input", filterTeachers);
// subjectFilter?.addEventListener("change", filterTeachers);
// statusFilter?.addEventListener("change", filterTeachers);

// /* CLOSE MODALS WITH ESCAPE */

// document.addEventListener("keydown", (event) => {
//     if (event.key === "Escape") {
//         closeAllModals();
//     }
// });
