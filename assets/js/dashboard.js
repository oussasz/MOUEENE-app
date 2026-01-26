/**
 * Dashboard Module
 * Handles customer dashboard functionality
 *
 * @author Moueene Development Team
 * @version 1.0.0
 */

const Dashboard = {
  apiUrl: "/backend/api",

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
        // Update profile display
        document.querySelector(".dashboard-user h4").textContent =
          `${user.first_name} ${user.last_name}`;

        if (user.profile_picture) {
          document.querySelector(".dashboard-user img").src =
            user.profile_picture;
        }

        // Update welcome message
        const welcomeMessage = document.querySelector(".dashboard-header h2");
        if (welcomeMessage) {
          welcomeMessage.textContent = `Welcome back, ${user.first_name}!`;
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

      const result = await response.json();

      if (result.success) {
        const bookings = result.data;

        // Calculate stats
        const totalBookings = bookings.length;
        const upcoming = bookings.filter((b) =>
          ["pending", "confirmed"].includes(b.booking_status),
        ).length;
        const completed = bookings.filter(
          (b) => b.booking_status === "completed",
        ).length;

        // Update stat cards
        const statCards = document.querySelectorAll(".dashboard-stat-card h3");
        if (statCards[0]) statCards[0].textContent = totalBookings;
        if (statCards[1]) statCards[1].textContent = upcoming;
        if (statCards[2]) statCards[2].textContent = completed;
      }
    } catch (error) {
      console.error("Error loading stats:", error);
    }
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

      const result = await response.json();

      if (result.success) {
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
    const bookingsSection = container.querySelector(
      'div[style*="margin-bottom: 30px"]',
    );

    if (!bookingsSection || bookings.length === 0) {
      return;
    }

    const bookingsList = bookingsSection.querySelector(
      'div[style*="background: var(--bg-light)"]',
    );

    if (bookingsList) {
      bookingsList.innerHTML = bookings
        .map(
          (booking) => `
        <div style="display: flex; align-items: center; gap: 20px; padding: 20px; background: var(--bg-white); border-bottom: 1px solid var(--border-color);">
          <img
            src="${booking.provider_picture || "../assets/images/providers/default.jpg"}"
            alt="${booking.provider_first_name} ${booking.provider_last_name}"
            style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;"
          />
          <div style="flex: 1;">
            <h4 style="margin: 0 0 5px 0;">${booking.service_name}</h4>
            <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
              with ${booking.provider_first_name} ${booking.provider_last_name}
            </p>
            <p style="margin: 5px 0 0 0; color: var(--text-light); font-size: 0.85rem;">
              <i class="far fa-calendar"></i> ${this.formatDate(booking.booking_date)} at ${booking.booking_time}
            </p>
          </div>
          <div>
            <span class="badge badge-${this.getStatusClass(booking.booking_status)}">
              ${this.capitalizeFirst(booking.booking_status)}
            </span>
          </div>
          <div>
            <span style="font-size: 1.2rem; font-weight: 600; color: var(--primary-color);">
              $${booking.total_price}
            </span>
          </div>
        </div>
      `,
        )
        .join("");
    }
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
    return date.toLocaleDateString("en-US", {
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
