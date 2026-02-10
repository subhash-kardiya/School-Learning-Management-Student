const toggleBtn = document.getElementById("sidebarToggle");
const sidebar = document.querySelector(".sidebar");
const body = document.body;

// Create an overlay div for clicking outside to close
const overlay = document.createElement("div");
overlay.className = "sidebar-overlay";
body.appendChild(overlay);

if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    });
}

// Close sidebar when clicking the overlay
overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
});

// Initialize Bootstrap offcanvas component
const offcanvasElement = document.getElementById("offcanvasWithBothOptions");
// Explicitly set the backdrop property during initialization
if (offcanvasElement) {
    const offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
        backdrop: true, // Ensure the backdrop is properly set
    });
}

// Highlight the active menu item based on the current URL
const currentPath = window.location.pathname;
const menuItems = document.querySelectorAll(".sidebar a");

menuItems.forEach((item) => {
    if (item.href.includes(currentPath)) {
        item.classList.add("active");
    }
});
