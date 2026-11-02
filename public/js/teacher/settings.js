
document.addEventListener("DOMContentLoaded", function () {

    // Teacher settings form
    const settingsForm = document.getElementById("teacherSettingsForm");
    const saveSettings = document.getElementById("saveSettings");

    // Save settings
    if (settingsForm && saveSettings) {
        saveSettings.addEventListener("click", function () {

            if (!settingsForm.reportValidity()) {
                return;
            }

            
            alert("BACKEND YUDIPUTA");

        });
    }

    // Change password
    const changePasswordBtn = document.getElementById("changePasswordBtn");

    if (changePasswordBtn) {
        changePasswordBtn.addEventListener("click", function () {

            // Connect this button to the secure password-change feature.
            alert("The password-change feature needs to be connected to Laravel.");

        });
    }

});
