
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("studentSidebar");
    const toggleButton = document.getElementById("studentMobileToggle");
    const overlay = document.getElementById("studentSidebarOverlay");

    if (!sidebar || !toggleButton || !overlay) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add("mobile-open");
        overlay.classList.add("visible");
        toggleButton.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
    }

    function closeSidebar() {
        sidebar.classList.remove("mobile-open");
        overlay.classList.remove("visible");
        toggleButton.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
    }

    toggleButton.addEventListener("click", function () {
        if (sidebar.classList.contains("mobile-open")) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener("click", closeSidebar);

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeSidebar();
        }
    });

    sidebar.querySelectorAll(".student-nav-link").forEach(function (link) {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
});
