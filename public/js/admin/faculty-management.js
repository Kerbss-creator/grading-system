document.addEventListener("DOMContentLoaded", () => {
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

    const grades = ["Grade 7", "Grade 8", "Grade 9", "Grade 10"];

    // DEMO DATA ONLY — records are not saved to the database.
// Load faculty records from Laravel database.
    let facultyRecords = (window.facultyData || []).map((record) => ({
        ...record,
        assignments: record.assignments || [],
    }));

    let editingRecordId = null;
    let deletingRecordId = null;

    const tableBody = document.getElementById("facultyTableBody");
    const emptyState = document.getElementById("facultyEmptyState");
    const emptyTitle = document.getElementById("facultyEmptyTitle");
    const emptyMessage = document.getElementById("facultyEmptyMessage");

    const formModal = document.getElementById("facultyFormModal");
    const viewModal = document.getElementById("viewFacultyModal");
    const deleteModal = document.getElementById("deleteFacultyModal");

    const form = document.getElementById("facultyForm");
    const assignmentsList = document.getElementById("teachingAssignmentsList");
    const teacherCheckbox = document.getElementById("responsibilityTeacher");
    const adviserCheckbox = document.getElementById("responsibilityAdviser");

    const teachingSection = document.getElementById(
        "teachingAssignmentsSection",
    );

    const advisorySection = document.getElementById(
        "advisoryAssignmentSection",
    );

    const searchInput = document.getElementById("facultySearch");
    const roleFilter = document.getElementById("roleFilter");
    const subjectFilter = document.getElementById("subjectFilter");
    const statusFilter = document.getElementById("statusFilter");

    const totalFaculty = document.getElementById("totalFaculty");
    const activeFaculty = document.getElementById("activeFaculty");
    const adviserFaculty = document.getElementById("adviserFaculty");

    const facultyTable = tableBody ? tableBody.closest("table") : null;

    // Check that the required HTML elements exist.
    if (
        !tableBody ||
        !emptyState ||
        !emptyTitle ||
        !emptyMessage ||
        !facultyTable ||
        !form
    ) {
        console.error(
            "Faculty Management: A required HTML element is missing. Check the Blade IDs.",
        );
        return;
    }

    // Escape text before placing user-entered values into HTML.
    function escapeHTML(value) {
        return String(value ?? "").replace(
            /[&<>"']/g,
            (character) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#039;",
                })[character],
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

        if (!document.querySelector(".faculty-modal-overlay.show")) {
            document.body.style.overflow = "";
        }
    }

    function closeAllModals() {
        [formModal, viewModal, deleteModal].forEach(closeModal);
    }

    function assignmentSummary(record) {
        const parts = [];

        if (record.assignments.length) {
            parts.push(`${record.assignments.length} subject assignment(s)`);
        }

        if (record.adviser && record.advisoryGrade) {
            parts.push(`Adviser: ${record.advisoryGrade}`);
        }

        return parts.length ? parts.join(" · ") : "No assignments";
    }

    // Update summary cards.
    function updateSummaryCards() {
        if (totalFaculty) {
            totalFaculty.textContent = facultyRecords.length;
        }

        if (activeFaculty) {
            activeFaculty.textContent = facultyRecords.filter(
                (record) => record.status === "Active",
            ).length;
        }

        if (adviserFaculty) {
            adviserFaculty.textContent = facultyRecords.filter(
                (record) => record.adviser,
            ).length;
        }
    }

    // Show the correct empty state or the faculty table.
    function updateEmptyState(filteredRecords) {
        if (filteredRecords.length === 0) {
            facultyTable.style.display = "none";
            emptyState.hidden = false;

            if (facultyRecords.length === 0) {
                emptyTitle.textContent = "No Faculty Records Yet";
                emptyMessage.textContent =
                    "Add a faculty member to get started.";
            } else {
                emptyTitle.textContent = "No Matching Faculty Found";
                emptyMessage.textContent =
                    "Try changing your search or filter options.";
            }

            return;
        }

        facultyTable.style.display = "";
        emptyState.hidden = true;
    }

    // Render the faculty table using the current search and filters.
    function renderTable() {
        const search = searchInput
            ? searchInput.value.trim().toLowerCase()
            : "";

        const selectedRole = roleFilter ? roleFilter.value : "";
        const selectedSubject = subjectFilter ? subjectFilter.value : "";
        const selectedStatus = statusFilter ? statusFilter.value : "";

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

            const matchesStatus =
                !selectedStatus || record.status === selectedStatus;

            const matchesSubject =
                !selectedSubject ||
                record.assignments.some(
                    (item) => item.subject === selectedSubject,
                );

            return (
                matchesSearch && matchesRole && matchesStatus && matchesSubject
            );
        });

        tableBody.innerHTML = filteredRecords
            .map(
                (record) => `
                    <tr>
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
                                    title="View Faculty"
                                    aria-label="View ${escapeHTML(fullName(record))}">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <button
                                    type="button"
                                    class="faculty-action-btn edit"
                                    data-action="edit"
                                    data-id="${record.id}"
                                    title="Edit Faculty"
                                    aria-label="Edit ${escapeHTML(fullName(record))}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button
                                    type="button"
                                    class="faculty-action-btn toggle"
                                    data-action="toggle"
                                    data-id="${record.id}"
                                    title="${record.status === "Active" ? "Deactivate" : "Activate"} Faculty"
                                    aria-label="Toggle status for ${escapeHTML(fullName(record))}">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>

                                <button
                                    type="button"
                                    class="faculty-action-btn delete"
                                    data-action="delete"
                                    data-id="${record.id}"
                                    title="Delete Faculty"
                                    aria-label="Delete ${escapeHTML(fullName(record))}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `,
            )
            .join("");

        // Important: update empty state after rendering rows.
        updateEmptyState(filteredRecords);
        updateSummaryCards();
    }

    // Create one teaching assignment row.
    function createAssignmentRow(assignment = {}) {
        if (!assignmentsList) return;

        const row = document.createElement("div");
        row.className = "faculty-assignment-row";

        const gradeGroup = document.createElement("div");
        gradeGroup.className = "faculty-form-group";

        const gradeLabel = document.createElement("label");
        gradeLabel.textContent = "Grade Level *";

        const gradeSelect = document.createElement("select");
        gradeSelect.className = "assignment-grade";
        gradeSelect.required = true;

        gradeSelect.innerHTML =
            '<option value="">Select Grade</option>' +
            grades
                .map(
                    (grade) =>
                        `<option value="${escapeHTML(grade)}">${escapeHTML(grade)}</option>`,
                )
                .join("");

        gradeSelect.value = assignment.grade || "";

        gradeGroup.append(gradeLabel, gradeSelect);

        const subjectGroup = document.createElement("div");
        subjectGroup.className = "faculty-form-group";

        const subjectLabel = document.createElement("label");
        subjectLabel.textContent = "Subject *";

        const subjectSelect = document.createElement("select");
        subjectSelect.className = "assignment-subject";
        subjectSelect.required = true;

        subjectSelect.innerHTML =
            '<option value="">Select Subject</option>' +
            subjects
                .map(
                    (subject) =>
                        `<option value="${escapeHTML(subject)}">${escapeHTML(subject)}</option>`,
                )
                .join("");

        subjectSelect.value = assignment.subject || "";

        subjectGroup.append(subjectLabel, subjectSelect);

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "faculty-remove-row-btn";
        removeButton.title = "Remove assignment";
        removeButton.setAttribute("aria-label", "Remove assignment");
        removeButton.innerHTML = '<i class="fa-solid fa-trash"></i>';

        removeButton.addEventListener("click", () => {
            row.remove();
            updateResponsibilityFields();
        });

        row.append(gradeGroup, subjectGroup, removeButton);
        assignmentsList.appendChild(row);

        updateResponsibilityFields();
    }

    // Show or hide fields based on the selected responsibilities.

    function updateResponsibilityFields() {
    if (teachingSection && teacherCheckbox) {
        teachingSection.hidden = !teacherCheckbox.checked;
    }

    if (advisorySection && adviserCheckbox) {
        advisorySection.hidden = !adviserCheckbox.checked;
    }

    if (assignmentsList && teacherCheckbox) {
        assignmentsList.querySelectorAll("select").forEach((select) => {
            select.required = teacherCheckbox.checked;
        });
    }

    const advisoryGrade = document.getElementById("advisoryGrade");

    if (advisoryGrade && adviserCheckbox) {
        advisoryGrade.required = adviserCheckbox.checked;
    }
}


    // Reset the Add/Edit form.
    function resetForm() {
        form.reset();
        editingRecordId = null;

        if (assignmentsList) {
            assignmentsList.innerHTML = "";
        }

        document.getElementById("facultyFormTitle").textContent = "Add Faculty";

        document.getElementById("facultyFormDescription").textContent =
            "Enter the faculty information and assignments.";

        document.getElementById("saveFacultyText").textContent = "Save Faculty";

        teacherCheckbox.checked = true;
        adviserCheckbox.checked = false;

        createAssignmentRow();
        updateResponsibilityFields();
    }

    function openAddForm() {
        resetForm();
        openModal(formModal);
    }

    // Open an existing record for editing.
    function openEditForm(id) {
        const record = facultyRecords.find((item) => item.id === id);

        if (!record) return;

        resetForm();
        editingRecordId = id;

        document.getElementById("facultyId").value = record.facultyId;
        document.getElementById("facultyEmail").value = record.email;
        document.getElementById("facultyFirstName").value = record.firstName;
        document.getElementById("facultyMiddleName").value = record.middleName;
        document.getElementById("facultyLastName").value = record.lastName;
        document.getElementById("facultyStatus").value = record.status;

        teacherCheckbox.checked = record.teacher;
        adviserCheckbox.checked = record.adviser;

        assignmentsList.innerHTML = "";

        if (record.assignments.length) {
            record.assignments.forEach(createAssignmentRow);
        } else {
            createAssignmentRow();
        }

        document.getElementById("facultyFormTitle").textContent =
            "Edit Faculty";

        document.getElementById("facultyFormDescription").textContent =
            "Update the faculty information and assignments.";

        document.getElementById("saveFacultyText").textContent = "Save Changes";

        updateResponsibilityFields();
        openModal(formModal);
    }

    // View faculty details.
    function openViewModal(id) {
        const record = facultyRecords.find((item) => item.id === id);

        if (!record) return;

        const assignmentHTML = record.assignments.length
            ? record.assignments
                  .map(
                      (item) => `
                        <div class="faculty-detail-assignment">
                            <strong>${escapeHTML(item.grade)}</strong>
                            <span>${escapeHTML(item.subject)}</span>
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
                        ${escapeHTML(
                            record.adviser
                                ? record.advisoryGrade || "Not assigned"
                                : "Not applicable",
                        )}
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

    // Collect teaching assignments from the form.
    function collectAssignments() {
        if (!teacherCheckbox.checked) return [];

        const rows = [
            ...assignmentsList.querySelectorAll(".faculty-assignment-row"),
        ];

        return rows.map((row) => ({
            grade: row.querySelector(".assignment-grade").value,
            subject: row.querySelector(".assignment-subject").value,
        }));
    }

    // Validate teaching assignments.
    function validateAssignments(assignments) {
        if (teacherCheckbox.checked && assignments.length === 0) {
            alert("Please add at least one teaching assignment.");
            return false;
        }

        if (
            teacherCheckbox.checked &&
            assignments.some((item) => !item.grade || !item.subject)
        ) {
            alert("Please complete every teaching assignment.");
            return false;
        }

        const uniqueAssignments = new Set(
            assignments.map((item) => `${item.grade}|${item.subject}`),
        );

        if (uniqueAssignments.size !== assignments.length) {
            alert(
                "Duplicate Grade Level and Subject assignments are not allowed.",
            );
            return false;
        }

        return true;
    }

    // Open Add Faculty using either Add button.
    const addFacultyBtn = document.getElementById("addFacultyBtn");
    const emptyAddFacultyBtn = document.getElementById("emptyAddFacultyBtn");

    if (addFacultyBtn) {
        addFacultyBtn.addEventListener("click", openAddForm);
    }

    if (emptyAddFacultyBtn) {
        emptyAddFacultyBtn.addEventListener("click", openAddForm);
    }

    // Add another teaching assignment.
    const addAssignmentBtn = document.getElementById("addAssignmentBtn");

    if (addAssignmentBtn) {
        addAssignmentBtn.addEventListener("click", () => {
            createAssignmentRow();
        });
    }

    teacherCheckbox.addEventListener("change", updateResponsibilityFields);
    adviserCheckbox.addEventListener("change", updateResponsibilityFields);

    // Save new or edited demo record.

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!teacherCheckbox.checked && !adviserCheckbox.checked) {
            alert("Please select Teacher, Adviser, or both.");
            return;
        }

        const assignments = collectAssignments();

        if (!validateAssignments(assignments)) return;

        const facultyId = document.getElementById("facultyId").value.trim();
        const email = document.getElementById("facultyEmail").value.trim();
        const advisoryGrade = document.getElementById("advisoryGrade").value;

        if (adviserCheckbox.checked && !advisoryGrade) {
            alert("Please select the advisory grade level.");
            document.getElementById("advisoryGrade").focus();
            return;
        }

        const recordData = {
            faculty_id: facultyId,
            first_name: document.getElementById("facultyFirstName").value.trim(),
            middle_name: document.getElementById("facultyMiddleName").value.trim(),
            last_name: document.getElementById("facultyLastName").value.trim(),
            email: email,
            status: document.getElementById("facultyStatus").value.toLowerCase(),
            teacher: teacherCheckbox.checked,
            adviser: adviserCheckbox.checked,
            assignments: teacherCheckbox.checked
                ? assignments.map((item) => ({
                    grade_level: item.grade,
                    subject: item.subject,
                }))
                : [],
            advisory_grade: adviserCheckbox.checked ? advisoryGrade : "",
            school_year: "",
        };

        const isEditing = editingRecordId !== null;
        const url = isEditing
            ? `/admin/faculty-management/${editingRecordId}`
            : "/admin/faculty-management";

        const method = isEditing ? "PUT" : "POST";

        const csrfToken = form.querySelector('input[name="_token"]')?.value;

        if (!csrfToken) {
            alert("CSRF token not found. Check that @csrf is inside the form.");
            return;
        }

        const saveButton = form.querySelector(
            'button[type="submit"], input[type="submit"]'
        );

        if (saveButton) saveButton.disabled = true;

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify(recordData),
            });

            const result = await response.json();

            if (!response.ok) {
                const validationMessages = result.errors
                    ? Object.values(result.errors).flat().join("\n")
                    : result.message || "Unable to save faculty.";

                alert(validationMessages);
                return;
            }

            alert(result.message || "Faculty saved successfully.");

            closeModal(formModal);
            window.location.reload();
        } catch (error) {
            console.error("Faculty save error:", error);
            alert("Unable to connect to the server. Check the browser console and Laravel log.");
        } finally {
            if (saveButton) saveButton.disabled = false;
        }
    });

    // Handle View, Edit, Toggle, and Delete buttons.
    tableBody.addEventListener("click", (event) => {
        const button = event.target.closest("button[data-action]");

        if (!button) return;

        const id = Number(button.dataset.id);
        const action = button.dataset.action;

        console.log("Faculty action:", action, "ID:", id);

        if (action === "view") {
            openViewModal(id);
        } else if (action === "edit") {
            openEditForm(id);
        } else if (action === "delete") {
            deletingRecordId = id;
            openModal(deleteModal);
        } else if (action === "toggle") {
            toggleFacultyStatus(id);
        }
    });

    // Confirm deletion.
    const confirmDeleteBtn = document.getElementById("confirmDeleteFaculty");

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener("click", async () => {
            if (deletingRecordId === null) return;

            const id = deletingRecordId;
            const csrfToken = form.querySelector('input[name="_token"]')?.value;

            if (!csrfToken) {
                alert("CSRF token not found. Please check the form's @csrf.");
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
                        },
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    alert(result.message || "Unable to delete faculty.");
                    return;
                }

                // Remove from the visible list only after database deletion succeeds.
                facultyRecords = facultyRecords.filter(
                    (record) => Number(record.id) !== Number(id)
                );

                deletingRecordId = null;
                closeModal(deleteModal);
                renderTable();

                alert(result.message || "Faculty deleted successfully.");
            } catch (error) {
                console.error("Faculty delete error:", error);
                alert("Unable to connect to the server. The faculty record was not confirmed deleted.");
            } finally {
                confirmDeleteBtn.disabled = false;
            }
        });
    }


    // Search and filters.
    if (searchInput) {
        searchInput.addEventListener("input", renderTable);
    }

    if (roleFilter) {
        roleFilter.addEventListener("change", renderTable);
    }

    if (subjectFilter) {
        subjectFilter.addEventListener("change", renderTable);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", renderTable);
    }

    // Close modal using close buttons.
    document.querySelectorAll("[data-close-modal]").forEach((button) => {
        button.addEventListener("click", () => {
            const modal = button.closest(".faculty-modal-overlay");

            if (modal) {
                closeModal(modal);
            }
        });
    });

    // Close modal when clicking the dark overlay.
    [formModal, viewModal, deleteModal].forEach((modal) => {
        if (!modal) return;

        modal.addEventListener("click", (event) => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Escape key closes open modals.
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeAllModals();
        }
    });

    // Initial page render.
    resetForm();
    renderTable();
});
