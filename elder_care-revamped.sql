-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 06, 2026 at 01:11 PM
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
(4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `caregiver`
--

CREATE TABLE `caregiver` (
  `empID` int(8) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caregiver`
--

INSERT INTO `caregiver` (`empID`, `user_id`, `phone`, `fname`, `lname`) VALUES
(1, 20, NULL, 'Eve', 'Smith');

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
(2, 21, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `healthreport`
--

CREATE TABLE `healthreport` (
  `reportID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(10) UNSIGNED NOT NULL,
  `empID` int(10) UNSIGNED NOT NULL,
  `heartRate` int(11) NOT NULL,
  `bloodPressure` int(11) NOT NULL,
  `bloodSugar` int(11) NOT NULL,
  `temperature` int(11) NOT NULL,
  `dateOfCreation` datetime NOT NULL DEFAULT current_timestamp(),
  `dateEdited` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link`
--

CREATE TABLE `link` (
  `linkID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(9) UNSIGNED NOT NULL,
  `fmID` int(8) UNSIGNED NOT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `link`
--

INSERT INTO `link` (`linkID`, `residentSIN`, `fmID`, `status`) VALUES
(1, 1, 1, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `medID` int(10) UNSIGNED NOT NULL,
  `residentSIN` int(10) UNSIGNED NOT NULL,
  `empID` int(10) UNSIGNED NOT NULL,
  `medName` varchar(100) NOT NULL,
  `dose` varchar(50) NOT NULL,
  `timeScheduled` time NOT NULL,
  `status` enum('given','missed','pending') NOT NULL DEFAULT 'pending',
  `dateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--

CREATE TABLE `resident` (
  `residentSIN` int(9) UNSIGNED NOT NULL,
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
(1, 18, NULL, '6048305765', NULL, 'Holy', NULL, NULL, 'Adam', 'Smith');

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
(18, 'adam', 'theyebird.com@gmail.com', '$2y$10$nmiyxe12yrfED8N2Gzkp4OE32iBFtnLEvZ.mpypkLb13w5LEveUW6', 'resident', 1, '2026-04-06 06:58:29'),
(20, 'Eve', 'evesmith@gmail.com', '$2y$10$G4y4D8GNl16FBcL8K0iKmem3OQg1Z9/qzVu.TntgEjsavmLzMxPlK', 'caregiver', 0, '2026-04-06 07:05:34'),
(21, 'christianah', 'christianah123.ac@gmail.com', '$2y$10$K60bWal7TOaxPUpofIA6SOFmdgGoKU63LOPgR4uWtl3oYVqxq5Zim', 'family', 1, '2026-04-06 20:09:37');

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
(13, 21, 'active');

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
(4, 4, '0', '2026-04-04 00:58:17'),
(10, 13, '51f8edc4bb021e990bc6d3abd847697f', '2026-04-06 08:31:41'),
(11, 14, 'd700afea4b86392f11d2494e348c072f', '2026-04-06 08:39:45'),
(12, 15, '76ba087f3c1253546d44f04f8d8d6bd6', '2026-04-06 08:42:26'),
(13, 16, '23cc164e1476470b93b71972ece20b89', '2026-04-06 08:43:12'),
(14, 17, '94763cb63d6e34f7ad762513dec5b965', '2026-04-06 08:52:12'),
(16, 19, '06b30e55e1f757800ee837962a389c90', '2026-04-06 09:02:42'),
(17, 20, 'e8b5e89829fb0d83f2165a49e958b6cf', '2026-04-06 09:05:34');

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
-- Indexes for table `healthreport`
--
ALTER TABLE `healthreport`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `fk_healthreport_resident` (`residentSIN`),
  ADD KEY `fk_healthreport_caregiver` (`empID`);

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
  ADD KEY `fk_med_resident` (`residentSIN`),
  ADD KEY `fk_med_caregiver` (`empID`);

--
-- Indexes for table `resident`
--
ALTER TABLE `resident`
  ADD PRIMARY KEY (`residentSIN`),
  ADD UNIQUE KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignmentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `caregiver`
--
ALTER TABLE `caregiver`
  MODIFY `empID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `familymember`
--
ALTER TABLE `familymember`
  MODIFY `fmID` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `resident`
--
ALTER TABLE `resident`
  MODIFY `residentSIN` int(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `selfreport`
--
ALTER TABLE `selfreport`
  MODIFY `selfReportID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `tokenID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrator`
--
ALTER TABLE `administrator`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `fk_assignment_caregiver` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE;

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
-- Constraints for table `healthreport`
--
ALTER TABLE `healthreport`
  ADD CONSTRAINT `fk_healthreport_caregiver` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_healthreport_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE;

--
-- Constraints for table `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `link_ibfk_1` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE,
  ADD CONSTRAINT `link_ibfk_2` FOREIGN KEY (`fmID`) REFERENCES `familymember` (`fmID`) ON DELETE CASCADE;

--
-- Constraints for table `medication`
--
ALTER TABLE `medication`
  ADD CONSTRAINT `fk_med_caregiver` FOREIGN KEY (`empID`) REFERENCES `caregiver` (`empID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_med_resident` FOREIGN KEY (`residentSIN`) REFERENCES `resident` (`residentSIN`) ON DELETE CASCADE;

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
