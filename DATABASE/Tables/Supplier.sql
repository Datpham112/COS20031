
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `Supplier_ID` int NOT NULL AUTO_INCREMENT,
  `Supplier_Name` varchar(150) NOT NULL,
  `Contact_Name` varchar(100) DEFAULT NULL,
  `Phone_Number` varchar(15) NOT NULL,
  `Email_Address` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Delivery_Time` datetime DEFAULT NULL,
  PRIMARY KEY (`Supplier_ID`),
  UNIQUE KEY `Phone_Number` (`Phone_Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;