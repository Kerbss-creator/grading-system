document.addEventListener("DOMContentLoaded", function () {
    // ELEMENTS

    const tableBody = document.getElementById("adviserTableBody");
    const formModal = document.getElementById("adviserFormModal");
    const viewModal = document.getElementById("viewAdviserModal");
    const deleteModal = document.getElementById("deleteAdviserModal");
    const adviserForm = document.getElementById("adviserForm");

    const searchInput = document.getElementById("adviserSearch");
    const gradeFilter = document.getElementById("gradeFilter");
    const statusFilter = document.getElementById("statusFilter");

    let editingRow = null;
    let deletingRow = null;

    // GET ELEMENT BY ID

    function getElement(id) {
        return document.getElementById(id);
    }

    // OPEN MODAL

    function openModal(modal) {
        if (!modal) return;

        modal.classList.add("show");
    }

    // CLOSE MODAL

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("show");
    }

    // CLOSE ALL MODALS

    function closeAllModals() {
        document
            .querySelectorAll(".modal-overlay.show")
            .forEach(function (modal) {
                closeModal(modal);
            });
    }

    // FULL NAME

    function getFullName(row) {
        return row.dataset.name || "";
    }

    // CREATE STATUS BADGE

    function createStatusBadge(status) {
        const badge = document.createElement("span");

        badge.className = "status " + status;
        badge.textContent = status === "inactive" ? "Inactive" : "Active";

        return badge;
    }

    // RESET FORM

    function resetForm() {
        adviserForm.reset();

        editingRow = null;

        getElement("adviserFormTitle").textContent = "Add Adviser";

        getElement("adviserFormDescription").textContent =
            "Create an adviser record and assign an advisory class.";

        getElement("saveAdviserText").textContent = "Add Adviser";

        adviserForm.querySelector('button[type="submit"]').disabled = false;
    }

    // ADD ADVISER MODAL

    function openAddModal() {
        resetForm();
        openModal(formModal);
    }

    getElement("addAdviserBtn").addEventListener("click", openAddModal);

    // EMPTY STATE ADD BUTTON
    // Event delegation para gumana rin kapag bumalik ang empty state.

    tableBody.addEventListener("click", function (event) {
        if (event.target.closest("#emptyAddAdviserBtn")) {
            openAddModal();
        }
    });

    // UPDATE TABLE ROW

    function updateRow(row, data) {
        row.dataset.adviserId = data.id;
        row.dataset.name = data.name;
        row.dataset.email = data.email;
        row.dataset.advisoryClass = data.advisoryClass;
        row.dataset.status = data.status;

        row.classList.toggle("is-inactive", data.status === "inactive");

        // Clear existing cells safely.

        row.replaceChildren();

        const adviserIdCell = document.createElement("td");
        adviserIdCell.textContent = data.id;

        const nameCell = document.createElement("td");
        nameCell.textContent = data.name;

        const emailCell = document.createElement("td");
        emailCell.textContent = data.email;

        const classCell = document.createElement("td");
        classCell.textContent = data.advisoryClass || "Not assigned";

        const statusCell = document.createElement("td");
        statusCell.appendChild(createStatusBadge(data.status));

        const actionCell = document.createElement("td");
        const actions = document.createElement("div");

        actions.className = "action-buttons";

        const buttons = [
            {
                action: "view",
                className: "view-btn",
                title: "View Adviser",
                icon: "fa-eye",
            },
            {
                action: "edit",
                className: "edit-btn",
                title: "Edit Adviser",
                icon: "fa-pen",
            },
            {
                action: "delete",
                className: "delete-btn",
                title: "Delete Adviser",
                icon: "fa-trash",
            },
            {
                action: "status",
                className: "status-btn",
                title:
                    data.status === "active"
                        ? "Deactivate Adviser"
                        : "Activate Adviser",
                icon:
                    data.status === "active"
                        ? "fa-user-slash"
                        : "fa-user-check",
            },
        ];

        buttons.forEach(function (item) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = item.className;
            button.title = item.title;
            button.dataset.action = item.action;

            const icon = document.createElement("i");
            icon.className = "fa-solid " + item.icon;

            button.appendChild(icon);
            actions.appendChild(button);
        });

        actionCell.appendChild(actions);

        row.append(
            adviserIdCell,
            nameCell,
            emailCell,
            classCell,
            statusCell,
            actionCell,
        );
    }

    // CHECK DUPLICATE ID OR EMAIL

    function hasDuplicate(data, currentRow = null) {
        const rows = tableBody.querySelectorAll("tr[data-id]");

        return Array.from(rows).some(function (row) {
            if (row === currentRow) return false;

            const sameId =
                (row.dataset.adviserId || "").toLowerCase() ===
                data.id.toLowerCase();

            const sameEmail =
                (row.dataset.email || "").toLowerCase() ===
                data.email.toLowerCase();

            return sameId || sameEmail;
        });
    }

    // FORM SUBMISSION
    // DEMO ONLY: No database requests are made.

    adviserForm.addEventListener("submit", function (event) {
        event.preventDefault();

        if (!adviserForm.reportValidity()) return;

        const data = {
            id: getElement("adviserId").value.trim(),

            name: [
                getElement("adviserFirstName").value.trim(),
                getElement("adviserMiddleName").value.trim(),
                getElement("adviserLastName").value.trim(),
            ]
                .filter(Boolean)
                .join(" "),

            email: getElement("adviserEmail").value.trim(),

            advisoryClass: getElement("advisoryClass").value,

            status: getElement("adviserStatus").value,
        };

        // Prevent duplicate IDs or emails.

        if (hasDuplicate(data, editingRow)) {
            alert("Adviser ID or email already exists in the table.");
            return;
        }

        if (editingRow) {
            // EDIT EXISTING DEMO ROW

            updateRow(editingRow, data);
        } else {
            // ADD NEW DEMO ROW

            const emptyRow = getElement("adviserEmptyRow");

            if (emptyRow) {
                emptyRow.remove();
            }

            const newRow = document.createElement("tr");

            newRow.dataset.id = "demo-" + Date.now();

            updateRow(newRow, data);

            tableBody.appendChild(newRow);
        }

        closeModal(formModal);

        resetForm();

        updateEmptyState();

        filterAdvisers();

        alert(
            "Demo only: Adviser record updated on this page. " +
                "Changes are not saved to the database.",
        );
    });

    // VIEW ADVISER

    function viewAdviser(row) {
        getElement("viewAdviserId").textContent = row.dataset.adviserId || "-";

        getElement("viewAdviserName").textContent = getFullName(row) || "-";

        getElement("viewAdviserEmail").textContent = row.dataset.email || "-";

        getElement("viewAdvisoryClass").textContent =
            row.dataset.advisoryClass || "Not assigned";

        getElement("viewAdviserStatus").textContent = (
            row.dataset.status || "active"
        ).toUpperCase();

        openModal(viewModal);
    }

    // EDIT ADVISER

    function editAdviser(row) {
        editingRow = row;

        // Populate the form from the selected row.

        const nameParts = getFullName(row).trim().split(/\s+/);

        getElement("adviserId").value = row.dataset.adviserId || "";

        getElement("adviserFirstName").value = nameParts[0] || "";

        getElement("adviserMiddleName").value =
            nameParts.length > 2 ? nameParts.slice(1, -1).join(" ") : "";

        getElement("adviserLastName").value =
            nameParts.length > 1 ? nameParts[nameParts.length - 1] : "";

        getElement("adviserEmail").value = row.dataset.email || "";

        getElement("advisoryClass").value = row.dataset.advisoryClass || "";

        getElement("adviserStatus").value = row.dataset.status || "active";

        getElement("adviserFormTitle").textContent = "Edit Adviser";

        getElement("adviserFormDescription").textContent =
            "Update adviser record information.";

        getElement("saveAdviserText").textContent = "Save Changes";

        openModal(formModal);
    }

    // DELETE ADVISER

    function deleteAdviser(row) {
        deletingRow = row;

        getElement("deleteAdviserName").textContent =
            getFullName(row) || "this adviser";

        openModal(deleteModal);
    }

    getElement("confirmDeleteAdviser").addEventListener("click", function () {
        if (!deletingRow) return;

        // Removes the demo row only.

        deletingRow.remove();

        deletingRow = null;

        closeModal(deleteModal);

        updateEmptyState();

        filterAdvisers();

        alert("Demo row removed. No database record was deleted.");
    });

    // ACTIVATE / DEACTIVATE

    function toggleStatus(row) {
        const currentStatus = row.dataset.status || "active";

        const newStatus = currentStatus === "active" ? "inactive" : "active";

        row.dataset.status = newStatus;

        const statusCell = row.children[4];

        if (statusCell) {
            statusCell.replaceChildren(createStatusBadge(newStatus));
        }

        row.classList.toggle("is-inactive", newStatus === "inactive");

        filterAdvisers();
    }

    // TABLE ACTIONS

    tableBody.addEventListener("click", function (event) {
        const button = event.target.closest("button[data-action]");

        if (!button) return;

        const row = button.closest("tr[data-id]");

        if (!row) return;

        switch (button.dataset.action) {
            case "view":
                viewAdviser(row);
                break;

            case "edit":
                editAdviser(row);
                break;

            case "delete":
                deleteAdviser(row);
                break;

            case "status":
                toggleStatus(row);
                break;
        }
    });

    // SEARCH AND FILTER

    function filterAdvisers() {
        const searchValue = searchInput.value.toLowerCase().trim();

        const selectedGrade = gradeFilter.value;

        const selectedStatus = statusFilter.value;

        const rows = tableBody.querySelectorAll("tr[data-id]");

        rows.forEach(function (row) {
            const adviserId = (row.dataset.adviserId || "").toLowerCase();

            const adviserName = (row.dataset.name || "").toLowerCase();

            const adviserEmail = (row.dataset.email || "").toLowerCase();

            const advisoryClass = row.dataset.advisoryClass || "";

            const adviserStatus = row.dataset.status || "active";

            const matchesSearch =
                adviserId.includes(searchValue) ||
                adviserName.includes(searchValue) ||
                adviserEmail.includes(searchValue) ||
                advisoryClass.toLowerCase().includes(searchValue);

            const matchesGrade =
                selectedGrade === "all" ||
                advisoryClass.startsWith(selectedGrade);

            const matchesStatus =
                selectedStatus === "all" || adviserStatus === selectedStatus;

            row.style.display =
                matchesSearch && matchesGrade && matchesStatus ? "" : "none";
        });
    }

    searchInput.addEventListener("input", filterAdvisers);

    gradeFilter.addEventListener("change", filterAdvisers);

    statusFilter.addEventListener("change", filterAdvisers);

    // EMPTY STATE

    function updateEmptyState() {
        const rows = tableBody.querySelectorAll("tr[data-id]");

        const existingEmptyRow = getElement("adviserEmptyRow");

        if (rows.length === 0 && !existingEmptyRow) {
            const row = document.createElement("tr");

            row.className = "empty-state-row";
            row.id = "adviserEmptyRow";

            row.innerHTML = `
                <td colspan="6">
                    <div class="adviser-empty-state">
                        <div class="empty-state-icon">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h3>No Adviser Records Yet</h3>
                        <p>
                            There are no adviser records to display.
                            Add your first adviser to get started.
                        </p>
                        <button type="button"
                            class="empty-state-add-btn"
                            id="emptyAddAdviserBtn">
                            <i class="fa-solid fa-plus"></i>
                            Add Adviser
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        }

        if (rows.length > 0 && existingEmptyRow) {
            existingEmptyRow.remove();
        }
    }

    // CLOSE BUTTONS

    document.querySelectorAll("[data-close]").forEach(function (button) {
        button.addEventListener("click", function () {
            const modal = getElement(button.dataset.close);

            closeModal(modal);

            if (modal === formModal) {
                resetForm();
            }
        });
    });

    // CLOSE WHEN CLICKING OUTSIDE THE MODAL

    document.querySelectorAll(".modal-overlay").forEach(function (modal) {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal(modal);

                if (modal === formModal) {
                    resetForm();
                }
            }
        });
    });

    // ESCAPE KEY

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllModals();

            resetForm();

            deletingRow = null;
        }
    });

    // INITIALIZE

    updateEmptyState();

    console.log("Adviser Management UI loaded successfully.");
});
