document.addEventListener("DOMContentLoaded", () => {
    const gradebook = document.querySelector(".gradebook-page");

    if (!gradebook) return;

    // ==========================================
    // MAIN ELEMENTS
    // ==========================================

    const termTable = document.getElementById("termGradebook");
    const summaryView = document.getElementById("summaryGradebook");

    if (!termTable) return;

    const getLearnerRows = () =>
        Array.from(termTable.querySelectorAll(".learner-row"));

    const getScoreInputs = () =>
        Array.from(termTable.querySelectorAll(".score-input"));

    const tabs = Array.from(gradebook.querySelectorAll(".gradebook-tab"));

    const tableTitle = document.getElementById("gradebookTableTitle");
    const termSelect = document.getElementById("gradebookTerm");

    const resetButton = document.getElementById("resetGradebook");
    const clearButton = document.getElementById("clearScores");
    const saveButton = document.getElementById("saveGradebook");
    const unsavedNote = document.getElementById("unsavedNote");

    // ==========================================
    // LOAD CONFIGURATION FROM BLADE
    // ==========================================

    let assessmentDefinitions = [];
    let weights = {};

    try {
        assessmentDefinitions = JSON.parse(
            gradebook.dataset.assessments || "[]",
        );

        const rawWeights = JSON.parse(gradebook.dataset.gradingWeights || "{}");

        weights = {
            ww: Number(rawWeights.ww) / 100,
            pt: Number(rawWeights.pt) / 100,
            ex: Number(rawWeights.ex) / 100,
        };
    } catch (error) {
        console.error("Unable to read gradebook configuration:", error);

        window.alert(
            "Gradebook configuration could not be loaded. Please refresh the page.",
        );

        return;
    }

    // Normalize assessment configuration from Blade.
    assessmentDefinitions = assessmentDefinitions.map((assessment) => ({
        ...assessment,
        label: String(assessment.label ?? "")
            .trim()
            .toUpperCase(),
        group: String(assessment.group ?? "")
            .trim()
            .toLowerCase(),
        max: Number(assessment.max),
        weight:
            assessment.weight !== undefined
                ? Number(assessment.weight)
                : undefined,
    }));

    const assessmentCount = assessmentDefinitions.length;

    const validGroups = ["ww", "pt", "ex"];

    const validAssessments =
        assessmentCount > 0 &&
        assessmentDefinitions.every((assessment) => {
            return (
                assessment.label &&
                validGroups.includes(assessment.group) &&
                Number.isFinite(assessment.max) &&
                assessment.max > 0
            );
        });

    const validWeights =
        Object.values(weights).every(
            (weight) => Number.isFinite(weight) && weight >= 0,
        ) && Math.abs(weights.ww + weights.pt + weights.ex - 1) < 0.0001;

    const renderedAssessmentCount = termTable.querySelectorAll(
        ".assessment-heading[data-assessment-index]",
    ).length;

    if (
        !validAssessments ||
        !validWeights ||
        renderedAssessmentCount !== assessmentCount
    ) {
        console.error("Invalid gradebook configuration:", {
            assessmentDefinitions,
            weights,
            renderedAssessmentCount,
            assessmentCount,
        });

        window.alert(
            "The gradebook assessment configuration is incomplete or invalid.",
        );

        return;
    }

    // ==========================================
    // ASSESSMENT LOOKUP
    // ==========================================

    const assessmentIndexes = {
        ww: assessmentDefinitions
            .map((item, index) => (item.group === "ww" ? index : null))
            .filter((index) => index !== null),

        pt: assessmentDefinitions
            .map((item, index) => (item.group === "pt" ? index : null))
            .filter((index) => index !== null),

        ex: assessmentDefinitions
            .map((item, index) => (item.group === "ex" ? index : null))
            .filter((index) => index !== null),
    };

    // Required EX assessments: SA 20%, SB 20%, SC 20%, TE 40%.

    const requiredExamLabels = ["SA", "SB", "SC", "TE"];
    const requiredExamWeights = [20, 20, 20, 40];

    const examConfigurationIsValid =
        assessmentIndexes.ex.length === 4 &&
        requiredExamLabels.every((label, index) => {
            const assessment =
                assessmentDefinitions[assessmentIndexes.ex[index]];

            return (
                assessment.label === label &&
                assessment.weight === requiredExamWeights[index]
            );
        });

    if (
        assessmentIndexes.ww.length !== 4 ||
        assessmentIndexes.pt.length !== 2 ||
        !examConfigurationIsValid
    ) {
        console.error("Unexpected assessment structure:", {
            assessments: assessmentDefinitions,
            assessmentIndexes,
        });

        window.alert(
            "The assessment structure must contain 4 WW, 2 PT, SA, SB, SC, and TE.",
        );

        return;
    }

    // ==========================================
    // TERM DATA MANAGEMENT
    // ==========================================

    let activeTerm = 1;

    function captureTableData() {
        return getScoreInputs().map((input) => ({
            value: input.value,
            max: input.dataset.max || input.max || "0",
        }));
    }

    const originalTermData = captureTableData();

    function createBlankTermData() {
        return originalTermData.map((item) => ({
            value: "",
            max: item.max,
        }));
    }

    const termData = {
        1: originalTermData.map((item) => ({ ...item })),
        2: createBlankTermData(),
        3: createBlankTermData(),
    };

    function saveCurrentTermData() {
        termData[activeTerm] = getScoreInputs().map((input) => ({
            value: input.value,
            max: input.dataset.max || input.max || "0",
        }));
    }

    function getAssessmentData(index) {
        return termData[activeTerm]?.[index] || null;
    }

    function updateAssessmentHeaders() {
        const headers = termTable.querySelectorAll(
            ".assessment-heading[data-assessment-index]",
        );

        headers.forEach((header) => {
            const index = Number(header.dataset.assessmentIndex);
            const assessment = getAssessmentData(index);

            if (!assessment) return;

            let maxLabel = header.querySelector("small");

            if (!maxLabel) {
                maxLabel = document.createElement("small");
                header.appendChild(maxLabel);
            }

            maxLabel.textContent = `Max ${assessment.max}`;
        });
    }

    function loadTermData(term) {
        const inputs = getScoreInputs();
        const data = termData[term];

        if (!data) return;

        inputs.forEach((input, index) => {
            const item = data[index];

            if (!item) return;

            input.value = item.value;
            input.dataset.max = item.max;
            input.max = item.max;

            input.classList.remove("is-invalid");
            input.setAttribute("aria-invalid", "false");
        });

        updateAssessmentHeaders();
        calculateAllRows();
    }

    // ==========================================
    // FORMATTING AND CELL HELPERS
    // ==========================================

    function formatNumber(value) {
        return Number.isFinite(value) ? value.toFixed(2) : "—";
    }

    function setCell(row, selector, value) {
        const cell = row.querySelector(selector);

        if (cell) {
            cell.textContent = value === null ? "—" : formatNumber(value);
        }
    }

    // ==========================================
    // SCORE VALIDATION
    // ==========================================

    function getScoreInformation(input) {
        const rawValue = input.value.trim();
        const max = Number(input.dataset.max || input.max || 0);

        if (rawValue === "") {
            return {
                entered: false,
                valid: true,
                score: null,
                max,
            };
        }

        const score = Number(rawValue);

        const valid =
            Number.isFinite(score) && score >= 0 && score <= max && max > 0;

        return {
            entered: true,
            valid,
            score: valid ? score : null,
            max,
        };
    }

    function validateRow(row) {
        let hasInvalid = false;
        let allScoresComplete = true;

        const inputs = Array.from(row.querySelectorAll(".score-input"));

        inputs.forEach((input) => {
            const information = getScoreInformation(input);

            input.classList.toggle("is-invalid", !information.valid);

            input.setAttribute(
                "aria-invalid",
                information.valid ? "false" : "true",
            );

            if (!information.valid) {
                hasInvalid = true;
            }

            if (!information.entered) {
                allScoresComplete = false;
            }
        });

        return {
            hasInvalid,
            allScoresComplete,
        };
    }

    // ==========================================
    // WW AND PT COMPUTATION
    // ==========================================

    function calculateGroup(row, group) {
        const inputs = Array.from(
            row.querySelectorAll(`.score-input[data-group="${group}"]`),
        );

        let earned = 0;
        let possible = 0;
        let hasAnyScore = false;
        let hasInvalidScore = false;

        inputs.forEach((input) => {
            const information = getScoreInformation(input);

            if (!information.entered) return;

            if (!information.valid) {
                hasInvalidScore = true;
                return;
            }

            earned += information.score;
            possible += information.max;
            hasAnyScore = true;
        });

        if (hasInvalidScore || !hasAnyScore || possible <= 0) {
            return {
                earned: null,
                ps: null,
                ws: null,
            };
        }

        const ps = (earned / possible) * 100;

        return {
            earned,
            ps,
            ws: ps * weights[group],
        };
    }

    // ==========================================
    // INDIVIDUAL SA / SB / SC / TE COMPUTATION
    // ==========================================

    function calculateIndividualExam(row, label) {
        const assessmentIndex = assessmentDefinitions.findIndex(
            (assessment) =>
                assessment.group === "ex" && assessment.label === label,
        );

        if (assessmentIndex === -1) {
            return null;
        }

        const input = row.querySelector(
            `.score-input[data-assessment-index="${assessmentIndex}"]`,
        );

        if (!input) return null;

        const information = getScoreInformation(input);

        if (!information.entered || !information.valid) {
            return null;
        }

        const ps = (information.score / information.max) * 100;

        const internalWeight =
            assessmentDefinitions[assessmentIndex].weight / 100;

        return {
            score: information.score,
            max: information.max,
            ps,
            ws: ps * internalWeight,
        };
    }

    function calculateExamGroup(row) {
        const exams = {
            sa: calculateIndividualExam(row, "SA"),
            sb: calculateIndividualExam(row, "SB"),
            sc: calculateIndividualExam(row, "SC"),
            te: calculateIndividualExam(row, "TE"),
        };

        setCell(row, ".sa-ws", exams.sa?.ws ?? null);
        setCell(row, ".sb-ws", exams.sb?.ws ?? null);
        setCell(row, ".sc-ws", exams.sc?.ws ?? null);
        setCell(row, ".te-ws", exams.te?.ws ?? null);

        const allComplete = Object.values(exams).every((exam) => exam !== null);

        if (!allComplete) {
            setCell(row, ".ex-total", null);
            setCell(row, ".ex-ws", null);

            return {
                total: null,
                ws: null,
            };
        }

        // EX Total is the sum of individual weighted scores.

        const total = exams.sa.ws + exams.sb.ws + exams.sc.ws + exams.te.ws;

        // Apply the overall subject-specific EX component weight.

        const ws = total * weights.ex;

        setCell(row, ".ex-total", total);
        setCell(row, ".ex-ws", ws);

        return {
            total,
            ws,
        };
    }

    // ==========================================
    // COMPLETE ROW COMPUTATION
    // ==========================================

    function calculateRow(row) {
        const validation = validateRow(row);

        const ww = calculateGroup(row, "ww");
        const pt = calculateGroup(row, "pt");
        const ex = calculateExamGroup(row);

        setCell(row, ".ww-total", ww.earned);
        setCell(row, ".ww-ps", ww.ps);
        setCell(row, ".ww-ws", ww.ws);

        setCell(row, ".pt-total", pt.earned);
        setCell(row, ".pt-ps", pt.ps);
        setCell(row, ".pt-ws", pt.ws);

        /*
         * Initial Grade is displayed only when:
         * - All assessment scores are entered.
         * - No score is invalid.
         * - All three component calculations are available.
         */

        const canCalculateInitialGrade =
            validation.allScoresComplete &&
            !validation.hasInvalid &&
            ww.ws !== null &&
            pt.ws !== null &&
            ex.ws !== null;

        const initialGrade = canCalculateInitialGrade
            ? ww.ws + pt.ws + ex.ws
            : null;

        setCell(row, ".initial-grade", initialGrade);

        // Official Term Grade and Descriptor remain pending.

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
        getLearnerRows().forEach(calculateRow);
    }

    // ==========================================
    // UNSAVED CHANGES
    // ==========================================

    function markUnsaved() {
        if (unsavedNote) {
            unsavedNote.textContent = `Term ${activeTerm} changes — not saved to the database.`;
        }
    }

    // ==========================================
    // SCORE INPUT EVENTS
    // ==========================================

    function attachScoreInputEvents() {
        getScoreInputs().forEach((input) => {
            input.addEventListener("input", () => {
                const row = input.closest(".learner-row");

                if (row) {
                    calculateRow(row);
                }

                saveCurrentTermData();
                markUnsaved();
            });

            input.addEventListener("change", () => {
                const information = getScoreInformation(input);

                if (information.entered && information.valid) {
                    input.value = String(information.score);
                }

                saveCurrentTermData();
                calculateAllRows();
            });

            input.addEventListener("focus", () => {
                input.select();
            });

            input.addEventListener("keydown", handleScoreNavigation);
        });
    }

    // ==========================================
    // SPREADSHEET-STYLE NAVIGATION
    // ==========================================

    function handleScoreNavigation(event) {
        const input = event.currentTarget;
        const key = event.key;

        const navigationKeys = [
            "ArrowRight",
            "ArrowLeft",
            "ArrowUp",
            "ArrowDown",
            "Enter",
        ];

        if (!navigationKeys.includes(key)) return;

        if (
            (key === "ArrowLeft" || key === "ArrowRight") &&
            input.selectionStart !== null &&
            input.selectionEnd !== null &&
            input.selectionStart !== input.selectionEnd
        ) {
            return;
        }

        event.preventDefault();

        const currentRow = input.closest(".learner-row");

        if (!currentRow) return;

        const currentColumn = Number(input.dataset.assessmentIndex);

        const rows = getLearnerRows();
        const currentRowIndex = rows.indexOf(currentRow);

        let targetRowIndex = currentRowIndex;
        let targetColumn = currentColumn;

        if (key === "ArrowRight") {
            targetColumn++;
        } else if (key === "ArrowLeft") {
            targetColumn--;
        } else if (key === "ArrowDown") {
            targetRowIndex++;
        } else if (key === "ArrowUp") {
            targetRowIndex--;
        } else if (key === "Enter") {
            targetRowIndex += event.shiftKey ? -1 : 1;
        }

        const targetRow = rows[targetRowIndex];

        if (!targetRow) return;

        const targetInput = targetRow.querySelector(
            `.score-input[data-assessment-index="${targetColumn}"]`,
        );

        if (targetInput) {
            targetInput.focus();
            targetInput.select();
        }
    }

    attachScoreInputEvents();

    // ==========================================
    // TERM AND SUMMARY NAVIGATION
    // ==========================================

    function setActiveTab(selectedTab, index) {
        const isSummary = selectedTab.dataset.view === "summary";

        if (!isSummary) {
            const selectedTerm = index + 1;

            if (selectedTerm !== activeTerm) {
                saveCurrentTermData();

                activeTerm = selectedTerm;

                loadTermData(activeTerm);
            }
        } else {
            saveCurrentTermData();
        }

        tabs.forEach((tab, tabIndex) => {
            const isActive = tab === selectedTab;

            tab.classList.toggle("is-active", isActive);

            tab.setAttribute("aria-selected", isActive ? "true" : "false");

            tab.tabIndex = isActive ? 0 : -1;

            if (tabIndex < 3) {
                tab.textContent = `Term ${tabIndex + 1}`;
            }
        });

        termTable.classList.toggle("is-hidden", isSummary);

        summaryView?.classList.toggle("is-hidden", !isSummary);

        if (tableTitle) {
            tableTitle.textContent = isSummary
                ? "Summary of Grades"
                : `Term ${activeTerm} Scores`;
        }

        if (!isSummary && termSelect) {
            termSelect.value = String(activeTerm);
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

    // ==========================================
    // WARNING MODALS
    // ==========================================

    const clearScoresModal = document.getElementById("clearScoresModal");

    const resetGradebookModal = document.getElementById("resetGradebookModal");

    const confirmClearScoresButton =
        document.getElementById("confirmClearScores");

    const confirmResetGradebookButton = document.getElementById(
        "confirmResetGradebook",
    );

    const clearScoresTerm = document.getElementById("clearScoresTerm");

    const resetGradebookTerm = document.getElementById("resetGradebookTerm");

    function openWarningModal(modal, termElement) {
        if (!modal) return;

        if (termElement) {
            termElement.textContent = String(activeTerm);
        }

        modal.classList.remove("is-hidden");
        modal.setAttribute("aria-hidden", "false");
    }

    function closeWarningModal(modal) {
        if (!modal) return;

        modal.classList.add("is-hidden");
        modal.setAttribute("aria-hidden", "true");
    }

    document.querySelectorAll("[data-close-warning]").forEach((button) => {
        button.addEventListener("click", () => {
            closeWarningModal(button.closest(".warning-modal-overlay"));
        });
    });

    [clearScoresModal, resetGradebookModal].forEach((modal) => {
        if (!modal) return;

        modal.addEventListener("click", (event) => {
            if (event.target === modal) {
                closeWarningModal(modal);
            }
        });
    });

    // ==========================================
    // CLEAR SCORES
    // ==========================================

    function clearCurrentTermScores() {
        getScoreInputs().forEach((input) => {
            input.value = "";

            input.classList.remove("is-invalid");

            input.setAttribute("aria-invalid", "false");
        });

        saveCurrentTermData();
        calculateAllRows();
        markUnsaved();
    }

    clearButton?.addEventListener("click", () => {
        openWarningModal(clearScoresModal, clearScoresTerm);
    });

    confirmClearScoresButton?.addEventListener("click", () => {
        clearCurrentTermScores();
        closeWarningModal(clearScoresModal);
    });

    // ==========================================
    // RESET GRADEBOOK
    // ==========================================

    function resetCurrentTerm() {
        if (activeTerm === 1) {
            termData[1] = originalTermData.map((item) => ({
                ...item,
            }));
        } else {
            termData[activeTerm] = createBlankTermData();
        }

        loadTermData(activeTerm);

        if (unsavedNote) {
            unsavedNote.textContent = `Term ${activeTerm} has been reset. Changes are not saved to the database.`;
        }
    }

    resetButton?.addEventListener("click", () => {
        openWarningModal(resetGradebookModal, resetGradebookTerm);
    });

    confirmResetGradebookButton?.addEventListener("click", () => {
        resetCurrentTerm();
        closeWarningModal(resetGradebookModal);
    });

    // ==========================================
    // SAVE GRADEBOOK
    // ==========================================

    saveButton?.addEventListener("click", () => {
        saveCurrentTermData();

        const hasInvalidScore = getScoreInputs().some((input) =>
            input.classList.contains("is-invalid"),
        );

        if (hasInvalidScore) {
            window.alert(
                "May invalid score. Siguraduhing nasa pagitan ng 0 at maximum points ang bawat score.",
            );

            return;
        }

        window.alert(
            `Term ${activeTerm} scores are currently stored in page memory only. Database saving is not yet connected.`,
        );
    });

    // ==========================================
    // ASSESSMENT SETTINGS
    // ==========================================

    const assessmentSettingsButton =
        document.getElementById("assessmentSettings");

    const assessmentModal = document.getElementById("assessmentSettingsModal");

    const assessmentFields = document.getElementById(
        "assessmentSettingsFields",
    );

    const closeAssessmentButton = document.getElementById(
        "closeAssessmentSettings",
    );

    const cancelAssessmentButton = document.getElementById(
        "cancelAssessmentSettings",
    );

    const applyAssessmentButton = document.getElementById(
        "saveAssessmentSettings",
    );

    function getDefaultMaximum(index) {
        return String(assessmentDefinitions[index]?.max ?? 0);
    }

    function getAssessmentMaximum(index) {
        const firstLearnerValue = getAssessmentData(index);

        return firstLearnerValue
            ? firstLearnerValue.max
            : getDefaultMaximum(index);
    }

    function renderAssessmentSettings() {
        if (!assessmentFields) return;

        assessmentFields.replaceChildren();

        assessmentDefinitions.forEach((assessment, index) => {
            const wrapper = document.createElement("div");

            wrapper.className = "assessment-setting-row";

            const label = document.createElement("label");

            label.textContent = assessment.label;
            label.htmlFor = `assessmentMax${index}`;

            const input = document.createElement("input");

            input.type = "number";
            input.id = `assessmentMax${index}`;
            input.min = "0.01";
            input.step = "any";
            input.required = true;
            input.value = getAssessmentMaximum(index);

            const group = document.createElement("span");

            group.className = `assessment-group-label ${assessment.group}`;

            group.textContent = assessment.group.toUpperCase();

            wrapper.append(label, input, group);

            assessmentFields.appendChild(wrapper);
        });
    }

    function updateAssessmentMaximums() {
        getScoreInputs().forEach((input, flatIndex) => {
            const item = termData[activeTerm][flatIndex];

            if (!item) return;

            input.dataset.max = item.max;
            input.max = item.max;
        });

        updateAssessmentHeaders();
        calculateAllRows();
    }

    function openAssessmentModal() {
        if (!assessmentModal || !assessmentFields) return;

        renderAssessmentSettings();

        assessmentModal.classList.remove("is-hidden");

        assessmentModal.setAttribute("aria-hidden", "false");

        assessmentFields.querySelector("input")?.focus();
    }

    function closeAssessmentModal() {
        if (!assessmentModal) return;

        assessmentModal.classList.add("is-hidden");

        assessmentModal.setAttribute("aria-hidden", "true");
    }

    assessmentSettingsButton?.addEventListener("click", openAssessmentModal);

    closeAssessmentButton?.addEventListener("click", closeAssessmentModal);

    cancelAssessmentButton?.addEventListener("click", closeAssessmentModal);

    assessmentModal?.addEventListener("click", (event) => {
        if (event.target === assessmentModal) {
            closeAssessmentModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            assessmentModal &&
            !assessmentModal.classList.contains("is-hidden")
        ) {
            closeAssessmentModal();
        }
    });

    applyAssessmentButton?.addEventListener("click", () => {
        if (!assessmentFields) return;

        const settingInputs = Array.from(
            assessmentFields.querySelectorAll("input"),
        );

        if (settingInputs.length !== assessmentCount) {
            window.alert(
                "Assessment settings could not be loaded. Please try again.",
            );

            return;
        }

        const newMaximums = settingInputs.map((input) => {
            const value = input.value.trim();

            return value === "" ? NaN : Number(value);
        });

        const hasInvalidMaximum = newMaximums.some(
            (max) => !Number.isFinite(max) || max <= 0,
        );

        if (hasInvalidMaximum) {
            window.alert(
                "Please enter a valid maximum point greater than zero for every assessment.",
            );

            return;
        }

        // Update each learner's maximum points using
        // the matching assessment index.

        termData[activeTerm] = termData[activeTerm].map((item, flatIndex) => {
            const assessmentIndex = flatIndex % assessmentCount;

            return {
                ...item,
                max: String(newMaximums[assessmentIndex]),
            };
        });

        updateAssessmentMaximums();
        saveCurrentTermData();
        markUnsaved();

        closeAssessmentModal();

        window.alert(
            `Assessment maximum points for Term ${activeTerm} have been updated.`,
        );
    });

    // ==========================================
    // INITIAL DISPLAY
    // ==========================================

    if (termSelect) {
        termSelect.value = "1";
    }

    loadTermData(1);
});
