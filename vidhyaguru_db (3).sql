-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 06:14 AM
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
-- Database: `vidhyaguru_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'mohit@gmail.com', '$2y$10$sHaRJWdHkLmFhSyd3QNTa.8WbkB.BXwEcRwpGgmSv.AMzQDzF1gGu', '2025-08-16 05:12:34');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `program_applied` varchar(100) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `session` varchar(20) DEFAULT NULL,
  `last_qualification` varchar(100) DEFAULT NULL,
  `percentage` varchar(20) DEFAULT NULL,
  `passing_year` int(4) DEFAULT NULL,
  `board` varchar(200) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `guardian_name` varchar(200) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `first_name`, `last_name`, `dob`, `gender`, `full_name`, `email`, `phone`, `program_applied`, `program`, `session`, `last_qualification`, `percentage`, `passing_year`, `board`, `address`, `city`, `state`, `pincode`, `guardian_name`, `guardian_phone`, `status`, `submitted_at`, `updated_at`) VALUES
(13, 3, 'test', 'user', '2004-09-22', 'male', 'test user', 'mohit@gmail.com', '1234567890', 'MCA', 'MCA', '2025-26', '12th', '5', 2022, 'gsheb', 'fgh', 'surat', 'gujarat', '394107', 'amratbhai', '1234567890', 'pending', '2025-10-01 07:31:47', '2025-10-04 04:54:27'),
(14, 3, 'nikhil', 'dabhi', '2025-10-08', 'male', 'nikhil dabhi', 'nikhil@gmail.com', '0123456789', 'BCA', 'BCA', '2025-26', '12th', '65', 2023, 'gsheb', 'nbjk', 'surat', 'gujarat', '394107', 'manoj', '1234567890', 'approved', '2025-10-01 07:35:52', '2025-10-01 07:36:30'),
(15, 3, 'mohit', 'Vanjara', '2004-09-22', 'male', 'mohit Vanjara', 'mohit@gmail.com', '1234567890', 'BTeach', 'BTeach', '2025-26', 'graduation', '8', 2025, 'sutex', 'vj', 'surat', 'gujarat', '394107', 'amratbhai', '1234567890', 'pending', '2025-10-04 04:51:43', '2025-10-04 04:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `admin_username` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `target_table` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `admin_username`, `action`, `target_table`, `target_id`, `details`, `created_at`) VALUES
(1, 'mohit@gmail.com', 'update', 'applications', 1, '{\"status\":\"approved\"}', '2025-08-14 15:01:34'),
(2, 'mohit@gmail.com', 'delete', 'applications', 1, '', '2025-08-14 15:02:17'),
(3, 'mohit@gmail.com', 'insert', 'users', 3, '{\"first_name\":\"mohit\",\"last_name\":\"Vanjara\",\"email\":\"mohit@gmail.com\",\"phone\":\"01234567890\",\"program\":\"bca\",\"registration_date\":\"2025-08-14 16:13:46\"}', '2025-08-14 16:13:46'),
(4, 'mohit@gmail.com', 'update', 'applications', 4, '{\"status\":\"approved\"}', '2025-08-15 16:31:14'),
(5, 'mohit@gmail.com', 'update', 'applications', 5, '{\"status\":\"rejected\"}', '2025-08-15 16:31:28'),
(6, 'mohit@gmail.com', 'delete', 'users', 5, '', '2025-08-15 16:31:59'),
(7, 'mohit@gmail.com', 'update', 'applications', 7, '{\"status\":\"approved\"}', '2025-08-16 16:31:09'),
(8, 'mohit@gmail.com', 'update', 'applications', 7, '{\"status\":\"pending\"}', '2025-08-16 16:31:25'),
(9, 'mohit@gmail.com', 'delete', 'users', 12, '', '2025-08-18 16:09:53'),
(10, 'mohit@gmail.com', 'delete', 'users', 13, '', '2025-08-18 16:09:56'),
(11, 'mohit@gmail.com', 'update', 'applications', 7, '{\"status\":\"approved\"}', '2025-08-23 04:00:40'),
(12, 'mohit@gmail.com', 'insert', 'users', 15, '{\"first_name\":\"palak\",\"last_name\":\"vanajara\",\"email\":\"palak@gmail.com\",\"phone\":\"4567852135\",\"program\":\"bcom\",\"registration_date\":\"2025-08-29 14:17:10\"}', '2025-08-29 14:17:10'),
(13, 'mohit@gmail.com', 'delete', 'users', 15, '', '2025-08-29 14:17:27'),
(14, 'mohit@gmail.com', 'delete', 'users', 14, '', '2025-08-29 14:17:31'),
(15, 'mohit@gmail.com', 'insert', 'applications', 8, '{\"full_name\":\"palak\",\"email\":\"sa@gmail.com\",\"phone\":\"7845682486\",\"program_applied\":\"bcom\",\"status\":\"pending\"}', '2025-08-29 14:18:53'),
(16, 'mohit@gmail.com', 'update', 'applications', 8, '{\"status\":\"approved\"}', '2025-08-29 14:19:12'),
(17, 'mohit@gmail.com', 'update', 'applications', 8, '{\"status\":\"rejected\"}', '2025-08-29 14:19:16'),
(18, 'mohit@gmail.com', 'update', 'applications', 8, '{\"status\":\"approved\"}', '2025-08-29 14:20:17'),
(19, 'mohit@gmail.com', 'update', 'applications', 7, '{\"status\":\"pending\"}', '2025-08-31 07:53:54'),
(20, 'mohit@gmail.com', 'update', 'applications', 5, '{\"status\":\"approved\"}', '2025-08-31 07:54:29'),
(21, 'mohit@gmail.com', 'delete', 'users', 6, '', '2025-09-02 16:19:49'),
(22, 'mohit@gmail.com', 'update', 'applications', 9, '{\"status\":\"rejected\"}', '2025-09-04 14:40:13'),
(23, 'mohit@gmail.com', 'update', 'applications', 9, '{\"status\":\"approved\"}', '2025-09-04 14:53:13'),
(24, 'mohit@gmail.com', 'update', 'applications', 9, '{\"status\":\"rejected\"}', '2025-09-04 14:59:01'),
(25, 'mohit@gmail.com', 'update', 'applications', 10, '{\"status\":\"approved\"}', '2025-09-04 16:19:08'),
(26, 'mohit@gmail.com', 'update', 'applications', 9, '{\"status\":\"approved\"}', '2025-09-04 16:20:04'),
(27, 'mohit@gmail.com', 'delete', 'applications', 9, '', '2025-09-04 16:22:06'),
(28, 'mohit@gmail.com', 'update', 'applications', 11, '{\"status\":\"approved\"}', '2025-09-04 16:22:41'),
(29, 'mohit@gmail.com', 'update', 'applications', 12, '{\"status\":\"approved\"}', '2025-09-06 06:38:02'),
(30, 'mohit@gmail.com', 'delete', 'applications', 10, '', '2025-09-06 06:38:19'),
(31, 'mohit@gmail.com', 'delete', 'users', 17, '', '2025-09-15 14:36:57'),
(32, 'mohit@gmail.com', 'delete', 'users', 18, '', '2025-09-29 14:18:17'),
(33, 'mohit@gmail.com', 'delete', 'users', 19, '', '2025-09-30 02:35:17'),
(34, 'mohit@gmail.com', 'delete', 'applications', 12, '', '2025-10-01 07:33:12'),
(35, 'mohit@gmail.com', 'delete', 'applications', 6, '', '2025-10-01 07:33:23'),
(36, 'mohit@gmail.com', 'delete', 'applications', 8, '', '2025-10-01 07:33:31'),
(37, 'mohit@gmail.com', 'update', 'applications', 14, '{\"status\":\"approved\"}', '2025-10-01 07:36:30'),
(38, 'mohit@gmail.com', 'update', 'applications', 13, '{\"status\":\"approved\"}', '2025-10-04 04:52:42'),
(39, 'mohit@gmail.com', 'update', 'applications', 15, '{\"status\":\"approved\"}', '2025-10-04 04:53:10'),
(40, 'mohit@gmail.com', 'update', 'applications', 13, '{\"status\":\"rejected\"}', '2025-10-04 04:53:31'),
(41, 'mohit@gmail.com', 'update', 'applications', 15, '{\"status\":\"rejected\"}', '2025-10-04 04:53:41'),
(42, 'mohit@gmail.com', 'update', 'applications', 13, '{\"status\":\"approved\"}', '2025-10-04 04:53:44'),
(43, 'mohit@gmail.com', 'update', 'applications', 13, '{\"status\":\"pending\"}', '2025-10-04 04:53:55'),
(44, 'mohit@gmail.com', 'update', 'applications', 13, '{\"status\":\"rejected\"}', '2025-10-04 04:54:02'),
(45, 'mohit@gmail.com', 'update', 'applications', 15, '{\"status\":\"pending\"}', '2025-10-04 04:54:25'),
(46, 'mohit@gmail.com', 'update', 'applications', 13, '{\"status\":\"pending\"}', '2025-10-04 04:54:27'),
(47, 'mohit@gmail.com', 'delete', 'applications', 16, '', '2025-11-07 04:28:21'),
(48, 'mohit@gmail.com', 'delete', 'users', 20, '', '2025-11-07 04:28:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `registration_date` datetime DEFAULT NULL,
  `additional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_info`)),
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `google_id` varchar(255) DEFAULT NULL,
  `given_name` varchar(100) DEFAULT NULL,
  `family_name` varchar(100) DEFAULT NULL,
  `picture` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `first_name`, `last_name`, `email`, `phone`, `program`, `password_hash`, `registration_date`, `additional_info`, `profile_picture`, `created_at`, `updated_at`, `google_id`, `given_name`, `family_name`, `picture`) VALUES
(3, NULL, NULL, 'mohit', 'Vanjara', 'mohit@gmail.com', '9923856273', 'MCA', '$2y$10$ARbb/6uBey/VUAbDBVkc/OpwYauPx9sYRS4VtXd2PlDjK.8Izwjtq', '2025-08-14 16:13:46', '{\"dateOfBirth\":\"2004-09-22\",\"gender\":\"\",\"address\":\"b-1, haridham society, amroli, surat\",\"emergencyContact\":\"9912345678\",\"preferences\":{\"emailNotifications\":true,\"smsNotifications\":false,\"marketingCommunications\":false,\"profileVisibility\":true,\"contactInfoSharing\":false}}', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4QCORXhpZgAATU0AKgAAAAgABAEPAAIAAAAMAAAAPgEyAAIAAAAUAAAASpADAAIAAAAUAAAAXpAEAAIAAAAUAAAAcgAAAABJZGVvZ3JhbSBBSQAyMDI1OjA2OjIxIDA0OjEyOjMzADIwMjU6MDY6MjEgMDQ6MTI6MzMAMjAyNTowNjoyMSAwNDoxMjozMwD/2wBDAAUDBAQE', '2025-08-14 16:13:46', '2025-10-04 04:53:44', NULL, NULL, NULL, NULL),
(4, NULL, NULL, 'mohit', 'Vanjara', 'mohit1@gmail.com', '1234567890', 'BCom', '$2y$10$ARbb/6uBey/VUAbDBVkc/OpwYauPx9sYRS4VtXd2PlDjK.8Izwjtq', '2025-08-15 10:12:22', '{\"dateOfBirth\":\"2007-06-05\",\"gender\":\"male\",\"preferences\":{\"emailNotifications\":true,\"smsNotifications\":false,\"marketingCommunications\":false,\"profileVisibility\":true,\"contactInfoSharing\":false},\"address\":\"a1 society\",\"emergencyContact\":\"9912345678\"}', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/2wBDAQMDAwQDBAgEBAgQCwkLEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBD/wAARCANVBQADASIAAhEB', '2025-08-15 04:42:22', '2025-08-31 07:54:29', NULL, NULL, NULL, NULL),
(7, NULL, NULL, 'meet', 'chauhan', 'meet@gmail.com', '1234567890', 'MCA', '$2y$10$IdQkonsPGqqw9ClVNPcRbOn4ZOkDigT.emDYfnp6THpbdZP5gsWd6', '2025-08-16 10:49:47', '[]', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCI+PHJlY3Qgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiM0Q0FGNTAiLz48dGV4dCB4PSI1MCIgeT0iNTUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjE2Ij5URVNUPC90ZXh0Pjwvc3ZnPg==', '2025-08-16 05:19:47', '2025-08-31 08:08:01', NULL, NULL, NULL, NULL),
(10, NULL, NULL, 'momos', 'sahbji', 'momo@gmail.com', '6666666666', NULL, '$2y$10$W/J7skrHxFQyIiBAEKTGIub24cYOroqT9ZktGj7sSTOSKQX91QRKO', '2025-08-16 17:59:29', '[]', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAASABIAAD/4QBMRXhpZgAATU0AKgAAAAgAAYdpAAQAAAABAAAAGgAAAAAAA6ABAAMAAAABAAEAAKACAAQAAAABAAADiaADAAQAAAABAAADiQAAAAD/7QA4UGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAAA4QklNBCUAAAAAABDUHYzZjwCyBOmACZjs+EJ+/8AAEQgDiQOJAwEiAAIRAQMR', '2025-08-16 12:29:29', '2025-08-16 12:31:20', NULL, NULL, NULL, NULL),
(11, NULL, NULL, 'urmil', 'kevadia', 'urmil@gmail.com', '1234567890', 'MTech', '$2y$10$b5WbkOiGP0eshRXkFf3rKex13wUkMBTfHCYGVHfPc7HHIJEDY1bvG', '2025-08-16 21:56:18', '{\"dateOfBirth\":\"2025-08-16\",\"gender\":\"male\",\"address\":\"dfsjkg dkfj  fkl\",\"emergencyContact\":\"9912345678\"}', NULL, '2025-08-16 16:26:18', '2025-08-16 16:29:33', NULL, NULL, NULL, NULL),
(16, NULL, NULL, 'nikhil', 'dabhi', 'nikhil@gmail.com', '2332132232', 'bca', '$2y$10$am50OabDNV5Ct7fIcj0u1eR93e5l8Abid.rakgd8YB1o/eXranhdK', '2025-08-31 13:30:25', '{\"dateOfBirth\":\"2005-06-15\",\"gender\":\"male\",\"address\":\"bv44 fis\",\"emergencyContact\":\"9912345678\"}', NULL, '2025-08-31 08:00:25', '2025-09-06 06:38:02', NULL, NULL, NULL, NULL),
(21, NULL, NULL, 'test', 'user1', 'user1@gmail.com', '1234567890', NULL, '$2y$10$JwhGJu8aVFCOOXDS43LhSe7V5Hwi/ph8/wJ.6vQXtbyIE8gjqQGLK', '2025-11-06 19:56:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'mohitvanjara', 'Mohit Vanjara', NULL, NULL, 'mohitvanjara7276@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 04:29:50', '2025-11-07 05:08:32', '114662686100555794731', 'Mohit', 'Vanjara', 'https://lh3.googleusercontent.com/a/ACg8ocIXwxXavzgshumEvIax6xqkgSN9O8X9vrrJBd71-Tf64G-kig=s96-c'),
(23, NULL, NULL, 'mohit', 'test', 'user2@gmail.com', '1234567890', NULL, '$2y$10$C0KDwh/MHWnVu7l6lyKTs.g63SQ4b8xiO7bBMKXoXxqL3ZtCdriSy', '2025-11-07 10:30:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_google_id` (`google_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
