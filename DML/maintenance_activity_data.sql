INSERT INTO Maintenance_Activity
(
    Job_ID,
    Activity_Type,
    Diagnostic_Result,
    Repeat_Fault_Indicator,
    Warranty_Indicator
)
VALUES
(1, 'Brake Inspection', 'Front brake pads worn, replaced with new set', FALSE, FALSE),
(2, 'Tyre Replacement', 'Rear tyre pressure sensor faulty, replacement scheduled', FALSE, FALSE),
(3, 'Battery Replacement', 'Battery degraded below 70 percent capacity, replaced under warranty', TRUE, TRUE),
(4, 'Cooling System Repair', 'Radiator leak found and repaired', FALSE, FALSE),
(5, 'General Inspection', 'Routine service inspection, no issues found', FALSE, FALSE);
