/**
 * Authentication Module
 * Handles user authentication, registration, and session management
 *
 * @author Moueene Development Team
 * @version 1.0.0
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

const __t = (s) => (window.I18N && window.I18N.t ? window.I18N.t(s) : s);

const Auth = {
  // API base URL
  apiUrl: "/backend/api/v1",

  /**
   * Get auth token from localStorage
   */
  getToken() {
    return localStorage.getItem("auth_token");
  },

  /**
   * Set auth token in localStorage
   */
  setToken(token) {
    localStorage.setItem("auth_token", token);
  },

  /**
   * Remove auth token
   */
  removeToken() {
    localStorage.removeItem("auth_token");
    localStorage.removeItem("user_data");
    localStorage.removeItem("user_type");
  },

  /**
   * Get user data from localStorage
   */
  getUser() {
    const userData = localStorage.getItem("user_data");
    return userData ? JSON.parse(userData) : null;
  },

  /**
   * Set user data in localStorage
   */
  setUser(userData, userType) {
    localStorage.setItem("user_data", JSON.stringify(userData));
    localStorage.setItem("user_type", userType);
  },

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return this.getToken() !== null;
  },

  /**
   * Get user type
   */
  getUserType() {
    return localStorage.getItem("user_type");
  },

  /**
   * Register new user
   */
  async register(userData) {
    try {
      console.log(
        "[Auth.register] Sending userData:",
        JSON.stringify(userData),
      );

      const response = await fetch(`${this.apiUrl}/auth/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(userData),
      });

      const result = await response.json();
      console.log("[Auth.register] Received result:", JSON.stringify(result));

      if (result.success) {
        // Auto-login after registration
        this.setToken(result.data.token);
        const resolvedType =
          result.data.user_type ||
          result.data.user?.user_type ||
          userData.user_type;
        console.log("[Auth.register] Resolved user_type:", resolvedType);
        this.setUser(result.data, resolvedType);
        console.log(
          "[Auth.register] Stored in localStorage - user_type:",
          localStorage.getItem("user_type"),
        );
        return { success: true, data: result.data, message: result.message };
      } else {
        return {
          success: false,
          message: result.message,
          errors: result.errors,
        };
      }
    } catch (error) {
      console.error("Registration error:", error);
      return {
        success: false,
        message: __t("Registration failed. Please try again."),
      };
    }
  },

  /**
   * Login user
   */
  async login(credentials) {
    try {
      const response = await fetch(`${this.apiUrl}/auth/login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(credentials),
      });

      const result = await response.json();

      if (result.success) {
        this.setToken(result.data.token);
        const resolvedType =
          result.data.user_type ||
          result.data.user?.user_type ||
          credentials.user_type;
        this.setUser(result.data.user, resolvedType);

        // Handle remember me
        if (credentials.remember_me) {
          localStorage.setItem("remember_me", "true");
        }

        return { success: true, data: result.data, message: result.message };
      } else {
        return { success: false, message: result.message };
      }
    } catch (error) {
      console.error("Login error:", error);
      return {
        success: false,
        message: __t("Login failed. Please try again."),
      };
    }
  },

  /**
   * Logout user
   */
  async logout() {
    try {
      const token = this.getToken();

      if (token) {
        await fetch(`${this.apiUrl}/auth/logout`, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
      }

      this.removeToken();
      window.location.href = "/pages/login.html";
    } catch (error) {
      console.error("Logout error:", error);
      this.removeToken();
      window.location.href = "/pages/login.html";
    }
  },

  /**
   * Get current authenticated user
   */
  async getCurrentUser() {
    try {
      const token = this.getToken();

      if (!token) {
        return null;
      }

      const response = await fetch(`${this.apiUrl}/auth/me`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      const result = await response.json();

      if (result.success) {
        this.setUser(result.data, this.getUserType());
        return result.data;
      } else {
        return null;
      }
    } catch (error) {
      console.error("Get current user error:", error);
      return null;
    }
  },

  /**
   * Update user profile
   */
  async updateProfile(profileData) {
    try {
      const token = this.getToken();

      const response = await fetch(`${this.apiUrl}/users/profile`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(profileData),
      });

      const result = await response.json();

      if (result.success) {
        this.setUser(result.data, this.getUserType());
        return { success: true, data: result.data, message: result.message };
      } else {
        return { success: false, message: result.message };
      }
    } catch (error) {
      console.error("Update profile error:", error);
      return { success: false, message: __t("Failed to update profile.") };
    }
  },

  /**
   * Change password
   */
  async changePassword(passwordData) {
    try {
      const token = this.getToken();

      const response = await fetch(`${this.apiUrl}/users/change-password`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(passwordData),
      });

      const result = await response.json();

      return {
        success: result.success,
        message: result.message,
      };
    } catch (error) {
      console.error("Change password error:", error);
      return { success: false, message: __t("Failed to change password.") };
    }
  },

  /**
   * Request password reset
   */
  async forgotPassword(email, userType) {
    try {
      const response = await fetch(`${this.apiUrl}/auth/forgot-password`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, user_type: userType }),
      });

      const result = await response.json();

      return {
        success: result.success,
        message: result.message,
      };
    } catch (error) {
      console.error("Forgot password error:", error);
      return { success: false, message: "Failed to send reset email." };
    }
  },

  /**
   * Reset password
   */
  async resetPassword(token, password, userType) {
    try {
      const response = await fetch(`${this.apiUrl}/auth/reset-password`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ token, password, user_type: userType }),
      });

      const result = await response.json();

      return {
        success: result.success,
        message: result.message,
      };
    } catch (error) {
      console.error("Reset password error:", error);
      return { success: false, message: "Failed to reset password." };
    }
  },

  /**
   * Verify email
   */
  async verifyEmail(token) {
    try {
      const response = await fetch(`${this.apiUrl}/auth/verify-email`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ token }),
      });

      const result = await response.json();

      return {
        success: result.success,
        message: result.message,
      };
    } catch (error) {
      console.error("Verify email error:", error);
      return { success: false, message: "Email verification failed." };
    }
  },

  /**
   * Redirect to dashboard based on user type
   */
  redirectToDashboard() {
    const userType = this.getUserType();

    if (userType === "provider") {
      window.location.href = "/pages/provider-dashboard.html";
    } else if (userType === "admin") {
      window.location.href = "/pages/admin-dashboard.html";
    } else {
      window.location.href = "/pages/dashboard.html";
    }
  },

  /**
   * Require authentication (redirect if not authenticated)
   */
  requireAuth() {
    if (!this.isAuthenticated()) {
      window.location.href = "/pages/login.html";
      return false;
    }
    return true;
  },

  /**
   * Require specific user type
   */
  requireUserType(allowedTypes) {
    if (!this.requireAuth()) {
      return false;
    }

    const userType = this.getUserType();
    const types = Array.isArray(allowedTypes) ? allowedTypes : [allowedTypes];

    if (!types.includes(userType)) {
      this.showModal({
        type: "error",
        title: __t("Access denied"),
        message: __t("You do not have permission to view this page."),
      }).then(() => this.redirectToDashboard());
      return false;
    }

    return true;
  },
};

// UI helpers (in-page modal)
Auth.showModal = function (options = {}) {
  const config = {
    type: options.type || "info",
    title: options.title || __t("Message"),
    message: options.message || "",
    okText: options.okText || __t("OK"),
  };

  const overlayId = "mm-modal-overlay";
  let overlay = document.getElementById(overlayId);

  const ensureModal = () => {
    if (overlay) return overlay;

    overlay = document.createElement("div");
    overlay.id = overlayId;
    overlay.className = "mm-modal-overlay";
    overlay.setAttribute("role", "dialog");
    overlay.setAttribute("aria-modal", "true");
    overlay.setAttribute("aria-hidden", "true");

    overlay.innerHTML = `
      <div class="mm-modal" role="document" tabindex="-1">
        <div class="mm-modal-header">
          <div class="mm-modal-meta">
            <div class="mm-modal-icon mm-modal-icon--info" aria-hidden="true">i</div>
            <h3 class="mm-modal-title"></h3>
          </div>
          <button type="button" class="mm-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="mm-modal-body">
          <p class="mm-modal-message"></p>
        </div>
        <div class="mm-modal-actions">
          <button type="button" class="btn btn-primary mm-modal-ok">OK</button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
  };

  const iconByType = {
    success: { klass: "mm-modal-icon--success", text: "✓" },
    error: { klass: "mm-modal-icon--error", text: "!" },
    warning: { klass: "mm-modal-icon--warning", text: "!" },
    info: { klass: "mm-modal-icon--info", text: "i" },
  };

  return new Promise((resolve) => {
    const el = ensureModal();
    const modal = el.querySelector(".mm-modal");
    const titleEl = el.querySelector(".mm-modal-title");
    const msgEl = el.querySelector(".mm-modal-message");
    const okBtn = el.querySelector(".mm-modal-ok");
    const closeBtn = el.querySelector(".mm-modal-close");
    const iconEl = el.querySelector(".mm-modal-icon");

    const icon = iconByType[config.type] || iconByType.info;
    iconEl.className = `mm-modal-icon ${icon.klass}`;
    iconEl.textContent = icon.text;

    titleEl.textContent = __t(config.title);
    msgEl.textContent = __t(config.message);
    okBtn.textContent = __t(config.okText);

    let lastActive = document.activeElement;

    const cleanup = () => {
      el.classList.remove("is-open");
      el.setAttribute("aria-hidden", "true");
      document.removeEventListener("keydown", onKeyDown);
      okBtn.removeEventListener("click", onOk);
      closeBtn.removeEventListener("click", onClose);
      el.removeEventListener("click", onOverlayClick);
      if (lastActive && typeof lastActive.focus === "function") {
        lastActive.focus();
      }
    };

    const close = () => {
      cleanup();
      resolve(true);
    };

    const onOk = () => close();
    const onClose = () => close();
    const onOverlayClick = (e) => {
      if (e.target === el) close();
    };
    const onKeyDown = (e) => {
      if (e.key === "Escape") close();
      if (e.key === "Enter" && el.classList.contains("is-open")) close();
    };

    okBtn.addEventListener("click", onOk);
    closeBtn.addEventListener("click", onClose);
    el.addEventListener("click", onOverlayClick);
    document.addEventListener("keydown", onKeyDown);

    el.classList.add("is-open");
    el.setAttribute("aria-hidden", "false");

    // focus
    setTimeout(() => {
      if (modal) modal.focus();
    }, 0);
  });
};

// Make Auth available globally
window.Auth = Auth;
