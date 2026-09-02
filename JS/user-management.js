document.addEventListener("DOMContentLoaded", () => {
  // Elements
  const userModal = document.getElementById("userModal");
  const openAddUser = document.getElementById("openAddUser");
  const closeModal = document.getElementById("closeModal");
  const cancelForm = document.getElementById("cancelForm");

  const roleSelection = document.getElementById("roleSelection");
  const userForm = document.getElementById("userForm");
  const roleOptions = document.querySelectorAll(".role-option");

  const selectedRoleText =
    document.getElementById("selectedRoleText");

  const selectedRoleIcon =
    document.getElementById("selectedRoleIcon");

  const studentFields =
    document.getElementById("studentFields");

  const roleFilter =
    document.getElementById("roleFilter");

  const searchInput =
    document.getElementById("searchInput");

  const userTableBody =
    document.getElementById("userTableBody");

  const userFormElement =
    document.getElementById("userForm");

  // Open Modal
  openAddUser.addEventListener("click", () => {
    userModal.classList.add("show");

    roleSelection.classList.remove("hidden");
    userForm.classList.add("hidden");
  });

  // Close Modal
  function closeUserModal() {
    userModal.classList.remove("show");

    userFormElement.reset();

    roleSelection.classList.remove("hidden");
    userForm.classList.add("hidden");

    studentFields.style.display = "none";
  }

  closeModal.addEventListener("click", closeUserModal);
  cancelForm.addEventListener("click", closeUserModal);

  // Close When Clicking Outside
  userModal.addEventListener("click", (event) => {
    if (event.target === userModal) {
      closeUserModal();
    }
  });

  // Role Selection
  roleOptions.forEach((option) => {
    option.addEventListener("click", () => {
      const selectedRole = option.dataset.role;

      roleSelection.classList.add("hidden");
      userForm.classList.remove("hidden");

      // Student
      if (selectedRole === "student") {
        selectedRoleText.textContent = "Student";

        selectedRoleIcon.innerHTML =
          '<i class="fa-solid fa-user-graduate"></i>';

        studentFields.style.display = "block";
      }

      // Teacher
      if (selectedRole === "teacher") {
        selectedRoleText.textContent = "Teacher";

        selectedRoleIcon.innerHTML =
          '<i class="fa-solid fa-chalkboard-user"></i>';

        studentFields.style.display = "none";
      }
    });
  });

  // Search + Filter
  function filterUsers() {
    const searchValue =
      searchInput.value.toLowerCase().trim();

    const selectedRole = roleFilter.value;

    const rows =
      userTableBody.querySelectorAll("tr");

    rows.forEach((row) => {
      const rowText =
        row.textContent.toLowerCase();

      const roleBadge =
        row.querySelector(".role-badge");

      const rowRole = roleBadge
        ? roleBadge.textContent.trim()
        : "";

      const matchesSearch =
        rowText.includes(searchValue);

      const matchesRole =
        selectedRole === "all" ||
        rowRole === selectedRole;

      if (matchesSearch && matchesRole) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  }

  searchInput.addEventListener(
    "input",
    filterUsers
  );

  roleFilter.addEventListener(
    "change",
    filterUsers
  );

  // Create Account
  userFormElement.addEventListener(
    "submit",
    (event) => {
      event.preventDefault();

      const password =
        document.getElementById("password").value;

      const confirmPassword =
        document.getElementById("confirmPassword").value;

      if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
      }

      alert("User account created successfully.");

      closeUserModal();
    }
  );

  // Delete User
  const deleteButtons =
    document.querySelectorAll(
      ".action-btn.delete"
    );

  deleteButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const row = button.closest("tr");

      const userName =
        row.querySelector(
          ".user-name span"
        ).textContent;

      const confirmed = confirm(
        `Are you sure you want to delete ${userName}?`
      );

      if (confirmed) {
        row.remove();

        alert("User deleted successfully.");
      }
    });
  });

  // Reset Password
  const resetButtons =
    document.querySelectorAll(
      ".action-btn.reset"
    );

  resetButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const row = button.closest("tr");

      const userName =
        row.querySelector(
          ".user-name span"
        ).textContent;

      const confirmed = confirm(
        `Reset the password for ${userName}?`
      );

      if (confirmed) {
        alert(
          `Password reset for ${userName}.`
        );
      }
    });
  });
});
