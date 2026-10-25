document.addEventListener("DOMContentLoaded", function () {
    // =====================================
    // ELEMENTS
    // =====================================

    const tableBody = document.getElementById("adminTableBody");
    const searchInput = document.getElementById("adminSearch");
    const statusFilter = document.getElementById("adminStatusFilter");
    const adminForm = document.getElementById("adminForm");

    const formModal = document.getElementById("adminFormModal");
    const viewModal = document.getElementById("viewAdminModal");
    const deleteModal = document.getElementById("deleteAdminModal");
    const statusModal = document.getElementById("adminStatusModal");

    let editingRow = null;
    let selectedDeleteRow = null;
    let selectedStatusRow = null;
    let nextDemoId = 1;

    // =====================================
    // MODAL FUNCTIONS
    // =====================================

    function openModal(modal) {
        if (!modal) return;

        modal.classList.add("show");
        document.body.style.overflow = "hidden";
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("show");

        if (!document.querySelector(".admin-modal-overlay.show")) {
            document.body.style.overflow = "";
        }
    }

    function closeAllModals() {
        document
            .querySelectorAll(".admin-modal-overlay.show")
            .forEach(function (modal) {
                modal.classList.remove("show");
            });

        document.body.style.overflow = "";
    }

    document.querySelectorAll("[data-close]").forEach(function (button) {
        button.addEventListener("click", function () {
            closeModal(document.getElementById(button.dataset.close));
        });
    });

    document
        .querySelectorAll(".admin-modal-overlay")
        .forEach(function (overlay) {
            overlay.addEventListener("click", function (event) {
                if (event.target === overlay) {
                    closeModal(overlay);
                }
            });
        });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllModals();
        }
    });

    // =====================================
    // TABLE HELPERS
    // =====================================

    function getAdminRows() {
        return Array.from(tableBody.querySelectorAll("tr[data-id]"));
    }

    function getFullName(row) {
        return [
            row.dataset.firstName,
            row.dataset.middleName,
            row.dataset.lastName,
        ]
            .filter(Boolean)
            .join(" ");
    }

    function getFullNameFromData(data) {
        return [data.firstName, data.middleName, data.lastName]
            .filter(Boolean)
            .join(" ");
    }

    // =====================================
    // EMPTY STATE
    // =====================================

    function updateEmptyState() {
        const emptyRow = document.getElementById("adminEmptyRow");

        const visibleRows = getAdminRows().filter(function (row) {
            return row.style.display !== "none";
        });

        if (emptyRow) {
            emptyRow.style.display =
                visibleRows.length === 0 ? "table-row" : "none";
        }
    }

    // =====================================
    // UPDATE ROW STATUS
    // =====================================

    function updateStatusDisplay(row) {
        const status = row.dataset.status;
        const statusCell = row.cells[4];
        const statusButton = row.querySelector('[data-action="status"]');

        row.classList.toggle("is-inactive", status === "inactive");

        statusCell.replaceChildren();

        const badge = document.createElement("span");
        badge.className = "admin-status " + status;
        badge.textContent = status === "active" ? "Active" : "Inactive";

        statusCell.appendChild(badge);

        if (statusButton) {
            const icon = statusButton.querySelector("i");

            if (icon) {
                icon.className =
                    status === "active"
                        ? "fa-solid fa-user-slash"
                        : "fa-solid fa-user-check";
            }

            statusButton.title =
                status === "active" ? "Deactivate Admin" : "Activate Admin";
        }
    }

    // =====================================
    // CREATE ACTION BUTTONS
    // =====================================

    function createActionButton(action, className, iconName, title) {
        const button = document.createElement("button");

        button.type = "button";
        button.className = "action-btn " + className;
        button.dataset.action = action;
        button.title = title;
        button.setAttribute("aria-label", title);

        const icon = document.createElement("i");
        icon.className = "fa-solid " + iconName;

        button.appendChild(icon);

        return button;
    }

    // =====================================
    // ADD / UPDATE TABLE ROW
    // =====================================

    function updateRow(row, data) {
        row.dataset.adminId = data.adminId;
        row.dataset.firstName = data.firstName;
        row.dataset.middleName = data.middleName;
        row.dataset.lastName = data.lastName;
        row.dataset.email = data.email;
        row.dataset.status = data.status;

        row.replaceChildren();

        // Admin ID
        const idCell = document.createElement("td");
        idCell.textContent = data.adminId;
        row.appendChild(idCell);

        // Full name
        const nameCell = document.createElement("td");
        nameCell.textContent = getFullNameFromData(data);
        row.appendChild(nameCell);

        // Email
        const emailCell = document.createElement("td");
        emailCell.textContent = data.email;
        row.appendChild(emailCell);

        // Role
        const roleCell = document.createElement("td");
        const roleBadge = document.createElement("span");

        roleBadge.className = "admin-role";
        roleBadge.textContent = "Admin";

        roleCell.appendChild(roleBadge);
        row.appendChild(roleCell);

        // Status
        const statusCell = document.createElement("td");
        const statusBadge = document.createElement("span");

        statusBadge.className = "admin-status " + data.status;
        statusBadge.textContent =
            data.status === "active" ? "Active" : "Inactive";

        statusCell.appendChild(statusBadge);
        row.appendChild(statusCell);

        // Actions
        const actionCell = document.createElement("td");
        const actionContainer = document.createElement("div");

        actionContainer.className = "admin-actions";

        actionContainer.appendChild(
            createActionButton("view", "view-btn", "fa-eye", "View Admin"),
        );

        actionContainer.appendChild(
            createActionButton("edit", "edit-btn", "fa-pen", "Edit Admin"),
        );

        actionContainer.appendChild(
            createActionButton(
                "delete",
                "delete-btn",
                "fa-trash",
                "Delete Admin",
            ),
        );

        actionContainer.appendChild(
            createActionButton(
                "status",
                "status-btn",
                data.status === "active" ? "fa-user-slash" : "fa-user-check",
                data.status === "active"
                    ? "Deactivate Admin"
                    : "Activate Admin",
            ),
        );

        actionCell.appendChild(actionContainer);
        row.appendChild(actionCell);

        row.classList.toggle("is-inactive", data.status === "inactive");
    }

    // =====================================
    // OPEN ADD ADMIN MODAL
    // =====================================

    function openAddModal() {
        editingRow = null;
        adminForm.reset();

        document.getElementById("adminFormTitle").textContent = "Add Admin";

        document.getElementById("adminFormDescription").textContent =
            "Enter the administrator's information.";

        document.getElementById("saveAdminText").textContent = "Add Admin";

        const adminIdInput = document.getElementById("adminId");
        adminIdInput.disabled = false;
        adminIdInput.value = "";

        document.getElementById("adminAccountStatus").value = "active";

        openModal(formModal);
    }

    document
        .getElementById("addAdminBtn")
        .addEventListener("click", openAddModal);

    document
        .getElementById("emptyAddAdminBtn")
        .addEventListener("click", openAddModal);

    // =====================================
    // SAVE ADMIN: ADD / EDIT
    // =====================================

    adminForm.addEventListener("submit", function (event) {
        event.preventDefault();

        if (!adminForm.reportValidity()) return;

        const data = {
            adminId: document.getElementById("adminId").value.trim(),
            firstName: document.getElementById("adminFirstName").value.trim(),
            middleName: document.getElementById("adminMiddleName").value.trim(),
            lastName: document.getElementById("adminLastName").value.trim(),
            email: document.getElementById("adminEmail").value.trim(),
            status: document.getElementById("adminAccountStatus").value,
        };

        // Check duplicate ID or email in the current demo table.
        const duplicate = getAdminRows().some(function (row) {
            if (editingRow && row === editingRow) {
                return false;
            }

            return (
                row.dataset.adminId.toLowerCase() ===
                    data.adminId.toLowerCase() ||
                row.dataset.email.toLowerCase() === data.email.toLowerCase()
            );
        });

        if (duplicate) {
            alert("Admin ID or email address already exists in this list.");
            return;
        }

        if (editingRow) {
            updateRow(editingRow, data);
        } else {
            const row = document.createElement("tr");

            row.dataset.id = "demo-" + nextDemoId++;

            updateRow(row, data);

            const emptyRow = document.getElementById("adminEmptyRow");
            tableBody.insertBefore(row, emptyRow);
        }

        closeModal(formModal);
        adminForm.reset();

        editingRow = null;

        filterAdmins();
    });

    // HANDLE TABLE ACTIONS
    tableBody.addEventListener("click", function (event) {
        const target = event.target;

        if (!(target instanceof Element)) return;

        const button = target.closest("button[data-action]");

        if (!button || !tableBody.contains(button)) return;

        const row = button.closest("tr[data-id]");

        if (!row) return;

        const action = button.dataset.action;

        // VIEW
        if (action === "view") {
            document.getElementById("viewAdminId").textContent =
                row.dataset.adminId;

            document.getElementById("viewAdminName").textContent =
                getFullName(row);

            document.getElementById("viewAdminEmail").textContent =
                row.dataset.email;

            const statusElement = document.getElementById("viewAdminStatus");
            const isActive = row.dataset.status === "active";

            statusElement.textContent = isActive ? "Active" : "Inactive";
            statusElement.style.color = isActive ? "#168345" : "#c44747";

            openModal(viewModal);
        }

        // EDIT
        if (action === "edit") {
            editingRow = row;

            document.getElementById("adminFormTitle").textContent =
                "Edit Admin";

            document.getElementById("adminFormDescription").textContent =
                "Update the administrator's information.";

            document.getElementById("saveAdminText").textContent =
                "Save Changes";

            const adminIdInput = document.getElementById("adminId");

            adminIdInput.value = row.dataset.adminId;
            adminIdInput.disabled = true;

            document.getElementById("adminFirstName").value =
                row.dataset.firstName;

            document.getElementById("adminMiddleName").value =
                row.dataset.middleName;

            document.getElementById("adminLastName").value =
                row.dataset.lastName;

            document.getElementById("adminEmail").value = row.dataset.email;

            document.getElementById("adminAccountStatus").value =
                row.dataset.status;

            openModal(formModal);
        }

        // DELETE
        if (action === "delete") {
            selectedDeleteRow = row;

            document.getElementById("deleteAdminName").textContent =
                getFullName(row);

            openModal(deleteModal);
        }

        // ACTIVATE / DEACTIVATE
        if (action === "status") {
            selectedStatusRow = row;

            const isActive = row.dataset.status === "active";
            const nextStatus = isActive ? "inactive" : "active";

            document.getElementById("adminStatusTitle").textContent = isActive
                ? "Deactivate Admin"
                : "Activate Admin";

            document.getElementById("adminStatusHeading").textContent = isActive
                ? "Deactivate this admin?"
                : "Activate this admin?";

            document.getElementById("adminStatusMessage").textContent = isActive
                ? "This admin will be marked as inactive in this demo."
                : "This admin will be marked as active in this demo.";

            const statusIcon = document.getElementById("adminStatusIcon");

            statusIcon.classList.toggle("inactive", nextStatus === "inactive");

            statusIcon.replaceChildren();

            const icon = document.createElement("i");

            icon.className =
                nextStatus === "active"
                    ? "fa-solid fa-user-check"
                    : "fa-solid fa-user-slash";

            statusIcon.appendChild(icon);

            const confirmButton = document.getElementById("confirmAdminStatus");

            confirmButton.textContent = isActive ? "Deactivate" : "Activate";
            confirmButton.dataset.nextStatus = nextStatus;

            openModal(statusModal);
        }
    });

    // =====================================
    // CONFIRM DELETE
    // =====================================

    document
        .getElementById("confirmDeleteAdmin")
        .addEventListener("click", function () {
            if (!selectedDeleteRow) return;

            selectedDeleteRow.remove();
            selectedDeleteRow = null;

            closeModal(deleteModal);
            filterAdmins();
        });

    // =====================================
    // CONFIRM STATUS CHANGE
    // =====================================

    document
        .getElementById("confirmAdminStatus")
        .addEventListener("click", function () {
            if (!selectedStatusRow) return;

            selectedStatusRow.dataset.status = this.dataset.nextStatus;

            updateStatusDisplay(selectedStatusRow);

            selectedStatusRow = null;

            closeModal(statusModal);
            filterAdmins();
        });

    
    // SEARCH AND STATUS FILTER
    

    function filterAdmins() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const selectedStatus = statusFilter.value;

        getAdminRows().forEach(function (row) {
            const searchableText = [
                row.dataset.adminId,
                getFullName(row),
                row.dataset.email,
            ]
                .join(" ")
                .toLowerCase();

            const matchesSearch = searchableText.includes(searchTerm);

            const matchesStatus =
                selectedStatus === "all" ||
                row.dataset.status === selectedStatus;

            row.style.display = matchesSearch && matchesStatus ? "" : "none";
        });

        updateEmptyState();
    }

    searchInput.addEventListener("input", filterAdmins);
    statusFilter.addEventListener("change", filterAdmins);

    
    // INITIALIZE EMPTY STATE
    
    filterAdmins();
});
