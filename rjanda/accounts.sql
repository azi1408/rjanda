-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 06:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `accounts`
--

-- --------------------------------------------------------

--
-- Table structure for table `accountstb`
--

CREATE TABLE `accountstb` (
  `userID` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catering_orders`
--

CREATE TABLE `catering_orders` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `guests` int(11) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catering_orders`
--

INSERT INTO `catering_orders` (`id`, `name`, `event_date`, `guests`, `address`, `latitude`, `longitude`, `special_requests`, `created_at`, `payment_method`, `user_id`, `status`) VALUES
(14, 'argea roldan', '2025-04-23', 23, '23', NULL, NULL, NULL, '2025-04-20 04:47:07', 'GCash', 6, 'done');

-- --------------------------------------------------------

--
-- Table structure for table `registertb`
--

CREATE TABLE `registertb` (
  `userid` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `contact` int(10) UNSIGNED DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `checkpass` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registertb`
--

INSERT INTO `registertb` (`userid`, `name`, `username`, `contact`, `password`, `checkpass`, `role`) VALUES
(6, 'john arfel cortes', 'helloworld12@gmail.com', 4294967295, '$2y$10$7FbMLj1cJswWViRCnmcIMuwN7QFh/rykecnzHNE/1zYvK4B7PYoZq', 'helloworld', 'user'),
(8, 'adminazi', 'admin123@gmail.com', 4294967295, '$2y$10$Sk9uepQR.rw1ksSaT/OxkO/AEAHbtK6Na5dLKalnySYyFurAsIUd2', 'helloworldgoodbye', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accountstb`
--
ALTER TABLE `accountstb`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `catering_orders`
--
ALTER TABLE `catering_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registertb`
--
ALTER TABLE `registertb`
  ADD PRIMARY KEY (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accountstb`
--
ALTER TABLE `accountstb`
  MODIFY `userID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catering_orders`
--
ALTER TABLE `catering_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `registertb`
--
ALTER TABLE `registertb`
  MODIFY `userid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
