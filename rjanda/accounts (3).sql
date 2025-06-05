-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 01:21 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_dashboard_stats` ()   BEGIN
    -- Clear old data
    DELETE FROM dashboard WHERE date_recorded < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
    
    -- Insert daily stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        DATE(order_date) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_price) as total_sales,
        'daily' as period_type,
        DATE_FORMAT(order_date, '%Y-%m-%d') as period_value
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE(order_date);

    -- Insert weekly stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        MAX(DATE(order_date)) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_price) as total_sales,
        'weekly' as period_type,
        DATE_FORMAT(order_date, '%Y-%u') as period_value
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE_FORMAT(order_date, '%Y-%u');

    -- Insert monthly stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        MAX(DATE(order_date)) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_price) as total_sales,
        'monthly' as period_type,
        DATE_FORMAT(order_date, '%Y-%m') as period_value
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m');
END$$

DELIMITER ;

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
  `review` text DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `selected_dishes` text DEFAULT NULL,
  `selected_desserts` text DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lng` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catering_orders`
--

INSERT INTO `catering_orders` (`id`, `package_name`, `name`, `events_date`, `guests`, `address`, `dishes`, `desserts`, `latitude`, `longitude`, `special_requests`, `created_at`, `payment_method`, `user_id`, `status`, `total_price`, `review`, `package_id`, `customer_name`, `event_type`, `guest_count`, `selected_dishes`, `selected_desserts`, `lat`, `lng`) VALUES
(71, 'Basic Package', 'argea roldan', '2025-05-17', 121, '2121', 'Chicken Adobo, Beef Caldereta, Pancit Canton', 'Mango Float', 14.00000000, NULL, NULL, '2025-05-01 05:39:58', 'Paid', 10, 'done', NULL, 'helloworld muntik na madelete haha', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `sender_type`, `created_at`, `is_read`) VALUES
(1, 10, 'hi can i get a custom order', 'user', '2025-05-12 16:38:55', 1),
(2, 10, 'hello ano po yun', 'admin', '2025-05-12 16:44:42', 0),
(3, 10, 'yun', 'user', '2025-05-12 16:44:59', 1),
(4, 10, 'would you like me to create a custom order for you?', 'admin', '2025-05-12 17:33:58', 0),
(5, 10, 'yes please i have a budget of 5000 for a birthday party', 'user', '2025-05-12 17:34:15', 1),
(6, 10, 'hi guys', 'user', '2025-05-12 17:41:34', 1),
(7, 10, 'hello', 'user', '2025-05-12 17:41:59', 1),
(8, 10, 'test', 'user', '2025-05-12 17:43:09', 1),
(9, 10, 'hiii', 'admin', '2025-05-12 17:43:21', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dashboard`
--

CREATE TABLE `dashboard` (
  `id` int(11) NOT NULL,
  `date_recorded` date DEFAULT NULL,
  `total_orders` int(11) DEFAULT NULL,
  `completed_orders` int(11) DEFAULT NULL,
  `total_sales` decimal(10,2) DEFAULT NULL,
  `period_type` enum('daily','weekly','monthly') DEFAULT NULL,
  `period_value` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `date_availability`
--

CREATE TABLE `date_availability` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('available','unavailable') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `date_availability`
--

INSERT INTO `date_availability` (`id`, `date`, `status`) VALUES
(1, '2025-05-14', 'available'),
(2, '2025-05-21', 'unavailable'),
(3, '2025-05-30', 'available'),
(4, '2025-05-13', 'unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `guest_count` int(11) NOT NULL,
  `selected_dishes` text DEFAULT NULL,
  `selected_desserts` text DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_date` date DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `reviews` text DEFAULT NULL,
  `rating` int(10) DEFAULT NULL,
  `total_price` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `package_id`, `customer_name`, `address`, `event_type`, `guest_count`, `selected_dishes`, `selected_desserts`, `lat`, `lng`, `created_at`, `order_date`, `user_id`, `status`, `payment_method`, `reviews`, `rating`, `total_price`) VALUES
(6, 8, 'argea test', 'helloworld goodbye', 'birthdays', 21, 'adobo,sinigang', 'manggo float', 14.5909659, 120.9960148, '2025-05-12 07:04:27', '2025-05-12', 10, 'done', 'Paid', 'ganda dito promise solid sa uulitin!', NULL, NULL),
(7, 8, 'review test', 'helloworld', 'binyag', 21, 'adobo,sinigang', 'manggo float', 14.5711776, 121.0028813, '2025-05-12 08:01:16', '2025-05-12', 10, 'done', 'Paid', 'lupet solid ', 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `dishes` text NOT NULL,
  `desserts` text NOT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_dishes` int(11) DEFAULT 0,
  `max_desserts` int(11) DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `package_name`, `price`, `dishes`, `desserts`, `status`, `created_at`, `max_dishes`, `max_desserts`, `description`) VALUES
(8, 'basic package', 2000.00, 'adobo, sinigang, tinola', 'manggo float, mango shake, coffee jelly', 'available', '2025-05-12 03:35:14', 2, 1, 'test only');

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
(6, 'Arfel Cortes', 'helloworld12@gmail.com', '09913509650', '$2y$10$fW2phfgOrZLB5TbL/U8etOJFYxTyzSOkxbKWVyEMzmsnTzKG1EVIi', 'helloworld', 'moderator'),
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
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `dashboard`
--
ALTER TABLE `dashboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dashboard_date` (`date_recorded`),
  ADD KEY `idx_dashboard_period` (`period_type`,`period_value`);

--
-- Indexes for table `date_availability`
--
ALTER TABLE `date_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `dashboard`
--
ALTER TABLE `dashboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `date_availability`
--
ALTER TABLE `date_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `registertb`
--
ALTER TABLE `registertb`
  MODIFY `userid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `registertb` (`userid`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `update_dashboard_daily` ON SCHEDULE EVERY 1 DAY STARTS '2025-05-13 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL update_dashboard_stats()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
