// RECENT ACTIVITY MODAL
document.addEventListener('DOMContentLoaded', function () {

  const viewAllBtn = document.getElementById('viewAllGradesBtn');
  const modal = document.getElementById('gradeActivityModal');
  const closeBtn = document.getElementById('closeGradeModal');

  if (!viewAllBtn || !modal || !closeBtn) return;

  const openModal = () => {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // prevent background scroll
  };

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  };

  viewAllBtn.addEventListener('click', openModal);
  closeBtn.addEventListener('click', closeModal);

  // Close when clicking outside the modal box
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  // Close on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
  });

});

// ANNOUCNEMENT MODAL
document.addEventListener('DOMContentLoaded', function () {

  function setupModal(triggerId, modalId, closeId) {
    const trigger = document.getElementById(triggerId);
    const modal = document.getElementById(modalId);
    const closeBtn = document.getElementById(closeId);

    if (!trigger || !modal || !closeBtn) return;

    const openModal = () => {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    };

    trigger.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });
  }

  // GRADE ACTIVITY MODAL
  setupModal('viewAllGradesBtn', 'gradeActivityModal', 'closeGradeModal');

  // ANNOUNCEMENTS MODAL
  setupModal('viewAllAnnouncementsBtn', 'announcementModal', 'closeAnnouncementModal');

});