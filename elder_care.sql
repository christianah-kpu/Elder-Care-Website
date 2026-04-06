-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 05, 2026 at 08:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elder_care`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `adminID` int(8) UNSIGNED NOT NULL,
  `username` varchar(20) NOT NULL,
  `name` text NOT NULL,
  `phone` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`adminID`, `username`, `name`, `phone`) VALUES
(1, 'login', 'Logan Login', '778123987');

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignmentID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(9) UNSIGNED NOT NULL,
  `empID` int(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignmentID`, `residentSIN`, `empID`) VALUES
(1, 123456789, 1);

-- --------------------------------------------------------

--
-- Table structure for table `caregiver`
--

CREATE TABLE `caregiver` (
  `empID` int(8) UNSIGNED NOT NULL,
  `username` varchar(20) NOT NULL,
  `name` text NOT NULL,
  `phone` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caregiver`
--

INSERT INTO `caregiver` (`empID`, `username`, `name`, `phone`) VALUES
(1, 'blamp', 'Blue Lamp', '604123567');

-- --------------------------------------------------------

--
-- Table structure for table `familymember`
--

CREATE TABLE `familymember` (
  `fmID` int(8) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `phone` varchar(10) NOT NULL,
  `username` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `familymember`
--

INSERT INTO `familymember` (`fmID`, `name`, `phone`, `username`) VALUES
(1, 'Jimothy Doe', '5554443333', 'jdoe1');

-- --------------------------------------------------------

--
-- Table structure for table `healthreport`
--

CREATE TABLE `healthreport` (
  `reportID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(9) UNSIGNED NOT NULL,
  `empID` int(8) UNSIGNED NOT NULL,
  `heartRate` int(11) NOT NULL,
  `bloodPressure` int(11) NOT NULL,
  `bloodSugar` int(11) NOT NULL,
  `temperature` int(11) NOT NULL,
  `dateOfCreation` date NOT NULL DEFAULT current_timestamp(),
  `dateEdited` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link`
--

CREATE TABLE `link` (
  `linkID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(9) UNSIGNED NOT NULL,
  `fmID` int(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `link`
--

INSERT INTO `link` (`linkID`, `residentSIN`, `fmID`) VALUES
(1, 123456789, 1);

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `medID` int(10) UNSIGNED NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `residentSIN` int(9) UNSIGNED NOT NULL,
  `scheduledTime` time DEFAULT NULL,
  `dose` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--

CREATE TABLE `resident` (
  `residentSIN` int(9) UNSIGNED NOT NULL,
  `username` varchar(20) NOT NULL,
  `name` text NOT NULL,
  `DoB` date NOT NULL,
  `phone` varchar(10) NOT NULL,
  `profilePhoto` text NOT NULL,
  `ECname` text NOT NULL,
  `ECphone` varchar(10) NOT NULL,
  `ECemail` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident`
--

INSERT INTO `resident` (`residentSIN`, `username`, `name`, `DoB`, `phone`, `profilePhoto`, `ECname`, `ECphone`, `ECemail`) VALUES
(123456789, 'jdoe', 'John Doe', '1945-04-16', '1112223333', '', 'Jimothy Doe', '5554443333', 'jimothy.doe@email.com');

-- --------------------------------------------------------

--
-- Table structure for table `selfreport`
--

CREATE TABLE `selfreport` (
  `selfReportID` int(10) UNSIGNED NOT NULL,
  `reportID` int(10) UNSIGNED NOT NULL,
  `sleepQuality` set('Poor','Neutral','Good','Excellent') NOT NULL,
  `mood` text NOT NULL,
  `painLevel` set('Low','Medium','High','None') NOT NULL,
  `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` text CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
  `password_hash` text CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
  `role` enum('admin','family','caregiver','resident') CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL DEFAULT 'family',
  `is_verified` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `is_verified`, `created_at`) VALUES
(1, 'jdoe', 'john.doe@email.com', '$2y$10$6iA/3VwcTRQ1GefE8ecfB.pg46aanfyZHIz5nYGXXJNVYrkwZEFwO', 'resident', 1, '2026-04-05 05:44:21'),
(2, 'blamp', 'blue.lamp@email.com', '$2y$10$6iA/3VwcTRQ1GefE8ecfB.pg46aanfyZHIz5nYGXXJNVYrkwZEFwO', 'caregiver', 1, '2026-04-05 04:46:09'),
(3, 'jdoe1', 'jimothy.doe', '$2y$10$8.EO4VmkAmCAJHLixGaxmeAUc6X5iZMpPZ3mKCB/FMB1AhEMASUDG', 'family', 1, '2026-04-05 05:44:59'),
(4, 'login', 'login@email.com', '$2y$10$6iA/3VwcTRQ1GefE8ecfB.pg46aanfyZHIz5nYGXXJNVYrkwZEFwO', 'admin', 1, '2026-04-01 07:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_tokens`
--

CREATE TABLE `verification_tokens` (
  `tokenID` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token` int(11) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `usernames` (`username`);

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignmentID`),
  ADD KEY `residentSIN` (`residentSIN`,`empID`),
  ADD KEY `assignment_ibfk_1` (`empID`);

--
-- Indexes for table `caregiver`
--
ALTER TABLE `caregiver`
  ADD PRIMARY KEY (`empID`),
  ADD UNIQUE KEY `usernames` (`username`);

--
-- Indexes for table `familymember`
--
ALTER TABLE `familymember`
  ADD PRIMARY KEY (`fmID`),
  ADD UNIQUE KEY `usernames` (`username`);

--
-- Indexes for table `healthreport`
--
ALTER TABLE `healthreport`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `residentSIN` (`residentSIN`,`empID`),
  ADD KEY `healthreport_ibfk_1` (`empID`);

--
-- Indexes for table `link`
--
ALTER TABLE `link`
  ADD PRIMARY KEY (`linkID`),
  ADD KEY `link_ibfk_1` (`residentSIN`),
  ADD KEY `link_ibfk_2` (`fmID`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`medID`),
  ADD KEY `residentSIN` (`residentSIN`);

--
-- Indexes for table `resident`
--
ALTER TABLE `resident`
  ADD PRIMARY KEY (`residentSIN`),
  ADD UNIQUE KEY `usernames` (`username`);

--
-- Indexes for table `selfreport`
--
ALTER TABLE `selfreport`
  ADD PRIMARY KEY (`selfReportID`),
  ADD KEY `selfreport_ibfk_1` (`reportID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD PRIMARY KEY (`tokenID`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `adminID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignmentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `caregiver`
--
ALTER TABLE `caregiver`
  MODIFY `empID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `familymember`
--
ALTER TABLE `familymember`
  MODIFY `fmID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `healthreport`
--
ALTER TABLE `healthreport`
  MODIFY `reportID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `link`
--
ALTER TABLE `link`
  MODIFY `linkID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medication`
--
ALTER TABLE `medication`
  MODIFY `medID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `selfreport`
--
ALTER TABLE `selfreport`
  MODIFY `selfReportID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `tokenID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrator`
--
ALTER TABLE `administrator`
  ADD CONSTRAINT `administrator_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `assignment_ibfk_1` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignment_ibfk_2` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `caregiver`
--
ALTER TABLE `caregiver`
  ADD CONSTRAINT `caregiver_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `familymember`
--
ALTER TABLE `familymember`
  ADD CONSTRAINT `familymember_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `healthreport`
--
ALTER TABLE `healthreport`
  ADD CONSTRAINT `healthreport_ibfk_1` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `healthreport_ibfk_2` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `link_ibfk_1` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `link_ibfk_2` FOREIGN KEY (`fmID`) REFERENCES `familymember` (`fmID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medication`
--
ALTER TABLE `medication`
  ADD CONSTRAINT `medication_ibfk_1` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resident`
--
ALTER TABLE `resident`
  ADD CONSTRAINT `resident_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `selfreport`
--
ALTER TABLE `selfreport`
  ADD CONSTRAINT `selfreport_ibfk_1` FOREIGN KEY (`reportID`) REFERENCES `healthreport` (`reportID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_status`
--
ALTER TABLE `user_status`
  ADD CONSTRAINT `user_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD CONSTRAINT `verification_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
