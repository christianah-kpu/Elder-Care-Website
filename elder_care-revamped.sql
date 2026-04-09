-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 09, 2026 at 05:19 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
  `adminID` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_trend_log`
--

CREATE TABLE `ai_trend_log` (
  `trendID` int(10) UNSIGNED NOT NULL,
  `residentSIN` varchar(9) NOT NULL,
  `consecutive_abnormal_count` int(11) DEFAULT 0,
  `alert_sent` tinyint(1) DEFAULT 0,
  `last_checked` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_trend_log`
--

INSERT INTO `ai_trend_log` (`trendID`, `residentSIN`, `consecutive_abnormal_count`, `alert_sent`, `last_checked`) VALUES
(2, '123456789', 3, 1, '2026-04-07 14:48:39');

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignmentID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(10) UNSIGNED NOT NULL,
  `empID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignmentID`, `residentSIN`, `empID`) VALUES
(5, 123456789, 1),
(6, 999999999, 2),
(7, 123456789, 2),
(8, 999999999, 1);

-- --------------------------------------------------------

--
-- Table structure for table `caregiver`
--

CREATE TABLE `caregiver` (
  `empID` int(8) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `profilePhoto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caregiver`
--

INSERT INTO `caregiver` (`empID`, `user_id`, `phone`, `fname`, `lname`, `profilePhoto`) VALUES
(1, 20, NULL, 'Eve', 'James', NULL),
(2, 25, NULL, 'blue', 'lamp', NULL),
(3, 29, NULL, 'Dam', 'Sel', NULL),
(4, 35, '5968973456', 'passion', 'compass', '69d75d4c9154b.png');

-- --------------------------------------------------------

--
-- Table structure for table `familymember`
--

CREATE TABLE `familymember` (
  `fmID` int(8) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `fname` text DEFAULT NULL,
  `lname` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `familymember`
--

INSERT INTO `familymember` (`fmID`, `user_id`, `fname`, `lname`, `phone`) VALUES
(1, 11, NULL, NULL, NULL),
(2, 21, 'Christianah', 'Ade', '1113456785'),
(3, 27, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `family_requests`
--

CREATE TABLE `family_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `healthreport`
--

CREATE TABLE `healthreport` (
  `reportID` int(10) UNSIGNED NOT NULL,
  `residentSIN` varchar(9) NOT NULL,
  `empID` int(10) UNSIGNED NOT NULL,
  `heartRate` int(11) NOT NULL,
  `bloodPressure` int(11) NOT NULL,
  `bloodSugar` int(11) NOT NULL,
  `temperature` int(11) NOT NULL,
  `dateOfCreation` datetime NOT NULL DEFAULT current_timestamp(),
  `dateEdited` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `healthreport`
--

INSERT INTO `healthreport` (`reportID`, `residentSIN`, `empID`, `heartRate`, `bloodPressure`, `bloodSugar`, `temperature`, `dateOfCreation`, `dateEdited`) VALUES
(1, '999999999', 2, 11, 123, 11, 11, '2026-04-06 19:53:00', '2026-04-06 19:53:00'),
(2, '999999999', 2, 0, 0, 0, 0, '2026-04-06 20:49:25', '2026-04-06 20:49:25'),
(3, '999999999', 2, 0, 0, 0, 0, '2026-04-06 21:18:23', '2026-04-06 21:18:23'),
(4, '999999999', 2, 11, 12, 11, 11, '2026-04-06 21:36:58', '2026-04-06 21:36:58'),
(10, '123456789', 2, 200, 200, 500, 300, '2026-04-07 14:48:08', '2026-04-07 14:48:08'),
(11, '123456789', 2, 4238824, 500, 290049, 52995, '2026-04-07 14:48:20', '2026-04-07 14:48:20'),
(12, '123456789', 2, 838838, 8888494, 299299, 993939, '2026-04-07 14:48:34', '2026-04-07 14:48:34');

-- --------------------------------------------------------

--
-- Table structure for table `link`
--

CREATE TABLE `link` (
  `linkID` int(10) UNSIGNED NOT NULL,
  `residentSIN` varchar(9) NOT NULL,
  `fmID` int(8) UNSIGNED NOT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `link`
--

INSERT INTO `link` (`linkID`, `residentSIN`, `fmID`, `status`) VALUES
(2, '999999999', 3, 'approved'),
(4, '123456789', 2, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `medID` int(10) UNSIGNED NOT NULL,
  `residentSIN` varchar(9) NOT NULL,
  `empID` int(10) UNSIGNED NOT NULL,
  `medName` varchar(100) NOT NULL,
  `dose` varchar(50) NOT NULL,
  `timeScheduled` time NOT NULL,
  `dateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medication`
--

INSERT INTO `medication` (`medID`, `residentSIN`, `empID`, `medName`, `dose`, `timeScheduled`, `dateCreated`) VALUES
(3, '999999999', 2, 'paracetemol', '5ml', '22:40:00', '2026-04-06 21:28:41'),
(4, '123456789', 2, 'paracetemol', '50mg', '03:00:00', '2026-04-06 22:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `medication_entry`
--

CREATE TABLE `medication_entry` (
  `entryID` int(10) UNSIGNED NOT NULL,
  `medID` int(10) UNSIGNED NOT NULL,
  `reportID` int(10) UNSIGNED NOT NULL,
  `status` enum('delayed','pending','missed','taken') NOT NULL DEFAULT 'pending',
  `timeTaken` time DEFAULT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medication_entry`
--

INSERT INTO `medication_entry` (`entryID`, `medID`, `reportID`, `status`, `timeTaken`, `date`) VALUES
(2, 3, 2, 'pending', NULL, '2026-04-06'),
(3, 4, 2, 'pending', NULL, '2026-04-06');

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--

CREATE TABLE `resident` (
  `residentSIN` varchar(9) NOT NULL,
  `user_id` int(11) NOT NULL,
  `DoB` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `profilePhoto` text DEFAULT NULL,
  `ECname` text DEFAULT NULL,
  `ECphone` varchar(15) DEFAULT NULL,
  `ECemail` text DEFAULT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident`
--

INSERT INTO `resident` (`residentSIN`, `user_id`, `DoB`, `phone`, `profilePhoto`, `ECname`, `ECphone`, `ECemail`, `fname`, `lname`) VALUES
('123456789', 22, NULL, NULL, NULL, NULL, NULL, NULL, 'Adam', 'Smith'),
('667877777', 34, '1960-06-07', '6048305765', '69d75a49cb3df.jpg', 'Holy', '8785765843', 'abc@gmail.com', 'Flavour', 'Nabania'),
('999999999', 26, NULL, NULL, NULL, NULL, NULL, NULL, 'john', 'doe');

-- --------------------------------------------------------

--
-- Table structure for table `selfreport`
--

CREATE TABLE `selfreport` (
  `selfReportID` int(10) UNSIGNED NOT NULL,
  `reportID` int(10) UNSIGNED NOT NULL,
  `sleepQuality` enum('Poor','Neutral','Good','Excellent') NOT NULL,
  `mood` text NOT NULL,
  `painLevel` enum('None','Low','Medium','High') NOT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(20) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
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
(4, 'login', 'login@email.com', '$2y$10$6iA/3VwcTRQ1GefE8ecfB.pg46aanfyZHIz5nYGXXJNVYrkwZEFwO', 'admin', 1, '2026-04-01 07:49:29'),
(11, 'Jane', 'adeyemo123.ac@gmail.com', '$2y$10$EIQdyz7qEWJNZj2PEK32beZcq/d1EV6IcSwIK4T.lGlYeHzW2F8MG', 'family', 1, '2026-04-06 14:14:13'),
(20, 'Eve', 'evesmith@gmail.com', '$2y$10$G4y4D8GNl16FBcL8K0iKmem3OQg1Z9/qzVu.TntgEjsavmLzMxPlK', 'caregiver', 0, '2026-04-06 07:05:34'),
(21, 'christianah', 'chris123.ac@gmail.com', '$2y$10$K60bWal7TOaxPUpofIA6SOFmdgGoKU63LOPgR4uWtl3oYVqxq5Zim', 'family', 1, '2026-04-06 20:09:37'),
(22, 'adam', 'theyebird.com@gmail.com', '$2y$10$VZmkTUGilMffQX4cZDZQb.BLL8nQBPGYOQBQwHy9yJZM/eUD01FDW', 'resident', 1, '2026-04-06 20:31:32'),
(25, 'blamp', 'blue@lamp.com', '$2y$10$ZbxL8h5WOKvXov86j7cmHeOC3iuIWtAWZmmI2k4H8xseYDgmCPpwu', 'caregiver', 1, '2026-04-07 02:18:43'),
(26, 'jdoe', 'jdoe@doe.com', '$2y$10$0qxMN5.VXM7VEsP6wAXhhOxufqQNkhnAirjgyIsJrFYPurvU5nNye', 'resident', 1, '2026-04-07 02:19:12'),
(27, 'Fmilliar', 'christianah123.ac@gmail.com', '$2y$10$lXZNujqveZ0z6jVQ8CNP0ObeqFYzwji5pGQXJ6SmdBkLyYzj1pKIi', 'family', 1, '2026-04-07 11:20:05'),
(29, 'Dam', 'dam@gmail.com', '$2y$10$G0GmP5aWyprb7ErAVK6bVeeSAl.JOBccRl4nalxi9gglc3QU3U8QG', 'caregiver', 0, '2026-04-08 01:15:05'),
(34, 'Flavour', 'adeyemochristianah03@gmail.com', '$2y$10$ih6aI/B0ASfA7nPEMgEm0uAklVsp3HNAez/Qw3G/bjsDeCtANsmhy', 'resident', 0, '2026-04-09 07:50:33'),
(35, 'Passion', 'compass@gmail.com', '$2y$10$1LglTFimxORrPz./mscIOez6QwQhLXdDEYfJk/FT9SAhiNt2126xi', 'caregiver', 0, '2026-04-09 08:03:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`id`, `user_id`, `status`) VALUES
(2, 4, 'active'),
(9, 11, 'active'),
(11, 18, 'active'),
(12, 20, 'active'),
(13, 21, 'active'),
(14, 24, 'suspended'),
(15, 23, 'suspended'),
(16, 27, 'active'),
(17, 29, 'active'),
(18, 26, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `verification_tokens`
--

CREATE TABLE `verification_tokens` (
  `tokenID` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_tokens`
--

INSERT INTO `verification_tokens` (`tokenID`, `user_id`, `token`, `created_at`) VALUES
(20, 23, '002711e8c830b6690578c691a048cb3f', '2026-04-07 03:13:44'),
(21, 24, '780483f7e737534080bfaa6438f74402', '2026-04-07 03:14:38'),
(26, 29, '180b27a6350d8d5908c089388e8ce368', '2026-04-08 03:15:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`adminID`),
  ADD KEY `fk_admin_user` (`user_id`);

--
-- Indexes for table `ai_trend_log`
--
ALTER TABLE `ai_trend_log`
  ADD PRIMARY KEY (`trendID`),
  ADD UNIQUE KEY `unique_resident` (`residentSIN`);

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignmentID`),
  ADD KEY `fk_assignment_resident` (`residentSIN`),
  ADD KEY `fk_assignment_caregiver` (`empID`);

--
-- Indexes for table `caregiver`
--
ALTER TABLE `caregiver`
  ADD PRIMARY KEY (`empID`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `familymember`
--
ALTER TABLE `familymember`
  ADD PRIMARY KEY (`fmID`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `family_requests`
--
ALTER TABLE `family_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `healthreport`
--
ALTER TABLE `healthreport`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `fk_healthreport_caregiver` (`empID`),
  ADD KEY `fk_healthreport_resident` (`residentSIN`);

--
-- Indexes for table `link`
--
ALTER TABLE `link`
  ADD PRIMARY KEY (`linkID`),
  ADD UNIQUE KEY `residentSIN` (`residentSIN`,`fmID`),
  ADD KEY `fmID` (`fmID`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`medID`),
  ADD KEY `fk_med_caregiver` (`empID`),
  ADD KEY `fk_med_resident` (`residentSIN`);

--
-- Indexes for table `medication_entry`
--
ALTER TABLE `medication_entry`
  ADD PRIMARY KEY (`entryID`),
  ADD KEY `medication_entry_ibfk_1` (`medID`),
  ADD KEY `medication_entry_ibfk_2` (`reportID`);

--
-- Indexes for table `resident`
--
ALTER TABLE `resident`
  ADD PRIMARY KEY (`residentSIN`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `residentSIN` (`residentSIN`);

--
-- Indexes for table `selfreport`
--
ALTER TABLE `selfreport`
  ADD PRIMARY KEY (`selfReportID`),
  ADD KEY `fk_selfreport_health` (`reportID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD PRIMARY KEY (`tokenID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `adminID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_trend_log`
--
ALTER TABLE `ai_trend_log`
  MODIFY `trendID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignmentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `caregiver`
--
ALTER TABLE `caregiver`
  MODIFY `empID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `familymember`
--
ALTER TABLE `familymember`
  MODIFY `fmID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `family_requests`
--
ALTER TABLE `family_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `healthreport`
--
ALTER TABLE `healthreport`
  MODIFY `reportID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `link`
--
ALTER TABLE `link`
  MODIFY `linkID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medication`
--
ALTER TABLE `medication`
  MODIFY `medID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medication_entry`
--
ALTER TABLE `medication_entry`
  MODIFY `entryID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `selfreport`
--
ALTER TABLE `selfreport`
  MODIFY `selfReportID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `tokenID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrator`
--
ALTER TABLE `administrator`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_trend_log`
--
ALTER TABLE `ai_trend_log`
  ADD CONSTRAINT `fk_trend_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE;

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `fk_assignment_caregiver` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE;

--
-- Constraints for table `caregiver`
--
ALTER TABLE `caregiver`
  ADD CONSTRAINT `caregiver_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `familymember`
--
ALTER TABLE `familymember`
  ADD CONSTRAINT `familymember_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `family_requests`
--
ALTER TABLE `family_requests`
  ADD CONSTRAINT `family_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `healthreport`
--
ALTER TABLE `healthreport`
  ADD CONSTRAINT `fk_healthreport_caregiver` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_healthreport_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE;

--
-- Constraints for table `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `fk_link_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE,
  ADD CONSTRAINT `link_ibfk_2` FOREIGN KEY (`fmID`) REFERENCES `familymember` (`fmID`) ON DELETE CASCADE;

--
-- Constraints for table `medication`
--
ALTER TABLE `medication`
  ADD CONSTRAINT `fk_med_caregiver` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_med_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE;

--
-- Constraints for table `medication_entry`
--
ALTER TABLE `medication_entry`
  ADD CONSTRAINT `medication_entry_ibfk_1` FOREIGN KEY (`medID`) REFERENCES `medication` (`medID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `medication_entry_ibfk_2` FOREIGN KEY (`reportID`) REFERENCES `healthreport` (`reportID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resident`
--
ALTER TABLE `resident`
  ADD CONSTRAINT `resident_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `selfreport`
--
ALTER TABLE `selfreport`
  ADD CONSTRAINT `fk_selfreport_health` FOREIGN KEY (`reportID`) REFERENCES `healthreport` (`reportID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
