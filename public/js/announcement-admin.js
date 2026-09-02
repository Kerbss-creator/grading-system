const addAnnouncementBtn = document.getElementById("addAnnouncementBtn");
const announcementModal = document.getElementById("announcementModal");
const closeAnnouncementModal = document.getElementById(
  "closeAnnouncementModal",
);
const cancelAnnouncement = document.getElementById("cancelAnnouncement");
const announcementForm = document.getElementById("announcementForm");
const modalTitle = document.getElementById("modalTitle");
const announcementTitle = document.getElementById("announcementTitle");
const announcementAudience = document.getElementById("announcementAudience");
const announcementMessage = document.getElementById("announcementMessage");
const announcementList = document.getElementById("announcementList");
const filterButtons = document.querySelectorAll(".filter-btn");
const seeAllBtn = document.getElementById("seeAllBtn");

let editingCard = null;
let cardToDelete = null;

/* OPEN CREATE ANNOUNCEMENT MODAL */

addAnnouncementBtn.addEventListener("click", function () {
  editingCard = null;
  modalTitle.textContent = "Create Announcement";
  announcementForm.reset();
  announcementModal.classList.add("show");
  announcementTitle.focus();
});

/* CLOSE CREATE / EDIT MODAL */

function closeAnnouncement() {
  announcementModal.classList.remove("show");

  editingCard = null;

  announcementForm.reset();
}

closeAnnouncementModal.addEventListener("click", closeAnnouncement);

cancelAnnouncement.addEventListener("click", closeAnnouncement);

/* CLOSE MODAL WHEN CLICKING OUTSIDE */

announcementModal.addEventListener("click", function (event) {
  if (event.target === announcementModal) {
    closeAnnouncement();
  }
});

/* FILTER BUTTONS */

filterButtons.forEach(function (button) {
  button.addEventListener("click", function () {
    filterButtons.forEach(function (btn) {
      btn.classList.remove("active");
    });

    button.classList.add("active");

    const filter = button.dataset.filter;

    const cards = document.querySelectorAll(".announcement-card");

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

/* CREATE OR UPDATE ANNOUNCEMENT */

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

/* CREATE NEW ANNOUNCEMENT */

function createAnnouncement(title, audience, message) {
  const card = document.createElement("div");

  card.className = "announcement-card";

  card.dataset.audience = audience;

  card.dataset.title = title;

  card.dataset.message = message;

  card.dataset.date = getCurrentDate();

  card.innerHTML = `

    <div class="announcement-icon">

      <i class="fa-solid ${getAudienceIcon(audience)}"></i>

    </div>


    <div class="announcement-content">

      <div class="announcement-heading">

        <h3>
          ${escapeHTML(title)}
        </h3>

        <span class="announcement-tag ${audience}">
          ${getAudienceName(audience)}
        </span>

      </div>


      <p>
        ${escapeHTML(message)}
      </p>

    </div>


    <div class="announcement-actions">

      <button
        type="button"
        class="edit-btn"
        title="Edit"
      >

        <i class="fa-solid fa-pen"></i>

      </button>


      <button
        type="button"
        class="delete-btn"
        title="Delete"
      >

        <i class="fa-solid fa-trash"></i>

      </button>

    </div>

  `;

  announcementList.prepend(card);

  attachCardEvents(card);
}

/* UPDATE ANNOUNCEMENT */

function updateAnnouncement(card, title, audience, message) {
  card.dataset.title = title;

  card.dataset.audience = audience;

  card.dataset.message = message;

  const icon = card.querySelector(".announcement-icon i");

  icon.className = `fa-solid ${getAudienceIcon(audience)}`;

  const heading = card.querySelector(".announcement-heading h3");

  heading.textContent = title;

  const tag = card.querySelector(".announcement-tag");

  tag.textContent = getAudienceName(audience);

  tag.className = `announcement-tag ${audience}`;

  const paragraph = card.querySelector(".announcement-content p");

  paragraph.textContent = message;
}

/* EDIT ANNOUNCEMENT */

function editAnnouncement(card) {
  editingCard = card;

  modalTitle.textContent = "Edit Announcement";

  announcementTitle.value = card.dataset.title;

  announcementAudience.value = card.dataset.audience;

  announcementMessage.value = card.dataset.message;

  announcementModal.classList.add("show");

  announcementTitle.focus();
}

/* DELETE ANNOUNCEMENT */

function deleteAnnouncement(card) {
  cardToDelete = card;

  const deleteTitle = document.getElementById("deleteAnnouncementTitle");

  deleteTitle.textContent = card.dataset.title;

  deleteModal.classList.add("show");
}

/* ATTACH EVENTS TO ANNOUNCEMENT CARD */

function attachCardEvents(card) {
  const editBtn = card.querySelector(".edit-btn");

  const deleteBtn = card.querySelector(".delete-btn");

  editBtn.addEventListener("click", function () {
    editAnnouncement(card);
  });

  deleteBtn.addEventListener("click", function () {
    deleteAnnouncement(card);
  });
}

/* ATTACH EVENTS TO EXISTING CARDS */

document.querySelectorAll(".announcement-card").forEach(function (card) {
  attachCardEvents(card);
});

/* DELETE MODAL */

const deleteModal = document.getElementById("deleteModal");

const closeDeleteModal = document.getElementById("closeDeleteModal");

const cancelDelete = document.getElementById("cancelDelete");

const confirmDelete = document.getElementById("confirmDelete");

/* CLOSE DELETE MODAL */

function closeDelete() {
  deleteModal.classList.remove("show");

  cardToDelete = null;
}

closeDeleteModal.addEventListener("click", closeDelete);

cancelDelete.addEventListener("click", closeDelete);

/* CLICK OUTSIDE DELETE MODAL */

deleteModal.addEventListener("click", function (event) {
  if (event.target === deleteModal) {
    closeDelete();
  }
});

/* CONFIRM DELETE */

confirmDelete.addEventListener("click", function () {
  if (cardToDelete) {
    cardToDelete.remove();
  }

  closeDelete();
});

/* SEE ALL */

seeAllBtn.addEventListener("click", function () {
  filterButtons.forEach(function (btn) {
    btn.classList.remove("active");
  });

  const allButton = document.querySelector('[data-filter="all"]');

  if (allButton) {
    allButton.classList.add("active");
  }

  document.querySelectorAll(".announcement-card").forEach(function (card) {
    card.classList.remove("hidden");
  });
});

/* VIEW ANNOUNCEMENT MODAL */

const viewModal = document.getElementById("viewModal");

const closeViewModal = document.getElementById("closeViewModal");

const closeViewBtn = document.getElementById("closeViewBtn");

/* OPEN VIEW MODAL */

function openViewModal(card) {
  document.getElementById("viewTitle").textContent = card.dataset.title;

  document.getElementById("viewDate").textContent = card.dataset.date;

  document.getElementById("viewDateDetails").textContent = card.dataset.date;

  document.getElementById("viewAudience").textContent = getAudienceName(
    card.dataset.audience,
  );

  document.getElementById("viewMessage").textContent = card.dataset.message;

  viewModal.classList.add("show");
}

/* CLOSE VIEW MODAL */

function closeView() {
  viewModal.classList.remove("show");
}

closeViewModal.addEventListener("click", closeView);

closeViewBtn.addEventListener("click", closeView);

/* CLICK OUTSIDE VIEW MODAL */

viewModal.addEventListener("click", function (event) {
  if (event.target === viewModal) {
    closeView();
  }
});

/* DOUBLE CLICK ANNOUNCEMENT TO VIEW */

document.querySelectorAll(".announcement-card").forEach(function (card) {
  card.addEventListener("dblclick", function () {
    openViewModal(card);
  });
});

/* GET AUDIENCE NAME */

function getAudienceName(audience) {
  if (audience === "teacher") {
    return "Teachers";
  }

  if (audience === "student") {
    return "Students";
  }

  return "All Users";
}

/* GET AUDIENCE ICON */

function getAudienceIcon(audience) {
  if (audience === "teacher") {
    return "fa-chalkboard-user";
  }

  if (audience === "student") {
    return "fa-user-graduate";
  }

  return "fa-bullhorn";
}

/* GET CURRENT DATE */

function getCurrentDate() {
  const date = new Date();

  return date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

/* ESCAPE HTML */

function escapeHTML(text) {
  const div = document.createElement("div");

  div.textContent = text;

  return div.innerHTML;
}
