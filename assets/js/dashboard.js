/**
 * Dashboard Module
 * Handles customer dashboard functionality
 *
 * @author Moueene Development Team
 * @version 1.0.0
 */

function __t(s) {
  return window.I18N && window.I18N.t ? window.I18N.t(s) : s;
}

function __lang() {
  return window.I18N && window.I18N.getLanguage
    ? window.I18N.getLanguage()
    : "en";
}

const Dashboard = {
  apiUrl: "/backend/api/v1",

  /**
   * Initialize dashboard
   */
  async init() {
    // Require authentication
    if (!Auth.requireAuth()) {
      return;
    }

    // Require customer access
    if (!Auth.requireUserType("user")) {
      return;
    }

    // Load user data
    await this.loadUserProfile();
    await this.loadStats();
    await this.loadUpcomingBookings();
    this.setupEventListeners();
  },

  /**
   * Load user profile
   */
  async loadUserProfile() {
    try {
      const user = await Auth.getCurrentUser();

      if (user) {
        // Update username in header
        const headerNameEl = document.getElementById("headerName");
        if (headerNameEl) {
          headerNameEl.textContent = user.first_name || __t("User");
        }

        // Update sidebar and dropdown names
        const sidebarNameEl = document.getElementById("sidebarName");
        const dropdownNameEl = document.getElementById("dropdownName");
        const fullName =
          `${user.first_name || ""} ${user.last_name || ""}`.trim();

        if (sidebarNameEl) sidebarNameEl.textContent = fullName;
        if (dropdownNameEl) dropdownNameEl.textContent = fullName;

        if (user.email) {
          const emailEl = document.getElementById("dropdownEmail");
          if (emailEl) emailEl.textContent = user.email;
        }

        // Update profile pictures (all instances)
        const userAvatars = document.querySelectorAll(".user-avatar");
        const avatarSrc =
          user.profile_picture && user.profile_picture !== ""
            ? user.profile_picture
            : "../assets/images/default-avatar.jpg";
        userAvatars.forEach((img) => (img.src = avatarSrc));

        // Update welcome message
        const welcomeMessage = document.querySelector(".dashboard-welcome h1");
        if (welcomeMessage) {
          const name = user.first_name || __t("User");
          welcomeMessage.textContent = `${__t("Welcome back")}, ${name}!`;
        }
      }
    } catch (error) {
      console.error("Error loading profile:", error);
    }
  },

  /**
   * Load dashboard statistics
   */
  async loadStats() {
    try {
      const token = Auth.getToken();
      const response = await fetch(`${this.apiUrl}/users/bookings`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      // Check if response is ok before parsing JSON
      if (!response.ok) {
        console.warn("Could not load bookings, using default values");
        this.setDefaultStats();
        return;
      }

      const text = await response.text();
      if (!text) {
        console.warn("Empty response from bookings API");
        this.setDefaultStats();
        return;
      }

      const result = JSON.parse(text);

      if (result.success && Array.isArray(result.data)) {
        const bookings = result.data;

        // Calculate stats
        const totalBookings = bookings.length;
        const upcoming = bookings.filter((b) =>
          ["pending", "confirmed"].includes(b.booking_status),
        ).length;
        const completed = bookings.filter(
          (b) => b.booking_status === "completed",
        ).length;

        // Update stat cards - new HTML structure
        const activeBookingsEl = document.getElementById("activeBookings");
        const completedBookingsEl =
          document.getElementById("completedBookings");

        if (activeBookingsEl) activeBookingsEl.textContent = upcoming;
        if (completedBookingsEl) completedBookingsEl.textContent = completed;

        // Legacy stat cards support
        const statCards = document.querySelectorAll(".dashboard-stat-card h3");
        if (statCards.length >= 3) {
          statCards[0].textContent = totalBookings;
          statCards[1].textContent = upcoming;
          statCards[2].textContent = completed;
        }
      } else {
        this.setDefaultStats();
      }
    } catch (error) {
      console.error("Error loading stats:", error);
      this.setDefaultStats();
    }
  },

  /**
   * Set default stats when API fails
   */
  setDefaultStats() {
    const activeBookingsEl = document.getElementById("activeBookings");
    const completedBookingsEl = document.getElementById("completedBookings");

    if (activeBookingsEl) activeBookingsEl.textContent = "0";
    if (completedBookingsEl) completedBookingsEl.textContent = "0";

    const statCards = document.querySelectorAll(".dashboard-stat-card h3");
    statCards.forEach((card) => (card.textContent = "0"));
  },

  /**
   * Load upcoming bookings
   */
  async loadUpcomingBookings() {
    try {
      const token = Auth.getToken();
      const response = await fetch(`${this.apiUrl}/users/bookings`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      // Check if response is ok before parsing JSON
      if (!response.ok) {
        console.warn("Could not load upcoming bookings");
        return;
      }

      const text = await response.text();
      if (!text) {
        return;
      }

      const result = JSON.parse(text);

      if (result.success && Array.isArray(result.data)) {
        const bookings = result.data;
        const upcomingBookings = bookings
          .filter((b) => ["pending", "confirmed"].includes(b.booking_status))
          .slice(0, 5);

        this.renderBookings(upcomingBookings);
      }
    } catch (error) {
      console.error("Error loading bookings:", error);
    }
  },

  /**
   * Render bookings list
   */
  renderBookings(bookings) {
    const container = document.querySelector(".dashboard-content");

    // Check if container exists
    if (!container) {
      console.warn("Dashboard content container not found");
      return;
    }

    if (bookings.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <p style="color: var(--text-light);">${__t("No active bookings found.")}</p>
        </div>
      `;
      return;
    }

    // Modern list view matching new design
    container.innerHTML = bookings
      .map(
        (booking) => `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="${booking.provider_picture || "../assets/images/default-avatar.jpg"}" 
                     alt="${booking.provider_first_name}" 
                     style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover;">
                <div>
                    <div style="font-weight: 600; color: var(--text-dark);">${booking.service_name}</div>
                    <div style="font-size: 0.85rem; color: #64748b;">
                      ${__t("with")} ${booking.provider_first_name} ${booking.provider_last_name}
                    </div>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 4px;">
                    ${this.formatDate(booking.booking_date)}
                </div>
                <span class="badge badge-${this.getStatusClass(booking.booking_status)}" 
                      style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; background: ${this.getStatusColor(booking.booking_status)}; color: white;">
                    ${__t(this.capitalizeFirst(booking.booking_status))}
                </span>
            </div>
        </div>
    `,
      )
      .join("");

    if (window.I18N && window.I18N.refresh) {
      window.I18N.refresh();
    }
  },

  getStatusColor(status) {
    const colors = {
      pending: "#f59e0b",
      confirmed: "#10b981",
      completed: "#3b82f6",
      cancelled: "#ef4444",
    };
    return colors[status] || "#94a3b8";
  },

  /**
   * Setup event listeners
   */
  setupEventListeners() {
    // Logout button
    const logoutBtn = document.querySelector('a[href="login.html"]');
    if (logoutBtn) {
      logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        Auth.logout();
      });
    }

    // Profile settings link
    const settingsLink = document.querySelector('a[href="#"] i.fa-cog');
    if (settingsLink) {
      settingsLink.parentElement.addEventListener("click", (e) => {
        e.preventDefault();
        window.location.href = "/pages/profile-edit.html";
      });
    }
  },

  /**
   * Format date for display
   */
  formatDate(dateString) {
    const date = new Date(dateString);
    const lang = __lang();
    const locale = lang === "fr" ? "fr-FR" : lang === "ar" ? "ar" : "en-US";
    return date.toLocaleDateString(locale, {
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  },

  /**
   * Get status badge class
   */
  getStatusClass(status) {
    const statusClasses = {
      pending: "warning",
      confirmed: "success",
      completed: "info",
      cancelled: "danger",
    };
    return statusClasses[status] || "secondary";
  },

  /**
   * Capitalize first letter
   */
  capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  },
};

// Initialize dashboard when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  Dashboard.init();
});

// Make Dashboard available globally
window.Dashboard = Dashboard;
