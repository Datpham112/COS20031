
DROP TABLE IF EXISTS `warranty_claims`;
CREATE TABLE `warranty_claims` (
  `Claim_ID` int NOT NULL AUTO_INCREMENT,
  `Activity_ID` int DEFAULT NULL,
  `Part_ID` int DEFAULT NULL,
  `Claim_Status` varchar(50) DEFAULT NULL,
  `Claim_Date` date NOT NULL,
  `Claim_Type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Claim_ID`),
  KEY `Part_ID` (`Part_ID`),
  CONSTRAINT `warranty_claims_ibfk_1` FOREIGN KEY (`Part_ID`) REFERENCES `part` (`Part_ID`),
  CONSTRAINT `warranty_claims_chk_1` CHECK ((`Claim_Status` in (_utf8mb4'Pending',_utf8mb4'Approved',_utf8mb4'Rejected')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;