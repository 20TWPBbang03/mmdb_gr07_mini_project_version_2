-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2026 at 10:24 AM
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
-- Database: `gr07`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignment_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `max_file_size_mb` decimal(6,2) NOT NULL,
  `assignment_status` enum('Available','Closed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facial_expression_analysis`
--

CREATE TABLE `facial_expression_analysis` (
  `analysis_id` int(11) NOT NULL,
  `student_matric_no` varchar(15) DEFAULT NULL,
  `face_captured` longtext DEFAULT NULL,
  `eye_position` varchar(100) DEFAULT NULL,
  `mouth_position` varchar(100) DEFAULT NULL,
  `eyebrow_position` varchar(100) DEFAULT NULL,
  `cbr_expression_result` varchar(50) DEFAULT NULL,
  `expression_confidence` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `multimedia_content`
--

CREATE TABLE `multimedia_content` (
  `content_id` int(11) NOT NULL,
  `content_file_name` varchar(50) NOT NULL,
  `content_file_type` varchar(50) NOT NULL,
  `content_file_path` varchar(500) NOT NULL,
  `extracted_text` longtext NOT NULL,
  `tbr_theme_category` varchar(100) NOT NULL,
  `word_analyse` int(11) NOT NULL,
  `student_matric_no` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_matric_no` varchar(15) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `group_no` enum('GK01','GK02','GR01','GR02','GR03','GR04','GR05','GR06','GR07','GR08','GR09','GS01','GS02','GS03','GS04','GS05','GW01','GW02','GW03','GW04','GW05','GW06','GW07','GW08','GW09') DEFAULT NULL,
  `life_motto` varchar(255) DEFAULT NULL,
  `profile_image_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `submission_id` int(11) NOT NULL,
  `student_matric_no` varchar(15) DEFAULT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `file_path` varchar(500) NOT NULL,
  `file_size_mb` decimal(8,4) NOT NULL,
  `file_validation` enum('Oversized','Valid') DEFAULT NULL,
  `submission_status` enum('Late','Early') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignment_id`);

--
-- Indexes for table `facial_expression_analysis`
--
ALTER TABLE `facial_expression_analysis`
  ADD PRIMARY KEY (`analysis_id`),
  ADD KEY `student_matric_no` (`student_matric_no`);

--
-- Indexes for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `student_matric_no` (`student_matric_no`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_matric_no`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `student_matric_no` (`student_matric_no`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facial_expression_analysis`
--
ALTER TABLE `facial_expression_analysis`
  MODIFY `analysis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `facial_expression_analysis`
--
ALTER TABLE `facial_expression_analysis`
  ADD CONSTRAINT `facial_expression_analysis_ibfk_1` FOREIGN KEY (`student_matric_no`) REFERENCES `student` (`student_matric_no`);

--
-- Constraints for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  ADD CONSTRAINT `multimedia_content_ibfk_1` FOREIGN KEY (`student_matric_no`) REFERENCES `student` (`student_matric_no`);

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `submission_ibfk_1` FOREIGN KEY (`student_matric_no`) REFERENCES `student` (`student_matric_no`),
  ADD CONSTRAINT `submission_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
