INSERT INTO Predictive_Alert
(
  VIN,
  Depot_ID,
  Alert_Type,
  Action_Taken
)
VALUES
('VIN00000000000001', 1, 'Brake Wear', 'Scheduled Repair'),
('VIN00000000000002', 1, 'Tyre Pressure', 'Acknowledged'),
('VIN00000000000003', 2, 'Battery Degradation', 'Emergency Repair'),
('VIN00000000000004', 3, 'Overheating Risk', 'Resolved'),
('VIN00000000000005', 4, 'Engine Fault', 'Acknowledged');

ALTER TABLE Predictive_Alert DROP CHECK chk_alert_type;

UPDATE Predictive_Alert
SET Alert_Type = 'Oil Quality Deterioration'
WHERE Alert_Type = 'Engine Fault';

ALTER TABLE Predictive_Alert
    ADD CONSTRAINT chk_alert_type
        CHECK (
            Alert_Type IN (
                'Brake Wear',
                'Overheating Risk',
                'Battery Degradation',
                'Oil Quality Deterioration',
                'Transmission Fault',
                'Cooling System Anomaly',
                'Tyre Pressure'
            )
        );
