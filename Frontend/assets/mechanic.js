
const API_URL = "../Backend/api/mechanic.php";

let mechanicData = {
    mechanic: null,
    assignedWork: [],
    certifications: []
};

document.addEventListener("DOMContentLoaded", () => {
    loadMechanicData();
    setupJobFilters();
    initJobDrawer();
});


async function loadMechanicData() {
    try {
        const response = await fetch(`${API_URL}?me=1`);

        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.detail || data.error);
        }

        const jobs = normalizeAssignedWork(data);

        mechanicData = {
            mechanic: data.mechanic || null,
            assignedWork: jobs,
            certifications: Array.isArray(data.certifications) ? data.certifications : [],
            certHistory: Array.isArray(data.cert_history) ? data.cert_history : []
        };

        renderMechanicProfile(mechanicData.mechanic);
        renderCertifications(mechanicData.certifications);
        renderCertificationHistory(mechanicData.certHistory);
        renderJobs(mechanicData.assignedWork);

    } catch (error) {
        console.error("Failed to load mechanic data:", error);

        const certTable = document.getElementById("certificationTableBody");
        const jobTable = document.getElementById("jobTableBody");

        if (certTable) {
            certTable.innerHTML = '<tr><td colspan="4">Could not load certification data.</td></tr>';
        }

        if (jobTable) {
            jobTable.innerHTML = '<tr><td colspan="6">Could not load job data.</td></tr>';
        }
    }
}


function normalizeAssignedWork(payload) {
    const rows = Array.isArray(payload)
        ? payload
        : (Array.isArray(payload?.assignedWork)
            ? payload.assignedWork
            : (Array.isArray(payload?.assigned_work)
                ? payload.assigned_work
                : (Array.isArray(payload?.jobs) ? payload.jobs : [])));

    const jobsById = new Map();

    rows.forEach((row) => {
        const activityId = row.Activity_ID ?? row.activity_id ?? null;
        const jobId = row.Job_ID ?? row.job_id ?? row.JobId ?? null;
        const key = activityId ?? jobId ?? `row-${Math.random()}`;

        if (!jobsById.has(key)) {
            jobsById.set(key, {
                Job_ID: jobId ?? "",
                VIN: pickValue(row.VIN, row.vin, row.Vehicle_ID, row.vehicle_id),
                Workshop_ID: pickValue(row.Workshop_ID, row.workshop_id),
                Date_Opened: pickValue(row.Date_Opened, row.date_opened),
                Date_Closed: pickValue(row.Date_Closed, row.date_closed),
                Downtime_Hours: pickValue(row.Downtime_Hours, row.downtime_hours),
                Total_Cost: pickValue(row.Total_Cost, row.total_cost),
                Priority: pickValue(row.Priority, row.priority),
                Activity_ID: pickValue(row.Activity_ID, row.activity_id),
                Activity_Type: pickValue(row.Activity_Type, row.activity_type, row.Activity),
                Diagnostic_Result: pickValue(row.Diagnostic_Result, row.diagnostic_result),
                Repeat_Fault_Indicator: pickValue(row.Repeat_Fault_Indicator, row.repeat_fault_indicator),
                Warranty_Indicator: pickValue(row.Warranty_Indicator, row.warranty_indicator),
                Labour_Hours: pickValue(row.Labour_Hours, row.labour_hours, row.labourHours)
            });
        }

        const job = jobsById.get(key);
        job.Activity_Type = pickValue(job.Activity_Type, row.Activity_Type, row.activity_type, row.Activity);
        job.Diagnostic_Result = pickValue(job.Diagnostic_Result, row.Diagnostic_Result, row.diagnostic_result);
        job.Repeat_Fault_Indicator = pickValue(job.Repeat_Fault_Indicator, row.Repeat_Fault_Indicator, row.repeat_fault_indicator);
        job.Warranty_Indicator = pickValue(job.Warranty_Indicator, row.Warranty_Indicator, row.warranty_indicator);
        job.Labour_Hours = pickValue(job.Labour_Hours, row.Labour_Hours, row.labour_hours, row.labourHours);
        job.Activity_ID = pickValue(job.Activity_ID, row.Activity_ID, row.activity_id);
        job.VIN = pickValue(job.VIN, row.VIN, row.vin, row.Vehicle_ID, row.vehicle_id);
        job.Workshop_ID = pickValue(job.Workshop_ID, row.Workshop_ID, row.workshop_id);
        job.Date_Opened = pickValue(job.Date_Opened, row.Date_Opened, row.date_opened);
        job.Date_Closed = pickValue(job.Date_Closed, row.Date_Closed, row.date_closed);
        job.Downtime_Hours = pickValue(job.Downtime_Hours, row.Downtime_Hours, row.downtime_hours);
        job.Total_Cost = pickValue(job.Total_Cost, row.Total_Cost, row.total_cost);
        job.Priority = pickValue(job.Priority, row.Priority, row.priority);
    });

    return Array.from(jobsById.values()).sort((a, b) => {
        const left = a.Date_Opened || "";
        const right = b.Date_Opened || "";
        return right.localeCompare(left);
    });
}

function pickValue(...values) {
    for (const value of values) {
        if (value !== undefined && value !== null && value !== "") {
            return value;
        }
    }
    return undefined;
}

function renderMechanicProfile(mechanic) {
    if (!mechanic) return;

    const statusText = mechanic.Employment_Status || mechanic.Status || mechanic.status || "Active";
    const depotId = mechanic.Depot_ID ?? mechanic.depot_id;
    const depotName = mechanic.Depot_Name || mechanic.Depot || mechanic.depot_name;
    const depotDisplay = depotId !== undefined && depotId !== null && depotId !== ""
        ? `${depotName ? `${depotName}` : `#${depotId}`}`
        : (depotName || "—");
    const contactInfo = mechanic.Contact_Info || mechanic.Contact || mechanic.contact || "—";

    setText("mechanicId", mechanic.Mechanic_ID);
    setText("mechanicName", mechanic.Full_Name);
    setText("mechanicWorkshop", `Workshop #${mechanic.Workshop_ID}`);
    setText("mechanicDepot", depotDisplay);
    setText("mechanicContact", contactInfo);

    const statusPill = document.getElementById("mechanicStatus");
    if (statusPill) {
        const normalizedStatus = `${statusText}`.toLowerCase();
        statusPill.textContent = statusText;
        statusPill.className = `pill ${normalizedStatus === "active" ? "ok" : normalizedStatus === "on leave" || normalizedStatus === "inactive" ? "warn" : "ok"}`;
    }
}


function renderCertifications(certifications) {
    renderTableRows("certificationTableBody", certifications, "No certifications on record.", cert => {
        const validity = certValidity(cert.Expiry_Date);
        const validityClass = validity === "Expired" ? "crit" : validity === "Expiring" ? "warn" : "ok";

        const statusCell = document.createElement("td");
        statusCell.appendChild(createPill(validity, validityClass));

        const row = document.createElement("tr");
        row.append(
            createCell(cert.Certification_Name),
            createCell(cert.Issue_Date),
            createCell(cert.Expiry_Date),
            statusCell
        );
        return row;
    }, 4);
}

function renderCertificationHistory(history) {
    // mechanic.php's ?me=1 branch doesn't query Mechanic_Cert_History
    // yet, so this always renders empty until the backend adds it.
    renderTableRows("historyTableBody", history, "No certification history available.", entry => {
        const row = document.createElement("tr");
        row.append(
            createCell(entry.Certificate_Name),
            createCell(entry.Issue_Date),
            createCell(entry.Expiry_Date)
        );
        return row;
    }, 3);
}


function setupJobFilters() {
    const statusFilter = document.getElementById("jobStatusFilter");
    const searchInput = document.getElementById("jobSearch");

    statusFilter?.addEventListener("change", filterJobs);
    searchInput?.addEventListener("input", filterJobs);
    searchInput?.addEventListener("keyup", filterJobs);
}

function filterJobs() {
    const statusFilter = document.getElementById("jobStatusFilter")?.value || "";
    const searchText = document.getElementById("jobSearch")?.value.toLowerCase().trim() || "";
    const allJobs = Array.isArray(mechanicData.assignedWork) ? mechanicData.assignedWork : [];

    if (!statusFilter && !searchText) {
        renderJobs(allJobs);
        return;
    }

    const filteredJobs = allJobs.filter(job => {
        const status = jobStatus(job);

        if (statusFilter) {
            if (statusFilter === "In Progress") {
                if (status !== "In Progress") return false;
            } else if (statusFilter === "Completed") {
                if (status !== "Completed") return false;
            }
        }

        if (searchText) {
            const searchableText = [
                job.Job_ID,
                job.VIN,
                job.Activity_Type,
                job.Labour_Hours,
                job.Priority,
                status,
                job.Date_Opened,
                job.Date_Closed,
                job.Diagnostic_Result,
                job.Repeat_Fault_Indicator,
                job.Warranty_Indicator
            ].filter(Boolean).join(" ").toLowerCase();

            if (!searchableText.includes(searchText)) return false;
        }

        return true;
    });

    renderJobs(filteredJobs);
}

function renderJobs(jobs) {
    renderTableRows("jobTableBody", jobs, "No assigned work found.", job => {
        const status = jobStatus(job);
        const statusClass = status === "Completed" ? "ok" : "warn";

        const statusCell = document.createElement("td");
        statusCell.appendChild(createPill(status, statusClass));

        const actionCell = document.createElement("td");
        actionCell.appendChild(createButton("View →", event => {
            event.stopPropagation();
            showJobDetails(job);
        }));

        const row = document.createElement("tr");
        row.className = "clickable-row";
        row.addEventListener("click", () => showJobDetails(job));

        row.append(
            createCell(job.Job_ID),
            createCell(job.VIN),
            createCell(job.Activity_Type),
            createCell(formatMetric(job.Labour_Hours)),
            createCell(formatMetric(job.Downtime_Hours)),
            createCell(formatCurrency(job.Total_Cost)),
            statusCell,
            actionCell
        );
        return row;
    }, 8);
}

function initJobDrawer() {
    document.getElementById("closeJobDrawer")?.addEventListener("click", closeJobDrawer);
    document.getElementById("closeJobDrawerBtn")?.addEventListener("click", closeJobDrawer);
    document.getElementById("overlay")?.addEventListener("click", closeJobDrawer);

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeJobDrawer();
        }
    });
}

function showJobDetails(job) {
    const status = jobStatus(job);
    const drawerTitle = document.getElementById("drawerTitle");
    const drawerSub = document.getElementById("drawerSub");
    const drawerBody = document.getElementById("drawerBody");

    if (drawerTitle) drawerTitle.textContent = `Job #${job.Job_ID || "—"}`;
    if (drawerSub) drawerSub.textContent = job.Activity_Type || "Maintenance Activity";

    if (drawerBody) {
        drawerBody.innerHTML = `
            <div class="drawer-sec">
                <div class="drawer-sec-title">Job Information</div>
                <div class="kv-grid">
                    <div>
                        <div class="kv-label">Vehicle</div>
                        <div class="kv-val">${job.VIN || "—"}</div>
                    </div>
                    <div>
                        <div class="kv-label">Status</div>
                        <div class="kv-val">${status}</div>
                    </div>
                    <div>
                        <div class="kv-label">Labour Hours</div>
                        <div class="kv-val">${job.Labour_Hours ?? "—"}</div>
                    </div>
                    <div>
                        <div class="kv-label">Workshop</div>
                        <div class="kv-val">#${job.Workshop_ID ?? "—"}</div>
                    </div>
                    <div>
                        <div class="kv-label">Opened</div>
                        <div class="kv-val">${job.Date_Opened || "—"}</div>
                    </div>
                    <div>
                        <div class="kv-label">Closed</div>
                        <div class="kv-val">${job.Date_Closed || "Still open"}</div>
                    </div>
                </div>
            </div>

            <div class="drawer-sec">
                <div class="drawer-sec-title">Maintenance Activity Details</div>
                <p><strong>Activity Type:</strong> ${job.Activity_Type || "—"}</p>
                <p><strong>Diagnostic Result:</strong> ${job.Diagnostic_Result || "None recorded"}</p>
                <p><strong>Repeat Fault:</strong> ${job.Repeat_Fault_Indicator ? "Yes" : "No"}</p>
                <p><strong>Warranty:</strong> ${job.Warranty_Indicator ? "Yes" : "No"}</p>
                <p><strong>Downtime:</strong> ${job.Downtime_Hours ?? "N/A"} hrs</p>
                <p><strong>Total Cost:</strong> ${job.Total_Cost ?? "N/A"}</p>
            </div>
        `;
    }

    document.getElementById("overlay")?.classList.add("open");
    document.getElementById("drawer")?.classList.add("open");
}

function closeJobDrawer() {
    document.getElementById("overlay")?.classList.remove("open");
    document.getElementById("drawer")?.classList.remove("open");
}

function jobStatus(job) {
    if (job.Date_Closed) return "Completed";
    return "In Progress";
}

function certValidity(expiryDate) {
    if (!expiryDate) return "Valid";

    const expiry = new Date(expiryDate);
    if (isNaN(expiry)) return "Valid";

    const now = new Date();
    const daysLeft = (expiry - now) / (1000 * 60 * 60 * 24);

    if (daysLeft < 0) return "Expired";
    if (daysLeft <= 30) return "Expiring";
    return "Valid";
}


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

function formatMetric(value) {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    const numericValue = Number(value);
    if (!Number.isNaN(numericValue)) {
        return numericValue.toLocaleString("en-US", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    return value;
}

function formatCurrency(value) {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    const numericValue = Number(value);
    if (!Number.isNaN(numericValue)) {
        return `₫${numericValue.toLocaleString("en-US", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        })}`;
    }

    return value;
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
