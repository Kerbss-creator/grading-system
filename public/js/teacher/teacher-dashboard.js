// PROFILE DROPDOWN

const profileBtn = document.getElementById("profileBtn");
const profileDropdown = document.getElementById("profileDropdown");

if (profileBtn && profileDropdown) {

    profileBtn.addEventListener("click", function (event) {

        event.stopPropagation();

        profileDropdown.classList.toggle("show");

    });

}


// CLOSE DROPDOWN WHEN CLICKING OUTSIDE

document.addEventListener("click", function (event) {

    if (
        profileDropdown &&
        profileBtn &&
        !profileBtn.contains(event.target) &&
        !profileDropdown.contains(event.target)
    ) {
        profileDropdown.classList.remove("show");
    }

});


// CLOSE DROPDOWN WITH ESCAPE KEY

document.addEventListener("keydown", function (event) {

    if (event.key === "Escape") {

        if (profileDropdown) {
            profileDropdown.classList.remove("show");
        }

    }

});


// NOTIFICATION BUTTON
const notificationBtn = document.getElementById("notificationBtn");

if (notificationBtn) {

    notificationBtn.addEventListener("click", function () {

        // Temporary notification action
        alert("You have new notifications.");

    });

}



// ACTION BUTTONS
const actionButtons = document.querySelectorAll(".action-btn");

actionButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        // Temporary action
        console.log("Class action button clicked.");

    });

});
