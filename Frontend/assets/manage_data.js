/**
 * Frontend/assets/manage_data.js
 * ------------------------------------------------------------------
 * Generic CRUD UI. Instead of hand-writing 9 nearly-identical forms +
 * tables, each entity is described once in ENTITY_CONFIG and the form/
 * table/edit/delete logic is built from that description.
 *
 * To add a 10th table later: just add one more entry to ENTITY_CONFIG,
 * nothing else needs to change.
 * ------------------------------------------------------------------
 */

const ENTITY_CONFIG = {

  vehicle: {
    label: 'Vehicle', endpoint: '../Backend/api/vehicle.php', idParam: 'vin', idColumn: 'Vin',
    fields: [
      { name: 'vin', column: 'Vin', label: 'VIN', type: 'text', required: true, pk: true },
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot ID', type: 'number', required: true },
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
      { name: 'depot_id', column: 'Depot_ID', label: 'Depot ID', type: 'number', required: true },
      { name: 'full_name', column: 'Full_Name', label: 'Full Name', type: 'text', required: true },
      { name: 'contact_information', column: 'Contact_Information', label: 'Contact Info', type: 'text', required: true },
      { name: 'emergency_contact', column: 'Emergency_Contact', label: 'Emergency Contact', type: 'text', required: true },
      { name: 'license_type', column: 'License_Type', label: 'License Type', type: 'text', required: true },
      { name: 'license_expiry_date', column: 'License_Expiry_Date', label: 'License Expiry', type: 'date', required: true },
      { name: 'employment_status', column: 'Employment_Status', label: 'Employment Status', type: 'select', required: true,
        options: ['Active', 'On Leave', 'Suspended', 'Terminated'] },
    ],
  },

  driver_certification: {
    label: 'Driver Certification', endpoint: '../Backend/api/driver_certification.php', idParam: 'cert_key', idColumn: 'Cert_Key',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true, pk: true },
      { name: 'certification_name', column: 'Certification_Name', label: 'Certification Name', type: 'text', required: true, pk: true },
      { name: 'expiry_date', column: 'Expiry_Date', label: 'Expiry Date', type: 'date', required: true },
    ],
  },

  vehicle_driver_assignment: {
    label: 'Vehicle Assignment', endpoint: '../Backend/api/vehicle_driver_assignment.php', idParam: 'assignment_id', idColumn: 'Assignment_ID',
    fields: [
      { name: 'driver_id', column: 'Driver_ID', label: 'Driver ID', type: 'text', required: true },
      { name: 'vin', column: 'VIN', label: 'Vehicle VIN', type: 'text', required: true },
      { name: 'start_date', column: 'Start_Date', label: 'Start Date', type: 'datetime-local', required: true },
      { name: 'end_date', column: 'End_Date', label: 'End Date (leave blank if still active)', type: 'datetime-local', required: false },
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

const ROLE_ENTITY_ACCESS = {
  'Head Manager': Object.keys(ENTITY_CONFIG),
  'Depot Manager': ['vehicle', 'driver', 'driver_certification', 'vehicle_driver_assignment', 'workshop', 'staff'],
  'Driver Manager': ['vehicle', 'driver', 'driver_certification', 'vehicle_driver_assignment'],
  'Workshop Manager': ['workshop', 'mechanic', 'maintenance_job', 'alert', 'part', 'staff'],
  'Inventory Manager': ['part', 'supplier', 'staff'],
};

function getVisibleEntityKeys(roleType) {
  return ROLE_ENTITY_ACCESS[roleType] || [];
}

let currentEditId = {}; // tracks which row (if any) is being edited, per entity key

/* ============ BUILD TABS + PANELS ============ */

function buildUI() {
  const tabsBox = document.getElementById('entityTabs');
  const panelsBox = document.getElementById('entityPanels');
  const keys = getVisibleEntityKeys(window.currentStaff.roleType);

  if (keys.length === 0) {
    tabsBox.innerHTML = '<div class="empty-state">Bạn không có quyền xem phần này.</div>';
    panelsBox.innerHTML = '';
    return;
  }

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
    if (f.type === 'select') {
      const opts = ['<option value="">— select —</option>']
        .concat(f.options.map(o => `<option value="${o}">${o}</option>`));
      input = `<select id="${inputId}" name="${f.name}" ${f.required ? 'required' : ''}>${opts.join('')}</select>`;
    } else {
      input = `<input id="${inputId}" name="${f.name}" type="${f.type}" step="${f.type === 'number' ? 'any' : ''}" ${f.required ? 'required' : ''}>`;
    }
    return `<div class="md-field"><label>${f.label}${f.required ? ' *' : ''}</label>${input}</div>`;
  }).join('');

  form.innerHTML = fieldsHtml + `
    <div class="md-actions">
      <button type="submit" class="btn-primary" id="submit-btn-${key}">Create</button>
      <button type="button" class="btn-secondary" id="cancel-btn-${key}" style="display:none;" onclick="cancelEdit('${key}')">Cancel edit</button>
      <span class="md-status" id="status-${key}"></span>
    </div>
  `;
}

async function submitForm(key, event) {
  event.preventDefault();
  const cfg = ENTITY_CONFIG[key];
  const statusEl = document.getElementById(`status-${key}`);
  statusEl.textContent = '';
  statusEl.className = 'md-status';

  const body = {};
  cfg.fields.forEach(f => {
    const val = document.getElementById(`${key}-${f.name}`).value;
    // Skip empty optional fields and blank password on edit (keeps existing one)
    if (val !== '') body[f.name] = val;
  });

  const isEdit = currentEditId[key] != null;
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
}

function startEdit(key, row) {
  const cfg = ENTITY_CONFIG[key];
  const idValue = row[cfg.idColumn];
  currentEditId[key] = idValue;

  cfg.fields.forEach(f => {
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
    const headHtml = '<tr>' + displayCols.map(f => `<th>${f.label.split(' (')[0]}</th>`).join('') + '<th>Actions</th></tr>';

    const bodyHtml = rows.length
      ? rows.map(row => {
          const idValue = row[cfg.idColumn];
          const cells = displayCols.map(f => `<td>${row[f.column] ?? ''}</td>`).join('');
          return `<tr>${cells}<td>
            <button class="md-action-btn" onclick='startEdit("${key}", ${JSON.stringify(row).replace(/'/g, "&#39;")})'>Edit</button>
            <button class="md-action-btn danger" onclick="deleteRow('${key}', '${idValue}')">Delete</button>
          </td></tr>`;
        }).join('')
      : `<tr><td colspan="${displayCols.length + 1}">No records yet.</td></tr>`;

    table.innerHTML = `<thead>${headHtml}</thead><tbody>${bodyHtml}</tbody>`;
  } catch (err) {
    table.innerHTML = `<thead><tr><th>Network error: ${err.message}</th></tr></thead>`;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (window.currentStaff) {
    buildUI();
  } else {
    document.addEventListener('staffReady', buildUI, { once: true });
  }
});
