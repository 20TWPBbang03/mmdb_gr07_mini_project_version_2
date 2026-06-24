-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 05:03 PM
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
  `due_date` datetime NOT NULL,
  `max_file_size` int(11) NOT NULL,
  `assignment_status` enum('Available','Closed','','') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignment_id`, `title`, `due_date`, `max_file_size`, `assignment_status`) VALUES
(101, 'Advanced Multimedia Geodatabase Project', '2026-06-01 23:59:59', 52428800, NULL),
(102, 'Real-time Feature Extraction Script', '2026-06-15 18:00:00', 20971520, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `facial_expression_analysis`
--

CREATE TABLE `facial_expression_analysis` (
  `analysis_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_matric_no` varchar(50) NOT NULL,
  `eye_position` varchar(100) DEFAULT NULL,
  `mouth_position` varchar(100) DEFAULT NULL,
  `eyebrow_position` varchar(100) DEFAULT NULL,
  `facial_landmarks` longtext DEFAULT NULL,
  `cbr_expression_result` varchar(50) DEFAULT NULL,
  `expression_confidence` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facial_expression_analysis`
--

INSERT INTO `facial_expression_analysis` (`analysis_id`, `student_id`, `student_matric_no`, `eye_position`, `mouth_position`, `eyebrow_position`, `facial_landmarks`, `cbr_expression_result`, `expression_confidence`) VALUES
(401, NULL, 'B032210001', 'X:242,Y:180', 'X:245,Y:290', NULL, '{\"landmarks\": [[242,180], [285,182], [245,290]], \"confidence\": 0.98, \"mesh_version\": \"v2.1\"}', 'Happy', NULL),
(402, NULL, 'B032210042', 'X:238,Y:182', 'X:240,Y:275', NULL, '{\"landmarks\": [[238,182], [281,183], [240,275]], \"confidence\": 0.94, \"mesh_version\": \"v2.1\"}', 'Neutral', NULL),
(403, NULL, 'B032210085', 'X:240,Y:195', 'X:242,Y:310', NULL, '{\"landmarks\": [[240,195], [284,196], [242,310]], \"confidence\": 0.91, \"mesh_version\": \"v2.1\"}', 'Surprise', NULL),
(404, NULL, 'B032210112', 'X:245,Y:178', 'X:244,Y:260', NULL, '{\"landmarks\": [[245,178], [288,180], [244,260]], \"confidence\": 0.96, \"mesh_version\": \"v2.1\"}', 'Sad', NULL);

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

--
-- Dumping data for table `multimedia_content`
--

INSERT INTO `multimedia_content` (`content_id`, `submission_id`, `content_file_type`, `content_file_path`, `extracted_text`, `content_title`, `tbr_theme_category`) VALUES
(301, 201, 'PDF', '/storage/docs/b032210001_report.pdf', 'This geodatabase report outlines a structured implementation strategy. Sustaining an active, positive mindset across team divisions optimizes development velocity and software resilience.', NULL, 'Positive Mindset'),
(302, 202, 'Video', '/storage/videos/b032210042_demo.mp4', 'Technical walk-through mapping architectural points. Project structures aim to leave an inspirational footprint on municipal logistics frameworks.', 'Inspirational Architectural Frameworks V1', 'Inspirational'),
(303, 203, 'Video', '/storage/videos/b032210085_presentation.mp4', 'Comprehensive asset documentation slide presentation detailing core algorithm execution behaviors.', 'Motivational Strategies for Project Lifecycle Management', 'Motivational'),
(304, 204, 'PDF', '/storage/docs/b032210112_script_analysis.pdf', 'Standard implementation file mapping operational limits and matrix configurations without major thematic classifications.', NULL, 'Technical-Unclassified');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_matric_no` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `phone_no` varchar(100) NOT NULL,
  `life_motto` varchar(255) DEFAULT NULL,
  `profile_image_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_matric_no`, `student_name`, `phone_no`, `life_motto`, `profile_image_path`) VALUES
('B032210001', 'Tan Wei Pin', 'tan@student.utem.edu.my', 'Positive Mindset drives exceptional execution.', '/storage/profiles/b032210001_face.jpg'),
('B032210042', 'Nur Asyiqin binti Abdullah', 'asyiqin@student.utem.edu.my', 'Strive for consistency, not perfection.', '/storage/profiles/b032210042_face.jpg'),
('B032210085', 'Nur Hannah Fatini binti Mohd Azahar', 'hannah@student.utem.edu.my', 'An inspirational life changes perspectives.', '/storage/profiles/b032210085_face.jpg'),
('B032210112', 'Tengku Umairah Khadijah binti Tengku Rithaudden', 'umairah@student.utem.edu.my', 'Motivational core produces resilient structures.', '/storage/profiles/b032210112_face.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `submission_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_matric_no` varchar(50) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `submission_date` datetime NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `file_validation` enum('Oversized','Within Range','','') DEFAULT NULL,
  `submission_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`submission_id`, `student_id`, `student_matric_no`, `assignment_id`, `submission_date`, `file_path`, `file_size`, `file_validation`, `submission_status`) VALUES
(201, NULL, 'B032210001', 101, '2026-05-31 14:20:00', NULL, 31457280, 'Oversized', 'On-Time'),
(202, NULL, 'B032210042', 101, '2026-06-02 09:15:00', NULL, 41943040, 'Oversized', 'Late Submission'),
(203, NULL, 'B032210085', 101, '2026-05-30 21:00:00', NULL, 62914560, 'Oversized', 'Oversized'),
(204, NULL, 'B032210112', 102, '2026-06-16 11:00:00', NULL, 25165824, 'Oversized', 'Oversized');

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
