-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 10:57 AM
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
-- Database: `librarydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activerequests`
--

CREATE TABLE `activerequests` (
  `Issue_ID` int(11) NOT NULL,
  `Roll_No` int(11) DEFAULT NULL,
  `Book_ID` int(11) DEFAULT NULL,
  `Due_Date` date NOT NULL,
  `Dues` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `password`) VALUES
('admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `bookentry`
--

CREATE TABLE `bookentry` (
  `entry_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookentry`
--

INSERT INTO `bookentry` (`entry_id`, `book_id`, `status`) VALUES
(1, 2, 'available'),
(2, 2, 'available'),
(3, 3, 'available'),
(4, 4, 'available'),
(5, 4, 'available'),
(6, 4, 'available'),
(7, 4, 'available'),
(8, 4, 'available'),
(9, 4, 'available'),
(10, 4, 'available'),
(11, 4, 'available'),
(12, 4, 'available'),
(13, 4, 'available'),
(14, 4, 'available'),
(15, 4, 'available'),
(16, 4, 'available'),
(17, 4, 'available'),
(18, 4, 'available'),
(19, 4, 'available'),
(20, 4, 'available'),
(21, 4, 'available'),
(22, 4, 'available'),
(23, 4, 'available'),
(24, 4, 'available'),
(25, 4, 'available'),
(26, 4, 'available'),
(27, 4, 'available'),
(28, 4, 'available'),
(29, 4, 'available'),
(30, 4, 'available'),
(31, 4, 'available'),
(32, 4, 'available'),
(33, 4, 'available'),
(34, 4, 'available'),
(35, 4, 'available'),
(36, 4, 'available'),
(37, 4, 'available'),
(38, 4, 'available'),
(39, 4, 'available'),
(40, 4, 'available'),
(41, 4, 'available'),
(42, 4, 'available'),
(43, 4, 'available'),
(44, 4, 'available'),
(45, 4, 'available'),
(46, 4, 'available'),
(47, 4, 'available'),
(48, 4, 'available'),
(49, 4, 'available'),
(50, 4, 'available'),
(51, 4, 'available'),
(52, 4, 'available'),
(53, 4, 'available'),
(54, 5, 'available'),
(55, 5, 'available'),
(56, 5, 'available'),
(57, 5, 'available'),
(58, 5, 'available'),
(59, 5, 'available'),
(60, 5, 'available'),
(61, 5, 'available'),
(62, 5, 'available'),
(63, 5, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `catalog`
--

CREATE TABLE `catalog` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `publisher` varchar(255) NOT NULL,
  `copies_available` int(11) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catalog`
--

INSERT INTO `catalog` (`book_id`, `title`, `author`, `year`, `publisher`, `copies_available`, `isbn`, `category`) VALUES
(1, 'DBMS', 'DR.Sadagopan', 2025, 'srm', 2, '332', 'database management'),
(2, 'Artificial Intelligence', 'Elon Musk', 2025, 'srm', 0, NULL, NULL),
(3, 'DAA', 'AL.Amutha', 2024, 'SRM', 21, NULL, NULL),
(4, 'Wings of Fire', 'APJ Abdul Kalam', 2010, 'Tamil Publications', 58, NULL, NULL),
(5, 'ghost stories', 'vijay', 2009, 'pk publications', 10, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `issuedbooks`
--

CREATE TABLE `issuedbooks` (
  `issued_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issuedbooks`
--

INSERT INTO `issuedbooks` (`issued_id`, `student_id`, `book_id`, `issue_date`, `due_date`) VALUES
(1, 6, 2, '2025-07-10', '2025-07-24'),
(2, 1, 4, '2025-07-10', '2025-07-25'),
(3, 1, 2, '2025-07-11', '2025-07-26'),
(4, 7, 4, '2025-07-11', '2025-07-26');

-- --------------------------------------------------------

--
-- Table structure for table `issuerequest`
--

CREATE TABLE `issuerequest` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issuerequest`
--

INSERT INTO `issuerequest` (`request_id`, `student_id`, `book_id`, `request_date`, `status`) VALUES
(1, 1, 3, '2025-05-06 09:53:30', 'approved'),
(2, 1, 2, '2025-05-06 09:55:54', 'approved'),
(3, 1, 1, '2025-05-06 10:41:45', 'pending'),
(4, 6, 2, '2025-07-10 23:00:03', 'approved'),
(5, 6, 3, '2025-07-10 23:04:35', 'pending'),
(6, 1, 4, '2025-07-10 23:55:02', 'approved'),
(7, 7, 4, '2025-07-11 13:14:59', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Notification_ID` int(11) NOT NULL,
  `Roll_No` int(11) DEFAULT NULL,
  `Message` text DEFAULT NULL,
  `sent_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`Notification_ID`, `Roll_No`, `Message`, `sent_date`) VALUES
(2, 1, 'your request is verified', '2025-05-06 10:09:06'),
(7, 1, 'your request is verified', '2025-05-06 10:17:49'),
(8, 1, 'your request is verified', '2025-05-06 10:28:53'),
(9, 1, 'your request is verified', '2025-05-06 10:31:37'),
(10, 1, 'your request is verified', '2025-05-06 10:32:23'),
(11, 1, 'your request is verified', '2025-05-06 10:33:03'),
(12, 1, 'issue confirmed', '2025-05-06 10:43:51'),
(13, 6, 'fees payment last date is tomorrow', '2025-07-10 22:51:08'),
(14, 7, 'you are enrolled in the library', '2025-07-11 13:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `penalty`
--

CREATE TABLE `penalty` (
  `Fine_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Book_ID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Status` enum('Pending','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `Publisher_ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Contact_No` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `roll_no` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `recommended_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`roll_no`, `title`, `author`, `reason`, `recommended_at`) VALUES
(6, 'DBMS', 'DR.Sadagopan', 'it is very useful in understanding the concept of database and mysql', '2025-07-10 23:14:40'),
(7, 'Wings of Fire', 'APJ Abdul Kalam', 'motivating book', '2025-07-11 13:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `renewal`
--

CREATE TABLE `renewal` (
  `Renew_ID` int(11) NOT NULL,
  `Roll_No` int(11) DEFAULT NULL,
  `Book_ID` int(11) DEFAULT NULL,
  `Renew` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `Stock_ID` int(11) NOT NULL,
  `Book_ID` int(11) DEFAULT NULL,
  `Total_Copies` int(11) NOT NULL,
  `Available_Copies` int(11) NOT NULL,
  `Issued_Copies` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`Stock_ID`, `Book_ID`, `Total_Copies`, `Available_Copies`, `Issued_Copies`) VALUES
(1, 3, 3, 1, 0),
(2, 3, 3, 1, 0),
(3, 4, 10, 40, 0),
(4, 3, 10, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `Roll_No` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`Roll_No`, `Name`, `Phone`, `Username`, `photo`) VALUES
(1, 'VINAYAK MISHRA', '9783343545', 'VINAYAK', NULL),
(3, 'Sagun', '8265446554', 'sagun1234', NULL),
(5, 'Harsh', '9242424242', 'harsh11', NULL),
(6, 'VINAYAK MISHRA', '600008786', 'vinayak11', NULL),
(7, 'Sahil Mishra', '896749374', 'sahil', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'VINAYAK', '$2y$10$y1oZf0PCzecBAfTAE7yHgO5wigAVSf//N23truMbz586Lgz59/mx6', 'user'),
(2, 'sagun', '$2y$10$veRJ23J/07Ezl3.Ajn8I4ujY5JZZ/Ec3W4TV/5Raz7nZ.q1WWqbdG', 'user'),
(3, 'sagun1234', '$2y$10$tltkco3CIbMFeirzLSMS8ejZ8yXTStMFw39O7q4CRLPmnIAIS0Gkm', 'user'),
(5, 'harsh11', '$2y$10$8D6vEV.Bn.jsw/DyK5nHwOI1yDZUUxGnv6s5WdPE7LHU2d711xyUm', 'user'),
(6, 'vinayak11', '$2y$10$9TYo3alVyDanqH0FfyZy8etaDXmiy4NIN.a4wdvbdaugI3OAVKN2a', 'user'),
(7, 'sahil', '$2y$10$MQuiSLm3T50/erT9p1EUku1ENdCATQ0xOeErqHt/DcRr7WnSm5z6e', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activerequests`
--
ALTER TABLE `activerequests`
  ADD PRIMARY KEY (`Issue_ID`),
  ADD KEY `Roll_No` (`Roll_No`),
  ADD KEY `Book_ID` (`Book_ID`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `bookentry`
--
ALTER TABLE `bookentry`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `catalog`
--
ALTER TABLE `catalog`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `issuedbooks`
--
ALTER TABLE `issuedbooks`
  ADD PRIMARY KEY (`issued_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `issuerequest`
--
ALTER TABLE `issuerequest`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Notification_ID`),
  ADD KEY `Roll_No` (`Roll_No`);

--
-- Indexes for table `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`Fine_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Book_ID` (`Book_ID`);

--
-- Indexes for table `publisher`
--
ALTER TABLE `publisher`
  ADD PRIMARY KEY (`Publisher_ID`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD KEY `roll_no` (`roll_no`);

--
-- Indexes for table `renewal`
--
ALTER TABLE `renewal`
  ADD PRIMARY KEY (`Renew_ID`),
  ADD KEY `Roll_No` (`Roll_No`),
  ADD KEY `Book_ID` (`Book_ID`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`Stock_ID`),
  ADD KEY `Book_ID` (`Book_ID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Roll_No`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activerequests`
--
ALTER TABLE `activerequests`
  MODIFY `Issue_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookentry`
--
ALTER TABLE `bookentry`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `catalog`
--
ALTER TABLE `catalog`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `issuedbooks`
--
ALTER TABLE `issuedbooks`
  MODIFY `issued_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `issuerequest`
--
ALTER TABLE `issuerequest`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `penalty`
--
ALTER TABLE `penalty`
  MODIFY `Fine_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renewal`
--
ALTER TABLE `renewal`
  MODIFY `Renew_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `Stock_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Roll_No` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activerequests`
--
ALTER TABLE `activerequests`
  ADD CONSTRAINT `activerequests_ibfk_1` FOREIGN KEY (`Roll_No`) REFERENCES `students` (`Roll_No`) ON DELETE CASCADE,
  ADD CONSTRAINT `activerequests_ibfk_2` FOREIGN KEY (`Book_ID`) REFERENCES `catalog` (`Book_ID`) ON DELETE CASCADE;

--
-- Constraints for table `bookentry`
--
ALTER TABLE `bookentry`
  ADD CONSTRAINT `bookentry_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `catalog` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `issuedbooks`
--
ALTER TABLE `issuedbooks`
  ADD CONSTRAINT `issuedbooks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`Roll_No`),
  ADD CONSTRAINT `issuedbooks_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `catalog` (`book_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Roll_No`) REFERENCES `students` (`Roll_No`) ON DELETE CASCADE;

--
-- Constraints for table `penalty`
--
ALTER TABLE `penalty`
  ADD CONSTRAINT `penalty_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `penalty_ibfk_2` FOREIGN KEY (`Book_ID`) REFERENCES `catalog` (`Book_ID`) ON DELETE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`roll_no`) REFERENCES `students` (`Roll_No`);

--
-- Constraints for table `renewal`
--
ALTER TABLE `renewal`
  ADD CONSTRAINT `renewal_ibfk_1` FOREIGN KEY (`Roll_No`) REFERENCES `students` (`Roll_No`) ON DELETE CASCADE,
  ADD CONSTRAINT `renewal_ibfk_2` FOREIGN KEY (`Book_ID`) REFERENCES `catalog` (`Book_ID`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`Book_ID`) REFERENCES `catalog` (`book_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
