-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: petvoyage
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.27-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `branch`
--

DROP TABLE IF EXISTS `branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branch` (
  `Id` int(11) NOT NULL,
  `Name` varchar(45) NOT NULL,
  `Address` varchar(200) NOT NULL,
  `PhoneNumber` varchar(45) NOT NULL,
  `CityId` smallint(5) unsigned NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_Branch_City1_idx` (`CityId`),
  KEY `fk_Branch_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_Branch_City1` FOREIGN KEY (`CityId`) REFERENCES `city` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Branch_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch`
--

LOCK TABLES `branch` WRITE;
/*!40000 ALTER TABLE `branch` DISABLE KEYS */;
INSERT INTO `branch` VALUES (1,'Pet Voyage I','Psr Jatinegara Bl BKS/30','087844508972',6,'2024-11-07 07:08:07',NULL),(2,'Pet Voyage II','Jl Gn Sawo 2','081267863542',5,'2024-11-07 07:08:07',NULL),(3,'Pet Voyage III','Psr Turi Baru Street H/91-92','087846275912',1,'2024-11-07 07:08:07',NULL),(4,'Pet Voyage IV','Jl Guru Sinumba 2','087856281542',2,'2024-11-07 07:08:07',NULL),(5,'Pet Voyage V','Jl Gedung Baru','087686471564',3,'2024-11-07 07:08:07',NULL),(6,'Pet Voyage VI','Jl Kapt Muslim Halvetia','081256486235',4,'2024-11-07 07:08:07',NULL);
/*!40000 ALTER TABLE `branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cage`
--

DROP TABLE IF EXISTS `cage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cage` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  `Dimensions` varchar(45) NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_Cage_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_Cage_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cage`
--

LOCK TABLES `cage` WRITE;
/*!40000 ALTER TABLE `cage` DISABLE KEYS */;
/*!40000 ALTER TABLE `cage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `city`
--

DROP TABLE IF EXISTS `city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `city` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  `Province` varchar(255) NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_City_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_City_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city`
--

LOCK TABLES `city` WRITE;
/*!40000 ALTER TABLE `city` DISABLE KEYS */;
INSERT INTO `city` VALUES (1,'Surabaya','Jawa Timur','2024-11-13 12:02:13','1'),(2,'Malang','Jawa Timur','2024-11-07 07:04:42',NULL),(3,'Jember','Jawa Timur','2024-11-07 07:04:42',NULL),(4,'Madiun','Jawa Timur','2024-11-13 12:16:40','8'),(5,'Jogja','Jawa Tengah','2024-11-07 07:04:42',NULL),(6,'Jakarta','DKI Jakarta','2024-11-13 12:03:55','1');
/*!40000 ALTER TABLE `city` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client` (
  `Id` int(11) NOT NULL,
  `Name` varchar(45) NOT NULL,
  `Address` varchar(200) NOT NULL,
  `PhoneNumber` varchar(20) NOT NULL,
  `CityId` smallint(5) unsigned NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_Client_City1_idx` (`CityId`),
  KEY `fk_Client_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_Client_City1` FOREIGN KEY (`CityId`) REFERENCES `city` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Client_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES (1,'John Doe','123 Maple St','1234567890',3,'2024-11-14 10:02:35',NULL),(2,'Jane Smith','456 Oak Ave','2345678901',5,'2024-11-14 10:02:35',NULL),(3,'Alice Johnson','789 Pine Dr','3456789012',2,'2024-11-14 10:02:35',NULL),(4,'Bob Williams','101 Cedar Ln','4567890123',4,'2024-11-14 10:02:35',NULL),(5,'Charlie Brown','202 Birch Rd','5678901234',1,'2024-11-14 10:14:01','7'),(6,'Emily Davis','303 Elm St','6789012345',6,'2024-11-14 10:02:35',NULL),(7,'Frank Thomas','404 Maple Ave','7890123456',2,'2024-11-14 10:02:35',NULL),(8,'Grace Lee','505 Oak St','8901234567',3,'2024-11-14 10:02:35',NULL),(9,'Henry Wilson','606 Pine Ave','9012345678',5,'2024-11-14 10:02:35',NULL),(10,'Ivy Martinez','707 Cedar Blvd','0123456789',4,'2024-11-14 10:02:35',NULL),(11,'Jack Harris','808 Birch St','1234567809',1,'2024-11-14 10:02:35',NULL),(12,'Kelly Clark','909 Elm Blvd','2345678012',6,'2024-11-14 10:02:35',NULL),(13,'Liam Walker','1010 Maple Dr','3456789123',3,'2024-11-14 10:02:35',NULL),(14,'Mia Lewis','1111 Oak Ave','4567890234',2,'2024-11-14 10:02:35',NULL),(15,'Noah King','1212 Pine St','5678901345',4,'2024-11-14 10:02:35',NULL),(16,'Olivia Scott','1313 Cedar Rd','6789012456',5,'2024-11-14 10:02:35',NULL),(17,'Paul Young','1414 Birch Blvd','7890123567',6,'2024-11-14 10:02:35',NULL),(18,'Quinn Hall','1515 Elm Dr','8901234678',1,'2024-11-14 10:02:35',NULL),(19,'Rachel Allen','1616 Maple Ln','9012345789',4,'2024-11-14 10:02:35',NULL),(20,'Sam Wright','1717 Oak Blvd','0123456890',3,'2024-11-14 10:02:35',NULL);
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `Id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Name` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `BranchId` int(11) NOT NULL,
  `Username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Password` varchar(256) NOT NULL,
  `Role` varchar(45) NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_Staff_Branch1_idx` (`BranchId`),
  KEY `fk_Staff_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_Staff_Branch1` FOREIGN KEY (`BranchId`) REFERENCES `branch` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Staff_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_esperanto_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES ('1','Devon Larrat',5,'devlarrat','07f60032ed0d5b4db9c524ffdaac2ded9989e94bcc30db5828096e4678da90ef','Staff','2024-11-13 11:17:15','8'),('2','Denis Cyplenkov',2,'cyplenkov','07f60032ed0d5b4db9c524ffdaac2ded9989e94bcc30db5828096e4678da90ef','Staff','2024-11-07 07:18:00',NULL),('4','Justin Bieber',3,'bieberz','07f60032ed0d5b4db9c524ffdaac2ded9989e94bcc30db5828096e4678da90ef','Staff','2024-11-07 07:18:00','4'),('5','Christiano Ronaldo',4,'ronaldo','07f60032ed0d5b4db9c524ffdaac2ded9989e94bcc30db5828096e4678da90ef','Staff','2024-11-10 06:57:08','1'),('6','Febriona Mendoza',1,'mendoza','07f60032ed0d5b4db9c524ffdaac2ded9989e94bcc30db5828096e4678da90ef','Staff','2024-11-07 07:18:00',NULL),('7','Steve Rogers',2,'steve','07f60032ed0d5b4db9c524ffdaac2ded9989e94bcc30db5828096e4678da90ef','Staff','2024-11-07 07:18:00',NULL),('8','Marcella Tanjung',6,'cella','a6b9abdf1653539e73ec7e45aeeeb11739f1677d97b4c4c65f0f070ecc1fe491','Administrator','2024-11-13 12:15:42','8');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction` (
  `BranchId` int(11) NOT NULL,
  `Id` int(11) NOT NULL,
  `TransactionDateDate` date NOT NULL,
  `ClientId` int(11) NOT NULL,
  `CreatedBy` varchar(50) NOT NULL,
  `DestinationAddress` varchar(200) NOT NULL,
  `DestinationCity` smallint(5) unsigned NOT NULL,
  `ExpectedArrival` date NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`BranchId`,`Id`),
  KEY `fk_Transaction_Branch1_idx` (`BranchId`),
  KEY `fk_Transaction_Client1_idx` (`ClientId`),
  KEY `fk_Transaction_Staff1_idx` (`CreatedBy`),
  KEY `fk_Transaction_City1_idx` (`DestinationCity`),
  KEY `fk_Transaction_Staff2_idx` (`LastModifier`),
  CONSTRAINT `fk_Transaction_Branch1` FOREIGN KEY (`BranchId`) REFERENCES `branch` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Transaction_City1` FOREIGN KEY (`DestinationCity`) REFERENCES `city` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Transaction_Client1` FOREIGN KEY (`ClientId`) REFERENCES `client` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Transaction_Staff1` FOREIGN KEY (`CreatedBy`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Transaction_Staff2` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction`
--

LOCK TABLES `transaction` WRITE;
/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactiondetail`
--

DROP TABLE IF EXISTS `transactiondetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactiondetail` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BranchId` int(11) NOT NULL,
  `TransactionId` int(11) NOT NULL,
  `CageId` smallint(5) unsigned DEFAULT NULL,
  `Description` varchar(100) NOT NULL,
  `Price` int(11) NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_TransactionDetail_Transaction1_idx` (`BranchId`,`TransactionId`),
  KEY `fk_TransactionDetail_Cage1_idx` (`CageId`),
  KEY `fk_TransactionDetail_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_TransactionDetail_Cage1` FOREIGN KEY (`CageId`) REFERENCES `cage` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TransactionDetail_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TransactionDetail_Transaction1` FOREIGN KEY (`BranchId`, `TransactionId`) REFERENCES `transaction` (`BranchId`, `Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactiondetail`
--

LOCK TABLES `transactiondetail` WRITE;
/*!40000 ALTER TABLE `transactiondetail` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactiondetail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transportrate`
--

DROP TABLE IF EXISTS `transportrate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transportrate` (
  `CityOrigin` smallint(5) unsigned NOT NULL,
  `CityDestination` smallint(5) unsigned NOT NULL,
  `CageId` smallint(5) unsigned NOT NULL,
  `Rate` int(10) unsigned NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastModifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`CityOrigin`,`CityDestination`,`CageId`),
  KEY `fk_ServiceType_has_City_City1_idx` (`CityOrigin`),
  KEY `fk_TransportRate_City1_idx` (`CityDestination`),
  KEY `fk_TransportRate_Cage1_idx` (`CageId`),
  KEY `fk_TransportRate_Staff1_idx` (`LastModifier`),
  CONSTRAINT `fk_ServiceType_has_City_City1` FOREIGN KEY (`CityOrigin`) REFERENCES `city` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TransportRate_Cage1` FOREIGN KEY (`CageId`) REFERENCES `cage` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TransportRate_City1` FOREIGN KEY (`CityDestination`) REFERENCES `city` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TransportRate_Staff1` FOREIGN KEY (`LastModifier`) REFERENCES `staff` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transportrate`
--

LOCK TABLES `transportrate` WRITE;
/*!40000 ALTER TABLE `transportrate` DISABLE KEYS */;
/*!40000 ALTER TABLE `transportrate` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-14 17:16:16
