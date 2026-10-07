document.addEventListener("DOMContentLoaded", function () {
  /* SETTINGS ELEMENTS */

  const settingsTabs = document.querySelectorAll(".settings-tab");
  const settingsSections = document.querySelectorAll(".settings-section");

  const passwordModal = document.getElementById("passwordModal");
  const backupModal = document.getElementById("backupModal");

  const changePasswordBtn = document.getElementById("changePasswordBtn");

  const backupBtn = document.getElementById("backupBtn");

  const updatePasswordBtn = document.getElementById("updatePasswordBtn");

  const confirmBackupBtn = document.getElementById("confirmBackupBtn");

  const saveSettings = document.getElementById("saveSettings");

  const cancelSettings = document.getElementById("cancelSettings");

  /* ACCOUNT SETTINGS */

  const adminName = document.getElementById("adminName");

  const adminEmail = document.getElementById("adminEmail");

  const headerAdminName = document.getElementById("headerAdminName");

  const menuAdminName = document.getElementById("menuAdminName");

  /* LOAD SAVED SETTINGS */

  const savedAdminName = localStorage.getItem("adminName");

  const savedAdminEmail = localStorage.getItem("adminEmail");

  const savedSchoolName = localStorage.getItem("schoolName");

  const savedSchoolYear = localStorage.getItem("schoolYear");

  const savedSemester = localStorage.getItem("semester");

  const savedPassingGrade = localStorage.getItem("passingGrade");

  const savedMaximumGrade = localStorage.getItem("maximumGrade");

  /* APPLY SAVED ACCOUNT SETTINGS */

  if (adminName && savedAdminName) {
    adminName.value = savedAdminName;
  }

  if (adminEmail && savedAdminEmail) {
    adminEmail.value = savedAdminEmail;
  }

  /* APPLY SAVED GENERAL SETTINGS */

  if (savedSchoolName) {
    document.getElementById("schoolName").value = savedSchoolName;
  }

  if (savedSchoolYear) {
    document.getElementById("schoolYear").value = savedSchoolYear;
  }

  if (savedSemester) {
    document.getElementById("semester").value = savedSemester;
  }

  /* APPLY SAVED GRADE SETTINGS */

  if (savedPassingGrade) {
    document.getElementById("passingGrade").value = savedPassingGrade;
  }

  if (savedMaximumGrade) {
    document.getElementById("maximumGrade").value = savedMaximumGrade;
  }

  /* UPDATE HEADER PROFILE */

  function updateAdminProfile() {
    const name = localStorage.getItem("adminName") || "Administrator";

    if (headerAdminName) {
      headerAdminName.textContent = name;
    }

    if (menuAdminName) {
      menuAdminName.textContent = name;
    }
  }

  updateAdminProfile();

  /* SETTINGS TABS */

  settingsTabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
      const targetSection = tab.dataset.section;

      settingsTabs.forEach(function (item) {
        item.classList.remove("active");
      });

      settingsSections.forEach(function (section) {
        section.classList.remove("active");
      });

      tab.classList.add("active");

      const section = document.getElementById(targetSection);

      if (section) {
        section.classList.add("active");
      }
    });
  });

  /* OPEN PASSWORD MODAL */

  if (changePasswordBtn && passwordModal) {
    changePasswordBtn.addEventListener("click", function () {
      passwordModal.classList.add("show");
    });
  }

  /* OPEN BACKUP MODAL */

  if (backupBtn && backupModal) {
    backupBtn.addEventListener("click", function () {
      backupModal.classList.add("show");
    });
  }

  /* CLOSE MODALS */

  const closeButtons = document.querySelectorAll(".close-modal");

  closeButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const modalId = button.dataset.close;

      const modal = document.getElementById(modalId);

      if (modal) {
        modal.classList.remove("show");
      }
    });
  });

  /* CLOSE MODAL WHEN CLICKING OUTSIDE */

  document.querySelectorAll(".settings-modal").forEach(function (modal) {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) {
        modal.classList.remove("show");
      }
    });
  });

  /* ESC KEY FOR MODALS */

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      document
        .querySelectorAll(".settings-modal.show")
        .forEach(function (modal) {
          modal.classList.remove("show");
        });
    }
  });

  /* UPDATE PASSWORD */

  if (updatePasswordBtn) {
    updatePasswordBtn.addEventListener("click", function () {
      const currentPassword = document
        .getElementById("currentPassword")
        .value.trim();

      const newPassword = document.getElementById("newPassword").value.trim();

      const confirmPassword = document
        .getElementById("confirmPassword")
        .value.trim();

      if (!currentPassword || !newPassword || !confirmPassword) {
        alert("Please complete all password fields.");

        return;
      }

      if (newPassword.length < 8) {
        alert("New password must contain at least 8 characters.");

        return;
      }

      if (newPassword !== confirmPassword) {
        alert("New password and confirm password do not match.");

        return;
      }

      alert("Password updated successfully.");

      document.getElementById("currentPassword").value = "";

      document.getElementById("newPassword").value = "";

      document.getElementById("confirmPassword").value = "";

      passwordModal.classList.remove("show");
    });
  }

  /* DATABASE BACKUP */

  if (confirmBackupBtn) {
    confirmBackupBtn.addEventListener("click", function () {
      alert("Database backup created successfully.");

      backupModal.classList.remove("show");
    });
  }

  /* SAVE SETTINGS */

  if (saveSettings) {
    saveSettings.addEventListener("click", function () {
      /* GENERAL SETTINGS */

      const schoolName = document.getElementById("schoolName").value.trim();

      const schoolYear = document.getElementById("schoolYear").value;

      const semester = document.getElementById("semester").value;

      /* GRADE SETTINGS */

      const passingGrade = Number(
        document.getElementById("passingGrade").value,
      );

      const maximumGrade = Number(
        document.getElementById("maximumGrade").value,
      );

      /* ACCOUNT SETTINGS */

      const name = adminName ? adminName.value.trim() : "";

      const email = adminEmail ? adminEmail.value.trim() : "";

      /* VALIDATION */

      if (!schoolName) {
        alert("Please enter the school name.");

        return;
      }

      if (!name) {
        alert("Please enter the administrator name.");

        return;
      }

      if (!email) {
        alert("Please enter the administrator email.");

        return;
      }

      if (!email.includes("@")) {
        alert("Please enter a valid email address.");

        return;
      }

      if (
        isNaN(passingGrade) ||
        passingGrade < 0 ||
        passingGrade > maximumGrade
      ) {
        alert("Please enter a valid passing grade.");

        return;
      }

      if (isNaN(maximumGrade) || maximumGrade <= 0 || maximumGrade > 100) {
        alert("Maximum grade must be between 1 and 100.");

        return;
      }

      /* SAVE TO LOCAL STORAGE */

      localStorage.setItem("schoolName", schoolName);

      localStorage.setItem("schoolYear", schoolYear);

      localStorage.setItem("semester", semester);

      localStorage.setItem("passingGrade", passingGrade);

      localStorage.setItem("maximumGrade", maximumGrade);

      localStorage.setItem("adminName", name);

      localStorage.setItem("adminEmail", email);

      /* UPDATE HEADER */

      updateAdminProfile();

      /* SUCCESS MESSAGE */

      alert("Settings saved successfully!");
    });
  }

  /* CANCEL SETTINGS */

  if (cancelSettings) {
    cancelSettings.addEventListener("click", function () {
      const confirmed = confirm(
        "Are you sure you want to discard your changes?",
      );

      if (confirmed) {
        location.reload();
      }
    });
  }
});
