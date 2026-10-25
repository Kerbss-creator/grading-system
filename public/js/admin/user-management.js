document.addEventListener("DOMContentLoaded", function () {
    // DOM Elements

    const userTableBody = document.getElementById("userTableBody");
    const roleFilter = document.getElementById("roleFilter");
    const searchInput = document.getElementById("searchInput");
    const tableResultCount = document.getElementById("tableResultCount");

    const userModal = document.getElementById("userModal");
    const userForm = document.getElementById("userForm");
    const roleSelection = document.getElementById("roleSelection");
    const modalTitle = document.getElementById("modalTitle");
    const modalDescription = document.getElementById("modalDescription");

    const openAddUser = document.getElementById("openAddUser");
    const emptyAddUser = document.getElementById("emptyAddUser");
    const closeModalButton = document.getElementById("closeModal");
    const cancelForm = document.getElementById("cancelForm");
    const changeRoleBtn = document.getElementById("changeRoleBtn");

    const selectedRoleInput = document.getElementById("selectedRole");
    const selectedRoleText = document.getElementById("selectedRoleText");
    const selectedRoleIcon = document.getElementById("selectedRoleIcon");

    const userIdInput = document.getElementById("userId");
    const firstNameInput = document.getElementById("firstName");
    const middleNameInput = document.getElementById("middleName");
    const lastNameInput = document.getElementById("lastName");
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("email");
    const accountStatusInput = document.getElementById("accountStatus");

    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const passwordFields = document.getElementById("passwordFields");
    const saveUserText = document.getElementById("saveUserText");

    const studentFields = document.getElementById("studentFields");
    const teacherFields = document.getElementById("teacherFields");
    const adviserFields = document.getElementById("adviserFields");
    const adminFields = document.getElementById("adminFields");

    const gradeLevelInput = document.getElementById("gradeLevel");
    const sectionInput = document.getElementById("section");
    const teacherDepartmentInput = document.getElementById("teacherDepartment");
    const adviserGradeLevelInput = document.getElementById("adviserGradeLevel");
    const adviserSectionInput = document.getElementById("adviserSection");

    const emptyStateRow = document.getElementById("emptyStateRow");
    const emptyStateTitle = document.getElementById("emptyStateTitle");
    const emptyStateMessage = document.getElementById("emptyStateMessage");

    const viewUserModal = document.getElementById("viewUserModal");
    const viewUserDetails = document.getElementById("viewUserDetails");

    const deleteUserModal = document.getElementById("deleteUserModal");
    const deleteUserName = document.getElementById("deleteUserName");
    const confirmDeleteUser = document.getElementById("confirmDeleteUser");

    const resetPasswordModal = document.getElementById("resetPasswordModal");
    const resetUserName = document.getElementById("resetUserName");
    const resetPasswordForm = document.getElementById("resetPasswordForm");
    const newPasswordInput = document.getElementById("newPassword");
    const confirmNewPasswordInput =
        document.getElementById("confirmNewPassword");

    const totalUsersElement = document.getElementById("totalUsers");
    const totalStudentsElement = document.getElementById("totalStudents");
    const totalTeachersElement = document.getElementById("totalTeachers");
    const totalAdvisersElement = document.getElementById("totalAdvisers");
    const totalAdminsElement = document.getElementById("totalAdmins");

    // State Variables

    let editingRow = null;
    let selectedDeleteRow = null;
    let selectedResetRow = null;

    const roleSettings = {
        Student: {
            icon: "fa-user-graduate",
            prefix: "STU",
            avatarClass: "student-avatar",
        },
        Teacher: {
            icon: "fa-chalkboard-user",
            prefix: "TCH",
            avatarClass: "teacher-avatar",
        },
        Adviser: {
            icon: "fa-user-tie",
            prefix: "ADV",
            avatarClass: "adviser-avatar",
        },
        Admin: {
            icon: "fa-user-shield",
            prefix: "ADM",
            avatarClass: "admin-avatar",
        },
    };

    // Utility Functions

    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, function (character) {
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

    function getFullName(data) {
        return [data.firstName, data.middleName, data.lastName]
            .filter(Boolean)
            .join(" ");
    }

    function getRowName(row) {
        return [
            row.dataset.firstName,
            row.dataset.middleName,
            row.dataset.lastName,
        ]
            .filter(Boolean)
            .join(" ");
    }

    function getUserRows() {
        if (!userTableBody) {
            return [];
        }

        return Array.from(userTableBody.querySelectorAll("tr[data-user-row]"));
    }

    function generateUserId(role) {
        const settings = roleSettings[role];

        if (!settings) {
            return "";
        }

        const existingIds = getUserRows().map(function (row) {
            return row.dataset.userId;
        });

        let number = 1;
        let generatedId = "";

        do {
            generatedId = settings.prefix + String(number).padStart(3, "0");
            number++;
        } while (existingIds.includes(generatedId));

        return generatedId;
    }

    function openModal(modal) {
        if (modal) {
            modal.classList.add("show");
            modal.setAttribute("aria-hidden", "false");
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.classList.remove("show");
            modal.setAttribute("aria-hidden", "true");
        }
    }

    function closeAllModals() {
        [userModal, viewUserModal, deleteUserModal, resetPasswordModal].forEach(
            closeModal,
        );
    }

    function setFieldGroupState(container, enabled) {
        if (!container) {
            return;
        }

        container.hidden = !enabled;

        container
            .querySelectorAll("input, select, textarea")
            .forEach(function (input) {
                input.disabled = !enabled;
            });
    }

    function setEmptyState(title, message) {
        if (emptyStateTitle) {
            emptyStateTitle.textContent = title;
        }

        if (emptyStateMessage) {
            emptyStateMessage.textContent = message;
        }
    }

    // Role Selection and Form Visibility

    function selectUserRole(role) {
        const settings = roleSettings[role];

        if (!settings) {
            return;
        }

        selectedRoleInput.value = role;
        selectedRoleText.textContent = role;

        selectedRoleIcon.className = "fa-solid " + settings.icon;

        document.querySelectorAll(".role-option").forEach(function (button) {
            const isSelected = button.dataset.role === role;

            button.classList.toggle("selected", isSelected);
            button.setAttribute("aria-pressed", String(isSelected));
        });

        // Hide role selection and show the account form
        if (roleSelection) {
            roleSelection.hidden = true;
        }

        userForm.classList.remove("hidden");

        // Show fields based on selected role
        setFieldGroupState(studentFields, role === "Student");
        setFieldGroupState(teacherFields, role === "Teacher");
        setFieldGroupState(adviserFields, role === "Adviser");
        setFieldGroupState(adminFields, role === "Admin");

        if (gradeLevelInput) {
            gradeLevelInput.required = role === "Student";
        }

        if (sectionInput) {
            sectionInput.required = role === "Student";
        }

        if (adviserGradeLevelInput) {
            adviserGradeLevelInput.required = role === "Adviser";
        }

        if (adviserSectionInput) {
            adviserSectionInput.required = role === "Adviser";
        }

        if (!editingRow && userIdInput && !userIdInput.value) {
            userIdInput.value = generateUserId(role);
        }
    }

    // Reset User Form

    function resetUserForm() {
        if (!userForm) {
            return;
        }

        userForm.reset();
        editingRow = null;
        userForm.dataset.mode = "add";

        if (modalTitle) {
            modalTitle.textContent = "Add New User";
        }

        if (modalDescription) {
            modalDescription.textContent = "Create a new system account.";
        }

        if (roleSelection) {
            roleSelection.hidden = false;
        }

        userForm.classList.add("hidden");

        if (selectedRoleInput) {
            selectedRoleInput.value = "";
        }

        if (selectedRoleText) {
            selectedRoleText.textContent = "Select Role";
        }

        if (selectedRoleIcon) {
            selectedRoleIcon.className = "fa-solid fa-user";
        }

        document.querySelectorAll(".role-option").forEach(function (button) {
            button.classList.remove("selected");
            button.setAttribute("aria-pressed", "false");
        });

        setFieldGroupState(studentFields, false);
        setFieldGroupState(teacherFields, false);
        setFieldGroupState(adviserFields, false);
        setFieldGroupState(adminFields, false);

        if (passwordFields) {
            passwordFields.hidden = false;
        }

        if (passwordInput) {
            passwordInput.disabled = false;
            passwordInput.required = true;
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.disabled = false;
            confirmPasswordInput.required = true;
        }

        if (userIdInput) {
            userIdInput.readOnly = true;
            userIdInput.value = "";
        }

        if (accountStatusInput) {
            accountStatusInput.value = "Active";
        }

        if (gradeLevelInput) {
            gradeLevelInput.required = false;
        }

        if (sectionInput) {
            sectionInput.required = false;
        }

        if (adviserGradeLevelInput) {
            adviserGradeLevelInput.required = false;
        }

        if (adviserSectionInput) {
            adviserSectionInput.required = false;
        }

        if (saveUserText) {
            saveUserText.textContent = "Save User";
        }
    }

    // Open and Close Add User Modal

    function showAddUserModal() {
        resetUserForm();
        openModal(userModal);
    }

    if (openAddUser) {
        openAddUser.addEventListener("click", showAddUserModal);
    }

    if (emptyAddUser) {
        emptyAddUser.addEventListener("click", showAddUserModal);
    }

    if (closeModalButton) {
        closeModalButton.addEventListener("click", function () {
            closeModal(userModal);
            resetUserForm();
        });
    }

    if (cancelForm) {
        cancelForm.addEventListener("click", function () {
            closeModal(userModal);
            resetUserForm();
        });
    }

    document.querySelectorAll(".role-option").forEach(function (button) {
        button.addEventListener("click", function () {
            selectUserRole(button.dataset.role);
        });
    });

    if (changeRoleBtn) {
        changeRoleBtn.addEventListener("click", function () {
            if (roleSelection) {
                roleSelection.hidden = false;
            }

            userForm.classList.add("hidden");
        });
    }

    // Close Modal Buttons

    document.querySelectorAll("[data-close-modal]").forEach(function (button) {
        button.addEventListener("click", function () {
            const modal = document.getElementById(button.dataset.closeModal);

            closeModal(modal);

            if (modal === userModal) {
                resetUserForm();
            }
        });
    });

    document.querySelectorAll(".modal-overlay").forEach(function (overlay) {
        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                closeModal(overlay);

                if (overlay === userModal) {
                    resetUserForm();
                }
            }
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllModals();
            resetUserForm();
        }
    });

    // Render User Row

    function renderRow(data, existingRow = null) {
        const row = existingRow || document.createElement("tr");
        const settings = roleSettings[data.role];
        const fullName = getFullName(data);

        const initials = (
            (data.firstName.charAt(0) || "") + (data.lastName.charAt(0) || "")
        ).toUpperCase();

        row.dataset.userRow = "true";
        row.dataset.userId = data.userId;
        row.dataset.role = data.role;
        row.dataset.firstName = data.firstName;
        row.dataset.middleName = data.middleName || "";
        row.dataset.lastName = data.lastName;
        row.dataset.username = data.username;
        row.dataset.email = data.email;
        row.dataset.status = data.status || "Active";
        row.dataset.gradeLevel = data.gradeLevel || "";
        row.dataset.section = data.section || "";
        row.dataset.department = data.department || "";
        row.dataset.adviserGradeLevel = data.adviserGradeLevel || "";
        row.dataset.adviserSection = data.adviserSection || "";

        row.innerHTML = `
            <td>
                <div class="user-name">
                    <div class="user-avatar ${settings.avatarClass}">
                        ${escapeHTML(initials)}
                    </div>
                    <span>${escapeHTML(fullName)}</span>
                </div>
                <small>${escapeHTML(data.userId)}</small>
            </td>
            <td>${escapeHTML(data.username)}</td>
            <td>${escapeHTML(data.email)}</td>
            <td>
                <span class="role-badge ${data.role.toLowerCase()}">
                    ${escapeHTML(data.role)}
                </span>
            </td>
            <td>
                <span class="status-badge ${data.status.toLowerCase()}">
                    ${escapeHTML(data.status)}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="action-btn view" data-action="view" title="View User">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button type="button" class="action-btn edit" data-action="edit" title="Edit User">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button type="button" class="action-btn reset" data-action="reset" title="Reset Password">
                        <i class="fa-solid fa-key"></i>
                    </button>
                    <button type="button" class="action-btn delete" data-action="delete" title="Delete User">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    // Update Statistics

    function updateStatistics() {
        const rows = getUserRows();

        const counts = {
            Student: 0,
            Teacher: 0,
            Adviser: 0,
            Admin: 0,
        };

        rows.forEach(function (row) {
            if (
                Object.prototype.hasOwnProperty.call(counts, row.dataset.role)
            ) {
                counts[row.dataset.role]++;
            }
        });

        if (totalUsersElement) totalUsersElement.textContent = rows.length;
        if (totalStudentsElement)
            totalStudentsElement.textContent = counts.Student;
        if (totalTeachersElement)
            totalTeachersElement.textContent = counts.Teacher;
        if (totalAdvisersElement)
            totalAdvisersElement.textContent = counts.Adviser;
        if (totalAdminsElement) totalAdminsElement.textContent = counts.Admin;
    }

    // Search, Filter, and Empty State

    function filterUsers() {
        const searchTerm = (searchInput?.value || "").trim().toLowerCase();
        const selectedRole = roleFilter?.value || "all";
        const rows = getUserRows();

        let visibleCount = 0;

        rows.forEach(function (row) {
            const rowText = [
                row.dataset.userId,
                row.dataset.firstName,
                row.dataset.middleName,
                row.dataset.lastName,
                row.dataset.username,
                row.dataset.email,
                row.dataset.role,
                row.dataset.status,
            ]
                .join(" ")
                .toLowerCase();

            const matchesSearch = rowText.includes(searchTerm);
            const matchesRole =
                selectedRole === "all" ||
                row.dataset.role.toLowerCase() === selectedRole.toLowerCase();

            row.hidden = !(matchesSearch && matchesRole);

            if (!row.hidden) {
                visibleCount++;
            }
        });

        if (tableResultCount) {
            tableResultCount.textContent = `Showing ${visibleCount} of ${rows.length} users`;
        }

        if (emptyStateRow) {
            emptyStateRow.hidden = visibleCount > 0;
        }

        if (visibleCount === 0) {
            if (rows.length === 0) {
                setEmptyState(
                    "No Users Yet",
                    "There are no user accounts available. Add your first user to get started.",
                );
            } else {
                setEmptyState(
                    "No Users Found",
                    "No users match your search or selected role. Try changing your filters.",
                );
            }
        }

        if (emptyAddUser) {
            emptyAddUser.hidden = rows.length > 0 && visibleCount > 0;
        }

        updateStatistics();
    }

    if (roleFilter) {
        roleFilter.addEventListener("change", filterUsers);
    }

    if (searchInput) {
        searchInput.addEventListener("input", filterUsers);
    }

    // Duplicate Checking

    function hasDuplicate(data) {
        return getUserRows().some(function (row) {
            if (row === editingRow) {
                return false;
            }

            return (
                row.dataset.userId.toLowerCase() ===
                    data.userId.toLowerCase() ||
                row.dataset.username.toLowerCase() ===
                    data.username.toLowerCase() ||
                row.dataset.email.toLowerCase() === data.email.toLowerCase()
            );
        });
    }

    // Get Form Data

    function getFormData() {
        return {
            userId: userIdInput.value.trim(),
            role: selectedRoleInput.value,
            firstName: firstNameInput.value.trim(),
            middleName: middleNameInput.value.trim(),
            lastName: lastNameInput.value.trim(),
            username: usernameInput.value.trim(),
            email: emailInput.value.trim(),
            status: accountStatusInput.value,
            gradeLevel: gradeLevelInput?.value || "",
            section: sectionInput?.value || "",
            department: teacherDepartmentInput?.value || "",
            adviserGradeLevel: adviserGradeLevelInput?.value || "",
            adviserSection: adviserSectionInput?.value || "",
        };
    }

    // Save New User or Update Existing User

    if (userForm) {
        userForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (!userForm.reportValidity()) {
                return;
            }

            const data = getFormData();
            const wasEditing = Boolean(editingRow);

            if (!data.role || !roleSettings[data.role]) {
                alert("Please select a user role.");
                return;
            }

            if (!wasEditing) {
                if (!passwordInput.value || !confirmPasswordInput.value) {
                    alert("Please enter and confirm the password.");
                    return;
                }

                if (passwordInput.value !== confirmPasswordInput.value) {
                    alert("Passwords do not match.");
                    confirmPasswordInput.focus();
                    return;
                }
            }

            if (hasDuplicate(data)) {
                alert("User ID, username, or email already exists.");
                return;
            }

            const row = renderRow(data, editingRow);

            if (!wasEditing) {
                userTableBody.appendChild(row);
            }

            closeModal(userModal);
            resetUserForm();
            filterUsers();

            alert(
                wasEditing
                    ? "User updated in this demo."
                    : "User added in this demo.",
            );
        });
    }

    // Edit User

    function editUser(row) {
        resetUserForm();
        editingRow = row;

        const data = {
            userId: row.dataset.userId,
            role: row.dataset.role,
            firstName: row.dataset.firstName,
            middleName: row.dataset.middleName,
            lastName: row.dataset.lastName,
            username: row.dataset.username,
            email: row.dataset.email,
            status: row.dataset.status,
            gradeLevel: row.dataset.gradeLevel,
            section: row.dataset.section,
            department: row.dataset.department,
            adviserGradeLevel: row.dataset.adviserGradeLevel,
            adviserSection: row.dataset.adviserSection,
        };

        modalTitle.textContent = "Edit User";
        modalDescription.textContent =
            "Update the selected user's account information.";

        selectUserRole(data.role);

        userIdInput.value = data.userId;
        firstNameInput.value = data.firstName;
        middleNameInput.value = data.middleName;
        lastNameInput.value = data.lastName;
        usernameInput.value = data.username;
        emailInput.value = data.email;
        accountStatusInput.value = data.status;

        if (gradeLevelInput) gradeLevelInput.value = data.gradeLevel;
        if (sectionInput) sectionInput.value = data.section;
        if (teacherDepartmentInput)
            teacherDepartmentInput.value = data.department;
        if (adviserGradeLevelInput)
            adviserGradeLevelInput.value = data.adviserGradeLevel;
        if (adviserSectionInput)
            adviserSectionInput.value = data.adviserSection;

        if (passwordFields) {
            passwordFields.hidden = true;
        }

        passwordInput.disabled = true;
        passwordInput.required = false;
        confirmPasswordInput.disabled = true;
        confirmPasswordInput.required = false;

        if (saveUserText) {
            saveUserText.textContent = "Save Changes";
        }

        openModal(userModal);
    }

    // View User

    function viewUser(row) {
        const data = {
            userId: row.dataset.userId,
            role: row.dataset.role,
            fullName: getRowName(row),
            username: row.dataset.username,
            email: row.dataset.email,
            status: row.dataset.status,
            gradeLevel: row.dataset.gradeLevel,
            section: row.dataset.section,
            department: row.dataset.department,
            adviserGradeLevel: row.dataset.adviserGradeLevel,
            adviserSection: row.dataset.adviserSection,
        };

        const details = [
            ["User ID", data.userId],
            ["Full Name", data.fullName],
            ["Username", data.username],
            ["Email Address", data.email],
            ["Role", data.role],
            ["Account Status", data.status],
        ];

        if (data.role === "Student") {
            details.push(
                ["Grade Level", data.gradeLevel || "Not assigned"],
                ["Section", data.section || "Not assigned"],
            );
        }

        if (data.role === "Teacher") {
            details.push(["Department", data.department || "Not specified"]);
        }

        if (data.role === "Adviser") {
            details.push(
                ["Advisory Grade", data.adviserGradeLevel || "Not assigned"],
                ["Advisory Section", data.adviserSection || "Not assigned"],
            );
        }

        viewUserDetails.innerHTML = details
            .map(function (item) {
                return `
                <div class="info-group">
                    <span>${escapeHTML(item[0])}</span>
                    <strong>${escapeHTML(item[1])}</strong>
                </div>
            `;
            })
            .join("");

        openModal(viewUserModal);
    }

    // Delete User

    function deleteUser(row) {
        selectedDeleteRow = row;

        if (deleteUserName) {
            deleteUserName.textContent = getRowName(row);
        }

        openModal(deleteUserModal);
    }

    if (confirmDeleteUser) {
        confirmDeleteUser.addEventListener("click", function () {
            if (!selectedDeleteRow) {
                return;
            }

            selectedDeleteRow.remove();
            selectedDeleteRow = null;

            closeModal(deleteUserModal);
            filterUsers();

            alert("User deleted in this demo.");
        });
    }

    // Reset Password

    function showResetPasswordModal(row) {
        selectedResetRow = row;

        if (resetUserName) {
            resetUserName.textContent = getRowName(row);
        }

        if (resetPasswordForm) {
            resetPasswordForm.reset();
        }

        openModal(resetPasswordModal);
    }

    if (resetPasswordForm) {
        resetPasswordForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (!resetPasswordForm.reportValidity()) {
                return;
            }

            if (newPasswordInput.value !== confirmNewPasswordInput.value) {
                alert("Passwords do not match.");
                confirmNewPasswordInput.focus();
                return;
            }

            closeModal(resetPasswordModal);
            resetPasswordForm.reset();
            selectedResetRow = null;

            alert(
                "Password reset preview completed. Connect Laravel to save the password.",
            );
        });
    }

    // Table Action Buttons

    if (userTableBody) {
        userTableBody.addEventListener("click", function (event) {
            const actionButton = event.target.closest("[data-action]");

            if (!actionButton) {
                return;
            }

            const row = actionButton.closest("tr[data-user-row]");

            if (!row) {
                return;
            }

            switch (actionButton.dataset.action) {
                case "view":
                    viewUser(row);
                    break;
                case "edit":
                    editUser(row);
                    break;
                case "delete":
                    deleteUser(row);
                    break;
                case "reset":
                    showResetPasswordModal(row);
                    break;
            }
        });
    }

    // Initial Page Setup

    filterUsers();
});
