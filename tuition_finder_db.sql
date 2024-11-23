-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql302.infinityfree.com
-- Generation Time: Nov 18, 2024 at 12:22 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_37570428_tuition_finder_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', 'testing', NULL, '2024-10-11 14:13:52'),
(3, 'admin1', '$2y$10$cQ8PTsCs1Yk5R3eQ4u7GyuChL98WZoZhyqlcuQoDJHF5GqYHin0QS', 'admin@example.com', '2024-10-20 06:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tuition_center_id` int(11) DEFAULT NULL,
  `appointment_datetime` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','cancelled','rescheduled') NOT NULL DEFAULT 'pending',
  `cancellation_reason` text DEFAULT NULL,
  `reschedule_reason` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `tuition_center_id`, `appointment_datetime`, `admin_id`, `created_at`, `reason`, `status`, `cancellation_reason`, `reschedule_reason`, `completed_at`) VALUES
(7, 1, 6, '2024-11-06 15:30:00', NULL, '2024-11-03 09:05:05', 'testing', 'cancelled', 'Changed Mind', 'Testing', '2024-11-03 01:25:51'),
(8, 1, 6, '2024-11-04 15:30:00', NULL, '2024-11-03 09:39:02', 'testing', 'cancelled', 'Schedule Conflict', NULL, '2024-11-03 01:39:11'),
(9, 2, 9, '2024-11-05 15:30:00', NULL, '2024-11-03 10:42:07', 'testing', '', NULL, NULL, '2024-11-05 04:43:55'),
(10, 2, 5, '2024-11-04 14:30:00', NULL, '2024-11-03 12:27:03', 'test', 'cancelled', 'Found Alternative', NULL, '2024-11-05 04:41:53'),
(11, 4, 5, '2024-11-14 12:00:00', NULL, '2024-11-11 19:46:27', 'Get more detail information', 'cancelled', 'testing', NULL, NULL),
(12, 4, 5, '2024-11-14 11:00:00', NULL, '2024-11-14 03:05:47', 'hi', '', NULL, NULL, '2024-11-13 19:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tuition_center_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `tuition_center_id`, `created_at`) VALUES
(11, 1, 9, '2024-11-14 03:20:56');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` enum('good','neutral','bad') NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 'good', '', '2024-10-22 13:56:50'),
(2, NULL, 'neutral', '', '2024-11-01 10:28:52'),
(3, NULL, 'good', '', '2024-11-01 10:48:34'),
(4, NULL, 'good', 'Nice function!', '2024-11-03 10:17:59'),
(5, NULL, 'good', 'If can have a google map to see the surrounding will be good', '2024-11-11 03:50:57'),
(6, NULL, 'good', 'very clean and easy to use, can use it directly without login or membership ', '2024-11-14 12:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notification_type` enum('favorite','upcoming','confirmation') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `notification_type`) VALUES
(1, 1, 'An admin has replied to your review.', 1, '2024-10-23 08:49:41', 'favorite'),
(2, 1, 'Your appointment on 2024-10-28 11:30 has been marked as completed.', 1, '2024-10-27 11:36:36', 'favorite'),
(3, 1, 'Your appointment on 2024-10-28 11:30 has been marked as completed.', 1, '2024-10-27 11:37:34', 'favorite'),
(4, 1, 'Your appointment on 2024-10-28 11:30 has been marked as completed.', 1, '2024-10-27 11:41:46', 'favorite'),
(5, 1, 'Your appointment on 2024-10-28 11:30 has been marked as completed.', 1, '2024-10-28 05:27:02', 'favorite'),
(6, 2, 'An admin has replied to your review.', 0, '2024-11-02 17:21:56', 'favorite'),
(7, 2, 'Admin liked your review.', 0, '2024-11-03 10:15:38', 'favorite'),
(8, 2, 'Your appointment on 2024-11-04 11:30 has been marked as completed.', 0, '2024-11-03 10:19:08', 'favorite'),
(9, 2, 'Your appointment on 2024-11-04 11:30 has been marked as completed.', 1, '2024-11-03 10:25:09', 'favorite'),
(10, 2, 'Your appointment on 2024-11-04 11:30 has been marked as completed.', 1, '2024-11-03 10:39:48', 'favorite'),
(11, 2, 'Your appointment on 2024-11-04 11:30 has been marked as completed.', 1, '2024-11-03 10:42:25', 'favorite'),
(17, 4, 'Your appointment on 2024-11-14 12:00 has been marked as completed.', 0, '2024-11-14 02:56:00', 'favorite'),
(19, 4, 'Your appointment has been cancelled by the admin. Reason: testing', 0, '2024-11-14 03:23:46', 'favorite');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tuition_center_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `tuition_center_id`, `rating`, `comment`, `created_at`, `reply`, `liked_by_admin`, `approved`) VALUES
(16, 2, 5, 5, 'Good\n', '2024-11-02 17:10:20', 'Thanks!', 1, 1),
(17, 1, 6, 4, 'Nice', '2024-11-03 09:41:34', NULL, 0, 1),
(20, 4, 5, 5, 'Nice', '2024-11-11 20:06:26', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tuition_centers`
--

CREATE TABLE `tuition_centers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_tags` varchar(255) DEFAULT NULL,
  `teaching_language` varchar(255) DEFAULT 'English,Bahasa Malaysia,Chinese',
  `price_range` varchar(50) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tuition_centers`
--

INSERT INTO `tuition_centers` (`id`, `name`, `address`, `city`, `latitude`, `longitude`, `image`, `created_at`, `course_tags`, `teaching_language`, `price_range`, `contact`, `description`) VALUES
(5, 'Infinity Tuition Centre', '3-1, Lorong Batu Nilam 34c, Bandar Bukit Tinggi 2, 41200 Klang, Selangor, Malaysia', NULL, '2.999253', '101.431832', 'uploads/672496ead5e88.jpg', '2024-11-01 08:46:09', 'Math,Science,English,Biology,Chemistry,Physics,Add Math,Account,History,Economy', 'English,Chinese', '65', '0166736132', 'Infinity Tuition Centre focuses on the quality and quality of teaching, so every teacher will focus 100% on teaching in class. In order to ensure that every student is paid attention to by the teacher, Infinity Tuition Center adopts small class teaching.\r\n\r\n[Quality is Our Priority] Teachers are very willing to accept suggestions from parents and classmates ðŸ¥° Thank you for your support ðŸ¤—'),
(6, 'Catalyst Education & Daycare Centre', '27-3-2, Jalan Setia Prima A U13/A, Setia Alam, 40170 Shah Alam, Selangor, Malaysia', NULL, '3.096862', '101.444456', 'uploads/Picture1.jpg', '2024-11-02 14:47:28', 'Math,Science,English,Biology,Chemistry,Physics,Add Math,Account,History,Economy,Malay', 'English,Chinese', 'RM50', '012-238 9400', 'The Catalyst Education Centre has been operating for over 15 years, providing education according to student\'s aptitude, incorporating technology, and making learning available anytime and anywhere.\r\n\r\nThe centre has gained the support and trust of many parents and students.'),
(7, 'Pusat Tuisyen Era Tinta (ET)', 'No. 10-3-1, Third Floor, Jalan Setia Prima C U13/C, Setia Alam, 40170 Shah Alam, Selangor, Malaysia', NULL, '3.094493', '101.445422', 'uploads/Picture2.png', '2024-11-02 14:50:35', 'Math,Science,English,History,Malay', 'English', 'RM55', '011-1653 4918', 'The Well Known No. 1 Specialist Tuition Centre for Primary, Secondary, IGCSE, SPM & STPM since 1988'),
(8, 'VBEST Tuition Centre @ Setia Alam', '7-3-1, Jalan Setia Prima R U13/R, Setia Alam, Seksyen U13, 40170 Shah Alam, Selangor, Malaysia', NULL, '3.100626', '101.444281', 'uploads/Picture3.png', '2024-11-02 14:57:34', 'Math,Science,English,Biology,Chemistry,Physics,Add Math,Account,Economy', 'English', 'RM220', '016-351 9588', 'VBEST Tuition Centre @ Setia Alam offers high-quality tutoring services specializing in various subjects, including Mathematics, Science, and English, for primary and secondary students. Known for its dedicated teachers and personalized approach, VBEST aims to enhance student performance through small class sizes and a results-driven curriculum. Located in a convenient area of Setia Alam, the center provides a supportive learning environment with well-equipped facilities designed to foster academic excellence.\r\n\r\nFor more information, visit their website: https://www.vbest.edu.my/'),
(9, 'AZ Smart Learning Centre', 'No.15, 2nd floor, Jalan SS2/67, Petaling Jaya, Selangor, Malaysia', NULL, '3.119535', '101.622114', 'uploads/Screenshot 2024-11-03 180619.png', '2024-11-03 10:09:55', 'Math,Science,English,Biology,Chemistry,Physics,Add Math,Account,History,Malay', 'English,Chinese', 'RM30-RM40', '010-293 8798', 'We are a well trusted Learning Centre which is providing education services for primary and secondary school students. We are Experts for Pt3 and SPM.\r\n- More then 13 years teaching experience\r\n- Confirmed improve \r\n- Confirmed pass in result \r\n- SCORE A');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `user_role` enum('admin','user') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `fav_update` tinyint(1) DEFAULT 0,
  `upcoming_appointment` tinyint(1) DEFAULT 0,
  `appointment_confirmation` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `password`, `email`, `user_role`, `created_at`, `phone_number`, `address`, `fav_update`, `upcoming_appointment`, `appointment_confirmation`) VALUES
(1, 'xyme', 'Cheong', 'En Ying', '$2y$10$F88Tx0/MF8w4zHbG5Xeev.V8NII7qEizRJ5SkPPrikYfpdqNeUfMG', 'enyingcheong@gmail.com', 'user', '2024-10-17 05:58:45', '123', 'dsds', 0, 0, 1),
(2, 'john', 'John', 'Doe', '$2y$10$FylHnm.c1NjkCpbtBQndoeItFCI/7CW1w3DFgBO/ZutPQ2SL/iBJW', 'john@example.com', 'user', '2024-11-01 08:55:35', '0123456789', '12, jalan ss2/64', 1, 0, 0),
(3, 'jamon', NULL, NULL, '$2y$10$V2l9CyHSj7wXG641dc8j3u8xzW0cmVc7rktwPH21bJZ2n00tDkxzS', 'j22039051@student.newinti.edu.my', 'user', '2024-11-03 13:59:17', NULL, NULL, 0, 0, 0),
(4, 'edward', NULL, NULL, '$2y$10$ylnPilQHlMJZdRAshwuDfeHtSdBcpd0o2ZEDSp9rIsS.ckfCKCvWy', 'j23039376@student.newinti.edu.my', 'user', '2024-11-11 19:42:38', NULL, NULL, 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admin_id` (`admin_id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_tuition_center_id` (`tuition_center_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_favorite` (`user_id`),
  ADD KEY `fk_tuition_center_favorite` (`tuition_center_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_ibfk_1` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tuition_centers`
--
ALTER TABLE `tuition_centers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lat_lng` (`latitude`,`longitude`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuition_centers`
--
ALTER TABLE `tuition_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tuition_center_id` FOREIGN KEY (`tuition_center_id`) REFERENCES `tuition_centers` (`id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`tuition_center_id`) REFERENCES `tuition_centers` (`id`),
  ADD CONSTRAINT `fk_user_favorite` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
