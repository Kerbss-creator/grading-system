// ADVISER CLASSES PAGE

document.addEventListener("DOMContentLoaded", function () {
    // GET SEARCH, FILTER, TABLE, AND COUNT ELEMENTS
    const searchInput = document.getElementById("gradeReviewSearch");
    const statusFilter = document.getElementById("gradeReviewFilter");
    const tableBody = document.getElementById("gradeReviewTableBody");
    const emptyMessage = document.getElementById("gradeReviewEmpty");
    const countLabel = document.getElementById("gradeReviewCount");

    // STOP IF THE GRADE REVIEW TABLE IS NOT ON THE PAGE
    if (!searchInput || !statusFilter || !tableBody) {
        return;
    }

    // GET ALL GRADE REVIEW ROWS
    const reviewRows = Array.from(
        tableBody.querySelectorAll(".grade-review-row"),
    );

    // FILTER GRADE REVIEWS
    function filterGradeReviews() {
        const searchValue = searchInput.value.trim().toLowerCase();

        const selectedStatus = statusFilter.value;

        let visibleCount = 0;

        reviewRows.forEach(function (row) {
            // GET THE SEARCH TEXT AND STATUS OF EACH ROW
            const searchText = row.dataset.search.toLowerCase();
            const rowStatus = row.dataset.status;

            // CHECK IF THE ROW MATCHES THE SEARCH
            const matchesSearch = searchText.includes(searchValue);

            // CHECK IF THE ROW MATCHES THE SELECTED STATUS
            const matchesStatus =
                selectedStatus === "all" || rowStatus === selectedStatus;

            // SHOW ONLY MATCHING ROWS
            const shouldShow = matchesSearch && matchesStatus;

            row.style.display = shouldShow ? "" : "none";

            // COUNT VISIBLE SUBMISSIONS
            if (shouldShow) {
                visibleCount++;
            }
        });

        // SHOW OR HIDE THE EMPTY MESSAGE
        if (emptyMessage) {
            emptyMessage.style.display = visibleCount === 0 ? "" : "none";
        }

        // UPDATE THE SUBMISSION COUNT
        if (countLabel) {
            countLabel.textContent =
                "Showing " +
                visibleCount +
                (visibleCount === 1 ? " submission" : " submissions");
        }
    }

    // LISTEN FOR SEARCH INPUT
    searchInput.addEventListener("input", filterGradeReviews);

    // LISTEN FOR STATUS FILTER CHANGES
    statusFilter.addEventListener("change", filterGradeReviews);

    // DISPLAY THE INITIAL TABLE
    filterGradeReviews();
});
