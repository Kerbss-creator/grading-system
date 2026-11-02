document.addEventListener("DOMContentLoaded", () => {
    const gradebook = document.querySelector(".gradebook-page");

    if (!gradebook) {
        return;
    }

    const scoreInputs = Array.from(gradebook.querySelectorAll(".score-input"));
    const learnerRows = Array.from(gradebook.querySelectorAll(".learner-row"));
    const tabs = Array.from(gradebook.querySelectorAll(".gradebook-tab"));

    const termTable = document.getElementById("termGradebook");
    const summaryView = document.getElementById("summaryGradebook");
    const tableTitle = document.getElementById("gradebookTableTitle");
    const termSelect = document.getElementById("gradebookTerm");
    const resetButton = document.getElementById("resetGradebook");
    const clearButton = document.getElementById("clearScores");
    const saveButton = document.getElementById("saveGradebook");
    const unsavedNote = document.getElementById("unsavedNote");

    const weights = {
        ww: 0.2,
        pt: 0.5,
        ex: 0.3,
    };

    const originalValues = scoreInputs.map((input) => input.value);

    function formatNumber(value) {
        return Number.isFinite(value) ? value.toFixed(2) : "—";
    }

    function getGroupScores(row, group) {
        return Array.from(
            row.querySelectorAll(`.score-input[data-group="${group}"]`),
        );
    }

    function calculateGroup(row, group) {
        const inputs = getGroupScores(row, group);

        let earned = 0;
        let possible = 0;
        let hasAnyScore = false;
        let hasInvalidScore = false;

        inputs.forEach((input) => {
            const rawValue = input.value.trim();
            const max = Number(input.dataset.max || input.max || 0);

            if (rawValue === "") {
                return;
            }

            const score = Number(rawValue);

            if (
                !Number.isFinite(score) ||
                score < 0 ||
                score > max ||
                max <= 0
            ) {
                hasInvalidScore = true;
                return;
            }

            earned += score;
            possible += max;
            hasAnyScore = true;
        });

        if (hasInvalidScore || !hasAnyScore || possible === 0) {
            return {
                ps: null,
                ws: null,
            };
        }

        const ps = (earned / possible) * 100;

        return {
            ps,
            ws: ps * weights[group],
        };
    }

    function setCell(row, selector, value) {
        const cell = row.querySelector(selector);

        if (cell) {
            cell.textContent = value === null ? "—" : formatNumber(value);
        }
    }

    function calculateRow(row) {
        const rowInputs = Array.from(row.querySelectorAll(".score-input"));

        let hasInvalid = false;

        rowInputs.forEach((input) => {
            const rawValue = input.value.trim();
            const max = Number(input.dataset.max || input.max || 0);
            const score = Number(rawValue);

            const invalid =
                rawValue !== "" &&
                (!Number.isFinite(score) || score < 0 || score > max);

            input.classList.toggle("is-invalid", invalid);
            input.setAttribute("aria-invalid", invalid ? "true" : "false");

            if (invalid) {
                hasInvalid = true;
            }
        });

        const ww = calculateGroup(row, "ww");
        const pt = calculateGroup(row, "pt");
        const ex = calculateGroup(row, "ex");

        setCell(row, ".ww-ps", ww.ps);
        setCell(row, ".ww-ws", ww.ws);
        setCell(row, ".pt-ps", pt.ps);
        setCell(row, ".pt-ws", pt.ws);
        setCell(row, ".ex-ps", ex.ps);
        setCell(row, ".ex-ws", ex.ws);

        const hasScores = [ww, pt, ex].some((group) => group.ps !== null);

        const initialGrade =
            hasScores && !hasInvalid
                ? (ww.ws || 0) + (pt.ws || 0) + (ex.ws || 0)
                : null;

        setCell(row, ".initial-grade", initialGrade);

        // Term Grade and Descriptor stay pending until the approved
        // DepEd conversion rules are verified and implemented.
        const termGrade = row.querySelector(".term-grade");
        const descriptor = row.querySelector(".descriptor");

        if (termGrade) {
            termGrade.textContent = "Pending";
        }

        if (descriptor) {
            descriptor.replaceChildren();

            const badge = document.createElement("span");
            badge.className = "status-pending";
            badge.textContent = "Pending";

            descriptor.appendChild(badge);
        }
    }

    function calculateAllRows() {
        learnerRows.forEach(calculateRow);
    }

    function markUnsaved() {
        if (unsavedNote) {
            unsavedNote.textContent =
                "Demo scores changed — not saved to the database.";
        }
    }

    scoreInputs.forEach((input) => {
        input.addEventListener("input", () => {
            const row = input.closest(".learner-row");

            if (row) {
                calculateRow(row);
            }

            markUnsaved();
        });

        input.addEventListener("change", () => {
            const max = Number(input.dataset.max || input.max || 0);
            const rawValue = input.value.trim();

            if (rawValue === "") {
                return;
            }

            const score = Number(rawValue);

            if (Number.isFinite(score) && score >= 0 && score <= max) {
                input.value = String(score);
            }
        });
    });

    function setActiveTab(selectedTab, index) {
        tabs.forEach((tab, tabIndex) => {
            const isActive = tab === selectedTab;

            tab.classList.toggle("is-active", isActive);
            tab.setAttribute("aria-selected", isActive ? "true" : "false");
            tab.tabIndex = isActive ? 0 : -1;

            if (tabIndex < 3) {
                tab.textContent = `Term ${tabIndex + 1}`;
            }
        });

        const isSummary = selectedTab.dataset.view === "summary";

        if (termTable) {
            termTable.classList.toggle("is-hidden", isSummary);
        }

        if (summaryView) {
            summaryView.classList.toggle("is-hidden", !isSummary);
        }

        if (tableTitle) {
            tableTitle.textContent = isSummary
                ? "Summary of Grades"
                : `Term ${index + 1} Scores`;
        }

        if (!isSummary && termSelect) {
            termSelect.value = String(index + 1);
        }
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener("click", () => {
            setActiveTab(tab, index);
        });
    });

    if (termSelect) {
        termSelect.addEventListener("change", () => {
            const selectedTerm = Number(termSelect.value);
            const matchingTab = tabs[selectedTerm - 1];

            if (matchingTab) {
                setActiveTab(matchingTab, selectedTerm - 1);
            }
        });
    }

    function clearAllScores(restoreOriginal = false) {
        scoreInputs.forEach((input, index) => {
            input.value = restoreOriginal ? originalValues[index] : "";

            input.classList.remove("is-invalid");
            input.setAttribute("aria-invalid", "false");
        });

        calculateAllRows();
        markUnsaved();
    }

    if (clearButton) {
        clearButton.addEventListener("click", () => {
            clearAllScores(false);
        });
    }

    if (resetButton) {
        resetButton.addEventListener("click", () => {
            clearAllScores(true);

            if (unsavedNote) {
                unsavedNote.textContent =
                    "Demo scores reset. Changes are not saved to the database.";
            }
        });
    }

    if (saveButton) {
        saveButton.addEventListener("click", () => {
            const hasInvalidScore = scoreInputs.some((input) =>
                input.classList.contains("is-invalid"),
            );

            if (hasInvalidScore) {
                window.alert(
                    "May invalid score. Siguraduhing nasa pagitan ng 0 at maximum points ang bawat score.",
                );
                return;
            }

            window.alert(
                "UI prototype pa lamang ito. Hindi pa nakakonekta ang Save Changes sa database.",
            );
        });
    }

    calculateAllRows();
});
