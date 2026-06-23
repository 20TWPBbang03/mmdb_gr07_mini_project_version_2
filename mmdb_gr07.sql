-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 04:30 PM
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
-- Database: `mmdb_gr07`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignment_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `max_file_size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facial_expression_analysis`
--

CREATE TABLE `facial_expression_analysis` (
  `analysis_id` int(11) NOT NULL,
  `student_matric_no` varchar(50) NOT NULL,
  `eye_position` varchar(100) DEFAULT NULL,
  `mouth_position` varchar(100) DEFAULT NULL,
  `eyebrow_position` varchar(100) DEFAULT NULL,
  `facial_landmarks` longtext DEFAULT NULL,
  `cbr_expression_result` varchar(50) DEFAULT NULL,
  `expression_confidence` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `multimedia_content`
--

CREATE TABLE `multimedia_content` (
  `content_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `content_file_type` varchar(50) NOT NULL,
  `content_file_path` varchar(500) NOT NULL,
  `extracted_text` longtext DEFAULT NULL,
  `content_title` varchar(255) DEFAULT NULL,
  `tbr_theme_category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_matric_no` varchar(15) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `life_motto` varchar(255) DEFAULT NULL,
  `profile_image_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `submission_id` int(11) NOT NULL,
  `student_matric_no` varchar(15) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `submission_date` datetime NOT NULL,
  `submission_file_path` varchar(500) NOT NULL,
  `submission_file_size_mb` int(11) NOT NULL,
  `abr_status` enum('Late Submission','On-Time','File Oversized','') DEFAULT NULL
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
  ADD KEY `fk_facial_student` (`student_matric_no`);

--
-- Indexes for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `fk_multimedia_sub` (`submission_id`);

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
  ADD KEY `fk_submission_student` (`student_matric_no`),
  ADD KEY `fk_submission_assignment` (`assignment_id`);

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
-- Constraints for dumped tables
--

--
-- Constraints for table `facial_expression_analysis`
--
ALTER TABLE `facial_expression_analysis`
  ADD CONSTRAINT `fk_facial_student` FOREIGN KEY (`student_matric_no`) REFERENCES `student` (`student_matric_no`) ON DELETE CASCADE;

--
-- Constraints for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  ADD CONSTRAINT `fk_multimedia_sub` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`) ON DELETE CASCADE;

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `fk_submission_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_submission_student` FOREIGN KEY (`student_matric_no`) REFERENCES `student` (`student_matric_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
