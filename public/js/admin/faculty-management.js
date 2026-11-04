
document.addEventListener("DOMContentLoaded", () => {
    const csrfToken = document.querySelector(
        '#facultyForm input[name="_token"]'
    )?.value;

    const facultyForm = document.getElementById("facultyForm");
    const facultyFormModal = document.getElementById("facultyFormModal");
    const viewFacultyModal = document.getElementById("viewFacultyModal");
    const deleteFacultyModal = document.getElementById("deleteFacultyModal");

    const addFacultyBtn = document.getElementById("addFacultyBtn");
    const emptyAddFacultyBtn = document.getElementById("emptyAddFacultyBtn");
    const confirmDeleteBtn = document.getElementById("confirmDeleteFaculty");

    const facultySearch = document.getElementById("facultySearch");
    const roleFilter = document.getElementById("roleFilter");
    const subjectFilter = document.getElementById("subjectFilter");
    const statusFilter = document.getElementById("statusFilter");

    const tableBody = document.getElementById("facultyTableBody");

    let editingFacultyId = null;
    let deletingFacultyId = null;

    const facultyRecords = window.facultyData || [];

    // MODAL FUNCTIONS
    function openModal(modal) {
        if (!modal) return;

        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
    }

    document.querySelectorAll("[data-close-modal]").forEach(button => {
        button.addEventListener("click", () => {
            closeModal(facultyFormModal);
            closeModal(viewFacultyModal);
            closeModal(deleteFacultyModal);
        });
    });

    [facultyFormModal, viewFacultyModal, deleteFacultyModal].forEach(modal => {
        if (!modal) return;

        modal.addEventListener("click", event => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener("keydown", event => {
        if (event.key === "Escape") {
            closeModal(facultyFormModal);
            closeModal(viewFacultyModal);
            closeModal(deleteFacultyModal);
        }
    });

    // GET FACULTY RECORD
    function getFaculty(id) {
        return facultyRecords.find(
            record => String(record.id) === String(id)
        );
    }

    // RESET FORM
    function resetFacultyForm() {
        if (!facultyForm) return;

        facultyForm.reset();

        editingFacultyId = null;

        document.getElementById("facultyFormTitle").textContent =
            "Add Faculty";

        document.getElementById("facultyFormDescription").textContent =
            "Enter the faculty information and assignments.";

        document.getElementById("saveFacultyText").textContent =
            "Save Faculty";

        document.getElementById("facultyStatus").value = "Active";

        document.getElementById("responsibilityTeacher").checked = true;
        document.getElementById("responsibilityAdviser").checked = false;

        document.getElementById("teachingAssignmentsList").innerHTML = "";

        document.getElementById("advisoryGrade").value = "";

        updateResponsibilitySections();
    }

    // ADD FACULTY
    function openAddFaculty() {
        resetFacultyForm();

        const facultyIdInput = document.getElementById("facultyId");
        facultyIdInput.readOnly = false;

        openModal(facultyFormModal);
    }

    if (addFacultyBtn) {
        addFacultyBtn.addEventListener("click", openAddFaculty);
    }

    if (emptyAddFacultyBtn) {
        emptyAddFacultyBtn.addEventListener("click", openAddFaculty);
    }

    // RESPONSIBILITY SECTIONS
    function updateResponsibilitySections() {
        const teacher = document.getElementById("responsibilityTeacher").checked;
        const adviser = document.getElementById("responsibilityAdviser").checked;

        document.getElementById("teachingAssignmentsSection").hidden = !teacher;
        document.getElementById("advisoryAssignmentSection").hidden = !adviser;
    }

    document.getElementById("responsibilityTeacher")
        .addEventListener("change", updateResponsibilitySections);

    document.getElementById("responsibilityAdviser")
        .addEventListener("change", updateResponsibilitySections);

    // ASSIGNMENT ROW
    function createAssignmentRow(assignment = {}) {
        const row = document.createElement("div");
        row.className = "faculty-assignment-row";

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
            "Computer"
        ];

        row.innerHTML = `
            <select class="assignment-grade" required>
                <option value="">Select Grade</option>
                ${grades.map(grade => `
                    <option value="${grade}" ${assignment.grade === grade ? "selected" : ""}>
                        ${grade}
                    </option>
                `).join("")}
            </select>

            <select class="assignment-subject" required>
                <option value="">Select Subject</option>
                ${subjects.map(subject => `
                    <option value="${subject}" ${assignment.subject === subject ? "selected" : ""}>
                        ${subject}
                    </option>
                `).join("")}
            </select>

            <button type="button" class="faculty-remove-row-btn">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;

        row.querySelector(".faculty-remove-row-btn")
            .addEventListener("click", () => row.remove());

        return row;
    }

    const addAssignmentBtn = document.getElementById("addAssignmentBtn");
    const assignmentsList = document.getElementById("teachingAssignmentsList");

    if (addAssignmentBtn) {
        addAssignmentBtn.addEventListener("click", () => {
            assignmentsList.appendChild(createAssignmentRow());
        });
    }

    function collectAssignments() {
        return [...document.querySelectorAll(".faculty-assignment-row")]
            .map(row => ({
                grade_level: row.querySelector(".assignment-grade").value,
                subject: row.querySelector(".assignment-subject").value
            }));
    }

    // VIEW FACULTY
    window.viewTeacher = function(id) {
        const faculty = getFaculty(id);

        if (!faculty) {
            alert("Faculty record not found.");
            return;
        }

        const content = document.getElementById("viewFacultyContent");

        const role = faculty.teacher && faculty.adviser
            ? "Teacher + Adviser"
            : faculty.adviser
                ? "Adviser"
                : "Teacher";

        const assignments = (faculty.assignments || [])
            .map(item => `<p>${item.grade} - ${item.subject}</p>`)
            .join("");

        content.innerHTML = `
            <div class="faculty-view-details">
                <p><strong>Faculty ID:</strong> ${escapeHTML(faculty.facultyId)}</p>
                <p><strong>Name:</strong> ${escapeHTML(
                    `${faculty.firstName} ${faculty.middleName || ""} ${faculty.lastName}`
                )}</p>
                <p><strong>Email:</strong> ${escapeHTML(faculty.email)}</p>
                <p><strong>Role:</strong> ${role}</p>
                <p><strong>Status:</strong> ${escapeHTML(faculty.status)}</p>

                <h3>Teaching Assignments</h3>
                ${assignments || "<p>No teaching assignments.</p>"}

                <p><strong>Advisory Grade:</strong>
                    ${escapeHTML(faculty.advisoryGrade || "None")}
                </p>
            </div>
        `;

        openModal(viewFacultyModal);
    };

    // SAFE TEXT
    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, character => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        })[character]);
    }

    // EDIT FACULTY
    window.editFaculty = function(id) {
        const faculty = getFaculty(id);

        if (!faculty) {
            alert("Faculty record not found.");
            return;
        }

        resetFacultyForm();

        editingFacultyId = faculty.id;

        document.getElementById("facultyFormTitle").textContent =
            "Edit Faculty";

        document.getElementById("facultyFormDescription").textContent =
            "Update faculty information and assignments.";

        document.getElementById("saveFacultyText").textContent =
            "Update Faculty";

        document.getElementById("facultyId").value = faculty.facultyId || "";
        document.getElementById("facultyId").readOnly = true;

        document.getElementById("facultyEmail").value = faculty.email || "";
        document.getElementById("facultyFirstName").value = faculty.firstName || "";
        document.getElementById("facultyMiddleName").value = faculty.middleName || "";
        document.getElementById("facultyLastName").value = faculty.lastName || "";

        document.getElementById("facultyStatus").value =
            String(faculty.status).toLowerCase() === "active"
                ? "Active"
                : "Inactive";

        document.getElementById("responsibilityTeacher").checked = !!faculty.teacher;
        document.getElementById("responsibilityAdviser").checked = !!faculty.adviser;

        assignmentsList.innerHTML = "";

        (faculty.assignments || []).forEach(assignment => {
            assignmentsList.appendChild(createAssignmentRow(assignment));
        });

        document.getElementById("advisoryGrade").value =
            faculty.advisoryGrade || "";

        updateResponsibilitySections();
        openModal(facultyFormModal);
    };

    // SAVE / UPDATE FACULTY
    if (facultyForm) {
        facultyForm.addEventListener("submit", async event => {
            event.preventDefault();

            const teacher = document.getElementById("responsibilityTeacher").checked;
            const adviser = document.getElementById("responsibilityAdviser").checked;

            const payload = {
                faculty_id: document.getElementById("facultyId").value.trim(),
                first_name: document.getElementById("facultyFirstName").value.trim(),
                middle_name: document.getElementById("facultyMiddleName").value.trim(),
                last_name: document.getElementById("facultyLastName").value.trim(),
                email: document.getElementById("facultyEmail").value.trim(),
                status: document.getElementById("facultyStatus").value.toLowerCase(),
                teacher,
                adviser,
                assignments: teacher ? collectAssignments() : [],
                advisory_grade: adviser
                    ? document.getElementById("advisoryGrade").value
                    : "",
                school_year: window.currentSchoolYear || ""
            };

            if (!teacher && !adviser) {
                alert("Please select at least one responsibility.");
                return;
            }

            if (teacher && payload.assignments.length === 0) {
                alert("Please add at least one teaching assignment.");
                return;
            }

            if (adviser && !payload.advisory_grade) {
                alert("Please select an advisory grade level.");
                return;
            }

            const url = editingFacultyId
                ? `/admin/faculty-management/${editingFacultyId}`
                : "/admin/faculty-management";

            try {
                const response = await fetch(url, {
                    method: editingFacultyId ? "PUT" : "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok) {
                    const errors = data.errors
                        ? Object.values(data.errors).flat().join("\n")
                        : data.message || "Unable to save faculty.";

                    throw new Error(errors);
                }

                alert(data.message || "Faculty saved successfully.");
                window.location.reload();

            } catch (error) {
                console.error("Faculty Save Error:", error);
                alert(error.message);
            }
        });
    }

    // DELETE FACULTY
    window.deleteFaculty = function(id) {
        const faculty = getFaculty(id);

        if (!faculty) {
            alert("Faculty record not found.");
            return;
        }

        deletingFacultyId = id;

        document.getElementById("deleteFacultyMessage").textContent =
            `Are you sure you want to delete ${faculty.firstName} ${faculty.lastName}?`;

        openModal(deleteFacultyModal);
    };

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener("click", async () => {
            if (!deletingFacultyId) return;

            try {
                const response = await fetch(
                    `/admin/faculty-management/${deletingFacultyId}`,
                    {
                        method: "DELETE",
                        headers: {
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Unable to delete faculty.");
                }

                alert(data.message || "Faculty deleted successfully.");
                window.location.reload();

            } catch (error) {
                console.error("Delete Error:", error);
                alert(error.message);
            }
        });
    }

    // ACTIVATE / DEACTIVATE FACULTY
    window.toggleStatus = async function(id) {
        const faculty = getFaculty(id);

        if (!faculty) {
            alert("Faculty record not found.");
            return;
        }

        const isActive =
            String(faculty.status).toLowerCase() === "active";

        const action = isActive ? "deactivate" : "activate";

        if (!confirm(`Are you sure you want to ${action} this faculty?`)) {
            return;
        }

        try {
            const response = await fetch(
                `/admin/faculty-management/${id}/toggle-status`,
                {
                    method: "PATCH",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || "Unable to update faculty status.");
            }

            alert(`Faculty is now ${data.status}.`);
            window.location.reload();

        } catch (error) {
            console.error("Toggle Status Error:", error);
            alert(error.message);
        }
    };

    // SEARCH AND FILTER
    function filterFaculty() {
        const search = facultySearch?.value.toLowerCase().trim() || "";
        const selectedRole = roleFilter?.value.toLowerCase() || "";
        const selectedSubject = subjectFilter?.value || "";
        const selectedStatus = statusFilter?.value.toLowerCase() || "";

        tableBody?.querySelectorAll("tr").forEach(row => {
            const id = (row.dataset.facultyId || "").toLowerCase();
            const name = `${row.dataset.firstName || ""} ${row.dataset.lastName || ""}`.toLowerCase();
            const email = (row.dataset.email || "").toLowerCase();
            const role = (row.dataset.role || "").toLowerCase();
            const status = (row.dataset.status || "").toLowerCase();

            const assignments = getFaculty(row.dataset.id)?.assignments || [];

            const matchesSearch =
                !search ||
                id.includes(search) ||
                name.includes(search) ||
                email.includes(search);

            const matchesRole = !selectedRole || role === selectedRole.replace(" + ", "_").replace(" ", "_");

            const matchesSubject =
                !selectedSubject ||
                assignments.some(item => item.subject === selectedSubject);

            const matchesStatus = !selectedStatus || status === selectedStatus;

            row.style.display =
                matchesSearch && matchesRole && matchesSubject && matchesStatus
                    ? ""
                    : "none";
        });
    }

    [facultySearch, roleFilter, subjectFilter, statusFilter].forEach(input => {
        if (!input) return;

        input.addEventListener(
            input === facultySearch ? "input" : "change",
            filterFaculty
        );
    });
});
