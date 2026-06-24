-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 02:11 PM
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
-- Table structure for table `multimedia_content`
--

CREATE TABLE `multimedia_content` (
  `content_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `content_file_type` varchar(50) NOT NULL,
  `content_file_path` varchar(500) NOT NULL,
  `extracted_text` longtext DEFAULT NULL,
  `content_title` varchar(255) DEFAULT NULL,
  `tbr_theme_category` varchar(100) DEFAULT NULL,
  `word_analyse` int(11) NOT NULL DEFAULT 0,
  `theme_found` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `multimedia_content`
--

INSERT INTO `multimedia_content` (`content_id`, `submission_id`, `content_file_type`, `content_file_path`, `extracted_text`, `content_title`, `tbr_theme_category`, `word_analyse`, `theme_found`) VALUES
(301, 201, 'PDF', '/storage/docs/b032210001_report.pdf', 'This geodatabase report outlines a structured implementation strategy. Sustaining an active, positive mindset across team divisions optimizes development velocity and software resilience.', NULL, 'Positive Mindset', 0, 0),
(302, 202, 'Video', '/storage/videos/b032210042_demo.mp4', 'Technical walk-through mapping architectural points. Project structures aim to leave an inspirational footprint on municipal logistics frameworks.', 'Inspirational Architectural Frameworks V1', 'Inspirational', 0, 0),
(303, 203, 'Video', '/storage/videos/b032210085_presentation.mp4', 'Comprehensive asset documentation slide presentation detailing core algorithm execution behaviors.', 'Motivational Strategies for Project Lifecycle Management', 'Motivational', 0, 0),
(304, 204, 'PDF', '/storage/docs/b032210112_script_analysis.pdf', 'Standard implementation file mapping operational limits and matrix configurations without major thematic classifications.', NULL, 'Technical-Unclassified', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `fk_multimedia_sub` (`submission_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `multimedia_content`
--
ALTER TABLE `multimedia_content`
  ADD CONSTRAINT `fk_multimedia_sub` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
