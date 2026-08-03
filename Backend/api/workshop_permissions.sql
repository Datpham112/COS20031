-- 1. Create the workshop manager role
CREATE ROLE 'workshop_manager';

-- 2. Grant necessary privileges to the role
GRANT SELECT, INSERT, UPDATE ON Maintenance_Job TO 'workshop_manager'; 
GRANT SELECT, INSERT, UPDATE ON Maintenance_Activity TO 'workshop_manager'; 
GRANT SELECT, UPDATE ON Mechanic TO 'workshop_manager'; 
GRANT SELECT, INSERT, UPDATE ON Activity_Mechanic_Assignment TO 'workshop_manager'; 
GRANT SELECT, UPDATE ON Predictive_Alert TO 'workshop_manager';

-- 3. Create login accounts for the 4 depot managers
CREATE USER 'nhu_depot1'@'localhost' IDENTIFIED BY 'StrongPassword123!';
CREATE USER 'manager_depot2'@'localhost' IDENTIFIED BY 'StrongPassword123!';
CREATE USER 'manager_depot3'@'localhost' IDENTIFIED BY 'StrongPassword123!';
CREATE USER 'manager_depot4'@'localhost' IDENTIFIED BY 'StrongPassword123!';

-- 4. Assign the role to all workshop manager accounts
GRANT 'workshop_manager' TO 'nhu_depot1'@'localhost', 
                            'manager_depot2'@'localhost', 
                            'manager_depot3'@'localhost', 
                            'manager_depot4'@'localhost';

-- 5. Flush privileges to apply changes immediately
FLUSH PRIVILEGES;