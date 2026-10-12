document.addEventListener("DOMContentLoaded", function () {
    // STUDENT SEARCH
    const searchInput = document.getElementById("studentSearch");

    // STATUS FILTER
    const statusFilter = document.getElementById("statusFilter");

    // TABLE BODY
    const tableBody = document.getElementById("gradeTableBody");

    // SAVE BUTTON
    const saveGradesBtn = document.getElementById("saveGradesBtn");

    // SUBMIT BUTTON
    const submitGradesBtn = document.getElementById("submitGradesBtn");

    // LOAD BUTTON
    const loadGradesBtn = document.getElementById("loadGradesBtn");

    // FILTER STUDENTS
    function filterStudents() {
        const searchValue = searchInput.value.toLowerCase().trim();

        const selectedStatus = statusFilter.value;

        const rows = tableBody.querySelectorAll("tr");

        rows.forEach(function (row) {
            const studentId = row.children[1]?.textContent.toLowerCase();

            const studentName = row.children[2]?.textContent.toLowerCase();

            const rowStatus = row.dataset.status;

            const matchesSearch =
                studentId.includes(searchValue) ||
                studentName.includes(searchValue);

            const matchesStatus =
                selectedStatus === "all" || rowStatus === selectedStatus;

            if (matchesSearch && matchesStatus) {
                row.classList.remove("grade-row-hidden");
            } else {
                row.classList.add("grade-row-hidden");
            }
        });
    }

    // SEARCH EVENT
    searchInput.addEventListener("input", filterStudents);

    // STATUS FILTER EVENT
    statusFilter.addEventListener("change", filterStudents);

    // UPDATE REMARKS BASED ON GRADE
    function updateRemarks(input) {
        const row = input.closest("tr");

        const remarksSelect = row.querySelector(".remarks-select");

        const grade = Number(input.value);

        if (grade >= 90) {
            remarksSelect.value = "Outstanding";
        } else if (grade >= 85) {
            remarksSelect.value = "Very Satisfactory";
        } else if (grade >= 80) {
            remarksSelect.value = "Satisfactory";
        } else if (grade >= 75) {
            remarksSelect.value = "Fairly Satisfactory";
        } else {
            remarksSelect.value = "Did Not Meet Expectations";
        }
    }

    // GRADE INPUT EVENTS
    const gradeInputs = document.querySelectorAll(".grade-input");

    gradeInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            let grade = Number(this.value);

            // PREVENT VALUES ABOVE 100
            if (grade > 100) {
                this.value = 100;
            }

            // PREVENT VALUES BELOW 0
            if (grade < 0) {
                this.value = 0;
            }

            updateRemarks(this);
        });
    });

    // EDIT BUTTON
    const editButtons = document.querySelectorAll(".edit-btn");

    editButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const row = this.closest("tr");

            const gradeInput = row.querySelector(".grade-input");

            gradeInput.focus();

            gradeInput.select();
        });
    });

    // SAVE GRADES
    saveGradesBtn.addEventListener("click", function () {
        const originalText = this.innerHTML;

        this.innerHTML = '<i class="fa-solid fa-check"></i> Saved';

        setTimeout(() => {
            this.innerHTML = originalText;
        }, 1800);
    });

    // SUBMIT GRADES
    submitGradesBtn.addEventListener("click", function () {
        const pendingRows = tableBody.querySelectorAll(
            'tr[data-status="pending"]',
        );

        if (pendingRows.length > 0) {
            const confirmSubmit = confirm(
                "There are still pending grades. Are you sure you want to submit?",
            );

            if (!confirmSubmit) {
                return;
            }
        }

        const confirmSubmission = confirm(
            "Are you sure you want to submit these grades?",
        );

        if (!confirmSubmission) {
            return;
        }

        // CHANGE PENDING STATUS TO SUBMITTED
        pendingRows.forEach(function (row) {
            row.dataset.status = "submitted";

            const statusBadge = row.querySelector(".status-badge");

            statusBadge.textContent = "Submitted";

            statusBadge.classList.remove("pending");

            statusBadge.classList.add("submitted");
        });

        updateSummary();

        alert("Grades have been submitted successfully.");
    });

    // UPDATE SUMMARY
    function updateSummary() {
        const rows = tableBody.querySelectorAll("tr");

        let submitted = 0;

        let pending = 0;

        let totalGrade = 0;

        let gradedStudents = 0;

        rows.forEach(function (row) {
            if (row.dataset.status === "submitted") {
                submitted++;
            } else {
                pending++;
            }

            const gradeInput = row.querySelector(".grade-input");

            const grade = Number(gradeInput.value);

            if (!isNaN(grade)) {
                totalGrade += grade;

                gradedStudents++;
            }
        });

        const totalStudents = rows.length;

        const average =
            gradedStudents > 0
                ? (totalGrade / gradedStudents).toFixed(1)
                : "0.0";

        document.getElementById("totalStudents").textContent = totalStudents;

        document.getElementById("submittedGrades").textContent = submitted;

        document.getElementById("pendingGrades").textContent = pending;

        document.getElementById("averageGrade").textContent = average;
    }

    // LOAD GRADES BUTTON
    loadGradesBtn.addEventListener("click", function () {
        const originalText = this.innerHTML;

        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading';

        setTimeout(() => {
            this.innerHTML = originalText;
        }, 800);
    });

    // INITIAL SUMMARY
    updateSummary();
});
