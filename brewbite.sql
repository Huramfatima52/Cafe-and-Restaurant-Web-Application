-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 04:54 PM
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
-- Database: `brewbite`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `created_at`) VALUES
(1, 'admin@gmail.com', '$2y$10$xnUW.KVx9s74J7GPmndjUeFounVDm.1fhTGiFL2loPqX39wm6EsJG', '2026-05-19 18:10:59');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `item_name`, `price`, `status`) VALUES
(1, 'Soup', 'Corn Soup', 400, 'not available'),
(2, 'Ice Cream', 'Chocolate Ice Cream', 350, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `city`) VALUES
(1, 'Hina Fatima', 'hina822@gmail.com', '0322963783', 'Lahore'),
(6, 'Faiza', 'faiza287@gmail.com', '03039823556', 'karachi'),
(12, 'Mubeen', 'mubeen@842gmail.com', '03087213768', 'Lahore'),
(13, 'M.Ahmad', 'ahmed201@gmail.com', '03022562789', 'Islamabad');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `o_id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL,
  `items` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL,
  `order_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`o_id`, `c_name`, `items`, `total`, `status`, `order_date`) VALUES
(1, 'Maham ', 'Capuuccino', 1450.00, 'Completed', '2026-10-03 19:00:00.000000');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `item_name`, `price`, `category`, `status`, `stock`, `image`) VALUES
(1, 'Cappuccino', 1450.00, 'Coffee', 'available', 10, 'cappuccino.jpg'),
(2, 'Iced Latte', 1840.00, 'Coffee', 'not available', 3, 'icedlatte.jpg'),
(3, 'Italian Manicotti', 2450.00, 'Italian Corner', 'available', 9, 'italianmanicotti.jpg'),
(4, 'Italian Pasta', 1700.00, 'Italian Corner', 'available', 15, 'italianpasta.jpg'),
(5, 'Expresso', 890.00, 'Coffee', 'available', 10, 'expresso.jpg'),
(6, 'Americano', 2150.00, 'Coffee', 'available', 2, 'americano.jpg'),
(7, 'Chocolate Fudge Cake', 2500.00, 'Sweets & Dessert', 'available', 18, 'chocolatefudgecake.jpg'),
(8, 'Donuts', 1370.00, 'Sweets & Dessert', 'available', 30, 'donuts.jpg'),
(10, 'Mango Shake', 650.00, 'Sweets & Dessert', 'available', 2, 'mangoshake.jpg'),
(11, 'Fajita Wrap', 1600.00, 'Sandwiches & Wraps', 'available', 6, 'fajitawrap.jpg'),
(12, 'Coca Cola', 250.00, 'Cold drinks & Soft Refreshers', 'available', 120, 'cocacola.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `reply` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_name`, `rating`, `comment`, `status`, `reply`) VALUES
(1, 'Hina ', 4, 'Food was amazing and service was fast', 'approved', 'Thank you for your feedback'),
(2, 'Wahaj ', 5, 'Pizza taste was excellent', 'approved', 'Glad you enjoyed it'),
(3, 'Maira ', 3, 'Coffee was good but delivery was late', 'approved', 'Sorry for the delay we’ll improve');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`o_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `o_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
