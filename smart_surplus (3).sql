-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 07:44 AM
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
-- Database: `smart_surplus`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Sumit', 'sb@gmail.com', '$2y$10$Ltv47Q71XfRKfpO2ntqbF.oJkO/5Ikiz/xiWDqhVh0Ez0SNwSCUQK', '2025-08-22 09:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `claimed_at` datetime DEFAULT current_timestamp(),
  `status` enum('Booked','Pickup Pending','Expired','Completed') DEFAULT 'Booked',
  `total_bill` double(10,2) NOT NULL,
  `pickup_window` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `food_id`, `recipient_id`, `quantity`, `claimed_at`, `status`, `total_bill`, `pickup_window`) VALUES
(26, 1, 3, 1, '2025-08-22 19:07:14', 'Pickup Pending', 0.00, NULL),
(27, 1, 3, 1, '2025-08-22 19:22:42', 'Pickup Pending', 0.00, '07:22 PM - 08:22 PM'),
(28, 1, 3, 1, '2025-08-22 19:26:24', 'Pickup Pending', 0.00, '07:26 PM - 08:26 PM'),
(29, 18, 8, 2, '2025-08-31 13:52:55', 'Completed', 60.00, '01:52 PM - 02:52 PM');

-- --------------------------------------------------------

--
-- Table structure for table `food_listings`
--

CREATE TABLE `food_listings` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `food_title` varchar(100) NOT NULL,
  `food_description` text DEFAULT NULL,
  `food_type` enum('Veg','Non-Veg') NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_unit` text NOT NULL DEFAULT 'serving',
  `price` float NOT NULL,
  `freshness_status` enum('Fresh','Good','Near Expiry') DEFAULT 'Fresh',
  `pickup_location` varchar(255) NOT NULL,
  `available_until` datetime NOT NULL,
  `status` enum('Active','Expired') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_listings`
--

INSERT INTO `food_listings` (`id`, `provider_id`, `food_title`, `food_description`, `food_type`, `quantity`, `quantity_unit`, `price`, `freshness_status`, `pickup_location`, `available_until`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Paneer Sandwich', 'Freshly made paneer sandwich with herbs', 'Veg', 15, 'serving', 0, 'Fresh', 'Canteen A, Block 5', '2025-08-23 18:00:00', 'Expired', '2025-08-20 06:49:45', '2025-08-31 08:09:04'),
(2, 1, 'Chicken Wrap', 'Grilled chicken wrap with salad', 'Non-Veg', 15, 'serving', 0, 'Good', 'Hostel C, Ground Floor', '2025-08-20 20:00:00', 'Expired', '2025-08-20 06:49:45', '2025-08-21 14:46:23'),
(3, 1, 'Veg Pizza Slice', 'Thin crust pizza slices with veggies', 'Veg', 30, 'serving', 0, 'Near Expiry', 'Canteen B, Block 3', '2025-08-20 15:00:00', 'Expired', '2025-08-20 06:49:45', '2025-08-21 14:46:23'),
(4, 1, 'Cheese Sandwich', 'Sandwich with cheddar cheese and lettuce', 'Veg', 10, 'serving', 0, 'Near Expiry', 'Canteen A, Block 5', '2025-08-20 10:00:00', 'Expired', '2025-08-20 06:50:37', '2025-08-20 06:50:37'),
(5, 1, 'Pasta', 'Delicious Pasta', 'Veg', 2, 'serving', 0, '', 'home', '2025-08-20 17:00:00', 'Expired', '2025-08-20 08:28:11', '2025-08-21 09:58:46'),
(6, 1, 'Biryani', 'Biryani', 'Non-Veg', 10, 'serving', 0, '', 'home', '2025-08-21 14:07:00', 'Expired', '2025-08-20 08:37:34', '2025-08-21 14:46:23'),
(9, 1, 'Maggi', 'maggi', 'Veg', 2, 'serving', 0, 'Fresh', 'home', '2025-08-21 16:31:00', 'Expired', '2025-08-21 09:01:48', '2025-08-21 14:46:23'),
(11, 1, 'Fried rice', 'Fried rice', 'Non-Veg', 6, 'serving', 0, 'Good', 'home', '2025-08-21 18:17:00', 'Expired', '2025-08-21 11:47:35', '2025-08-21 14:46:23'),
(12, 1, 'Manchurian', 'Manchurian', 'Veg', 2, 'serving', 0, 'Near Expiry', 'Canteen', '2025-08-21 21:29:00', 'Expired', '2025-08-21 13:59:18', '2025-08-21 16:05:34'),
(13, 1, 'Roll', 'Egg Roll', 'Non-Veg', 2, 'serving', 0, 'Fresh', 'Hostel', '2025-08-21 22:37:00', 'Expired', '2025-08-21 16:07:13', '2025-08-21 17:15:47'),
(14, 1, 'Mutton Curry', 'Mutton Curry', 'Non-Veg', 0, 'serving', 0, 'Fresh', 'Hostel A', '2025-08-22 22:03:00', 'Expired', '2025-08-21 16:33:48', '2025-08-22 04:15:55'),
(15, 1, 'Maggi', 'Cheese Maggi', 'Veg', 1, 'serving', 0, 'Near Expiry', 'Auditorium', '2025-08-23 01:26:00', 'Expired', '2025-08-21 19:56:28', '2025-08-31 08:09:04'),
(16, 6, 'Biryani', 'Delicious Chicken Biryani', 'Non-Veg', 3, 'serving', 0, 'Fresh', 'Hostel A', '2025-08-22 07:40:00', 'Expired', '2025-08-21 20:06:19', '2025-08-22 04:15:42'),
(17, 7, 'maggi', 'maggi', 'Veg', 2, 'serving', 0, 'Fresh', 'canteen', '2025-08-23 11:25:00', 'Expired', '2025-08-22 04:55:49', '2025-08-31 08:09:04'),
(18, 9, 'Maggi', 'Cheese maggi', 'Veg', 8, 'servings', 30, 'Fresh', 'Hostel B,Main Canteen', '2025-09-01 13:46:00', 'Expired', '2025-08-31 08:20:49', '2025-09-02 23:45:45'),
(19, 9, ' Fried Rice', 'Veg Fried Rice', 'Veg', 5, 'plates', 80, 'Fresh', 'Hostel 2', '2025-09-04 05:17:00', 'Active', '2025-09-02 23:47:43', '2025-09-02 23:47:43'),
(20, 9, 'Chai', 'Chai', 'Veg', 10, 'servings', 10, 'Fresh', 'Hostel Main Building', '2025-09-03 11:29:00', 'Active', '2025-09-02 23:59:40', '2025-09-02 23:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_id`, `listing_id`, `message`, `is_read`, `created_at`, `read_at`) VALUES
(1, 3, 11, 'New listing added: coffee', 1, '2025-08-21 13:20:29', '2025-08-21 13:21:17'),
(3, 3, 13, 'New listing added: tea', 1, '2025-08-21 13:26:34', '2025-08-21 13:27:15'),
(4, 3, 14, 'New listing added: popcorn', 1, '2025-08-21 15:44:43', '2025-08-21 15:45:13'),
(5, 3, 14, 'New listing added: Mutton Curry', 1, '2025-08-21 16:33:48', '2025-08-21 16:43:36'),
(6, 3, 15, 'New listing added: Maggi', 1, '2025-08-21 19:56:28', '2025-08-21 20:08:40'),
(7, 5, 15, 'New listing added: Maggi', 0, '2025-08-21 19:56:28', NULL),
(9, 3, 16, 'New listing added: Biryani', 1, '2025-08-21 20:06:19', '2025-08-21 20:08:40'),
(10, 5, 16, 'New listing added: Biryani', 0, '2025-08-21 20:06:19', NULL),
(11, 3, 17, 'New listing added: maggi', 1, '2025-08-22 04:55:49', '2025-08-22 04:57:46'),
(12, 5, 17, 'New listing added: maggi', 0, '2025-08-22 04:55:49', NULL),
(14, 3, 18, 'New listing added: Maggi', 0, '2025-08-31 08:20:49', NULL),
(15, 5, 18, 'New listing added: Maggi', 0, '2025-08-31 08:20:49', NULL),
(16, 8, 18, 'New listing added: Maggi', 1, '2025-08-31 08:20:49', '2025-08-31 08:35:27'),
(17, 3, 19, 'New listing added:  Fried Rice', 0, '2025-09-02 23:47:43', NULL),
(18, 5, 19, 'New listing added:  Fried Rice', 0, '2025-09-02 23:47:43', NULL),
(19, 8, 19, 'New listing added:  Fried Rice', 1, '2025-09-02 23:47:43', '2025-09-02 23:47:55'),
(20, 3, 20, 'New listing added: Chai', 0, '2025-09-02 23:59:40', NULL),
(21, 5, 20, 'New listing added: Chai', 0, '2025-09-02 23:59:40', NULL),
(22, 8, 20, 'New listing added: Chai', 1, '2025-09-02 23:59:40', '2025-09-02 23:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `method`, `status`, `created_at`) VALUES
(1, 1, 'COD', 'pending', '2025-08-22 19:14:39'),
(2, 1, 'COD', 'pending', '2025-08-22 19:17:51'),
(3, 1, 'COD', 'pending', '2025-08-22 19:17:57'),
(4, 1, 'COD', 'pending', '2025-08-22 19:35:26'),
(5, 1, 'COD', 'pending', '2025-08-23 01:53:02'),
(6, 1, 'COD', 'pending', '2025-08-23 01:54:57'),
(7, 1, 'COD', 'pending', '2025-08-23 01:55:00'),
(8, 1, 'COD', 'pending', '2025-08-23 02:00:38'),
(9, 1, 'COD', 'pending', '2025-08-23 05:47:54'),
(10, 1, 'COD', 'pending', '2025-08-31 08:24:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('provider','recipient') NOT NULL,
  `subrole` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `subrole`, `created_at`) VALUES
(1, 'canteen1', 'canteen1@campus.com', 'canteen123', 'provider', 'Canteen', '2025-08-20 03:54:04'),
(3, 'tanu', 'tanu@gmail.com', 'Kolkata', 'recipient', 'Students', '2025-08-21 03:21:43'),
(4, 'Disha', 'Disha30@gmail.com', 'disha', 'provider', '', '2025-08-21 17:12:23'),
(5, 'Suman', 'Suman55@gmail.com', 'sum123', 'recipient', '', '2025-08-21 18:24:38'),
(6, 'hostel_admin', 'Hostel@gmail.com', 'hostel', 'provider', '', '2025-08-21 20:04:38'),
(7, 'canteen3', 'canteen3@gmail.com', '1234', 'provider', '', '2025-08-22 04:51:53'),
(8, 'Aditi', 'aditi30@gmail.com', '$2y$10$U.3.oTiugj3b1as9qIrqm..SsYimk2gEoRkas77KNp/d4CDvpzZdm', 'recipient', 'students', '2025-08-31 08:08:34'),
(9, 'Hostel2', 'hostel2@gmail.com', '$2y$10$R3yqD0dDtCP6ovvzRT8J2evRRKPqHgRRVijgvRobiIVosGlu/657C', 'provider', 'hostel', '2025-08-31 08:15:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_email` (`email`),
  ADD UNIQUE KEY `unique_admin_username` (`username`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_id` (`food_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `food_listings`
--
ALTER TABLE `food_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient_isread` (`recipient_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `food_listings`
--
ALTER TABLE `food_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `food_listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_listings`
--
ALTER TABLE `food_listings`
  ADD CONSTRAINT `food_listings_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
