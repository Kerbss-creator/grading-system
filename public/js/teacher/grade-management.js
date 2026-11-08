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

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    const gradeLockModal = document.getElementById("gradeLockModal");
    const closeGradeLockModal = document.getElementById("closeGradeLockModal");

    function closeGradeLock() {
        gradeLockModal?.classList.remove("show");
    }

    closeGradeLockModal?.addEventListener("click", closeGradeLock);

    // Close kapag pinindot ang background
    gradeLockModal?.addEventListener("click", (event) => {
        if (event.target === gradeLockModal) {
            closeGradeLock();
        }
    });

    // Close kapag pinindot ang Escape
    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            gradeLockModal?.classList.contains("show")
        ) {
            closeGradeLock();
        }
    });

    // ==========================================
    // CONFIGURATION
    // ==========================================

    let assessmentDefinitions = [];
    let weights = {};

    try {
        assessmentDefinitions = JSON.parse(
            gradebook.dataset.assessments || "[]",
        );

        const rawWeights = JSON.parse(gradebook.dataset.gradingWeights || "{}");

        weights = {
            ww: Number(rawWeights.ww ?? rawWeights.WW) / 100,
            pt: Number(rawWeights.pt ?? rawWeights.PT) / 100,
            ex: Number(rawWeights.ex ?? rawWeights.EX) / 100,
        };
    } catch (error) {
        console.error("Gradebook configuration error:", error);

        alert("Gradebook configuration could not be loaded.");
        return;
    }

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
        assessmentDefinitions.every(
            (assessment) =>
                assessment.label &&
                validGroups.includes(assessment.group) &&
                Number.isFinite(assessment.max) &&
                assessment.max > 0,
        );

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

        alert(
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

        alert(
            "The assessment structure must contain 4 WW, 2 PT, SA, SB, SC, and TE.",
        );

        return;
    }

    // ==========================================
    // TERM DATA MANAGEMENT
    // ==========================================

    let activeTerm = Number(termSelect?.value || 1);
    let isLoadingTerm = false;
    let isSaving = false;

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

    const loadedTerms = new Set();

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

            maxLabel.textContent = ` ${Number(assessment.max)}`;
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
    // DATABASE LOADING
    // ==========================================

    async function loadTermFromDatabase(term) {
        const loadUrl = gradebook.dataset.loadUrl;

        if (!loadUrl) {
            throw new Error("Grade loading URL is missing.");
        }

        const params = new URLSearchParams({
            assignment: gradebook.dataset.assignmentId,
            school_year: gradebook.dataset.schoolYear,
            term: String(term),
        });

        const response = await fetch(`${loadUrl}?${params.toString()}`, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Unable to load saved grades.");
        }

        const scores = result.scores || {};
        const maxScores = result.max_scores || {};

        const newTermData = [];

        getLearnerRows().forEach((row) => {
            const studentId = String(row.dataset.learnerId);
            const studentScores = scores[studentId] || {};

            assessmentDefinitions.forEach((assessment, index) => {
                const savedScore = studentScores[index];

                newTermData.push({
                    value:
                        savedScore === null || savedScore === undefined
                            ? ""
                            : String(savedScore),

                    max: String(maxScores[index] ?? assessment.max),
                });
            });
        });

        termData[term] = newTermData;
        loadedTerms.add(term);

        return result;
    }

    // ==========================================
    // FORMATTING AND CELL HELPERS
    // ==========================================

    function formatNumber(value) {
        return Number.isFinite(value) ? value.toFixed(2) : "—";
    }

    function setCell(row, selector, value) {
        const cell = row.querySelector(selector);

        if (!cell) return;

        if (value === null || !Number.isFinite(value)) {
            cell.textContent = "—";
            return;
        }

        // TOTAL cells must display whole numbers.
        const totalCells = [".ww-total", ".pt-total", ".ex-total"];

        if (totalCells.includes(selector)) {
            cell.textContent = String(Math.round(value));
        } else {
            cell.textContent = formatNumber(value);
        }
    }

    // ==========================================
    // TERM GRADE TRANSMUTATION
    // ==========================================

    function transmuteGrade(initialGrade) {
        if (!Number.isFinite(initialGrade)) return null;

        const ranges = [
            [99.5, 100.0, 100],
            [98.32, 99.49, 99],
            [97.14, 98.31, 98],
            [95.96, 97.13, 97],
            [94.78, 95.95, 96],
            [93.6, 94.77, 95],
            [92.42, 93.59, 94],
            [91.24, 92.41, 93],
            [90.06, 91.23, 92],
            [88.88, 90.05, 91],
            [87.7, 88.87, 90],
            [86.52, 87.69, 89],
            [85.34, 86.51, 88],
            [84.16, 85.33, 87],
            [82.98, 84.15, 86],
            [81.8, 82.97, 85],
            [80.62, 81.79, 84],
            [79.44, 80.61, 83],
            [78.26, 79.43, 82],
            [77.08, 78.25, 81],
            [75.9, 77.07, 80],
            [74.72, 75.89, 79],
            [73.54, 74.71, 78],
            [72.36, 73.53, 77],
            [71.18, 72.35, 76],
            [70.0, 71.17, 75],
            [65.34, 69.99, 74],
            [60.67, 65.33, 73],
            [56.01, 60.66, 72],
            [51.34, 56.0, 71],
            [46.67, 51.33, 70],
            [42.01, 46.66, 69],
            [37.34, 42.0, 68],
            [32.68, 37.33, 67],
            [28.01, 32.67, 66],
            [23.35, 28.0, 65],
            [18.68, 23.34, 64],
            [14.01, 18.67, 63],
            [9.35, 14.0, 62],
            [4.68, 9.34, 61],
            [0.0, 4.67, 60],
        ];

        const matchedRange = ranges.find(
            ([minimum, maximum]) =>
                initialGrade >= minimum && initialGrade <= maximum,
        );

        return matchedRange ? matchedRange[2] : null;
    }

    // ==========================================
    // TERM GRADE DESCRIPTOR
    // ==========================================

    function getGradeDescriptor(termGrade) {
        if (!Number.isFinite(termGrade)) return null;

        if (termGrade >= 90) return "Advancing";
        if (termGrade >= 80) return "Benchmarking";
        if (termGrade >= 75) return "Connecting";
        if (termGrade >= 65) return "Developing";

        return "Emerging";
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
            Number.isInteger(score) && score >= 0 && score <= max && max > 0;

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
    // INDIVIDUAL EX COMPUTATION
    // ==========================================

    function calculateIndividualExam(row, label) {
        const assessmentIndex = assessmentDefinitions.findIndex(
            (assessment) =>
                assessment.group === "ex" && assessment.label === label,
        );

        if (assessmentIndex === -1) return null;

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

        const total = exams.sa.ws + exams.sb.ws + exams.sc.ws + exams.te.ws;

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

        const termGrade =
            initialGrade !== null ? transmuteGrade(initialGrade) : null;

        const descriptor =
            termGrade !== null ? getGradeDescriptor(termGrade) : null;

        const termGradeCell = row.querySelector(".term-grade");
        const descriptorCell = row.querySelector(".descriptor");

        if (termGradeCell) {
            termGradeCell.textContent =
                termGrade !== null ? String(termGrade) : "Pending";

            termGradeCell.classList.toggle(
                "grade-failed",
                termGrade !== null && termGrade <= 74,
            );

            termGradeCell.classList.toggle(
                "grade-passed",
                termGrade !== null && termGrade >= 75,
            );
        }

        if (descriptorCell) {
            descriptorCell.replaceChildren();

            const badge = document.createElement("span");

            badge.className =
                termGrade !== null && termGrade <= 74
                    ? "status-failed"
                    : termGrade !== null
                      ? "status-passed"
                      : "status-pending";

            badge.textContent = descriptor ?? "Pending";

            descriptorCell.appendChild(badge);
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
    // EXCEL-STYLE KEYBOARD NAVIGATION
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

    async function setActiveTab(selectedTab, index) {
        if (isLoadingTerm) return;

        const isSummary = selectedTab.dataset.view === "summary";

        if (!isSummary) {
            const selectedTerm = index + 1;

            if (selectedTerm !== activeTerm) {
                saveCurrentTermData();

                const previousTerm = activeTerm;

                activeTerm = selectedTerm;
                isLoadingTerm = true;

                try {
                    if (!loadedTerms.has(activeTerm)) {
                        await loadTermFromDatabase(activeTerm);
                    }

                    loadTermData(activeTerm);
                } catch (error) {
                    console.error("Term loading error:", error);

                    alert(error.message || "Unable to load saved grades.");

                    activeTerm = previousTerm;

                    if (termSelect) {
                        termSelect.value = String(previousTerm);
                    }

                    isLoadingTerm = false;
                    return;
                } finally {
                    isLoadingTerm = false;
                }
            }
        } else {
            saveCurrentTermData();
        }

        const showingSummary = selectedTab.dataset.view === "summary";

        tabs.forEach((tab, tabIndex) => {
            const isActive = tab === selectedTab;

            tab.classList.toggle("is-active", isActive);

            tab.setAttribute("aria-selected", isActive ? "true" : "false");

            tab.tabIndex = isActive ? 0 : -1;

            if (tabIndex < 3) {
                tab.textContent = `Term ${tabIndex + 1}`;
            }
        });

        termTable.classList.toggle("is-hidden", showingSummary);

        summaryView?.classList.toggle("is-hidden", !showingSummary);

        if (tableTitle) {
            tableTitle.textContent = showingSummary
                ? "Summary of Grades"
                : `Term ${activeTerm} Scores`;
        }

        if (!showingSummary && termSelect) {
            termSelect.value = String(activeTerm);
        }
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener("click", () => {
            setActiveTab(tab, index);
        });
    });

    termSelect?.addEventListener("change", () => {
        const selectedTerm = Number(termSelect.value);
        const matchingTab = tabs[selectedTerm - 1];

        if (matchingTab) {
            setActiveTab(matchingTab, selectedTerm - 1);
        }
    });

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
        modal?.addEventListener("click", (event) => {
            if (event.target === modal) {
                closeWarningModal(modal);
            }
        });
    });

    // ==========================================
    // CLEAR SCORES
    // ==========================================

    async function clearCurrentTermScores() {
        const clearUrl = gradebook.dataset.clearScoresUrl;

        if (!csrfToken || !clearUrl) {
            alert("Clear Scores URL or CSRF token is missing.");
            return;
        }

        try {
            confirmClearScoresButton.disabled = true;
            confirmClearScoresButton.textContent = "Clearing...";

            const response = await fetch(clearUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    assignment: gradebook.dataset.assignmentId,
                    school_year: gradebook.dataset.schoolYear,
                    term: activeTerm,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                const validationMessage = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : null;

                throw new Error(
                    validationMessage ||
                        result.message ||
                        "Unable to clear student scores.",
                );
            }

            getScoreInputs().forEach((input) => {
                input.value = "";
                input.classList.remove("is-invalid");
                input.setAttribute("aria-invalid", "false");
            });

            saveCurrentTermData();
            calculateAllRows();

            if (unsavedNote) {
                unsavedNote.textContent = `Term ${activeTerm} scores cleared successfully.`;
            }

            closeWarningModal(clearScoresModal);

            alert(result.message || "All student scores have been cleared.");
        } catch (error) {
            console.error("Clear scores error:", error);
            alert(error.message || "Unable to clear student scores.");
        } finally {
            confirmClearScoresButton.disabled = false;
            confirmClearScoresButton.textContent = "Clear Scores";
        }
    }

    clearButton?.addEventListener("click", () => {
        openWarningModal(clearScoresModal, clearScoresTerm);
    });

    confirmClearScoresButton?.addEventListener("click", clearCurrentTermScores);

    // ==========================================
    // RESET GRADEBOOK
    // ==========================================

    async function resetCurrentTerm() {
        const resetUrl = gradebook.dataset.resetUrl;

        if (!csrfToken || !resetUrl) {
            alert("Reset Gradebook URL or CSRF token is missing.");
            return;
        }

        try {
            confirmResetGradebookButton.disabled = true;
            confirmResetGradebookButton.textContent = "Resetting...";

            const response = await fetch(resetUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    assignment: gradebook.dataset.assignmentId,
                    school_year: gradebook.dataset.schoolYear,
                    term: activeTerm,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                const validationMessage = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : null;

                throw new Error(
                    validationMessage ||
                        result.message ||
                        "Unable to reset assessment settings.",
                );
            }

            const defaultMaximums = result.max_scores || {};

            termData[activeTerm] = termData[activeTerm].map(
                (item, flatIndex) => {
                    const assessmentIndex = flatIndex % assessmentCount;

                    return {
                        ...item,
                        max: String(
                            defaultMaximums[assessmentIndex] ??
                                assessmentDefinitions[assessmentIndex].max,
                        ),
                    };
                },
            );

            loadTermData(activeTerm);
            saveCurrentTermData();

            if (unsavedNote) {
                unsavedNote.textContent = `Term ${activeTerm} assessment maximum points restored to defaults.`;
            }

            closeWarningModal(resetGradebookModal);

            alert(
                result.message ||
                    "Assessment maximum points restored successfully.",
            );
        } catch (error) {
            console.error("Reset gradebook error:", error);

            alert(error.message || "Unable to reset assessment settings.");
        } finally {
            confirmResetGradebookButton.disabled = false;
            confirmResetGradebookButton.textContent = "Reset Gradebook";
        }
    }

    resetButton?.addEventListener("click", () => {
        openWarningModal(resetGradebookModal, resetGradebookTerm);
    });

    confirmResetGradebookButton?.addEventListener("click", resetCurrentTerm);

    // ==========================================
    // SAVE GRADEBOOK
    // ==========================================

    async function saveGradebookToDatabase() {
        if (isSaving) return;

        saveCurrentTermData();

        const inputs = getScoreInputs();

        for (const input of inputs) {
            const information = getScoreInformation(input);

            if (!information.valid) {
                input.classList.add("is-invalid");
                input.focus();

                alert(
                    "May invalid score. Siguraduhing nasa pagitan ng 0 at maximum points ang bawat score.",
                );

                return;
            }
        }

        if (!csrfToken) {
            alert("CSRF token is missing.");
            return;
        }

        const saveUrl = gradebook.dataset.saveUrl;

        if (!saveUrl) {
            alert("Gradebook save URL is missing.");
            return;
        }

        const scores = {};
        const maxScores = {};

        getLearnerRows().forEach((row) => {
            const studentId = row.dataset.learnerId;

            if (!studentId) return;

            scores[studentId] = {};

            row.querySelectorAll(".score-input").forEach((input) => {
                const assessmentIndex = Number(input.dataset.assessmentIndex);

                scores[studentId][assessmentIndex] =
                    input.value.trim() === "" ? null : Number(input.value);

                maxScores[assessmentIndex] = Number(
                    input.dataset.max || input.max || 0,
                );
            });
        });

        const payload = {
            assignment: gradebook.dataset.assignmentId,
            school_year: gradebook.dataset.schoolYear,
            term: activeTerm,
            scores,
            max_scores: maxScores,
        };

        try {
            isSaving = true;

            saveButton.disabled = true;
            saveButton.textContent = "Saving...";

            const response = await fetch(saveUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok) {
                const validationMessage = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : null;

                throw new Error(
                    validationMessage ||
                        result.message ||
                        "Unable to save grades.",
                );
            }

            loadedTerms.add(activeTerm);

            if (unsavedNote) {
                unsavedNote.textContent = `Term ${activeTerm} scores saved successfully.`;
            }

            alert(result.message || "Successfully saved grades!");
        } catch (error) {
            console.error("Grade saving error:", error);

            const errorMessage = String(error.message || "");

            if (
                errorMessage
                    .toLowerCase()
                    .includes("only draft grades can be edited")
            ) {
                if (gradeLockModal) {
                    gradeLockModal.classList.add("show");
                } else {
                    alert(
                        "These grades have already been submitted and can no longer be edited.",
                    );
                }
            } else {
                alert(errorMessage || "Unable to save grades.");
            }
        } finally {
            isSaving = false;

            saveButton.disabled = false;
            saveButton.textContent = "Save Changes";
        }
    }

    saveButton?.addEventListener("click", saveGradebookToDatabase);

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

    applyAssessmentButton?.addEventListener("click", async () => {
        if (!assessmentFields) return;

        const settingInputs = Array.from(
            assessmentFields.querySelectorAll("input"),
        );

        if (settingInputs.length !== assessmentCount) {
            alert("Assessment settings could not be loaded.");
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
            alert(
                "Please enter a valid maximum point greater than zero for every assessment.",
            );

            return;
        }

        for (const row of getLearnerRows()) {
            const rowInputs = Array.from(row.querySelectorAll(".score-input"));

            for (const input of rowInputs) {
                const index = Number(input.dataset.assessmentIndex);

                const score = input.value.trim();

                if (score !== "" && Number(score) > newMaximums[index]) {
                    alert(
                        `A student score exceeds the new maximum for ${assessmentDefinitions[index].label}. Please check the scores first.`,
                    );

                    return;
                }
            }
        }

        const settingsUrl = gradebook.dataset.assessmentSettingsUrl;

        if (!csrfToken || !settingsUrl) {
            alert("Assessment settings URL or CSRF token is missing.");

            return;
        }

        const payload = {
            assignment: gradebook.dataset.assignmentId,
            school_year: gradebook.dataset.schoolYear,
            term: activeTerm,
            max_scores: Object.fromEntries(
                newMaximums.map((max, index) => [index, max]),
            ),
        };

        try {
            applyAssessmentButton.disabled = true;
            applyAssessmentButton.textContent = "Saving...";

            const response = await fetch(settingsUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok) {
                const validationMessage = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : null;

                throw new Error(
                    validationMessage ||
                        result.message ||
                        "Unable to save assessment settings.",
                );
            }

            termData[activeTerm] = termData[activeTerm].map(
                (item, flatIndex) => {
                    const assessmentIndex = flatIndex % assessmentCount;

                    return {
                        ...item,
                        max: String(newMaximums[assessmentIndex]),
                    };
                },
            );

            updateAssessmentMaximums();
            saveCurrentTermData();

            closeAssessmentModal();

            if (unsavedNote) {
                unsavedNote.textContent = `Term ${activeTerm} assessment settings saved successfully.`;
            }

            alert(result.message || "Assessment settings saved successfully.");
        } catch (error) {
            console.error("Assessment settings error:", error);

            alert(error.message || "Unable to save assessment settings.");
        } finally {
            applyAssessmentButton.disabled = false;
            applyAssessmentButton.textContent = "Apply Changes";
        }
    });

    // ==========================================
    // INITIAL DISPLAY
    // ==========================================

    async function initializeGradebook() {
        activeTerm = 1;

        if (termSelect) {
            termSelect.value = "1";
        }

        try {
            isLoadingTerm = true;

            await loadTermFromDatabase(1);

            loadTermData(1);

            if (unsavedNote) {
                unsavedNote.textContent = "Saved grades loaded successfully.";
            }
        } catch (error) {
            console.error("Initial grade loading error:", error);

            alert(error.message || "Unable to load saved grades.");

            // Retain server-rendered scores if loading fails.
            loadTermData(1);
        } finally {
            isLoadingTerm = false;
        }
    }

    initializeGradebook();
});
