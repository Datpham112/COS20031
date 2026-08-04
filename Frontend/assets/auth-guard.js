/**
 * auth-guard.js
 * ------------------------------------------------------------------
 * Include this on every protected Frontend/*.html page (near the top
 * of <body>, before other scripts that need window.currentStaff).
 *
 * What it does:
 *  1. Calls Backend/api/me.php. If nobody is logged in, redirects to
 *     login.html immediately.
 *  2. Stores the result on window.currentStaff for other scripts to use.
 *  3. Hides any element with a data-roles="Role A,Role B" attribute
 *     unless the logged-in role is in that list.
 *  4. Wires up any element with data-action="logout" to go to
 *     Backend/auth/logout.php (which clears the session first).
 *
 * Optional: set data-page-roles="Role A,Role B" on <body> to restrict
 * the WHOLE page -- anyone else gets redirected to their own role's
 * home page instead of seeing a half-hidden page (see ROLE_HOME below;
 * this must match the redirect logic in Backend/auth/login_process.php,
 * otherwise a role that isn't allowed on dashboard.html could bounce
 * back and forth forever).
 * ------------------------------------------------------------------
 */
(function () {
  const ROLE_HOME = {
    Driver: "my_profile.html",
    Mechanic: "workload.html",
    "Driver Manager": "manage_data.html",
  };

  async function guard() {
    let me;
    try {
      const res = await fetch("../Backend/api/me.php");
      me = await res.json();
    } catch (err) {
      console.error("auth-guard: could not reach me.php", err);
      return;
    }

    if (!me.loggedIn) {
      window.location.href = "login.html";
      return;
    }

    window.currentStaff = me;

    const pageRoles = document.body.getAttribute("data-page-roles");
    if (pageRoles) {
      const allowed = pageRoles.split(",").map((r) => r.trim());
      if (!allowed.includes(me.roleType)) {
        const home = ROLE_HOME[me.roleType] || "dashboard.html";
        // Guard against redirecting to the page we're already on
        // (would otherwise be an infinite loop if ROLE_HOME is ever
        // out of sync with a page's own data-page-roles).
        if (!window.location.pathname.endsWith(home)) {
          window.location.href = home;
        }
        return;
      }
    }

    document.querySelectorAll("[data-roles]").forEach((el) => {
      const allowed = el.getAttribute("data-roles").split(",").map((r) => r.trim());
      if (!allowed.includes(me.roleType)) {
        el.style.display = "none";
      }
    });

    document.querySelectorAll('[data-action="logout"]').forEach((el) => {
      el.addEventListener("click", (e) => {
        e.preventDefault();
        window.location.href = "../Backend/auth/logout.php";
      });
    });

    document.dispatchEvent(new CustomEvent("staffReady", { detail: me }));
  }

  document.addEventListener("DOMContentLoaded", guard);
})();
