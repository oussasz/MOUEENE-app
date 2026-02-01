/**
 * MOUEENE - Main JavaScript
 * Service Provider Platform
 */

// --- i18n bootstrap (loads assets/js/i18n.js once, then initializes) ---
(function ensureI18NLoaded() {
  if (window.__moueeneI18NReady) {
    window.__moueeneI18NReady.then((i18n) => i18n && i18n.init && i18n.init());
    return;
  }

  window.__moueeneI18NReady = new Promise((resolve) => {
    if (window.I18N) {
      resolve(window.I18N);
      return;
    }

    try {
      const base =
        document.currentScript && document.currentScript.src
          ? new URL(".", document.currentScript.src)
          : new URL("/assets/js/", window.location.origin);
      const src = new URL("i18n.js", base).toString();
      const script = document.createElement("script");
      script.src = src;
      script.defer = true;
      script.onload = () => resolve(window.I18N);
      script.onerror = () => resolve(null);
      document.head.appendChild(script);
    } catch (e) {
      resolve(null);
    }
  });

  window.__moueeneI18NReady.then((i18n) => i18n && i18n.init && i18n.init());
})();

document.addEventListener("DOMContentLoaded", function () {
  // Initialize all components
  initNavbar();
  initScrollEffects();
  initFAQ();
  initMobileMenu();
  initMobileAppShell();
  initFormValidation();
  initImagePlaceholders();
});

/**
 * Navbar Functionality
 */
function initNavbar() {
  const header = document.querySelector(".header");

  // Many pages (dashboards/admin/auth) don't use the marketing header.
  // Avoid runtime errors on scroll.
  if (!header) return;

  window.addEventListener("scroll", function () {
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });
}

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");
  const navButtons = document.querySelector(".nav-buttons");

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", function () {
      this.classList.toggle("active");
      navMenu.classList.toggle("active");

      // Animate hamburger
      const spans = this.querySelectorAll("span");
      if (this.classList.contains("active")) {
        spans[0].style.transform = "rotate(45deg) translate(5px, 5px)";
        spans[1].style.opacity = "0";
        spans[2].style.transform = "rotate(-45deg) translate(7px, -6px)";
      } else {
        spans[0].style.transform = "none";
        spans[1].style.opacity = "1";
        spans[2].style.transform = "none";
      }
    });
  }

  // Close menu when clicking on a link
  if (navMenu && hamburger) {
    document.querySelectorAll(".nav-menu a").forEach((link) => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");
        hamburger.classList.remove("active");
      });
    });
  }
}

/**
 * Mobile App Shell (bottom tab bar + app-style menu overlay)
 * - Runs only on small screens
 * - Does not change desktop UI
 */
function initMobileAppShell() {
  const isMobile =
    window.matchMedia && window.matchMedia("(max-width: 768px)").matches;
  if (!isMobile) return;

  document.body.classList.add("app-mobile", "app-has-tabbar");

  // Backdrop overlay used for hamburger menu on mobile
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");
  if (hamburger && navMenu) {
    const overlay = document.createElement("div");
    overlay.className = "app-overlay";
    overlay.addEventListener("click", () => {
      navMenu.classList.remove("active");
      hamburger.classList.remove("active");
      document.body.classList.remove("app-menu-open");
    });
    document.body.appendChild(overlay);

    const syncOverlay = () => {
      const open =
        navMenu.classList.contains("active") ||
        hamburger.classList.contains("active");
      document.body.classList.toggle("app-menu-open", open);
    };

    hamburger.addEventListener("click", syncOverlay);
    navMenu
      .querySelectorAll("a")
      .forEach((a) => a.addEventListener("click", syncOverlay));
  }

  // Inject bottom tab bar
  if (document.querySelector(".app-tabbar")) return;

  const current = (window.location.pathname || "").toLowerCase();

  const t = (s) => (window.I18N && window.I18N.t ? window.I18N.t(s) : s);
  const token = (() => {
    try {
      return localStorage.getItem("auth_token");
    } catch {
      return null;
    }
  })();
  const userType = (() => {
    try {
      return localStorage.getItem("user_type");
    } catch {
      return null;
    }
  })();
  const isAuthed = !!token;

  // Clean URLs (served via router.php + .htaccess)
  const hrefHome = "/";
  const hrefServices = "/services";
  const hrefProviders = "/providers";

  // Auth-aware destinations
  const hrefDashboard =
    userType === "provider" ? "/provider/dashboard" : "/dashboard";

  const hrefMessages =
    userType === "provider" ? "/provider/messages" : "/messages";

  const hrefAccount = isAuthed
    ? userType === "provider"
      ? "/provider/settings"
      : "/profile"
    : "/login";

  const tabs = [
    {
      href: hrefHome,
      icon: "fa-house",
      label: t("Home"),
      aria: t("Home"),
      match: ["/", "/index.html"],
    },
    {
      href: hrefServices,
      icon: "fa-bolt",
      label: t("Services"),
      aria: t("Services"),
      match: ["/pages/services.html"],
    },
  ];

  // Mobile primary nav differs depending on auth state
  if (isAuthed) {
    tabs.push(
      {
        href: hrefDashboard,
        icon: "fa-table-columns",
        label: t("Dashboard"),
        aria: t("Dashboard"),
        match: ["/pages/dashboard.html", "/pages/provider-dashboard.html"],
      },
      {
        href: hrefMessages,
        icon: "fa-comments",
        label: t("Messages"),
        aria: t("Messages"),
        match: ["/pages/messages.html", "/pages/provider-messages.html"],
      },
    );
  } else {
    tabs.push(
      {
        href: hrefProviders,
        icon: "fa-users",
        label: t("Providers"),
        aria: t("Providers"),
        match: ["/pages/providers.html", "/pages/provider-profile.html"],
      },
      {
        href: "#",
        icon: "fa-ellipsis",
        label: t("More"),
        aria: t("More"),
        kind: "more",
        match: [],
      },
    );
  }

  tabs.push({
    href: hrefAccount,
    icon: isAuthed ? "fa-circle-user" : "fa-right-to-bracket",
    label: isAuthed ? t("Account") : t("Login"),
    aria: isAuthed ? t("Account") : t("Login"),
    match: [
      "/pages/profile.html",
      "/pages/settings.html",
      "/pages/profile-edit.html",
      "/pages/login.html",
      "/pages/register.html",
    ],
  });

  const tabbar = document.createElement("nav");
  tabbar.className = "app-tabbar";
  tabbar.setAttribute("aria-label", "Primary");
  tabbar.style.gridTemplateColumns = `repeat(${tabs.length}, minmax(0, 1fr))`;

  // Guest-only "More" sheet
  let sheetOverlay = null;
  let sheet = null;
  const openSheet = () => {
    if (!sheetOverlay || !sheet) return;
    document.body.classList.add("app-sheet-open");
  };
  const closeSheet = () => {
    document.body.classList.remove("app-sheet-open");
  };

  if (!isAuthed) {
    sheetOverlay = document.createElement("div");
    sheetOverlay.className = "app-sheet-overlay";
    sheetOverlay.addEventListener("click", closeSheet);

    sheet = document.createElement("div");
    sheet.className = "app-sheet";
    sheet.setAttribute("role", "dialog");
    sheet.setAttribute("aria-label", "More");

    const items = [
      { href: "/about", label: t("About") },
      { href: "/contact", label: t("Contact") },
      { href: "/faq", label: t("FAQ") },
      { href: "/help", label: t("Help") },
      { href: "/terms", label: t("Terms") },
      { href: "/privacy", label: t("Privacy") },
      { href: "/register", label: t("Sign Up") },
    ];

    sheet.innerHTML = `
      <div class="app-sheet__handle" aria-hidden="true"></div>
      <div class="app-sheet__title">${t("More")}</div>
      <div class="app-sheet__list">
        ${items
          .map(
            (it) =>
              `<a class="app-sheet__item" href="${it.href}">${it.label}</a>`,
          )
          .join("")}
      </div>
    `;

    sheet.addEventListener("click", (e) => {
      const a = e.target && e.target.closest ? e.target.closest("a") : null;
      if (a) closeSheet();
    });

    document.body.appendChild(sheetOverlay);
    document.body.appendChild(sheet);

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeSheet();
    });
  }

  tabs.forEach((t) => {
    const a = document.createElement("a");
    a.className = "app-tabbar__item";
    a.href = t.href;
    a.setAttribute("aria-label", t.aria || t.label);
    a.innerHTML = `
      <span class="app-tabbar__icon"><i class="fa-solid ${t.icon}"></i></span>
      <span class="app-tabbar__label">${t.label}</span>
    `;
    const active = t.match.some((m) =>
      m === "/" ? current === "/" : current.endsWith(m),
    );
    if (active) {
      a.classList.add("active");
      a.setAttribute("aria-current", "page");
    }
    if (t.kind === "more") {
      a.classList.add("app-tabbar__item--button");
      a.addEventListener("click", (e) => {
        e.preventDefault();
        openSheet();
      });
    }

    tabbar.appendChild(a);
  });

  document.body.appendChild(tabbar);
}

/**
 * Scroll Effects and Animations
 */
function initScrollEffects() {
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href !== "#") {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }
    });
  });

  // Fade in on scroll
  const fadeElements = document.querySelectorAll(".fade-in");

  const fadeObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
        }
      });
    },
    {
      threshold: 0.1,
    },
  );

  fadeElements.forEach((el) => fadeObserver.observe(el));

  // Counter animation for stats
  const statNumbers = document.querySelectorAll(".stat-number");

  const counterObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (
          entry.isIntersecting &&
          !entry.target.classList.contains("counted")
        ) {
          animateCounter(entry.target);
          entry.target.classList.add("counted");
        }
      });
    },
    {
      threshold: 0.5,
    },
  );

  statNumbers.forEach((stat) => counterObserver.observe(stat));
}

/**
 * Animate Counter
 */
function animateCounter(element) {
  const text = element.textContent;
  const hasPlus = text.includes("+");
  const isDecimal = text.includes(".");
  const target = parseFloat(text.replace(/[^0-9.]/g, ""));
  const duration = 2000;
  const step = target / (duration / 16);
  let current = 0;

  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      current = target;
      clearInterval(timer);
    }

    let display = isDecimal
      ? current.toFixed(1)
      : Math.floor(current).toLocaleString();
    element.textContent = display + (hasPlus ? "+" : "");
  }, 16);
}

/**
 * FAQ Accordion
 */
function initFAQ() {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");

    question.addEventListener("click", () => {
      const isActive = item.classList.contains("active");

      // Close all items
      faqItems.forEach((faq) => {
        faq.classList.remove("active");
      });

      // Open clicked item if it wasn't active
      if (!isActive) {
        item.classList.add("active");
      }
    });
  });
}

/**
 * Form Validation
 */
function initFormValidation() {
  const forms = document.querySelectorAll("form[data-validate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      let isValid = true;

      // Clear previous errors
      form.querySelectorAll(".error-message").forEach((err) => err.remove());
      form.querySelectorAll(".form-group").forEach((group) => {
        group.classList.remove("error");
      });

      // Validate required fields
      form.querySelectorAll("[required]").forEach((field) => {
        if (!field.value.trim()) {
          isValid = false;
          showFieldError(field, "This field is required");
        }
      });

      // Validate email fields
      form.querySelectorAll('input[type="email"]').forEach((field) => {
        if (field.value && !isValidEmail(field.value)) {
          isValid = false;
          showFieldError(field, "Please enter a valid email address");
        }
      });

      // Validate password match
      const password = form.querySelector('input[name="password"]');
      const confirmPassword = form.querySelector(
        'input[name="confirm_password"]',
      );

      if (
        password &&
        confirmPassword &&
        password.value !== confirmPassword.value
      ) {
        isValid = false;
        showFieldError(confirmPassword, "Passwords do not match");
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  });
}

function showFieldError(field, message) {
  const formGroup = field.closest(".form-group");
  if (formGroup) {
    formGroup.classList.add("error");
    const errorDiv = document.createElement("div");
    errorDiv.className = "error-message";
    errorDiv.textContent =
      window.I18N && window.I18N.t ? window.I18N.t(message) : message;
    errorDiv.style.color = "#e53e3e";
    errorDiv.style.fontSize = "0.875rem";
    errorDiv.style.marginTop = "5px";
    formGroup.appendChild(errorDiv);
  }
}

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

/**
 * Image Placeholders
 * Creates placeholder images for categories and providers
 */
function initImagePlaceholders() {
  // Handle broken images with placeholder
  document.querySelectorAll("img").forEach((img) => {
    img.addEventListener("error", function () {
      this.src = createPlaceholderDataURL(this.alt || "Image");
    });

    // Check if src doesn't exist
    if (!img.complete || img.naturalHeight === 0) {
      img.src = createPlaceholderDataURL(img.alt || "Image");
    }
  });
}

function createPlaceholderDataURL(text) {
  const canvas = document.createElement("canvas");
  canvas.width = 400;
  canvas.height = 300;
  const ctx = canvas.getContext("2d");

  // Create gradient background
  const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
  gradient.addColorStop(0, "#00C9A7");
  gradient.addColorStop(1, "#FF6B9D");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  // Add text
  ctx.fillStyle = "rgba(255, 255, 255, 0.9)";
  ctx.font = "bold 20px Poppins, sans-serif";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";

  // Truncate text if too long
  const maxLength = 20;
  const displayText =
    text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
  ctx.fillText(displayText, canvas.width / 2, canvas.height / 2);

  return canvas.toDataURL();
}

/**
 * Search Functionality
 */
function handleSearch(event) {
  event.preventDefault();
  const form = event.target;
  const searchInput = form.querySelector('input[type="text"]');
  const categorySelect = form.querySelector("select");

  const searchTerm = searchInput.value.trim();
  const category = categorySelect.value;

  let url = "pages/services.html?";
  if (searchTerm) url += `search=${encodeURIComponent(searchTerm)}&`;
  if (category) url += `category=${encodeURIComponent(category)}`;

  window.location.href = url;
}

/**
 * Rating Stars Component
 */
function initRatingStars() {
  const ratingContainers = document.querySelectorAll(".rating-input");

  ratingContainers.forEach((container) => {
    const stars = container.querySelectorAll(".star");
    const input = container.querySelector('input[type="hidden"]');

    stars.forEach((star, index) => {
      star.addEventListener("click", () => {
        const rating = index + 1;
        input.value = rating;

        stars.forEach((s, i) => {
          s.classList.toggle("active", i < rating);
        });
      });

      star.addEventListener("mouseenter", () => {
        stars.forEach((s, i) => {
          s.classList.toggle("hover", i <= index);
        });
      });

      star.addEventListener("mouseleave", () => {
        stars.forEach((s) => s.classList.remove("hover"));
      });
    });
  });
}

/**
 * Filter Services
 */
function filterServices() {
  const category = document.getElementById("category-filter")?.value;
  const priceRange = document.getElementById("price-filter")?.value;
  const rating = document.getElementById("rating-filter")?.value;
  const sortBy = document.getElementById("sort-filter")?.value;

  // In a real application, this would make an API call
  // For the prototype, we'll just reload with query params
  let url = "services.html?";
  if (category) url += `category=${category}&`;
  if (priceRange) url += `price=${priceRange}&`;
  if (rating) url += `rating=${rating}&`;
  if (sortBy) url += `sort=${sortBy}`;

  window.location.href = url;
}

/**
 * Booking Modal
 */
function openBookingModal(providerId, serviceName) {
  const modal = document.getElementById("booking-modal");
  if (modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";

    // Set provider info
    const serviceTitle = modal.querySelector(".service-title");
    if (serviceTitle) serviceTitle.textContent = serviceName;
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "";
  }
}

// Close modal on outside click
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("modal-overlay")) {
    e.target.style.display = "none";
    document.body.style.overflow = "";
  }
});

/**
 * Dashboard Tabs
 */
function initDashboardTabs() {
  const tabs = document.querySelectorAll(".dashboard-tab");
  const contents = document.querySelectorAll(".dashboard-tab-content");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const target = tab.dataset.tab;

      tabs.forEach((t) => t.classList.remove("active"));
      contents.forEach((c) => c.classList.remove("active"));

      tab.classList.add("active");
      document.getElementById(target)?.classList.add("active");
    });
  });
}

/**
 * Notifications
 */
function showNotification(message, type = "success") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;

  // Style the notification
  Object.assign(notification.style, {
    position: "fixed",
    top: "100px",
    right: "20px",
    padding: "15px 20px",
    borderRadius: "10px",
    backgroundColor:
      type === "success" ? "#00C9A7" : type === "error" ? "#e53e3e" : "#3182ce",
    color: "white",
    boxShadow: "0 4px 15px rgba(0,0,0,0.2)",
    zIndex: "9999",
    display: "flex",
    alignItems: "center",
    gap: "15px",
    animation: "slideIn 0.3s ease",
  });

  document.body.appendChild(notification);

  notification
    .querySelector(".notification-close")
    .addEventListener("click", () => {
      notification.remove();
    });

  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease";
    setTimeout(() => notification.remove(), 300);
  }, 5000);
}

// Add animation keyframes
const style = document.createElement("style");
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
