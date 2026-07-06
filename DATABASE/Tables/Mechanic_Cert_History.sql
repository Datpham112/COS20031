
DROP TABLE IF EXISTS `mechanic_cert_history`;
CREATE TABLE `mechanic_cert_history` (
  `Cert_ID` int NOT NULL,
  `Mechanic_ID` int DEFAULT NULL,
  `Certificate_Name` varchar(255) DEFAULT NULL,
  `issue_Date` date DEFAULT NULL,
  `Expiry_Date` date DEFAULT NULL,
  PRIMARY KEY (`Cert_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;