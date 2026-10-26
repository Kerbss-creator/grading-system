document.addEventListener("DOMContentLoaded", function () {
    const notificationBtn = document.getElementById("notificationBtn");
    const notificationPanel = document.getElementById("notificationPanel");
    const notificationClose = document.getElementById("notificationClose");
    const notificationList = document.getElementById("notificationList");
    const notificationDot = document.getElementById("notificationDot");
    const unreadCount = document.getElementById("unreadNotificationCount");
    const profileWrapper = document.querySelector(".profile-wrapper");

    if (!notificationBtn || !notificationPanel) {
        console.log("Notification elements not found.");
        return;
    }

    // OPEN / CLOSE NOTIFICATION
    notificationBtn.addEventListener("click", function (event) {
        event.stopPropagation();

        if (profileWrapper) {
            profileWrapper.classList.remove("active");
        }

        notificationPanel.classList.toggle("show");
    });

    // CLOSE BUTTON
    if (notificationClose) {
        notificationClose.addEventListener("click", function (event) {
            event.stopPropagation();
            notificationPanel.classList.remove("show");
        });
    }

    // CLICK OUTSIDE NOTIFICATION
    document.addEventListener("click", function (event) {
        if (
            !notificationPanel.contains(event.target) &&
            !notificationBtn.contains(event.target)
        ) {
            notificationPanel.classList.remove("show");
        }
    });

    // ESC KEY
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            notificationPanel.classList.remove("show");
        }
    });

    // CSRF TOKEN FOR LARAVEL REQUESTS
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    // UPDATE UNREAD COUNT AND RED DOT
    function updateUnreadCount(count) {
        if (unreadCount) {
            unreadCount.textContent = `${count} unread`;
        }

        if (notificationDot) {
            notificationDot.style.display = count > 0 ? "" : "none";
        }
    }

    // SHOW EMPTY STATE WHEN THERE ARE NO NOTIFICATIONS
    function updateEmptyState() {
        if (!notificationList) {
            return;
        }

        const notificationItems =
            notificationList.querySelectorAll(".notification-item");

        let emptyState = document.getElementById("emptyNotification");

        if (notificationItems.length === 0) {
            if (!emptyState) {
                emptyState = document.createElement("div");
                emptyState.id = "emptyNotification";
                emptyState.className = "empty-notification";

                const iconWrapper = document.createElement("div");
                iconWrapper.className = "empty-notification-icon";

                const icon = document.createElement("i");
                icon.className = "fa-regular fa-bell-slash";
                iconWrapper.appendChild(icon);

                const title = document.createElement("h4");
                title.textContent = "No notifications yet";

                const message = document.createElement("p");
                message.textContent =
                    "You don't have any notifications at the moment.";

                emptyState.append(iconWrapper, title, message);
                notificationList.appendChild(emptyState);
            }
        } else if (emptyState) {
            emptyState.remove();
        }
    }

    // SEND REQUEST TO LARAVEL
    async function sendNotificationRequest(url, method, button) {
        if (!csrfToken) {
            alert("CSRF token not found. Please refresh the page.");
            return null;
        }

        button.disabled = true;

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: method === "PATCH" ? JSON.stringify({}) : undefined,
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok || result.success !== true) {
                throw new Error(
                    result.message || `Request failed (${response.status}).`,
                );
            }

            return result;
        } catch (error) {
            console.error("Notification request failed:", error);
            alert(error.message || "Unable to update the notification.");
            return null;
        } finally {
            button.disabled = false;
        }
    }

    // MARK AS READ
    if (notificationList) {
        notificationList.addEventListener("click", async function (event) {
            const markReadButton = event.target.closest(".mark-read-btn");
            const deleteButton = event.target.closest(
                ".delete-notification-btn",
            );

            // MARK AS READ
            if (markReadButton) {
                event.preventDefault();

                const notificationId = markReadButton.dataset.id;

                if (!notificationId) {
                    return;
                }

                const result = await sendNotificationRequest(
                    `/notifications/${encodeURIComponent(notificationId)}/read`,
                    "PATCH",
                    markReadButton,
                );

                if (!result) {
                    return;
                }

                const notificationItem =
                    markReadButton.closest(".notification-item");

                if (notificationItem) {
                    notificationItem.classList.remove("unread");
                }

                // Replace button with read label
                const readLabel = document.createElement("span");
                readLabel.className = "notification-read-label";

                const readIcon = document.createElement("i");
                readIcon.className = "fa-solid fa-check-double";

                readLabel.append(readIcon, document.createTextNode(" Read"));
                markReadButton.replaceWith(readLabel);

                updateUnreadCount(result.unread_count);
                return;
            }

            // DELETE NOTIFICATION
            if (deleteButton) {
                event.preventDefault();

                const notificationId = deleteButton.dataset.id;

                if (!notificationId) {
                    return;
                }


                const result = await sendNotificationRequest(
                    `/notifications/${encodeURIComponent(notificationId)}`,
                    "DELETE",
                    deleteButton,
                );

                if (!result) {
                    return;
                }

                const notificationItem =
                    deleteButton.closest(".notification-item");

                if (notificationItem) {
                    notificationItem.remove();
                }

                updateUnreadCount(result.unread_count);
                updateEmptyState();
            }
        });
    }
});
