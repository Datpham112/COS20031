-- Check_Indexes.sql
-- Run this file to verify all indexes have been created successfully

-- 1. Check Predictive_Alert Indexes
SELECT 'Predictive_Alert' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Predictive_Alert'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 2. Check Safety_Event Indexes
SELECT 'Safety_Event' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Safety_Event'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 3. Check Maintenance_Job Indexes
SELECT 'Maintenance_Job' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Maintenance_Job'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 4. Check Vehicle_Driver_Assignment Indexes
SELECT 'Vehicle_Driver_Assignment' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Vehicle_Driver_Assignment'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 5. Check Vehicle Indexes
SELECT 'Vehicle' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Vehicle'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 6. Check Driver Indexes
SELECT 'Driver' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Driver'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 7. Check Driver_Safety_Score Indexes
SELECT 'Driver_Safety_Score' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Driver_Safety_Score'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 8. Check Maintenance_Activity Indexes
SELECT 'Maintenance_Activity' AS Table_Name, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Maintenance_Activity'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 9. Summary: List all newly created indexes
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() 
  AND INDEX_NAME IN (
    'idx_predictive_vin_raised', 'idx_predictive_depot',
    'idx_safety_vin_depot_ts',
    'idx_job_vin', 'idx_job_linked_alert',
    'idx_vda_vin',
    'idx_vehicle_depot',
    'idx_driver_depot',
    'idx_score_driver_month_year',
    'idx_activity_job'
  )
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- 10. Check index size and statistics
SELECT TABLE_NAME, INDEX_NAME, STAT_NAME, STAT_VALUE
FROM INFORMATION_SCHEMA.INNODB_STAT_DATA
WHERE TABLE_SCHEMA = DATABASE()
  AND INDEX_NAME IN (
    'idx_predictive_vin_raised', 'idx_predictive_depot',
    'idx_safety_vin_depot_ts',
    'idx_job_vin', 'idx_job_linked_alert',
    'idx_vda_vin',
    'idx_vehicle_depot',
    'idx_driver_depot',
    'idx_score_driver_month_year',
    'idx_activity_job'
  )\G
