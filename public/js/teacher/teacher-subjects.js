document.addEventListener("DOMContentLoaded", function () {
    const schoolYear = document.getElementById("schoolYear");
    const term = document.getElementById("term");

    if (schoolYear) {
        schoolYear.addEventListener("change", function () {
            console.log("School Year changed to:", this.value);

            // Future Laravel implementation:
            // Reload subject data based on selected school year.
        });
    }

    if (term) {
        term.addEventListener("change", function () {
            console.log("Term changed to:", this.value);

            // Future Laravel implementation:
            // Reload subject data based on selected term.
        });
    }
});
