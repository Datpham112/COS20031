/**
 * dashboard.js
 * Fetches Backend/api/dashboard.php and fills in the hero stats, KPI
 * cards, and the 5 panels on dashboard.html. Loaded after script.js.
 */
(function () {
  const API_URL = "../Backend/api/dashboard.php";

  document.addEventListener("DOMContentLoaded", loadDashboard);

  async function loadDashboard() {
    let data;
    try {
      const res = await fetch(API_URL);
      if (!res.ok) throw new Error("HTTP " + res.status);
      data = await res.json();
      if (data.error) throw new Error(data.detail || data.error);
    } catch (err) {
      console.error("Failed to load dashboard data:", err);
      showLoadError();
      return;
    }

    renderHero(data.hero);
    renderKpis(data.kpis);
    renderKeyAlerts(data.keyAlerts);
    renderHighRiskDrivers(data.highRiskDrivers);
    renderVehiclesByDepot(data.vehiclesByDepot);
    renderOpenJobs(data.openJobs);
    renderCertsExpiring(data.certsExpiring);
  }

  function showLoadError() {
    const msg = "Could not load data";
    ["heroVehiclesReady", "heroInMaintenance", "heroCriticalAlerts"].forEach((id) => setText(id, "—"));
    document.getElementById("keyAlertsList").innerHTML = `<div class="alert-row"><div class="alert-body">${msg}</div></div>`;
    document.getElementById("highRiskDriversBody").innerHTML = `<tr><td colspan="4">${msg}</td></tr>`;
    document.getElementById("vehiclesByDepotList").innerHTML = `<div class="depot-row"><div class="depot-name">${msg}</div></div>`;
    document.getElementById("openJobsBody").innerHTML = `<tr><td colspan="3">${msg}</td></tr>`;
    document.getElementById("certsExpiringBody").innerHTML = `<tr><td colspan="3">${msg}</td></tr>`;
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function renderHero(hero) {
    if (!hero) return;
    setText("heroVehiclesReady", hero.vehiclesReady);
    setText("heroInMaintenance", hero.inMaintenance);
    setText("heroCriticalAlerts", hero.criticalAlerts);
  }

  function renderKpis(kpis) {
    if (!kpis) return;
    setText("kpiTotalVehicles", kpis.totalVehicles);
    setText("kpiAvgSafetyScore", (kpis.avgSafetyScore ?? 0).toFixed(1));
    setText("kpiDriversNeedingTraining", kpis.driversNeedingTraining);
    setText(
      "kpiMaintenanceCost",
      Number(kpis.maintenanceCostThisMonth || 0).toLocaleString("en-US") + " VND"
    );
  }

  function timeAgo(dateStr) {
    const then = new Date(dateStr.replace(" ", "T"));
    const diffMs = Date.now() - then.getTime();
    const mins = Math.floor(diffMs / 60000);
    if (mins < 1) return "just now";
    if (mins < 60) return mins + " min ago";
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + " hour" + (hrs > 1 ? "s" : "") + " ago";
    const days = Math.floor(hrs / 24);
    return days + " day" + (days > 1 ? "s" : "") + " ago";
  }

  function severityIconClass(severity) {
    return severity === "Critical" || severity === "High" ? "crit" : "warn";
  }

  function renderKeyAlerts(alerts) {
    const container = document.getElementById("keyAlertsList");
    if (!container) return;
    if (!alerts || alerts.length === 0) {
      container.innerHTML = `<div class="alert-row"><div class="alert-body">No alerts</div></div>`;
      return;
    }
    container.innerHTML = alerts
      .map((a) => {
        const iconClass = severityIconClass(a.severity);
        const icon = iconClass === "crit" ? "⚠" : "●";
        return `
        <div class="alert-row">
          <div class="alert-icon ${iconClass}">${icon}</div>
          <div class="alert-body">
            <div class="alert-title">${escapeHtml(a.title)}</div>
            <div class="alert-meta">${escapeHtml(a.meta)}</div>
          </div>
          <div class="alert-time">${timeAgo(a.raised)}</div>
        </div>`;
      })
      .join("");
  }

  function initials(name) {
    return name
      .split(" ")
      .filter(Boolean)
      .slice(-2)
      .map((w) => w[0])
      .join("")
      .toUpperCase();
  }

  function renderHighRiskDrivers(drivers) {
    const tbody = document.getElementById("highRiskDriversBody");
    if (!tbody) return;
    if (!drivers || drivers.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4">No high-risk drivers</td></tr>`;
      return;
    }
    tbody.innerHTML = drivers
      .map((d) => {
        const scoreClass = d.status === "Suspended" ? "score-bad" : "score-warn";
        const pillClass = d.status === "Suspended" ? "crit" : "warn";
        return `
        <tr>
          <td><div class="driver-cell"><div class="driver-avatar">${initials(d.name)}</div>${escapeHtml(d.name)}</div></td>
          <td>${escapeHtml(d.depot)}</td>
          <td class="${scoreClass}">${d.score}</td>
          <td><span class="pill ${pillClass}">${escapeHtml(d.status)}</span></td>
        </tr>`;
      })
      .join("");
  }

  function renderVehiclesByDepot(rows) {
    const container = document.getElementById("vehiclesByDepotList");
    if (!container) return;
    if (!rows || rows.length === 0) {
      container.innerHTML = `<div class="depot-row"><div class="depot-name">No depots</div></div>`;
      return;
    }
    container.innerHTML = rows
      .map((r) => {
        const pct = r.total > 0 ? Math.round((r.ready / r.total) * 100) : 0;
        const barColor = pct < 70 ? "background:var(--amber)" : "";
        return `
        <div class="depot-row">
          <div class="depot-name">${escapeHtml(r.depot)}</div>
          <div class="bar-track"><div class="bar-fill" style="width:${pct}%;${barColor}"></div></div>
          <div class="depot-count">${r.ready}/${r.total}</div>
        </div>`;
      })
      .join("");
  }

  function renderOpenJobs(jobs) {
    const tbody = document.getElementById("openJobsBody");
    if (!tbody) return;
    if (!jobs || jobs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3">No open jobs</td></tr>`;
      return;
    }
    tbody.innerHTML = jobs
      .map(
        (j) => `
        <tr>
          <td class="mono">${escapeHtml(j.job)}</td>
          <td>${escapeHtml(j.workshop)}</td>
          <td><span class="pill warn">${escapeHtml(j.status)}</span></td>
        </tr>`
      )
      .join("");
  }

  function renderCertsExpiring(certs) {
    const tbody = document.getElementById("certsExpiringBody");
    if (!tbody) return;
    if (!certs || certs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3">No certifications expiring soon</td></tr>`;
      return;
    }
    tbody.innerHTML = certs
      .map((c) => {
        const color = c.daysLeft <= 7 ? "var(--red)" : "var(--amber)";
        return `
        <tr>
          <td>${escapeHtml(c.driver)}</td>
          <td class="mono">${escapeHtml(c.type)}</td>
          <td style="color:${color}">${c.daysLeft} days</td>
        </tr>`;
      })
      .join("");
  }

  function escapeHtml(str) {
    return String(str ?? "").replace(/[&<>"']/g, (m) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
    }[m]));
  }
})();
