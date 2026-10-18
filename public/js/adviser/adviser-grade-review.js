
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.review-tab');
    const pendingPanel = document.getElementById('pendingPanel');
    const historyPanel = document.getElementById('historyPanel');

    const searchInput = document.getElementById('reviewSearch');
    const termFilter = document.getElementById('termFilter');

    const modal = document.getElementById('reviewModal');
    const closeButtons = document.querySelectorAll('[data-close-modal]');
    const reviewButtons = document.querySelectorAll('.view-review-btn');

    let activeTab = 'pending';

    // SWITCH BETWEEN PENDING REVIEWS AND REVIEW HISTORY
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activeTab = this.dataset.tab;

            tabs.forEach(function (item) {
                item.classList.toggle('active', item === tab);
            });

            pendingPanel.hidden = activeTab !== 'pending';
            historyPanel.hidden = activeTab !== 'history';

            filterSubmissions();
        });
    });

    // SEARCH AND TERM FILTER
    function filterSubmissions() {
        const searchValue = searchInput.value.trim().toLowerCase();
        const selectedTerm = termFilter.value;

        const activePanel = activeTab === 'pending'
            ? pendingPanel
            : historyPanel;

        const rows = activePanel.querySelectorAll(
            'tbody tr:not(.empty-row)'
        );

        const emptyRow = activePanel.querySelector('.empty-row');
        let visibleCount = 0;

        rows.forEach(function (row) {
            const searchableText = row.dataset.search || '';
            const rowTerm = row.dataset.term || '';

            const matchesSearch = searchableText.includes(searchValue);
            const matchesTerm = selectedTerm === 'all' ||
                rowTerm === selectedTerm;

            const shouldShow = matchesSearch && matchesTerm;

            row.hidden = !shouldShow;

            if (shouldShow) {
                visibleCount++;
            }
        });

        if (emptyRow) {
            emptyRow.hidden = visibleCount > 0;
        }
    }

    searchInput.addEventListener('input', filterSubmissions);
    termFilter.addEventListener('change', filterSubmissions);

    // OPEN REVIEW DETAILS MODAL
    reviewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('modalSubject').textContent =
                this.dataset.subject || '—';

            document.getElementById('modalTeacher').textContent =
                this.dataset.teacher || '—';

            document.getElementById('modalSection').textContent =
                this.dataset.section || '—';

            document.getElementById('modalTerm').textContent =
                this.dataset.term || '—';

            document.getElementById('modalDate').textContent =
                this.dataset.date || '—';

            document.getElementById('modalStatus').textContent =
                this.dataset.status || '—';

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');

            document.body.style.overflow = 'hidden';
        });
    });

    // CLOSE REVIEW DETAILS MODAL
    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';
    }

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    // CLOSE MODAL USING THE ESCAPE KEY
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    // INITIALIZE THE PAGE
    filterSubmissions();
});
