
DROP TABLE IF EXISTS `part_supplier`;
CREATE TABLE `part_supplier` (
  `Part_ID` int NOT NULL,
  `Supplier_ID` int NOT NULL,
  `Supplier_Type` varchar(50) DEFAULT NULL,
  `Unit_Cost` decimal(10,2) DEFAULT NULL,
  `Lead_Time_Days` int DEFAULT NULL,
  PRIMARY KEY (`Part_ID`,`Supplier_ID`),
  KEY `Supplier_ID` (`Supplier_ID`),
  CONSTRAINT `part_supplier_ibfk_1` FOREIGN KEY (`Part_ID`) REFERENCES `part` (`Part_ID`),
  CONSTRAINT `part_supplier_ibfk_2` FOREIGN KEY (`Supplier_ID`) REFERENCES `supplier` (`Supplier_ID`),
  CONSTRAINT `part_supplier_chk_1` CHECK ((`Unit_Cost` >= 0)),
  CONSTRAINT `part_supplier_chk_2` CHECK ((`Lead_Time_Days` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;