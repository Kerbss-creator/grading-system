document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".review-tab");

    const pendingPanel = document.getElementById("pendingPanel");
    const historyPanel = document.getElementById("historyPanel");

    const searchInput = document.getElementById("reviewSearch");
    const termFilter = document.getElementById("termFilter");

    /*
    |--------------------------------------------------------------------------
    | TAB SWITCHING
    |--------------------------------------------------------------------------
    */

    tabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            const selectedTab = this.dataset.tab;

            tabs.forEach((item) => {
                item.classList.remove("active");
            });

            this.classList.add("active");

            if (selectedTab === "pending") {
                pendingPanel?.classList.remove("hidden");
                historyPanel?.classList.add("hidden");
            } else {
                historyPanel?.classList.remove("hidden");
                pendingPanel?.classList.add("hidden");
            }

            filterSubmissions();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SEARCH AND TERM FILTER
    |--------------------------------------------------------------------------
    */

    function filterTable(tbody) {
        if (!tbody) return;

        const searchValue = searchInput?.value.toLowerCase().trim() || "";

        const selectedTerm = termFilter?.value || "all";

        const rows = Array.from(tbody.querySelectorAll("tr:not(.empty-row)"));

        const emptyRows = tbody.querySelectorAll(".empty-row");

        let visibleCount = 0;

        rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();

            const rowTerm = (row.dataset.term || rowText).toLowerCase();

            const matchesSearch = rowText.includes(searchValue);

            const matchesTerm =
                selectedTerm === "all" ||
                rowTerm.includes(selectedTerm.toLowerCase());

            const shouldShow = matchesSearch && matchesTerm;

            row.style.display = shouldShow ? "" : "none";

            if (shouldShow) {
                visibleCount++;
            }
        });

        /*
         * Show only one empty-state row when no
         * submission matches the current filters.
         */

        emptyRows.forEach((emptyRow, index) => {
            emptyRow.style.display =
                visibleCount === 0 && index === 0 ? "" : "none";
        });
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER BOTH TABLES
    |--------------------------------------------------------------------------
    */

    function filterSubmissions() {
        filterTable(document.getElementById("pendingTableBody"));

        filterTable(document.getElementById("historyTableBody"));
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER EVENTS
    |--------------------------------------------------------------------------
    */

    searchInput?.addEventListener("input", filterSubmissions);

    termFilter?.addEventListener("change", filterSubmissions);

    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    filterSubmissions();
});
