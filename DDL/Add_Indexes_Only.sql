-- Add_Indexes_Only.sql
-- This file contains only CREATE INDEX statements for existing tables
-- Use this if your database already has tables and you just want to add the new indexes

-- Predictive_Alert Indexes
CREATE INDEX idx_predictive_vin_raised ON Predictive_Alert(VIN, Raised_At);
CREATE INDEX idx_predictive_depot ON Predictive_Alert(Depot_ID);

-- Safety_Event Index
CREATE INDEX idx_safety_vin_depot_ts ON Safety_Event(VIN, Depot_ID, Timestamp);

-- Maintenance_Job Indexes
CREATE INDEX idx_job_vin ON Maintenance_Job(VIN);
CREATE INDEX idx_job_linked_alert ON Maintenance_Job(Linked_Alert_ID);

-- Vehicle_Driver_Assignment Index
CREATE INDEX idx_vda_vin ON Vehicle_Driver_Assignment(VIN);

-- Vehicle Index
CREATE INDEX idx_vehicle_depot ON Vehicle(Depot_ID);

-- Driver Index
CREATE INDEX idx_driver_depot ON Driver(Depot_ID);

-- Driver_Safety_Score Index
CREATE INDEX idx_score_driver_month_year ON Driver_Safety_Score(Driver_ID, Year, Month);

-- Maintenance_Activity Index
CREATE INDEX idx_activity_job ON Maintenance_Activity(Job_ID);
