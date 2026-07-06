
DROP TABLE IF EXISTS `part`;
CREATE TABLE `part` (
  `Part_ID` int NOT NULL AUTO_INCREMENT,
  `Part_Name` varchar(100) NOT NULL,
  `Part_Category` varchar(50) DEFAULT NULL,
  `Brand` varchar(50) DEFAULT NULL,
  `Unit_Price` decimal(10,2) DEFAULT NULL,
  `Reorder_Level` int DEFAULT NULL,
  PRIMARY KEY (`Part_ID`),
  CONSTRAINT `part_chk_1` CHECK ((`Unit_Price` >= 0)),
  CONSTRAINT `part_chk_2` CHECK ((`Reorder_Level` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;