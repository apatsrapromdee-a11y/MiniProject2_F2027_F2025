-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 11:47 AM
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
-- Database: `kidzania_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_no` varchar(10) NOT NULL,
  `booking_date` date NOT NULL,
  `infants` int(11) DEFAULT 0,
  `toddlers` int(11) DEFAULT 0,
  `kids` int(11) DEFAULT 0,
  `adults` int(11) DEFAULT 0,
  `senior_citizens` int(11) DEFAULT 0,
  `disabled` int(11) DEFAULT 0,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `status` enum('Pending','Paid','Confirmed') DEFAULT 'Pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `booking_no`, `booking_date`, `infants`, `toddlers`, `kids`, `adults`, `senior_citizens`, `disabled`, `total_price`, `status`, `receipt_path`, `created_at`) VALUES
(1, 3, 'KZ001', '2026-04-13', 0, 2, 1, 1, 0, 0, 214.00, 'Confirmed', 'uploads/receipts/receipt_KZ001_1775811444.png', '2026-04-10 08:55:48'),
(2, 4, 'KZ002', '2026-04-14', 0, 1, 1, 1, 0, 0, 173.00, 'Confirmed', 'uploads/receipts/receipt_KZ002_1775811726.png', '2026-04-10 09:00:07'),
(3, 4, 'KZ003', '2026-04-15', 0, 0, 4, 4, 0, 0, 528.00, 'Confirmed', 'uploads/receipts/receipt_KZ003_1775811764.png', '2026-04-10 09:02:29'),
(4, 5, 'KZ004', '2026-04-16', 0, 1, 1, 1, 1, 0, 208.00, 'Paid', 'uploads/receipts/receipt_KZ004_1775811885.png', '2026-04-10 09:04:34'),
(5, 5, 'KZ005', '2026-04-30', 0, 1, 3, 2, 1, 0, 425.00, 'Pending', NULL, '2026-04-10 09:05:04'),
(6, 6, 'KZ006', '2026-04-19', 1, 1, 2, 2, 0, 0, 305.00, 'Paid', 'uploads/receipts/receipt_KZ006_1775812310.pdf', '2026-04-10 09:10:55'),
(7, 6, 'KZ007', '2026-04-21', 0, 2, 2, 2, 0, 0, 346.00, 'Pending', NULL, '2026-04-10 09:12:50'),
(8, 7, 'KZ008', '2026-04-15', 0, 1, 2, 2, 1, 0, 340.00, 'Pending', NULL, '2026-04-10 09:15:04'),
(9, 8, 'KZ009', '2026-04-16', 0, 1, 2, 1, 0, 0, 258.00, 'Pending', NULL, '2026-04-10 09:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `regdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `phone`, `role`, `regdate`) VALUES
(1, 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', 'admin', '2026-04-10'),
(2, 'Apatsra', 'Apatsra', '$2y$10$K/h7RqXC5Lyq8z0Alm/3he/GwV9z6gsQvfCl1Fdq1uQLn66cFHcDe', '0179469531', 'admin', '2026-04-10'),
(3, 'Farisha', 'Farisha', '$2y$10$qiT03N3Pcj7Fk6B2WDDQ1OQA4HBx1UlZpYotJu54lFiBTJZeHeSNi', '0123456789', 'customer', '2026-04-10'),
(4, 'Jasmine', 'Jasmine', '$2y$10$o47e4ZhD8zCQe.qZ4KqNeurNMdYy7o63WsJMBm9uRoP7Yqlrx9GGm', '01130012563', 'customer', '2026-04-10'),
(5, 'Sarah', 'Sarah', '$2y$10$IjwB/j4ezd2M2VUoEDfsuOUZYNc8jC0BP0Tb4ZzRDnr8vfMjxwqk2', '0123455432', 'customer', '2026-04-10'),
(6, 'Ahmad', 'Ahmad', '$2y$10$EdVBHi/pIBUSgMR.FCDnVus8pvTTdvyLwC4pE5jGc.SERp2PMpO3K', '0112233452', 'customer', '2026-04-10'),
(7, 'Kamal', 'Kamal', '$2y$10$gBCKoQoIzAQTLGVljFM6XuCq8Sqrr/GPXQ5g/FxUoXuaNaE8BjyZy', '0155342378', 'customer', '2026-04-10'),
(8, 'Abu', 'Abu', '$2y$10$G9jcswDTKPYyR0L8WqomsuesO75olDmo0I.6nwja5LQj2z3Mcwn8u', '0198787876', 'customer', '2026-04-10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
