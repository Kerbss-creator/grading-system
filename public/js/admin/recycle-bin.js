document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".recycle-tab");
    const panels = document.querySelectorAll(".recycle-panel");
    const searchInput = document.getElementById("recycleSearch");
    const tabsContainer = document.querySelector(".recycle-tabs");

    const validTabs = ["students", "faculty", "admins", "users"];

    // ==========================================
    // REMEMBER ACTIVE TAB
    // ==========================================

    function updateTabInputs(selectedTab) {
        panels.forEach(function (panel) {
            const form = panel.querySelector('form[id^="bulk-delete-"]');

            if (!form) return;

            const tabInput = form.querySelector('input[name="tab"]');

            if (tabInput) {
                tabInput.value = selectedTab;
            }
        });
    }

    function activateTab(selectedTab, updateUrl = true) {
        if (!validTabs.includes(selectedTab)) {
            selectedTab = "students";
        }

        tabs.forEach(function (tab) {
            tab.classList.toggle("active", tab.dataset.tab === selectedTab);
        });

        panels.forEach(function (panel) {
            panel.classList.toggle(
                "active",
                panel.id === "panel-" + selectedTab,
            );
        });

        if (searchInput) {
            searchInput.value = "";
        }

        updateTabInputs(selectedTab);
        filterRecords("");

        const selectedPanel = document.getElementById("panel-" + selectedTab);

        if (selectedPanel) {
            updateSelection(selectedPanel);
        }

        // Update URL without refreshing the page.
        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set("tab", selectedTab);

            window.history.replaceState({}, "", url);
        }
    }

    // Get tab from URL or Blade default.
    const urlParams = new URLSearchParams(window.location.search);

    const initialTab =
        urlParams.get("tab") ||
        (tabsContainer ? tabsContainer.dataset.defaultTab : "students");

    activateTab(initialTab, false);

    // ==========================================
    // CATEGORY SWITCHING
    // ==========================================

    tabs.forEach(function (tab) {
        tab.addEventListener("click", function () {
            activateTab(this.dataset.tab, true);
        });
    });

    // ==========================================
    // SEARCH RECORDS
    // ==========================================

    function filterRecords(keyword) {
        const activePanel = document.querySelector(".recycle-panel.active");

        if (!activePanel) return;

        const rows = activePanel.querySelectorAll(
            ".recycle-table tbody tr:not(.recycle-empty-row)",
        );

        const searchValue = keyword.toLowerCase().trim();

        rows.forEach(function (row) {
            const rowText = row.textContent.toLowerCase();

            row.style.display = rowText.includes(searchValue) ? "" : "none";
        });

        updateSelection(activePanel);
    }

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            filterRecords(this.value);
        });
    }

    // ==========================================
    // GET VISIBLE RECORD CHECKBOXES
    // ==========================================

    function getVisibleCheckboxes(panel) {
        return Array.from(
            panel.querySelectorAll("[data-record-checkbox]"),
        ).filter(function (checkbox) {
            const row = checkbox.closest("tr");

            return row && row.style.display !== "none";
        });
    }

    // ==========================================
    // UPDATE SELECTION COUNT AND BUTTONS
    // ==========================================

    function updateSelection(panel) {
        const checkboxes = panel.querySelectorAll("[data-record-checkbox]");

        const selectedCount = panel.querySelector("[data-selected-count]");

        const bulkDeleteButton = panel.querySelector("[data-bulk-delete]");

        const bulkRestoreButton = panel.querySelector("[data-bulk-restore]");

        const selectAllCheckbox = panel.querySelector("[data-select-all]");

        const selected = Array.from(checkboxes).filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        if (selectedCount) {
            selectedCount.textContent = selected + " selected";
        }

        // Enable Bulk Delete.
        if (bulkDeleteButton) {
            bulkDeleteButton.disabled = selected === 0;
        }

        // Enable Bulk Restore.
        if (bulkRestoreButton) {
            bulkRestoreButton.disabled = selected === 0;
        }

        // Update Select All state.
        if (selectAllCheckbox) {
            const visibleCheckboxes = getVisibleCheckboxes(panel);

            const visibleSelected = visibleCheckboxes.filter(
                function (checkbox) {
                    return checkbox.checked;
                },
            ).length;

            selectAllCheckbox.checked =
                visibleCheckboxes.length > 0 &&
                visibleSelected === visibleCheckboxes.length;

            selectAllCheckbox.indeterminate =
                visibleSelected > 0 &&
                visibleSelected < visibleCheckboxes.length;

            selectAllCheckbox.disabled = visibleCheckboxes.length === 0;
        }
    }

    // ==========================================
    // INDIVIDUAL CHECKBOX AND BULK ACTIONS
    // ==========================================

    panels.forEach(function (panel) {
        const checkboxes = panel.querySelectorAll("[data-record-checkbox]");

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                updateSelection(panel);
            });
        });

        // ======================================
        // SELECT ALL CHECKBOX
        // ======================================

        const selectAllCheckbox = panel.querySelector("[data-select-all]");

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener("change", function () {
                const visibleCheckboxes = getVisibleCheckboxes(panel);

                visibleCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = this.checked;
                }, this);

                updateSelection(panel);
            });
        }

        // ======================================
        // BULK RESTORE SELECTED
        // ======================================

        const bulkRestoreButton = panel.querySelector("[data-bulk-restore]");

        if (bulkRestoreButton) {
            bulkRestoreButton.addEventListener("click", async function () {
                const selectedCheckboxes = Array.from(
                    panel.querySelectorAll("[data-record-checkbox]:checked"),
                );

                if (selectedCheckboxes.length === 0) return;

                const confirmed = window.confirm(
                    "Restore " +
                        selectedCheckboxes.length +
                        " selected record(s)?",
                );

                if (!confirmed) return;

                const activeTab = panel.id.replace("panel-", "");

                bulkRestoreButton.disabled = true;

                const bulkDeleteButton =
                    panel.querySelector("[data-bulk-delete]");

                if (bulkDeleteButton) {
                    bulkDeleteButton.disabled = true;
                }

                let restoredCount = 0;
                let failedCount = 0;

                for (const checkbox of selectedCheckboxes) {
                    const row = checkbox.closest("tr");

                    const restoreForm = row
                        ? row.querySelector(".restore-record-form")
                        : null;

                    if (!restoreForm) {
                        failedCount++;
                        continue;
                    }

                    try {
                        const formData = new FormData(restoreForm);

                        const response = await fetch(restoreForm.action, {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                                Accept: "application/json",
                            },
                            credentials: "same-origin",
                        });

                        if (!response.ok) {
                            throw new Error(
                                "Restore request failed: " + response.status,
                            );
                        }

                        restoredCount++;
                    } catch (error) {
                        console.error("Unable to restore record:", error);

                        failedCount++;
                    }
                }

                if (restoredCount > 0) {
                    const url = new URL(window.location.href);
                    url.searchParams.set("tab", activeTab);

                    if (failedCount > 0) {
                        alert(
                            restoredCount +
                                " record(s) restored. " +
                                failedCount +
                                " record(s) failed to restore.",
                        );
                    }

                    // Refresh and retain the active category.
                    window.location.reload();
                    return;
                }

                alert(
                    "Unable to restore the selected records. Please try again.",
                );

                updateSelection(panel);
            });
        }

        // Initialize each category.
        updateSelection(panel);
    });

    // ==========================================
    // PRESERVE TAB WHEN BULK DELETE IS SUBMITTED
    // ==========================================

    panels.forEach(function (panel) {
        const bulkDeleteForm = panel.querySelector('form[id^="bulk-delete-"]');

        if (bulkDeleteForm) {
            bulkDeleteForm.addEventListener("submit", function () {
                const activeTab = panel.id.replace("panel-", "");

                const tabInput =
                    bulkDeleteForm.querySelector('input[name="tab"]');

                if (tabInput) {
                    tabInput.value = activeTab;
                }
            });
        }
    });
});
