document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("classSearch");
    const gradeFilter = document.getElementById("gradeFilter");
    const classCards = document.querySelectorAll(".class-card");
    const noClasses = document.getElementById("noClasses");

    const modal = document.getElementById("classModal");
    const closeModal = document.getElementById("closeClassModal");

    const modalGrade = document.getElementById("modalGrade");
    const modalClass = document.getElementById("modalClass");
    const modalStudents = document.getElementById("modalStudents");
    const modalSubjects = document.getElementById("modalSubjects");

    const manageClassBtn = document.getElementById("manageClassBtn");

    /* SEARCH + FILTER */

    function filterClasses() {
        const searchValue = searchInput.value.toLowerCase().trim();

        const gradeValue = gradeFilter.value;

        let visibleClasses = 0;

        classCards.forEach(function (card) {
            const grade = card.dataset.grade;
            const searchText = card.dataset.search;

            const matchesSearch = searchText.includes(searchValue);
            const matchesGrade = gradeValue === "all" || grade === gradeValue;

            if (matchesSearch && matchesGrade) {
                card.style.display = "";

                visibleClasses++;
            } else {
                card.style.display = "none";
            }
        });

        if (visibleClasses === 0) {
            noClasses.style.display = "block";
        } else {
            noClasses.style.display = "none";
        }
    }

    searchInput.addEventListener("input", filterClasses);

    gradeFilter.addEventListener("change", filterClasses);

    /* OPEN CLASS MODAL */

    document.querySelectorAll(".view-class-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            modalClass.textContent = button.dataset.class;
            modalGrade.textContent = button.dataset.grade.toUpperCase();
            modalStudents.textContent = button.dataset.students;
            modalSubjects.textContent = button.dataset.subjects;
            modal.classList.add("show");
        });
    });

    /* CLOSE MODAL */

    closeModal.addEventListener("click", function () {
        modal.classList.remove("show");
    });

    /* CLOSE WHEN CLICKING OUTSIDE */

    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.remove("show");
        }
    });

    /* ESC KEY */

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            modal.classList.remove("show");
        }
    });

    /* MANAGE CLASS */

    manageClassBtn.addEventListener("click", function () {
        alert("Class management page will be connected here.");
    });
});
