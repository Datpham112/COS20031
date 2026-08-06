

const ENTITY_CONFIG = {

  vehicle: {
    label: 'Vehicle', endpoint: '../Backend/api/vehicle.php', idParam: 'vin', idColumn: 'Vin',
    fields: [
      { name: 'vin', column: 'Vin', label: 'VIN', type: 'text', required: true, pk: true },
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot', type: 'number', required: true, autoFill: 'depot' },
      { name: 'registration_number', column: 'Registration_Number', label: 'Registration No.', type: 'text', required: true },
      { name: 'vehicle_category', column: 'Vehicle_Category', label: 'Category', type: 'select', required: true,
        options: ['Delivery Van', 'Refrigerated Truck', 'Electric Van', 'Service Vehicle', 'Heavy Transport Truck'] },
      { name: 'manufacturer_and_model', column: 'Manufacturer_and_Model', label: 'Manufacturer & Model', type: 'text', required: true },
      { name: 'year_of_manufacture', column: 'Year_of_Manufacture', label: 'Year', type: 'number', required: true },
      { name: 'current_odometer', column: 'Current_Odometer', label: 'Odometer (km)', type: 'number', required: false },
      { name: 'operational_status', column: 'Operational_Status', label: 'Status', type: 'select', required: true,
        options: ['Active', 'Available', 'Under Maintenance', 'Awaiting Inspection', 'Out of Service', 'Retired'] },
    ],
  },

  driver: {
    label: 'Driver', endpoint: '../Backend/api/driver.php', idParam: 'driver_id', idColumn: 'Driver_ID',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true, pk: true },
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot', type: 'number', required: true, autoFill: 'depot' },
      { name: 'full_name', column: 'Full_Name', label: 'Full Name', type: 'text', required: true },
      { name: 'contact_information', column: 'Contact_Information', label: 'Contact Info', type: 'text', required: true },
      { name: 'emergency_contact', column: 'Emergency_Contact', label: 'Emergency Contact', type: 'text', required: true },
      { name: 'license_type', column: 'License_Type', label: 'License Type', type: 'text', required: true },
      { name: 'license_expiry_date', column: 'License_Expiry_Date', label: 'License Expiry', type: 'date', required: true },
      { name: 'employment_status', column: 'Employment_Status', label: 'Employment Status', type: 'select', required: true,
        options: ['Active', 'On Leave', 'Suspended', 'Terminated'] },
      // Login account, created together with the Driver row - only shown/sent when creating, not editing.
      { name: 'login_staff_id', column: null, label: 'Login Staff ID (e.g. S011)', type: 'text', required: true, createOnly: true },
      { name: 'login_username', column: null, label: 'Login Username', type: 'text', required: true, createOnly: true },
      { name: 'login_password', column: null, label: 'Login Password', type: 'password', required: true, createOnly: true },
    ],
  },

  driver_certification: {
    label: 'Driver Certification', endpoint: '../Backend/api/driver_certification.php', idParam: 'cert_key', idColumn: 'Cert_Key',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true, pk: true,
        dynamicOptions: { endpoint: '../Backend/api/driver.php', value: 'Driver_ID', label: r => `${r.Driver_ID} — ${r.Full_Name}` } },
      { name: 'certification_name', column: 'Certification_Name', label: 'Certification Name', type: 'text', required: true, pk: true },
      { name: 'expiry_date', column: 'Expiry_Date', label: 'Expiry Date', type: 'date', required: true },
    ],
  },

  vehicle_driver_assignment: {
    label: 'Vehicle Assignment', endpoint: '../Backend/api/vehicle_driver_assignment.php', idParam: 'assignment_id', idColumn: 'Assignment_ID',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true,
        dynamicOptions: { endpoint: '../Backend/api/driver.php', value: 'Driver_ID', label: r => `${r.Driver_ID} — ${r.Full_Name}` } },
      { name: 'vin', column: 'VIN', label: 'Vehicle VIN', type: 'text', required: true,
        dynamicOptions: { endpoint: '../Backend/api/vehicle.php', value: 'Vin', label: r => `${r.Vin} — ${r.Registration_Number} (${r.Vehicle_Category})` } },
      { name: 'start_date', column: 'Start_Date', label: 'Start Date', type: 'datetime-local', required: true },
      { name: 'end_date', column: 'End_Date', label: 'End Date (leave blank if still active)', type: 'datetime-local', required: false },
    ],
  },

  safety_event: {
    label: 'Safety Event', endpoint: '../Backend/api/safety_event.php', idParam: 'event_id', idColumn: 'Event_ID',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true,
        dynamicOptions: { endpoint: '../Backend/api/driver.php', value: 'Driver_ID', label: r => `${r.Driver_ID} — ${r.Full_Name}` } },
      { name: 'vin', column: 'VIN', label: 'Vehicle VIN', type: 'text', required: true,
        dynamicOptions: { endpoint: '../Backend/api/vehicle.php', value: 'Vin', label: r => `${r.Vin} — ${r.Registration_Number} (${r.Vehicle_Category})` } },
      { name: 'timestamp', column: 'Timestamp', label: 'Date/Time', type: 'datetime-local', required: true },
      { name: 'event_type', column: 'Event_Type', label: 'Event Type', type: 'text', required: true,
        dynamicOptions: { endpoint: '../Backend/api/event_penalty.php', value: 'Event_Type', label: r => `${r.Event_Type} (-${r.Penalty_Points} pts)` } },
      { name: 'severity_level', column: 'Severity_Level', label: 'Severity', type: 'select', required: true,
        options: ['Low', 'Medium', 'High', 'Critical'] },
      { name: 'odometer_at_event', column: 'Odometer_At_Event', label: 'Odometer at Event (km)', type: 'number', required: true },
      { name: 'review_comments', column: 'Review_Comments', label: 'Review Comments', type: 'text', required: false },
    ],
  },

  driver_safety_score: {
    label: 'Driver Safety Score', endpoint: '../Backend/api/driver_safety_score.php', idParam: 'score_id', idColumn: 'Score_ID',
    readOnly: true, // auto-calculated by UpdateMonthlySafetyScores() from Safety_Event + Event_Penalty -- see recalcEndpoint below
    recalcEndpoint: '../Backend/api/recalculate_safety_scores.php',
    recalcLabel: 'Recalculate Scores',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true },
      { name: 'month', column: 'Month', label: 'Month', type: 'number', required: true },
      { name: 'year', column: 'Year', label: 'Year', type: 'number', required: true },
      { name: 'score', column: 'Score', label: 'Score', type: 'number', required: true },
    ],
  },

  workshop: {
    label: 'Workshop', endpoint: '../Backend/api/workshop_crud.php', idParam: 'workshop_id', idColumn: 'Workshop_ID',
    fields: [
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot ID', type: 'number', required: true },
    ],
  },

  mechanic: {
    label: 'Mechanic', endpoint: '../Backend/api/mechanic.php', idParam: 'mechanic_id', idColumn: 'Mechanic_ID',
    fields: [
      { name: 'workshop_id', column: 'Workshop_ID', label: 'Workshop ID', type: 'number', required: true },
      { name: 'full_name', column: 'Full_Name', label: 'Full Name', type: 'text', required: true },
    ],
  },

  maintenance_job: {
    label: 'Maintenance Job', endpoint: '../Backend/api/maintenance_job.php', idParam: 'job_id', idColumn: 'Job_ID',
    fields: [
      { name: 'vin', column: 'VIN', label: 'Vehicle VIN', type: 'text', required: true },
      { name: 'workshop_id', column: 'Workshop_ID', label: 'Workshop ID', type: 'number', required: true },
      { name: 'linked_alert_id', column: 'Linked_Alert_ID', label: 'Linked Alert ID', type: 'number', required: false },
      { name: 'date_opened', column: 'Date_Opened', label: 'Date Opened', type: 'date', required: true },
      { name: 'date_closed', column: 'Date_Closed', label: 'Date Closed', type: 'date', required: false },
      { name: 'downtime_hours', column: 'Downtime_Hours', label: 'Downtime (hrs)', type: 'number', required: false },
      { name: 'total_cost', column: 'Total_Cost', label: 'Total Cost', type: 'number', required: false },
    ],
  },

  alert: {
    label: 'Predictive Alert', endpoint: '../Backend/api/alert.php', idParam: 'alert_id', idColumn: 'Alert_ID',
    fields: [
      { name: 'vin', column: 'VIN', label: 'Vehicle VIN', type: 'text', required: true },
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot ID', type: 'number', required: true },
      { name: 'alert_type', column: 'Alert_Type', label: 'Alert Type', type: 'select', required: true,
        options: ['Brake Wear', 'Overheating Risk', 'Battery Degradation', 'Engine Fault', 'Tyre Pressure'] },
      { name: 'severity_level', column: 'Severity_Level', label: 'Severity', type: 'select', required: false,
        options: ['Low', 'Medium', 'High', 'Critical'] },
      { name: 'action_taken', column: 'Action_Taken', label: 'Action Taken', type: 'select', required: true,
        options: ['Acknowledged', 'Scheduled Repair', 'Emergency Repair', 'Resolved'] },
      { name: 'raised_at', column: 'Raised_At', label: 'Raised At', type: 'datetime-local', required: false },
    ],
  },

  part: {
    label: 'Part', endpoint: '../Backend/api/part.php', idParam: 'part_id', idColumn: 'Part_ID',
    fields: [
      { name: 'part_name', column: 'Part_Name', label: 'Part Name', type: 'text', required: true },
      { name: 'part_category', column: 'Part_Category', label: 'Category', type: 'text', required: false },
      { name: 'brand', column: 'Brand', label: 'Brand', type: 'text', required: false },
      { name: 'unit_price', column: 'Unit_Price', label: 'Unit Price', type: 'number', required: false },
      { name: 'reorder_level', column: 'Reorder_Level', label: 'Reorder Level', type: 'number', required: false },
    ],
  },

  supplier: {
    label: 'Supplier', endpoint: '../Backend/api/supplier.php', idParam: 'supplier_id', idColumn: 'Supplier_ID',
    fields: [
      { name: 'supplier_name', column: 'Supplier_Name', label: 'Supplier Name', type: 'text', required: true },
      { name: 'contact_name', column: 'Contact_Name', label: 'Contact Name', type: 'text', required: false },
      { name: 'phone_number', column: 'Phone_Number', label: 'Phone Number', type: 'text', required: true },
      { name: 'email_address', column: 'Email_Address', label: 'Email', type: 'text', required: false },
      { name: 'address', column: 'Address', label: 'Address', type: 'text', required: false },
      { name: 'delivery_time', column: 'Delivery_Time', label: 'Delivery Time', type: 'datetime-local', required: false },
    ],
  },

  staff: {
    label: 'Staff', endpoint: '../Backend/api/staff.php', idParam: 'staff_id', idColumn: 'Staff_ID',
    fields: [
      { name: 'staff_id', column: 'Staff_ID', label: 'Staff ID', type: 'text', required: true, pk: true },
      { name: 'full_name', column: 'Full_Name', label: 'Full Name', type: 'text', required: true },
      { name: 'role_type', column: 'Role_Type', label: 'Role', type: 'select', required: true,
        options: ['Head Manager', 'Depot Manager', 'Driver Manager', 'Workshop Manager', 'Inventory Manager', 'Mechanic', 'Driver'] },
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot ID (blank for Head/Inventory Manager)', type: 'number', required: false },
      { name: 'contact_info', column: 'Contact_Info', label: 'Contact Info', type: 'text', required: true },
      { name: 'username', column: 'Username', label: 'Username', type: 'text', required: true },
      { name: 'password', column: null, label: 'Password (leave blank to keep unchanged when editing)', type: 'password', required: true },
    ],
  },

};

let currentEditId = {}; // tracks which row (if any) is being edited, per entity key
let currentUser = null; // filled by fetchCurrentUser() before the UI is built

/**
 * Loads the logged-in staff's own session info (depot, role, etc.) so
 * depot-scoped forms (Vehicle, Driver) can auto-fill + lock the Depot
 * field instead of making the user type a raw depot number.
 */
async function fetchCurrentUser() {
  try {
    const res = await fetch('../Backend/api/me.php');
    const me = await res.json();
    currentUser = me.loggedIn ? me : null;
  } catch (err) {
    console.warn('Could not load current user session:', err);
    currentUser = null;
  }
}

/* ============ BUILD TABS + PANELS ============ */

function buildUI() {
  const tabsBox = document.getElementById('entityTabs');
  const panelsBox = document.getElementById('entityPanels');
  const keys = Object.keys(ENTITY_CONFIG);

  keys.forEach((key, i) => {
    const cfg = ENTITY_CONFIG[key];

    const tabBtn = document.createElement('button');
    tabBtn.className = 'tab-btn' + (i === 0 ? ' active' : '');
    tabBtn.textContent = cfg.label;
    tabBtn.onclick = () => switchTab(key, tabBtn);
    tabsBox.appendChild(tabBtn);

    const panel = document.createElement('div');
    panel.className = 'tab-panel' + (i === 0 ? ' active' : '');
    panel.id = 'panel-' + key;

    if (cfg.readOnly) {
      // Derived data (e.g. Driver Safety Score, computed by a stored
      // procedure from Safety_Event + Event_Penalty) -- no add/edit
      // form, just the table plus an optional action to recompute it.
      panel.innerHTML = `
        <div class="card" style="margin-bottom:16px; padding:16px 20px;">
          <div class="panel-title" style="margin-bottom:8px;">${cfg.label} (auto-calculated)</div>
          ${cfg.recalcEndpoint ? `
            <button type="button" class="btn-primary" id="recalc-btn-${key}">${cfg.recalcLabel || 'Recalculate'}</button>
            <span class="md-status" id="status-${key}"></span>
          ` : ''}
        </div>
        <div class="card md-table-wrap">
          <table class="data-table" id="table-${key}"></table>
        </div>
      `;
      panelsBox.appendChild(panel);
      if (cfg.recalcEndpoint) {
        document.getElementById(`recalc-btn-${key}`).addEventListener('click', () => runRecalc(key));
      }
      loadEntity(key);
      return;
    }

    panel.innerHTML = `
      <div class="card" style="margin-bottom:16px;">
        <div class="panel-head" style="padding:16px 20px 0;">
          <div class="panel-title" id="form-title-${key}">Add ${cfg.label}</div>
        </div>
        <form id="form-${key}" class="md-form"></form>
      </div>
      <div class="card md-table-wrap">
        <table class="data-table" id="table-${key}"></table>
      </div>
    `;
    panelsBox.appendChild(panel);

    buildForm(key);
    document.getElementById(`form-${key}`).addEventListener('submit', (e) => submitForm(key, e));
    cfg.fields.filter(f => f.dynamicOptions).forEach(f => populateDynamicSelect(key, f));
    loadEntity(key);
  });
}

function switchTab(key, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('panel-' + key).classList.add('active');
}

/* ============ FORM ============ */

function buildForm(key) {
  const cfg = ENTITY_CONFIG[key];
  const form = document.getElementById(`form-${key}`);

  const fieldsHtml = cfg.fields.map(f => {
    const inputId = `${key}-${f.name}`;
    let input;

    if (f.autoFill === 'depot') {
      // Locked to the logged-in manager's own depot - no manual picker,
      // just a read-only line plus a hidden input that still submits.
      const depotId = currentUser?.depotId ?? '';
      const depotLabel = depotId !== '' ? `Depot #${depotId} (your depot)` : '— not scoped to a depot —';
      input = `
        <div class="md-locked-field">${depotLabel}</div>
        <input id="${inputId}" name="${f.name}" type="hidden" value="${depotId}">
      `;
    } else if (f.dynamicOptions) {
      // Populated from a live API call after the form renders (see
      // populateDynamicSelect) so the picker only ever offers IDs the
      // logged-in user is actually allowed to use (their own depot).
      input = `<select id="${inputId}" name="${f.name}" ${f.required ? 'required' : ''}><option value="">Loading…</option></select>`;
    } else if (f.type === 'select') {
      const opts = ['<option value="">— select —</option>']
        .concat(f.options.map(o => `<option value="${o}">${o}</option>`));
      input = `<select id="${inputId}" name="${f.name}" ${f.required ? 'required' : ''}>${opts.join('')}</select>`;
    } else {
      input = `<input id="${inputId}" name="${f.name}" type="${f.type}" step="${f.type === 'number' ? 'any' : ''}" ${f.required ? 'required' : ''}>`;
    }

    const fieldHtml = `<div class="md-field"><label>${f.label}${f.required && !f.autoFill ? ' *' : ''}</label>${input}</div>`;

    // create-only fields (e.g. login account) get wrapped so they can be hidden while editing
    return f.createOnly ? `<div class="md-create-only" data-field="${f.name}">${fieldHtml}</div>` : fieldHtml;
  }).join('');

  form.innerHTML = fieldsHtml + `
    <div class="md-actions">
      <button type="submit" class="btn-primary" id="submit-btn-${key}">Create</button>
      <button type="button" class="btn-secondary" id="cancel-btn-${key}" style="display:none;" onclick="cancelEdit('${key}')">Cancel edit</button>
      <span class="md-status" id="status-${key}"></span>
    </div>
  `;
}

/**
 * Fetches the option list for a dynamicOptions field (e.g. every Driver
 * or Vehicle the logged-in user can see - the backend already scopes
 * this to their own depot) and fills in the <select>. If the field
 * already has a value set (e.g. startEdit ran first), that value is
 * restored after the options load.
 */
async function populateDynamicSelect(key, f) {
  const select = document.getElementById(`${key}-${f.name}`);
  if (!select) return;
  const previousValue = select.value;

  try {
    const res = await fetch(f.dynamicOptions.endpoint);
    const rows = await res.json();

    if (!Array.isArray(rows)) {
      select.innerHTML = `<option value="">Could not load options</option>`;
      return;
    }

    const opts = ['<option value="">— select —</option>']
      .concat(rows.map(r => {
        const val = r[f.dynamicOptions.value];
        return `<option value="${val}">${f.dynamicOptions.label(r)}</option>`;
      }));
    select.innerHTML = opts.join('');

    if (previousValue) select.value = previousValue;
  } catch (err) {
    select.innerHTML = `<option value="">Network error loading options</option>`;
  }
}

async function runRecalc(key) {
  const cfg = ENTITY_CONFIG[key];
  const statusEl = document.getElementById(`status-${key}`);
  const btn = document.getElementById(`recalc-btn-${key}`);
  btn.disabled = true;
  statusEl.textContent = 'Recalculating…';
  statusEl.className = 'md-status';

  try {
    const res = await fetch(cfg.recalcEndpoint, { method: 'POST' });
    const data = await res.json();
    if (res.ok) {
      statusEl.textContent = data.message || 'Done';
      statusEl.classList.add('ok');
      loadEntity(key);
    } else {
      statusEl.textContent = (data.error || 'Error') + (data.detail ? ' — ' + data.detail : '');
      statusEl.classList.add('err');
    }
  } catch (err) {
    statusEl.textContent = 'Network error: ' + err.message;
    statusEl.classList.add('err');
  } finally {
    btn.disabled = false;
  }
}

async function submitForm(key, event) {
  event.preventDefault();
  const cfg = ENTITY_CONFIG[key];
  const statusEl = document.getElementById(`status-${key}`);
  statusEl.textContent = '';
  statusEl.className = 'md-status';

  const isEdit = currentEditId[key] != null;

  const body = {};
  cfg.fields.forEach(f => {
    if (isEdit && f.createOnly) return; // login account fields only apply on create
    const val = document.getElementById(`${key}-${f.name}`).value;
    // Skip empty optional fields and blank password on edit (keeps existing one)
    if (val !== '') body[f.name] = val;
  });

  const url = isEdit ? `${cfg.endpoint}?${cfg.idParam}=${encodeURIComponent(currentEditId[key])}` : cfg.endpoint;
  const method = isEdit ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();

    if (res.ok) {
      statusEl.textContent = data.message || 'Saved';
      statusEl.classList.add('ok');
      cancelEdit(key);
      loadEntity(key);
    } else {
      statusEl.textContent = (data.error || 'Error') + (data.fields ? ': ' + data.fields.join(', ') : '') + (data.detail ? ' — ' + data.detail : '');
      statusEl.classList.add('err');
    }
  } catch (err) {
    statusEl.textContent = 'Network error: ' + err.message;
    statusEl.classList.add('err');
  }
}

function cancelEdit(key) {
  currentEditId[key] = null;
  document.getElementById(`form-${key}`).reset();
  document.getElementById(`form-title-${key}`).textContent = `Add ${ENTITY_CONFIG[key].label}`;
  document.getElementById(`submit-btn-${key}`).textContent = 'Create';
  document.getElementById(`cancel-btn-${key}`).style.display = 'none';

  // Re-enable the primary key field(s) (disabled while editing). Some
  // tables (e.g. Driver Certification) have a composite primary key,
  // so every field marked pk:true needs to be re-enabled here.
  ENTITY_CONFIG[key].fields.filter(f => f.pk).forEach(f => {
    document.getElementById(`${key}-${f.name}`).disabled = false;
  });

  // Bring back the login-account fields (they only apply when creating)
  document.querySelectorAll(`#form-${key} .md-create-only`).forEach(el => {
    el.style.display = '';
  });
}

function startEdit(key, row) {
  const cfg = ENTITY_CONFIG[key];
  const idValue = row[cfg.idColumn];
  currentEditId[key] = idValue;

  cfg.fields.forEach(f => {
    if (f.createOnly) return; // these have no meaningful "current value" to show on edit
    const el = document.getElementById(`${key}-${f.name}`);
    if (f.column && row[f.column] !== undefined && row[f.column] !== null) {
      el.value = row[f.column];
    } else {
      el.value = '';
    }
    el.required = f.required && f.name !== 'password'; // password becomes optional on edit
  });

  // Composite keys (e.g. Driver Certification: Driver_ID + Certification_Name)
  // must have every pk field locked, not just the first one.
  cfg.fields.filter(f => f.pk).forEach(f => {
    document.getElementById(`${key}-${f.name}`).disabled = true;
  });

  // Login-account fields only make sense when CREATING a driver, hide them on edit.
  document.querySelectorAll(`#form-${key} .md-create-only`).forEach(el => {
    el.style.display = 'none';
  });

  document.getElementById(`form-title-${key}`).textContent = `Edit ${cfg.label}`;
  document.getElementById(`submit-btn-${key}`).textContent = 'Save changes';
  document.getElementById(`cancel-btn-${key}`).style.display = 'inline-block';

  document.getElementById(`panel-${key}`).scrollIntoView({ behavior: 'smooth' });
}

async function deleteRow(key, idValue) {
  if (!confirm(`Delete this ${ENTITY_CONFIG[key].label.toLowerCase()}? This cannot be undone.`)) return;
  const cfg = ENTITY_CONFIG[key];
  try {
    const res = await fetch(`${cfg.endpoint}?${cfg.idParam}=${encodeURIComponent(idValue)}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) alert((data.error || 'Delete failed') + (data.detail ? '\n' + data.detail : ''));
    loadEntity(key);
  } catch (err) {
    alert('Network error: ' + err.message);
  }
}

/* ============ TABLE ============ */

async function loadEntity(key) {
  const cfg = ENTITY_CONFIG[key];
  const table = document.getElementById(`table-${key}`);
  table.innerHTML = '<thead><tr><th>Loading…</th></tr></thead>';

  try {
    const res = await fetch(cfg.endpoint);
    const rows = await res.json();

    if (!Array.isArray(rows)) {
      table.innerHTML = `<thead><tr><th>Error loading data: ${(rows.error || 'unknown')}</th></tr></thead>`;
      return;
    }

    const displayCols = cfg.fields.filter(f => f.column); // skip password (column:null)
    const showActions = !cfg.readOnly;
    const headHtml = '<tr>' + displayCols.map(f => `<th>${f.label.split(' (')[0]}</th>`).join('') + (showActions ? '<th>Actions</th>' : '') + '</tr>';

    const bodyHtml = rows.length
      ? rows.map(row => {
          const idValue = row[cfg.idColumn];
          const cells = displayCols.map(f => `<td>${row[f.column] ?? ''}</td>`).join('');
          const actionsHtml = showActions ? `<td>
            <button class="md-action-btn" onclick='startEdit("${key}", ${JSON.stringify(row).replace(/'/g, "&#39;")})'>Edit</button>
            <button class="md-action-btn danger" onclick="deleteRow('${key}', '${idValue}')">Delete</button>
          </td>` : '';
          return `<tr>${cells}${actionsHtml}</tr>`;
        }).join('')
      : `<tr><td colspan="${displayCols.length + (showActions ? 1 : 0)}">No records yet.</td></tr>`;

    table.innerHTML = `<thead>${headHtml}</thead><tbody>${bodyHtml}</tbody>`;
  } catch (err) {
    table.innerHTML = `<thead><tr><th>Network error: ${err.message}</th></tr></thead>`;
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  await fetchCurrentUser();
  buildUI();
});
