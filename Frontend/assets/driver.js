/**
 * Frontend/assets/driver.js
 * ------------------------------------------------------------------
 * Powers the Driver's own portal (driver.html). Fetches everything
 * from Backend/api/driver.php?action=profile in one call and fills
 * in every element referenced by ID in driver.html.
 *
 * This file was referenced by driver.html but never existed - that's
 * why the page showed no data at all (the <script src="assets/driver.js">
 * tag 404'd silently, so nothing ever ran to fetch or render anything).
 * ------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
  setupTabSwitching();
  setupDrawer();
  loadDriverProfile();
});

/* ============ TABS ============ */

function setupTabSwitching() {
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });
}

/* ============ DRAWER (safety event details) ============ */

function setupDrawer() {
  const overlay = document.getElementById('overlay');
  const drawer = document.getElementById('drawer');
  const close = () => { overlay.classList.remove('open'); drawer.classList.remove('open'); };
  overlay.addEventListener('click', close);
  document.getElementById('closeDrawer').addEventListener('click', close);
  document.querySelector('.drawer-close').addEventListener('click', close);
}

function openEventDrawer(event) {
  document.getElementById('drawerDate').textContent = event.Timestamp ? new Date(event.Timestamp).toLocaleString() : '—';
  document.getElementById('drawerVehicle').textContent = event.Registration_Number || '—';
  document.getElementById('drawerVIN').textContent = event.VIN || '—';
  document.getElementById('drawerSeverity').textContent = event.Severity_Level || '—';
  document.getElementById('drawerOdometer').textContent = event.Odometer_At_Event != null ? Number(event.Odometer_At_Event).toLocaleString() + ' km' : '—';
  document.getElementById('drawerEvent').textContent = event.Event_Type || '—';
  document.getElementById('drawerComments').textContent = event.Review_Comments || 'No review comments available.';

  document.getElementById('overlay').classList.add('open');
  document.getElementById('drawer').classList.add('open');
}

/* ============ LOAD + RENDER PROFILE ============ */

async function loadDriverProfile() {
  try {
    const res = await fetch('../Backend/api/driver.php?action=profile');
    const data = await res.json();

    if (!res.ok) {
      console.error('Failed to load driver profile:', data.error || data);
      return;
    }

    renderPersonalInfo(data.profile);
    renderCertifications(data.certifications);
    renderCurrentVehicle(data.vehicles);
    renderSafetyScores(data.scores);
    renderSafetyEvents(data.events);
  } catch (err) {
    console.error('Network error loading driver profile:', err);
  }
}

function renderPersonalInfo(profile) {
  if (!profile) return;
  document.getElementById('driverId').textContent = profile.Driver_ID ?? '—';
  document.getElementById('driverName').textContent = profile.Full_Name ?? '—';
  document.getElementById('driverDepot').textContent = profile.Location_Name ?? '—';
  document.getElementById('driverStatus').textContent = profile.Employment_Status ?? '—';
  document.getElementById('driverContact').textContent = profile.Contact_Information ?? '—';
  document.getElementById('driverEmergency').textContent = profile.Emergency_Contact ?? '—';
  document.getElementById('driverLicenceType').textContent = profile.License_Type ?? '—';
  document.getElementById('driverLicenceExpiry').textContent = profile.License_Expiry_Date ?? '—';
}

function renderCertifications(certifications) {
  const tbody = document.getElementById('certificationTableBody');
  if (!certifications || certifications.length === 0) {
    tbody.innerHTML = '<tr><td colspan="3">No certifications on file.</td></tr>';
    return;
  }

  const today = new Date();
  tbody.innerHTML = certifications.map(c => {
    const expiry = new Date(c.Expiry_Date);
    const expired = expiry < today;
    return `
      <tr>
        <td>${c.Certification_Name}</td>
        <td class="mono">${c.Expiry_Date}</td>
        <td><span class="pill ${expired ? 'crit' : 'ok'}">${expired ? 'Expired' : 'Valid'}</span></td>
      </tr>
    `;
  }).join('');
}

function renderCurrentVehicle(vehicles) {
  // The current/active assignment is the one with no End_Date yet;
  // fall back to the most recent one (list is already ordered DESC by Start_Date).
  const current = (vehicles || []).find(v => !v.End_Date) || (vehicles || [])[0];

  if (!current) {
    document.getElementById('vehicleRegistration').textContent = 'No vehicle currently assigned';
    document.getElementById('vehicleModel').textContent = '—';
    document.getElementById('vehicleVIN').textContent = '—';
    document.getElementById('assignmentDate').textContent = '—';
    return;
  }

  document.getElementById('vehicleRegistration').textContent = current.Registration_Number ?? '—';
  document.getElementById('vehicleModel').textContent = `${current.Manufacturer_and_Model ?? ''} (${current.Vehicle_Category ?? ''})`;
  document.getElementById('vehicleVIN').textContent = current.VIN ?? '—';
  document.getElementById('assignmentDate').textContent = current.Start_Date ?? '—';
}

function renderSafetyScores(scores) {
  const scoreEl = document.getElementById('currentSafetyScore');
  const tbody = document.getElementById('scoreHistoryTableBody');

  if (!scores || scores.length === 0) {
    scoreEl.textContent = '—';
    tbody.innerHTML = '<tr><td colspan="3">No safety scores recorded yet.</td></tr>';
    return;
  }

  scoreEl.textContent = scores[0].Score; // list is ordered newest-first
  tbody.innerHTML = scores.map(s => `
    <tr><td>${s.Month}</td><td>${s.Year}</td><td class="mono">${s.Score}</td></tr>
  `).join('');
}

function renderSafetyEvents(events) {
  const tbody = document.getElementById('safetyEventTableBody');
  if (!events || events.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5">No safety events recorded.</td></tr>';
    return;
  }

  const severityPill = { Low: 'neutral', Medium: 'warn', High: 'warn', Critical: 'crit' };

  tbody.innerHTML = events.map((e, i) => `
    <tr onclick='openEventDrawer(${JSON.stringify(e).replace(/'/g, "&#39;")})' style="cursor:pointer;">
      <td class="mono">${e.Timestamp ? new Date(e.Timestamp).toLocaleDateString() : '—'}</td>
      <td>${e.Registration_Number ?? '—'}</td>
      <td>${e.Event_Type ?? '—'}</td>
      <td><span class="pill ${severityPill[e.Severity_Level] || 'neutral'}">${e.Severity_Level ?? '—'}</span></td>
      <td class="action-icons"><button class="icon-btn">→</button></td>
    </tr>
  `).join('');
}
