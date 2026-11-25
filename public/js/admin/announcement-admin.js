document.addEventListener("DOMContentLoaded", function () {
    // =====================================================
    // MODAL ELEMENTS
    // =====================================================

    const announcementModal = document.getElementById("announcementModal");
    const viewModal = document.getElementById("viewModal");
    const deleteModal = document.getElementById("deleteModal");

    // =====================================================
    // MAIN BUTTON
    // =====================================================

    const addAnnouncementBtn = document.getElementById("addAnnouncementBtn");

    // =====================================================
    // FORM ELEMENTS
    // =====================================================

    const announcementForm = document.getElementById("announcementForm");
    const announcementTitle = document.getElementById("announcementTitle");
    const announcementAudience = document.getElementById(
        "announcementAudience",
    );
    const announcementMessage = document.getElementById("announcementMessage");
    const modalTitle = document.getElementById("modalTitle");

    // =====================================================
    // CLOSE BUTTONS
    // =====================================================

    const closeAnnouncementModal = document.getElementById(
        "closeAnnouncementModal",
    );
    const cancelAnnouncement = document.getElementById("cancelAnnouncement");

    const closeViewModal = document.getElementById("closeViewModal");
    const closeViewBtn = document.getElementById("closeViewBtn");

    const closeDeleteModal = document.getElementById("closeDeleteModal");
    const cancelDelete = document.getElementById("cancelDelete");
    const confirmDelete = document.getElementById("confirmDelete");

    // =====================================================
    // VIEW MODAL ELEMENTS
    // =====================================================

    const viewTitle = document.getElementById("viewTitle");
    const viewDate = document.getElementById("viewDate");
    const viewAudience = document.getElementById("viewAudience");
    const viewDateDetails = document.getElementById("viewDateDetails");
    const viewMessage = document.getElementById("viewMessage");

    // =====================================================
    // DELETE MODAL ELEMENTS
    // =====================================================

    const deleteAnnouncementTitle = document.getElementById(
        "deleteAnnouncementTitle",
    );

    // =====================================================
    // ANNOUNCEMENT LIST AND FILTERS
    // =====================================================

    const announcementList = document.getElementById("announcementList");
    const filterButtons = document.querySelectorAll(".filter-btn");
    const emptyState = document.getElementById("announcementEmptyState");

    // =====================================================
    // BULK SELECTION ELEMENTS
    // =====================================================

    const selectAllCheckbox = document.getElementById("selectAllAnnouncements");
    const deleteSelectedBtn = document.getElementById(
        "deleteSelectedAnnouncements",
    );
    const selectedCount = document.getElementById("selectedAnnouncementCount");

    // Stores selected announcement IDs
    const selectedAnnouncementIds = new Set();

    const bulkDeleteModal = document.getElementById("bulkDeleteModal");
    const bulkDeleteMessage = document.getElementById("bulkDeleteMessage");
    const cancelBulkDelete = document.getElementById("cancelBulkDelete");
    const confirmBulkDelete = document.getElementById("confirmBulkDelete");

    let pendingBulkDeleteIds = [];

    // =====================================================
    // CURRENT STATES
    // =====================================================

    let editingCard = null;
    let cardToDelete = null;
    let currentFilter = "all";

    const emptyStateHeading = emptyState?.querySelector("h3");
    const emptyStateMessage = emptyState?.querySelector("p");

    // =====================================================
    // CHECK REQUIRED ELEMENTS
    // =====================================================

    if (
        !announcementList ||
        !announcementForm ||
        !announcementModal ||
        !addAnnouncementBtn
    ) {
        console.error("Announcement page elements could not be found.");
        return;
    }

    // =====================================================
    // EMPTY STATE
    // =====================================================

    function updateEmptyState() {
        if (!emptyState) return;

        const cards = Array.from(
            announcementList.querySelectorAll(".announcement-card"),
        );

        const visibleCards = cards.filter(
            (card) => !card.classList.contains("hidden"),
        );

        if (cards.length === 0 || visibleCards.length === 0) {
            emptyState.style.display = "flex";

            if (emptyStateHeading) {
                emptyStateHeading.textContent =
                    cards.length === 0
                        ? "No Announcement Yet"
                        : "No Announcements Found";
            }

            if (emptyStateMessage) {
                if (cards.length === 0) {
                    emptyStateMessage.textContent =
                        "There are no announcements yet. Create your first announcement to share important school updates.";
                } else {
                    emptyStateMessage.textContent =
                        currentFilter === "all"
                            ? "No announcements are currently available."
                            : `There are no announcements for ${getAudienceName(currentFilter).toLowerCase()} yet.`;
                }
            }

            return;
        }

        emptyState.style.display = "none";
    }

    // =====================================================
    // OPEN CREATE ANNOUNCEMENT MODAL
    // =====================================================

    addAnnouncementBtn.addEventListener("click", function () {
        editingCard = null;

        modalTitle.textContent = "Create Announcement";

        announcementForm.reset();

        announcementModal.classList.add("show");

        announcementTitle.focus();
    });

    // =====================================================
    // CLOSE CREATE / EDIT MODAL
    // =====================================================

    function closeAnnouncement() {
        announcementModal.classList.remove("show");

        editingCard = null;

        announcementForm.reset();
    }

    closeAnnouncementModal?.addEventListener("click", closeAnnouncement);
    cancelAnnouncement?.addEventListener("click", closeAnnouncement);

    // =====================================================
    // AUDIENCE FILTERS
    // =====================================================

    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            currentFilter = this.dataset.filter || "all";

            filterButtons.forEach((btn) => {
                btn.classList.remove("active");
            });

            this.classList.add("active");

            applyAnnouncementFilter();
        });
    });

    // =====================================================
    // APPLY ANNOUNCEMENT FILTER
    // =====================================================

    function applyAnnouncementFilter() {
        const cards = announcementList.querySelectorAll(".announcement-card");

        cards.forEach(function (card) {
            const matchesFilter =
                currentFilter === "all" ||
                card.dataset.audience === currentFilter;

            card.classList.toggle("hidden", !matchesFilter);
        });

        updateSelectionUI();
        updateEmptyState();
    }

    // =====================================================
    // UPDATE BULK SELECTION UI
    // =====================================================

    function updateSelectionUI() {
        const cards = Array.from(
            announcementList.querySelectorAll(".announcement-card"),
        );

        const visibleCards = cards.filter(
            (card) => !card.classList.contains("hidden"),
        );

        const selectedVisibleCards = visibleCards.filter((card) =>
            selectedAnnouncementIds.has(card.dataset.id),
        );

        const totalSelected = selectedAnnouncementIds.size;

        // Selected count
        if (selectedCount) {
            selectedCount.textContent = `${totalSelected} selected`;
        }

        // Enable / disable bulk delete
        if (deleteSelectedBtn) {
            deleteSelectedBtn.disabled = totalSelected === 0;
        }

        // Select All checkbox state
        if (selectAllCheckbox) {
            selectAllCheckbox.checked =
                visibleCards.length > 0 &&
                selectedVisibleCards.length === visibleCards.length;

            selectAllCheckbox.indeterminate =
                selectedVisibleCards.length > 0 &&
                selectedVisibleCards.length < visibleCards.length;

            selectAllCheckbox.disabled = visibleCards.length === 0;
        }

        // Synchronize individual checkboxes
        cards.forEach(function (card) {
            const checkbox = card.querySelector(".announcement-select");

            if (checkbox) {
                checkbox.checked = selectedAnnouncementIds.has(card.dataset.id);
            }
        });
    }

    // =====================================================
    // SELECT ALL VISIBLE ANNOUNCEMENTS
    // =====================================================

    selectAllCheckbox?.addEventListener("change", function () {
        const visibleCards = announcementList.querySelectorAll(
            ".announcement-card:not(.hidden)",
        );

        visibleCards.forEach(function (card) {
            const id = card.dataset.id;

            if (!id) return;

            if (selectAllCheckbox.checked) {
                selectedAnnouncementIds.add(id);
            } else {
                selectedAnnouncementIds.delete(id);
            }
        });

        updateSelectionUI();
    });

    // =====================================================
    // SAVE NEW ANNOUNCEMENT / UPDATE EXISTING ANNOUNCEMENT
    // =====================================================

    announcementForm.addEventListener("submit", async function (event) {
        event.preventDefault();

        const title = announcementTitle.value.trim();
        const audience = announcementAudience.value;
        const message = announcementMessage.value.trim();

        if (!title || !audience || !message) {
            alert("Please complete all announcement fields.");
            return;
        }

        const csrfInput = announcementForm.querySelector(
            'input[name="_token"]',
        );

        if (!csrfInput) {
            alert("CSRF token is missing. Please check the announcement form.");
            return;
        }

        const isEditing = editingCard !== null;

        const announcementId = isEditing ? editingCard.dataset.id : null;

        if (isEditing && !announcementId) {
            alert("Announcement ID is missing. Please refresh the page.");
            return;
        }

        const submitButton = announcementForm.querySelector(
            'button[type="submit"]',
        );

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const formData = new FormData(announcementForm);

            const response = await fetch(
                isEditing
                    ? `/admin/announcement/${encodeURIComponent(announcementId)}`
                    : announcementForm.action,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfInput.value,
                    },
                    body: isEditing ? addUpdateMethod(formData) : formData,
                },
            );

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    alert(Object.values(result.errors).flat().join("\n"));
                } else {
                    alert(result.message || "Failed to save announcement.");
                }

                return;
            }

            const saved = result.announcement;

            if (!saved || !saved.id) {
                alert("The server did not return the announcement details.");
                return;
            }

            if (isEditing) {
                // Update existing announcement
                const card = editingCard;

                card.dataset.id = String(saved.id);
                card.dataset.title = saved.title;
                card.dataset.audience = saved.audience;
                card.dataset.message = saved.message;
                card.dataset.createdAt =
                    saved.created_at || card.dataset.createdAt;

                const heading = card.querySelector(".announcement-heading h3");
                const tag = card.querySelector(".announcement-tag");
                const icon = card.querySelector(".announcement-icon i");

                const messageElement = card.querySelector(
                    ".announcement-content > p",
                );

                if (heading) {
                    heading.textContent = saved.title;
                }

                if (tag) {
                    tag.className = `announcement-tag ${escapeHTML(saved.audience)}`;
                    tag.textContent = getAudienceName(saved.audience);
                }

                if (icon) {
                    icon.className = `fa-solid ${getAudienceIcon(saved.audience)}`;
                }

                if (messageElement) {
                    messageElement.textContent = saved.message;
                }

                const published = formatPublishedDateTime(
                    card.dataset.createdAt,
                );

                const publishedDate = card.querySelector(".published-date");
                const publishedTime = card.querySelector(".published-time");

                if (publishedDate) {
                    publishedDate.textContent = published.date;
                }

                if (publishedTime) {
                    publishedTime.textContent = published.time;
                }

                closeAnnouncement();

                applyAnnouncementFilter();

                showSuccessModal(
                    "Successfully Updated!",
                    "Your announcement has been updated successfully.",
                );
            } else {
                // Create new announcement
                createAnnouncement(
                    saved.title,
                    saved.audience,
                    saved.message,
                    saved.created_at,
                    saved.id,
                );

                closeAnnouncement();

                showSuccessModal(
                    "Successfully Added!",
                    "Your announcement has been saved successfully.",
                );
            }
        } catch (error) {
            console.error("Announcement save error:", error);

            alert("Unable to connect to the server. Please try again.");
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });

    // =====================================================
    // LARAVEL METHOD SPOOFING FOR PUT UPDATES
    // =====================================================

    function addUpdateMethod(formData) {
        formData.set("_method", "PUT");

        return formData;
    }

    // =====================================================
    // CREATE ANNOUNCEMENT CARD
    // PRESERVED ORIGINAL CSS STRUCTURE
    // =====================================================

    function createAnnouncement(
        title,
        audience,
        message,
        createdAt = null,
        announcementId = null,
    ) {
        const published = formatPublishedDateTime(createdAt);

        const card = document.createElement("div");

        card.className = "announcement-card";

        if (announcementId !== null) {
            card.dataset.id = String(announcementId);
        }

        card.dataset.audience = audience;
        card.dataset.title = title;
        card.dataset.message = message;
        card.dataset.createdAt = createdAt || "";

        card.innerHTML = `

            <div class="announcement-select-wrap">
                <input
                    type="checkbox"
                    class="announcement-select"
                    aria-label="Select announcement"
                >
            </div>

            <div class="announcement-icon">
                <i class="fa-solid ${getAudienceIcon(audience)}"></i>
            </div>

            <div class="announcement-content">

                <div class="announcement-heading">

                    <div class="announcement-heading-info">

                        <span class="announcement-tag ${escapeHTML(audience)}">
                            ${escapeHTML(getAudienceName(audience))}
                        </span>

                        <h3>${escapeHTML(title)}</h3>

                    </div>

                    <div class="announcement-menu">

                        <button
                            type="button"
                            class="announcement-menu-toggle"
                            aria-label="Announcement actions"
                            aria-expanded="false"
                            title="More actions"
                        >
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>

                        <div class="announcement-menu-dropdown">

                            <button type="button" class="view-btn">
                                <i class="fa-solid fa-eye"></i>
                                View
                            </button>

                            <button type="button" class="edit-btn">
                                <i class="fa-solid fa-pen"></i>
                                Edit
                            </button>

                            <button type="button" class="delete-btn">
                                <i class="fa-solid fa-trash"></i>
                                Delete
                            </button>

                        </div>

                    </div>

                </div>

                <p>${escapeHTML(message)}</p>

                <div class="announcement-footer">

                    <div class="announcement-publisher">

                        <span>
                            <i class="fa-solid fa-user-shield"></i>
                            Administration
                        </span>

                    </div>

                    <div class="announcement-published">

                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            <span class="published-date">
                                ${escapeHTML(published.date)}
                            </span>
                        </span>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            <span class="published-time">
                                ${escapeHTML(published.time)}
                            </span>
                        </span>

                    </div>

                </div>

            </div>
        `;

        // New announcements appear first
        announcementList.prepend(card);

        attachCardEvents(card);

        applyAnnouncementFilter();
    }

    // =====================================================
    // LOAD ANNOUNCEMENTS FROM DATABASE
    // =====================================================

    if (Array.isArray(window.savedAnnouncements)) {
        [...window.savedAnnouncements]
            .reverse()
            .forEach(function (announcement) {
                createAnnouncement(
                    announcement.title,
                    announcement.audience,
                    announcement.message,
                    announcement.created_at,
                    announcement.id,
                );
            });
    }

    // =====================================================
    // FORMAT PUBLICATION DATE AND TIME
    // =====================================================

    function formatPublishedDateTime(value) {
        const date = value ? new Date(value) : new Date();

        if (Number.isNaN(date.getTime())) {
            return {
                date: "Date unavailable",
                time: "Time unavailable",
            };
        }

        return {
            date: date.toLocaleDateString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
            }),

            time: date.toLocaleTimeString("en-US", {
                hour: "numeric",
                minute: "2-digit",
                hour12: true,
            }),
        };
    }

    // =====================================================
    // EDIT ANNOUNCEMENT
    // =====================================================

    function editAnnouncement(card) {
        editingCard = card;

        modalTitle.textContent = "Edit Announcement";

        announcementTitle.value = card.dataset.title || "";
        announcementAudience.value = card.dataset.audience || "";
        announcementMessage.value = card.dataset.message || "";

        announcementModal.classList.add("show");

        announcementTitle.focus();
    }

    // =====================================================
    // VIEW ANNOUNCEMENT
    // =====================================================

    function viewAnnouncement(card) {
        const published = formatPublishedDateTime(card.dataset.createdAt);

        if (viewTitle) {
            viewTitle.textContent = card.dataset.title || "";
        }

        if (viewDate) {
            viewDate.textContent = `${published.date} · ${published.time}`;
        }

        if (viewAudience) {
            viewAudience.textContent = getAudienceName(card.dataset.audience);
        }

        if (viewDateDetails) {
            viewDateDetails.textContent = `${published.date} at ${published.time}`;
        }

        if (viewMessage) {
            viewMessage.textContent = card.dataset.message || "";
        }

        viewModal?.classList.add("show");
    }

    // =====================================================
    // OPEN INDIVIDUAL DELETE MODAL
    // =====================================================

    function deleteAnnouncement(card) {
        cardToDelete = card;

        if (deleteAnnouncementTitle) {
            deleteAnnouncementTitle.textContent =
                card.dataset.title || "this announcement";
        }

        deleteModal?.classList.add("show");
    }

    // =====================================================
    // OPEN BULK DELETE MODAL
    // =====================================================

    function openBulkDeleteModal(ids) {
        pendingBulkDeleteIds = [...ids];

        if (bulkDeleteMessage) {
            bulkDeleteMessage.textContent = `Are you sure you want to delete ${ids.length} selected announcement(s)?`;
        }

        bulkDeleteModal?.classList.add("show");

        // Disable background scrolling
        document.body.classList.add("modal-open");
    }

    // =====================================================
    // CLOSE BULK DELETE MODAL
    // =====================================================

    function closeBulkDeleteModal() {
        bulkDeleteModal?.classList.remove("show");

        // Restore background scrolling
        document.body.classList.remove("modal-open");

        pendingBulkDeleteIds = [];
    }

    cancelBulkDelete?.addEventListener("click", closeBulkDeleteModal);

    // Close when clicking outside
    bulkDeleteModal?.addEventListener("click", function (event) {
        if (event.target === bulkDeleteModal) {
            closeBulkDeleteModal();
        }
    });

    // =====================================================
    // ATTACH CARD EVENTS
    // KEBAB CLICK IS HANDLED BY EVENT DELEGATION BELOW
    // =====================================================

    function attachCardEvents(card) {
        const checkbox = card.querySelector(".announcement-select");

        // ---------------------------------------------
        // INDIVIDUAL CHECKBOX
        // ---------------------------------------------

        checkbox?.addEventListener("change", function () {
            const id = card.dataset.id;

            if (!id) return;

            if (this.checked) {
                selectedAnnouncementIds.add(id);
            } else {
                selectedAnnouncementIds.delete(id);
            }

            updateSelectionUI();
        });

        // ---------------------------------------------
        // VIEW BUTTON
        // ---------------------------------------------

        card.querySelector(".view-btn")?.addEventListener("click", function () {
            closeCardMenu(card);

            viewAnnouncement(card);
        });

        // ---------------------------------------------
        // EDIT BUTTON
        // ---------------------------------------------

        card.querySelector(".edit-btn")?.addEventListener("click", function () {
            closeCardMenu(card);

            editAnnouncement(card);
        });

        // ---------------------------------------------
        // DELETE BUTTON
        // ---------------------------------------------

        card.querySelector(".delete-btn")?.addEventListener(
            "click",
            function () {
                closeCardMenu(card);

                deleteAnnouncement(card);
            },
        );
    }

    // =====================================================
    // KEBAB MENU - EVENT DELEGATION
    // WORKS FOR EXISTING AND NEW ANNOUNCEMENTS
    // =====================================================

    function closeAllCardMenus() {
        announcementList
            .querySelectorAll(".announcement-menu-dropdown.show")
            .forEach(function (menu) {
                menu.classList.remove("show");
            });

        announcementList
            .querySelectorAll(".announcement-menu-toggle[aria-expanded='true']")
            .forEach(function (button) {
                button.setAttribute("aria-expanded", "false");
            });
    }

    announcementList.addEventListener("click", function (event) {
        const target = event.target;

        const menuToggle =
            target instanceof Element
                ? target.closest(".announcement-menu-toggle")
                : null;

        if (!menuToggle || !announcementList.contains(menuToggle)) {
            return;
        }

        event.stopPropagation();

        const currentMenu = menuToggle
            .closest(".announcement-menu")
            ?.querySelector(".announcement-menu-dropdown");

        if (!currentMenu) return;

        const isOpen = currentMenu.classList.contains("show");

        closeAllCardMenus();

        if (!isOpen) {
            currentMenu.classList.add("show");

            menuToggle.setAttribute("aria-expanded", "true");
        }
    });

    // =====================================================
    // CLOSE A CARD MENU
    // =====================================================

    function closeCardMenu(card) {
        const menu = card.querySelector(".announcement-menu-dropdown");

        const toggle = card.querySelector(".announcement-menu-toggle");

        menu?.classList.remove("show");

        toggle?.setAttribute("aria-expanded", "false");
    }

    // =====================================================
    // CLOSE MENUS WHEN CLICKING OUTSIDE
    // =====================================================

    document.addEventListener("click", function (event) {
        const target = event.target;

        if (
            !(target instanceof Element) ||
            !target.closest(".announcement-menu")
        ) {
            closeAllCardMenus();
        }
    });

    // =====================================================
    // CLOSE MENUS USING ESCAPE
    // =====================================================

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllCardMenus();
        }
    });

    // =====================================================
    // CLOSE VIEW MODAL
    // =====================================================

    function closeViewAnnouncement() {
        viewModal?.classList.remove("show");
    }

    closeViewModal?.addEventListener("click", closeViewAnnouncement);
    closeViewBtn?.addEventListener("click", closeViewAnnouncement);

    // =====================================================
    // CLOSE DELETE MODAL
    // =====================================================

    function closeDeleteAnnouncement() {
        deleteModal?.classList.remove("show");

        cardToDelete = null;
    }

    closeDeleteModal?.addEventListener("click", closeDeleteAnnouncement);
    cancelDelete?.addEventListener("click", closeDeleteAnnouncement);

    // =====================================================
    // DELETE INDIVIDUAL ANNOUNCEMENT
    // =====================================================

    confirmDelete?.addEventListener("click", async function () {
        if (!cardToDelete) return;

        const announcementId = cardToDelete.dataset.id;

        if (!announcementId) {
            alert(
                "Announcement ID is missing. Please refresh the page and try again.",
            );

            return;
        }

        const csrfInput = announcementForm.querySelector(
            'input[name="_token"]',
        );

        if (!csrfInput) {
            alert("CSRF token is missing. Please check the announcement form.");

            return;
        }

        confirmDelete.disabled = true;

        try {
            const response = await fetch(
                `/admin/announcement/${encodeURIComponent(announcementId)}`,
                {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfInput.value,
                    },
                },
            );

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Failed to delete announcement.");

                return;
            }

            // Remove selected ID
            selectedAnnouncementIds.delete(String(announcementId));

            // Remove card only after successful database deletion
            cardToDelete.remove();

            cardToDelete = null;

            deleteModal?.classList.remove("show");

            applyAnnouncementFilter();

            showSuccessModal(
                "Successfully Deleted!",
                "The announcement has been deleted successfully.",
            );
        } catch (error) {
            console.error("Delete announcement error:", error);

            alert("Unable to connect to the server. Please try again.");
        } finally {
            confirmDelete.disabled = false;
        }
    });

    // =====================================================
    // BULK DELETE SELECTED ANNOUNCEMENTS
    // =====================================================

    deleteSelectedBtn?.addEventListener("click", function () {
        const selectedIds = Array.from(selectedAnnouncementIds);

        if (selectedIds.length === 0) return;

        openBulkDeleteModal(selectedIds);
    });

    // =====================================================
    // CONFIRM BULK DELETE
    // =====================================================

    confirmBulkDelete?.addEventListener("click", async function () {
        const selectedIds = [...pendingBulkDeleteIds];

        if (selectedIds.length === 0) return;

        const csrfInput = announcementForm.querySelector(
            'input[name="_token"]',
        );

        if (!csrfInput) {
            alert("CSRF token is missing. Please refresh the page.");
            return;
        }

        confirmBulkDelete.disabled = true;

        const originalContent = confirmBulkDelete.innerHTML;

        confirmBulkDelete.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';

        let deletedCount = 0;
        let failedCount = 0;

        try {
            for (const id of selectedIds) {
                try {
                    const response = await fetch(
                        `/admin/announcement/${encodeURIComponent(id)}`,
                        {
                            method: "DELETE",
                            headers: {
                                Accept: "application/json",
                                "X-CSRF-TOKEN": csrfInput.value,
                            },
                        },
                    );

                    if (!response.ok) {
                        failedCount++;
                        continue;
                    }

                    const card = Array.from(
                        announcementList.querySelectorAll(".announcement-card"),
                    ).find((item) => item.dataset.id === id);

                    card?.remove();

                    selectedAnnouncementIds.delete(id);

                    deletedCount++;
                } catch (error) {
                    console.error(
                        `Failed to delete announcement ${id}:`,
                        error,
                    );

                    failedCount++;
                }
            }

            closeBulkDeleteModal();

            applyAnnouncementFilter();

            if (failedCount === 0) {
                showSuccessModal(
                    "Successfully Deleted!",
                    `${deletedCount} announcement(s) deleted successfully.`,
                );
            } else {
                showSuccessModal(
                    "Bulk Delete Completed",
                    `${deletedCount} deleted. ${failedCount} failed.`,
                );
            }
        } finally {
            confirmBulkDelete.disabled = false;

            confirmBulkDelete.innerHTML = originalContent;

            updateSelectionUI();
        }
    });

    // =====================================================
    // CLOSE MODALS WHEN CLICKING OUTSIDE
    // =====================================================

    announcementModal.addEventListener("click", function (event) {
        if (event.target === announcementModal) {
            closeAnnouncement();
        }
    });

    viewModal?.addEventListener("click", function (event) {
        if (event.target === viewModal) {
            closeViewAnnouncement();
        }
    });

    deleteModal?.addEventListener("click", function (event) {
        if (event.target === deleteModal) {
            closeDeleteAnnouncement();
        }
    });

    // =====================================================
    // AUDIENCE LABEL
    // =====================================================

    function getAudienceName(audience) {
        const names = {
            all: "All Users",
            advisers: "Advisers",
            teachers: "Teachers",
            students: "Students",
        };

        return names[audience] || audience;
    }

    // =====================================================
    // AUDIENCE ICON
    // =====================================================

    function getAudienceIcon(audience) {
        const icons = {
            all: "fa-bullhorn",
            advisers: "fa-user-tie",
            teachers: "fa-chalkboard-user",
            students: "fa-user-graduate",
        };

        return icons[audience] || "fa-bullhorn";
    }

    // =====================================================
    // ESCAPE HTML
    // =====================================================

    function escapeHTML(value) {
        const div = document.createElement("div");

        div.textContent = value ?? "";

        return div.innerHTML;
    }

    // =====================================================
    // ATTACH EVENTS TO EXISTING BLADE CARDS
    // =====================================================

    announcementList
        .querySelectorAll(".announcement-card")
        .forEach(function (card) {
            attachCardEvents(card);
        });

    // =====================================================
    // INITIAL FILTER AND EMPTY STATE
    // =====================================================

    applyAnnouncementFilter();

    updateSelectionUI();
});

// =========================================================
// SUCCESS MODAL
// =========================================================

function showSuccessModal(title, message) {
    const modal = document.getElementById("successModal");

    const modalTitle = document.getElementById("successModalTitle");

    const modalMessage = document.getElementById("successModalMessage");

    if (!modal || !modalTitle || !modalMessage) return;

    modalTitle.textContent = title;

    modalMessage.textContent = message;

    modal.classList.add("active");
}

// =========================================================
// CLOSE SUCCESS MODAL
// =========================================================

function closeSuccessModal() {
    const modal = document.getElementById("successModal");

    if (modal) {
        modal.classList.remove("active");
    }
}

// =========================================================
// SUCCESS MODAL BUTTON
// =========================================================

document
    .getElementById("successModalButton")
    ?.addEventListener("click", closeSuccessModal);
