/*=========================
    GLOBAL SEARCH
=========================*/

const searchInput = document.getElementById("dashboardSearch");
const searchResults = document.getElementById("searchResults");

const dashboardData = [
  {
    type: "Vehicle",
    title: "29A12345",
    subtitle: "Delivery Van • Ha Noi Depot",
    url: "#"
  },
  {
    type: "Vehicle",
    title: "43E45678",
    subtitle: "Electric Van • Da Nang Depot",
    url: "#"
  },
  {
    type: "Vehicle",
    title: "51C78901",
    subtitle: "Refrigerated Truck • Ho Chi Minh City Depot",
    url: "#"
  },
  {
    type: "Driver",
    title: "Nguyen Van Hung",
    subtitle: "Ha Noi • Workshop HN-01",
    url: "#"
  },
  {
    type: "Driver",
    title: "Le Thi Mai",
    subtitle: "Da Nang • Workshop DN-01",
    url: "#"
  },
  {
    type: "Driver",
    title: "Pham Van Son",
    subtitle: "Ho Chi Minh City • Workshop HCMC-01",
    url: "#"
  },
  {
    type: "Job",
    title: "JOB-1001",
    subtitle: "Workshop HN-01",
    url: "#"
  },
  {
    type: "Job",
    title: "JOB-1002",
    subtitle: "Workshop HCMC-01",
    url: "#"
  },
  {
    type: "Job",
    title: "JOB-1003",
    subtitle: "Workshop CT-01",
    url: "#"
  }
];

if (searchInput && searchResults) {
  searchInput.addEventListener("keyup", function () {
    const keyword = this.value.trim().toLowerCase();
    searchResults.innerHTML = "";

    if (keyword === "") {
      searchResults.style.display = "none";
      return;
    }

    const result = dashboardData.filter((item) => {
      return (
        item.title.toLowerCase().includes(keyword) ||
        item.subtitle.toLowerCase().includes(keyword) ||
        item.type.toLowerCase().includes(keyword)
      );
    });

    if (result.length === 0) {
      searchResults.innerHTML = '<div class="no-result">No result found</div>';
      searchResults.style.display = "block";
      return;
    }

    result.forEach((item) => {
      const div = document.createElement("div");
      div.className = "search-item";
      div.innerHTML = `
        <div class="search-type">${item.type}</div>
        <div class="search-title">${item.title}</div>
        <div class="search-sub">${item.subtitle}</div>
      `;
      div.onclick = () => {
        alert(item.type + "\n" + item.title + "\n" + item.subtitle);
      };
      searchResults.appendChild(div);
    });

    searchResults.style.display = "block";
  });
}

document.addEventListener("click", (e) => {
  if (searchResults && !e.target.closest(".search-wrapper")) {
    searchResults.style.display = "none";
  }
});

/*=========================
    WORKSHOP DATA
=========================*/

const workshopData = {
  depots: [
    { id: 1, name: "Ha Noi" },
    { id: 2, name: "Da Nang" },
    { id: 3, name: "Ho Chi Minh City" },
    { id: 4, name: "Can Tho" }
  ],
  workshops: [
    { id: 1, depotId: 1, name: "Workshop HN-01" },
    { id: 2, depotId: 2, name: "Workshop DN-01" },
    { id: 3, depotId: 3, name: "Workshop HCMC-01" },
    { id: 4, depotId: 4, name: "Workshop CT-01" }
  ],
  vehicles: [
    { plate: "29A12345", vin: "VIN00000000000001", category: "Delivery Van", depot: "Ha Noi", status: "Active", odometer: "45,100.50 km", alerts: 1 },
    { plate: "29C56789", vin: "VIN00000000000002", category: "Heavy Transport Truck", depot: "Ha Noi", status: "Available", odometer: "120,560.30 km", alerts: 0 },
    { plate: "43E45678", vin: "VIN00000000000003", category: "Electric Van", depot: "Da Nang", status: "Under Maintenance", odometer: "12,300.00 km", alerts: 2 },
    { plate: "51C78901", vin: "VIN00000000000004", category: "Refrigerated Truck", depot: "Ho Chi Minh City", status: "Out of Service", odometer: "112,480.40 km", alerts: 1 },
    { plate: "65A11223", vin: "VIN00000000000005", category: "Service Vehicle", depot: "Can Tho", status: "Awaiting Inspection", odometer: "5,200.80 km", alerts: 0 }
  ],
  alerts: [
    { title: "Brake Wear", vehicle: "29A12345", severity: "Critical", depot: "Ha Noi", raised: "2026-02-01", action: "Scheduled Repair" },
    { title: "Tyre Pressure", vehicle: "29C56789", severity: "Medium", depot: "Ha Noi", raised: "2026-03-05", action: "Acknowledged" },
    { title: "Battery Degradation", vehicle: "43E45678", severity: "High", depot: "Da Nang", raised: "2026-01-20", action: "Emergency Repair" },
    { title: "Overheating Risk", vehicle: "51C78901", severity: "High", depot: "Ho Chi Minh City", raised: "2025-12-10", action: "Resolved" },
    { title: "Engine Fault", vehicle: "65A11223", severity: "Medium", depot: "Can Tho", raised: "2026-04-01", action: "Acknowledged" }
  ],
  jobs: [
    { job: "JOB-1001", vehicle: "29A12345", workshop: "Workshop HN-01", opened: "2026-02-01", closed: "2026-02-03", downtime: "8.50h", cost: "1,500,000 VND", status: "Closed" },
    { job: "JOB-1002", vehicle: "29C56789", workshop: "Workshop HN-01", opened: "2026-03-05", closed: null, downtime: "0.00h", cost: "0 VND", status: "Open" },
    { job: "JOB-1003", vehicle: "43E45678", workshop: "Workshop DN-01", opened: "2026-01-20", closed: "2026-01-25", downtime: "40.00h", cost: "8,500,000 VND", status: "Closed" },
    { job: "JOB-1004", vehicle: "51C78901", workshop: "Workshop HCMC-01", opened: "2025-12-10", closed: "2025-12-15", downtime: "30.00h", cost: "6,200,000 VND", status: "Closed" },
    { job: "JOB-1005", vehicle: "65A11223", workshop: "Workshop CT-01", opened: "2026-04-01", closed: null, downtime: "5.00h", cost: "500,000 VND", status: "Open" }
  ],
  mechanics: [
    { name: "Nguyen Van Hung", depot: "Ha Noi", workshop: "Workshop HN-01", week: "this-week", days: [
      { text: "8h (Inspection)", kind: "good" },
      { text: "10h (Overload)", kind: "danger" },
      { text: "4h (Brakes)", kind: "warn" },
      { text: "-- Free --", kind: "free" },
      { text: "8h (Tires)", kind: "good" },
      { text: "Off", kind: "off" },
      { text: "Off", kind: "off" }
    ] },
    { name: "Tran Van Duc", depot: "Ha Noi", workshop: "Workshop HN-01", week: "last-week", days: [
      { text: "8h (Engine)", kind: "good" },
      { text: "5h (AC)", kind: "warn" },
      { text: "-- Free --", kind: "free" },
      { text: "6h (Inspection)", kind: "good" },
      { text: "-- Free --", kind: "free" },
      { text: "Off", kind: "off" },
      { text: "Off", kind: "off" }
    ] },
    { name: "Le Thi Mai", depot: "Da Nang", workshop: "Workshop DN-01", week: "this-week", days: [
      { text: "4h (Battery)", kind: "warn" },
      { text: "-- Free --", kind: "free" },
      { text: "8h (Gearbox)", kind: "good" },
      { text: "8h (Gearbox)", kind: "good" },
      { text: "-- Free --", kind: "free" },
      { text: "Off", kind: "off" },
      { text: "Off", kind: "off" }
    ] },
    { name: "Pham Van Son", depot: "Ho Chi Minh City", workshop: "Workshop HCMC-01", week: "this-week", days: [
      { text: "8h (Wiring)", kind: "good" },
      { text: "8h (Lights)", kind: "good" },
      { text: "-- Free --", kind: "free" },
      { text: "12h (Engine Swap)", kind: "danger" },
      { text: "10h (Finish)", kind: "danger" },
      { text: "Off", kind: "off" },
      { text: "Off", kind: "off" }
    ] },
    { name: "Hoang Thi Lan", depot: "Ho Chi Minh City", workshop: "Workshop HCMC-01", week: "this-week", days: [
      { text: "-- Free --", kind: "free" },
      { text: "-- Free --", kind: "free" },
      { text: "4h (Suspension)", kind: "warn" },
      { text: "8h (Alignment)", kind: "good" },
      { text: "-- Free --", kind: "free" },
      { text: "Off", kind: "off" },
      { text: "Off", kind: "off" }
    ] },
    { name: "Vo Van Tam", depot: "Can Tho", workshop: "Workshop CT-01", week: "this-week", days: [
      { text: "8h (Design)", kind: "good" },
      { text: "-- Free --", kind: "free" },
      { text: "4h (Prototyping)", kind: "warn" },
      { text: "-- Free --", kind: "free" },
      { text: "8h (Testing)", kind: "good" },
      { text: "Off", kind: "off" },
      { text: "Off", kind: "off" }
    ] }
  ]
};

function getCellStyle(kind) {
  switch (kind) {
    case "danger":
      return "background: #ffebee; color: #c62828;";
    case "warn":
      return "background: #fff3e0; color: #ef6c00;";
    case "good":
      return "background: #e8f5e9; color: #2e7d32;";
    case "free":
      return "color: #bbb;";
    default:
      return "color: #aaa;";
  }
}

function getActiveWorkshopTab() {
  const activePanel = document.querySelector(".tab-panel.active");
  return activePanel ? activePanel.id.replace("ws-", "") : "vehicles";
}

function syncDepotFilters(selectedValue) {
  [
    document.getElementById("topDepotFilter"),
    document.getElementById("vehicleDepotFilter"),
    document.getElementById("alertDepotFilter"),
    document.getElementById("jobDepotFilter")
  ].forEach((select) => {
    if (select && select.value !== selectedValue) {
      select.value = selectedValue;
    }
  });
}

function getWorkshopFilterState() {
  const activeTab = getActiveWorkshopTab();
  const searchText = (document.getElementById("vehicleSearchInput")?.value || document.getElementById("globalWorkshopSearch")?.value || "").trim().toLowerCase();

  if (activeTab === "alerts") {
    return {
      tab: "alerts",
      severity: document.getElementById("alertSeverityFilter")?.value || "all",
      type: document.getElementById("alertTypeFilter")?.value || "all",
      depot: document.getElementById("alertDepotFilter")?.value || document.getElementById("topDepotFilter")?.value || "all",
      searchText
    };
  }

  if (activeTab === "jobs") {
    return {
      tab: "jobs",
      status: document.getElementById("jobStatusFilter")?.value || "all",
      depot: document.getElementById("jobDepotFilter")?.value || document.getElementById("topDepotFilter")?.value || "all",
      workshop: document.getElementById("jobWorkshopFilter")?.value || "all",
      searchText
    };
  }

  return {
    tab: "vehicles",
    depot: document.getElementById("topDepotFilter")?.value || document.getElementById("vehicleDepotFilter")?.value || "all",
    status: document.getElementById("vehicleStatusFilter")?.value || "all",
    category: document.getElementById("vehicleCategoryFilter")?.value || "all",
    searchText
  };
}

function renderWorkshopPage() {
  const activeTab = getActiveWorkshopTab();
  const filters = getWorkshopFilterState();

  const vehicleBody = document.getElementById("vehicleTableBody");
  if (vehicleBody) {
    const filteredVehicles = workshopData.vehicles.filter((vehicle) => {
      const matchDepot = filters.depot === "all" || vehicle.depot === filters.depot;
      const matchStatus = filters.status === "all" || vehicle.status === filters.status;
      const matchCategory = filters.category === "all" || vehicle.category === filters.category;
      const matchSearch = !filters.searchText || `${vehicle.plate} ${vehicle.vin} ${vehicle.category}`.toLowerCase().includes(filters.searchText);
      return matchDepot && matchStatus && matchCategory && matchSearch;
    });

    vehicleBody.innerHTML = filteredVehicles.map((vehicle) => {
      const statusClass = vehicle.status === "Active" ? "pill ok" : vehicle.status === "Under Maintenance" ? "pill warn" : "pill neutral";
      return `
        <tr>
          <td class="mono">${vehicle.plate}</td>
          <td>${vehicle.category}</td>
          <td>${vehicle.depot}</td>
          <td><span class="${statusClass}">${vehicle.status}</span></td>
          <td class="mono">${vehicle.odometer}</td>
          <td>${vehicle.alerts}</td>
          <td><button class="icon-btn" title="Open details">↗</button></td>
        </tr>
      `;
    }).join("");
  }

  const alertBody = document.getElementById("alertTableBody");
  if (alertBody) {
    const filteredAlerts = workshopData.alerts.filter((alert) => {
      const matchSeverity = filters.severity === "all" || alert.severity === filters.severity;
      const matchType = filters.type === "all" || alert.title.toLowerCase().includes(filters.type.toLowerCase());
      const matchDepot = filters.depot === "all" || alert.depot === filters.depot;
      const matchSearch = !filters.searchText || alert.title.toLowerCase().includes(filters.searchText) || alert.vehicle.toLowerCase().includes(filters.searchText);
      return matchSeverity && matchType && matchDepot && matchSearch;
    });

    alertBody.innerHTML = filteredAlerts.map((alert) => {
      const severityClass = alert.severity === "Critical" ? "pill crit" : alert.severity === "High" ? "pill warn" : "pill neutral";
      return `
        <tr>
          <td>${alert.title}</td>
          <td class="mono">${alert.vehicle}</td>
          <td><span class="${severityClass}">${alert.severity}</span></td>
          <td>${alert.depot}</td>
          <td class="mono">${alert.raised}</td>
          <td class="action-icons">
            <button class="icon-btn" title="Acknowledge">✓</button>
            <button class="icon-btn" title="Schedule">📅</button>
          </td>
        </tr>
      `;
    }).join("");
  }

  const jobBody = document.getElementById("jobTableBody");
  if (jobBody) {
    const filteredJobs = workshopData.jobs.filter((job) => {
      const depot = job.workshop === "Workshop HN-01" ? "Ha Noi" : job.workshop === "Workshop DN-01" ? "Da Nang" : job.workshop === "Workshop HCMC-01" ? "Ho Chi Minh City" : job.workshop === "Workshop CT-01" ? "Can Tho" : "";
      const matchStatus = filters.status === "all" || job.status === filters.status;
      const matchDepot = filters.depot === "all" || depot === filters.depot;
      const matchWorkshop = filters.workshop === "all" || job.workshop === filters.workshop;
      const matchSearch = !filters.searchText || `${job.job} ${job.vehicle} ${job.workshop}`.toLowerCase().includes(filters.searchText);
      return matchStatus && matchDepot && matchWorkshop && matchSearch;
    });

    jobBody.innerHTML = filteredJobs.map((job) => {
      const chipClass = job.status === "Open" ? "pill warn" : "pill neutral";
      return `
        <tr>
          <td class="mono">${job.job}</td>
          <td class="mono">${job.vehicle}</td>
          <td>${job.workshop}</td>
          <td class="mono">${job.opened}</td>
          <td>${job.downtime}</td>
          <td>${job.cost}</td>
          <td><span class="${chipClass}">${job.status}</span></td>
        </tr>
      `;
    }).join("");
  }
}

function wsTab(tabName, button) {
  const panels = document.querySelectorAll(".tab-panel");
  const buttons = document.querySelectorAll(".tab-btn");

  panels.forEach((panel) => panel.classList.remove("active"));
  buttons.forEach((btn) => btn.classList.remove("active"));

  const targetPanel = document.getElementById(`ws-${tabName}`);
  if (targetPanel) {
    targetPanel.classList.add("active");
  }
  if (button) {
    button.classList.add("active");
  }
  renderWorkshopPage();
}

function renderWorkloadPage() {
  const rowsContainer = document.getElementById("workloadRows");
  if (!rowsContainer) {
    return;
  }

  const header = document.createElement("div");
  header.style.cssText = "display: grid; grid-template-columns: 1.8fr repeat(7, 1fr); gap: 10px; align-items: center; padding: 12px 16px; border-bottom: 2px solid #eee; font-weight: 700; color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;";
  header.innerHTML = ["Mechanic", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"].map((label) => `<div style="text-align: center;">${label}</div>`).join("");
  rowsContainer.innerHTML = "";
  rowsContainer.appendChild(header);

  workshopData.mechanics.forEach((mechanic) => {
    const row = document.createElement("div");
    row.className = "mechanic-row";
    row.dataset.name = mechanic.name;
    row.dataset.depot = mechanic.depot;
    row.dataset.workshop = mechanic.workshop;
    row.dataset.week = mechanic.week;
    row.style.cssText = "display: grid; grid-template-columns: 1.8fr repeat(7, 1fr); gap: 10px; align-items: center; padding: 12px 16px; border-bottom: 1px solid #eee;";

    const nameCell = document.createElement("div");
    nameCell.style.cssText = "font-weight: 600; color: #333; font-size: 14px;";
    nameCell.textContent = `👨‍🔧 ${mechanic.name}`;
    row.appendChild(nameCell);

    mechanic.days.forEach((day) => {
      const cell = document.createElement("div");
      cell.style.cssText = `padding: 6px; border-radius: 4px; text-align: center; font-size: 12px; font-weight: 500; ${getCellStyle(day.kind)}`;
      cell.textContent = day.text;
      row.appendChild(cell);
    });

    rowsContainer.appendChild(row);
  });

  const statValues = document.querySelectorAll(".stat-box .kpi-val");
  if (statValues.length >= 4) {
    statValues[0].textContent = `${workshopData.mechanics.length}/6`;
    statValues[1].textContent = `${workshopData.jobs.filter((job) => job.status === "Open").length}`;
    statValues[2].textContent = "3";
    statValues[3].textContent = `${workshopData.mechanics.filter((mechanic) => mechanic.days.some((day) => day.kind === "danger")).length}`;
  }
}

function filterWorkload() {
  const searchInputEl = document.getElementById("searchInput");
  const depotFilter = document.getElementById("depotFilter");
  const workshopFilter = document.getElementById("workshopFilter");
  const weekFilter = document.getElementById("weekFilter");

  if (!searchInputEl || !depotFilter || !workshopFilter || !weekFilter) {
    return;
  }

  const searchText = searchInputEl.value.toLowerCase();
  const selectedDepot = depotFilter.value;
  const selectedWorkshop = workshopFilter.value;
  const selectedWeek = weekFilter.value;

  document.querySelectorAll(".mechanic-row").forEach((row) => {
    const mechanicName = row.dataset.name.toLowerCase();
    const matchSearch = mechanicName.includes(searchText);
    const matchDepot = selectedDepot === "all" || row.dataset.depot === selectedDepot;
    const matchWorkshop = selectedWorkshop === "all" || row.dataset.workshop === selectedWorkshop;
    const matchWeek = selectedWeek === "all" || row.dataset.week === selectedWeek;

    row.style.display = matchSearch && matchDepot && matchWorkshop && matchWeek ? "grid" : "none";
  });
}

if (document.getElementById("workloadRows")) {
  renderWorkloadPage();
  const searchInputEl = document.getElementById("searchInput");
  const depotFilter = document.getElementById("depotFilter");
  const workshopFilter = document.getElementById("workshopFilter");
  const weekFilter = document.getElementById("weekFilter");

  searchInputEl?.addEventListener("input", filterWorkload);
  depotFilter?.addEventListener("change", filterWorkload);
  workshopFilter?.addEventListener("change", filterWorkload);
  weekFilter?.addEventListener("change", filterWorkload);
  filterWorkload();
}

const workshopFilterControls = [
  document.getElementById("topDepotFilter"),
  document.getElementById("vehicleDepotFilter"),
  document.getElementById("vehicleStatusFilter"),
  document.getElementById("vehicleCategoryFilter"),
  document.getElementById("vehicleSearchInput"),
  document.getElementById("globalWorkshopSearch"),
  document.getElementById("alertSeverityFilter"),
  document.getElementById("alertTypeFilter"),
  document.getElementById("alertDepotFilter"),
  document.getElementById("jobStatusFilter"),
  document.getElementById("jobDepotFilter"),
  document.getElementById("jobWorkshopFilter")
];

workshopFilterControls.forEach((control) => {
  if (!control) return;
  const eventName = control.tagName === "INPUT" ? "input" : "change";
  control.addEventListener(eventName, () => {
    if (control.id && control.id.includes("Depot")) {
      syncDepotFilters(control.value);
    }
    renderWorkshopPage();
  });
});

syncDepotFilters("all");
renderWorkshopPage();