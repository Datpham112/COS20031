INSERT INTO Safety_Event
(
    Event_ID,
    Driver_ID,
    VIN,
    Depot_ID,
    Timestamp,
    Event_Type,
    Severity_Level,
    Odometer_At_Event,
    Review_Comments
)
VALUES
(1, 'D-112', 'VIN00000000000001', 1, '2026-01-14 08:15:00', 'Hard Braking', 'Medium', 45218.40, 'Driver braked sharply while entering the depot gate.'),
(2, 'D-204', 'VIN00000000000002', 1, '2026-01-18 16:42:00', 'Speeding', 'High', 120884.70, 'Vehicle exceeded the speed limit on a suburban route.'),
(3, 'D-331', 'VIN00000000000003', 2, '2026-02-03 11:05:00', 'Lane Departure', 'Low', 12412.90, 'Brief lane drift detected during heavy traffic conditions.'),
(4, 'D-417', 'VIN00000000000004', 3, '2026-02-11 19:27:00', 'Sharp Cornering', 'Medium', 112942.10, 'Cornering speed was higher than expected for road conditions.'),
(5, 'D-302', 'VIN00000000000005', 4, '2026-02-19 07:50:00', 'Sudden Acceleration', 'High', 5488.60, 'Rapid acceleration observed during morning departure from depot.');