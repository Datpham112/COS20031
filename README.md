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

## 1. How to Run the Project (Installation Guide)

To successfully set up and run the SwinTech system on your local machine, please carefully follow these steps:

1. Install the XAMPP control panel on your computer.
2. Open XAMPP and start both the Apache and MySQL modules.
3. Extract the submitted project file (`COS20031.zip`) and copy the extracted `COS20031` folder directly into your XAMPP `htdocs` directory (typically located at `C:\xampp\htdocs\COS20031`).
4. Open your web browser and navigate to the MySQL database management interface using this URL: `http://localhost/phpmyadmin/`.
5. Click on "New" to create a new, empty database and name it `fleet_management`.
6. Import the SQL database files into your newly created database.
7. Once the database is fully set up, open a new tab in your browser and navigate to the system's login page using this URL:
   `http://localhost/COS20031/Frontend/login.html`
8. Log in using one of the testing accounts provided in the Login Guide section below to begin evaluating the system.

## How to Test the System (Role-Based Scenarios)

To verify that the frontend UI, backend database, and Role-Based Access Control (RBAC) are functioning perfectly, please try the following testing scenarios using the provided accounts:

**Scenario 1: Global Access and Oversight (Head Manager)**
Step 1: Please access the login page and use the account `head.manager` with the password `Password123` to enter the system.
Step 2: Navigate to the Manage Data page. You will see that you have full unrestricted access to view, create, edit, and delete records across all four depots (Ha Noi, Da Nang, Ho Chi Minh, Can Tho).
Step 3: Check the system views. The application allows you to monitor company-wide statistics, including all vehicles, drivers, inventory, and maintenance records without any regional limitations.

**Scenario 2: Workshop Maintenance Workflow (Workshop Manager)**
Step 1: Log out and log back in using the account `workshop.manager.hn` with the password `Password123`.
Step 2: Navigate to the Manage Data page and select the Maintenance Job tab. Fill in the form to create a new job by selecting an existing VIN, Workshop ID, and setting the Date Opened. Click Create to insert it into the database.
Step 3: Navigate to the Workshop Hub page from the left sidebar and click the Jobs tab. You will see the new job you just created at the top of the list with a yellow Open status pill.
Step 4: Click on the row of your newly created Job. A side-drawer will slide out from the right, fetching live data via the API. Click the blue Mark as Closed button inside the drawer. The table will refresh, and your job's status will instantly change to a grey Closed pill.

**Scenario 3: Data Isolation Test (Depot Managers)**
Step 1: Log out and log in using the account `depot.manager.hn` with the password `Password123`.
Step 2: Navigate to the Manage Data page. You will notice that the UI adapts to your role. You only have access to manage Vehicles, Drivers, and Staff specifically for Depot 1 (Ha Noi). 
Step 3: To verify data privacy across branches, log out and log in as `depot.manager.dn` (Depot 2), `depot.manager.hcm` (Depot 3), or `depot.manager.ct` (Depot 4). Check the data tables again. You will not be able to see, edit, or interact with the data belonging to other depots.

**Scenario 4: Driver and Safety Management (Driver Manager)**
Step 1: Log in using the account `driver.manager.dn` with the password `Password123`.
Step 2: Navigate through the system. You will observe that your permissions are tailored to personnel management. You can access driver information, manage vehicle-driver assignments, and review safety records for your specific depot.
Step 3: Try to access the Workshop Hub or Inventory sections. The system will restrict your access, ensuring you can only interact with driver-related data.

**Scenario 5: Parts and Stock Control (Inventory Manager)**
Step 1: Log in using the account `inventory.manager` with the password `Password123`.
Step 2: Navigate to the Manage Data page. Your view is strictly limited to the inventory module.
Step 3: Verify that you can seamlessly manage parts inventory, view stock levels, and update supplier information across the company, while having zero access to vehicle assignments or maintenance job creations.

**Scenario 6: Restricted Personal Access (Driver and Mechanic)**
Step 1: Log out and test the operational staff accounts by logging in as `mechanic.test` with the password `mechanic123`, or `driver.test` with the password `driver123`.
Step 2: Observe the left navigation sidebar. Administrative tabs like Manage Data or Manager Console are completely hidden from the user interface.
Step 3: Navigate the available pages. The system strictly restricts these roles to a read-only personal view. The mechanic can only view their assigned maintenance jobs, and the driver can only view their personal vehicle assignment and current safety score.