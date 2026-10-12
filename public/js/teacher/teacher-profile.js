document.addEventListener("DOMContentLoaded", function () {
    // Profile modals

    const editProfileModal = document.getElementById("editProfileModal");
    const changePasswordModal = document.getElementById("changePasswordModal");

    const editProfileBtn = document.getElementById("editProfileBtn");
    const changePasswordBtn = document.getElementById("changePasswordBtn");

    const closeEditProfile = document.getElementById("closeEditProfile");
    const closeChangePassword = document.getElementById("closeChangePassword");

    const cancelEditProfile = document.getElementById("cancelEditProfile");
    const cancelChangePassword = document.getElementById(
        "cancelChangePassword",
    );

    const editProfileForm = document.getElementById("editProfileForm");
    const changePasswordForm = document.getElementById("changePasswordForm");

    // Open edit profile modal

    if (editProfileBtn) {
        editProfileBtn.addEventListener("click", function () {
            editProfileModal.classList.add("show");
            document.body.style.overflow = "hidden";
        });
    }

    // Open change password modal

    if (changePasswordBtn) {
        changePasswordBtn.addEventListener("click", function () {
            changePasswordModal.classList.add("show");
            document.body.style.overflow = "hidden";
        });
    }

    // Close edit profile modal

    function closeEditModal() {
        editProfileModal.classList.remove("show");
        document.body.style.overflow = "";
    }

    if (closeEditProfile) {
        closeEditProfile.addEventListener("click", closeEditModal);
    }

    if (cancelEditProfile) {
        cancelEditProfile.addEventListener("click", closeEditModal);
    }

    // Close change password modal

    function closePasswordModal() {
        changePasswordModal.classList.remove("show");
        document.body.style.overflow = "";
    }

    if (closeChangePassword) {
        closeChangePassword.addEventListener("click", closePasswordModal);
    }

    if (cancelChangePassword) {
        cancelChangePassword.addEventListener("click", closePasswordModal);
    }

    // Close modal when clicking outside

    if (editProfileModal) {
        editProfileModal.addEventListener("click", function (event) {
            if (event.target === editProfileModal) {
                closeEditModal();
            }
        });
    }

    if (changePasswordModal) {
        changePasswordModal.addEventListener("click", function (event) {
            if (event.target === changePasswordModal) {
                closePasswordModal();
            }
        });
    }

    // Close modals using Escape key

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            if (editProfileModal.classList.contains("show")) {
                closeEditModal();
            }

            if (changePasswordModal.classList.contains("show")) {
                closePasswordModal();
            }
        }
    });

    // Toggle password visibility

    const passwordToggles = document.querySelectorAll(".toggle-password");

    passwordToggles.forEach(function (toggle) {
        toggle.addEventListener("click", function () {
            const targetId = toggle.getAttribute("data-target");
            const passwordInput = document.getElementById(targetId);
            const icon = toggle.querySelector("i");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";

                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";

                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    });

    // Edit profile form

    if (editProfileForm) {
        editProfileForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const saveButton = editProfileForm.querySelector(".modal-btn.save");

            saveButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Saving...
            `;

            saveButton.disabled = true;

            setTimeout(function () {
                saveButton.innerHTML = `
                    <i class="fa-solid fa-check"></i>
                    Saved
                `;

                setTimeout(function () {
                    closeEditModal();

                    saveButton.innerHTML = `
                        <i class="fa-solid fa-check"></i>
                        Save Changes
                    `;

                    saveButton.disabled = false;
                }, 700);
            }, 700);
        });
    }

    // Change password form

    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword =
                document.getElementById("confirmPassword").value;

            if (newPassword !== confirmPassword) {
                alert("New password and confirmation password do not match.");
                return;
            }

            if (newPassword.length < 8) {
                alert("Password must contain at least 8 characters.");
                return;
            }

            const updateButton =
                changePasswordForm.querySelector(".modal-btn.save");

            updateButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Updating...
            `;

            updateButton.disabled = true;

            setTimeout(function () {
                updateButton.innerHTML = `
                    <i class="fa-solid fa-check"></i>
                    Updated
                `;

                setTimeout(function () {
                    changePasswordForm.reset();
                    closePasswordModal();

                    updateButton.innerHTML = `
                        <i class="fa-solid fa-key"></i>
                        Update Password
                    `;

                    updateButton.disabled = false;
                }, 700);
            }, 700);
        });
    }
});

