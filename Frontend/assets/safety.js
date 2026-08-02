/**
 * safety.js
 * Takes PHP backend and connect to safety.html
 */

const API_URL = "../Backend/api/safety.php";

let safetyData = {
    drivers: [],
    incidents: [],
    analytics: {},
    coaching: {}
};

let currentDriver = null;
let currentIncident = null;


function setText(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = value ?? "";
    }
}

function renderTableRows(id, items, emptyText, buildRow, colspan = 1) {
    const tbody = document.getElementById(id);
    if (!tbody) return;

    tbody.replaceChildren();

    if (!items?.length) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = colspan;
        cell.textContent = emptyText;
        row.appendChild(cell);
        tbody.appendChild(row);
        return;
    }

    items.forEach(item => tbody.appendChild(buildRow(item)));
}

function createCell(text, className) {
    const cell = document.createElement("td");
    if (className) cell.className = className;
    cell.textContent = text ?? "";
    return cell;
}

function createPill(text, className) {
    const pill = document.createElement("span");
    pill.className = `pill ${className}`.trim();
    pill.textContent = text ?? "";
    return pill;
}

function createButton(label, handler, className = "link-btn") {
    const button = document.createElement("button");
    button.type = "button";
    button.className = className;
    button.textContent = label;
    button.addEventListener("click", handler);
    return button;
}

document.addEventListener("DOMContentLoaded", () => {
    loadSafetyData();
    setupSafetyTabs();
    setupDriverFilters();
    setupIncidentFilters();
    setupDrawerButtons();
});


async function loadSafetyData() {
    try {
        const response = await fetch(API_URL);

        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.detail || data.error);
        }

        safetyData = data;

        renderDrivers(safetyData.drivers);
        renderIncidents(safetyData.incidents);
        renderAnalytics(safetyData.analytics);
        renderCoaching(safetyData.coaching);

    } catch (error) {
        console.error("Failed to load safety data:", error);

        const driverTable = document.getElementById("driverTableBody");
        const incidentTable = document.getElementById("incidentTableBody");

        if (driverTable) {
            driverTable.innerHTML = '<tr><td colspan="7">Could not load driver data.</td></tr>';
        }

        if (incidentTable) {
            incidentTable.innerHTML = '<tr><td colspan="6">Could not load incident data.</td></tr>';
        }
    }
}


function setupSafetyTabs() {
  
    document.querySelectorAll(".tab-row .tab-btn").forEach(btn => {
        btn.addEventListener("click", () => safetyTab(btn.dataset.tab || btn.textContent.trim().toLowerCase().replace(/\s+/g, "-"), btn));
    });
}

function safetyTab(tabName, btn) {
    const name = String(tabName || "").trim().toLowerCase();
    const panel = document.getElementById("safety-" + name);
    const targetBtn = btn || document.querySelector(`.tab-btn[data-tab="${name}"]`) || document.querySelector(".tab-btn");

    document.querySelectorAll(".tab-panel").forEach(p => p.classList.toggle("active", p === panel));
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.toggle("active", b === targetBtn));
}


function setupDriverFilters() {
    // Keep the driver filters in sync with the table view.
    document.querySelectorAll("#safety-drivers .filter-select").forEach(f => f.addEventListener("change", filterDrivers));
    document.querySelector("#safety-drivers .filter-search")?.addEventListener("input", filterDrivers);
}


function filterDrivers() {

    const filters = document.querySelectorAll("#safety-drivers .filter-select");
    const depotFilter = filters[0].value;
    const statusFilter = filters[1].value;
    const certificationFilter = filters[2].value;
    const scoreFilter = filters[3].value;
    const searchText = document.querySelector("#safety-drivers .filter-search")?.value.toLowerCase().trim() || "";

    const filteredDrivers = safetyData.drivers.filter(driver => {
        if (depotFilter !== "All Depots" && driver.depot !== depotFilter) return false;
        if (statusFilter !== "All Status" && driver.employmentStatus !== statusFilter) return false;
        if (certificationFilter !== "Certification" && driver.certificationStatus !== certificationFilter) return false;

        const score = driver.safetyScore;
        if (scoreFilter === "90+" && (score === null || score < 90)) return false;
        if (scoreFilter === "75-89" && (score === null || score < 75 || score >= 90)) return false;
        if (scoreFilter === "Below 75" && (score === null || score >= 75)) return false;

        if (searchText) {
            const text = `${driver.name} ${driver.driverId} ${driver.depot}`.toLowerCase();
            if (!text.includes(searchText)) return false;
        }

        return true;
    });

    renderDrivers(filteredDrivers);
}
function renderDrivers(drivers) {
  
    renderTableRows("driverTableBody", drivers, "No drivers found.", driver => {
        const score = driver.safetyScore;
        const scoreClass = score === null ? "" : score <= 50 ? "score-bad" : score <= 75 ? "score-warn" : "";
        const statusClass = driver.employmentStatus === "Suspended" ? "crit" : driver.employmentStatus === "On Leave" ? "warn" : "ok";
        const certClass = driver.certificationStatus === "Expired" ? "crit" : driver.certificationStatus === "Expiring" ? "warn" : "ok";

        const row = document.createElement("tr");
        row.className = "clickable-row";
        row.addEventListener("click", () => openDriverDrawer(driver.driverId));

        const nameCell = document.createElement("td");
        const driverCell = document.createElement("div");
        driverCell.className = "driver-cell";
        const avatar = document.createElement("div");
        avatar.className = "driver-avatar";
        avatar.textContent = getInitials(driver.name);
        const nameText = document.createElement("span");
        nameText.textContent = driver.name;
        driverCell.append(avatar, nameText);
        nameCell.appendChild(driverCell);

        const statusCell = document.createElement("td");
        statusCell.appendChild(createPill(driver.employmentStatus, statusClass));

        const certCell = document.createElement("td");
        certCell.appendChild(createPill(driver.certificationStatus, certClass));

        const scoreCell = document.createElement("td");
        scoreCell.className = scoreClass;
        scoreCell.textContent = score === null ? "N/A" : score;

        const actionCell = document.createElement("td");
        actionCell.appendChild(createButton("View →", event => {
            event.stopPropagation();
            openDriverDrawer(driver.driverId);
        }));

        row.append(nameCell, createCell(driver.depot), statusCell, certCell, scoreCell, createCell(driver.incidentCount), actionCell);
        return row;
    }, 7);
}

function setupIncidentFilters() {
    // The incident view uses the same pattern, just with a different set of filters.
    document.querySelectorAll("#safety-incidents .filter-select").forEach(f => f.addEventListener("change", filterIncidents));
    document.querySelector("#safety-incidents .filter-search")?.addEventListener("input", filterIncidents);
}

function filterIncidents() {

    const filters = document.querySelectorAll("#safety-incidents .filter-select");
    const driverFilter = filters[0].value;
    const vehicleFilter = filters[1].value;
    const depotFilter = filters[2].value;
    const severityFilter = filters[3].value;
    const searchText = document.querySelector("#safety-incidents .filter-search")?.value.toLowerCase().trim() || "";

    const filteredIncidents = safetyData.incidents.filter(incident => {
        if (driverFilter !== "All Drivers" && incident.driverId !== driverFilter) return false;
        if (vehicleFilter !== "All Vehicles" && incident.vin !== vehicleFilter) return false;
        if (depotFilter !== "All Depots" && incident.depot !== depotFilter) return false;
        if (severityFilter !== "Severity" && incident.severity !== severityFilter) return false;

        if (searchText) {
            const text = `${incident.driverName} ${incident.eventType} ${incident.registrationNumber} ${incident.vin}`.toLowerCase();
            if (!text.includes(searchText)) return false;
        }

        return true;
    });

    renderIncidents(filteredIncidents);
}

function renderIncidents(incidents) {
    // Build the incident rows from the filtered data.
    renderTableRows("incidentTableBody", incidents, "No incidents found.", incident => {
        const severityClass = incident.severity === "High" || incident.severity === "Critical" ? "crit" : "warn";
        const row = document.createElement("tr");
        row.className = "clickable-row";
        row.addEventListener("click", () => openIncidentDrawer(incident.eventId));

        const severityCell = document.createElement("td");
        severityCell.appendChild(createPill(incident.severity, severityClass));

        const actionCell = document.createElement("td");
        actionCell.appendChild(createButton("Review →", event => {
            event.stopPropagation();
            openIncidentDrawer(incident.eventId);
        }));

        row.append(
            createCell(formatDate(incident.timestamp)),
            createCell(incident.driverName),
            createCell(incident.registrationNumber),
            createCell(incident.eventType),
            severityCell,
            actionCell
        );
        return row;
    }, 6);
}

function openDriverDrawer(driverId) {

    const driver = safetyData.drivers.find(driver => driver.driverId === driverId);

    if (!driver) return;

    currentDriver = driver;
    currentIncident = null;

    setText("drawerTitle", driver.name);
    setText("drawerSub", "Driver Safety Record");

    document.getElementById("driverDrawerContent").style.display = "block";
    document.getElementById("incidentDrawerContent").style.display = "none";

    setText("driverId", driver.driverId);
    setText("driverName", driver.name);
    setText("driverContact", driver.contact);
    setText("driverEmergencyContact", driver.emergencyContact);
    setText("driverLicenseType", driver.licenseType);
    setText("driverLicenseExpiry", driver.licenseExpiryDate);
    setText("driverDepot", driver.depot);
    setText("driverStatus", driver.employmentStatus);

    renderCertificationHistory(driver);
    renderScoreTrend(driver);
    renderIncidentHistory(driver);

    document.getElementById("overlay").classList.add("open");
    document.getElementById("drawer").classList.add("open");
}

function openIncidentDrawer(eventId) {

    const incident = safetyData.incidents.find(item => item.eventId === eventId);

    if (!incident) return;

    currentIncident = incident;
    currentDriver = null;

    setText("drawerTitle", "Safety Event Review");
    setText("drawerSub", "Review incident");

    document.getElementById("driverDrawerContent").style.display = "none";
    document.getElementById("incidentDrawerContent").style.display = "block";

    setText("incidentDate", formatDate(incident.timestamp));
    setText("incidentDriver", incident.driverName);
    setText("incidentVehicle", incident.registrationNumber);
    setText("incidentVehicleModel", incident.vehicleModel);
    setText("incidentDepot", incident.depot);
    setText("incidentEvent", incident.eventType);
    setText("incidentOdometer", incident.odometer);

    const severity = document.getElementById("incidentSeverity");
    severity.textContent = incident.severity;
    severity.className = "pill";
    severity.classList.add(incident.severity === "High" || incident.severity === "Critical" ? "crit" : "warn");

    document.getElementById("reviewCommentsInput").value = incident.reviewComments || "";
    document.getElementById("coachingRecommendationInput").value = "";

    document.getElementById("overlay").classList.add("open");
    document.getElementById("drawer").classList.add("open");
}

function renderCertificationHistory(driver) {

    const container = document.getElementById("driverCertificationHistory");
    container.replaceChildren();

    if (!driver.certifications?.length) {
        const empty = document.createElement("p");
        empty.textContent = "No certification records found.";
        container.appendChild(empty);
        return;
    }

    driver.certifications.forEach(cert => {
        const row = document.createElement("div");
        row.className = "drawer-list-row";

        const name = document.createElement("span");
        name.textContent = cert.name;

        const status = createPill(cert.validity, cert.validity === "Expired" ? "crit" : cert.validity === "Expiring" ? "warn" : "ok");

        const expiry = document.createElement("small");
        expiry.textContent = `Expiry: ${cert.expiryDate}`;

        row.append(name, status, expiry);
        container.appendChild(row);
    });
}

function renderScoreTrend(driver) {

    const container = document.getElementById("driverScoreTrend");
    container.replaceChildren();

    if (driver.safetyScore === null) {
        const empty = document.createElement("p");
        empty.textContent = "No safety score available.";
        container.appendChild(empty);
        return;
    }

    const current = document.createElement("p");
    const strong = document.createElement("strong");
    strong.textContent = driver.safetyScore;
    current.append(document.createTextNode("Current Score: "), strong);
    container.appendChild(current);

    if (driver.scoreTrend?.length) {
        const table = document.createElement("table");
        table.className = "mini-table";
        const head = document.createElement("thead");
        head.innerHTML = "<tr><th>Month</th><th>Year</th><th>Score</th></tr>";
        const body = document.createElement("tbody");

        driver.scoreTrend.slice(-6).forEach(item => {
            const row = document.createElement("tr");
            row.append(createCell(item.month), createCell(item.year), createCell(item.score));
            body.appendChild(row);
        });

        table.append(head, body);
        container.appendChild(table);
    }
}

function renderIncidentHistory(driver) {
    const container = document.getElementById("driverIncidentHistory");
    container.replaceChildren();

    if (!driver.incidentHistory?.length) {
        const empty = document.createElement("p");
        empty.textContent = "No incident history.";
        container.appendChild(empty);
        return;
    }

    const list = document.createElement("ul");

    driver.incidentHistory.slice(0, 10).forEach(incident => {
        const item = document.createElement("li");
        const strong = document.createElement("strong");
        strong.textContent = incident.eventType;
        item.append(strong, document.createTextNode(` - ${incident.severity} - ${formatDate(incident.timestamp)}`));
        list.appendChild(item);
    });

    container.appendChild(list);
}

async function saveReviewComments() {
    // Save the review notes back to API.
    if (!currentIncident) {
        alert("No safety event selected.");
        return;
    }

    const comments = document.getElementById("reviewCommentsInput").value.trim();

    try {
        const response = await fetch(API_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "save_review",
                eventId: currentIncident.eventId,
                reviewComments: comments
            })
        });

        const result = await response.json();

        if (!response.ok || result.error) {
            throw new Error(result.detail || result.error);
        }

        currentIncident.reviewComments = comments;

        const incident = safetyData.incidents.find(item => item.eventId === currentIncident.eventId);
        if (incident) incident.reviewComments = comments;

        alert("Review comments saved successfully.");
    } catch (error) {
        console.error(error);
        alert("Could not save review comments: " + error.message);
    }
}

function renderAnalytics(analytics) {
    if (!analytics) return;

    setText("analyticsAverageScore", Number(analytics.averageSafetyScore || 0).toFixed(1));
    setText("analyticsCriticalEvents", analytics.criticalEvents || 0);
    setText("analyticsHighRiskDrivers", analytics.highRiskDrivers || 0);
    setText("analyticsRepeatOffenders", analytics.repeatOffenders || 0);

    renderTableRows("depotComparisonBody", analytics.depotComparison || [], "No depot data.", depot => {
        const row = document.createElement("tr");
        row.append(createCell(depot.depot), createCell(depot.averageScore), createCell(depot.criticalEvents));
        return row;
    }, 3);

    renderTableRows("repeatOffenderBody", analytics.repeatOffenderList || [], "No repeat offenders.", offender => {
        const row = document.createElement("tr");
        row.append(createCell(offender.name), createCell(offender.score ?? "N/A"), createCell(offender.events));
        return row;
    }, 3);
}

function renderCoaching(coaching) {
    // Show who needs coaching or if driver is blocked
    if (!coaching) return;

    renderTableRows("trainingRequiredBody", coaching.trainingRequired || [], "No drivers require training.", driver => {
        const row = document.createElement("tr");
        row.append(createCell(driver.name), createCell(driver.score), createCell(driver.trainingStatus));
        return row;
    }, 3);

    renderTableRows("blockedDriversBody", coaching.blockedDrivers || [], "No blocked drivers.", driver => {
        const row = document.createElement("tr");
        const reasonCell = createCell(driver.reason);
        const blockedCell = document.createElement("td");
        blockedCell.appendChild(createPill("Blocked", "crit"));
        row.append(createCell(driver.name), reasonCell, blockedCell);
        return row;
    }, 3);
}

function setupDrawerButtons() {

    document.querySelector(".drawer-close")?.addEventListener("click", closeDrawer);
    document.querySelector(".drawer-actions .btn-secondary")?.addEventListener("click", closeDrawer);
    document.getElementById("overlay")?.addEventListener("click", closeDrawer);
    document.querySelector(".drawer-body .btn-primary")?.addEventListener("click", saveReviewComments);
}

function closeDrawer() {
    // Close the drawer and clear the current selection.
    document.getElementById("overlay").classList.remove("open");
    document.getElementById("drawer").classList.remove("open");

    currentDriver = null;
    currentIncident = null;
}

function getInitials(name) {

    return name
        .split(" ")
        .filter(Boolean)
        .slice(-2)
        .map(word => word[0])
        .join("")
        .toUpperCase();
}

function formatDate(dateString) {
    if (!dateString) return "";

    const date = new Date(dateString.replace(" ", "T"));
    if (isNaN(date)) return dateString;

    return date.toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
} 
