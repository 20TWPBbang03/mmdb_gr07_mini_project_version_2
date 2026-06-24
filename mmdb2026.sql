-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 04:27 PM
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
-- Database: `mmdb2026`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `vstu`
-- (See below for the actual view)
--
CREATE TABLE `vstu` (
`id` int(11)
,`matric_no` varchar(20)
,`full_name` varchar(100)
,`phone_no` varchar(20)
,`group_no` varchar(10)
,`life_motto` text
,`password` varchar(100)
,`photoStu` varchar(255)
,`photoStu_date` date
,`docStu` varchar(255)
,`docStu_date` date
,`audioStu` varchar(255)
,`audioStu_date` date
,`videoStu` varchar(255)
,`videoStu_date` date
);

-- --------------------------------------------------------

--
-- Structure for view `vstu`
--
DROP TABLE IF EXISTS `vstu`;
-- Error reading structure for table mmdb2026.vstu: #1142 - SHOW VIEW command denied to user &#039;GR07&#039;@&#039;localhost&#039; for table `mmdb2026`.`vstu`
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
