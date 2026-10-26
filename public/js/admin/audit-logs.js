document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("auditModal");
    const modalBody = document.getElementById("auditModalBody");

    if (!modal || !modalBody) return;

    const closeButtons = [
        document.getElementById("closeAuditModal"),
        document.getElementById("closeAuditModalBottom"),
    ];

    // Gumawa ng HTML elements gamit ang textContent
    // upang hindi ma-interpret bilang HTML ang database text.
    function addDetail(label, value) {
        const item = document.createElement("div");
        item.className = "audit-detail-item";

        const title = document.createElement("span");
        title.className = "audit-detail-label";
        title.textContent = label;

        const content = document.createElement("div");
        content.className = "audit-detail-value";
        content.textContent = value ?? "Not available";

        item.append(title, content);
        modalBody.appendChild(item);
    }

    function addJsonDetail(label, value) {
        if (!value || Object.keys(value).length === 0) return;

        addDetail(label, JSON.stringify(value, null, 2));
    }

    // Open a specific audit record
    document.querySelectorAll(".audit-view-btn").forEach((button) => {
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
                addDetail("User", log.user_name);
                addDetail("Role", log.user_role);
                addDetail("Action", log.action);
                addDetail("Module", log.module);
                addDetail("Description", log.description);
                addDetail("IP Address", log.ip_address);

                addJsonDetail("Previous Values", log.old_values);
                addJsonDetail("New Values", log.new_values);
            } catch (error) {
                modalBody.replaceChildren();

                const message = document.createElement("p");
                message.textContent =
                    "Unable to load details. Please refresh and try again.";

                modalBody.appendChild(message);
            }
        });
    });

    function closeModal() {
        modal.hidden = true;
    }

    closeButtons.forEach((button) => {
        button?.addEventListener("click", closeModal);
    });

    modal.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !modal.hidden) {
            closeModal();
        }
    });
});
