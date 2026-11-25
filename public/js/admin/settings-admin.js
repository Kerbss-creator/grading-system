document.addEventListener("DOMContentLoaded", function () {
    // =====================================
    // SETTINGS TABS
    // =====================================

    const settingsTabs = document.querySelectorAll(".settings-tab");
    const settingsSections = document.querySelectorAll(".settings-section");

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

    // =====================================
    // MODAL ELEMENTS
    // =====================================

    const addYearModal = document.getElementById("addYearModal");
    const openAddYearModal = document.getElementById("openAddYearModal");

    // Open modal
    function openModal(modal) {
        if (!modal) return;

        modal.classList.add("show");
        document.body.classList.add("modal-open");
    }

    // Close modal
    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("show");

        // Unlock scrolling only when no modal remains open
        if (!document.querySelector(".settings-modal.show")) {
            document.body.classList.remove("modal-open");
        }
    }

    // Open Add School Year Modal
    if (openAddYearModal && addYearModal) {
        openAddYearModal.addEventListener("click", function () {
            openModal(addYearModal);

            const yearInput = document.getElementById("year_name");

            if (yearInput) {
                setTimeout(function () {
                    yearInput.focus();
                }, 100);
            }
        });
    }

    // ==========================================
    // EDIT SCHOOL YEAR MODAL
    // ==========================================

    const editYearModal = document.getElementById("editYearModal");
    const editYearForm = document.getElementById("editYearForm");

    const editYearButtons = document.querySelectorAll(".edit-year-btn");

    const editYearName = document.getElementById("edit_year_name");
    const editStartDate = document.getElementById("edit_start_date");
    const editEndDate = document.getElementById("edit_end_date");

    editYearButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            if (
                !editYearModal ||
                !editYearForm ||
                !editYearName ||
                !editStartDate ||
                !editEndDate
            ) {
                return;
            }

            const yearId = button.dataset.id;

            // Populate existing school year details
            editYearName.value = button.dataset.year || "";
            editStartDate.value = button.dataset.start || "";
            editEndDate.value = button.dataset.end || "";

            // Set the correct update URL
            const editUrlTemplate =
                editYearForm.dataset.urlTemplate ||
                editYearForm.getAttribute("action");

            editYearForm.action = editUrlTemplate.replace(
                "__YEAR_ID__",
                yearId,
            );
            // Set minimum end date
            editEndDate.min = editStartDate.value || "";

            // Clear previous validation messages
            editStartDate.setCustomValidity("");
            editEndDate.setCustomValidity("");

            openModal(editYearModal);

            setTimeout(function () {
                editStartDate.focus();
            }, 100);
        });
    });

    // ==========================================
    // EDIT SCHOOL YEAR DATE VALIDATION
    // ==========================================

    if (editStartDate && editEndDate) {
        editStartDate.addEventListener("change", function () {
            editEndDate.min = this.value || "";

            editStartDate.setCustomValidity("");
            editEndDate.setCustomValidity("");

            if (
                this.value &&
                editEndDate.value &&
                editEndDate.value < this.value
            ) {
                editEndDate.value = "";
            }
        });

        editEndDate.addEventListener("change", function () {
            if (
                editStartDate.value &&
                editEndDate.value &&
                editEndDate.value < editStartDate.value
            ) {
                this.setCustomValidity(
                    "End Date cannot be earlier than Start Date.",
                );

                this.reportValidity();
                this.value = "";
            } else {
                this.setCustomValidity("");
            }
        });
    }

    // ==========================================
    // EDIT SCHOOL YEAR FORM VALIDATION
    // ==========================================

    if (editYearForm) {
        editYearForm.addEventListener("submit", function (event) {
            if (
                editStartDate.value &&
                editEndDate.value &&
                editEndDate.value < editStartDate.value
            ) {
                event.preventDefault();

                editEndDate.setCustomValidity(
                    "End Date cannot be earlier than Start Date.",
                );

                editEndDate.reportValidity();
                editEndDate.focus();

                return;
            }

            editEndDate.setCustomValidity("");
        });
    }

    // =====================================
    // CLOSE BUTTONS (INCLUDING CANCEL)
    // =====================================

    document.querySelectorAll(".close-modal").forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();

            const modalId = button.dataset.close;
            const modal = document.getElementById(modalId);

            closeModal(modal);
        });
    });

    // =====================================
    // CLICK OUTSIDE MODAL TO CLOSE
    // =====================================

    document.querySelectorAll(".settings-modal").forEach(function (modal) {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    // =====================================
    // ESC KEY TO CLOSE MODAL
    // =====================================

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            document
                .querySelectorAll(".settings-modal.show")
                .forEach(function (modal) {
                    closeModal(modal);
                });
        }
    });

    // =====================================
    // SCHOOL YEAR ACTIVATION CONFIRMATION
    // =====================================

    document.querySelectorAll(".activate-year-form").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const confirmed = window.confirm(
                "Are you sure you want to activate this school year?\n\n" +
                    "The currently active school year will be marked as Completed.",
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    // =====================================
    // SCHOOL YEAR DATE VALIDATION
    // =====================================

    const startDate = document.getElementById("start_date");
    const endDate = document.getElementById("end_date");

    if (startDate && endDate) {
        // Set minimum end date based on start date
        startDate.addEventListener("change", function () {
            endDate.min = this.value || "";

            if (this.value && endDate.value && endDate.value < this.value) {
                endDate.value = "";
            }
        });

        // Validate end date
        endDate.addEventListener("change", function () {
            if (
                startDate.value &&
                endDate.value &&
                endDate.value < startDate.value
            ) {
                this.setCustomValidity(
                    "End Date cannot be earlier than Start Date.",
                );

                this.reportValidity();
                this.value = "";
            } else {
                this.setCustomValidity("");
            }
        });
    }

    // =====================================
    // SCHOOL YEAR NAME VALIDATION
    // =====================================

    const addYearForm = document.getElementById("addYearForm");

    if (addYearForm) {
        addYearForm.addEventListener("submit", function (event) {
            const yearInput = document.getElementById("year_name");

            if (!yearInput) return;

            const yearName = yearInput.value.trim();
            const yearPattern = /^\d{4}-\d{4}$/;

            // Validate format
            if (!yearPattern.test(yearName)) {
                event.preventDefault();

                yearInput.setCustomValidity(
                    "Please use the correct format. Example: 2027-2028.",
                );

                yearInput.reportValidity();
                yearInput.focus();
                return;
            }

            yearInput.setCustomValidity("");

            // Validate consecutive years
            const years = yearName.split("-");

            const startYear = Number(years[0]);
            const endYear = Number(years[1]);

            if (endYear !== startYear + 1) {
                event.preventDefault();

                yearInput.setCustomValidity(
                    "The school year must contain consecutive years. Example: 2027-2028.",
                );

                yearInput.reportValidity();
                yearInput.focus();
                return;
            }

            // Validate dates
            if (
                startDate &&
                endDate &&
                startDate.value &&
                endDate.value &&
                endDate.value < startDate.value
            ) {
                event.preventDefault();

                endDate.setCustomValidity(
                    "End Date cannot be earlier than Start Date.",
                );

                endDate.reportValidity();
                endDate.focus();
                return;
            }
        });
    }

    // =====================================
    // REOPEN MODAL IF VALIDATION FAILED
    // =====================================

    const hasValidationErrors = document.querySelector(".settings-alert.error");

    const hasOldYearInput = document.getElementById("year_name");

    if (
        hasValidationErrors &&
        hasOldYearInput &&
        hasOldYearInput.value.trim() !== "" &&
        addYearModal
    ) {
        openModal(addYearModal);
    }

    // ==========================================
    // DATABASE BACKUP - BACKUP HISTORY
    // ==========================================

    const backupContainer = document.getElementById("backupHistoryContainer");
    const backupHistoryBody = document.getElementById("backupHistoryBody");
    const refreshBackupHistory = document.getElementById(
        "refreshBackupHistory",
    );

    const restoreBackupModal = document.getElementById("restoreBackupModal");
    const restoreBackupForm = document.getElementById("restoreBackupForm");
    const restoreBackupFilename = document.getElementById(
        "restoreBackupFilename",
    );
    const confirmRestoreBtn = document.getElementById("confirmRestoreBtn");

    let backupHistoryLoaded = false;

    function formatBackupSize(bytes) {
        const size = Number(bytes);

        if (!Number.isFinite(size) || size <= 0) {
            return "0 KB";
        }

        if (size < 1024 * 1024) {
            return (size / 1024).toFixed(2) + " KB";
        }

        return (size / (1024 * 1024)).toFixed(2) + " MB";
    }

    function formatBackupDate(dateValue) {
        if (!dateValue) {
            return "Not available";
        }

        const date = new Date(dateValue);

        if (Number.isNaN(date.getTime())) {
            return String(dateValue);
        }

        return date.toLocaleString("en-PH", {
            year: "numeric",
            month: "short",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    function showBackupMessage(message, className) {
        if (!backupHistoryBody) return;

        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 4;
        cell.className = className;
        cell.textContent = message;

        row.appendChild(cell);
        backupHistoryBody.replaceChildren(row);
    }

    async function loadBackupHistory(forceRefresh = false) {
        if (!backupContainer || !backupHistoryBody) return;

        if (backupHistoryLoaded && !forceRefresh) return;

        const indexUrl = backupContainer.dataset.indexUrl;

        showBackupMessage("Loading backup history...", "backup-loading");

        try {
            const response = await fetch(indexUrl, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                cache: "no-store",
            });

            if (!response.ok) {
                throw new Error("Unable to retrieve backup history.");
            }

            const result = await response.json();

            const backups = Array.isArray(result)
                ? result
                : result.backups || result.data || [];

            backupHistoryBody.replaceChildren();

            if (!backups.length) {
                showBackupMessage("No database backups found.", "backup-empty");

                backupHistoryLoaded = true;
                return;
            }

            backups.forEach(function (backup) {
                const filename = String(backup.filename || "");

                // Only accept safe SQL backup filenames.
                if (!/^[A-Za-z0-9_.-]+\.sql$/i.test(filename)) {
                    return;
                }

                const row = document.createElement("tr");

                // Backup filename
                const fileCell = document.createElement("td");
                const fileNameWrapper = document.createElement("div");
                const fileIcon = document.createElement("i");
                const fileName = document.createElement("span");

                fileNameWrapper.className = "backup-file-name";
                fileIcon.className = "fa-solid fa-file-code";
                fileName.textContent = filename;

                fileNameWrapper.append(fileIcon, fileName);
                fileCell.appendChild(fileNameWrapper);

                // File size
                const sizeCell = document.createElement("td");
                sizeCell.textContent = formatBackupSize(backup.size);

                // Date created
                const dateCell = document.createElement("td");
                dateCell.textContent = formatBackupDate(backup.created_at);

                // Actions
                const actionCell = document.createElement("td");
                const actions = document.createElement("div");

                actions.className = "backup-actions";

                // Download link
                const downloadLink = document.createElement("a");
                const downloadIcon = document.createElement("i");

                downloadLink.className = "backup-download-btn";
                downloadLink.href = backupContainer.dataset.downloadUrl.replace(
                    "__BACKUP_FILENAME__",
                    encodeURIComponent(filename),
                );

                downloadIcon.className = "fa-solid fa-download";
                downloadLink.append(
                    downloadIcon,
                    document.createTextNode(" Download"),
                );

                // Restore button
                const restoreButton = document.createElement("button");
                const restoreIcon = document.createElement("i");

                restoreButton.type = "button";
                restoreButton.className = "backup-restore-btn";
                restoreButton.dataset.filename = filename;

                restoreIcon.className = "fa-solid fa-rotate-left";
                restoreButton.append(
                    restoreIcon,
                    document.createTextNode(" Restore"),
                );

                restoreButton.addEventListener("click", function () {
                    if (
                        !restoreBackupModal ||
                        !restoreBackupForm ||
                        !restoreBackupFilename
                    ) {
                        return;
                    }

                    const template = restoreBackupForm.dataset.urlTemplate;

                    restoreBackupForm.action = template.replace(
                        "__BACKUP_FILENAME__",
                        encodeURIComponent(filename),
                    );

                    restoreBackupFilename.textContent = filename;

                    if (confirmRestoreBtn) {
                        confirmRestoreBtn.disabled = false;
                        confirmRestoreBtn.innerHTML =
                            '<i class="fa-solid fa-rotate-left"></i> Restore Database';
                    }

                    openModal(restoreBackupModal);
                });

                actions.append(downloadLink, restoreButton);
                actionCell.appendChild(actions);

                row.append(fileCell, sizeCell, dateCell, actionCell);

                backupHistoryBody.appendChild(row);
            });

            if (!backupHistoryBody.children.length) {
                showBackupMessage(
                    "No valid SQL backup files found.",
                    "backup-empty",
                );
            }

            backupHistoryLoaded = true;
        } catch (error) {
            console.error("Backup history error:", error);

            showBackupMessage(
                "Unable to load backup history. Please try refreshing.",
                "backup-error",
            );

            backupHistoryLoaded = false;
        }
    }

    // Load backup history when the Database Backup tab is opened.
    document
        .querySelectorAll('.settings-tab[data-section="backup"]')
        .forEach(function (tab) {
            tab.addEventListener("click", function () {
                loadBackupHistory();
            });
        });

    // Refresh backup history.
    if (refreshBackupHistory) {
        refreshBackupHistory.addEventListener("click", function () {
            loadBackupHistory(true);
        });
    }

    // ==========================================
    // CREATE DATABASE BACKUP
    // ==========================================

    const createBackupForm = document.getElementById("createBackupForm");
    const createBackupBtn = document.getElementById("createBackupBtn");

    if (createBackupForm && createBackupBtn) {
        createBackupForm.addEventListener("submit", function () {
            createBackupBtn.disabled = true;

            createBackupBtn.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i> Creating Backup...';
        });
    }

    // ==========================================
    // RESTORE DATABASE CONFIRMATION
    // ==========================================

    // ==========================================
    // RESTORE DATABASE CONFIRMATION
    // ==========================================

    if (restoreBackupForm) {
        restoreBackupForm.addEventListener("submit", function (event) {
            const restoreAction = restoreBackupForm.getAttribute("action");

            // Prevent submission if no valid restore URL is selected.
            if (!restoreAction || !restoreAction.includes("/restore")) {
                event.preventDefault();
                return;
            }

            // The custom Restore modal already serves as confirmation.
            // No additional browser confirmation is necessary.

            if (confirmRestoreBtn) {
                confirmRestoreBtn.disabled = true;

                confirmRestoreBtn.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i> Restoring Database...';
            }
        });
    }
});
