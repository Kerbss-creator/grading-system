document.addEventListener("DOMContentLoaded", function () {
    // =====================================================
    // ROUTES
    // =====================================================

    const routes = window.userManagementRoutes || {};

    // =====================================================
    // DOM ELEMENTS
    // =====================================================

    const userTableBody = document.getElementById("userTableBody");
    const searchInput = document.getElementById("searchInput");
    const roleFilter = document.getElementById("roleFilter");
    const statusFilter = document.getElementById("statusFilter");

    const userModal = document.getElementById("userModal");
    const userForm = document.getElementById("userForm");
    const profileType = document.getElementById("profileType");

    const studentFields = document.getElementById("studentFields");
    const facultyFields = document.getElementById("facultyFields");

    const isTeacher = document.getElementById("isTeacher");
    const isAdviser = document.getElementById("isAdviser");

    const teachingAssignmentSection = document.getElementById(
        "teachingAssignmentSection",
    );
    const advisoryAssignmentSection = document.getElementById(
        "advisoryAssignmentSection",
    );
    const teachingAssignmentsContainer = document.getElementById(
        "teachingAssignmentsContainer",
    );

    const saveUserBtn = document.getElementById("saveUserBtn");
    const saveUserText = document.getElementById("saveUserText");

    const viewUserModal = document.getElementById("viewUserModal");
    const viewUserDetails = document.getElementById("viewUserDetails");

    const statusModal = document.getElementById("deleteUserModal");
    const resetPasswordModal = document.getElementById("resetPasswordModal");

    // =====================================================
    // STATE
    // =====================================================

    let users = [];
    let selectedUserId = null;
    let pendingStatus = null;
    let editingUserId = null;

    // Temporary placeholder until the controller includes
    // administrator accounts in the returned user collection.
    const ADMIN_ACCOUNT_COUNT = 1;

    // =====================================================
    // HELPERS
    // =====================================================

    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, function (char) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            }[char];
        });
    }

    function normalizeRole(role) {
        return String(role || "")
            .toLowerCase()
            .trim();
    }

    function roleLabel(role) {
        const labels = {
            student: "Student",
            teacher: "Teacher",
            adviser: "Adviser",
            teacher_adviser: "Teacher + Adviser",
            admin: "Administrator",
        };

        return labels[normalizeRole(role)] || "Unknown";
    }

    function statusLabel(status) {
        const labels = {
            active: "Active",
            pending: "Pending",
            inactive: "Inactive",
        };

        return labels[String(status || "").toLowerCase()] || "Unknown";
    }

    function getStatusClass(status) {
        const value = String(status || "").toLowerCase();

        if (["active", "pending", "inactive"].includes(value)) {
            return value;
        }

        return "inactive";
    }

    function getInitials(name) {
        return String(name || "User")
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join("");
    }

    function getCsrfToken() {
        return (
            document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="_token"]')?.value ||
            ""
        );
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": getCsrfToken(),
                ...(options.headers || {}),
            },
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            let message = data.message || "Something went wrong.";

            if (data.errors) {
                message = Object.values(data.errors).flat().join("\n");
            }

            throw new Error(message);
        }

        return data;
    }

    function showModal(modal) {
        if (!modal) return;

        modal.classList.add("active");
        modal.style.display = "flex";
    }

    function hideModal(modal) {
        if (!modal) return;

        modal.classList.remove("active");
        modal.style.display = "none";
    }

    function notify(message) {
        alert(message);
    }

    function setLoading(button, loading, originalText = "Save") {
        if (!button) return;

        button.disabled = loading;

        if (button === saveUserBtn && saveUserText) {
            saveUserText.textContent = loading ? "Saving..." : originalText;
        }
    }

    function replaceUserId(url, id) {
        return url.replace("__ID__", encodeURIComponent(id));
    }

    // =====================================================
    // FORM FIELD HELPERS
    // =====================================================

    function setSectionVisibility(section, visible) {
        if (!section) return;

        section.hidden = !visible;

        section.querySelectorAll("input, select, textarea").forEach((field) => {
            field.disabled = !visible;

            if (field.dataset.required === "true") {
                field.required = visible;
            }
        });
    }

    function configureRequiredFields() {
        document
            .querySelectorAll("#userForm [data-required]")
            .forEach((field) => {
                field.required = false;
            });

        if (profileType.value === "student") {
            [
                "studentId",
                "studentSex",
                "studentGradeLevel",
                "studentSchoolYear",
            ].forEach((id) => {
                const field = document.getElementById(id);
                if (field) field.required = true;
            });
        }

        if (profileType.value === "faculty") {
            const facultyId = document.getElementById("facultyId");
            if (facultyId) facultyId.required = true;
        }
    }

    function updateProfileFields() {
        const type = profileType.value;

        setSectionVisibility(studentFields, type === "student");
        setSectionVisibility(facultyFields, type === "faculty");

        if (type !== "faculty") {
            if (isTeacher) isTeacher.checked = false;
            if (isAdviser) isAdviser.checked = false;
        }

        updateFacultyResponsibilities();
        configureRequiredFields();
    }

    function updateFacultyResponsibilities() {
        const facultySelected = profileType.value === "faculty";
        const teacherSelected = facultySelected && isTeacher?.checked;
        const adviserSelected = facultySelected && isAdviser?.checked;

        setSectionVisibility(teachingAssignmentSection, teacherSelected);
        setSectionVisibility(advisoryAssignmentSection, adviserSelected);
    }

    // =====================================================
    // TEACHING ASSIGNMENTS
    // =====================================================

    function createTeachingAssignmentRow() {
        const row = document.createElement("div");
        row.className = "teaching-assignment-row";

        row.innerHTML = `
            <div class="form-group">
                <label>Grade Level <span class="required">*</span></label>
                <select class="assignment-grade" required>
                    <option value="">Select grade level</option>
                    <option value="Grade 7">Grade 7</option>
                    <option value="Grade 8">Grade 8</option>
                    <option value="Grade 9">Grade 9</option>
                    <option value="Grade 10">Grade 10</option>
                </select>
            </div>

            <div class="form-group">
                <label>Subject <span class="required">*</span></label>
                <select class="assignment-subject" required>
                    <option value="">Select subject</option>
                    <option value="Filipino">Filipino</option>
                    <option value="English">English</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Science">Science</option>
                    <option value="Araling Panlipunan">Araling Panlipunan</option>
                    <option value="TLE">TLE</option>
                    <option value="Values Education">Values Education</option>
                    <option value="MAPEH">MAPEH</option>
                    <option value="Computer Education">Computer Education</option>
                </select>
            </div>

            <button type="button" class="btn-remove-assignment" title="Remove assignment">
                <i class="fas fa-trash"></i>
            </button>
        `;

        row.querySelector(".btn-remove-assignment").addEventListener(
            "click",
            function () {
                row.remove();
            },
        );

        teachingAssignmentsContainer.appendChild(row);
    }

    function getTeachingAssignments() {
        return Array.from(
            teachingAssignmentsContainer.querySelectorAll(
                ".teaching-assignment-row",
            ),
        ).map((row) => ({
            grade_level: row.querySelector(".assignment-grade").value,
            subject: row.querySelector(".assignment-subject").value,
        }));
    }

    // =====================================================
    // RESET AND OPEN FORM
    // =====================================================

    function resetUserForm() {
        userForm.reset();

        editingUserId = null;

        const userId = document.getElementById("userId");
        if (userId) userId.value = "";

        // Allow profile type selection again for Add User.
        profileType.disabled = false;

        teachingAssignmentsContainer.innerHTML = "";

        setSectionVisibility(studentFields, false);
        setSectionVisibility(facultyFields, false);
        setSectionVisibility(teachingAssignmentSection, false);
        setSectionVisibility(advisoryAssignmentSection, false);

        if (saveUserText) {
            saveUserText.textContent = "Create Account";
        }

        const title = document.getElementById("userModalTitle");
        const description = document.getElementById("userModalDescription");

        if (title) title.textContent = "Create New Account";

        if (description) {
            description.textContent =
                "Create a system account and its corresponding profile.";
        }

        const setupNotice = document.getElementById("setupNotice");
        if (setupNotice) setupNotice.hidden = false;

        configureRequiredFields();
    }

    function openCreateModal() {
        resetUserForm();
        updateProfileFields();
        showModal(userModal);
    }

    function openEditModal(user) {
        resetUserForm();

        editingUserId = user.id;

        const userId = document.getElementById("userId");
        if (userId) userId.value = user.id;

        // Common information
        profileType.value = user.profile_type || "";

        document.getElementById("firstName").value = user.first_name || "";
        document.getElementById("middleName").value = user.middle_name || "";
        document.getElementById("lastName").value = user.last_name || "";
        document.getElementById("email").value = user.email || "";

        // Prevent changing Student into Faculty or vice versa.
        profileType.disabled = true;

        updateProfileFields();

        if (user.profile_type === "student") {
            document.getElementById("studentId").value = user.student_id || "";

            document.getElementById("studentSex").value = user.sex || "";

            document.getElementById("studentGradeLevel").value =
                user.grade_level || "";

            document.getElementById("studentSection").value =
                user.section || "";

            document.getElementById("studentSchoolYear").value =
                user.school_year || "";
        }

        if (user.profile_type === "faculty") {
            document.getElementById("facultyId").value = user.faculty_id || "";

            isTeacher.checked = Boolean(user.is_teacher);
            isAdviser.checked = Boolean(user.is_adviser);

            updateFacultyResponsibilities();

            // Restore existing teaching assignments.
            teachingAssignmentsContainer.innerHTML = "";

            if (Array.isArray(user.assignments)) {
                user.assignments.forEach((assignment) => {
                    createTeachingAssignmentRow();

                    const rows = teachingAssignmentsContainer.querySelectorAll(
                        ".teaching-assignment-row",
                    );

                    const row = rows[rows.length - 1];

                    row.querySelector(".assignment-grade").value =
                        assignment.grade_level || "";

                    row.querySelector(".assignment-subject").value =
                        assignment.subject || "";
                });
            }

            // Restore the latest advisory assignment.
            document.getElementById("advisoryGrade").value =
                user.advisory_grade || "";

            document.getElementById("advisorySchoolYear").value =
                user.advisory_school_year || "";
        }

        const title = document.getElementById("userModalTitle");
        const description = document.getElementById("userModalDescription");

        if (title) title.textContent = "Edit User Account";

        if (description) {
            description.textContent =
                "Update the user's account information and profile details.";
        }

        if (saveUserText) {
            saveUserText.textContent = "Update Account";
        }

        // Hide the account setup notice during editing.
        const setupNotice = document.getElementById("setupNotice");
        if (setupNotice) setupNotice.hidden = true;

        configureRequiredFields();

        showModal(userModal);
    }

    // =====================================================
    // ACCOUNT STATISTICS
    // =====================================================

    function updateStatistics() {
        const studentCount = users.filter(
            (user) => normalizeRole(user.role) === "student",
        ).length;

        const teacherCount = users.filter((user) =>
            ["teacher", "teacher_adviser"].includes(normalizeRole(user.role)),
        ).length;

        const adviserCount = users.filter((user) =>
            ["adviser", "teacher_adviser"].includes(normalizeRole(user.role)),
        ).length;

        const activeCount = users.filter(
            (user) => String(user.status).toLowerCase() === "active",
        ).length;

        const pendingCount = users.filter(
            (user) => String(user.status).toLowerCase() === "pending",
        ).length;

        const inactiveCount = users.filter(
            (user) => String(user.status).toLowerCase() === "inactive",
        ).length;

        document.getElementById("totalUsers").textContent =
            users.length + ADMIN_ACCOUNT_COUNT;
        document.getElementById("totalStudents").textContent = studentCount;
        document.getElementById("totalTeachers").textContent = teacherCount;
        document.getElementById("totalAdvisers").textContent = adviserCount;
        document.getElementById("totalAdmins").textContent =
            ADMIN_ACCOUNT_COUNT;
        document.getElementById("totalActive").textContent = activeCount;
        document.getElementById("totalPending").textContent = pendingCount;
        document.getElementById("totalInactive").textContent = inactiveCount;
    }

    // =====================================================
    // USER TABLE
    // =====================================================

    function renderUserRow(user) {
        const role = normalizeRole(user.role);
        const status = getStatusClass(user.status);

        const row = document.createElement("tr");

        // Role icons
        const roleIcons = {
            student: "fa-graduation-cap",
            teacher: "fa-book-open",
            adviser: "fa-user-check",
            teacher_adviser: "fa-users",
            admin: "fa-user-shield",
        };

        // Account status icons
        const statusIcons = {
            active: "fa-check-circle",
            inactive: "fa-times-circle",
            pending: "fa-clock",
        };

        row.innerHTML = `
        <td>
            <span class="user-id">
                ${escapeHTML(user.profile_identifier || "—")}
            </span>
        </td>

        <td>
            <div class="user-info-cell">
                <div class="user-avatar">
                    ${escapeHTML(getInitials(user.name))}
                </div>
                <div>
                    <strong>${escapeHTML(user.name)}</strong>
                </div>
            </div>
        </td>

        <td>${escapeHTML(user.email || "—")}</td>

        <td>
            <span class="role-badge ${escapeHTML(role)}">
                <i class="fas ${roleIcons[role] || "fa-user"}"></i>
                ${escapeHTML(roleLabel(role))}
            </span>
        </td>

        <td>
            <span class="status-badge ${status}">
                <i class="fas ${statusIcons[status] || "fa-question-circle"}"></i>
                ${escapeHTML(statusLabel(status))}
            </span>
        </td>

        <td>
            <div class="user-actions-menu">
                <button type="button"
                    class="btn-user-menu"
                    aria-label="User actions"
                    aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>

                <div class="user-dropdown-menu">
                    <button type="button" data-action="view">
                        <i class="fas fa-eye"></i> View Details
                    </button>

                    <button type="button" data-action="edit">
                        <i class="fas fa-edit"></i> Edit
                    </button>

                    <button type="button" data-action="reset">
                        <i class="fas fa-key"></i> Reset Password
                    </button>

                    <button type="button" data-action="role">
                        <i class="fas fa-user-tag"></i> Assigned Role
                    </button>

                    <button type="button" data-action="setup" disabled
                        title="Available in a future update">
                        <i class="fas fa-envelope"></i> Resend Setup Link
                        <small>Coming soon</small>
                    </button>

                    <div class="dropdown-divider"></div>

                    <button type="button" data-action="status"
                        class="${status === "active" ? "danger-action" : "success-action"}">
                        <i class="fas ${status === "active" ? "fa-user-slash" : "fa-user-check"}"></i>
                        ${status === "active" ? "Disable Account" : "Activate Account"}
                    </button>
                </div>
            </div>
        </td>
    `;

        const menuButton = row.querySelector(".btn-user-menu");
        const dropdown = row.querySelector(".user-dropdown-menu");

        menuButton.addEventListener("click", function (event) {
            event.stopPropagation();

            document
                .querySelectorAll(".user-dropdown-menu.open")
                .forEach((menu) => {
                    if (menu !== dropdown) {
                        menu.classList.remove("open");
                        menu.previousElementSibling?.setAttribute(
                            "aria-expanded",
                            "false",
                        );
                    }
                });

            dropdown.classList.toggle("open");

            menuButton.setAttribute(
                "aria-expanded",
                dropdown.classList.contains("open") ? "true" : "false",
            );
        });

        dropdown.querySelectorAll("[data-action]").forEach((button) => {
            button.addEventListener("click", function () {
                dropdown.classList.remove("open");
                menuButton.setAttribute("aria-expanded", "false");

                handleUserAction(button.dataset.action, user);
            });
        });

        return row;
    }

    function renderUsers() {
        if (!userTableBody) return;

        userTableBody.innerHTML = "";

        const filteredUsers = getFilteredUsers();

        if (filteredUsers.length === 0) {
            userTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-users"></i>
                        <h4>No Accounts Found</h4>
                        <p>Try changing your search or filter selection.</p>
                    </td>
                </tr>
            `;

            return;
        }

        filteredUsers.forEach((user) => {
            userTableBody.appendChild(renderUserRow(user));
        });
    }

    function getFilteredUsers() {
        const search = (searchInput?.value || "").toLowerCase().trim();
        const selectedRole = roleFilter?.value || "all";
        const selectedStatus = statusFilter?.value || "all";

        return users.filter((user) => {
            const matchesSearch = [
                user.name,
                user.email,
                user.profile_identifier,
            ].some((value) =>
                String(value || "")
                    .toLowerCase()
                    .includes(search),
            );
            const role = normalizeRole(user.role);
            const status = String(user.status || "").toLowerCase();

            const matchesRole = selectedRole === "all" || role === selectedRole;

            const matchesStatus =
                selectedStatus === "all" || status === selectedStatus;

            return matchesSearch && matchesRole && matchesStatus;
        });
    }

    // =====================================================
    // LOAD USERS
    // =====================================================

    async function loadUsers() {
        if (!routes.data) {
            console.error("User Management data route is missing.");
            return;
        }

        try {
            const data = await request(routes.data);

            users = Array.isArray(data.users) ? data.users : [];

            updateStatistics();
            renderUsers();
        } catch (error) {
            console.error("Unable to load users:", error);
            notify(error.message || "Unable to load user accounts.");
        }
    }

    // =====================================================
    // VIEW USER DETAILS
    // =====================================================

    function viewUser(user) {
        const role = normalizeRole(user.role);

        const details = [
            ["Full Name", user.name],
            ["Email Address", user.email],
            ["Username", user.username || "—"],
            ["Role", roleLabel(role)],
            ["Account Status", statusLabel(user.status)],
            ["Profile Type", user.profile_type || "—"],
            ["Profile ID", user.profile_identifier || "—"],
            ["Grade Level", user.grade_level || "—"],
            ["Section", user.section || "—"],
            ["School Year", user.school_year || "—"],
        ];

        viewUserDetails.innerHTML = `
            <div class="view-user-details-grid">
                ${details
                    .map(
                        ([label, value]) => `
                    <div class="view-user-detail-item">
                        <small>${escapeHTML(label)}</small>
                        <strong>${escapeHTML(value || "—")}</strong>
                    </div>
                `,
                    )
                    .join("")}
            </div>
        `;

        showModal(viewUserModal);
    }

    // =====================================================
    // ASSIGNED ROLE
    // =====================================================

    function showAssignedRole(user) {
        notify(
            `Assigned Role: ${roleLabel(user.role)}\n\n` +
                "Role changes are managed through the user's profile responsibilities.",
        );
    }

    // =====================================================
    // STATUS MODAL
    // =====================================================

    function openStatusModal(user) {
        selectedUserId = user.id;

        const currentlyActive = String(user.status).toLowerCase() === "active";
        pendingStatus = currentlyActive ? "inactive" : "active";

        document.getElementById("statusModalHeading").textContent =
            currentlyActive ? "Disable Account?" : "Activate Account?";

        document.getElementById("statusModalDescription").textContent =
            currentlyActive
                ? "Are you sure you want to disable this account?"
                : "Are you sure you want to activate this account?";

        document.getElementById("deleteUserName").textContent = user.name;

        document.getElementById("statusModalWarning").textContent =
            currentlyActive
                ? "The user will no longer be able to access the system."
                : "The user will be able to access the system if their profile is also active.";

        document.getElementById("confirmStatusText").textContent =
            currentlyActive ? "Disable Account" : "Activate Account";

        showModal(statusModal);
    }

    async function confirmStatusChange() {
        if (!selectedUserId || !routes.toggleStatus) return;

        const button = document.getElementById("confirmDeleteUser");
        const originalText =
            document.getElementById("confirmStatusText").textContent;

        button.disabled = true;
        document.getElementById("confirmStatusText").textContent =
            "Updating...";

        try {
            await request(replaceUserId(routes.toggleStatus, selectedUserId), {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    status: pendingStatus,
                }),
            });

            hideModal(statusModal);
            await loadUsers();

            notify("Account status updated successfully.");
        } catch (error) {
            notify(error.message || "Unable to update account status.");
        } finally {
            button.disabled = false;
            document.getElementById("confirmStatusText").textContent =
                originalText;
            selectedUserId = null;
            pendingStatus = null;
        }
    }

    // =====================================================
    // RESET PASSWORD
    // =====================================================

    function openResetPasswordModal(user) {
        selectedUserId = user.id;

        document.getElementById("resetUserName").textContent = user.name;
        document.getElementById("resetPasswordForm").reset();

        showModal(resetPasswordModal);
    }

    async function submitResetPassword(event) {
        event.preventDefault();

        if (!selectedUserId || !routes.resetPassword) return;

        const password = document.getElementById("newPassword").value;
        const confirmation =
            document.getElementById("confirmNewPassword").value;

        if (password.length < 8) {
            notify("Password must contain at least 8 characters.");
            return;
        }

        if (password !== confirmation) {
            notify("Password confirmation does not match.");
            return;
        }

        const button = document.getElementById("confirmResetPassword");
        button.disabled = true;
        button.textContent = "Updating...";

        try {
            await request(replaceUserId(routes.resetPassword, selectedUserId), {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    password,
                    password_confirmation: confirmation,
                }),
            });

            hideModal(resetPasswordModal);
            notify("Password reset successfully.");
        } catch (error) {
            notify(error.message || "Unable to reset password.");
        } finally {
            button.disabled = false;
            button.textContent = "Reset Password";
            selectedUserId = null;
        }
    }

    // =====================================================
    // USER ACTION HANDLER
    // =====================================================

    function handleUserAction(action, user) {
        switch (action) {
            case "view":
                viewUser(user);
                break;

            case "edit":
                openEditModal(user);
                break;

            case "reset":
                openResetPasswordModal(user);
                break;

            case "role":
                showAssignedRole(user);
                break;

            case "setup":
                notify(
                    "Account Setup Link will be available in a future update.",
                );
                break;

            case "status":
                openStatusModal(user);
                break;
        }
    }

    // =====================================================
    // CREATE ACCOUNT
    // =====================================================

    function collectFormData() {
        const type = profileType.value;

        const payload = {
            profile_type: type,
            first_name: document.getElementById("firstName").value.trim(),
            middle_name: document.getElementById("middleName").value.trim(),
            last_name: document.getElementById("lastName").value.trim(),
            email: document.getElementById("email").value.trim(),
        };

        if (type === "student") {
            payload.student_id = document
                .getElementById("studentId")
                .value.trim();
            payload.sex = document.getElementById("studentSex").value;
            payload.grade_level =
                document.getElementById("studentGradeLevel").value;
            payload.section = document
                .getElementById("studentSection")
                .value.trim();
            payload.school_year = document
                .getElementById("studentSchoolYear")
                .value.trim();
        }

        if (type === "faculty") {
            payload.faculty_id = document
                .getElementById("facultyId")
                .value.trim();
            payload.is_teacher = isTeacher.checked;
            payload.is_adviser = isAdviser.checked;

            payload.assignments = isTeacher.checked
                ? getTeachingAssignments()
                : [];

            payload.advisory_grade = isAdviser.checked
                ? document.getElementById("advisoryGrade").value
                : null;

            payload.advisory_school_year = isAdviser.checked
                ? document.getElementById("advisorySchoolYear").value.trim()
                : null;
        }

        return payload;
    }

    function validateForm(payload) {
        if (!payload.profile_type) {
            return "Please select a profile type.";
        }

        if (!payload.first_name || !payload.last_name) {
            return "First name and last name are required.";
        }

        if (payload.profile_type === "student") {
            if (
                !payload.student_id ||
                !payload.sex ||
                !payload.grade_level ||
                !payload.school_year
            ) {
                return "Please complete all required student information.";
            }
        }

        if (payload.profile_type === "faculty") {
            if (!payload.faculty_id) {
                return "Faculty ID is required.";
            }

            if (!payload.is_teacher && !payload.is_adviser) {
                return "Please select at least one faculty responsibility.";
            }

            if (payload.is_teacher) {
                if (payload.assignments.length === 0) {
                    return "Please add at least one teaching assignment.";
                }

                const incompleteAssignment = payload.assignments.some(
                    (assignment) =>
                        !assignment.grade_level || !assignment.subject,
                );

                if (incompleteAssignment) {
                    return "Please complete all teaching assignments.";
                }
            }

            if (payload.is_adviser) {
                if (!payload.advisory_grade || !payload.advisory_school_year) {
                    return "Please complete the advisory assignment.";
                }
            }
        }

        return null;
    }

    async function submitUserForm(event) {
        event.preventDefault();

        const isEditing = Boolean(editingUserId);

        const payload = collectFormData();
        const validationError = validateForm(payload);

        if (validationError) {
            notify(validationError);
            return;
        }

        let url = routes.store;
        let method = "POST";
        let buttonText = "Create Account";

        if (isEditing) {
            if (!routes.update) {
                notify("The account update route is missing.");
                return;
            }

            url = replaceUserId(routes.update, editingUserId);
            method = "PUT";
            buttonText = "Update Account";
        } else if (!routes.store) {
            notify("The account creation route is missing.");
            return;
        }

        setLoading(saveUserBtn, true, buttonText);

        try {
            const data = await request(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(payload),
            });

            hideModal(userModal);
            resetUserForm();

            await loadUsers();

            notify(
                data.message ||
                    (isEditing
                        ? "Account updated successfully."
                        : "Account created successfully."),
            );
        } catch (error) {
            notify(error.message || "Unable to save account.");
        } finally {
            setLoading(saveUserBtn, false, "Create Account");
        }
    }

    // =====================================================
    // EVENT LISTENERS
    // =====================================================

    document
        .getElementById("openAddUser")
        ?.addEventListener("click", openCreateModal);

    document.getElementById("closeUserModal")?.addEventListener("click", () => {
        hideModal(userModal);
    });

    document
        .getElementById("cancelUserModal")
        ?.addEventListener("click", () => {
            hideModal(userModal);
        });

    document
        .getElementById("closeViewUserModal")
        ?.addEventListener("click", () => {
            hideModal(viewUserModal);
        });

    document
        .getElementById("closeViewUserButton")
        ?.addEventListener("click", () => {
            hideModal(viewUserModal);
        });

    document
        .getElementById("closeStatusModal")
        ?.addEventListener("click", () => {
            hideModal(statusModal);
        });

    document
        .getElementById("cancelStatusModal")
        ?.addEventListener("click", () => {
            hideModal(statusModal);
        });

    document
        .getElementById("closeResetPasswordModal")
        ?.addEventListener("click", () => {
            hideModal(resetPasswordModal);
        });

    document
        .getElementById("cancelResetPassword")
        ?.addEventListener("click", () => {
            hideModal(resetPasswordModal);
        });

    document
        .getElementById("confirmDeleteUser")
        ?.addEventListener("click", confirmStatusChange);

    profileType?.addEventListener("change", updateProfileFields);

    isTeacher?.addEventListener("change", updateFacultyResponsibilities);
    isAdviser?.addEventListener("change", updateFacultyResponsibilities);

    document
        .getElementById("addTeachingAssignment")
        ?.addEventListener("click", () => {
            createTeachingAssignmentRow();
        });

    userForm?.addEventListener("submit", submitUserForm);

    document
        .getElementById("resetPasswordForm")
        ?.addEventListener("submit", submitResetPassword);

    searchInput?.addEventListener("input", renderUsers);
    roleFilter?.addEventListener("change", renderUsers);
    statusFilter?.addEventListener("change", renderUsers);

    // Close dropdowns when clicking outside.
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".user-actions-menu")) {
            document
                .querySelectorAll(".user-dropdown-menu.open")
                .forEach((menu) => {
                    menu.classList.remove("open");
                    menu.previousElementSibling?.setAttribute(
                        "aria-expanded",
                        "false",
                    );
                });
        }
    });

    // Close modals when clicking their backdrop.
    [userModal, viewUserModal, statusModal, resetPasswordModal].forEach(
        (modal) => {
            modal?.addEventListener("click", function (event) {
                if (event.target === modal) {
                    hideModal(modal);
                }
            });
        },
    );

    // =====================================================
    // INITIALIZATION
    // =====================================================

    loadUsers();
});
