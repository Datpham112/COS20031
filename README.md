# 🚚 Fleet Management Database System

## Login Guide

When you open the Fleet Management Database System, the first page displayed is the **Login Page**.

Use one of the following accounts to access the system based on the user role.

| Username | Password | Role | Description |
|----------|----------|------|-------------|
| **head.manager** | Password123 | Head Manager | Company-wide, read-only access. Can view data from all four depots, including vehicles, drivers, and maintenance records. |
| **depot.manager.hn** | Password123 | Depot Manager (Depot 1) | Manages vehicles and drivers assigned to Depot 1. |
| **depot.manager.dn** | Password123 | Depot Manager (Depot 2) | Manages vehicles and drivers assigned to Depot 2. |
| **depot.manager.hcm** | Password123 | Depot Manager (Depot 3) | Manages vehicles and drivers assigned to Depot 3. |
| **depot.manager.ct** | Password123 | Depot Manager (Depot 4) | Manages vehicles and drivers assigned to Depot 4. |
| **workshop.manager.hn** | Password123 | Workshop Manager (Depot 1) | Manages mechanics, maintenance jobs, and workshop operations. Can create, edit, and delete records in the **Manage Data** page. |
| **driver.manager.dn** | Password123 | Driver Manager (Depot 2) | Manages driver information, assignments, and related records. Can create, edit, and delete records in the **Manage Data** page. |
| **inventory.manager** | Password123 | Inventory Manager | Company-wide management of parts inventory and stock information. |
| **mechanic.test** | mechanic123 | Mechanic | Allows mechanics to view their assigned maintenance jobs and personal work information. |
| **driver.test** | driver123 | Driver | Allows drivers to view their personal information, assigned vehicle, and current safety score. |

---

## Notes

- User permissions are restricted based on their assigned role.
- Managers have access only to the functions relevant to their responsibilities.
- Drivers and mechanics have read-only access to their own information.


## How to Run the Project

1. Install XAMPP.
2. Start Apache and MySQL.
3. Import the SQL database into phpMyAdmin.
4. Copy the project folder into the `htdocs` directory.
5. Open your browser and navigate to:

http://localhost/Fleet_Management/

6. Log in using one of the accounts listed above.