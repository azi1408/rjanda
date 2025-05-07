-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 02:36 AM
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
  `package_name` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `events_date` date NOT NULL,
  `guests` int(11) NOT NULL,
  `address` text NOT NULL,
  `dishes` text DEFAULT NULL,
  `desserts` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `review` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catering_orders`
--

INSERT INTO `catering_orders` (`id`, `package_name`, `name`, `events_date`, `guests`, `address`, `dishes`, `desserts`, `latitude`, `longitude`, `special_requests`, `created_at`, `payment_method`, `user_id`, `status`, `total_price`, `review`) VALUES
(71, 'Basic Package', 'argea roldan', '2025-05-17', 121, '2121', 'Chicken Adobo, Beef Caldereta, Pancit Canton', 'Mango Float', 14.00000000, NULL, NULL, '2025-05-01 05:39:58', 'Paid', 10, 'done', NULL, 'helloworld muntik na madelete haha'),
(72, 'Basic Package', 'Juan Dela Cruz', '2025-05-16', 21, '21', 'Beef Caldereta, Pancit Canton, Kare-Kare', 'Buko Pandan', 14.00000000, NULL, NULL, '2025-05-01 05:40:14', NULL, 10, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registertb`
--

CREATE TABLE `registertb` (
  `userid` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `checkpass` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registertb`
--

INSERT INTO `registertb` (`userid`, `name`, `username`, `contact`, `password`, `checkpass`, `role`) VALUES
(6, 'Arfel Cortes', 'helloworld12@gmail.com', '09913509650', '$2y$10$fW2phfgOrZLB5TbL/U8etOJFYxTyzSOkxbKWVyEMzmsnTzKG1EVIi', 'helloworld', 'admin'),
(8, 'adminazi', 'admin123@gmail.com', '4294967295', '$2y$10$Sk9uepQR.rw1ksSaT/OxkO/AEAHbtK6Na5dLKalnySYyFurAsIUd2', 'helloworldgoodbye', 'admin'),
(10, 'testaccount', 'testaccount123@gmail.com', '096846456465', '$2y$10$zwuOfEFZ/0GJjzYz8NTRO.rbu9epMOsd9lTZQoGaklbdFEeiszMyC', 'helloworld', 'user');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `registertb`
--
ALTER TABLE `registertb`
  MODIFY `userid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
