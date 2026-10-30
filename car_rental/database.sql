-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2026 at 08:00 PM
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
-- Database: `booking_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `last_nudge_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `customer_id`, `staff_id`, `service_id`, `start_datetime`, `end_datetime`, `status`, `last_nudge_at`, `created_at`) VALUES
(1, 4, 2, 1, '2026-10-30 10:00:00', '2026-10-30 10:30:00', 'confirmed', NULL, '2026-10-30 00:25:09'),
(2, 4, 3, 3, '2026-10-31 14:00:00', '2026-10-31 15:00:00', 'confirmed', NULL, '2026-10-30 00:25:09'),
(6, 6, 5, 1, '2026-10-30 21:30:00', '2026-10-30 22:00:00', 'confirmed', NULL, '2026-10-30 00:31:58');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  `link` varchar(200) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 5, 'Welcome wanja! An admin created your staff account. You can log in with this email.', 'admin/appointments.php', 1, '2026-10-30 00:30:18'),
(2, 6, 'Your request for Haircut with wanja on Fri 30 Oct 2026, 21:30 was received. It is waiting for approval.', 'my_appointments.php', 1, '2026-10-30 00:31:58'),
(3, 5, 'New booking request from sage: Haircut on Fri 30 Oct 2026, 21:30. Please review it.', 'admin/appointments.php', 1, '2026-10-30 00:31:58'),
(4, 1, 'New booking request from sage: Haircut with wanja on Fri 30 Oct 2026, 21:30.', 'admin/appointments.php', 1, '2026-10-30 00:31:58'),
(5, 4, 'Your Consultation appointment with Omar Barber on Sat 31 Oct 2026, 14:00 has been approved ✅.', 'my_appointments.php', 0, '2026-10-30 00:55:34'),
(6, 1, 'Omar Barber approved ✅ the Consultation appointment for Demo Customer on Sat 31 Oct 2026, 14:00.', 'admin/appointments.php', 1, '2026-10-30 00:55:34'),
(7, 6, 'Your Haircut appointment with wanja on Fri 30 Oct 2026, 21:30 has been approved ✅.', 'my_appointments.php', 1, '2026-10-30 13:38:54'),
(8, 5, 'Admin approved ✅ the Haircut appointment for sage on Fri 30 Oct 2026, 21:30.', 'admin/appointments.php', 0, '2026-10-30 13:38:55');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `duration_min` int(11) NOT NULL,
  `price` decimal(8,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `duration_min`, `price`) VALUES
(1, 'Haircut', 30, 25.00),
(2, 'Hair Colouring', 90, 80.00),
(3, 'Consultation', 60, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff_services`
--

CREATE TABLE `staff_services` (
  `staff_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_services`
--

INSERT INTO `staff_services` (`staff_id`, `service_id`) VALUES
(2, 1),
(2, 2),
(2, 3),
(3, 1),
(3, 2),
(3, 3),
(5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `time_off`
--

CREATE TABLE `time_off` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_off`
--

INSERT INTO `time_off` (`id`, `staff_id`, `start_datetime`, `end_datetime`) VALUES
(1, 3, '2026-11-05 00:00:00', '2026-11-08 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-10-30 00:25:08'),
(2, 'Sara Stylist', 'sara@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '2026-10-30 00:25:08'),
(3, 'Omar Barber', 'omar@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '2026-10-30 00:25:08'),
(4, 'Demo Customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2026-10-30 00:25:08'),
(5, 'wanja', 'sethwanja07@gmail.com', '$2y$10$ZctzXi6s93Nkqhr94/2r6.lYj3AH51rCy1QeeY1eLkeY5W40jd4re', 'staff', '2026-10-30 00:30:17'),
(6, 'sage', 'sage@gmail.com', '$2y$10$ZRrknxoXejksQA1C5FS5ROlourW.mNYPds0V9/3uGYLkWFluqI/Ha', 'customer', '2026-10-30 00:31:05');

-- --------------------------------------------------------

--
-- Table structure for table `working_hours`
--

CREATE TABLE `working_hours` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `working_hours`
--

INSERT INTO `working_hours` (`id`, `staff_id`, `weekday`, `start_time`, `end_time`) VALUES
(1, 2, 1, '09:00:00', '17:00:00'),
(2, 2, 2, '09:00:00', '17:00:00'),
(3, 2, 3, '09:00:00', '17:00:00'),
(4, 2, 4, '09:00:00', '17:00:00'),
(5, 2, 5, '09:00:00', '17:00:00'),
(6, 3, 1, '09:00:00', '17:00:00'),
(7, 3, 2, '09:00:00', '17:00:00'),
(8, 3, 3, '09:00:00', '17:00:00'),
(9, 3, 4, '09:00:00', '17:00:00'),
(10, 3, 5, '09:00:00', '17:00:00'),
(11, 5, 1, '09:00:00', '22:00:00'),
(12, 5, 2, '09:00:00', '22:00:00'),
(13, 5, 3, '09:00:00', '22:00:00'),
(14, 5, 4, '09:00:00', '22:00:00'),
(15, 5, 5, '09:00:00', '22:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staff_time` (`staff_id`,`start_datetime`,`end_datetime`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_services`
--
ALTER TABLE `staff_services`
  ADD PRIMARY KEY (`staff_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `time_off`
--
ALTER TABLE `time_off`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `working_hours`
--
ALTER TABLE `working_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `time_off`
--
ALTER TABLE `time_off`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `working_hours`
--
ALTER TABLE `working_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_services`
--
ALTER TABLE `staff_services`
  ADD CONSTRAINT `staff_services_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_off`
--
ALTER TABLE `time_off`
  ADD CONSTRAINT `time_off_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `working_hours`
--
ALTER TABLE `working_hours`
  ADD CONSTRAINT `working_hours_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
