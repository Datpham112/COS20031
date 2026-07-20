INSERT INTO Maintenance_Job
(
    VIN,
    Workshop_ID,
    Linked_Alert_ID,
    Date_Opened,
    Date_Closed,
    Downtime_Hours,
    Total_Cost,
    Priority
)
VALUES
('VIN00000000000001', 1, 1, '2026-02-01', '2026-02-03', 8.50, 1500000.00, 'Moderate'),
('VIN00000000000002', 1, 2, '2026-03-05', NULL, NULL, NULL, 'Low'),
('VIN00000000000003', 2, 3, '2026-01-20', '2026-01-25', 40.00, 8500000.00, 'High'),
('VIN00000000000004', 3, 4, '2025-12-10', '2025-12-15', 30.00, 6200000.00, 'High'),
('VIN00000000000005', 4, NULL, '2026-04-01', '2026-04-02', 5.00, 500000.00, 'Low');
