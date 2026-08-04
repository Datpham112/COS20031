/**
 * Frontend/assets/driver.js
 * ------------------------------------------------------------------
 * Populates driver.html by calling the API from backend driver.php
 * ------------------------------------------------------------------
 */

const API_URL = "../Backend/api/driver.php?action=profile";

// Global data store 
let driverData = {
    profile: null,
    certifications: [],
    vehicles: [],
    scores: [],
    events: []
};

let currentEvent = null;

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = (value === null || value === undefined || value === '') ? '—' : value;
    }
}

function createCell(text, className) {
    const cell = document.createElement("td");
    if (className) cell.className = className;
    cell.textContent = text ?? "";
    return cell;
}

function createPill(text, className) {
    const pill = document.createElement("span");
    pill.className = `status-pill ${className}`.trim();
    pill.textContent = text ?? "";
    return pill;
}

function createButton(label, handler, className = "btn-link") {
    const button = document.createElement("button");
    button.type = "button";
    button.className = className;
    button.textContent = label;
    button.addEventListener("click", handler);
    return button;
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

document.addEventListener('DOMContentLoaded', () => {
    loadDriverProfile();
    initTabs();
    initDrawer();
});

async function loadDriverProfile() {
    try {
        const res = await fetch(API_URL, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' },
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.error || "HTTP " + res.status);
        }

        // Save into global 
        driverData.profile = data.profile;
        driverData.certifications = data.certifications || [];
        driverData.vehicles = data.vehicles || [];
        driverData.scores = data.scores || [];
        driverData.events = data.events || [];

        // Run rendering steps
        renderProfile(driverData.profile);
        renderCertifications(driverData.certifications);
        renderVehicle(driverData.vehicles);
        renderSafetyScore(driverData.scores);
        renderScoreHistory(driverData.scores);
        renderSafetyEvents(driverData.events);

    } catch (err) {
        console.error('Error loading driver profile:', err);
        
        const certTable = document.getElementById('certificationTableBody');
        const scoreTable = document.getElementById('scoreHistoryTableBody');
        const eventTable = document.getElementById('safetyEventTableBody');

        if (certTable) certTable.innerHTML = '<tr><td colspan="3">Could not load certifications.</td></tr>';
        if (scoreTable) scoreTable.innerHTML = '<tr><td colspan="3">Could not load history.</td></tr>';
        if (eventTable) eventTable.innerHTML = '<tr><td colspan="5">Could not load safety events.</td></tr>';
    }
}

function renderProfile(profile) {
    if (!profile) return;

    setText('driverId', profile.Driver_ID);
    setText('driverName', profile.Full_Name);
    setText('driverDepot', profile.Location_Name);
    setText('driverStatus', profile.Employment_Status);
    setText('driverContact', profile.Contact_Information);
    setText('driverEmergency', profile.Emergency_Contact);
    setText('driverLicenceType', profile.License_Type);
    setText('driverLicenceExpiry', formatDate(profile.License_Expiry_Date));
}

function renderCertifications(certifications) {
    renderTableRows('certificationTableBody', certifications, 'No certifications on file.', cert => {
        const isExpired = cert.Expiry_Date && new Date(cert.Expiry_Date) < new Date();
        const pillClass = isExpired ? 'status-bad' : 'status-good';
        const pillText = isExpired ? 'Expired' : 'Valid';

        const row = document.createElement('tr');
        
        const statusCell = document.createElement('td');
        statusCell.appendChild(createPill(pillText, pillClass));

        row.append(
            createCell(cert.Certification_Name),
            createCell(formatDate(cert.Expiry_Date)),
            statusCell
        );
        return row;
    }, 3);
}

function renderVehicle(vehicles) {
    if (!vehicles || vehicles.length === 0) return;


    const current = vehicles.find(v => !v.End_Date) || vehicles[0];
    if (!current) return;

    setText('vehicleRegistration', current.Registration_Number);
    setText('vehicleModel', `${current.Manufacturer_and_Model} (${current.Vehicle_Category})`);
    setText('vehicleVIN', current.VIN);
    setText('assignmentDate', formatDate(current.Start_Date));
}

function renderSafetyScore(scores) {
    if (!scores || scores.length === 0) {
        setText('currentSafetyScore', '—');
        return;
    }
    setText('currentSafetyScore', scores[0].Score);
}

function renderScoreHistory(scores) {
    renderTableRows('scoreHistoryTableBody', scores, 'No score history available.', s => {
        const row = document.createElement('tr');
        row.append(
            createCell(monthName(s.Month)),
            createCell(s.Year),
            createCell(s.Score)
        );
        return row;
    }, 3);
}

function renderSafetyEvents(events) {
    renderTableRows('safetyEventTableBody', events, 'No safety events on record.', evt => {
        const row = document.createElement('tr');
        
        const actionCell = document.createElement('td');
        actionCell.appendChild(createButton('View', () => {
            openEventDrawer(evt.Event_ID);
        }));

        row.append(
            createCell(formatDate(evt.Timestamp)),
            createCell(evt.Registration_Number),
            createCell(evt.Event_Type),
            createCell(evt.Severity_Level),
            actionCell
        );
        return row;
    }, 5);
}

function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            tabPanels.forEach(p => p.classList.remove('active'));

            btn.classList.add('active');
            const targetId = btn.dataset.tab || btn.textContent.trim().toLowerCase().replace(/\s+/g, "-");
            const target = document.getElementById(targetId);
            if (target) target.classList.add('active');
        });
    });
}

function initDrawer() {
    const overlay = document.getElementById('overlay');
    const closeButtons = document.querySelectorAll('.drawer-close, #closeDrawer');

    closeButtons.forEach(btn => btn.addEventListener('click', closeEventDrawer));
    if (overlay) overlay.addEventListener('click', closeEventDrawer);
}

function openEventDrawer(eventId) {
    currentEvent = driverData.events.find(evt => evt.Event_ID === eventId);
    if (!currentEvent) return;

    setText('drawerDate', formatDate(currentEvent.Timestamp));
    setText('drawerVehicle', currentEvent.Registration_Number);
    setText('drawerVIN', currentEvent.VIN || '—');
    setText('drawerSeverity', currentEvent.Severity_Level);
    setText('drawerOdometer', currentEvent.Odometer_At_Event != null ? `${currentEvent.Odometer_At_Event} km` : '—');
    setText('drawerEvent', currentEvent.Event_Type);
    setText('drawerComments', currentEvent.Review_Comments || 'No review comments available.');

    document.getElementById('overlay')?.classList.add('open');
    document.getElementById('drawer')?.classList.add('open');
}

function closeEventDrawer() {
    document.getElementById('overlay')?.classList.remove('open');
    document.getElementById('drawer')?.classList.remove('open');
}

// Utility Formatters
function formatDate(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function monthName(monthNumber) {
    const names = ['', 'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    return names[monthNumber] || monthNumber;
}
