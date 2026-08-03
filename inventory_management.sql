-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 03, 2026 at 11:50 AM
-- Server version: 10.11.15-MariaDB-log
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `agency`
--

CREATE TABLE `agency` (
  `id` int(11) NOT NULL,
  `agency_name` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `mob_number` varchar(20) DEFAULT NULL,
  `alt_number` varchar(20) DEFAULT NULL,
  `mail_id` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agency`
--

INSERT INTO `agency` (`id`, `agency_name`, `customer_name`, `mob_number`, `alt_number`, `mail_id`, `city`, `state`, `address`, `feedback`) VALUES
(1, 'agency_name', 'customer_name', 'mob_number', 'alt_number', 'mail_id', 'city', 'state', 'address', 'feedback'),
(2, 'Agency 1', 'Customer 1', '9899538166', '9749606689', 'customer1@example.com', 'Kolhapur', 'Maharashtra', 'Address 1, Kolhapur', 'Good Service'),
(3, 'Agency 2', 'Customer 2', '9856369377', '9788874527', 'customer2@example.com', 'Mumbai', 'Maharashtra', 'Address 2, Mumbai', 'Good Service'),
(4, 'Agency 3', 'Customer 3', '9822051220', '9754090688', 'customer3@example.com', 'Mumbai', 'Maharashtra', 'Address 3, Mumbai', 'Good Service'),
(5, 'Agency 4', 'Customer 4', '9887515859', '9796721585', 'customer4@example.com', 'Nashik', 'Maharashtra', 'Address 4, Nashik', 'Good Service'),
(6, 'Agency 5', 'Customer 5', '9816264609', '9783484093', 'customer5@example.com', 'Nashik', 'Maharashtra', 'Address 5, Nashik', 'Good Service'),
(7, 'Agency 6', 'Customer 6', '9827306225', '9726845903', 'customer6@example.com', 'Pune', 'Maharashtra', 'Address 6, Pune', 'Good Service'),
(8, 'Agency 7', 'Customer 7', '9893149851', '9774330528', 'customer7@example.com', 'Mumbai', 'Maharashtra', 'Address 7, Mumbai', 'Good Service'),
(9, 'Agency 8', 'Customer 8', '9833869607', '9758300681', 'customer8@example.com', 'Pune', 'Maharashtra', 'Address 8, Pune', 'Good Service'),
(10, 'Agency 9', 'Customer 9', '9815772809', '9794936588', 'customer9@example.com', 'Mumbai', 'Maharashtra', 'Address 9, Mumbai', 'Good Service'),
(11, 'Agency 10', 'Customer 10', '9842819987', '9791671019', 'customer10@example.com', 'Nagpur', 'Maharashtra', 'Address 10, Nagpur', 'Good Service'),
(12, 'Agency 11', 'Customer 11', '9854682274', '9711181379', 'customer11@example.com', 'Pune', 'Maharashtra', 'Address 11, Pune', 'Good Service'),
(13, 'Agency 12', 'Customer 12', '9846046110', '9795647465', 'customer12@example.com', 'Pune', 'Maharashtra', 'Address 12, Pune', 'Good Service'),
(14, 'Agency 13', 'Customer 13', '9821766598', '9732044277', 'customer13@example.com', 'Mumbai', 'Maharashtra', 'Address 13, Mumbai', 'Good Service'),
(15, 'Agency 14', 'Customer 14', '9860852432', '9736271255', 'customer14@example.com', 'Nashik', 'Maharashtra', 'Address 14, Nashik', 'Good Service'),
(16, 'Agency 15', 'Customer 15', '9878301411', '9757109044', 'customer15@example.com', 'Mumbai', 'Maharashtra', 'Address 15, Mumbai', 'Good Service'),
(17, 'Agency 16', 'Customer 16', '9837953217', '9742071449', 'customer16@example.com', 'Pune', 'Maharashtra', 'Address 16, Pune', 'Good Service'),
(18, 'Agency 17', 'Customer 17', '9894455945', '9748850590', 'customer17@example.com', 'Nashik', 'Maharashtra', 'Address 17, Nashik', 'Good Service'),
(19, 'Agency 18', 'Customer 18', '9891633557', '9729212022', 'customer18@example.com', 'Kolhapur', 'Maharashtra', 'Address 18, Kolhapur', 'Good Service'),
(20, 'Agency 19', 'Customer 19', '9826193422', '9796809444', 'customer19@example.com', 'Nagpur', 'Maharashtra', 'Address 19, Nagpur', 'Good Service'),
(21, 'Agency 20', 'Customer 20', '9821206953', '9723941070', 'customer20@example.com', 'Mumbai', 'Maharashtra', 'Address 20, Mumbai', 'Good Service'),
(22, 'Agency 21', 'Customer 21', '9837865204', '9761379399', 'customer21@example.com', 'Nagpur', 'Maharashtra', 'Address 21, Nagpur', 'Good Service'),
(23, 'Agency 22', 'Customer 22', '9884092074', '9730823465', 'customer22@example.com', 'Mumbai', 'Maharashtra', 'Address 22, Mumbai', 'Good Service'),
(24, 'Agency 23', 'Customer 23', '9871765678', '9716792031', 'customer23@example.com', 'Mumbai', 'Maharashtra', 'Address 23, Mumbai', 'Good Service'),
(25, 'Agency 24', 'Customer 24', '9832383321', '9764165860', 'customer24@example.com', 'Nashik', 'Maharashtra', 'Address 24, Nashik', 'Good Service'),
(26, 'Agency 25', 'Customer 25', '9855344997', '9715863989', 'customer25@example.com', 'Pune', 'Maharashtra', 'Address 25, Pune', 'Good Service'),
(27, 'Agency 26', 'Customer 26', '9882951852', '9770525648', 'customer26@example.com', 'Pune', 'Maharashtra', 'Address 26, Pune', 'Good Service'),
(28, 'Agency 27', 'Customer 27', '9870076432', '9724353063', 'customer27@example.com', 'Mumbai', 'Maharashtra', 'Address 27, Mumbai', 'Good Service'),
(29, 'Agency 28', 'Customer 28', '9813245378', '9740332415', 'customer28@example.com', 'Pune', 'Maharashtra', 'Address 28, Pune', 'Good Service'),
(30, 'Agency 29', 'Customer 29', '9844689048', '9736042931', 'customer29@example.com', 'Nagpur', 'Maharashtra', 'Address 29, Nagpur', 'Good Service'),
(31, 'Agency 30', 'Customer 30', '9864311314', '9755042341', 'customer30@example.com', 'Mumbai', 'Maharashtra', 'Address 30, Mumbai', 'Good Service'),
(32, 'Agency 31', 'Customer 31', '9874875557', '9740788197', 'customer31@example.com', 'Pune', 'Maharashtra', 'Address 31, Pune', 'Good Service'),
(33, 'Agency 32', 'Customer 32', '9856739596', '9798034227', 'customer32@example.com', 'Pune', 'Maharashtra', 'Address 32, Pune', 'Good Service'),
(34, 'Agency 33', 'Customer 33', '9897800756', '9778769847', 'customer33@example.com', 'Kolhapur', 'Maharashtra', 'Address 33, Kolhapur', 'Good Service'),
(35, 'Agency 34', 'Customer 34', '9866415938', '9786731092', 'customer34@example.com', 'Kolhapur', 'Maharashtra', 'Address 34, Kolhapur', 'Good Service'),
(36, 'Agency 35', 'Customer 35', '9868015766', '9788929730', 'customer35@example.com', 'Pune', 'Maharashtra', 'Address 35, Pune', 'Good Service'),
(37, 'Agency 36', 'Customer 36', '9859425654', '9734282348', 'customer36@example.com', 'Nagpur', 'Maharashtra', 'Address 36, Nagpur', 'Good Service'),
(38, 'Agency 37', 'Customer 37', '9814789249', '9720814336', 'customer37@example.com', 'Mumbai', 'Maharashtra', 'Address 37, Mumbai', 'Good Service'),
(39, 'Agency 38', 'Customer 38', '9866292603', '9746198854', 'customer38@example.com', 'Kolhapur', 'Maharashtra', 'Address 38, Kolhapur', 'Good Service'),
(40, 'Agency 39', 'Customer 39', '9839784105', '9710396511', 'customer39@example.com', 'Kolhapur', 'Maharashtra', 'Address 39, Kolhapur', 'Good Service'),
(41, 'Agency 40', 'Customer 40', '9875270556', '9714066814', 'customer40@example.com', 'Kolhapur', 'Maharashtra', 'Address 40, Kolhapur', 'Good Service'),
(42, 'Agency 41', 'Customer 41', '9866647519', '9716095463', 'customer41@example.com', 'Kolhapur', 'Maharashtra', 'Address 41, Kolhapur', 'Good Service'),
(43, 'Agency 42', 'Customer 42', '9841239398', '9771749948', 'customer42@example.com', 'Pune', 'Maharashtra', 'Address 42, Pune', 'Good Service'),
(44, 'Agency 43', 'Customer 43', '9814723730', '9730871521', 'customer43@example.com', 'Nashik', 'Maharashtra', 'Address 43, Nashik', 'Good Service'),
(45, 'Agency 44', 'Customer 44', '9841057137', '9754420443', 'customer44@example.com', 'Kolhapur', 'Maharashtra', 'Address 44, Kolhapur', 'Good Service'),
(46, 'Agency 45', 'Customer 45', '9899646555', '9794339917', 'customer45@example.com', 'Kolhapur', 'Maharashtra', 'Address 45, Kolhapur', 'Good Service'),
(47, 'Agency 46', 'Customer 46', '9830452665', '9764023120', 'customer46@example.com', 'Nagpur', 'Maharashtra', 'Address 46, Nagpur', 'Good Service'),
(48, 'Agency 47', 'Customer 47', '9868593487', '9790139574', 'customer47@example.com', 'Nagpur', 'Maharashtra', 'Address 47, Nagpur', 'Good Service'),
(49, 'Agency 48', 'Customer 48', '9864801059', '9718691270', 'customer48@example.com', 'Pune', 'Maharashtra', 'Address 48, Pune', 'Good Service'),
(50, 'Agency 49', 'Customer 49', '9886547583', '9733079825', 'customer49@example.com', 'Mumbai', 'Maharashtra', 'Address 49, Mumbai', 'Good Service'),
(52, 'agency_name', 'customer_name', 'mob_number', 'alt_number', 'mail_id', 'city', 'state', 'address', 'feedback'),
(53, 'Agency 1', 'Customer 1', '9899538166', '9749606689', 'customer1@example.com', 'Kolhapur', 'Maharashtra', 'Address 1, Kolhapur', 'Good Service'),
(54, 'Agency 2', 'Customer 2', '9856369377', '9788874527', 'customer2@example.com', 'Mumbai', 'Maharashtra', 'Address 2, Mumbai', 'Good Service'),
(55, 'Agency 3', 'Customer 3', '9822051220', '9754090688', 'customer3@example.com', 'Mumbai', 'Maharashtra', 'Address 3, Mumbai', 'Good Service'),
(56, 'Agency 4', 'Customer 4', '9887515859', '9796721585', 'customer4@example.com', 'Nashik', 'Maharashtra', 'Address 4, Nashik', 'Good Service'),
(57, 'Agency 5', 'Customer 5', '9816264609', '9783484093', 'customer5@example.com', 'Nashik', 'Maharashtra', 'Address 5, Nashik', 'Good Service'),
(58, 'Agency 6', 'Customer 6', '9827306225', '9726845903', 'customer6@example.com', 'Pune', 'Maharashtra', 'Address 6, Pune', 'Good Service'),
(59, 'Agency 7', 'Customer 7', '9893149851', '9774330528', 'customer7@example.com', 'Mumbai', 'Maharashtra', 'Address 7, Mumbai', 'Good Service'),
(60, 'Agency 8', 'Customer 8', '9833869607', '9758300681', 'customer8@example.com', 'Pune', 'Maharashtra', 'Address 8, Pune', 'Good Service'),
(61, 'Agency 9', 'Customer 9', '9815772809', '9794936588', 'customer9@example.com', 'Mumbai', 'Maharashtra', 'Address 9, Mumbai', 'Good Service'),
(62, 'Agency 10', 'Customer 10', '9842819987', '9791671019', 'customer10@example.com', 'Nagpur', 'Maharashtra', 'Address 10, Nagpur', 'Good Service'),
(63, 'Agency 11', 'Customer 11', '9854682274', '9711181379', 'customer11@example.com', 'Pune', 'Maharashtra', 'Address 11, Pune', 'Good Service'),
(64, 'Agency 12', 'Customer 12', '9846046110', '9795647465', 'customer12@example.com', 'Pune', 'Maharashtra', 'Address 12, Pune', 'Good Service'),
(65, 'Agency 13', 'Customer 13', '9821766598', '9732044277', 'customer13@example.com', 'Mumbai', 'Maharashtra', 'Address 13, Mumbai', 'Good Service'),
(66, 'Agency 14', 'Customer 14', '9860852432', '9736271255', 'customer14@example.com', 'Nashik', 'Maharashtra', 'Address 14, Nashik', 'Good Service'),
(67, 'Agency 15', 'Customer 15', '9878301411', '9757109044', 'customer15@example.com', 'Mumbai', 'Maharashtra', 'Address 15, Mumbai', 'Good Service'),
(68, 'Agency 16', 'Customer 16', '9837953217', '9742071449', 'customer16@example.com', 'Pune', 'Maharashtra', 'Address 16, Pune', 'Good Service'),
(69, 'Agency 17', 'Customer 17', '9894455945', '9748850590', 'customer17@example.com', 'Nashik', 'Maharashtra', 'Address 17, Nashik', 'Good Service'),
(70, 'Agency 18', 'Customer 18', '9891633557', '9729212022', 'customer18@example.com', 'Kolhapur', 'Maharashtra', 'Address 18, Kolhapur', 'Good Service'),
(71, 'Agency 19', 'Customer 19', '9826193422', '9796809444', 'customer19@example.com', 'Nagpur', 'Maharashtra', 'Address 19, Nagpur', 'Good Service'),
(72, 'Agency 20', 'Customer 20', '9821206953', '9723941070', 'customer20@example.com', 'Mumbai', 'Maharashtra', 'Address 20, Mumbai', 'Good Service'),
(73, 'Agency 21', 'Customer 21', '9837865204', '9761379399', 'customer21@example.com', 'Nagpur', 'Maharashtra', 'Address 21, Nagpur', 'Good Service'),
(74, 'Agency 22', 'Customer 22', '9884092074', '9730823465', 'customer22@example.com', 'Mumbai', 'Maharashtra', 'Address 22, Mumbai', 'Good Service'),
(75, 'Agency 23', 'Customer 23', '9871765678', '9716792031', 'customer23@example.com', 'Mumbai', 'Maharashtra', 'Address 23, Mumbai', 'Good Service'),
(76, 'Agency 24', 'Customer 24', '9832383321', '9764165860', 'customer24@example.com', 'Nashik', 'Maharashtra', 'Address 24, Nashik', 'Good Service'),
(77, 'Agency 25', 'Customer 25', '9855344997', '9715863989', 'customer25@example.com', 'Pune', 'Maharashtra', 'Address 25, Pune', 'Good Service'),
(78, 'Agency 26', 'Customer 26', '9882951852', '9770525648', 'customer26@example.com', 'Pune', 'Maharashtra', 'Address 26, Pune', 'Good Service'),
(79, 'Agency 27', 'Customer 27', '9870076432', '9724353063', 'customer27@example.com', 'Mumbai', 'Maharashtra', 'Address 27, Mumbai', 'Good Service'),
(80, 'Agency 28', 'Customer 28', '9813245378', '9740332415', 'customer28@example.com', 'Pune', 'Maharashtra', 'Address 28, Pune', 'Good Service'),
(81, 'Agency 29', 'Customer 29', '9844689048', '9736042931', 'customer29@example.com', 'Nagpur', 'Maharashtra', 'Address 29, Nagpur', 'Good Service'),
(82, 'Agency 30', 'Customer 30', '9864311314', '9755042341', 'customer30@example.com', 'Mumbai', 'Maharashtra', 'Address 30, Mumbai', 'Good Service'),
(83, 'Agency 31', 'Customer 31', '9874875557', '9740788197', 'customer31@example.com', 'Pune', 'Maharashtra', 'Address 31, Pune', 'Good Service'),
(84, 'Agency 32', 'Customer 32', '9856739596', '9798034227', 'customer32@example.com', 'Pune', 'Maharashtra', 'Address 32, Pune', 'Good Service'),
(85, 'Agency 33', 'Customer 33', '9897800756', '9778769847', 'customer33@example.com', 'Kolhapur', 'Maharashtra', 'Address 33, Kolhapur', 'Good Service'),
(86, 'Agency 34', 'Customer 34', '9866415938', '9786731092', 'customer34@example.com', 'Kolhapur', 'Maharashtra', 'Address 34, Kolhapur', 'Good Service'),
(87, 'Agency 35', 'Customer 35', '9868015766', '9788929730', 'customer35@example.com', 'Pune', 'Maharashtra', 'Address 35, Pune', 'Good Service'),
(88, 'Agency 36', 'Customer 36', '9859425654', '9734282348', 'customer36@example.com', 'Nagpur', 'Maharashtra', 'Address 36, Nagpur', 'Good Service'),
(89, 'Agency 37', 'Customer 37', '9814789249', '9720814336', 'customer37@example.com', 'Mumbai', 'Maharashtra', 'Address 37, Mumbai', 'Good Service'),
(90, 'Agency 38', 'Customer 38', '9866292603', '9746198854', 'customer38@example.com', 'Kolhapur', 'Maharashtra', 'Address 38, Kolhapur', 'Good Service'),
(91, 'Agency 39', 'Customer 39', '9839784105', '9710396511', 'customer39@example.com', 'Kolhapur', 'Maharashtra', 'Address 39, Kolhapur', 'Good Service'),
(92, 'Agency 40', 'Customer 40', '9875270556', '9714066814', 'customer40@example.com', 'Kolhapur', 'Maharashtra', 'Address 40, Kolhapur', 'Good Service'),
(93, 'Agency 41', 'Customer 41', '9866647519', '9716095463', 'customer41@example.com', 'Kolhapur', 'Maharashtra', 'Address 41, Kolhapur', 'Good Service'),
(94, 'Agency 42', 'Customer 42', '9841239398', '9771749948', 'customer42@example.com', 'Pune', 'Maharashtra', 'Address 42, Pune', 'Good Service'),
(95, 'Agency 43', 'Customer 43', '9814723730', '9730871521', 'customer43@example.com', 'Nashik', 'Maharashtra', 'Address 43, Nashik', 'Good Service'),
(96, 'Agency 44', 'Customer 44', '9841057137', '9754420443', 'customer44@example.com', 'Kolhapur', 'Maharashtra', 'Address 44, Kolhapur', 'Good Service'),
(97, 'Agency 45', 'Customer 45', '9899646555', '9794339917', 'customer45@example.com', 'Kolhapur', 'Maharashtra', 'Address 45, Kolhapur', 'Good Service'),
(98, 'Agency 46', 'Customer 46', '9830452665', '9764023120', 'customer46@example.com', 'Nagpur', 'Maharashtra', 'Address 46, Nagpur', 'Good Service');

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `email_id` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `stored_name` varchar(255) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `name`, `attendance_date`, `check_in`, `check_out`, `status`) VALUES
(1, '3', 'soham', '2026-07-09', '13:57:16', NULL, 'Present'),
(2, '6', 'ram', '2026-07-27', '13:36:13', NULL, 'Present'),
(3, '2', 'Aryan', '2026-07-27', '13:44:12', NULL, 'Present'),
(4, '5', 'gosavi', '2026-07-28', '12:50:00', '12:50:18', 'Present'),
(5, '3', 'soham', '2026-07-28', '13:31:33', NULL, 'Present'),
(6, '18', 'ex', '2026-07-28', '13:51:02', '13:53:20', 'Present'),
(7, '5', 'gosavi', '2026-07-29', '10:59:29', '10:59:36', 'Present'),
(8, '18', 'ex', '2026-07-29', '10:59:59', NULL, 'Present'),
(9, '6', 'ram', '2026-07-29', '17:06:21', NULL, 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `bill_type` varchar(50) DEFAULT NULL,
  `bill_number` varchar(50) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `quote_number` varchar(50) DEFAULT NULL,
  `bill_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_contact` varchar(50) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `company_gst` varchar(50) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_contact_person` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_gst` varchar(50) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `place_of_supply` varchar(255) DEFAULT NULL,
  `hsn_sac_code` varchar(50) DEFAULT NULL,
  `gst_mode` varchar(20) DEFAULT NULL,
  `gst_rate` decimal(5,2) DEFAULT NULL,
  `cgst_rate` decimal(5,2) DEFAULT NULL,
  `sgst_rate` decimal(5,2) DEFAULT NULL,
  `igst_rate` decimal(5,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `taxable_value` decimal(10,2) DEFAULT NULL,
  `cgst_amount` decimal(10,2) DEFAULT NULL,
  `sgst_amount` decimal(10,2) DEFAULT NULL,
  `igst_amount` decimal(10,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `other_charges` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `amount_in_words` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_ifsc_code` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `client_name_footer` varchar(255) DEFAULT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_json`)),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_role` varchar(50) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `receiver_role` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `voice_message` varchar(255) DEFAULT NULL,
  `message_type` varchar(50) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_id`, `sender_role`, `receiver_id`, `receiver_role`, `message`, `attachment`, `voice_message`, `message_type`, `duration`, `created_at`) VALUES
(29, 3, 'employee', 1, 'admin', 'hi', '', '', NULL, NULL, NULL),
(30, 2, 'admin', 1, 'employee', 'sdfg', '', '', NULL, NULL, NULL),
(32, 6, 'employee', 1, 'admin', 'hi', '', '', NULL, NULL, NULL),
(34, 5, 'employee', 1, 'admin', 'HI', '', '', NULL, NULL, NULL),
(35, 1, 'admin', 2, 'employee', 'hi pineapple', '', '', NULL, NULL, NULL),
(36, 5, 'employee', 1, 'admin', '', '', '1785301833_voice.webm', NULL, NULL, NULL),
(37, 5, 'employee', 1, 'admin', '', '', '1785301863_voice.webm', NULL, NULL, NULL),
(38, 18, 'employee', 2, 'admin', '', '1785303102_IMG_1690 (3).MOV', '', NULL, NULL, NULL),
(39, 18, 'employee', 2, 'admin', '', '', '1785303137_voice.webm', NULL, NULL, NULL),
(40, 18, 'employee', 2, 'admin', '', '', '1785303167_voice.webm', NULL, NULL, NULL),
(41, 18, 'employee', 2, 'admin', 'Hello', '', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id` int(11) NOT NULL,
  `agency_name` varchar(255) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `alt_name` varchar(255) DEFAULT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `alt_phone` varchar(20) DEFAULT NULL,
  `support_alt_no` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `alt_address` text DEFAULT NULL,
  `mail_id` varchar(255) DEFAULT NULL,
  `purchase_rental` varchar(20) DEFAULT NULL,
  `only_software` varchar(20) DEFAULT NULL,
  `gateway_quantity` int(11) DEFAULT NULL,
  `gateway_name` varchar(255) DEFAULT NULL,
  `gateway_mac_id` varchar(255) DEFAULT NULL,
  `server_quantity` int(11) DEFAULT NULL,
  `server_name` varchar(255) DEFAULT NULL,
  `server_mac_id` varchar(255) DEFAULT NULL,
  `gateway_price` decimal(10,2) DEFAULT NULL,
  `server_price` decimal(10,2) DEFAULT NULL,
  `amc` varchar(20) DEFAULT NULL,
  `amc_year_month` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `paid` decimal(10,2) DEFAULT 0.00,
  `unpaid` decimal(10,2) DEFAULT 0.00,
  `amc_expiry` date DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT NULL,
  `total_outstanding` decimal(10,2) DEFAULT NULL,
  `headphone_total_count` int(11) DEFAULT 0,
  `paid_headphone_price` decimal(10,2) DEFAULT 0.00,
  `unpaid_headphone_price` decimal(10,2) DEFAULT 0.00,
  `gst_no` varchar(50) DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `headphones_total_count` int(11) DEFAULT NULL,
  `headphones_price` decimal(10,2) DEFAULT NULL,
  `unpaid_headphones_price` decimal(10,2) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `service` varchar(50) DEFAULT NULL,
  `payment_photos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id`, `agency_name`, `owner_name`, `alt_name`, `mobile_no`, `alt_phone`, `support_alt_no`, `address`, `alt_address`, `mail_id`, `purchase_rental`, `only_software`, `gateway_quantity`, `gateway_name`, `gateway_mac_id`, `server_quantity`, `server_name`, `server_mac_id`, `gateway_price`, `server_price`, `amc`, `amc_year_month`, `date`, `time`, `paid`, `unpaid`, `amc_expiry`, `payment_status`, `total_outstanding`, `headphone_total_count`, `paid_headphone_price`, `unpaid_headphone_price`, `gst_no`, `payment_screenshot`, `headphones_total_count`, `headphones_price`, `unpaid_headphones_price`, `gst_number`, `created_at`, `updated_at`, `service`, `payment_photos`) VALUES
(1, 'ABC Technologies', 'Rahul Sharma', NULL, '9876543210', NULL, '9123456780', 'Pune Maharashtra', 'Shivajinagar Pune', 'rahul@abctech.com', 'Purchase', 'No', 2, 'OpenVox', '0', 1, 'Dell PowerEdge', '11:22:33:44:55:66', 25000.00, 50000.00, '0', NULL, NULL, NULL, 0.00, 0.00, '2026-12-31', 'Paid', 0.00, 0, 0.00, 0.00, NULL, 'uploads/payment_screenshot/logo.png.jpeg', 10, 15000.00, 0.00, '27ABCDE1234F1Z5', NULL, '2026-07-14 09:54:38', 'off', NULL),
(2, 'XYZ Solutions', 'Priya Patel', NULL, '9988776655', NULL, '9765432109', 'Mumbai Maharashtra', 'Andheri East Mumbai', 'priya@xyzsolutions.com', 'Rental', 'Yes', 1, 'Dinstar', '0', 0, '', '', 12000.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 12000.00, 0, 0.00, 0.00, NULL, NULL, 5, 5000.00, 2500.00, '27XYZAB5678K2Z3', NULL, NULL, 'off', NULL),
(3, 'Techno Hub', 'Amit Joshi', NULL, '9898989898', NULL, '9000000001', 'Nashik Maharashtra', 'College Road Nashik', 'amit@technohub.in', 'Purchase', 'No', 3, 'OpenVox', '0', 2, 'HP ProLiant', '22:33:44:55:66:77', 45000.00, 90000.00, '0', NULL, NULL, NULL, 0.00, 0.00, '2027-06-30', 'Unpaid', 35000.00, 0, 0.00, 0.00, NULL, NULL, 15, 22500.00, 7500.00, '27LMNOP9876Q1Z2', NULL, NULL, 'off', NULL),
(4, 'Agency 46', 'adfasd123456789', NULL, '1234567890', NULL, '9000000001', 'pune', 'karad', 'codingmaster859@gmail.com', 'Rental', 'Yes', 5, 'OpenVox', '234', 2, 'HP ProLiant', '09876', 0.08, 0.04, '0.05', NULL, NULL, NULL, 0.00, 0.00, '2026-07-14', 'Paid', 0.17, 0, 0.00, 0.00, NULL, NULL, 6, 0.06, 0.03, '27LMNOP9876Q1Z2', NULL, NULL, 'on', NULL),
(5, 'ABC', 'Owner 49', NULL, '9810000049', NULL, '7845164515', 'dsfadf', 'awdw', 'sohamtarate98@gmail.com', 'Purchase', 'No', 5, 'Dinstar', '9876', 5, 'Server-49', '69696', 8.00, 5.00, '0.05', NULL, NULL, NULL, 0.00, 0.00, '2026-07-14', 'Paid', 0.06, 0, 0.00, 0.00, NULL, NULL, 3, 0.03, 0.03, 'asd55', '2026-07-14 15:02:41', '2026-07-14 09:32:41', 'on', NULL),
(6, 'Aryan', 'adfasd123456789', NULL, '9898989898', NULL, '9000000001', 'sdfbdgbd', 'sfhrsf', 'sohamtarate98@gmail.com', 'Rental', 'Yes', 8, 'OpenVox', '0', 0, 'erg', '\\', 5.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, NULL, 0, 0.00, 0.00, 'wrgg', '2026-07-14 15:09:33', '2026-07-14 09:39:33', 'on', NULL),
(7, 'sdyt', 'strdy', NULL, '97812864532', NULL, '9110000049', 'fgch', 'dxgfh', 'sohamtarate98@gmail.com', 'Rental', 'No', 5, 'Dinstar', '0', 9865, 'erg', '11:22:33:44:55:49', 5.00, 865.00, '0.04', NULL, NULL, NULL, 0.00, 0.00, '2026-07-16', 'Paid', 0.05, 0, 0.00, 0.00, NULL, NULL, 852, 0.01, 0.00, '86523', '2026-07-14 15:11:24', '2026-07-14 09:41:24', 'off', NULL),
(8, 'Aryan', 'Soham', NULL, '97812864532', NULL, '1234567890', 'rwg', 'df', 'sohamtarate98@gmail.com', 'Purchase', 'Yes', 8, 'OpenVox', '0', 5, 'iua', '69696', 8.00, 5.00, '0.08', NULL, NULL, NULL, 0.00, 0.00, '2026-07-14', 'Paid', 0.03, 0, 0.00, 0.00, NULL, NULL, 2, 0.03, 0.02, '87542', '2026-07-14 15:23:02', '2026-07-14 09:53:02', 'on', NULL),
(9, 'ydsh', 'lsd', NULL, '78452', NULL, '8965', 'asc', 'sfa', 'sohamtarate98@gmail.com', 'Rental', 'No', 0, 'OpenVox', '0', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, NULL, 0, 0.00, 0.00, 'asas54', '2026-07-14 15:24:05', '2026-07-14 09:54:05', 'on', NULL),
(10, 'xfdh', 'kgkjhk', NULL, 'jgk', NULL, 'ijg', 'gj', 'bjk', 'sohamtarate98@gmail.com', 'Purchase', 'No', 0, 'OpenVox', '0', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, NULL, 0, 0.00, 0.00, 'asas54', '2026-07-14 15:26:19', '2026-07-14 09:56:19', 'off', NULL),
(11, 'sass', 's', NULL, '78452', NULL, '1234567890', 's', 's', 'sohamtarate98@gmail.com', 'Purchase', 'No', 0, 'OpenVox', '', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, '', 0, 0.00, 0.00, '86523', '2026-07-14 15:40:25', NULL, 'on', NULL),
(12, 'ABC', 'Owner 49', NULL, '97812864532', NULL, '7845164515', 'g', 'h', 'sohamtarate98@gmail.com', 'Rental', 'No', 0, 'OpenVox', '', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, '', 0, 0.00, 0.00, '8652', '2026-07-14 15:44:25', '2026-07-14 10:15:01', 'on', NULL),
(13, 'Agency 46', 'Soham', NULL, '5463154351', NULL, '9110000049', 'sdgd', 'rthrh', 'sohamtarate98@gmail.com', 'Purchase', 'Yes', 0, 'OpenVox', '', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, '', 0, 0.00, 0.00, '65541', '2026-07-15 11:53:48', NULL, 'on', NULL),
(14, 'blah blah blah', 'asd', NULL, '1234567890', NULL, '1234567890', 'mala ', 'nahi', 'alskdjksdj@gmail.com', 'Purchase', 'No', 12, 'Dinstar', '234 2342 234', 23423, 'basd', 'w234', 23423.00, 234.00, '212', NULL, NULL, NULL, 0.00, 0.00, '2026-07-28', 'Paid', 468.00, 0, 0.00, 0.00, NULL, '', 234, 234.00, 234.00, '1234567890', '2026-07-15 12:37:32', NULL, 'on', NULL),
(15, 'HELLO', '1234', NULL, '1234567890', NULL, '123456', 'asdf', 'asdf', 'asdfed@gmail.com', 'Purchase', 'Yes', 234, 'Dinstar', '234', 324, '234', '234', 1341.99, 242.99, '234', NULL, NULL, NULL, 0.00, 0.00, '2026-08-06', 'Paid', 468.00, 0, 0.00, 0.00, NULL, '', 234, 234.00, 234.00, '2343424', '2026-07-15 12:58:28', NULL, 'on', NULL),
(16, 'sadf', 'sadf', 'sdfg', '1234567', '65432', '234567', 'bn', '234567', 'bgn@gmail.com', 'Rental', 'Yes', 345, 'OpenVox', '23456', 23456, '3456', '567', 23456.00, 4567.00, '2345', '34564', '2026-07-22', '17:12:00', 2345.00, 2.00, '2026-08-05', 'Paid', 2347.00, 2, 345.00, 45.00, '34567', '', 4, 5.00, 45.00, NULL, '2026-07-15 13:13:05', NULL, 'on', NULL),
(17, 'asdf', 'klsadjfl', '123456', '12345', '121334', '234234', 'laskdjf', 'laskdfj', 'edmil@gmail.com', 'Purchase', 'Yes', 324, 'Dinstar', '234', 0, '234', '', 234.00, 0.00, '', '', '0000-00-00', '00:00:00', 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, '234', NULL, 0, 0.00, 0.00, NULL, '2026-07-15 13:19:15', NULL, 'off', NULL),
(18, 'sedr', 'sdfad', NULL, '213', NULL, '34', '134', 'sdf', 'sadf@gamil.com', 'Purchase', 'No', 0, 'OpenVox', '0', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, NULL, 0, 0.00, 0.00, '', NULL, NULL, 'off', NULL),
(19, 'adfas', '132', NULL, '134', NULL, '34', 'wsadf', 'sadf', '2d@gmail.com', 'Purchase', 'No', 0, 'OpenVox', '0', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, NULL, 0, 0.00, 0.00, '', NULL, NULL, 'off', NULL),
(20, 'dafsd', 'qdfga', NULL, '2345', NULL, '234', 'sssdkl', '2sldkajf', '234@gmai.com', 'Purchase', 'No', 0, 'OpenVox', '0', 0, '', '', 0.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 0.00, 0, 0.00, 0.00, NULL, NULL, 0, 0.00, 0.00, '', NULL, NULL, 'off', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_backup`
--

CREATE TABLE `client_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `agency_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `support_alt_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_rental` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `only_software` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_quantity` int(11) DEFAULT NULL,
  `gateway_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_mac_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_quantity` int(11) DEFAULT NULL,
  `server_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_mac_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_price` decimal(10,2) DEFAULT NULL,
  `server_price` decimal(10,2) DEFAULT NULL,
  `amc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amc_expiry` date DEFAULT NULL,
  `payment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_outstanding` decimal(10,2) DEFAULT NULL,
  `headphones_total_count` int(11) DEFAULT NULL,
  `headphones_price` decimal(10,2) DEFAULT NULL,
  `unpaid_headphones_price` decimal(10,2) DEFAULT NULL,
  `gst_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `service` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_photos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `client_backup`
--

INSERT INTO `client_backup` (`id`, `agency_name`, `owner_name`, `mobile_no`, `support_alt_no`, `address`, `alt_address`, `mail_id`, `purchase_rental`, `only_software`, `gateway_quantity`, `gateway_name`, `gateway_mac_id`, `server_quantity`, `server_name`, `server_mac_id`, `gateway_price`, `server_price`, `amc`, `amc_expiry`, `payment_status`, `total_outstanding`, `headphones_total_count`, `headphones_price`, `unpaid_headphones_price`, `gst_number`, `created_at`, `service`, `payment_photos`) VALUES
(1, 'ABC Technologies', 'Rahul Sharma', '9876543210', '9123456780', 'Pune Maharashtra', 'Shivajinagar Pune', 'rahul@abctech.com', 'Purchase', 'No', 2, 'OpenVox', '0', 1, 'Dell PowerEdge', '11:22:33:44:55:66', 25000.00, 50000.00, '0', '2026-12-31', 'Paid', 0.00, 10, 15000.00, 0.00, '27ABCDE1234F1Z5', NULL, 'off', NULL),
(2, 'XYZ Solutions', 'Priya Patel', '9988776655', '9765432109', 'Mumbai Maharashtra', 'Andheri East Mumbai', 'priya@xyzsolutions.com', 'Rental', 'Yes', 1, 'Dinstar', '0', 0, '', '', 12000.00, 0.00, '0', '0000-00-00', 'Unpaid', 12000.00, 5, 5000.00, 2500.00, '27XYZAB5678K2Z3', NULL, 'off', NULL),
(3, 'Techno Hub', 'Amit Joshi', '9898989898', '9000000001', 'Nashik Maharashtra', 'College Road Nashik', 'amit@technohub.in', 'Purchase', 'No', 3, 'OpenVox', '0', 2, 'HP ProLiant', '22:33:44:55:66:77', 45000.00, 90000.00, '0', '2027-06-30', 'Unpaid', 35000.00, 15, 22500.00, 7500.00, '27LMNOP9876Q1Z2', NULL, 'off', NULL),
(4, 'Agency 46', 'adfasd123456789', '1234567890', '9000000001', 'pune', 'karad', 'codingmaster859@gmail.com', 'Rental', 'Yes', 5, 'OpenVox', '234', 2, 'HP ProLiant', '09876', 0.08, 0.04, '0.05', '2026-07-14', 'Paid', 0.17, 6, 0.06, 0.03, '27LMNOP9876Q1Z2', NULL, 'on', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_backup_2026_07_14`
--

CREATE TABLE `client_backup_2026_07_14` (
  `id` int(11) NOT NULL DEFAULT 0,
  `agency_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `support_alt_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_rental` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `only_software` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_quantity` int(11) DEFAULT NULL,
  `gateway_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_mac_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_quantity` int(11) DEFAULT NULL,
  `server_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_mac_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_price` decimal(10,2) DEFAULT NULL,
  `server_price` decimal(10,2) DEFAULT NULL,
  `amc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amc_year_month` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `paid` decimal(10,2) DEFAULT 0.00,
  `unpaid` decimal(10,2) DEFAULT 0.00,
  `amc_expiry` date DEFAULT NULL,
  `payment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_outstanding` decimal(10,2) DEFAULT NULL,
  `headphone_total_count` int(11) DEFAULT 0,
  `paid_headphone_price` decimal(10,2) DEFAULT 0.00,
  `unpaid_headphone_price` decimal(10,2) DEFAULT 0.00,
  `gst_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_screenshot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headphones_total_count` int(11) DEFAULT NULL,
  `headphones_price` decimal(10,2) DEFAULT NULL,
  `unpaid_headphones_price` decimal(10,2) DEFAULT NULL,
  `gst_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `service` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_photos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `client_backup_2026_07_14`
--

INSERT INTO `client_backup_2026_07_14` (`id`, `agency_name`, `owner_name`, `alt_name`, `mobile_no`, `alt_phone`, `support_alt_no`, `address`, `alt_address`, `mail_id`, `purchase_rental`, `only_software`, `gateway_quantity`, `gateway_name`, `gateway_mac_id`, `server_quantity`, `server_name`, `server_mac_id`, `gateway_price`, `server_price`, `amc`, `amc_year_month`, `date`, `time`, `paid`, `unpaid`, `amc_expiry`, `payment_status`, `total_outstanding`, `headphone_total_count`, `paid_headphone_price`, `unpaid_headphone_price`, `gst_no`, `payment_screenshot`, `headphones_total_count`, `headphones_price`, `unpaid_headphones_price`, `gst_number`, `created_at`, `updated_at`, `service`, `payment_photos`) VALUES
(1, 'ABC Technologies', 'Rahul Sharma', NULL, '9876543210', NULL, '9123456780', 'Pune Maharashtra', 'Shivajinagar Pune', 'rahul@abctech.com', 'Purchase', 'No', 2, 'OpenVox', '0', 1, 'Dell PowerEdge', '11:22:33:44:55:66', 25000.00, 50000.00, '0', NULL, NULL, NULL, 0.00, 0.00, '2026-12-31', 'Paid', 0.00, 0, 0.00, 0.00, NULL, NULL, 10, 15000.00, 0.00, '27ABCDE1234F1Z5', NULL, NULL, 'off', NULL),
(2, 'XYZ Solutions', 'Priya Patel', NULL, '9988776655', NULL, '9765432109', 'Mumbai Maharashtra', 'Andheri East Mumbai', 'priya@xyzsolutions.com', 'Rental', 'Yes', 1, 'Dinstar', '0', 0, '', '', 12000.00, 0.00, '0', NULL, NULL, NULL, 0.00, 0.00, '0000-00-00', 'Unpaid', 12000.00, 0, 0.00, 0.00, NULL, NULL, 5, 5000.00, 2500.00, '27XYZAB5678K2Z3', NULL, NULL, 'off', NULL),
(3, 'Techno Hub', 'Amit Joshi', NULL, '9898989898', NULL, '9000000001', 'Nashik Maharashtra', 'College Road Nashik', 'amit@technohub.in', 'Purchase', 'No', 3, 'OpenVox', '0', 2, 'HP ProLiant', '22:33:44:55:66:77', 45000.00, 90000.00, '0', NULL, NULL, NULL, 0.00, 0.00, '2027-06-30', 'Unpaid', 35000.00, 0, 0.00, 0.00, NULL, NULL, 15, 22500.00, 7500.00, '27LMNOP9876Q1Z2', NULL, NULL, 'off', NULL),
(4, 'Agency 46', 'adfasd123456789', NULL, '1234567890', NULL, '9000000001', 'pune', 'karad', 'codingmaster859@gmail.com', 'Rental', 'Yes', 5, 'OpenVox', '234', 2, 'HP ProLiant', '09876', 0.08, 0.04, '0.05', NULL, NULL, NULL, 0.00, 0.00, '2026-07-14', 'Paid', 0.17, 0, 0.00, 0.00, NULL, NULL, 6, 0.06, 0.03, '27LMNOP9876Q1Z2', NULL, NULL, 'on', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_acc`
--

CREATE TABLE `email_acc` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `app_password` varchar(255) DEFAULT NULL,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `encryption` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_acc`
--

INSERT INTO `email_acc` (`id`, `user_id`, `email`, `app_password`, `smtp_host`, `smtp_port`, `encryption`, `created_at`, `updated_at`) VALUES
(2, 2, 'codingmaster859@gmail.com', 'pWjjCdNDGR9JYrRVVhGmkfTwEbkg4iBAzE0gMdMSJmeXv7ayHrXYYcsa0WYKCAeN', 'smtp.gmail.com', 587, 'tls', '2026-07-13 11:43:48', '2026-07-13 11:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `email_drafts`
--

CREATE TABLE `email_drafts` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `to_email` text DEFAULT NULL,
  `cc_email` text DEFAULT NULL,
  `bcc_email` text DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_folders`
--

CREATE TABLE `email_folders` (
  `id` int(11) NOT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `folder` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_starred` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `to_email` text DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `quote_number` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_contact` varchar(50) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_contact_person` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `supplier_gstin` varchar(50) DEFAULT NULL,
  `buyer_gstin` varchar(50) DEFAULT NULL,
  `place_of_supply` varchar(255) DEFAULT NULL,
  `hsn_sac_code` varchar(50) DEFAULT NULL,
  `gst_mode` varchar(20) DEFAULT NULL,
  `billing_type` varchar(20) DEFAULT 'monthly',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `taxable_value` decimal(10,2) DEFAULT NULL,
  `cgst_rate` decimal(5,2) DEFAULT NULL,
  `sgst_rate` decimal(5,2) DEFAULT NULL,
  `igst_rate` decimal(5,2) DEFAULT NULL,
  `cgst_amount` decimal(10,2) DEFAULT NULL,
  `sgst_amount` decimal(10,2) DEFAULT NULL,
  `igst_amount` decimal(10,2) DEFAULT NULL,
  `other_charges` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_ifsc_code` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `client_name_footer` varchar(255) DEFAULT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_json`)),
  `pdf_path` varchar(255) DEFAULT NULL,
  `bill_type` varchar(50) DEFAULT NULL,
  `bill_number` varchar(50) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `amount_in_words` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'unpaid',
  `payment_verified` tinyint(1) DEFAULT 0,
  `payment_verified_by` int(11) DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `payment_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `quote_number`, `customer_id`, `invoice_date`, `due_date`, `status`, `currency`, `company_name`, `company_address`, `company_contact`, `company_email`, `customer_name`, `customer_contact_person`, `customer_address`, `supplier_gstin`, `buyer_gstin`, `place_of_supply`, `hsn_sac_code`, `gst_mode`, `billing_type`, `subtotal`, `discount`, `taxable_value`, `cgst_rate`, `sgst_rate`, `igst_rate`, `cgst_amount`, `sgst_amount`, `igst_amount`, `other_charges`, `grand_total`, `bank_account_name`, `bank_account_number`, `bank_ifsc_code`, `bank_name`, `bank_branch`, `terms_conditions`, `notes`, `client_name_footer`, `items_json`, `pdf_path`, `bill_type`, `bill_number`, `reference`, `amount_in_words`, `created_at`, `updated_at`, `payment_screenshot`, `payment_status`, `payment_verified`, `payment_verified_by`, `payment_verified_at`, `payment_notes`) VALUES
(6, '2026/1034', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40\r\n              ', 'Email: info@ebiztech.in', 'LSKDJF', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra\r\n                            401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '', 'intra', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E\r\n                                Business\r\n                                Technology Solutions', '610000000062910', '', 'Saraswat Co-Op\r\n                                Bank\r\n                                Ltd.', 'Tilak Road,\r\n                                Pune', '', '', '', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) —\\n                                Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center\\n                                Suite\\n                                Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/1034', '2025/1021', '', '2026-07-13 12:33:53', '2026-07-13 12:33:53', NULL, 'unpaid', 0, NULL, NULL, NULL),
(7, '2026/1023', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40\r\n              ', 'Email: info@ebiztech.in', 'aksdf', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra\r\n                            401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '', 'intra', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E\r\n                                Business\r\n                                Technology Solutions', '610000000062910', '', 'Saraswat Co-Op\r\n                                Bank\r\n                                Ltd.', 'Tilak Road,\r\n                                Pune', '', '', '', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) —\\n                                Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center\\n                                Suite\\n                                Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/1023', '2025/1021', '', '2026-07-13 13:52:37', '2026-07-13 13:52:37', NULL, 'unpaid', 0, NULL, NULL, NULL),
(8, '2026/105', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'paid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40\r\n              ', 'Email: info@ebiztech.in', 'SOHAM BHAIIKA DHABA', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra\r\n                            401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '', 'inter', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E\r\n                                Business\r\n                                Technology Solutions', '610000000062910', '', 'Saraswat Co-Op\r\n                                Bank\r\n                                Ltd.', 'Tilak Road,\r\n                                Pune', '', '', '', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) —\\n                                Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center\\n                                Suite\\n                                Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/105', '2025/1021', '', '2026-07-13 14:01:57', '2026-07-13 14:01:57', NULL, 'unpaid', 0, NULL, NULL, NULL),
(9, '2026/10341', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'paid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40\r\n              ', 'Email: info@ebiztech.in', 'DHABA', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra\r\n                            401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '', 'inter', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E\r\n                                Business\r\n                                Technology Solutions', '610000000062910', '', 'Saraswat Co-Op\r\n                                Bank\r\n                                Ltd.', 'Tilak Road,\r\n                                Pune', '', '', '', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) —\\n                                Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center\\n                                Suite\\n                                Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/10341', '2025/1021', '', '2026-07-13 14:03:20', '2026-07-13 14:03:20', NULL, 'unpaid', 0, NULL, NULL, NULL),
(10, '2026/1551', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40\r\n              ', 'Email: info@ebiztech.in', 'Blink tata', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra\r\n                            401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '', 'intra', 'monthly', 46000.00, 0.00, 46000.00, 9.00, 9.00, 18.00, 4140.00, 4140.00, 8280.00, 0.00, 54280.00, 'E\r\n                                Business\r\n                                Technology Solutions', '610000000062910', '', 'Saraswat Co-Op\r\n                                Bank\r\n                                Ltd.', 'Tilak Road,\r\n                                Pune', '', 'hi', '', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":3,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) —\\n                                Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":4,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center\\n                                Suite\\n                                Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/1551', '2025/1021', '', '2026-07-14 10:19:21', '2026-07-14 10:19:21', NULL, 'unpaid', 0, NULL, NULL, NULL),
(11, '2026/1021', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'paid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40\r\n              ', 'Email: info@ebiztech.in', 'Blink Finance', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra\r\n                            401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '998314', 'intra', 'rental', 46000.00, 0.00, 46000.00, 9.00, 9.00, 18.00, 4140.00, 4140.00, 4140.00, 0.00, 54280.00, 'E\r\n                                Business\r\n                                Technology Solutions', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op\r\n                                Bank\r\n                                Ltd.', 'Tilak Road,\r\n                                Pune', 'Hardware warranty and support will be provided by the\r\n                                manufacturer as\r\n                                per manufacturer\'s policy.\r\nPayments should be made in favor of \"E Business Technology\r\n                                Solutions\".\r\nServices may be deactivated without prior notice if payment is\r\n                                not\r\n                                made on time.\r\nPayment can be made after deducting applicable taxes.\r\nPayment should be made within the defined credit period.', 'hi', 'Blink\r\n                            Finance', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":3,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) —\\n                                Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":4,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center\\n                                Suite\\n                                Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/1021', '2025/1021', 'Rupees Fifty Four Thousand Two Hundred Eighty Only', '2026-07-14 10:59:01', '2026-07-14 11:43:50', NULL, 'unpaid', 0, NULL, NULL, ''),
(12, '2026/1028', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40 97 97', 'Email: info@ebiztech.in', 'Blink Finance', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra 401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '998314', 'inter', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E Business Technology Solutions', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', 'Hardware warranty and support will be provided by the manufacturer as per manufacturer\'s policy.\r\nPayments should be made in favor of \"E Business Technology Solutions\".\r\nServices may be deactivated without prior notice if payment is not made on time.\r\nPayment can be made after deducting applicable taxes.\r\nPayment should be made within the defined credit period.', '', 'Blink Finance', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) — Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', NULL, 'Invoice', '2026/1028', '2025/1021', 'Rupees Twenty Seven Thousand One Hundred Forty Only', '2026-07-29 11:30:29', '2026-07-29 11:30:29', NULL, 'unpaid', 0, NULL, NULL, NULL),
(13, '2026/10', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'paid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40 97 97', 'Email: info@ebiztech.in', 'Test', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra 401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '998314', 'intra', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E Business Technology Solutions', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', 'Hardware warranty and support will be provided by the manufacturer as per manufacturer\'s policy.\r\nPayments should be made in favor of \"E Business Technology Solutions\".\r\nServices may be deactivated without prior notice if payment is not made on time.\r\nPayment can be made after deducting applicable taxes.\r\nPayment should be made within the defined credit period.', '', 'Blink Finance', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) — Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', '', 'Invoice', '2026/10', '2025/1021', 'Rupees Twenty Seven Thousand One Hundred Forty Only', '2026-07-29 11:46:42', '2026-07-29 11:46:42', NULL, 'unpaid', 0, NULL, NULL, NULL),
(14, '2026/11', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40 97 97', 'Email: info@ebiztech.in', 'TEST2', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra 401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '998314', 'intra', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E Business Technology Solutions', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', 'Hardware warranty and support will be provided by the manufacturer as per manufacturer\'s policy.\r\nPayments should be made in favor of \"E Business Technology Solutions\".\r\nServices may be deactivated without prior notice if payment is not made on time.\r\nPayment can be made after deducting applicable taxes.\r\nPayment should be made within the defined credit period.', '', 'Blink Finance', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) — Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', '', 'Invoice', '2026/11', '2025/1021', 'Rupees Twenty Seven Thousand One Hundred Forty Only', '2026-07-29 11:49:33', '2026-07-29 11:49:33', NULL, 'unpaid', 0, NULL, NULL, NULL),
(15, '2026/1', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40 97 97', 'Email: info@ebiztech.in', 'TEST3', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra 401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '998314', 'intra', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E Business Technology Solutions', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', 'Hardware warranty and support will be provided by the manufacturer as per manufacturer\'s policy.\r\nPayments should be made in favor of \"E Business Technology Solutions\".\r\nServices may be deactivated without prior notice if payment is not made on time.\r\nPayment can be made after deducting applicable taxes.\r\nPayment should be made within the defined credit period.', '', 'Blink Finance', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) — Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', '', 'Invoice', '2026/1', '2025/1021', 'Rupees Twenty Seven Thousand One Hundred Forty Only', '2026-07-29 11:51:01', '2026-07-29 11:51:01', NULL, 'unpaid', 0, NULL, NULL, NULL),
(16, '2026/2021', '2025/1021', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unpaid', '₹', 'E Business Technology Solutions', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune – 411043.', 'Contact: 77 55 97 97 97 / 92 70 40 97 97', 'Email: info@ebiztech.in', 'TEST4', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd,, beside Sagar Sangam Hotel, CHSL, Geeta Nagar,, Mira Road East, Thane, Mira Bhayandar, Maharashtra 401107', '27AAMFE3315J1ZD', '', 'Maharashtra (27)', '998314', 'intra', 'monthly', 23000.00, 0.00, 23000.00, 9.00, 9.00, 18.00, 2070.00, 2070.00, 4140.00, 0.00, 27140.00, 'E Business Technology Solutions', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', 'Hardware warranty and support will be provided by the manufacturer as per manufacturer\'s policy.\r\nPayments should be made in favor of \"E Business Technology Solutions\".\r\nServices may be deactivated without prior notice if payment is not made on time.\r\nPayment can be made after deducting applicable taxes.\r\nPayment should be made within the defined credit period.', '', 'Blink Finance', '[{\"number\":1,\"product\":\"Gateway\",\"description\":\"32 Port GSM Gateway\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":23000,\"amount\":23000},{\"number\":2,\"product\":\"Dialer Server\",\"description\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) — Dialer Server\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0},{\"number\":3,\"product\":\"AMC\",\"description\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite Software — Installation, Configuration and AMC\",\"period\":\"06 May 2026\",\"quantity\":1,\"price\":0,\"amount\":0}]', '', 'Invoice', '2026/2021', '2025/1021', 'Rupees Twenty Seven Thousand One Hundred Forty Only', '2026-07-29 11:55:26', '2026-07-29 11:55:26', NULL, 'unpaid', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_management`
--

CREATE TABLE `leave_management` (
  `leave_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `applied_on` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_management`
--

INSERT INTO `leave_management` (`leave_id`, `user_id`, `name`, `leave_type`, `from_date`, `to_date`, `reason`, `status`, `applied_on`, `rejection_reason`) VALUES
(1, 5, 'gosavi', 'Sick Leave', '2026-07-30', '2026-07-31', 'Hi im sick', 'Approved', NULL, NULL),
(2, 18, 'ex', 'Sick Leave', '2026-07-30', '2026-07-31', 'Idk', 'Rejected', NULL, 'Ik'),
(3, 6, 'ram', 'Casual Leave', '2026-08-01', '2026-08-03', 'gfiy', 'Pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `lock_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `permanent_lock_until` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `email`, `attempts`, `lock_until`, `created_at`, `updated_at`, `permanent_lock_until`) VALUES
(1, 'sdfsdf', 'sdfsdf', 7, NULL, '2026-07-27 07:02:11', '2026-07-27 07:34:52', NULL),
(2, 'sfsdf', 'sfsdf', 8, NULL, '2026-07-27 07:02:20', '2026-07-27 07:37:51', NULL),
(3, 'dfsdf', 'dfsdf', 3, NULL, '2026-07-27 07:02:27', '2026-07-27 07:31:56', NULL),
(4, 'dfdfg', 'dfdfg', 1, NULL, '2026-07-27 07:02:34', '2026-07-27 07:02:34', NULL),
(5, 'dgdfgdfg', 'dgdfgdfg', 1, NULL, '2026-07-27 07:02:41', '2026-07-27 07:02:41', NULL),
(6, 'dgdfgdf', 'dgdfgdf', 1, NULL, '2026-07-27 07:02:47', '2026-07-27 07:02:47', NULL),
(7, 'sfdsf', 'sfdsf', 1, NULL, '2026-07-27 07:08:14', '2026-07-27 07:08:14', NULL),
(8, 'sdfsd', 'sdfsd', 13, NULL, '2026-07-27 07:08:29', '2026-07-27 07:43:43', '2026-07-28 13:13:43'),
(9, 'bc', 'bc', 1, NULL, '2026-07-27 07:09:26', '2026-07-27 07:09:26', NULL),
(10, 'fcf', 'fcf', 1, NULL, '2026-07-27 07:09:31', '2026-07-27 07:09:31', NULL),
(11, 'cfhggh', 'cfhggh', 1, NULL, '2026-07-27 07:09:37', '2026-07-27 07:09:37', NULL),
(12, 'fgcghhg', 'fgcghhg', 1, NULL, '2026-07-27 07:09:43', '2026-07-27 07:09:43', NULL),
(13, 'cfgfgf', 'cfgfgf', 1, NULL, '2026-07-27 07:09:48', '2026-07-27 07:09:48', NULL),
(14, 'ccfgfgfg', 'ccfgfgfg', 1, NULL, '2026-07-27 07:09:56', '2026-07-27 07:09:56', NULL),
(15, 'fgfgffgfgf', 'fgfgffgfgf', 1, NULL, '2026-07-27 07:10:08', '2026-07-27 07:10:08', NULL),
(16, 'dxggh', 'dxggh', 1, NULL, '2026-07-27 07:16:48', '2026-07-27 07:16:48', NULL),
(17, 'asdfs', 'asdfs', 1, NULL, '2026-07-27 07:16:55', '2026-07-27 07:16:55', NULL),
(18, 'dfsd', 'dfsd', 0, NULL, '2026-07-27 07:16:59', '2026-07-27 07:18:14', NULL),
(19, 'sdfs', 'sdfs', 4, NULL, '2026-07-27 07:17:13', '2026-07-27 07:35:46', NULL),
(21, 'sfd', 'sfd', 1, NULL, '2026-07-27 07:19:33', '2026-07-27 07:19:33', NULL),
(20, 'sdfds', 'sdfds', 2, NULL, '2026-07-27 07:18:29', '2026-07-27 07:32:37', NULL),
(22, 'xdfsd', 'xdfsd', 1, NULL, '2026-07-27 07:19:38', '2026-07-27 07:19:38', NULL),
(23, 'sfsfsd', 'sfsfsd', 1, NULL, '2026-07-27 07:19:43', '2026-07-27 07:19:43', NULL),
(24, 'sfsdfs', 'sfsdfs', 1, NULL, '2026-07-27 07:20:01', '2026-07-27 07:20:01', NULL),
(25, 'adasd', 'adasd', 1, NULL, '2026-07-27 07:20:07', '2026-07-27 07:20:07', NULL),
(26, 'ssdf', 'ssdf', 1, NULL, '2026-07-27 07:27:32', '2026-07-27 07:27:32', NULL),
(27, 'dfsdfsd', 'dfsdfsd', 1, NULL, '2026-07-27 07:27:56', '2026-07-27 07:27:56', NULL),
(28, 'sksld', 'sksld', 1, NULL, '2026-07-27 07:29:31', '2026-07-27 07:29:31', NULL),
(29, 'sdfsf', 'sdfsf', 1, NULL, '2026-07-27 07:29:45', '2026-07-27 07:29:45', NULL),
(30, 'dsjdn', 'dsjdn', 1, NULL, '2026-07-27 07:30:46', '2026-07-27 07:30:46', NULL),
(31, 'sfsf', 'sfsf', 1, NULL, '2026-07-27 07:31:48', '2026-07-27 07:31:48', NULL),
(32, 'fsdf', 'fsdf', 1, NULL, '2026-07-27 07:31:52', '2026-07-27 07:31:52', NULL),
(33, 'fsdfsdsdfsd', 'fsdfsdsdfsd', 1, NULL, '2026-07-27 07:32:10', '2026-07-27 07:32:10', NULL),
(34, 'efrwe', 'efrwe', 1, NULL, '2026-07-27 07:33:52', '2026-07-27 07:33:52', NULL),
(35, 'erwer', 'erwer', 1, NULL, '2026-07-27 07:33:56', '2026-07-27 07:33:56', NULL),
(36, 'sfsfs', 'sfsfs', 1, NULL, '2026-07-27 07:34:04', '2026-07-27 07:34:04', NULL),
(37, 'sfs', 'sfs', 1, NULL, '2026-07-27 07:34:16', '2026-07-27 07:34:16', NULL),
(38, 'wdsds', 'wdsds', 1, NULL, '2026-07-27 07:35:55', '2026-07-27 07:35:55', NULL),
(39, 'sdfsfs', 'sdfsfs', 1, NULL, '2026-07-27 07:35:59', '2026-07-27 07:35:59', NULL),
(40, 'asdssd', 'asdssd', 1, NULL, '2026-07-27 07:37:59', '2026-07-27 07:37:59', NULL),
(41, 'adf', 'adf', 1, NULL, '2026-07-27 07:38:13', '2026-07-27 07:38:13', NULL),
(42, 'asdsds', 'asdsds', 1, NULL, '2026-07-27 07:43:36', '2026-07-27 07:43:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `meeting_date` datetime DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `title`, `description`, `meeting_link`, `meeting_date`, `start_time`, `end_time`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Ebiztech', 'Ebiztech Urgent Meeting ', 'https://meet.jit.si/meeting_1785133243', '2026-07-24 00:00:00', '23:51:00', '14:51:00', 'Ended', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `meeting_attendees`
--

CREATE TABLE `meeting_attendees` (
  `id` int(11) NOT NULL,
  `meeting_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `joined_at` datetime DEFAULT NULL,
  `left_at` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `attendance_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meeting_notifications`
--

CREATE TABLE `meeting_notifications` (
  `id` int(11) NOT NULL,
  `meeting_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `notification_type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meeting_notifications`
--

INSERT INTO `meeting_notifications` (`id`, `meeting_id`, `user_id`, `is_read`, `notification_type`, `created_at`, `read_at`) VALUES
(1, 1, 5, 0, 'Scheduled', NULL, NULL),
(2, 1, 5, 0, 'Started', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_photos`
--

CREATE TABLE `payment_photos` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `quotation_date` date DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_contact` varchar(50) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `company_gst` varchar(50) DEFAULT NULL,
  `customer_company` varchar(255) DEFAULT NULL,
  `customer_contact` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_gst` varchar(50) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `taxable_value` decimal(10,2) DEFAULT NULL,
  `gst_rate` decimal(5,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `other_charges` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `grand_total_without_gst` decimal(10,2) DEFAULT NULL,
  `show_gst` tinyint(1) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_json`)),
  `bill_type` varchar(50) DEFAULT NULL,
  `bill_number` varchar(50) DEFAULT NULL,
  `quote_number` varchar(50) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `gst_mode` varchar(20) DEFAULT NULL,
  `cgst_rate` decimal(5,2) DEFAULT NULL,
  `sgst_rate` decimal(5,2) DEFAULT NULL,
  `igst_rate` decimal(5,2) DEFAULT NULL,
  `cgst_amount` decimal(10,2) DEFAULT NULL,
  `sgst_amount` decimal(10,2) DEFAULT NULL,
  `igst_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `client_name_footer` varchar(255) DEFAULT NULL,
  `amount_in_words` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'unpaid',
  `payment_verified` tinyint(1) DEFAULT 0,
  `payment_verified_by` int(11) DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `payment_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `quotation_number`, `quotation_date`, `reference`, `customer_id`, `valid_until`, `status`, `currency`, `company_name`, `company_address`, `company_contact`, `company_email`, `company_gst`, `customer_company`, `customer_contact`, `customer_address`, `customer_gst`, `customer_email`, `customer_phone`, `subtotal`, `discount`, `taxable_value`, `gst_rate`, `gst_amount`, `other_charges`, `grand_total`, `grand_total_without_gst`, `show_gst`, `bank_account_name`, `bank_account_number`, `bank_ifsc`, `bank_name`, `bank_branch`, `terms_conditions`, `items_json`, `bill_type`, `bill_number`, `quote_number`, `bill_date`, `due_date`, `gst_mode`, `cgst_rate`, `sgst_rate`, `igst_rate`, `cgst_amount`, `sgst_amount`, `igst_amount`, `notes`, `client_name_footer`, `amount_in_words`, `created_at`, `updated_at`, `payment_screenshot`, `payment_status`, `payment_verified`, `payment_verified_by`, `payment_verified_at`, `payment_notes`) VALUES
(1, 'Q2026/1021', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'Blink Finance', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—', '—', '—', 95500.00, 0.00, 95500.00, 18.00, 17190.00, 0.00, 112690.00, 95500.00, 1, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                                            <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                                            <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                                            <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                                            <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                                            <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                                            <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                                            <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                                            <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                                            <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                                            <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                                    ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":45000,\"amount\":45000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-29 12:01:46', NULL, 'unpaid', 0, NULL, NULL, NULL),
(2, 'Q2026/1022', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'SOHAM', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—', '—', '—', 95500.00, 0.00, 95500.00, 18.00, 17190.00, 0.00, 112690.00, 95500.00, 1, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                      <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                      <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                      <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                      <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                      <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                      <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                      <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                      <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                      <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                      <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                  ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":45000,\"amount\":45000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unpaid', 0, NULL, NULL, NULL),
(4, 'Q2026/104', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'wer', 'sdfdge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—', '—', '—', 95500.00, 0.00, 95500.00, 18.00, 0.00, 0.00, 95500.00, 95500.00, 0, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                      <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                      <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                      <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                      <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                      <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                      <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                      <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                      <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                      <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                      <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                  ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":45000,\"amount\":45000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unpaid', 0, NULL, NULL, NULL),
(5, 'Q2026/1028', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'TBSM', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—', '—', '—', 95500.00, 0.00, 95500.00, 18.00, 17190.00, 0.00, 112690.00, 95500.00, 1, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                      <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                      <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                      <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                      <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                      <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                      <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                      <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                      <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                      <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                      <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                  ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":45000,\"amount\":45000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unpaid', 0, NULL, NULL, NULL),
(6, 'Q2026/1026', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'SOHAM', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—', '—', '—', 95500.00, 0.00, 95500.00, 18.00, 17190.00, 0.00, 112690.00, 95500.00, 1, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                      <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                      <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                      <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                      <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                      <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                      <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                      <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                      <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                      <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                      <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                  ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":45000,\"amount\":45000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unpaid', 0, NULL, NULL, NULL),
(7, 'Q2026/15241', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'Blink Finance', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—97865', '—codemaster624.gmail.com', '—78965', 410500.00, 0.00, 410500.00, 18.00, 73890.00, 0.00, 484390.00, 410500.00, 1, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                      <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                      <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                      <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                      <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                      <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                      <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                      <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                      <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                      <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                      <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                  ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":8,\"price\":45000,\"amount\":360000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unpaid', 0, NULL, NULL, NULL),
(8, 'Q2026/15441', '0000-00-00', 'REF/2026/001', 0, '0000-00-00 00:00:00', 'draft', NULL, 'E BUSINESS TECHNOLOGY SOLUTIONS', 'Office No-89, D-Wing, 4th Floor, Dhankawadi, Pune- 411043.', '77 55 97 97 97 / 92 70 40 97 97', 'info@ebiztech.in', '27AAMFE3315J1ZD', 'Blink Finance', 'Ashish Ghadge', 'Sai Prasad Complex, A 001/002, Mira Bhayander Link Rd, beside SAGAR SANGAM HOTEL, CHSL, Geeta Nagar, Mira Road East, THANE, Mira Bhayandar, Maharashtra 401107', '—465', '—sdxfgvbjm', '—7984513297', 95500.00, 0.00, 95500.00, 18.00, 0.00, 0.00, 95500.00, 95500.00, 0, 'E BUSINESS TECHNOLOGY SOLUTIONS', '610000000062910', 'SRCB0000038', 'Saraswat Co-Op Bank Ltd.', 'Tilak Road, Pune', '\r\n                      <li>This quotation is valid for <strong>15 days</strong> from the date of issue.</li>\r\n                      <li>All payments shall be made in favor of <strong>E Business Technology Solutions</strong>.</li>\r\n                      <li>A <strong>minimum lock-in period of 6 months</strong> is mandatory from the date of service activation.</li>\r\n                      <li>Prices quoted are exclusive of GST and other applicable taxes.</li>\r\n                      <li>Hardware warranty, if applicable, shall be covered by the respective manufacturer\'s warranty policy.</li>\r\n                      <li>Any additional customization, training, or support beyond the agreed scope shall be chargeable.</li>\r\n                      <li>All payments made towards setup, licensing, subscription, and support services are <strong>non-refundable</strong>.</li>\r\n                      <li>Any disputes arising from the services shall be subject to the jurisdiction of <strong>Pune, Maharashtra</strong> only.</li>\r\n                      <li>This quotation is subject to stock availability and price changes without prior notice.</li>\r\n                      <li>Acceptance of this quotation constitutes an agreement to the terms and conditions stated herein.</li>\r\n                  ', '[{\"product\":\"GATEWAY\",\"desc\":\"32 Port GSM Gateway\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":23000,\"amount\":23000},{\"product\":\"DIALER SERVER\",\"desc\":\"Dell PowerEdge-R710, 16 Core, 16GB RAM, 1TB HDD (Refurbished) - Dialer Server\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":27500,\"amount\":27500},{\"product\":\"AMC\",\"desc\":\"Domestic Call Center Open Source Omni-channel Contact Center Suite - Installation, Configuration & AMC\",\"period\":\"01-06-2026\",\"qty\":1,\"price\":45000,\"amount\":45000}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unpaid', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `unified_bills`
-- (See below for the actual view)
--
CREATE TABLE `unified_bills` (
`id` int(11)
,`bill_type` varchar(9)
,`bill_number` varchar(50)
,`bill_number_display` varchar(50)
,`quote_number` varchar(50)
,`customer_id` int(11)
,`bill_date` datetime /* mariadb-5.3 */
,`due_date` datetime /* mariadb-5.3 */
,`status` varchar(50)
,`currency` varchar(10)
,`company_name` varchar(255)
,`company_address` mediumtext
,`company_contact` varchar(50)
,`company_email` varchar(255)
,`company_gst` varchar(50)
,`customer_name` varchar(255)
,`customer_contact_person` varchar(255)
,`customer_address` mediumtext
,`customer_gst` varchar(50)
,`customer_email` varchar(255)
,`customer_phone` varchar(20)
,`place_of_supply` varchar(255)
,`hsn_sac_code` varchar(50)
,`gst_mode` varchar(20)
,`gst_rate` decimal(5,2)
,`cgst_rate` decimal(5,2)
,`sgst_rate` decimal(5,2)
,`igst_rate` decimal(5,2)
,`subtotal` decimal(10,2)
,`discount` decimal(10,2)
,`taxable_value` decimal(10,2)
,`cgst_amount` decimal(10,2)
,`sgst_amount` decimal(10,2)
,`igst_amount` decimal(10,2)
,`gst_amount` decimal(10,2)
,`other_charges` decimal(10,2)
,`grand_total` decimal(10,2)
,`amount_in_words` varchar(255)
,`bank_account_name` varchar(255)
,`bank_account_number` varchar(50)
,`bank_ifsc_code` varchar(50)
,`bank_name` varchar(255)
,`bank_branch` varchar(255)
,`terms_conditions` mediumtext
,`notes` mediumtext
,`client_name_footer` varchar(255)
,`items_json` longtext
,`created_at` datetime /* mariadb-5.3 */
,`updated_at` datetime /* mariadb-5.3 */
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `email_id` varchar(255) DEFAULT NULL,
  `email_pass` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `address`, `employee_id`, `email_id`, `email_pass`, `contact_no`, `username`, `password_hash`, `designation`, `role`, `city`, `profile_picture`, `status`, `session_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', '123 Main Street, Mumbai, Maharashtra - 400001', 'EMP001', 'admin@example.com', 'admin123', '+91 9876543210', 'admin', '0192023a7bbd73250516f069df18b500', 'Senior Administrator', 'Admin', 'Mumbai', 'profile_admin.jpg', 'active', NULL, '2026-07-09 11:12:39', '2026-07-09 11:12:39'),
(2, 'Aryan', 'ABCD', '100', 'codingmaster859@gmail.com', NULL, '7972196282', 'aryan', '202cb962ac59075b964b07152d234b70', 'CEO', 'Admin', 'pune', '1783575851_0140-015.png', 'active', NULL, NULL, NULL),
(3, 'soham', 'sdf', '104', 'soham@gmai.com', NULL, '1234567890', 'soham', '202cb962ac59075b964b07152d234b70', 'hsld', 'User', 'sadf', '', 'active', NULL, NULL, NULL),
(5, 'gosavi', 'hi', '9', 'bigwolf22@gmail.com', NULL, '1234567890', 'AG', '202cb962ac59075b964b07152d234b70', 'staff', 'User', 'karad', '1783575851_0140-015.png', 'active', NULL, NULL, NULL),
(6, 'ram', 'NIPANI, Chhatrapati Sambhajinagar Maharashtra India431007', 'Emp04', 'rambhalekar08@gmail.com', NULL, '9309051187', 'ram88', '1cfc8bbd5d6a781f5b698c58de0acd0b', 'Developer', 'User', 'Chh. Sambhajinagar', '1785139458_attendance.png', 'active', '147e75d6702878b12a901e5d1d356621e27609e083e5b91884c4d5401fe0ac5c', NULL, NULL),
(7, 'Soham Tarate', 'pune', '1462', 'soham@gmail.com', NULL, '7777777778', 'Soham_', '81dc9bdb52d04dc20036dbd8313ed055', 'CEO', 'Admin', 'Pune', '1785217273_Screenshot 2026-07-15 131518.png', 'active', 'c81b247643e17d8e5edba67d79fd4488542a5c94ac5434f87c4bcbdf045dda52', NULL, NULL),
(18, 'ex', 'kdjf', '21093', 'j@gmail.com', NULL, '1290399', 'ex', '202cb962ac59075b964b07152d234b70', 'ksdjf', 'Employee', 'dkjf', '1785222664_lukas-souza-zBrNJfN76BA-unsplash.jpg', 'active', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `activity_id` int(11) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `activity_details` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity`
--

INSERT INTO `user_activity` (`activity_id`, `employee_id`, `employee_name`, `ip_address`, `activity_type`, `login_time`, `logout_time`, `activity_details`, `created_at`) VALUES
(1, 'EMP001', 'Admin User', '::1', 'Login', '2026-07-09 11:12:50', NULL, 'User logged in', NULL),
(2, '1', 'Admin User', '::1', 'Logout', NULL, '2026-07-09 11:14:14', 'User logged out', NULL),
(3, '100', 'Aryan', '::1', 'Login', '2026-07-09 11:14:17', NULL, 'User logged in', NULL),
(4, '100', 'Aryan', '::1', 'Login', '2026-07-09 11:20:23', NULL, 'User logged in', NULL),
(5, 'EMP001', 'Admin User', '::1', 'Login', '2026-07-09 12:15:40', NULL, 'User logged in', NULL),
(6, '100', 'Aryan', '::1', 'Login', '2026-07-09 13:39:43', NULL, 'User logged in', NULL),
(7, '2', 'Aryan', '::1', 'Logout', NULL, '2026-07-09 13:50:12', 'User logged out', NULL),
(8, '100', 'Aryan', '::1', 'Login', '2026-07-09 13:52:28', NULL, 'User logged in', NULL),
(9, '104', 'soham', '::1', 'Login', '2026-07-09 13:56:53', NULL, 'User logged in', NULL),
(10, '2', 'Aryan', '::1', 'Logout', NULL, '2026-07-09 14:05:46', 'User logged out', NULL),
(11, '104', 'soham', '::1', 'Login', '2026-07-09 14:05:51', NULL, 'User logged in', NULL),
(12, '3', 'soham', '::1', 'Logout', NULL, '2026-07-09 14:08:09', 'User logged out', NULL),
(13, '100', 'Aryan', '::1', 'Login', '2026-07-10 11:01:25', NULL, 'User logged in', NULL),
(14, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:04:45', NULL, 'User logged in', NULL),
(15, '100', 'Aryan', '192.168.0.55', 'Login', '2026-07-10 12:04:48', NULL, 'User logged in', NULL),
(16, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:05:05', NULL, 'User logged in', NULL),
(17, '100', 'Aryan', '192.168.0.55', 'Login', '2026-07-10 12:05:41', NULL, 'User logged in', NULL),
(18, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:06:29', NULL, 'User logged in', NULL),
(19, '2', 'Aryan', '192.168.0.42', 'Logout', NULL, '2026-07-10 12:07:26', 'User logged out', NULL),
(20, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:07:33', NULL, 'User logged in', NULL),
(21, '100', 'Aryan', '192.168.0.55', 'Login', '2026-07-10 12:07:33', NULL, 'User logged in', NULL),
(22, '104', 'soham', '192.168.0.42', 'Login', '2026-07-10 12:07:47', NULL, 'User logged in', NULL),
(23, '2', 'Aryan', '192.168.0.55', 'Logout', NULL, '2026-07-10 12:07:58', 'User logged out', NULL),
(24, '104', 'soham', '192.168.0.55', 'Login', '2026-07-10 12:08:14', NULL, 'User logged in', NULL),
(25, '3', 'soham', '192.168.0.55', 'Logout', NULL, '2026-07-10 12:08:25', 'User logged out', NULL),
(26, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:08:49', NULL, 'User logged in', NULL),
(27, '100', 'Aryan', '192.168.0.55', 'Login', '2026-07-10 12:09:05', NULL, 'User logged in', NULL),
(28, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:09:21', NULL, 'User logged in', NULL),
(29, '2', 'Aryan', '192.168.0.42', 'Logout', NULL, '2026-07-10 12:09:32', 'User logged out', NULL),
(30, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 12:11:29', NULL, 'User logged in', NULL),
(31, 'EMP001', 'Admin User', '192.168.0.55', 'Login', '2026-07-10 12:11:34', NULL, 'User logged in', NULL),
(32, '1', 'Admin User', '192.168.0.55', 'Logout', NULL, '2026-07-10 12:21:31', 'User logged out', NULL),
(33, 'EMP001', 'Admin User', '192.168.0.55', 'Login', '2026-07-10 12:22:23', NULL, 'User logged in', NULL),
(34, '1', 'Admin User', '192.168.0.55', 'Logout', NULL, '2026-07-10 12:24:57', 'User logged out', NULL),
(35, '9', 'gosavi', '192.168.0.55', 'Login', '2026-07-10 12:25:12', NULL, 'User logged in', NULL),
(36, '5', 'gosavi', '192.168.0.55', 'Logout', NULL, '2026-07-10 12:30:50', 'User logged out', NULL),
(37, '9', 'gosavi', '192.168.0.55', 'Login', '2026-07-10 12:30:58', NULL, 'User logged in', NULL),
(38, '5', 'gosavi', '192.168.0.55', 'Logout', NULL, '2026-07-10 12:35:14', 'User logged out', NULL),
(39, 'EMP001', 'Admin User', '192.168.0.55', 'Login', '2026-07-10 12:35:29', NULL, 'User logged in', NULL),
(40, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 13:54:24', NULL, 'User logged in', NULL),
(41, 'EMP001', 'Admin User', '192.168.0.55', 'Login', '2026-07-10 13:54:39', NULL, 'User logged in', NULL),
(42, 'EMP001', 'Admin User', '192.168.0.55', 'Login', '2026-07-10 15:10:32', NULL, 'User logged in', NULL),
(43, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 15:11:21', NULL, 'User logged in', NULL),
(44, '100', 'Aryan', '192.168.0.42', 'Login', '2026-07-10 15:12:25', NULL, 'User logged in', NULL),
(45, 'EMP001', 'Admin User', '192.168.0.46', 'Login', '2026-07-13 10:44:13', NULL, 'User logged in', NULL),
(46, '100', 'Aryan', '192.168.0.50', 'Login', '2026-07-13 10:49:33', NULL, 'User logged in', NULL),
(47, '100', 'Aryan', '192.168.0.50', 'Login', '2026-07-13 13:44:32', NULL, 'User logged in', NULL),
(48, 'EMP001', 'Admin User', '192.168.0.46', 'Login', '2026-07-13 14:04:09', NULL, 'User logged in', NULL),
(49, 'EMP001', 'Admin User', '192.168.0.46', 'Login', '2026-07-14 10:18:10', NULL, 'User logged in', NULL),
(50, 'EMP001', 'Admin User', '192.168.0.61', 'Login', '2026-07-14 11:04:02', NULL, 'User logged in', NULL),
(51, '100', 'Aryan', '192.168.0.46', 'Login', '2026-07-14 11:09:35', NULL, 'User logged in', NULL),
(52, '100', 'Aryan', '192.168.0.46', 'Login', '2026-07-14 13:27:29', NULL, 'User logged in', NULL),
(53, 'EMP001', 'Admin User', '192.168.0.61', 'Login', '2026-07-14 13:46:03', NULL, 'User logged in', NULL),
(54, '100', 'Aryan', '192.168.0.46', 'Login', '2026-07-15 10:20:48', NULL, 'User logged in', NULL),
(55, '104', 'soham', '192.168.0.40', 'Login', '2026-07-15 10:26:52', NULL, 'User logged in', NULL),
(56, '3', 'soham', '192.168.0.40', 'Logout', NULL, '2026-07-15 10:32:41', 'User logged out', NULL),
(57, '9', 'gosavi', '192.168.0.40', 'Login', '2026-07-15 10:33:03', NULL, 'User logged in', NULL),
(58, '5', 'gosavi', '192.168.0.40', 'Logout', NULL, '2026-07-15 10:39:20', 'User logged out', NULL),
(59, 'EMP001', 'Admin User', '192.168.0.40', 'Login', '2026-07-15 10:39:45', NULL, 'User logged in', NULL),
(60, '1', 'Admin User', '192.168.0.40', 'Logout', NULL, '2026-07-15 11:10:56', 'User logged out', NULL),
(61, '104', 'soham', '192.168.0.40', 'Login', '2026-07-15 11:11:00', NULL, 'User logged in', NULL),
(62, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-15 11:11:20', NULL, 'User logged in', NULL),
(63, '100', 'Aryan', '192.168.0.46', 'Login', '2026-07-15 11:13:04', NULL, 'User logged in', NULL),
(64, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-15 11:13:16', NULL, 'User logged in', NULL),
(65, '100', 'Aryan', '192.168.0.46', 'Login', '2026-07-15 11:48:35', NULL, 'User logged in', NULL),
(66, 'EMP001', 'Admin User', '192.168.0.40', 'Login', '2026-07-15 12:36:05', NULL, 'User logged in', NULL),
(67, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-16 12:01:10', NULL, 'User logged in', NULL),
(68, '100', 'Aryan', '192.168.0.43', 'Login', '2026-07-22 12:45:28', NULL, 'User logged in', NULL),
(69, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-24 11:50:27', NULL, 'User logged in', NULL),
(70, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-24 11:50:44', NULL, 'User logged in', NULL),
(71, '2', 'Aryan', '192.168.0.40', 'Logout', NULL, '2026-07-24 12:31:27', 'User logged out', NULL),
(72, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-24 12:31:37', NULL, 'User logged in', NULL),
(73, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-24 12:41:34', NULL, 'User logged in', NULL),
(74, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-24 14:14:24', NULL, 'User logged in', NULL),
(75, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-24 14:17:26', NULL, 'User logged in', NULL),
(76, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-24 14:18:38', NULL, 'User logged in', NULL),
(77, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-24 14:19:11', NULL, 'User logged in', NULL),
(78, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 11:25:45', NULL, 'User logged in', NULL),
(79, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 12:13:30', NULL, 'User logged in', NULL),
(80, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 12:18:05', NULL, 'User logged in', NULL),
(81, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 12:21:32', NULL, 'User logged in', NULL),
(82, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 12:26:22', NULL, 'User logged in', NULL),
(83, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 12:27:35', NULL, 'User logged in', NULL),
(84, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 12:31:41', NULL, 'User logged in', NULL),
(85, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 12:32:25', NULL, 'User logged in', NULL),
(86, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 12:37:49', NULL, 'User logged in', NULL),
(87, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 12:38:36', NULL, 'User logged in', NULL),
(88, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 12:55:10', NULL, 'User logged in', NULL),
(89, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 12:56:03', NULL, 'User logged in', NULL),
(90, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 14:55:57', NULL, 'User logged in', NULL),
(91, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 14:57:10', NULL, 'User logged in', NULL),
(92, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 16:52:42', NULL, 'User logged in', NULL),
(93, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-25 17:06:26', NULL, 'User logged in', NULL),
(94, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-25 17:07:12', NULL, 'User logged in', NULL),
(95, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 10:27:11', NULL, 'User logged in', NULL),
(96, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 10:39:10', NULL, 'User logged in', NULL),
(97, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 10:49:36', NULL, 'User logged in', NULL),
(98, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 10:50:08', NULL, 'User logged in', NULL),
(99, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 10:50:59', NULL, 'User logged in', NULL),
(100, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 10:56:17', NULL, 'User logged in', NULL),
(101, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 10:58:05', NULL, 'User logged in', NULL),
(102, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:02:20', NULL, 'User logged in', NULL),
(103, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:03:39', NULL, 'User logged in', NULL),
(104, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:04:08', NULL, 'User logged in', NULL),
(105, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:10:52', NULL, 'User logged in', NULL),
(106, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:20:31', NULL, 'User logged in', NULL),
(107, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:21:36', NULL, 'User logged in', NULL),
(108, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:22:05', NULL, 'User logged in', NULL),
(109, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:25:05', NULL, 'User logged in', NULL),
(110, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:25:36', NULL, 'User logged in', NULL),
(111, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:29:06', NULL, 'User logged in', NULL),
(112, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:29:48', NULL, 'User logged in', NULL),
(113, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:34:05', NULL, 'User logged in', NULL),
(114, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:41:26', NULL, 'User logged in', NULL),
(115, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:43:55', NULL, 'User logged in', NULL),
(116, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:45:53', NULL, 'User logged in', NULL),
(117, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:46:51', NULL, 'User logged in', NULL),
(118, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 11:49:12', NULL, 'User logged in', NULL),
(119, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 11:50:31', NULL, 'User logged in', NULL),
(120, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 12:00:09', NULL, 'User logged in', NULL),
(121, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 12:09:51', NULL, 'User logged in', NULL),
(122, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 12:12:48', NULL, 'User logged in', NULL),
(123, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 12:15:02', NULL, 'User logged in', NULL),
(124, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 12:17:07', NULL, 'User logged in', NULL),
(125, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 12:18:06', NULL, 'User logged in', NULL),
(126, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 12:20:19', NULL, 'User logged in', NULL),
(127, '2', 'Aryan', '192.168.0.37', 'Logout', NULL, '2026-07-27 12:21:10', 'User logged out', NULL),
(128, '100', 'Aryan', '192.168.0.37', 'Login', '2026-07-27 12:21:28', NULL, 'User logged in', NULL),
(129, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 12:25:07', NULL, 'User logged in', NULL),
(130, '2', 'Aryan', '192.168.0.40', 'Logout', NULL, '2026-07-27 12:29:52', 'User logged out', NULL),
(131, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 13:30:51', NULL, 'User logged in', NULL),
(132, '2', 'Aryan', '192.168.0.40', 'Logout', NULL, '2026-07-27 13:34:41', 'User logged out', NULL),
(133, 'Emp04', 'ram', '192.168.0.40', 'Login', '2026-07-27 13:34:53', NULL, 'User logged in', NULL),
(134, '100', 'Aryan', '192.168.0.40', 'Login', '2026-07-27 13:38:49', NULL, 'User logged in', NULL),
(135, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 10:41:30', NULL, 'User logged in', NULL),
(136, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 10:42:31', 'User logged out', NULL),
(137, 'Emp04', 'ram', '192.168.0.41', 'Login', '2026-07-28 10:42:57', NULL, 'User logged in', NULL),
(138, '100', 'Aryan', '192.168.0.44', 'Login', '2026-07-28 10:48:56', NULL, 'User logged in', NULL),
(139, '100', 'Aryan', '192.168.0.44', 'Login', '2026-07-28 10:49:21', NULL, 'User logged in', NULL),
(140, '9', 'gosavi', '192.168.0.39', 'Login', '2026-07-28 10:51:21', NULL, 'User logged in', NULL),
(141, '6', 'ram', '192.168.0.41', 'Logout', NULL, '2026-07-28 10:51:28', 'User logged out', NULL),
(142, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 10:52:00', NULL, 'User logged in', NULL),
(143, '104', 'soham', '192.168.0.40', 'Login', '2026-07-28 10:52:23', NULL, 'User logged in', NULL),
(144, '100', 'Aryan', '192.168.0.44', 'Login', '2026-07-28 10:54:13', NULL, 'User logged in', NULL),
(145, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 10:55:30', NULL, 'User logged in', NULL),
(146, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 10:58:02', 'User logged out', NULL),
(147, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 10:58:06', NULL, 'User logged in', NULL),
(148, '9', 'gosavi', '192.168.0.39', 'Login', '2026-07-28 10:58:58', NULL, 'User logged in', NULL),
(149, '3', 'soham', '192.168.0.40', 'Logout', NULL, '2026-07-28 10:59:26', 'User logged out', NULL),
(150, 'EMP001', 'Admin User', '192.168.0.40', 'Login', '2026-07-28 10:59:46', NULL, 'User logged in', NULL),
(151, '1', 'Admin User', '192.168.0.40', 'Logout', NULL, '2026-07-28 11:11:23', 'User logged out', NULL),
(152, '1462', 'Soham Tarate', '192.168.0.40', 'Login', '2026-07-28 11:11:35', NULL, 'User logged in', NULL),
(153, '7', 'Soham Tarate', '192.168.0.40', 'Logout', NULL, '2026-07-28 11:12:16', 'User logged out', NULL),
(154, '1462', 'Soham Tarate', '192.168.0.39', 'Login', '2026-07-28 11:12:28', NULL, 'User logged in', NULL),
(155, 'EMP001', 'Admin User', '192.168.0.40', 'Login', '2026-07-28 11:12:39', NULL, 'User logged in', NULL),
(156, '100', 'Aryan', '192.168.0.54', 'Login', '2026-07-28 11:20:56', NULL, 'User logged in', NULL),
(157, 'Emp04', 'ram', '192.168.0.37', 'Login', '2026-07-28 11:21:18', NULL, 'User logged in', NULL),
(158, '6', 'ram', '192.168.0.37', 'Logout', NULL, '2026-07-28 11:22:44', 'User logged out', NULL),
(159, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 11:23:08', NULL, 'User logged in', NULL),
(160, '100', 'Aryan', '192.168.0.44', 'Login', '2026-07-28 11:24:30', NULL, 'User logged in', NULL),
(161, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 11:24:57', NULL, 'User logged in', NULL),
(162, 'Emp04', 'ram', '192.168.0.57', 'Login', '2026-07-28 11:27:41', NULL, 'User logged in', NULL),
(163, '100', 'Aryan', '192.168.0.58', 'Login', '2026-07-28 11:29:12', NULL, 'User logged in', NULL),
(164, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 11:29:29', NULL, 'User logged in', NULL),
(165, '100', 'Aryan', '192.168.0.58', 'Login', '2026-07-28 11:29:54', NULL, 'User logged in', NULL),
(166, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 11:30:51', NULL, 'User logged in', NULL),
(167, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 11:31:05', NULL, 'User logged in', NULL),
(168, 'Emp04', 'ram', '192.168.0.57', 'Login', '2026-07-28 11:31:44', NULL, 'User logged in', NULL),
(169, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 11:34:38', 'User logged out', NULL),
(170, '103', 'exadmin', '192.168.0.41', 'Login', '2026-07-28 11:34:43', NULL, 'User logged in', NULL),
(171, '9', 'exadmin', '192.168.0.41', 'Logout', NULL, '2026-07-28 11:37:44', 'User logged out', NULL),
(172, '103', 'exadmin', '192.168.0.41', 'Login', '2026-07-28 11:37:48', NULL, 'User logged in', NULL),
(173, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 11:41:25', NULL, 'User logged in', NULL),
(174, 'Emp04', 'ram', '192.168.0.57', 'Login', '2026-07-28 11:43:38', NULL, 'User logged in', NULL),
(175, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 11:43:50', NULL, 'User logged in', NULL),
(176, '9', 'exadmin', '192.168.0.41', 'Logout', NULL, '2026-07-28 11:59:59', 'User logged out', NULL),
(177, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 12:00:02', NULL, 'User logged in', NULL),
(178, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 12:02:46', 'User logged out', NULL),
(179, '103', 'exadmin', '192.168.0.41', 'Login', '2026-07-28 12:02:50', NULL, 'User logged in', NULL),
(180, '100', 'Aryan', '192.168.0.54', 'Login', '2026-07-28 12:05:24', NULL, 'User logged in', NULL),
(181, '100', 'Aryan', '192.168.0.54', 'Login', '2026-07-28 12:05:24', NULL, 'User logged in', NULL),
(182, '12', 'exadmin', '192.168.0.41', 'Logout', NULL, '2026-07-28 12:06:38', 'User logged out', NULL),
(183, '103', 'exadmin', '192.168.0.41', 'Login', '2026-07-28 12:06:44', NULL, 'User logged in', NULL),
(184, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 12:09:28', NULL, 'User logged in', NULL),
(185, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:20:59', NULL, 'User logged in', NULL),
(186, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:27:18', NULL, 'User logged in', NULL),
(187, '9', 'gosavi', '192.168.0.39', 'Login', '2026-07-28 12:30:01', NULL, 'User logged in', NULL),
(188, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:30:57', NULL, 'User logged in', NULL),
(189, '100', 'Aryan', '192.168.0.58', 'Login', '2026-07-28 12:31:34', NULL, 'User logged in', NULL),
(190, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:36:36', NULL, 'User logged in', NULL),
(191, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:39:50', NULL, 'User logged in', NULL),
(192, '1', 'Admin User', '192.168.0.40', 'Logout', NULL, '2026-07-28 12:40:04', 'User logged out', NULL),
(193, '9', 'gosavi', '192.168.0.40', 'Login', '2026-07-28 12:40:22', NULL, 'User logged in', NULL),
(194, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 12:40:33', NULL, 'User logged in', NULL),
(195, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 12:41:22', 'User logged out', NULL),
(196, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 12:41:25', NULL, 'User logged in', NULL),
(197, '5', 'gosavi', '192.168.0.40', 'Logout', NULL, '2026-07-28 12:42:26', 'User logged out', NULL),
(198, '1462', 'Soham Tarate', '192.168.0.40', 'Login', '2026-07-28 12:42:36', NULL, 'User logged in', NULL),
(199, '18', 'ex', '192.168.0.41', 'Logout', NULL, '2026-07-28 12:42:42', 'User logged out', NULL),
(200, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 12:43:15', NULL, 'User logged in', NULL),
(201, '18', 'ex', '192.168.0.41', 'Logout', NULL, '2026-07-28 12:46:17', 'User logged out', NULL),
(202, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 12:46:20', NULL, 'User logged in', NULL),
(203, 'Emp04', 'ram', '192.168.0.57', 'Login', '2026-07-28 12:47:23', NULL, 'User logged in', NULL),
(204, '7', 'Soham Tarate', '192.168.0.40', 'Logout', NULL, '2026-07-28 12:49:18', 'User logged out', NULL),
(205, '9', 'gosavi', '192.168.0.40', 'Login', '2026-07-28 12:49:25', NULL, 'User logged in', NULL),
(206, '1462', 'Soham Tarate', '192.168.0.40', 'Login', '2026-07-28 12:51:04', NULL, 'User logged in', NULL),
(207, '5', 'Soham Tarate', '192.168.0.40', 'Logout', NULL, '2026-07-28 12:51:22', 'User logged out', NULL),
(208, '9', 'gosavi', '192.168.0.40', 'Login', '2026-07-28 12:51:29', NULL, 'User logged in', NULL),
(209, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:52:05', NULL, 'User logged in', NULL),
(210, 'Emp04', 'ram', '192.168.0.57', 'Login', '2026-07-28 12:52:42', NULL, 'User logged in', NULL),
(211, 'EMP001', 'Admin User', '192.168.0.41', 'Login', '2026-07-28 12:52:47', NULL, 'User logged in', NULL),
(212, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:54:09', NULL, 'User logged in', NULL),
(213, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:55:11', NULL, 'User logged in', NULL),
(214, '18', 'Admin User', '192.168.0.41', 'Logout', NULL, '2026-07-28 12:55:19', 'User logged out', NULL),
(215, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:55:47', NULL, 'User logged in', NULL),
(216, 'Emp04', 'ram', '192.168.0.58', 'Login', '2026-07-28 12:57:45', NULL, 'User logged in', NULL),
(217, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:00:23', NULL, 'User logged in', NULL),
(218, 'Emp04', 'ram', '192.168.0.57', 'Login', '2026-07-28 13:04:08', NULL, 'User logged in', NULL),
(219, '18', 'ex', '192.168.0.41', 'Logout', NULL, '2026-07-28 13:04:50', 'User logged out', NULL),
(220, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:04:53', NULL, 'User logged in', NULL),
(221, '18', 'ex', '192.168.0.41', 'Logout', NULL, '2026-07-28 13:06:25', 'User logged out', NULL),
(222, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:06:29', NULL, 'User logged in', NULL),
(223, '18', 'ex', '192.168.0.41', 'Logout', NULL, '2026-07-28 13:06:45', 'User logged out', NULL),
(224, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:06:48', NULL, 'User logged in', NULL),
(225, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 13:08:44', NULL, 'User logged in', NULL),
(226, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 13:09:07', NULL, 'User logged in', NULL),
(227, '9', 'gosavi', '192.168.0.54', 'Login', '2026-07-28 13:13:40', NULL, 'User logged in', NULL),
(228, '9', 'gosavi', '192.168.0.54', 'Login', '2026-07-28 13:16:11', NULL, 'User logged in', NULL),
(229, '104', 'soham', '192.168.0.40', 'Login', '2026-07-28 13:17:40', NULL, 'User logged in', NULL),
(230, 'EMP001', 'Admin User', '192.168.0.39', 'Login', '2026-07-28 13:17:53', NULL, 'User logged in', NULL),
(231, '18', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 13:20:19', 'User logged out', NULL),
(232, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-28 13:20:35', NULL, 'User logged in', NULL),
(233, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-28 13:20:39', 'User logged out', NULL),
(234, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:20:42', NULL, 'User logged in', NULL),
(235, '21093', 'ex', '192.168.0.54', 'Login', '2026-07-28 13:51:45', NULL, 'User logged in', NULL),
(236, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:52:44', NULL, 'User logged in', NULL),
(237, '21093', 'ex', '192.168.0.54', 'Login', '2026-07-28 13:53:03', NULL, 'User logged in', NULL),
(238, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-28 13:53:44', NULL, 'User logged in', NULL),
(239, '9', 'gosavi', '192.168.0.54', 'Login', '2026-07-28 13:56:53', NULL, 'User logged in', NULL),
(240, '9', 'gosavi', '192.168.0.54', 'Login', '2026-07-28 14:00:39', NULL, 'User logged in', NULL),
(241, '9', 'gosavi', '192.168.0.54', 'Login', '2026-07-28 14:10:32', NULL, 'User logged in', NULL),
(242, '9', 'gosavi', '192.168.0.37', 'Login', '2026-07-29 10:36:05', NULL, 'User logged in', NULL),
(243, '100', 'Aryan', '192.168.0.39', 'Login', '2026-07-29 10:36:33', NULL, 'User logged in', NULL),
(244, '100', 'Aryan', '192.168.0.54', 'Login', '2026-07-29 10:36:38', NULL, 'User logged in', NULL),
(245, 'EMP001', 'Admin User', '192.168.0.39', 'Login', '2026-07-29 10:37:42', NULL, 'User logged in', NULL),
(246, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-29 10:47:55', NULL, 'User logged in', NULL),
(247, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-29 10:48:23', 'User logged out', NULL),
(248, '21093', 'ex', '192.168.0.41', 'Login', '2026-07-29 10:48:29', NULL, 'User logged in', NULL),
(249, '18', 'ex', '192.168.0.41', 'Logout', NULL, '2026-07-29 11:15:41', 'User logged out', NULL),
(250, 'EMP001', 'Admin User', '192.168.0.41', 'Login', '2026-07-29 11:15:51', NULL, 'User logged in', NULL),
(251, '100', 'Aryan', '192.168.0.39', 'Login', '2026-07-29 11:19:39', NULL, 'User logged in', NULL),
(252, '5', 'gosavi', '192.168.0.37', 'Logout', NULL, '2026-07-29 11:30:28', 'User logged out', NULL),
(253, '1462', 'Soham Tarate', '192.168.0.37', 'Login', '2026-07-29 11:30:42', NULL, 'User logged in', NULL),
(254, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-29 11:58:07', NULL, 'User logged in', NULL),
(255, '21093', 'ex', '192.168.0.54', 'Login', '2026-07-29 11:58:59', NULL, 'User logged in', NULL),
(256, '18', 'ex', '192.168.0.54', 'Logout', NULL, '2026-07-29 12:00:22', 'User logged out', NULL),
(257, '100', 'Aryan', '192.168.0.54', 'Login', '2026-07-29 12:00:28', NULL, 'User logged in', NULL),
(258, '1462', 'Soham Tarate', '192.168.0.41', 'Login', '2026-07-29 12:01:26', NULL, 'User logged in', NULL),
(259, '2', 'Aryan', '192.168.0.54', 'Logout', NULL, '2026-07-29 12:02:18', 'User logged out', NULL),
(260, 'Emp04', 'ram', '192.168.0.54', 'Login', '2026-07-29 12:02:50', NULL, 'User logged in', NULL),
(261, 'EMP001', 'Admin User', '192.168.0.41', 'Login', '2026-07-29 12:02:55', NULL, 'User logged in', NULL),
(262, '1462', 'Soham Tarate', '192.168.0.37', 'Login', '2026-07-29 12:06:53', NULL, 'User logged in', NULL),
(263, '1', 'Admin User', '192.168.0.41', 'Logout', NULL, '2026-07-29 12:18:10', 'User logged out', NULL),
(264, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-29 12:18:14', NULL, 'User logged in', NULL),
(265, '100', 'Aryan', '192.168.0.41', 'Login', '2026-07-29 12:18:17', NULL, 'User logged in', NULL),
(266, '2', 'Aryan', '192.168.0.41', 'Logout', NULL, '2026-07-29 12:18:53', 'User logged out', NULL),
(267, 'EMP001', 'Admin User', '192.168.0.41', 'Login', '2026-07-29 12:19:00', NULL, 'User logged in', NULL),
(268, '1', 'Admin User', '192.168.0.41', 'Logout', NULL, '2026-07-29 12:19:04', 'User logged out', NULL),
(269, '9', 'gosavi', '192.168.0.41', 'Login', '2026-07-29 12:19:11', NULL, 'User logged in', NULL),
(270, '5', 'gosavi', '192.168.0.41', 'Logout', NULL, '2026-07-29 12:19:13', 'User logged out', NULL),
(271, '104', 'soham', '192.168.0.41', 'Login', '2026-07-29 12:20:05', NULL, 'User logged in', NULL),
(272, '7', 'Soham Tarate', '192.168.0.37', 'Logout', NULL, '2026-07-29 12:24:13', 'User logged out', NULL),
(273, '9', 'gosavi', '192.168.0.37', 'Login', '2026-07-29 12:24:22', NULL, 'User logged in', NULL),
(274, '5', 'gosavi', '192.168.0.37', 'Logout', NULL, '2026-07-29 12:24:43', 'User logged out', NULL),
(275, '104', 'soham', '192.168.0.37', 'Login', '2026-07-29 12:24:51', NULL, 'User logged in', NULL),
(276, '3', 'soham', '192.168.0.37', 'Logout', NULL, '2026-07-29 12:25:14', 'User logged out', NULL),
(277, '1462', 'Soham Tarate', '192.168.0.37', 'Login', '2026-07-29 12:25:24', NULL, 'User logged in', NULL),
(278, 'Emp04', 'ram', '192.168.0.69', 'Login', '2026-07-29 17:04:02', NULL, 'User logged in', NULL),
(279, 'Emp04', 'ram', '192.168.0.66', 'Login', '2026-07-29 17:04:52', NULL, 'User logged in', NULL),
(280, 'Emp04', 'ram', '192.168.0.69', 'Login', '2026-07-29 17:05:52', NULL, 'User logged in', NULL),
(281, '100', 'Aryan', '192.168.0.66', 'Login', '2026-07-29 17:09:10', NULL, 'User logged in', NULL),
(282, '100', 'Aryan', '192.168.0.66', 'Login', '2026-07-29 17:11:55', NULL, 'User logged in', NULL),
(283, '6', 'ram', '192.168.0.69', 'Logout', NULL, '2026-07-29 17:12:02', 'User logged out', NULL),
(284, '100', 'Aryan', '192.168.0.69', 'Login', '2026-07-29 17:12:15', NULL, 'User logged in', NULL),
(285, '100', 'Aryan', '192.168.0.34', 'Login', '2026-07-31 16:55:42', NULL, 'User logged in', NULL),
(286, '2', 'Aryan', '192.168.0.34', 'Logout', NULL, '2026-07-31 16:56:44', 'User logged out', NULL),
(287, 'Emp04', 'ram', '192.168.0.34', 'Login', '2026-07-31 16:56:56', NULL, 'User logged in', NULL),
(288, '6', 'ram', '192.168.0.34', 'Logout', NULL, '2026-07-31 16:57:18', 'User logged out', NULL),
(289, '100', 'Aryan', '192.168.0.34', 'Login', '2026-07-31 16:57:26', NULL, 'User logged in', NULL),
(290, '2', 'Aryan', '192.168.0.34', 'Logout', NULL, '2026-07-31 16:57:52', 'User logged out', NULL),
(291, 'Emp04', 'ram', '192.168.0.34', 'Login', '2026-07-31 16:58:04', NULL, 'User logged in', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agency`
--
ALTER TABLE `agency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_id` (`email_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bill_type` (`bill_type`),
  ADD KEY `idx_bill_number` (`bill_number`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_bill_date` (`bill_date`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_grand_total` (`grand_total`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_emails_recipient` (`recipient_email`),
  ADD KEY `idx_emails_sender` (`sender_id`);

--
-- Indexes for table `email_acc`
--
ALTER TABLE `email_acc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_email` (`email`);

--
-- Indexes for table `email_drafts`
--
ALTER TABLE `email_drafts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_folders`
--
ALTER TABLE `email_folders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_invoice_date` (`invoice_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `leave_management`
--
ALTER TABLE `leave_management`
  ADD PRIMARY KEY (`leave_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_lock_until` (`lock_until`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `meeting_attendees`
--
ALTER TABLE `meeting_attendees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`meeting_id`,`user_id`),
  ADD KEY `idx_meeting_user` (`meeting_id`,`user_id`),
  ADD KEY `idx_attendance_status` (`attendance_status`),
  ADD KEY `fk_attendees_user` (`user_id`);

--
-- Indexes for table `meeting_notifications`
--
ALTER TABLE `meeting_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notification` (`meeting_id`,`user_id`,`notification_type`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_meeting` (`meeting_id`);

--
-- Indexes for table `payment_photos`
--
ALTER TABLE `payment_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email_id` (`email_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_email_id` (`email_id`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`activity_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agency`
--
ALTER TABLE `agency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `emails`
--
ALTER TABLE `emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_acc`
--
ALTER TABLE `email_acc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_drafts`
--
ALTER TABLE `email_drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_folders`
--
ALTER TABLE `email_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leave_management`
--
ALTER TABLE `leave_management`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meeting_attendees`
--
ALTER TABLE `meeting_attendees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meeting_notifications`
--
ALTER TABLE `meeting_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_photos`
--
ALTER TABLE `payment_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

-- --------------------------------------------------------

--
-- Structure for view `unified_bills`
--
DROP TABLE IF EXISTS `unified_bills`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `unified_bills`  AS SELECT `invoices`.`id` AS `id`, 'invoice' AS `bill_type`, `invoices`.`invoice_number` AS `bill_number`, `invoices`.`invoice_number` AS `bill_number_display`, `invoices`.`quote_number` AS `quote_number`, `invoices`.`customer_id` AS `customer_id`, `invoices`.`invoice_date` AS `bill_date`, `invoices`.`due_date` AS `due_date`, `invoices`.`status` AS `status`, `invoices`.`currency` AS `currency`, `invoices`.`company_name` AS `company_name`, `invoices`.`company_address` AS `company_address`, `invoices`.`company_contact` AS `company_contact`, `invoices`.`company_email` AS `company_email`, `invoices`.`supplier_gstin` AS `company_gst`, `invoices`.`customer_name` AS `customer_name`, `invoices`.`customer_contact_person` AS `customer_contact_person`, `invoices`.`customer_address` AS `customer_address`, `invoices`.`buyer_gstin` AS `customer_gst`, NULL AS `customer_email`, NULL AS `customer_phone`, `invoices`.`place_of_supply` AS `place_of_supply`, `invoices`.`hsn_sac_code` AS `hsn_sac_code`, `invoices`.`gst_mode` AS `gst_mode`, NULL AS `gst_rate`, `invoices`.`cgst_rate` AS `cgst_rate`, `invoices`.`sgst_rate` AS `sgst_rate`, `invoices`.`igst_rate` AS `igst_rate`, `invoices`.`subtotal` AS `subtotal`, `invoices`.`discount` AS `discount`, `invoices`.`taxable_value` AS `taxable_value`, `invoices`.`cgst_amount` AS `cgst_amount`, `invoices`.`sgst_amount` AS `sgst_amount`, `invoices`.`igst_amount` AS `igst_amount`, NULL AS `gst_amount`, `invoices`.`other_charges` AS `other_charges`, `invoices`.`grand_total` AS `grand_total`, `invoices`.`amount_in_words` AS `amount_in_words`, `invoices`.`bank_account_name` AS `bank_account_name`, `invoices`.`bank_account_number` AS `bank_account_number`, `invoices`.`bank_ifsc_code` AS `bank_ifsc_code`, `invoices`.`bank_name` AS `bank_name`, `invoices`.`bank_branch` AS `bank_branch`, `invoices`.`terms_conditions` AS `terms_conditions`, `invoices`.`notes` AS `notes`, `invoices`.`client_name_footer` AS `client_name_footer`, `invoices`.`items_json` AS `items_json`, `invoices`.`created_at` AS `created_at`, `invoices`.`updated_at` AS `updated_at` FROM `invoices`union all select `quotations`.`id` AS `id`,'quotation' AS `bill_type`,`quotations`.`quotation_number` AS `bill_number`,`quotations`.`quotation_number` AS `bill_number_display`,`quotations`.`quote_number` AS `quote_number`,`quotations`.`customer_id` AS `customer_id`,`quotations`.`quotation_date` AS `bill_date`,`quotations`.`valid_until` AS `due_date`,`quotations`.`status` AS `status`,`quotations`.`currency` AS `currency`,`quotations`.`company_name` AS `company_name`,`quotations`.`company_address` AS `company_address`,`quotations`.`company_contact` AS `company_contact`,`quotations`.`company_email` AS `company_email`,`quotations`.`company_gst` AS `company_gst`,`quotations`.`customer_company` AS `customer_name`,`quotations`.`customer_contact` AS `customer_contact_person`,`quotations`.`customer_address` AS `customer_address`,`quotations`.`customer_gst` AS `customer_gst`,`quotations`.`customer_email` AS `customer_email`,`quotations`.`customer_phone` AS `customer_phone`,NULL AS `place_of_supply`,NULL AS `hsn_sac_code`,`quotations`.`gst_mode` AS `gst_mode`,`quotations`.`gst_rate` AS `gst_rate`,`quotations`.`cgst_rate` AS `cgst_rate`,`quotations`.`sgst_rate` AS `sgst_rate`,`quotations`.`igst_rate` AS `igst_rate`,`quotations`.`subtotal` AS `subtotal`,`quotations`.`discount` AS `discount`,`quotations`.`taxable_value` AS `taxable_value`,`quotations`.`cgst_amount` AS `cgst_amount`,`quotations`.`sgst_amount` AS `sgst_amount`,`quotations`.`igst_amount` AS `igst_amount`,`quotations`.`gst_amount` AS `gst_amount`,`quotations`.`other_charges` AS `other_charges`,`quotations`.`grand_total` AS `grand_total`,`quotations`.`amount_in_words` AS `amount_in_words`,`quotations`.`bank_account_name` AS `bank_account_name`,`quotations`.`bank_account_number` AS `bank_account_number`,`quotations`.`bank_ifsc` AS `bank_ifsc_code`,`quotations`.`bank_name` AS `bank_name`,`quotations`.`bank_branch` AS `bank_branch`,`quotations`.`terms_conditions` AS `terms_conditions`,`quotations`.`notes` AS `notes`,`quotations`.`client_name_footer` AS `client_name_footer`,`quotations`.`items_json` AS `items_json`,`quotations`.`created_at` AS `created_at`,`quotations`.`updated_at` AS `updated_at` from `quotations`  ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meeting_attendees`
--
ALTER TABLE `meeting_attendees`
  ADD CONSTRAINT `fk_attendees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_photos`
--
ALTER TABLE `payment_photos`
  ADD CONSTRAINT `fk_payment_photos_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
