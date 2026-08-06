
(function () {
  const ROLE_HOME = {
    Driver: "driver.html",
    Mechanic: "workload.html",
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
