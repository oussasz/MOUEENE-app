/**
 * Provider UI Helper
 * Keeps provider header/sidebar UI (including verification badge) in sync with the latest account state.
 */

// Ensure i18n is initialized if any shared JS already started loading it
if (window.__moueeneI18NReady) {
  window.__moueeneI18NReady.then((i18n) => i18n && i18n.init && i18n.init());
}

const __t = (s) => (window.I18N && window.I18N.t ? window.I18N.t(s) : s);

(function () {
  function safeSetText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function safeSetSrc(id, src) {
    const el = document.getElementById(id);
    if (el) el.src = src;
  }

  function setBadge(el, className, html, background) {
    el.className = className;
    el.innerHTML = html;
    if (background) {
      el.style.background = background;
      el.style.color = "white";
    } else {
      el.style.background = "";
      el.style.color = "";
    }
  }

  function applyVerificationAndAccountBadge(user) {
    const badge = document.getElementById("verification-badge");
    if (!badge || !user) return;

    const accountStatus = (user.account_status || "active").toLowerCase();
    const verificationStatus = (
      user.verification_status || "pending"
    ).toLowerCase();

    // Account status takes precedence if it's not active
    if (accountStatus && accountStatus !== "active") {
      if (accountStatus === "suspended") {
        setBadge(
          badge,
          "badge",
          `<i class="fas fa-ban"></i> ${__t("Account Suspended")}`,
          "linear-gradient(135deg, #ef4444, #dc2626)",
        );
        return;
      }
      if (accountStatus === "deactivated") {
        setBadge(
          badge,
          "badge",
          `<i class="fas fa-user-slash"></i> ${__t("Account Deactivated")}`,
          "linear-gradient(135deg, #6b7280, #374151)",
        );
        return;
      }
      setBadge(
        badge,
        "badge",
        `<i class="fas fa-info-circle"></i> ${__t("Account")}: ${accountStatus}`,
        "linear-gradient(135deg, #f59e0b, #d97706)",
      );
      return;
    }

    // Verification status
    if (verificationStatus === "verified") {
      setBadge(
        badge,
        "badge badge-verified",
        `<i class="fas fa-check-circle"></i> ${__t("Verified Provider")}`,
      );
      return;
    }

    if (verificationStatus === "rejected") {
      setBadge(
        badge,
        "badge",
        `<i class="fas fa-times-circle"></i> ${__t("Verification Rejected")}`,
        "linear-gradient(135deg, #ef4444, #dc2626)",
      );
      return;
    }

    // Default/pending
    setBadge(
      badge,
      "badge badge-pending",
      `<i class="fas fa-clock"></i> ${__t("Pending Verification")}`,
    );
  }

  async function getFreshUser() {
    try {
      if (typeof Auth !== "undefined" && Auth.getCurrentUser) {
        const fresh = await Auth.getCurrentUser();
        return fresh || (Auth.getUser ? Auth.getUser() : null);
      }
      return typeof Auth !== "undefined" && Auth.getUser
        ? Auth.getUser()
        : null;
    } catch {
      return typeof Auth !== "undefined" && Auth.getUser
        ? Auth.getUser()
        : null;
    }
  }

  async function loadAndApply() {
    const user = await getFreshUser();
    if (!user) return null;

    const fullName = `${user.first_name || ""} ${user.last_name || ""}`.trim();
    const providerName = user.business_name || fullName || __t("Provider");

    safeSetText("provider-name", providerName);
    safeSetText("header-user-name", user.first_name || __t("Provider"));
    safeSetText("dropdown-user-name", fullName || providerName);
    safeSetText("dropdown-user-email", user.email || "");

    const avatarSrc =
      user.profile_picture && user.profile_picture !== ""
        ? user.profile_picture
        : "../assets/images/default-avatar.jpg";

    safeSetSrc("provider-avatar", avatarSrc);
    safeSetSrc("header-avatar", avatarSrc);
    safeSetSrc("settings-avatar", avatarSrc);

    applyVerificationAndAccountBadge(user);

    return user;
  }

  window.ProviderUI = {
    loadAndApply,
    getFreshUser,
    applyVerificationAndAccountBadge,
  };
})();
