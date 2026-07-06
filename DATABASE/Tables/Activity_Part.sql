
DROP TABLE IF EXISTS `activity_part`;
CREATE TABLE `activity_part` (
  `Activity_ID` int NOT NULL,
  `Part_ID` int NOT NULL,
  `Quantity_Used` int DEFAULT NULL,
  `Unit_Cost` decimal(10,2) DEFAULT NULL,
  `Total_Cost` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`Activity_ID`,`Part_ID`),
  KEY `Part_ID` (`Part_ID`),
  CONSTRAINT `activity_part_ibfk_1` FOREIGN KEY (`Part_ID`) REFERENCES `part` (`Part_ID`),
  CONSTRAINT `activity_part_chk_1` CHECK ((`Quantity_Used` > 0)),
  CONSTRAINT `activity_part_chk_2` CHECK ((`Unit_Cost` >= 0)),
  CONSTRAINT `activity_part_chk_3` CHECK ((`Total_Cost` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;