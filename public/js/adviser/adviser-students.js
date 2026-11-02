document.addEventListener("DOMContentLoaded", function () {
    // GET STUDENT PAGE ELEMENTS
    const searchInput = document.getElementById("studentSearch");
    const genderFilter = document.getElementById("genderFilter");
    const tableBody = document.getElementById("studentsTableBody");
    const emptyState = document.getElementById("studentsEmpty");
    const studentCount = document.getElementById("studentCount");

    // GET MODAL ELEMENTS
    const modal = document.getElementById("studentRecordsModal");
    const modalStudentName = document.getElementById("modalStudentName");
    const modalStudentId = document.getElementById("modalStudentId");
    const closeModalButton = document.getElementById("closeStudentModal");
    const doneModalButton = document.getElementById("doneStudentModal");

    // STORE THE ORIGINAL STUDENT ROWS
    const studentRows = Array.from(tableBody.querySelectorAll("tr"));

    // FILTER STUDENTS
    function filterStudents() {
        const searchValue = searchInput.value.trim().toLowerCase();
        const selectedGender = genderFilter.value;

        let visibleStudents = 0;

        studentRows.forEach(function (row) {
            const studentText = row.textContent.toLowerCase();
            const studentGender = row.dataset.gender;

            const matchesSearch = studentText.includes(searchValue);

            const matchesGender =
                selectedGender === "" || studentGender === selectedGender;

            const shouldShow = matchesSearch && matchesGender;

            row.hidden = !shouldShow;

            if (shouldShow) {
                visibleStudents++;
            }
        });

        // UPDATE THE EMPTY STATE
        emptyState.hidden = visibleStudents !== 0;

        // UPDATE THE STUDENT COUNT
        studentCount.textContent =
            "Showing " +
            visibleStudents +
            " of " +
            studentRows.length +
            " sample students";
    }

    // LISTEN FOR SEARCH INPUT
    searchInput.addEventListener("input", filterStudents);

    // LISTEN FOR GENDER FILTER CHANGES
    genderFilter.addEventListener("change", filterStudents);

    // OPEN THE STUDENT RECORDS MODAL
    document.querySelectorAll(".view-records-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            modalStudentName.textContent = button.dataset.studentName;
            modalStudentId.textContent = button.dataset.studentId;

            modal.hidden = false;
            document.body.style.overflow = "hidden";

            closeModalButton.focus();
        });
    });

    // CLOSE THE STUDENT RECORDS MODAL
    function closeStudentModal() {
        modal.hidden = true;
        document.body.style.overflow = "";
    }

    closeModalButton.addEventListener("click", closeStudentModal);
    doneModalButton.addEventListener("click", closeStudentModal);

    // CLOSE MODAL WHEN CLICKING OUTSIDE IT
    modal
        .querySelector("[data-close-modal]")
        .addEventListener("click", closeStudentModal);

    // CLOSE MODAL USING THE ESCAPE KEY
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) {
            closeStudentModal();
        }
    });
});
