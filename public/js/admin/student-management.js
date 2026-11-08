document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // CSRF TOKEN
    // ==========================================

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // ==========================================
    // ELEMENT REFERENCES
    // ==========================================

    const studentModal = document.getElementById("studentModal");
    const editStudentModal = document.getElementById("editStudentModal");
    const viewStudentModal = document.getElementById("viewStudentModal");
    const deleteStudentModal = document.getElementById("deleteStudentModal");
    const notificationModal = document.getElementById(
        "studentNotificationModal",
    );

    const addStudentBtn = document.getElementById("addStudentBtn");
    const saveStudentBtn = document.getElementById("saveStudentBtn");
    const saveEditStudentBtn = document.getElementById("saveEditStudent");

    const studentTableBody = document.getElementById("studentTableBody");

    const studentSearch = document.getElementById("studentSearch");
    const gradeFilter = document.getElementById("gradeFilter");
    const sexFilter = document.getElementById("sexFilter");
    const statusFilter = document.getElementById("statusFilter");

    const studentNoResults = document.getElementById("studentNoResults");
    const clearStudentFilters = document.getElementById("clearStudentFilters");

    let selectedStudentId = null;

    // ==========================================
    // MODAL FUNCTIONS
    // ==========================================

    function openModal(modal) {
        if (modal) {
            modal.classList.add("show");
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.classList.remove("show");
        }
    }

    function closeAllModals() {
        [
            studentModal,
            editStudentModal,
            viewStudentModal,
            deleteStudentModal,
            notificationModal,
        ].forEach(closeModal);
    }

    // ==========================================
    // NOTIFICATION MODAL
    // ==========================================

    window.showModal = function (message, type = "info") {
        if (!notificationModal) {
            console.log(`[${type.toUpperCase()}] ${message}`);
            return;
        }

        const title = document.getElementById("notificationTitle");
        const subtitle = document.getElementById("notificationSubtitle");
        const messageElement = document.getElementById("notificationMessage");
        const iconContainer = document.getElementById("notificationIcon");

        const notificationTypes = {
            success: {
                title: "Success",
                subtitle: "Your action was completed successfully.",
                icon: "fa-circle-check",
            },
            error: {
                title: "Something Went Wrong",
                subtitle: "Please check the information and try again.",
                icon: "fa-circle-exclamation",
            },
            warning: {
                title: "Warning",
                subtitle: "Please review this action.",
                icon: "fa-triangle-exclamation",
            },
            info: {
                title: "Information",
                subtitle: "Student Management",
                icon: "fa-circle-info",
            },
        };

        const config = notificationTypes[type] || notificationTypes.info;

        if (title) title.textContent = config.title;
        if (subtitle) subtitle.textContent = config.subtitle;
        if (messageElement) messageElement.textContent = message;

        if (iconContainer) {
            iconContainer.className = `delete-icon notification-${type}`;
            iconContainer.innerHTML = `<i class="fa-solid ${config.icon}"></i>`;
        }

        openModal(notificationModal);
    };

    // ==========================================
    // NOTIFICATION CLOSE BUTTONS
    // ==========================================

    document
        .getElementById("closeNotificationModal")
        ?.addEventListener("click", () => closeModal(notificationModal));

    document
        .getElementById("closeNotificationBtn")
        ?.addEventListener("click", () => closeModal(notificationModal));

    // ==========================================
    // MODAL OPEN/CLOSE EVENTS
    // ==========================================

    addStudentBtn?.addEventListener("click", () => {
        openModal(studentModal);
    });

    document
        .getElementById("closeStudentModal")
        ?.addEventListener("click", () => closeModal(studentModal));

    document
        .getElementById("cancelStudentModal")
        ?.addEventListener("click", () => closeModal(studentModal));

    document
        .getElementById("closeEditModal")
        ?.addEventListener("click", () => closeModal(editStudentModal));

    document
        .getElementById("cancelEditModal")
        ?.addEventListener("click", () => closeModal(editStudentModal));

    document
        .getElementById("closeViewModal")
        ?.addEventListener("click", () => closeModal(viewStudentModal));

    document
        .getElementById("closeViewModalBtn")
        ?.addEventListener("click", () => closeModal(viewStudentModal));

    document
        .getElementById("closeDeleteModal")
        ?.addEventListener("click", () => closeModal(deleteStudentModal));

    document
        .getElementById("cancelDeleteModal")
        ?.addEventListener("click", () => closeModal(deleteStudentModal));

    // Close modal when clicking its background.
    [
        studentModal,
        editStudentModal,
        viewStudentModal,
        deleteStudentModal,
        notificationModal,
    ].forEach((modal) => {
        modal?.addEventListener("click", (event) => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Close modals using Escape.
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeAllModals();
        }
    });

    // ==========================================
    // API REQUEST HELPER
    // ==========================================

    async function sendRequest(url, method, body = null) {
        const headers = {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
        };

        const options = {
            method,
            headers,
        };

        if (body !== null) {
            headers["Content-Type"] = "application/json";
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url, options);

        let data = {};

        try {
            data = await response.json();
        } catch (error) {
            throw new Error(
                "The server returned an unexpected response. Please refresh the page and try again.",
            );
        }

        if (!response.ok) {
            const error = new Error(
                data.message || "The request could not be completed.",
            );
            error.data = data;
            error.status = response.status;
            throw error;
        }

        return data;
    }

    // ==========================================
    // VALIDATION ERROR MESSAGE
    // ==========================================

    function getErrorMessage(error, defaultMessage) {
        const errors = error.data?.errors;

        if (errors) {
            return Object.entries(errors)
                .map(([field, messages]) => {
                    const fieldName = field
                        .replaceAll("_", " ")
                        .replace(/\b\w/g, (letter) => letter.toUpperCase());

                    return `${fieldName}: ${messages[0]}`;
                })
                .join("\n");
        }

        return error.message || defaultMessage;
    }

    // ==========================================
    // FORM RESET
    // ==========================================

    function resetAddStudentForm() {
        const fields = [
            "studentId",
            "firstName",
            "middleName",
            "lastName",
            "gradeLevel",
            "sex",
            "schoolYear",
            "email",
        ];

        fields.forEach((id) => {
            const field = document.getElementById(id);
            if (field) field.value = "";
        });

        const status = document.getElementById("studentStatus");

        if (status) {
            status.value = "active";
        }
    }

    // ==========================================
    // ADD STUDENT
    // ==========================================

    saveStudentBtn?.addEventListener("click", async (event) => {
        event.preventDefault();

        const formData = {
            student_id: document.getElementById("studentId").value.trim(),
            first_name: document.getElementById("firstName").value.trim(),
            middle_name: document.getElementById("middleName").value.trim(),
            last_name: document.getElementById("lastName").value.trim(),
            grade_level: document.getElementById("gradeLevel").value,
            sex: document.getElementById("sex").value,
            school_year: document.getElementById("schoolYear").value,
            email: document.getElementById("email").value.trim(),
            status: document.getElementById("studentStatus").value,
        };

        saveStudentBtn.disabled = true;

        try {
            const data = await sendRequest("/admin/students", "POST", formData);

            closeModal(studentModal);
            resetAddStudentForm();

            showModal(data.message || "Student added successfully.", "success");

            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error("Add Student Error:", error);

            showModal(
                getErrorMessage(error, "Unable to add student."),
                "error",
            );
        } finally {
            saveStudentBtn.disabled = false;
        }
    });

    // ==========================================
    // VIEW STUDENT
    // ==========================================

    window.viewStudent = function (id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);

        if (!row) return;

        const fullName = [
            row.dataset.firstName,
            row.dataset.middleName,
            row.dataset.lastName,
        ]
            .filter(Boolean)
            .join(" ");

        document.getElementById("viewStudentId").textContent =
            row.dataset.studentId || "—";

        document.getElementById("viewStudentName").textContent =
            fullName || "—";

        document.getElementById("viewGradeLevel").textContent =
            row.dataset.gradeLevel || "—";

        document.getElementById("viewSex").textContent = row.dataset.sex || "—";

        document.getElementById("viewSchoolYear").textContent =
            row.dataset.schoolYear || "—";

        document.getElementById("viewGmail").textContent =
            row.dataset.email || "—";

        document.getElementById("viewStudentStatus").textContent = row.dataset
            .status
            ? row.dataset.status.charAt(0).toUpperCase() +
              row.dataset.status.slice(1)
            : "—";

        openModal(viewStudentModal);
    };

    // ==========================================
    // EDIT STUDENT
    // ==========================================

    window.editStudent = function (id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);

        if (!row) return;

        selectedStudentId = id;

        document.getElementById("editStudentId").value =
            row.dataset.studentId || "";

        document.getElementById("editFirstName").value =
            row.dataset.firstName || "";

        document.getElementById("editMiddleName").value =
            row.dataset.middleName || "";

        document.getElementById("editLastName").value =
            row.dataset.lastName || "";

        document.getElementById("editGradeLevel").value =
            row.dataset.gradeLevel || "";

        document.getElementById("editSex").value = row.dataset.sex || "";

        document.getElementById("editSchoolYear").value =
            row.dataset.schoolYear || "";

        document.getElementById("editGmail").value = row.dataset.email || "";

        document.getElementById("editStudentStatus").value =
            row.dataset.status || "active";

        openModal(editStudentModal);
    };

    // ==========================================
    // SAVE EDITED STUDENT
    // ==========================================

    saveEditStudentBtn?.addEventListener("click", async (event) => {
        event.preventDefault();

        if (!selectedStudentId) return;

        const updateData = {
            student_id: document.getElementById("editStudentId").value.trim(),
            first_name: document.getElementById("editFirstName").value.trim(),
            middle_name: document.getElementById("editMiddleName").value.trim(),
            last_name: document.getElementById("editLastName").value.trim(),
            grade_level: document.getElementById("editGradeLevel").value,
            sex: document.getElementById("editSex").value,
            school_year: document.getElementById("editSchoolYear").value,
            email: document.getElementById("editGmail").value.trim(),
            status: document.getElementById("editStudentStatus").value,
        };

        saveEditStudentBtn.disabled = true;

        try {
            const data = await sendRequest(
                `/admin/students/${selectedStudentId}`,
                "PUT",
                updateData,
            );

            closeModal(editStudentModal);

            showModal(
                data.message || "Student updated successfully.",
                "success",
            );

            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error("Update Student Error:", error);

            showModal(
                getErrorMessage(error, "Unable to update student."),
                "error",
            );
        } finally {
            saveEditStudentBtn.disabled = false;
        }
    });

    // ==========================================
    // DEACTIVATE STUDENT - OPEN CONFIRMATION
    // ==========================================

    window.deleteStudent = function (id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);

        if (!row) return;

        selectedStudentId = id;

        const studentName = [
            row.dataset.firstName,
            row.dataset.middleName,
            row.dataset.lastName,
        ]
            .filter(Boolean)
            .join(" ");

        const nameElement = document.getElementById("deleteStudentName");

        if (nameElement) {
            nameElement.textContent = studentName || "this student";
        }

        openModal(deleteStudentModal);
    };

    // ==========================================
    // CONFIRM DEACTIVATION
    // ==========================================

    const confirmDeleteStudent = document.getElementById(
        "confirmDeleteStudent",
    );

    confirmDeleteStudent?.addEventListener("click", async () => {
        if (!selectedStudentId) return;

        confirmDeleteStudent.disabled = true;

        try {
            const data = await sendRequest(
                `/admin/students/${selectedStudentId}`,
                "DELETE",
            );

            closeModal(deleteStudentModal);

            showModal(
                data.message || "Student account deactivated successfully.",
                "success",
            );

            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error("Deactivate Student Error:", error);

            showModal(
                getErrorMessage(error, "Unable to deactivate student."),
                "error",
            );
        } finally {
            confirmDeleteStudent.disabled = false;
        }
    });

    // ==========================================
    // ACTIVATE STUDENT
    // ==========================================

    window.toggleStatus = async function (id) {
        try {
            const data = await sendRequest(
                `/admin/students/${id}/toggle-status`,
                "PATCH",
            );

            showModal(
                data.message || "Student account status updated.",
                "success",
            );

            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error("Activate Student Error:", error);

            showModal(
                getErrorMessage(error, "Unable to update student status."),
                "error",
            );
        }
    };

    // ==========================================
    // SEARCH AND FILTERS
    // ==========================================

    function filterStudents() {
        const searchValue = studentSearch?.value.toLowerCase().trim() || "";
        const selectedGrade = gradeFilter?.value || "all";
        const selectedSex = sexFilter?.value || "all";
        const selectedStatus = statusFilter?.value || "all";

        const rows = studentTableBody
            ? studentTableBody.querySelectorAll("tr[data-id]")
            : [];

        let visibleCount = 0;

        rows.forEach((row) => {
            const studentId = (row.dataset.studentId || "").toLowerCase();

            const studentName = [
                row.dataset.firstName,
                row.dataset.middleName,
                row.dataset.lastName,
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase();

            const rowGrade = row.dataset.gradeLevel || "";
            const rowSex = row.dataset.sex || "";
            const rowStatus = row.dataset.status || "";

            const matchesSearch =
                searchValue === "" ||
                studentId.includes(searchValue) ||
                studentName.includes(searchValue);

            const matchesGrade =
                selectedGrade === "all" || rowGrade === selectedGrade;

            const matchesSex = selectedSex === "all" || rowSex === selectedSex;

            const matchesStatus =
                selectedStatus === "all" || rowStatus === selectedStatus;

            const isVisible =
                matchesSearch && matchesGrade && matchesSex && matchesStatus;

            row.style.display = isVisible ? "" : "none";

            if (isVisible) {
                visibleCount++;
            }
        });

        // Show no-results message only when records exist
        // but none match the selected search and filters.

        if (studentNoResults) {
            studentNoResults.hidden = rows.length === 0 || visibleCount > 0;
        }

        // Hide the original server-side empty row when
        // the separate no-results state is being displayed.

        const serverEmptyRow =
            studentTableBody?.querySelector(".empty-state-row");

        if (serverEmptyRow) {
            serverEmptyRow.style.display = rows.length === 0 ? "" : "none";
        }
    }

    studentSearch?.addEventListener("input", filterStudents);
    gradeFilter?.addEventListener("change", filterStudents);
    sexFilter?.addEventListener("change", filterStudents);
    statusFilter?.addEventListener("change", filterStudents);

    // ==========================================
    // CLEAR SEARCH AND FILTERS
    // ==========================================

    clearStudentFilters?.addEventListener("click", () => {
        if (studentSearch) studentSearch.value = "";
        if (gradeFilter) gradeFilter.value = "all";
        if (sexFilter) sexFilter.value = "all";
        if (statusFilter) statusFilter.value = "all";

        filterStudents();
    });

    // ==========================================
    // INITIAL FILTER STATE
    // ==========================================

    filterStudents();
});
