/**
 * inventory.js
 * Connects frontend inventory.html to backend API
 */

const API_URL = "../Backend/api/inventory.php";

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
});

async function loadInventoryData() {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error("HTTP " + response.status);
        const data = await response.json();
        
        if (data.error) throw new Error(data.detail || data.error);
        
        inventoryData = data;
        renderParts(inventoryData.parts);
        renderSuppliers(inventoryData.suppliers);
        renderWarranty(inventoryData.warrantyClaims);

    } catch (error) {
        console.error("Failed to load inventory data:", error);
        // Fallback: Test Data 
        inventoryData = {
            parts: [
                { id: 1, name: "Brake Pad Set", category: "Brake System", brand: "Bosch", price: "450000", reorderLevel: 10, currentStock: 5 },
                { id: 2, name: "EV Battery Module", category: "Battery", brand: "BYD", price: "25000000", reorderLevel: 2, currentStock: 4 }
            ],
            suppliers: [
                { name: "AutoParts Vietnam", contact: "Nguyen Thanh", phone: "0281234567", email: "contact@autopartsvn.com", address: "123 Nguyen Trai" }
            ],
            warrantyClaims: [
                { id: 1, partName: "Brake Pad Set", date: "2026-07-28", type: "Part Replacement", status: "Pending" },
                { id: 2, partName: "EV Battery Module", date: "2026-07-29", type: "Battery Warranty", status: "Approved" }
            ]
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
            createCell(claim.id),
            createCell(claim.partName),
            createCell(claim.date),
            createCell(claim.type),
            (() => { const td = document.createElement("td"); td.appendChild(statusPill); return td; })(),
            actionCell
        );
        return row;
    }, 6);
}

async function approveClaim(claimId) {
    if(!confirm("Are you sure you want to approve Claim #" + claimId + "?")) return;
    
    try {
        const response = await fetch(API_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "approve_warranty", claimId: claimId })
        });
        
        const result = await response.json();
        if (!response.ok || result.error) throw new Error(result.error);
        
        alert("Claim approved successfully!");
        loadInventoryData(); 
    } catch (error) {
        console.error(error);
        // Fallback for UI Testing
        const claim = inventoryData.warrantyClaims.find(c => c.id === claimId);
        if(claim) claim.status = "Approved";
        renderWarranty(inventoryData.warrantyClaims);
        alert("Claim approved (UI simulated).");
    }
}