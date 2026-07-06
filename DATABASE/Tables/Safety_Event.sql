
DROP TABLE IF EXISTS `safety_event`;
CREATE TABLE `safety_event` (
  `Event_ID` int NOT NULL,
  `Driver_ID` int DEFAULT NULL,
  `VIN` varchar(17) DEFAULT NULL,
  `Depot_ID` int DEFAULT NULL,
  `Timestamp` datetime DEFAULT NULL,
  `Event_Type` varchar(50) DEFAULT NULL,
  `Severity_Level` varchar(20) DEFAULT NULL,
  `Odometer_At_Event` decimal(10,2) DEFAULT NULL,
  `Review_Comments` text,
  PRIMARY KEY (`Event_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;