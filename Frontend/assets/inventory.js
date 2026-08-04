/**
 * inventory.js
 * Connects frontend inventory.html to backend API (part.php, supplier.php)
 */

const PART_API_URL = "../Backend/api/part.php";
const SUPPLIER_API_URL = "../Backend/api/supplier.php";

let inventoryData = {
    parts: [],
    suppliers: [],
    warrantyClaims: []
};

function renderTableRows(id, items, emptyText, buildRow, colspan = 1) {
    const tbody = document.getElementById(id);
    if (!tbody) return;
    tbody.replaceChildren();

    if (!items?.length) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = colspan;
        cell.textContent = emptyText;
        cell.style.textAlign = "center";
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

document.addEventListener("DOMContentLoaded", () => {
    loadInventoryData();
    setupTabs();
    setupDrawers(); 
});

async function loadInventoryData() {
    try {
        // Fetch Parts
        const partResponse = await fetch(PART_API_URL);
        if (!partResponse.ok) throw new Error("Failed to load parts");
        const partsDB = await partResponse.json();

        // Fetch Suppliers
        const supplierResponse = await fetch(SUPPLIER_API_URL);
        if (!supplierResponse.ok) throw new Error("Failed to load suppliers");
        const suppliersDB = await supplierResponse.json();

        inventoryData.parts = partsDB.map(p => ({
            id: p.Part_ID,
            name: p.Part_Name,
            category: p.Part_Category || "General",
            brand: p.Brand || "N/A",
            price: p.Unit_Price || 0,
            reorderLevel: p.Reorder_Level || 0,
            currentStock: 0 // Tạm để 0
        }));

        inventoryData.suppliers = suppliersDB.map(s => ({
            id: s.Supplier_ID,
            name: s.Supplier_Name,
            contact: s.Contact_Name || "N/A",
            phone: s.Phone_Number,
            email: s.Email_Address || "N/A",
            address: s.Address || "N/A"
        }));

        // Mock data cho Warranty 
        inventoryData.warrantyClaims = [
            { id: 1, partName: "Brake Pad Set", date: "2026-07-28", type: "Part Replacement", status: "Pending" },
            { id: 2, partName: "EV Battery Module", date: "2026-07-29", type: "Battery Warranty", status: "Approved" }
        ];

        renderParts(inventoryData.parts);
        renderSuppliers(inventoryData.suppliers);
        renderWarranty(inventoryData.warrantyClaims);

    } catch (error) {
        console.error("Using mock data due to API error:", error);
        inventoryData = {
            parts: [{ id: 1, name: "Brake Pad Set (Mock)", category: "Brake System", brand: "Bosch", price: "450000", reorderLevel: 10, currentStock: 5 }],
            suppliers: [{ name: "AutoParts Vietnam (Mock)", contact: "Nguyen Thanh", phone: "0281234567", email: "contact@autopartsvn.com", address: "123 Nguyen Trai" }],
            warrantyClaims: [{ id: 1, partName: "Brake Pad Set", date: "2026-07-28", type: "Part Replacement", status: "Pending" }]
        };
        renderParts(inventoryData.parts);
        renderSuppliers(inventoryData.suppliers);
        renderWarranty(inventoryData.warrantyClaims);
    }
}

function setupTabs() {
    document.querySelectorAll(".tab-row .tab-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const name = btn.dataset.tab;
            document.querySelectorAll(".tab-panel").forEach(p => p.classList.toggle("active", p.id === `inventory-${name}`));
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.toggle("active", b === btn));
        });
    });
}

function renderParts(parts) {
    renderTableRows("partsTableBody", parts, "No parts found.", part => {
        const row = document.createElement("tr");
        const isLowStock = part.currentStock <= part.reorderLevel;
        const statusPill = createPill(isLowStock ? "Restock Needed" : "In Stock", isLowStock ? "crit" : "ok");

        row.append(
            createCell(part.id),
            createCell(part.name),
            createCell(part.category),
            createCell(part.brand),
            createCell(Number(part.price).toLocaleString('vi-VN') + " ₫"),
            createCell(part.reorderLevel),
            (() => { const td = document.createElement("td"); td.appendChild(statusPill); return td; })()
        );
        return row;
    }, 7);
}

function renderSuppliers(suppliers) {
    renderTableRows("suppliersTableBody", suppliers, "No suppliers found.", supplier => {
        const row = document.createElement("tr");
        row.append(
            createCell(supplier.name),
            createCell(supplier.contact),
            createCell(supplier.phone),
            createCell(supplier.email),
            createCell(supplier.address)
        );
        return row;
    }, 5);
}

function renderWarranty(claims) {
    renderTableRows("warrantyTableBody", claims, "No warranty claims.", claim => {
        const row = document.createElement("tr");
        const statusClass = claim.status === "Approved" ? "ok" : claim.status === "Rejected" ? "crit" : "warn";
        const statusPill = createPill(claim.status, statusClass);
        const actionCell = document.createElement("td");
        
        if (claim.status === "Pending") {
            const approveBtn = document.createElement("button");
            approveBtn.textContent = "Approve";
            approveBtn.className = "link-btn";
            approveBtn.style.color = "var(--success)";
            approveBtn.onclick = () => approveClaim(claim.id);
            actionCell.appendChild(approveBtn);
        } else {
            actionCell.textContent = "—";
        }

        row.append(
            createCell(claim.id), createCell(claim.partName), createCell(claim.date), createCell(claim.type),
            (() => { const td = document.createElement("td"); td.appendChild(statusPill); return td; })(), actionCell
        );
        return row;
    }, 6);
}

function approveClaim(claimId) {
    if(!confirm("Are you sure you want to approve Claim #" + claimId + "?")) return;
    const claim = inventoryData.warrantyClaims.find(c => c.id === claimId);
    if(claim) claim.status = "Approved";
    renderWarranty(inventoryData.warrantyClaims);
    alert("Claim approved (UI simulated).");
}

function setupDrawers() {
    const overlay = document.getElementById("overlay");
    const drawerPart = document.getElementById("drawerPart");
    const drawerSupplier = document.getElementById("drawerSupplier");

    const closeAll = () => {
        if(overlay) overlay.classList.remove("open");
        if(drawerPart) drawerPart.classList.remove("open");
        if(drawerSupplier) drawerSupplier.classList.remove("open");
    };

    if(overlay) overlay.addEventListener("click", closeAll);
    document.getElementById("closePartBtn")?.addEventListener("click", closeAll);
    document.getElementById("closeSupplierBtn")?.addEventListener("click", closeAll);

    // Mở Drawer Part
    document.getElementById("btnAddPart")?.addEventListener("click", () => {
        overlay.classList.add("open");
        drawerPart.classList.add("open");
    });

    // Mở Drawer Supplier
    document.getElementById("btnAddSupplier")?.addEventListener("click", () => {
        overlay.classList.add("open");
        drawerSupplier.classList.add("open");
    });

    document.getElementById("savePartBtn")?.addEventListener("click", async () => {
        const name = document.getElementById("newPartName").value;
        if (!name) return alert("Please enter a part name!"); 
        
        const payload = {
            part_name: name,
            part_category: document.getElementById("newPartCategory").value || null,
            brand: document.getElementById("newPartBrand").value || null,
            unit_price: document.getElementById("newPartPrice").value ? parseFloat(document.getElementById("newPartPrice").value) : null,
            reorder_level: document.getElementById("newPartReorder").value ? parseInt(document.getElementById("newPartReorder").value) : null
        };
        
        try {
            const response = await fetch(PART_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || "Permission denied / Network Error");
            
            alert("Part added successfully!");
            closeAll();
            loadInventoryData(); 
        } catch (error) {
            alert("Error: " + error.message);
        }
    });

    document.getElementById("saveSupplierBtn")?.addEventListener("click", async () => {
        const name = document.getElementById("newSupName").value;
        const phone = document.getElementById("newSupPhone").value;
        if (!name || !phone) return alert("Please enter Supplier Name and Phone!"); 
        
        const payload = {
            supplier_name: name,
            phone_number: phone,
            contact_name: document.getElementById("newSupContact").value || null,
            email_address: document.getElementById("newSupEmail").value || null,
            address: document.getElementById("newSupAddress").value || null,
            delivery_time: document.getElementById("newSupTime").value ? parseInt(document.getElementById("newSupTime").value) : null
        };
        
        try {
            const response = await fetch(SUPPLIER_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || "Permission denied / Network Error");
            
            alert("Supplier added successfully!");
            closeAll();
            loadInventoryData(); 
        } catch (error) {
            alert("Error: " + error.message);
        }
    });
}