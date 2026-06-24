-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 01:10 PM
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
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `fk_submission_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_submission_student` FOREIGN KEY (`student_matric_no`) REFERENCES `student` (`student_matric_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
