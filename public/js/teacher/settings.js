document.addEventListener("DOMContentLoaded", function () {

    // Save settings button
    const saveSettings = document.getElementById("saveSettings");

    if (saveSettings) {

        saveSettings.addEventListener("click", function () {

            alert("Settings saved successfully.");

        });

    }


    // Change password button
    const changePassword = document.querySelector(".change-password-btn");

    if (changePassword) {

        changePassword.addEventListener("click", function () {

            alert("Change password feature will be available soon.");

        });

    }

});
