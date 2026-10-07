document.addEventListener("DOMContentLoaded", function () {
    // Modal elements
    const announcementModal = document.getElementById("announcementModal");
    const viewModal = document.getElementById("viewModal");
    const deleteModal = document.getElementById("deleteModal");

    // Main buttons
    const addAnnouncementBtn = document.getElementById("addAnnouncementBtn");
  
    // Form elements
    const announcementForm = document.getElementById("announcementForm");
    const announcementTitle = document.getElementById("announcementTitle");
    const announcementAudience = document.getElementById(
        "announcementAudience",
    );
    const announcementMessage = document.getElementById("announcementMessage");

    // Modal elements
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

    // View elements
    const viewTitle = document.getElementById("viewTitle");
    const viewDate = document.getElementById("viewDate");
    const viewAudience = document.getElementById("viewAudience");
    const viewDateDetails = document.getElementById("viewDateDetails");
    const viewMessage = document.getElementById("viewMessage");

    // Delete elements
    const deleteAnnouncementTitle = document.getElementById(
        "deleteAnnouncementTitle",
    );

    // Announcement list
    const announcementList = document.getElementById("announcementList");

    // Filter buttons
    const filterButtons = document.querySelectorAll(".filter-btn");

    // Current editing/deleting card
    let editingCard = null;
    let cardToDelete = null;

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

    closeAnnouncementModal.addEventListener("click", closeAnnouncement);

    cancelAnnouncement.addEventListener("click", closeAnnouncement);

    // Filter announcements
    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const filter = this.dataset.filter;

            filterButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            this.classList.add("active");

            const cards =
                announcementList.querySelectorAll(".announcement-card");

            cards.forEach(function (card) {
                const audience = card.dataset.audience;

                if (filter === "all" || audience === filter) {
                    card.classList.remove("hidden");
                } else {
                    card.classList.add("hidden");
                }
            });
        });
    });


    // Create or update announcement
    announcementForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const title = announcementTitle.value.trim();
        const audience = announcementAudience.value;
        const message = announcementMessage.value.trim();

        if (!title || !audience || !message) {
            return;
        }

        if (editingCard) {
            updateAnnouncement(editingCard, title, audience, message);
        } else {
            createAnnouncement(title, audience, message);
        }

        closeAnnouncement();
    });

    // Create announcement card
    function createAnnouncement(title, audience, message) {
        const date = getCurrentDate();

        const card = document.createElement("div");

        card.className = "announcement-card";

        card.dataset.audience = audience;
        card.dataset.title = title;
        card.dataset.message = message;
        card.dataset.date = date;

        card.innerHTML = `
            <div class="announcement-icon">
                <i class="fa-solid ${getAudienceIcon(audience)}"></i>
            </div>

            <div class="announcement-content">

                <div class="announcement-heading">

                    <h3>${escapeHTML(title)}</h3>

                    <span class="announcement-tag ${audience}">
                        ${getAudienceName(audience)}
                    </span>

                </div>

                <p>
                    ${escapeHTML(message)}
                </p>

            </div>

            <div class="announcement-actions">

                <button type="button"
                        class="view-btn"
                        title="View">
                    <i class="fa-solid fa-eye"></i>
                </button>

                <button type="button"
                        class="edit-btn"
                        title="Edit">
                    <i class="fa-solid fa-pen"></i>
                </button>

                <button type="button"
                        class="delete-btn"
                        title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </button>

            </div>
        `;

        announcementList.prepend(card);

        attachCardEvents(card);
    }

    // Update announcement
    function updateAnnouncement(card, title, audience, message) {
        card.dataset.audience = audience;
        card.dataset.title = title;
        card.dataset.message = message;

        card.querySelector(".announcement-icon i").className =
            `fa-solid ${getAudienceIcon(audience)}`;

        card.querySelector(".announcement-heading h3").textContent = title;

        const tag = card.querySelector(".announcement-tag");

        tag.textContent = getAudienceName(audience);

        tag.className = `announcement-tag ${audience}`;

        card.querySelector(".announcement-content p").textContent = message;
    }

    // Edit announcement
    function editAnnouncement(card) {
        editingCard = card;

        modalTitle.textContent = "Edit Announcement";

        announcementTitle.value = card.dataset.title;

        announcementAudience.value = card.dataset.audience;

        announcementMessage.value = card.dataset.message;

        announcementModal.classList.add("show");

        announcementTitle.focus();
    }

    // View announcement
    function viewAnnouncement(card) {
        viewTitle.textContent = card.dataset.title;

        viewDate.textContent = card.dataset.date;

        viewAudience.textContent = getAudienceName(card.dataset.audience);

        viewDateDetails.textContent = card.dataset.date;

        viewMessage.textContent = card.dataset.message;

        viewModal.classList.add("show");
    }

    // Delete announcement
    function deleteAnnouncement(card) {
        cardToDelete = card;

        deleteAnnouncementTitle.textContent = card.dataset.title;

        deleteModal.classList.add("show");
    }

    // Attach card events
    function attachCardEvents(card) {
        const viewButton = card.querySelector(".view-btn");

        const editButton = card.querySelector(".edit-btn");

        const deleteButton = card.querySelector(".delete-btn");

        viewButton.addEventListener("click", function () {
            viewAnnouncement(card);
        });

        editButton.addEventListener("click", function () {
            editAnnouncement(card);
        });

        deleteButton.addEventListener("click", function () {
            deleteAnnouncement(card);
        });
    }

    // Attach events to existing cards
    document.querySelectorAll(".announcement-card").forEach(function (card) {
        attachCardEvents(card);
    });

    // Close view modal
    function closeViewAnnouncement() {
        viewModal.classList.remove("show");
    }

    closeViewModal.addEventListener("click", closeViewAnnouncement);

    closeViewBtn.addEventListener("click", closeViewAnnouncement);

    // Close delete modal
    function closeDeleteAnnouncement() {
        deleteModal.classList.remove("show");

        cardToDelete = null;
    }

    closeDeleteModal.addEventListener("click", closeDeleteAnnouncement);

    cancelDelete.addEventListener("click", closeDeleteAnnouncement);

    // Confirm delete
    confirmDelete.addEventListener("click", function () {
        if (cardToDelete) {
            cardToDelete.remove();

            cardToDelete = null;
        }

        deleteModal.classList.remove("show");
    });

    // Close modal when clicking outside
    announcementModal.addEventListener("click", function (event) {
        if (event.target === announcementModal) {
            closeAnnouncement();
        }
    });

    viewModal.addEventListener("click", function (event) {
        if (event.target === viewModal) {
            closeViewAnnouncement();
        }
    });

    deleteModal.addEventListener("click", function (event) {
        if (event.target === deleteModal) {
            closeDeleteAnnouncement();
        }
    });

    // Get audience name
    function getAudienceName(audience) {
        const names = {
            all: "All Users",
            teacher: "Teachers",
            student: "Students",
        };

        return names[audience] || "All Users";
    }

    // Get audience icon
    function getAudienceIcon(audience) {
        const icons = {
            all: "fa-bullhorn",
            teacher: "fa-solid fa-calendar-check",
            student: "fa-user-graduate",
        };

        return icons[audience] || "fa-bullhorn";
    }

    // Get current date
    function getCurrentDate() {
        const date = new Date();

        return date.toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    }

    // Escape HTML
    function escapeHTML(value) {
        const div = document.createElement("div");

        div.textContent = value;

        return div.innerHTML;
    }
});
