/**
 * Admin Mobile UI
 * - Provides a usable navigation experience on phones (drawer + overlay)
 * - Non-invasive: only activates if an .admin-sidebar exists
 */

(function () {
  function isMobile() {
    return (
      window.matchMedia && window.matchMedia("(max-width: 768px)").matches
    );
  }

  function closeMenu() {
    document.body.classList.remove("admin-menu-open");
  }

  function toggleMenu() {
    document.body.classList.toggle("admin-menu-open");
  }

  document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".admin-sidebar");
    const header = document.querySelector(".admin-header");
    if (!sidebar || !header) return;

    // Only needed on mobile; still safe to set up on desktop.
    if (isMobile()) {
      document.body.classList.add("admin-mobile");
    }

    // Overlay
    let overlay = document.querySelector(".admin-mobile-overlay");
    if (!overlay) {
      overlay = document.createElement("div");
      overlay.className = "admin-mobile-overlay";
      overlay.addEventListener("click", closeMenu);
      document.body.appendChild(overlay);
    }

    // Toggle button (inject if missing)
    let toggle = header.querySelector(".admin-menu-toggle");
    if (!toggle) {
      toggle = document.createElement("button");
      toggle.type = "button";
      toggle.className = "admin-menu-toggle";
      toggle.setAttribute("aria-label", "Open menu");
      toggle.innerHTML = '<i class="fas fa-bars" aria-hidden="true"></i>';

      // Insert at start of admin header
      header.insertBefore(toggle, header.firstChild);
    }

    toggle.addEventListener("click", () => {
      toggleMenu();
      toggle.setAttribute(
        "aria-label",
        document.body.classList.contains("admin-menu-open")
          ? "Close menu"
          : "Open menu",
      );
    });

    // Close menu on navigation
    sidebar.querySelectorAll("a").forEach((a) => {
      a.addEventListener("click", closeMenu);
    });

    // Escape closes
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeMenu();
    });

    // Keep state in sync on resize
    window.addEventListener("resize", () => {
      if (!isMobile()) closeMenu();
    });
  });
})();
