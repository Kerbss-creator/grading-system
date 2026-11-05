
document.addEventListener('DOMContentLoaded', function () {

    const tabs = document.querySelectorAll('.recycle-tab');
    const panels = document.querySelectorAll('.recycle-panel');
    const searchInput = document.getElementById('recycleSearch');

    // CATEGORY SWITCHING
    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            const selectedTab = this.dataset.tab;

            tabs.forEach(function (item) {
                item.classList.remove('active');
            });

            panels.forEach(function (panel) {
                panel.classList.remove('active');
            });

            this.classList.add('active');

            const selectedPanel = document.getElementById(
                'panel-' + selectedTab
            );

            if (selectedPanel) {
                selectedPanel.classList.add('active');
            }

            // Reset search when changing categories.
            searchInput.value = '';
            filterRecords('');
        });
    });

    // SEARCH RECORDS
    function filterRecords(keyword) {

        const activePanel = document.querySelector(
            '.recycle-panel.active'
        );

        if (!activePanel) return;

        const rows = activePanel.querySelectorAll(
            '.recycle-table tbody tr:not(.recycle-empty-row)'
        );

        const searchValue = keyword.toLowerCase().trim();

        rows.forEach(function (row) {

            const rowText = row.textContent.toLowerCase();

            row.style.display = rowText.includes(searchValue)
                ? ''
                : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterRecords(this.value);
        });
    }

});
