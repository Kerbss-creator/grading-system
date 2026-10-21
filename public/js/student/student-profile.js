document.addEventListener("DOMContentLoaded", function () {
    const editProfileModal = document.getElementById("editProfileModal");
    const changePasswordModal = document.getElementById("changePasswordModal");

    const editProfileBtn = document.getElementById("editProfileBtn");
    const changePasswordBtn = document.getElementById("changePasswordBtn");

    const editProfileForm = document.getElementById("editProfileForm");
    const changePasswordForm = document.getElementById("changePasswordForm");

    const toast = document.getElementById("studentProfileToast");

    let toastTimeout;
    let previousFocus = null;

    function showToast(message) {
        if (!toast) return;

        toast.textContent = message;
        toast.classList.add("show");

        clearTimeout(toastTimeout);

        toastTimeout = setTimeout(function () {
            toast.classList.remove("show");
        }, 3500);
    }

    function openModal(modal) {
        if (!modal) return;

        previousFocus = document.activeElement;

        modal.classList.add("active");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";

        const firstInput = modal.querySelector("input:not([disabled]), button");

        if (firstInput) {
            firstInput.focus();
        }
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("active");
        modal.setAttribute("aria-hidden", "true");

        const anotherModalOpen = document.querySelector(
            ".student-modal-overlay.active",
        );

        if (!anotherModalOpen) {
            document.body.style.overflow = "";
        }

        if (previousFocus && typeof previousFocus.focus === "function") {
            previousFocus.focus();
        }
    }

    // EDIT PROFILE MODAL
    if (editProfileBtn) {
        editProfileBtn.addEventListener("click", function () {
            openModal(editProfileModal);
        });
    }

    // CHANGE PASSWORD MODAL
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener("click", function () {
            openModal(changePasswordModal);
        });
    }

    // CLOSE BUTTONS
    const closeButtons = [
        ["closeEditProfile", editProfileModal],
        ["cancelEditProfile", editProfileModal],
        ["closeChangePassword", changePasswordModal],
        ["cancelChangePassword", changePasswordModal],
    ];

    closeButtons.forEach(function ([buttonId, modal]) {
        const button = document.getElementById(buttonId);

        if (button) {
            button.addEventListener("click", function () {
                closeModal(modal);
            });
        }
    });

    // CLOSE WHEN CLICKING OUTSIDE THE MODAL
    [editProfileModal, changePasswordModal].forEach(function (modal) {
        if (!modal) return;

        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    // CLOSE MODAL USING ESCAPE
    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        [editProfileModal, changePasswordModal].forEach(function (modal) {
            if (modal && modal.classList.contains("active")) {
                closeModal(modal);
            }
        });
    });

    // TOGGLE PASSWORD VISIBILITY
    document
        .querySelectorAll(".toggle-student-password")
        .forEach(function (button) {
            button.addEventListener("click", function () {
                const targetId = button.dataset.target;
                const passwordInput = document.getElementById(targetId);

                if (!passwordInput) return;

                const isPassword = passwordInput.type === "password";

                passwordInput.type = isPassword ? "text" : "password";

                const icon = button.querySelector("i");

                if (icon) {
                    icon.classList.toggle("fa-eye", !isPassword);
                    icon.classList.toggle("fa-eye-slash", isPassword);
                }

                button.setAttribute(
                    "aria-label",
                    isPassword ? "Hide password" : "Show password",
                );
            });
        });

    // EDIT PROFILE: FRONTEND PREVIEW ONLY
    if (editProfileForm) {
        editProfileForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (!editProfileForm.reportValidity()) return;

            const nameInput = document.getElementById("editFullName");
            const emailInput = document.getElementById("editEmail");
            const contactInput = document.getElementById("editContact");

            const newName = nameInput.value.trim();
            const newEmail = emailInput.value.trim();
            const newContact = contactInput.value.trim();

            if (!newName || !newEmail) {
                showToast("Please enter your name and email address.");
                return;
            }

            const displayName = document.getElementById("displayFullName");
            const headerName = document.getElementById("profileDisplayName");
            const displayEmail = document.getElementById("displayEmail");
            const displayContact = document.getElementById("displayContact");
            const avatar = document.querySelector(".student-profile-avatar");

            if (displayName) displayName.textContent = newName;
            if (headerName) headerName.textContent = newName;

            if (displayEmail) {
                displayEmail.textContent = newEmail;
            }

            if (displayContact) {
                displayContact.textContent = newContact || "Not provided";
            }

            // Refresh initials in the profile avatar.
            if (avatar) {
                const initials = newName
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2)
                    .map(function (part) {
                        return part.charAt(0).toUpperCase();
                    })
                    .join("");

                avatar.textContent = initials || "S";
            }

            closeModal(editProfileModal);

            showToast(
                "Preview updated. Changes have not been saved to the database.",
            );
        });
    }

    // CHANGE PASSWORD: FRONTEND VALIDATION ONLY
    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (!changePasswordForm.reportValidity()) return;

            const currentPassword =
                document.getElementById("currentPassword").value;

            const newPassword = document.getElementById("newPassword").value;

            const confirmPassword =
                document.getElementById("confirmPassword").value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                showToast("Please complete all password fields.");
                return;
            }

            if (newPassword.length < 8) {
                showToast("Your new password must have at least 8 characters.");
                return;
            }

            if (newPassword !== confirmPassword) {
                showToast("The new passwords do not match.");
                document.getElementById("confirmPassword").focus();
                return;
            }

            // Do not pretend the password was changed.
            showToast(
                "Password form validated. Connect the Laravel backend to update your password securely.",
            );
        });
    }

    // ADDRESS UPDATE REQUEST: UI PREVIEW ONLY
    const requestAddressBtn = document.getElementById("requestAddressBtn");

    if (requestAddressBtn) {
        requestAddressBtn.addEventListener("click", function () {
            showToast(
                "Address update requests are not connected to the backend yet.",
            );
        });
    }
});
