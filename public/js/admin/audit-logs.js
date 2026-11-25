document.addEventListener("DOMContentLoaded", () => {
    // =========================================
    // AUTOMATIC AUDIT LOG FILTERS
    // =========================================

    const filterForm = document.getElementById("auditFilterForm");

    if (filterForm) {
        const searchInput = filterForm.querySelector('input[name="search"]');
        const filterSelects = filterForm.querySelectorAll("select");
        const dateInputs = filterForm.querySelectorAll('input[type="date"]');

        let searchTimeout;

        // Search automatically after user stops typing
        if (searchInput) {
            searchInput.addEventListener("input", () => {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(() => {
                    filterForm.requestSubmit();
                }, 400);
            });
        }

        // Automatically filter when a dropdown changes
        filterSelects.forEach((select) => {
            select.addEventListener("change", () => {
                filterForm.requestSubmit();
            });
        });

        // Automatically filter when dates change
        dateInputs.forEach((dateInput) => {
            dateInput.addEventListener("change", () => {
                filterForm.requestSubmit();
            });
        });
    }

    // =========================================
    // AUDIT DETAILS MODAL
    // =========================================

    const modal = document.getElementById("auditModal");
    const modalBody = document.getElementById("auditModalBody");

    if (!modal || !modalBody) return;

    const closeButtons = [
        document.getElementById("closeAuditModal"),
        document.getElementById("closeAuditModalBottom"),
    ];

    function addDetail(label, value) {
        const item = document.createElement("div");
        item.className = "audit-detail-item";

        const title = document.createElement("span");
        title.className = "audit-detail-label";
        title.textContent = label;

        const content = document.createElement("div");
        content.className = "audit-detail-value";

        content.textContent =
            value !== null && value !== undefined && value !== ""
                ? String(value)
                : "Not available";

        item.append(title, content);
        modalBody.appendChild(item);
    }

    function addDetail(label, value) {
        const item = document.createElement("div");
        item.className = "audit-detail-item";

        const title = document.createElement("span");
        title.className = "audit-detail-label";
        title.textContent = label;

        const content = document.createElement("div");
        content.className = "audit-detail-value";

        content.textContent =
            value !== null && value !== undefined && value !== ""
                ? String(value)
                : "Not available";

        item.append(title, content);
        modalBody.appendChild(item);
    }

    function addJsonDetail(label, value) {
        if (
            !value ||
            typeof value !== "object" ||
            Object.keys(value).length === 0
        ) {
            return;
        }

        const item = document.createElement("div");
        item.className = "audit-detail-item audit-json-detail";

        const title = document.createElement("span");
        title.className = "audit-detail-label";
        title.textContent = label;

        const list = document.createElement("div");
        list.className = "audit-json-list";

        Object.entries(value).forEach(([key, val]) => {
            const row = document.createElement("div");
            row.className = "audit-json-row";

            const field = document.createElement("span");
            field.className = "audit-json-key";
            field.textContent = key.replaceAll("_", " ");

            const fieldValue = document.createElement("span");
            fieldValue.className = "audit-json-value";

            let displayValue = val;

            if (val === null || val === undefined || val === "") {
                displayValue = "Not available";
            } else if (typeof val === "object") {
                displayValue = JSON.stringify(val, null, 2);
            } else {
                displayValue = String(val);
            }

            fieldValue.textContent = displayValue;

            row.append(field, fieldValue);
            list.appendChild(row);
        });

        item.append(title, list);
        modalBody.appendChild(item);
    }

    // Match the actual Blade button class
    document.querySelectorAll(".audit-details-button").forEach((button) => {
        button.addEventListener("click", async () => {
            modal.hidden = false;
            modalBody.replaceChildren();

            const loading = document.createElement("p");
            loading.textContent = "Loading audit details...";
            modalBody.appendChild(loading);

            try {
                const response = await fetch(button.dataset.url, {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    credentials: "same-origin",
                });

                if (!response.ok) {
                    throw new Error("Unable to load audit details.");
                }

                const log = await response.json();

                modalBody.replaceChildren();

                addDetail("Date & Time", log.created_at);
                addDetail("User", log.actor_name);
                addDetail("Email", log.actor_email);
                addDetail("User ID", log.actor_id);
                addDetail("Action", log.action);
                addDetail("Module", log.record_type);
                addDetail("Record Reference", log.record_key);
                addDetail("Description", log.details?.description);
                addDetail("IP Address", log.ip_address);

                addJsonDetail("Previous Values", log.before);
                addJsonDetail("New Values", log.after);
                addJsonDetail("Additional Details", log.details);
            } catch (error) {
                modalBody.replaceChildren();

                const message = document.createElement("p");
                message.textContent =
                    "Unable to load details. Please refresh and try again.";

                modalBody.appendChild(message);
            }
        });
    });

    // =========================================
    // CLOSE MODAL
    // =========================================

    function closeModal() {
        modal.hidden = true;
    }

    closeButtons.forEach((button) => {
        button?.addEventListener("click", closeModal);
    });

    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !modal.hidden) {
            closeModal();
        }
    });
});
