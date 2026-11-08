document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // CONFIGURATION
    // ==========================================

    const grades = ["Grade 7", "Grade 8", "Grade 9", "Grade 10"];

    const subjects = [
        "Filipino",
        "English",
        "Mathematics",
        "Science",
        "Araling Panlipunan",
        "TLE",
        "Values Education",
        "MAPEH",
        "Computer",
    ];

    // ==========================================
    // HTML ELEMENTS
    // ==========================================

    const form = document.getElementById("facultyForm");
    const formModal = document.getElementById("facultyFormModal");
    const viewModal = document.getElementById("viewFacultyModal");
    const deleteModal = document.getElementById("deleteFacultyModal");
    const statusModal = document.getElementById("facultyStatusModal");
    const alertModal = document.getElementById("facultyAlertModal");

    const facultyTable = document.getElementById("facultyTable");
    const tableBody = document.getElementById("facultyTableBody");

    const emptyState = document.getElementById("facultyEmptyState");
    const emptyTitle = document.getElementById("facultyEmptyTitle");
    const emptyMessage = document.getElementById("facultyEmptyMessage");

    const addFacultyBtn = document.getElementById("addFacultyBtn");
    const emptyAddFacultyBtn = document.getElementById("emptyAddFacultyBtn");
    const addAssignmentBtn = document.getElementById("addAssignmentBtn");

    const confirmDeleteBtn = document.getElementById("confirmDeleteFaculty");
    const confirmStatusBtn = document.getElementById("confirmFacultyStatus");

    const searchInput = document.getElementById("facultySearch");
    const roleFilter = document.getElementById("roleFilter");
    const subjectFilter = document.getElementById("subjectFilter");
    const statusFilter = document.getElementById("statusFilter");

    const assignmentsList = document.getElementById("teachingAssignmentsList");

    const teacherCheckbox = document.getElementById("responsibilityTeacher");
    const adviserCheckbox = document.getElementById("responsibilityAdviser");

    const teachingSection = document.getElementById(
        "teachingAssignmentsSection",
    );

    const advisorySection = document.getElementById(
        "advisoryAssignmentSection",
    );

    const statusTitle = document.getElementById("facultyStatusTitle");
    const statusMessage = document.getElementById("facultyStatusMessage");
    const statusIcon = document.getElementById("facultyStatusIcon");

    // BULK ACTION ELEMENTS
    const selectAllCheckbox = document.querySelector(".records-select-all");
    const bulkStatusButtons = document.querySelectorAll("[data-bulk-status]");
    const bulkDeleteButton = document.querySelector("[data-bulk-delete]");
    const selectedCountElement = document.querySelector(
        "[data-selected-count]",
    );

    // ==========================================
    // INITIAL DATA AND VARIABLES
    // ==========================================

    let facultyRecords = Array.isArray(window.facultyData)
        ? window.facultyData.map((record) => ({
              ...record,
              assignments: Array.isArray(record.assignments)
                  ? record.assignments
                  : [],
              status:
                  String(record.status).toLowerCase() === "active"
                      ? "Active"
                      : "Inactive",
          }))
        : [];

    let editingRecordId = null;
    let deletingRecordId = null;
    let pendingStatusId = null;

    // ==========================================
    // BASIC HELPERS
    // ==========================================

    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, (character) => {
            const entities = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            };

            return entities[character];
        });
    }

    function getCsrfToken() {
        return (
            form?.querySelector('input[name="_token"]')?.value ||
            document.querySelector('meta[name="csrf-token"]')?.content ||
            ""
        );
    }

    function getFaculty(id) {
        return facultyRecords.find(
            (record) => Number(record.id) === Number(id),
        );
    }

    function fullName(record) {
        return [record.firstName, record.middleName, record.lastName]
            .filter(Boolean)
            .join(" ");
    }

    function getRole(record) {
        if (record.teacher && record.adviser) {
            return "Teacher + Adviser";
        }

        if (record.teacher) return "Teacher";
        if (record.adviser) return "Adviser";

        return "No Role";
    }

    function assignmentSummary(record) {
        const parts = [];

        if (record.assignments.length > 0) {
            parts.push(`${record.assignments.length} subject assignment(s)`);
        }

        if (record.adviser && record.advisoryGrade) {
            parts.push(`Adviser: ${record.advisoryGrade}`);
        }

        return parts.length ? parts.join(" · ") : "No assignments";
    }

    // ==========================================
    // MODAL FUNCTIONS
    // ==========================================

    function openModal(modal) {
        if (!modal) return;

        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");

        const anotherModalIsOpen = document.querySelector(
            ".faculty-modal-overlay.show",
        );

        if (!anotherModalIsOpen) {
            document.body.style.overflow = "";
        }
    }

    function closeAllModals() {
        [
            formModal,
            viewModal,
            deleteModal,
            statusModal,
            alertModal,
            document.getElementById("facultySuccessModal"),
        ].forEach(closeModal);
    }

    // ==========================================
    // ALERT AND SUCCESS MODALS
    // ==========================================

    function showFacultyAlert(title, message, type = "warning") {
        if (!alertModal) {
            window.alert(`${title}\n\n${message}`);
            return;
        }

        const alertTitle = document.getElementById("facultyAlertTitle");
        const alertMessage = document.getElementById("facultyAlertMessage");
        const alertIcon = document.getElementById("facultyAlertIcon");

        if (alertTitle) alertTitle.textContent = title;
        if (alertMessage) alertMessage.textContent = message;

        if (alertIcon) {
            alertIcon.className = `faculty-alert-icon ${type}`;

            alertIcon.innerHTML =
                type === "warning"
                    ? '<i class="fa-solid fa-triangle-exclamation"></i>'
                    : '<i class="fa-solid fa-circle-exclamation"></i>';
        }

        openModal(alertModal);
    }

    function showSuccessState(title, message) {
        document.getElementById("facultySuccessModal")?.remove();

        const successModal = document.createElement("div");

        successModal.id = "facultySuccessModal";
        successModal.className = "faculty-modal-overlay show";
        successModal.setAttribute("aria-hidden", "false");

        successModal.innerHTML = `
            <div class="faculty-success-modal"
                role="dialog"
                aria-modal="true">

                <div class="faculty-success-icon">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h2>${escapeHTML(title)}</h2>
                <p>${escapeHTML(message)}</p>

                <button type="button" id="facultySuccessDone">
                    Done
                </button>
            </div>
        `;

        document.body.appendChild(successModal);
        document.body.style.overflow = "hidden";

        document
            .getElementById("facultySuccessDone")
            .addEventListener("click", () => {
                window.location.reload();
            });
    }

    // ==========================================
    // BULK ACTION HELPERS
    // ==========================================

    function getSelectedFacultyIds() {
        return [
            ...document.querySelectorAll(
                "#facultyTableBody .record-checkbox:checked",
            ),
        ].map((checkbox) => Number(checkbox.dataset.recordId));
    }

    function resetBulkSelection() {
        document
            .querySelectorAll("#facultyTableBody .record-checkbox")
            .forEach((checkbox) => {
                checkbox.checked = false;
            });

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        updateBulkControls();
    }

    function updateBulkControls() {
        const selectedIds = getSelectedFacultyIds();
        const selectedCount = selectedIds.length;

        if (selectedCountElement) {
            selectedCountElement.textContent = selectedCount;
        }

        bulkStatusButtons.forEach((button) => {
            button.disabled = selectedCount === 0;
        });

        // Keep Bulk Delete disabled until its backend is implemented.
        if (bulkDeleteButton) {
            bulkDeleteButton.disabled = true;
        }

        if (selectAllCheckbox) {
            const checkboxes = [
                ...document.querySelectorAll(
                    "#facultyTableBody .record-checkbox",
                ),
            ];

            const checkedCount = checkboxes.filter(
                (checkbox) => checkbox.checked,
            ).length;

            selectAllCheckbox.checked =
                checkboxes.length > 0 && checkedCount === checkboxes.length;

            selectAllCheckbox.indeterminate =
                checkedCount > 0 && checkedCount < checkboxes.length;
        }
    }

    // ==========================================
    // FACULTY TABLE
    // ==========================================

    function updateEmptyState(filteredRecords) {
        if (!emptyState || !facultyTable) return;

        if (filteredRecords.length === 0) {
            facultyTable.style.display = "none";
            emptyState.hidden = false;

            if (facultyRecords.length === 0) {
                if (emptyTitle) {
                    emptyTitle.textContent = "No Faculty Records Yet";
                }

                if (emptyMessage) {
                    emptyMessage.textContent =
                        "Add a faculty member to get started.";
                }
            } else {
                if (emptyTitle) {
                    emptyTitle.textContent = "No Matching Faculty Found";
                }

                if (emptyMessage) {
                    emptyMessage.textContent =
                        "Try changing your search or filter options.";
                }
            }

            return;
        }

        facultyTable.style.display = "";
        emptyState.hidden = true;
    }

    function renderTable() {
        if (!tableBody) return;

        const search = searchInput
            ? searchInput.value.trim().toLowerCase()
            : "";

        const selectedRole = roleFilter?.value || "";
        const selectedSubject = subjectFilter?.value || "";
        const selectedStatus = statusFilter?.value || "";

        const filteredRecords = facultyRecords.filter((record) => {
            const searchableText = [
                record.facultyId,
                fullName(record),
                record.email,
            ]
                .join(" ")
                .toLowerCase();

            const matchesSearch = searchableText.includes(search);

            const matchesRole =
                !selectedRole || getRole(record) === selectedRole;

            const matchesSubject =
                !selectedSubject ||
                record.assignments.some(
                    (assignment) => assignment.subject === selectedSubject,
                );

            const matchesStatus =
                !selectedStatus ||
                record.status.toLowerCase() === selectedStatus.toLowerCase();

            return (
                matchesSearch && matchesRole && matchesSubject && matchesStatus
            );
        });

        tableBody.innerHTML = filteredRecords
            .map(
                (record) => `
                    <tr>
                        <td>
                            <input
                                type="checkbox"
                                class="record-checkbox"
                                data-record-id="${record.id}"
                                aria-label="Select ${escapeHTML(fullName(record))}"
                            >
                        </td>

                        <td>
                            <span class="faculty-id-text">
                                ${escapeHTML(record.facultyId)}
                            </span>
                        </td>

                        <td>
                            <span class="faculty-name-text">
                                ${escapeHTML(fullName(record))}
                            </span>
                        </td>

                        <td>
                            <span class="faculty-role-badge">
                                ${escapeHTML(getRole(record))}
                            </span>
                        </td>

                        <td>
                            <span class="faculty-email-text">
                                ${escapeHTML(record.email)}
                            </span>
                        </td>

                        <td>
                            <div class="faculty-assignment-summary">
                                ${escapeHTML(assignmentSummary(record))}
                            </div>
                        </td>

                        <td>
                            <span class="faculty-status-badge ${record.status.toLowerCase()}">
                                ${escapeHTML(record.status)}
                            </span>
                        </td>

                        <td>
                            <div class="faculty-actions">
                                <button
                                    type="button"
                                    class="faculty-action-btn view"
                                    data-action="view"
                                    data-id="${record.id}"
                                    title="View Faculty">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <button
                                    type="button"
                                    class="faculty-action-btn edit"
                                    data-action="edit"
                                    data-id="${record.id}"
                                    title="Edit Faculty">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button
                                    type="button"
                                    class="faculty-action-btn toggle"
                                    data-action="toggle"
                                    data-id="${record.id}"
                                    title="${record.status === "Active" ? "Deactivate" : "Reactivate"} Faculty">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>

                                <button
                                    type="button"
                                    class="faculty-action-btn delete"
                                    data-action="delete"
                                    data-id="${record.id}"
                                    title="Delete Faculty">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `,
            )
            .join("");

        updateEmptyState(filteredRecords);

        // Reset selection whenever search/filter changes the table.
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        updateBulkControls();
    }

    // ==========================================
    // BULK SELECTION EVENTS
    // ==========================================

    selectAllCheckbox?.addEventListener("change", () => {
        document
            .querySelectorAll("#facultyTableBody .record-checkbox")
            .forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });

        updateBulkControls();
    });

    tableBody?.addEventListener("change", (event) => {
        if (event.target.matches(".record-checkbox")) {
            updateBulkControls();
        }
    });

    // ==========================================
    // BULK ACTIVATE / DEACTIVATE
    // ==========================================

    bulkStatusButtons.forEach((button) => {
        button.addEventListener("click", async () => {
            const ids = getSelectedFacultyIds();
            const status = button.dataset.bulkStatus;

            if (!ids.length) return;

            if (!["active", "inactive"].includes(status)) {
                showFacultyAlert(
                    "Invalid Action",
                    "The selected bulk action is not valid.",
                    "error",
                );
                return;
            }

            const action = status === "active" ? "activate" : "deactivate";

            const confirmed = window.confirm(
                `Are you sure you want to ${action} ${ids.length} selected faculty member(s)?`,
            );

            if (!confirmed) return;

            const csrfToken = getCsrfToken();

            if (!csrfToken) {
                showFacultyAlert(
                    "Security Error",
                    "CSRF token not found.",
                    "error",
                );
                return;
            }

            bulkStatusButtons.forEach((item) => {
                item.disabled = true;
            });

            try {
                const response = await fetch(
                    "/admin/faculty-management/bulk-status",
                    {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({
                            ids,
                            status,
                        }),
                    },
                );

                const result = await response.json();

                if (!response.ok) {
                    const messages = result.errors
                        ? Object.values(result.errors).flat().join("\n")
                        : result.message ||
                          "Unable to update selected faculty.";

                    throw new Error(messages);
                }

                facultyRecords.forEach((record) => {
                    if (ids.includes(Number(record.id))) {
                        record.status =
                            status === "active" ? "Active" : "Inactive";
                    }
                });

                renderTable();

                showSuccessState(
                    "Bulk Status Updated!",
                    result.message ||
                        `${result.changed ?? ids.length} faculty member(s) updated successfully.`,
                );
            } catch (error) {
                console.error("Bulk faculty status error:", error);

                showFacultyAlert("Bulk Update Failed", error.message, "error");
            } finally {
                updateBulkControls();
            }
        });
    });

    // ==========================================
    // TEACHING ASSIGNMENT ROWS
    // ==========================================

    function createAssignmentRow(assignment = {}) {
        const row = document.createElement("div");
        row.className = "faculty-assignment-row";

        row.innerHTML = `
            <div class="faculty-form-group">
                <label>Grade Level *</label>

                <select class="assignment-grade" required>
                    <option value="">Select Grade</option>

                    ${grades
                        .map(
                            (grade) => `
                                <option value="${escapeHTML(grade)}">
                                    ${escapeHTML(grade)}
                                </option>
                            `,
                        )
                        .join("")}
                </select>
            </div>

            <div class="faculty-form-group">
                <label>Subject *</label>

                <select class="assignment-subject" required>
                    <option value="">Select Subject</option>

                    ${subjects
                        .map(
                            (subject) => `
                                <option value="${escapeHTML(subject)}">
                                    ${escapeHTML(subject)}
                                </option>
                            `,
                        )
                        .join("")}
                </select>
            </div>

            <button
                type="button"
                class="faculty-remove-row-btn"
                title="Remove assignment"
                aria-label="Remove assignment">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;

        row.querySelector(".assignment-grade").value = assignment.grade || "";

        row.querySelector(".assignment-subject").value =
            assignment.subject || "";

        row.querySelector(".faculty-remove-row-btn").addEventListener(
            "click",
            () => {
                row.remove();
                updateResponsibilityFields();
            },
        );

        assignmentsList.appendChild(row);

        updateResponsibilityFields();
    }

    function updateResponsibilityFields() {
        const isTeacher = Boolean(teacherCheckbox?.checked);
        const isAdviser = Boolean(adviserCheckbox?.checked);

        if (teachingSection) {
            teachingSection.hidden = !isTeacher;
        }

        if (advisorySection) {
            advisorySection.hidden = !isAdviser;
        }

        assignmentsList?.querySelectorAll("select").forEach((select) => {
            select.required = isTeacher;
        });

        const advisoryGrade = document.getElementById("advisoryGrade");

        if (advisoryGrade) {
            advisoryGrade.required = isAdviser;
        }
    }

    function collectAssignments() {
        if (!teacherCheckbox.checked) return [];

        return [
            ...assignmentsList.querySelectorAll(".faculty-assignment-row"),
        ].map((row) => ({
            grade: row.querySelector(".assignment-grade").value,
            subject: row.querySelector(".assignment-subject").value,
        }));
    }

    function validateAssignments(assignments) {
        if (!teacherCheckbox.checked) return true;

        if (assignments.length === 0) {
            showFacultyAlert(
                "Missing Teaching Assignment",
                "Please add at least one teaching assignment.",
                "warning",
            );

            return false;
        }

        if (
            assignments.some(
                (assignment) => !assignment.grade || !assignment.subject,
            )
        ) {
            showFacultyAlert(
                "Incomplete Assignment",
                "Please complete every teaching assignment.",
                "warning",
            );

            return false;
        }

        const uniqueAssignments = new Set(
            assignments.map(
                (assignment) => `${assignment.grade}|${assignment.subject}`,
            ),
        );

        if (uniqueAssignments.size !== assignments.length) {
            showFacultyAlert(
                "Duplicate Assignment",
                "Duplicate Grade Level and Subject assignments are not allowed.",
                "warning",
            );

            return false;
        }

        return true;
    }

    // ==========================================
    // ADD / EDIT FORM
    // ==========================================

    function resetForm() {
        if (!form) return;

        form.reset();

        editingRecordId = null;

        assignmentsList.innerHTML = "";

        document.getElementById("facultyFormTitle").textContent = "Add Faculty";

        document.getElementById("facultyFormDescription").textContent =
            "Enter the faculty information and assignments.";

        document.getElementById("saveFacultyText").textContent = "Save Faculty";

        teacherCheckbox.checked = true;
        adviserCheckbox.checked = false;

        document.getElementById("facultyId").readOnly = false;

        createAssignmentRow();
        updateResponsibilityFields();
    }

    function openAddForm() {
        resetForm();
        openModal(formModal);
    }

    function openEditForm(id) {
        const record = getFaculty(id);

        if (!record) return;

        resetForm();

        editingRecordId = record.id;

        document.getElementById("facultyId").value = record.facultyId || "";

        document.getElementById("facultyEmail").value = record.email || "";

        document.getElementById("facultyFirstName").value =
            record.firstName || "";

        document.getElementById("facultyMiddleName").value =
            record.middleName || "";

        document.getElementById("facultyLastName").value =
            record.lastName || "";

        document.getElementById("facultyStatus").value = record.status;

        teacherCheckbox.checked = Boolean(record.teacher);
        adviserCheckbox.checked = Boolean(record.adviser);

        assignmentsList.innerHTML = "";

        if (record.assignments.length > 0) {
            record.assignments.forEach(createAssignmentRow);
        } else if (record.teacher) {
            createAssignmentRow();
        }

        document.getElementById("advisoryGrade").value =
            record.advisoryGrade || "";

        document.getElementById("facultyFormTitle").textContent =
            "Edit Faculty";

        document.getElementById("facultyFormDescription").textContent =
            "Update the faculty information and assignments.";

        document.getElementById("saveFacultyText").textContent = "Save Changes";

        updateResponsibilityFields();
        openModal(formModal);
    }

    // ==========================================
    // VIEW FACULTY
    // ==========================================

    function openViewModal(id) {
        const record = getFaculty(id);

        if (!record) return;

        const assignmentHTML = record.assignments.length
            ? record.assignments
                  .map(
                      (assignment) => `
                        <div class="faculty-detail-assignment">
                            <strong>${escapeHTML(assignment.grade)}</strong>
                            <span>${escapeHTML(assignment.subject)}</span>
                        </div>
                    `,
                  )
                  .join("")
            : '<p class="faculty-help-text">No teaching assignments.</p>';

        document.getElementById("viewFacultyContent").innerHTML = `
            <div class="faculty-detail-grid">
                <div class="faculty-detail-item">
                    <small>Faculty ID</small>
                    <strong>${escapeHTML(record.facultyId)}</strong>
                </div>

                <div class="faculty-detail-item">
                    <small>Full Name</small>
                    <strong>${escapeHTML(fullName(record))}</strong>
                </div>

                <div class="faculty-detail-item">
                    <small>Email Address</small>
                    <strong>${escapeHTML(record.email)}</strong>
                </div>

                <div class="faculty-detail-item">
                    <small>Responsibilities</small>
                    <strong>${escapeHTML(getRole(record))}</strong>
                </div>

                <div class="faculty-detail-item">
                    <small>Status</small>
                    <strong>${escapeHTML(record.status)}</strong>
                </div>

                <div class="faculty-detail-item">
                    <small>Advisory Grade Level</small>
                    <strong>
                        ${
                            record.adviser
                                ? escapeHTML(
                                      record.advisoryGrade || "Not assigned",
                                  )
                                : "Not applicable"
                        }
                    </strong>
                </div>
            </div>

            <div class="faculty-detail-block">
                <h3>Teaching Assignments</h3>
                ${assignmentHTML}
            </div>
        `;

        openModal(viewModal);
    }

    // ==========================================
    // SAVE FACULTY
    // ==========================================

    if (form) {
        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            if (!teacherCheckbox.checked && !adviserCheckbox.checked) {
                showFacultyAlert(
                    "Missing Responsibility",
                    "Please select Teacher, Adviser, or both.",
                    "warning",
                );

                return;
            }

            const assignments = collectAssignments();

            if (!validateAssignments(assignments)) return;

            const advisoryGrade =
                document.getElementById("advisoryGrade").value;

            if (adviserCheckbox.checked && !advisoryGrade) {
                showFacultyAlert(
                    "Missing Advisory Grade",
                    "Please select the advisory grade level.",
                    "warning",
                );

                document.getElementById("advisoryGrade").focus();
                return;
            }

            const payload = {
                faculty_id: document.getElementById("facultyId").value.trim(),
                first_name: document
                    .getElementById("facultyFirstName")
                    .value.trim(),
                middle_name: document
                    .getElementById("facultyMiddleName")
                    .value.trim(),
                last_name: document
                    .getElementById("facultyLastName")
                    .value.trim(),
                email: document.getElementById("facultyEmail").value.trim(),
                status: document
                    .getElementById("facultyStatus")
                    .value.toLowerCase(),
                teacher: teacherCheckbox.checked,
                adviser: adviserCheckbox.checked,
                assignments: teacherCheckbox.checked
                    ? assignments.map((assignment) => ({
                          grade_level: assignment.grade,
                          subject: assignment.subject,
                      }))
                    : [],
                advisory_grade: adviserCheckbox.checked ? advisoryGrade : "",
                school_year: window.currentSchoolYear || "",
            };

            const isEditing = editingRecordId !== null;

            const url = isEditing
                ? `/admin/faculty-management/${editingRecordId}`
                : "/admin/faculty-management";

            const csrfToken = getCsrfToken();

            if (!csrfToken) {
                showFacultyAlert(
                    "Security Error",
                    "CSRF token not found. Please check the form's @csrf directive.",
                    "error",
                );

                return;
            }

            const saveButton = form.querySelector(
                'button[type="submit"], input[type="submit"]',
            );

            if (saveButton) saveButton.disabled = true;

            try {
                const response = await fetch(url, {
                    method: isEditing ? "PUT" : "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok) {
                    const messages = result.errors
                        ? Object.values(result.errors).flat().join("\n")
                        : result.message || "Unable to save faculty.";

                    showFacultyAlert(
                        "Unable to Save Faculty",
                        messages,
                        "error",
                    );

                    return;
                }

                closeModal(formModal);

                showSuccessState(
                    isEditing
                        ? "Faculty Updated Successfully!"
                        : "Faculty Created Successfully!",
                    result.message ||
                        "The faculty information has been saved successfully.",
                );
            } catch (error) {
                console.error("Faculty save error:", error);

                showFacultyAlert(
                    "Connection Error",
                    "Unable to connect to the server. Check the browser console and Laravel log.",
                    "error",
                );
            } finally {
                if (saveButton) saveButton.disabled = false;
            }
        });
    }

    // ==========================================
    // DELETE FACULTY
    // ==========================================

    function openDeleteModal(id) {
        const record = getFaculty(id);

        if (!record) return;

        deletingRecordId = record.id;

        const message = document.getElementById("deleteFacultyMessage");

        if (message) {
            message.textContent = `Are you sure you want to delete ${fullName(record)}?`;
        }

        openModal(deleteModal);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener("click", async () => {
            if (deletingRecordId === null) return;

            const id = deletingRecordId;
            const csrfToken = getCsrfToken();

            if (!csrfToken) {
                showFacultyAlert(
                    "Security Error",
                    "CSRF token not found.",
                    "error",
                );

                return;
            }

            confirmDeleteBtn.disabled = true;

            try {
                const response = await fetch(
                    `/admin/faculty-management/${id}`,
                    {
                        method: "DELETE",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    },
                );

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || "Unable to delete faculty.",
                    );
                }

                facultyRecords = facultyRecords.filter(
                    (record) => Number(record.id) !== Number(id),
                );

                deletingRecordId = null;

                closeModal(deleteModal);
                renderTable();

                showSuccessState(
                    "Faculty Deleted Successfully!",
                    result.message ||
                        "The faculty record has been deleted successfully.",
                );
            } catch (error) {
                console.error("Faculty delete error:", error);

                showFacultyAlert(
                    "Unable to Delete Faculty",
                    error.message,
                    "error",
                );
            } finally {
                confirmDeleteBtn.disabled = false;
            }
        });
    }

    // ==========================================
    // ACTIVATE / DEACTIVATE FACULTY
    // ==========================================

    function openFacultyStatusModal(id) {
        const record = getFaculty(id);

        if (!record) return;

        pendingStatusId = record.id;

        const isActive = record.status === "Active";

        if (statusTitle) {
            statusTitle.textContent = isActive
                ? "Deactivate Faculty?"
                : "Reactivate Faculty?";
        }

        if (statusMessage) {
            statusMessage.textContent = isActive
                ? `Are you sure you want to deactivate ${fullName(record)}? This faculty member will no longer be able to access their account. Existing assignments and grade records will be preserved.`
                : `Are you sure you want to reactivate ${fullName(record)}? This faculty member will regain access to their account.`;
        }

        if (statusIcon) {
            statusIcon.innerHTML = isActive
                ? '<i class="fa-solid fa-user-slash"></i>'
                : '<i class="fa-solid fa-user-check"></i>';
        }

        if (confirmStatusBtn) {
            confirmStatusBtn.textContent = isActive
                ? "Deactivate Faculty"
                : "Reactivate Faculty";

            confirmStatusBtn.className = isActive
                ? "faculty-btn-delete"
                : "faculty-btn-save";
        }

        openModal(statusModal);
    }

    if (confirmStatusBtn) {
        confirmStatusBtn.addEventListener("click", async () => {
            if (pendingStatusId === null) return;

            const id = pendingStatusId;
            const csrfToken = getCsrfToken();

            if (!csrfToken) {
                showFacultyAlert(
                    "Security Error",
                    "CSRF token not found.",
                    "error",
                );

                return;
            }

            confirmStatusBtn.disabled = true;

            try {
                const response = await fetch(
                    `/admin/faculty-management/${id}/toggle-status`,
                    {
                        method: "PATCH",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    },
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || "Unable to update faculty status.",
                    );
                }

                const record = getFaculty(id);

                if (record) {
                    record.status =
                        String(result.status).toLowerCase() === "active"
                            ? "Active"
                            : "Inactive";
                }

                pendingStatusId = null;

                closeModal(statusModal);
                renderTable();

                showSuccessState(
                    "Faculty Status Updated!",
                    result.message || "Faculty status updated successfully.",
                );
            } catch (error) {
                console.error("Faculty status update error:", error);

                closeModal(statusModal);

                showFacultyAlert("Update Failed", error.message, "error");
            } finally {
                confirmStatusBtn.disabled = false;
            }
        });
    }

    // ==========================================
    // TABLE ACTIONS
    // ==========================================

    if (tableBody) {
        tableBody.addEventListener("click", (event) => {
            const button = event.target.closest("button[data-action]");

            if (!button) return;

            const id = Number(button.dataset.id);
            const action = button.dataset.action;

            if (action === "view") {
                openViewModal(id);
            } else if (action === "edit") {
                openEditForm(id);
            } else if (action === "delete") {
                openDeleteModal(id);
            } else if (action === "toggle") {
                openFacultyStatusModal(id);
            }
        });
    }

    // ==========================================
    // SEARCH AND FILTERS
    // ==========================================

    searchInput?.addEventListener("input", renderTable);
    roleFilter?.addEventListener("change", renderTable);
    subjectFilter?.addEventListener("change", renderTable);
    statusFilter?.addEventListener("change", renderTable);

    // ==========================================
    // BUTTON EVENTS
    // ==========================================

    addFacultyBtn?.addEventListener("click", openAddForm);
    emptyAddFacultyBtn?.addEventListener("click", openAddForm);

    addAssignmentBtn?.addEventListener("click", () => {
        createAssignmentRow();
    });

    teacherCheckbox?.addEventListener("change", updateResponsibilityFields);

    adviserCheckbox?.addEventListener("change", updateResponsibilityFields);

    // ==========================================
    // CLOSE MODALS
    // ==========================================

    document.querySelectorAll("[data-close-modal]").forEach((button) => {
        button.addEventListener("click", () => {
            const modal = button.closest(".faculty-modal-overlay");

            closeModal(modal);
        });
    });

    [formModal, viewModal, deleteModal, statusModal, alertModal].forEach(
        (modal) => {
            modal?.addEventListener("click", (event) => {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        },
    );

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeAllModals();
        }
    });

    // ==========================================
    // INITIALIZE
    // ==========================================

    if (
        !tableBody ||
        !form ||
        !assignmentsList ||
        !teacherCheckbox ||
        !adviserCheckbox
    ) {
        console.error(
            "Faculty Management: Required HTML elements are missing. Check the IDs in faculty-management.blade.php.",
        );

        return;
    }

    updateResponsibilityFields();
    renderTable();
    updateBulkControls();
});
z