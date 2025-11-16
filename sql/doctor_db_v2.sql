-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-11-11 08:45:14
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `doctor_db_v2`
--

-- --------------------------------------------------------

--
-- 資料表結構 `acupuncturepoints`
--

CREATE TABLE `AcupuncturePoints` (
  `Id` int(11) NOT NULL,
  `Name` longtext DEFAULT NULL,
  `BodyPart` longtext DEFAULT NULL,
  `Function` longtext DEFAULT NULL,
  `Harm` longtext DEFAULT NULL,
  `CoordX` float NOT NULL,
  `CoordY` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Appointments`
--

CREATE TABLE `Appointments` (
  `Id` int(11) NOT NULL,
  `PatientId` int(11) NOT NULL,
  `DoctorId` int(11) NOT NULL,
  `AppointmentTime` datetime(6) NOT NULL,
  `Status` longtext DEFAULT NULL,
  `Notes` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `Appointments`
--

INSERT INTO `Appointments` (`Id`, `PatientId`, `DoctorId`, `AppointmentTime`, `Status`, `Notes`) VALUES
(1, 3, 4, '2025-11-12 05:00:00.000000', NULL, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `DoctorAvailabilities`
--

CREATE TABLE `DoctorAvailabilities` (
  `Id` int(11) NOT NULL,
  `DoctorId` int(11) NOT NULL,
  `StartTime` datetime(6) NOT NULL,
  `EndTime` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Doctors`
--

CREATE TABLE `Doctors` (
  `Id` int(11) NOT NULL,
  `Name` longtext DEFAULT NULL,
  `Specialty` longtext DEFAULT NULL,
  `ContactInfo` longtext DEFAULT NULL,
  `UserId` int(11) DEFAULT NULL,
  `CancellationPolicyHours` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `Doctors`
--

INSERT INTO `Doctors` (`Id`, `Name`, `Specialty`, `ContactInfo`, `UserId`, `CancellationPolicyHours`) VALUES
(3, 'Doctor User', 'General Physiotherapy', 'doctor@example.com', 2, 0),
(4, '陳博源', '治療', NULL, 5, 48);

-- --------------------------------------------------------

--
-- 資料表結構 `MedicalRecords`
--

CREATE TABLE `MedicalRecords` (
  `Id` int(11) NOT NULL,
  `PatientId` int(11) NOT NULL,
  `DoctorId` int(11) NOT NULL,
  `RecordDate` datetime(6) NOT NULL,
  `Diagnosis` longtext DEFAULT NULL,
  `Treatment` longtext DEFAULT NULL,
  `Notes` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Patients`
--

CREATE TABLE `Patients` (
  `Id` int(11) NOT NULL,
  `Name` longtext DEFAULT NULL,
  `ContactInfo` longtext DEFAULT NULL,
  `DateOfBirth` datetime(6) NOT NULL,
  `UserId` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `Patients`
--

INSERT INTO `Patients` (`Id`, `Name`, `ContactInfo`, `DateOfBirth`, `UserId`) VALUES
(1, 'Test User', 'user@example.com', '1995-11-11 05:43:35.741921', 3),
(2, 'Doctor User', 'doctor@example.com', '1995-11-11 05:44:09.673363', 2),
(3, '張益誠111534131', '111534131@stu.ukn.edu.tw', '1995-11-11 06:10:13.310790', 4),
(4, '陳博源111534106', '111534106@stu.ukn.edu.tw', '1995-11-11 06:11:45.612065', 5),
(5, 'Admin User', 'admin@example.com', '1995-11-11 06:21:24.981420', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `Users`
--

CREATE TABLE `Users` (
  `Id` int(11) NOT NULL,
  `Username` longtext NOT NULL,
  `PasswordHash` longtext DEFAULT NULL,
  `Role` longtext NOT NULL,
  `Email` longtext DEFAULT NULL,
  `GoogleId` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `Users`
--

INSERT INTO `Users` (`Id`, `Username`, `PasswordHash`, `Role`, `Email`, `GoogleId`) VALUES
(1, 'Admin User', '$2a$11$u2A8KptzlEbaTO/YYlEmLO9NVloTyi8pEsPDzDASoQk1hTDCCru4m', 'Admin', 'admin@example.com', NULL),
(2, 'Doctor User', '$2a$11$sefcCCwxDFJkhXdsdFTqUuzeFHQDdNeg9YXG2C8ctdqIbOiLR7.Qy', 'Doctor', 'doctor@example.com', NULL),
(3, 'Test User', '$2a$11$AOXCf./106bDAvh4N6T40.nRUkujIRm3/EzgGMORNj/OOyT34Fbki', 'User', 'user@example.com', NULL),
(4, '張益誠111534131', NULL, 'User', '111534131@stu.ukn.edu.tw', '105712737249491088866'),
(5, '陳博源111534106', NULL, 'Doctor', '111534106@stu.ukn.edu.tw', '100813922327970142971');

-- --------------------------------------------------------

--
-- 資料表結構 `__EFMigrationsHistory`
--

CREATE TABLE `__EFMigrationsHistory` (
  `MigrationId` varchar(150) NOT NULL,
  `ProductVersion` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `__EFMigrationsHistory`
--

INSERT INTO `__EFMigrationsHistory` (`MigrationId`, `ProductVersion`) VALUES
('20251103041258_InitialCreate', '8.0.8'),
('20251103064314_AddAuthToUser', '8.0.8'),
('20251103113009_AddDoctorSchedule', '8.0.8'),
('20251103121316_AddDoctorAvailability', '8.0.8'),
('20251104162216_AddDoctorAvailabilityRelationshipFinal', '8.0.8'),
('20251111022444_AddUserToPatient', '8.0.8'),
('20251111063142_AddCancellationPolicyToDoctor', '8.0.8');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `acupuncturepoints`
--
ALTER TABLE `AcupuncturePoints`
  ADD PRIMARY KEY (`Id`);

--
-- 資料表索引 `Appointments`
--
ALTER TABLE `Appointments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Appointments_DoctorId` (`DoctorId`),
  ADD KEY `IX_Appointments_PatientId` (`PatientId`);

--
-- 資料表索引 `DoctorAvailabilities`
--
ALTER TABLE `DoctorAvailabilities`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_DoctorAvailabilities_DoctorId` (`DoctorId`);

--
-- 資料表索引 `Doctors`
--
ALTER TABLE `Doctors`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Doctors_UserId` (`UserId`);

--
-- 資料表索引 `MedicalRecords`
--
ALTER TABLE `MedicalRecords`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_MedicalRecords_DoctorId` (`DoctorId`),
  ADD KEY `IX_MedicalRecords_PatientId` (`PatientId`);

--
-- 資料表索引 `Patients`
--
ALTER TABLE `Patients`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Patients_UserId` (`UserId`);

--
-- 資料表索引 `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`Id`);

--
-- 資料表索引 `__EFMigrationsHistory`
--
ALTER TABLE `__EFMigrationsHistory`
  ADD PRIMARY KEY (`MigrationId`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `acupuncturepoints`
--
ALTER TABLE `AcupuncturePoints`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `Appointments`
--
ALTER TABLE `Appointments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `DoctorAvailabilities`
--
ALTER TABLE `DoctorAvailabilities`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `Doctors`
--
ALTER TABLE `Doctors`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `MedicalRecords`
--
ALTER TABLE `MedicalRecords`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `Patients`
--
ALTER TABLE `Patients`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `Users`
--
ALTER TABLE `Users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `Appointments`
--
ALTER TABLE `Appointments`
  ADD CONSTRAINT `FK_Appointments_Doctors_DoctorId` FOREIGN KEY (`DoctorId`) REFERENCES `Doctors` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Appointments_Patients_PatientId` FOREIGN KEY (`PatientId`) REFERENCES `Patients` (`Id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `DoctorAvailabilities`
--
ALTER TABLE `DoctorAvailabilities`
  ADD CONSTRAINT `FK_DoctorAvailabilities_Doctors_DoctorId` FOREIGN KEY (`DoctorId`) REFERENCES `Doctors` (`Id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `Doctors`
--
ALTER TABLE `Doctors`
  ADD CONSTRAINT `FK_Doctors_Users_UserId` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `MedicalRecords`
--
ALTER TABLE `MedicalRecords`
  ADD CONSTRAINT `FK_MedicalRecords_Doctors_DoctorId` FOREIGN KEY (`DoctorId`) REFERENCES `Doctors` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_MedicalRecords_Patients_PatientId` FOREIGN KEY (`PatientId`) REFERENCES `Patients` (`Id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `Patients`
--
ALTER TABLE `Patients`
  ADD CONSTRAINT `FK_Patients_Users_UserId` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
