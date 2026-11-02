document.addEventListener("DOMContentLoaded", function () {
    // Modal elements
    const announcementModal = document.getElementById("announcementModal");
    const viewModal = document.getElementById("viewModal");
    const deleteModal = document.getElementById("deleteModal");

    // Main button
    const addAnnouncementBtn = document.getElementById("addAnnouncementBtn");

    // Form elements
    const announcementForm = document.getElementById("announcementForm");
    const announcementTitle = document.getElementById("announcementTitle");
    const announcementAudience = document.getElementById(
        "announcementAudience",
    );
    const announcementMessage = document.getElementById("announcementMessage");
    const modalTitle = document.getElementById("modalTitle");

    // Close buttons
    const closeAnnouncementModal = document.getElementById(
        "closeAnnouncementModal",
    );
    const cancelAnnouncement = document.getElementById("cancelAnnouncement");
    const closeViewModal = document.getElementById("closeViewModal");
    const closeViewBtn = document.getElementById("closeViewBtn");
    const closeDeleteModal = document.getElementById("closeDeleteModal");
    const cancelDelete = document.getElementById("cancelDelete");
    const confirmDelete = document.getElementById("confirmDelete");

    // View modal elements
    const viewTitle = document.getElementById("viewTitle");
    const viewDate = document.getElementById("viewDate");
    const viewAudience = document.getElementById("viewAudience");
    const viewDateDetails = document.getElementById("viewDateDetails");
    const viewMessage = document.getElementById("viewMessage");

    // Delete element
    const deleteAnnouncementTitle = document.getElementById(
        "deleteAnnouncementTitle",
    );

    // Announcement list and filters
    const announcementList = document.getElementById("announcementList");
    const filterButtons = document.querySelectorAll(".filter-btn");
    const emptyState = document.getElementById("announcementEmptyState");

    // Current editing/deleting card
    let editingCard = null;
    let cardToDelete = null;
    let currentFilter = "all";

    const emptyStateHeading = emptyState?.querySelector("h3");
    const emptyStateMessage = emptyState?.querySelector("p");

    // Check required elements
    if (
        !announcementList ||
        !announcementForm ||
        !announcementModal ||
        !addAnnouncementBtn
    ) {
        console.error("Announcement page elements could not be found.");
        return;
    }

    // Update empty state
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

    // Open create announcement modal
    addAnnouncementBtn.addEventListener("click", function () {
        editingCard = null;
        modalTitle.textContent = "Create Announcement";
        announcementForm.reset();
        announcementModal.classList.add("show");
        announcementTitle.focus();
    });

    // Close create/edit modal
    function closeAnnouncement() {
        announcementModal.classList.remove("show");
        editingCard = null;
        announcementForm.reset();
    }

    closeAnnouncementModal?.addEventListener("click", closeAnnouncement);
    cancelAnnouncement?.addEventListener("click", closeAnnouncement);

    // Audience filters
    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            currentFilter = this.dataset.filter || "all";

            filterButtons.forEach((btn) => btn.classList.remove("active"));
            this.classList.add("active");

            applyAnnouncementFilter();
        });
    });

    // Apply audience filter
    function applyAnnouncementFilter() {
        const cards = announcementList.querySelectorAll(".announcement-card");

        cards.forEach(function (card) {
            const matchesFilter =
                currentFilter === "all" ||
                card.dataset.audience === currentFilter;

            card.classList.toggle("hidden", !matchesFilter);
        });

        updateEmptyState();
    }

    // Save new announcement or update existing announcement
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
                // Update content only; preserve the original publication date/time
                const card = editingCard;

                card.dataset.id = saved.id;
                card.dataset.title = saved.title;
                card.dataset.audience = saved.audience;
                card.dataset.message = saved.message;

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
                    tag.className = `announcement-tag ${saved.audience}`;
                    tag.textContent = getAudienceName(saved.audience);
                }

                if (icon) {
                    icon.className = `fa-solid ${getAudienceIcon(saved.audience)}`;
                }

                if (messageElement) {
                    messageElement.textContent = saved.message;
                }

                closeAnnouncement();
                applyAnnouncementFilter();

                showSuccessModal(
                    "Successfully Updated!",
                    "Your announcement has been updated successfully.",
                );
            } else {
                // Create card using the saved database timestamp
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

    // Add Laravel's method spoofing for PUT updates
    function addUpdateMethod(formData) {
        formData.set("_method", "PUT");
        return formData;
    }

    // Create announcement card
    function createAnnouncement(
        title,
        audience,
        message,
        createdAt = null,
        announcementId = null,
    ) {
        // Use the database timestamp. Fallback is only for missing timestamps.
        const published = formatPublishedDateTime(createdAt);

        const card = document.createElement("div");
        card.className = "announcement-card";

        if (announcementId !== null) {
            card.dataset.id = announcementId;
        }

        card.dataset.audience = audience;
        card.dataset.title = title;
        card.dataset.message = message;
        card.dataset.createdAt = createdAt || "";

        card.innerHTML = `
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
                            <span class="published-date">${escapeHTML(published.date)}</span>
                        </span>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            <span class="published-time">${escapeHTML(published.time)}</span>
                        </span>
                    </div>

                    <div class="announcement-actions">
                        <button type="button" class="view-btn" title="View" aria-label="View announcement">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <button type="button" class="edit-btn" title="Edit" aria-label="Edit announcement">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <button type="button" class="delete-btn" title="Delete" aria-label="Delete announcement">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // New announcements appear first
        announcementList.prepend(card);
        attachCardEvents(card);
        applyAnnouncementFilter();
    }

    // Load announcements from the database
    if (Array.isArray(window.savedAnnouncements)) {
        // Controller returns latest first; reverse before prepending cards
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

    // Format original publication date and time
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

    // Edit announcement
    function editAnnouncement(card) {
        editingCard = card;

        modalTitle.textContent = "Edit Announcement";
        announcementTitle.value = card.dataset.title || "";
        announcementAudience.value = card.dataset.audience || "";
        announcementMessage.value = card.dataset.message || "";

        announcementModal.classList.add("show");
        announcementTitle.focus();
    }

    // View announcement
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

    // Open delete confirmation modal
    function deleteAnnouncement(card) {
        cardToDelete = card;

        if (deleteAnnouncementTitle) {
            deleteAnnouncementTitle.textContent =
                card.dataset.title || "this announcement";
        }

        deleteModal?.classList.add("show");
    }

    // Attach View, Edit and Delete button events
    function attachCardEvents(card) {
        card.querySelector(".view-btn")?.addEventListener("click", function () {
            viewAnnouncement(card);
        });

        card.querySelector(".edit-btn")?.addEventListener("click", function () {
            editAnnouncement(card);
        });

        card.querySelector(".delete-btn")?.addEventListener(
            "click",
            function () {
                deleteAnnouncement(card);
            },
        );
    }

    // Attach events to any existing cards rendered by Blade
    announcementList
        .querySelectorAll(".announcement-card")
        .forEach(function (card) {
            attachCardEvents(card);
        });

    // Close view modal
    function closeViewAnnouncement() {
        viewModal?.classList.remove("show");
    }

    closeViewModal?.addEventListener("click", closeViewAnnouncement);
    closeViewBtn?.addEventListener("click", closeViewAnnouncement);

    // Close delete modal
    function closeDeleteAnnouncement() {
        deleteModal?.classList.remove("show");
        cardToDelete = null;
    }

    closeDeleteModal?.addEventListener("click", closeDeleteAnnouncement);
    cancelDelete?.addEventListener("click", closeDeleteAnnouncement);

    // Delete announcement from database
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

    // Close modals when clicking outside
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

    // Audience label
    function getAudienceName(audience) {
        const names = {
            all: "All Users",
            advisers: "Advisers",
            teachers: "Teachers",
            students: "Students",
        };

        return names[audience] || audience;
    }

    // Audience icon
    function getAudienceIcon(audience) {
        const icons = {
            all: "fa-bullhorn",
            advisers: "fa-user-tie",
            teachers: "fa-chalkboard-user",
            students: "fa-user-graduate",
        };

        return icons[audience] || "fa-bullhorn";
    }

    // Escape HTML to prevent user-entered content from becoming markup
    function escapeHTML(value) {
        const div = document.createElement("div");
        div.textContent = value ?? "";
        return div.innerHTML;
    }

    // Initial filter and empty state
    applyAnnouncementFilter();
});

// SUCCESS MODAL
function showSuccessModal(title, message) {
    const modal = document.getElementById("successModal");
    const modalTitle = document.getElementById("successModalTitle");
    const modalMessage = document.getElementById("successModalMessage");

    if (!modal || !modalTitle || !modalMessage) return;

    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modal.classList.add("active");
}

function closeSuccessModal() {
    const modal = document.getElementById("successModal");

    if (modal) {
        modal.classList.remove("active");
    }
}

document
    .getElementById("successModalButton")
    ?.addEventListener("click", closeSuccessModal);
