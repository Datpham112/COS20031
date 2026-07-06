-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: assignment
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_part`
--

DROP TABLE IF EXISTS `activity_part`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_part`
--

LOCK TABLES `activity_part` WRITE;
/*!40000 ALTER TABLE `activity_part` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_part` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mechanic_cert_history`
--

DROP TABLE IF EXISTS `mechanic_cert_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mechanic_cert_history` (
  `Cert_ID` int NOT NULL,
  `Mechanic_ID` int DEFAULT NULL,
  `Certificate_Name` varchar(255) DEFAULT NULL,
  `issue_Date` date DEFAULT NULL,
  `Expiry_Date` date DEFAULT NULL,
  PRIMARY KEY (`Cert_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mechanic_cert_history`
--

LOCK TABLES `mechanic_cert_history` WRITE;
/*!40000 ALTER TABLE `mechanic_cert_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `mechanic_cert_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `part`
--

DROP TABLE IF EXISTS `part`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part`
--

LOCK TABLES `part` WRITE;
/*!40000 ALTER TABLE `part` DISABLE KEYS */;
/*!40000 ALTER TABLE `part` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `part_supplier`
--

DROP TABLE IF EXISTS `part_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_supplier`
--

LOCK TABLES `part_supplier` WRITE;
/*!40000 ALTER TABLE `part_supplier` DISABLE KEYS */;
/*!40000 ALTER TABLE `part_supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safety_event`
--

DROP TABLE IF EXISTS `safety_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safety_event`
--

LOCK TABLES `safety_event` WRITE;
/*!40000 ALTER TABLE `safety_event` DISABLE KEYS */;
INSERT INTO `safety_event` VALUES (1,101,'1HGCM82633A004352',1,'2026-01-05 08:30:00','Hard Braking','Medium',15320.50,'Driver braked suddenly due to pedestrian crossing.'),(2,102,'2FMDK3GC4BBA12345',2,'2026-02-10 14:15:00','Speeding','High',22890.75,'Exceeded speed limit by 20km/h on highway.'),(3,103,'3VWFE21C04M000123',1,'2026-03-01 09:45:00','Harsh Acceleration','Low',8790.00,'Minor acceleration event, no risk detected.'),(4,104,'1FTSW21P34ED12345',3,'2026-03-15 17:20:00','Collision','Critical',30250.20,'Rear-end collision at low speed in parking lot.'),(5,101,'1HGCM82633A004352',1,'2026-04-02 11:10:00','Lane Departure','Medium',15890.00,'Vehicle drifted out of lane briefly.');
/*!40000 ALTER TABLE `safety_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warranty_claims`
--

DROP TABLE IF EXISTS `warranty_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warranty_claims`
--

LOCK TABLES `warranty_claims` WRITE;
/*!40000 ALTER TABLE `warranty_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `warranty_claims` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-06 11:04:44
