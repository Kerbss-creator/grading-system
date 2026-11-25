document.addEventListener("DOMContentLoaded", function () {
    // ==========================================
    // PROFILE MODALS
    // ==========================================

    const editProfileModal = document.getElementById("editProfileModal");
    const changePasswordModal = document.getElementById("changePasswordModal");
    const teacherPhotoModal = document.getElementById("teacherPhotoModal");

    const editProfileBtn = document.getElementById("editProfileBtn");
    const changePasswordBtn = document.getElementById("changePasswordBtn");
    const openTeacherPhotoModal = document.getElementById(
        "openTeacherPhotoModal",
    );

    const closeEditProfile = document.getElementById("closeEditProfile");
    const closeChangePassword = document.getElementById("closeChangePassword");
    const closeTeacherPhotoModal = document.getElementById(
        "closeTeacherPhotoModal",
    );

    const cancelEditProfile = document.getElementById("cancelEditProfile");
    const cancelChangePassword = document.getElementById(
        "cancelChangePassword",
    );
    const cancelTeacherPhoto = document.getElementById("cancelTeacherPhoto");

    const editProfileForm = document.getElementById("editProfileForm");
    const changePasswordForm = document.getElementById("changePasswordForm");

    // ==========================================
    // MODAL HELPERS
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

        const anotherModalOpen = document.querySelector(
            ".profile-modal-overlay.show",
        );

        if (!anotherModalOpen) {
            document.body.style.overflow = "";
        }
    }

    // ==========================================
    // OPEN MODALS
    // ==========================================

    if (editProfileBtn) {
        editProfileBtn.addEventListener("click", function () {
            openModal(editProfileModal);
        });
    }

    if (changePasswordBtn) {
        changePasswordBtn.addEventListener("click", function () {
            openModal(changePasswordModal);
        });
    }

    // ==========================================
    // CLOSE EDIT PROFILE MODAL
    // ==========================================

    function closeEditModal() {
        closeModal(editProfileModal);
    }

    if (closeEditProfile) {
        closeEditProfile.addEventListener("click", closeEditModal);
    }

    if (cancelEditProfile) {
        cancelEditProfile.addEventListener("click", closeEditModal);
    }

    // ==========================================
    // CLOSE CHANGE PASSWORD MODAL
    // ==========================================

    function closePasswordModal() {
        closeModal(changePasswordModal);
    }

    if (closeChangePassword) {
        closeChangePassword.addEventListener("click", closePasswordModal);
    }

    if (cancelChangePassword) {
        cancelChangePassword.addEventListener("click", closePasswordModal);
    }

    // ==========================================
    // PROFILE PHOTO ELEMENTS
    // ==========================================

    const teacherPhotoForm = document.getElementById("teacherPhotoForm");
    const teacherPhotoInput = document.getElementById("teacherPhotoInput");
    const teacherPhotoPreview = document.getElementById("teacherPhotoPreview");

    const teacherPhotoPlaceholder = document.getElementById(
        "teacherPhotoPlaceholder",
    );

    let selectedPhotoUrl = null;

    const originalPhotoSrc = teacherPhotoPreview
        ? teacherPhotoPreview.getAttribute("src")
        : "";

    const originalPhotoHidden = teacherPhotoPreview
        ? teacherPhotoPreview.classList.contains("is-hidden")
        : true;

    // ==========================================
    // PROFILE PHOTO PREVIEW
    // ==========================================

    function displayPhotoPreview(imageUrl) {
        if (!teacherPhotoPreview || !teacherPhotoPlaceholder) return;

        teacherPhotoPreview.src = imageUrl;
        teacherPhotoPreview.classList.remove("is-hidden");

        teacherPhotoPlaceholder.classList.add("is-hidden");
    }

    function resetPhotoPreview() {
        if (!teacherPhotoPreview || !teacherPhotoPlaceholder) return;

        if (selectedPhotoUrl) {
            URL.revokeObjectURL(selectedPhotoUrl);
            selectedPhotoUrl = null;
        }

        if (teacherPhotoInput) {
            teacherPhotoInput.value = "";
        }

        if (originalPhotoSrc) {
            teacherPhotoPreview.src = originalPhotoSrc;

            teacherPhotoPreview.classList.toggle(
                "is-hidden",
                originalPhotoHidden,
            );

            teacherPhotoPlaceholder.classList.add("is-hidden");
        } else {
            teacherPhotoPreview.removeAttribute("src");
            teacherPhotoPreview.classList.add("is-hidden");

            teacherPhotoPlaceholder.classList.remove("is-hidden");
        }
    }

    // ==========================================
    // OPEN PROFILE PHOTO MODAL
    // ==========================================

    if (openTeacherPhotoModal && teacherPhotoModal) {
        openTeacherPhotoModal.addEventListener("click", function () {
            resetPhotoPreview();
            openModal(teacherPhotoModal);
        });
    }

    // ==========================================
    // CLOSE PROFILE PHOTO MODAL
    // ==========================================

    function closeTeacherPhoto() {
        closeModal(teacherPhotoModal);
        resetPhotoPreview();
    }

    if (closeTeacherPhotoModal) {
        closeTeacherPhotoModal.addEventListener("click", closeTeacherPhoto);
    }

    if (cancelTeacherPhoto) {
        cancelTeacherPhoto.addEventListener("click", closeTeacherPhoto);
    }

    // ==========================================
    // VALIDATE AND PREVIEW SELECTED PHOTO
    // ==========================================

    if (teacherPhotoInput) {
        teacherPhotoInput.addEventListener("change", function () {
            const file = this.files[0];

            if (!file) return;

            const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

            const maximumSize = 2 * 1024 * 1024;

            if (!allowedTypes.includes(file.type)) {
                alert(
                    "Invalid image format. Please select a JPG, JPEG, PNG, or WEBP image.",
                );

                this.value = "";
                return;
            }

            if (file.size > maximumSize) {
                alert("The selected image exceeds the 2 MB file size limit.");

                this.value = "";
                return;
            }

            if (selectedPhotoUrl) {
                URL.revokeObjectURL(selectedPhotoUrl);
            }

            selectedPhotoUrl = URL.createObjectURL(file);

            displayPhotoPreview(selectedPhotoUrl);
        });
    }

    // ==========================================
    // PROFILE PHOTO FORM
    // ==========================================

    if (teacherPhotoForm) {
        teacherPhotoForm.addEventListener("submit", function (event) {
            if (!teacherPhotoInput || !teacherPhotoInput.files.length) {
                event.preventDefault();

                alert("Please select a profile photo first.");
                return;
            }

            // Submit normally to Laravel.
        });
    }

    // ==========================================
    // CLOSE MODALS WHEN CLICKING OUTSIDE
    // ==========================================

    [editProfileModal, changePasswordModal, teacherPhotoModal].forEach(
        function (modal) {
            if (!modal) return;

            modal.addEventListener("click", function (event) {
                if (event.target === modal) {
                    if (modal === editProfileModal) closeEditModal();
                    else if (modal === changePasswordModal)
                        closePasswordModal();
                    else closeTeacherPhoto();
                }
            });
        },
    );

    // ==========================================
    // CLOSE MODALS USING ESCAPE KEY
    // ==========================================

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        if (editProfileModal?.classList.contains("show")) {
            closeEditModal();
        }

        if (changePasswordModal?.classList.contains("show")) {
            closePasswordModal();
        }

        if (teacherPhotoModal?.classList.contains("show")) {
            closeTeacherPhoto();
        }
    });

    // ==========================================
    // TOGGLE PASSWORD VISIBILITY
    // ==========================================

    const passwordToggles = document.querySelectorAll(".toggle-password");

    passwordToggles.forEach(function (toggle) {
        toggle.addEventListener("click", function () {
            const targetId = toggle.getAttribute("data-target");
            const passwordInput = document.getElementById(targetId);
            const icon = toggle.querySelector("i");

            if (!passwordInput || !icon) return;

            const showPassword = passwordInput.type === "password";

            passwordInput.type = showPassword ? "text" : "password";

            icon.classList.toggle("fa-eye", !showPassword);
            icon.classList.toggle("fa-eye-slash", showPassword);
        });
    });

    // ==========================================
    // EDIT PROFILE FORM
    // BACKEND CONNECTION PENDING
    // ==========================================

    if (editProfileForm) {
        editProfileForm.addEventListener("submit", function (event) {
            event.preventDefault();

            alert("Profile editing is not yet connected to the database.");
        });
    }

    // ==========================================
    // CHANGE PASSWORD FORM
    // BACKEND CONNECTION PENDING
    // ==========================================

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

            alert("Password changing is not yet connected to the database.");
        });
    }
});
