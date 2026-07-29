CREATE ROLE 'head_manager', 'depot_manager', 'workshop_manager', 
            'driver_manager', 'inventory_manager', 'mechanic_role', 'driver_role';
-- [Head Manager]
GRANT ALL PRIVILEGES ON *.* TO 'head_manager';

-- [Depot Manager]
GRANT SELECT, INSERT, UPDATE ON Vehicle TO 'depot_manager';
GRANT SELECT, INSERT, UPDATE ON Driver TO 'depot_manager';
GRANT SELECT ON Maintenance_Job TO 'depot_manager';
GRANT SELECT ON Safety_Event TO 'depot_manager';

-- [Workshop Manager]
GRANT SELECT, INSERT, UPDATE ON Maintenance_Job TO 'workshop_manager';
GRANT SELECT, INSERT, UPDATE ON Maintenance_Activity TO 'workshop_manager';
GRANT SELECT, INSERT, UPDATE ON Mechanic TO 'workshop_manager';
GRANT SELECT, INSERT, UPDATE ON Activity_Mechanic_Assignment TO 'workshop_manager';
GRANT SELECT, UPDATE ON Predictive_Alert TO 'workshop_manager'; 

-- [Driver Manager]
GRANT SELECT, INSERT, UPDATE ON Driver TO 'driver_manager';
GRANT SELECT, INSERT, UPDATE ON Driver_Certification TO 'driver_manager';
GRANT SELECT, INSERT, UPDATE ON Vehicle_Driver_Assignment TO 'driver_manager';
GRANT SELECT, INSERT, UPDATE ON Driver_Safety_Score TO 'driver_manager';
GRANT SELECT, UPDATE ON Safety_Event TO 'driver_manager';

-- [Inventory Manager]
GRANT SELECT, INSERT, UPDATE, DELETE ON Part TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE, DELETE ON Supplier TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE, DELETE ON Part_Supplier TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE ON Activity_Part TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE ON Warranty_Claims TO 'inventory_manager';

-- [Mechanic Role]
GRANT SELECT ON Maintenance_Job TO 'mechanic_role';
GRANT SELECT, UPDATE ON Maintenance_Activity TO 'mechanic_role';
GRANT SELECT, INSERT ON Activity_Part TO 'mechanic_role';

-- [Driver Role]
GRANT SELECT ON Driver TO 'driver_role';
GRANT SELECT ON Driver_Safety_Score TO 'driver_role';
GRANT SELECT ON Vehicle_Driver_Assignment TO 'driver_role';

FLUSH PRIVILEGES;
