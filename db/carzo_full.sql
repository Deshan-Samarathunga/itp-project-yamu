-- Consolidated full database file
-- Sources: carzo.sql, phase2_user_roles_profile.sql, phase3_vehicle_booking_management.sql,
-- phase4_reviews_payments_promotions.sql, phase5_staff_role.sql, phase6_driver_ads.sql,
-- phase6_user_profile_role_management.sql

-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 11, 2023 at 12:36 PM
-- Server version: 5.7.36
-- PHP Version: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yamu`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` longtext,
  `city` varchar(50) DEFAULT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `name`, `email`, `address`, `city`, `phone`, `profile_pic`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'admin@email.com', '9091 Hillcrest Rd', 'colombo', '0769643114', 'avatar.png'),
(2, 'admin2', '0e7517141fb53f21ee439b355b5a1d0a', 'Second Admin', 'admin2@email.com', '22 Main Street', 'colombo', '0771234567', 'avatar.png');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

DROP TABLE IF EXISTS `booking`;
CREATE TABLE IF NOT EXISTS `booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_No` varchar(50) DEFAULT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `vehicle_ID` int(11) DEFAULT NULL,
  `start_Data` varchar(20) DEFAULT NULL,
  `end_Date` varchar(20) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `booking_status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `promotion_id` int(11) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `final_amount` decimal(10,2) DEFAULT NULL,
  `booking_Date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `update_Date` datetime DEFAULT NULL,
  PRIMARY KEY (`booking_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `booking_No`, `user_ID`, `driver_id`, `vehicle_ID`, `start_Data`, `end_Date`, `total`, `status`, `booking_status`, `payment_status`, `promotion_id`, `promo_code`, `discount_amount`, `final_amount`, `booking_Date`, `created_at`, `updated_at`, `cancelled_at`, `completed_at`, `update_Date`) VALUES
(1, 'BOOK88523', 3, 4, 3, '2023-06-06', '2023-06-08', 13000, 1, 'confirmed', 'pending', NULL, NULL, 0.00, 13000.00, '2023-06-06 23:13:43', '2023-06-06 23:13:43', '2023-06-06 23:13:43', NULL, NULL, NULL),
(3, 'BOOK44665', 3, 4, 4, '2023-06-07', '2023-06-08', 9500, 0, 'pending', 'pending', NULL, NULL, 0.00, 9500.00, '2023-06-07 23:17:44', '2023-06-07 23:17:44', '2023-06-07 23:17:44', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(50) DEFAULT NULL,
  `brand_logo` varchar(255) DEFAULT NULL,
  `creation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `brand_status` int(11) DEFAULT '1',
  PRIMARY KEY (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_logo`, `creation_date`, `update_date`, `brand_status`) VALUES
(1, 'Audi', '647329cb34cbd_audi.png', '2023-05-28 15:45:39', '2023-06-11 17:42:47', 1),
(2, 'BMW', '647329e14dfad_BMW .png', '2023-05-28 15:46:01', NULL, 1),
(3, 'Ford', '647329efeac6d_ford.png', '2023-05-28 15:46:15', NULL, 1),
(4, 'KIA', '647329f98bbdf_kia.png', '2023-05-28 15:46:25', NULL, 1),
(5, 'Mitsubishi', '64732a039b911_Mitsubishi.png', '2023-05-28 15:46:35', NULL, 1),
(6, 'Nissan', '64732a0fcfd6d_nissan.png', '2023-05-28 15:46:47', NULL, 1),
(7, 'Tesla', '64732a1ede9bd_tesla.png', '2023-05-28 15:47:02', NULL, 1),
(8, 'Toyota ', '64732a27dac8c_Toyota .png', '2023-05-28 15:47:11', NULL, 1),
(9, 'Volkswagen ', '64732a3198d4c_Volkswagen .png', '2023-05-28 15:47:21', NULL, 1),
(10, 'Benz ', '64745b66df716_mercedes-logo-15875.png', '2023-05-29 13:29:34', NULL, 1),
(11, 'Peugeot', '64745e3278184_pngwing.com (2).png', '2023-05-29 13:41:30', NULL, 1),
(12, 'Suzuki ', '64745f5ff2243_suzuki-logo-car-brands-6687.png', '2023-05-29 13:46:31', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `role` enum('admin','staff','driver','customer') NOT NULL DEFAULT 'customer',
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` longtext,
  `city` varchar(50) DEFAULT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `license_or_nic` varchar(100) DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'verified',
  `bio` longtext,
  `profile_pic` varchar(255) DEFAULT NULL,
  `account_status` varchar(20) NOT NULL DEFAULT 'active',
  `rag_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `full_name`, `email`, `address`, `city`, `phone`, `dob`, `license_or_nic`, `verification_status`, `bio`, `profile_pic`, `account_status`, `rag_date`, `created_at`, `updated_at`) VALUES
(3, 'hweranmadhuka@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'customer', 'Eran Madhuka', 'hweranmadhuka@gmail.com', 'No:94, willorawatta, moratuwa.', 'Moratuwa', '0785862007', '', NULL, 'verified', NULL, '6485bb59d310d_Untitled-1_0005_Layer-3-545x389.jpg', 'active', '2023-06-11 17:08:27', '2023-06-11 17:08:27', '2023-06-11 17:08:27'),
(4, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'admin', 'admin@email.com', '9091 Hillcrest Rd', 'colombo', '0769643114', '', NULL, 'verified', NULL, 'avatar.png', 'active', '2023-06-11 12:36:00', '2023-06-11 12:36:00', '2023-06-11 12:36:00'),
(5, 'admin2', '0e7517141fb53f21ee439b355b5a1d0a', 'admin', 'Second Admin', 'admin2@email.com', '22 Main Street', 'colombo', '0771234567', '', NULL, 'verified', NULL, 'avatar.png', 'active', '2023-06-11 12:40:00', '2023-06-11 12:40:00', '2023-06-11 12:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_user_id` int(11) DEFAULT NULL,
  `vehicle_title` varchar(50) DEFAULT NULL,
  `vehicle_brand` varchar(50) DEFAULT NULL,
  `vehicle_desc` longtext,
  `price` float DEFAULT NULL,
  `transmission` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `engine_capacity` varchar(50) DEFAULT NULL,
  `capacity` int(3) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `vImg1` varchar(255) DEFAULT NULL,
  `vImg2` varchar(255) DEFAULT NULL,
  `vImg3` varchar(255) DEFAULT NULL,
  `vImg4` varchar(255) DEFAULT NULL,
  `airConditioner` int(1) DEFAULT NULL,
  `powerdoorlocks` int(1) DEFAULT NULL,
  `antilockbrakingsys` int(1) DEFAULT NULL,
  `brakeassist` int(1) DEFAULT NULL,
  `powersteering` int(1) DEFAULT NULL,
  `driverairbag` int(1) DEFAULT NULL,
  `passengerairbag` int(1) DEFAULT NULL,
  `powerwindow` int(1) DEFAULT NULL,
  `cdplayer` int(1) DEFAULT NULL,
  `reg_date` datetime DEFAULT NULL,
  `updatation_date` datetime DEFAULT NULL,
  `vehicle_status` int(1) DEFAULT '1',
  `listing_status` varchar(20) NOT NULL DEFAULT 'approved',
  `availability_status` varchar(20) NOT NULL DEFAULT 'available',
  `maintenance_status` varchar(30) NOT NULL DEFAULT 'good',
  `service_date` date DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `service_notes` longtext,
  `service_cost` decimal(10,2) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `owner_user_id`, `vehicle_title`, `vehicle_brand`, `vehicle_desc`, `price`, `transmission`, `fuel_type`, `year`, `engine_capacity`, `capacity`, `location`, `registration_number`, `vImg1`, `vImg2`, `vImg3`, `vImg4`, `airConditioner`, `powerdoorlocks`, `antilockbrakingsys`, `brakeassist`, `powersteering`, `driverairbag`, `passengerairbag`, `powerwindow`, `cdplayer`, `reg_date`, `updatation_date`, `vehicle_status`, `listing_status`, `availability_status`, `maintenance_status`, `service_date`, `next_service_date`, `service_notes`, `service_cost`, `approved_by`, `approved_at`, `updated_at`) VALUES
(1, 4, 'Maruti Suzuki Wagon R', 'Suzuki ', 'Maruti Wagon R Latest Updates Maruti Suzuki has launched the BS6 Wagon R S-CNG in India. The LXI CNG and LXI (O) CNG variants now cost Rs 5.25 lakh and Rs 5.32 lakh respectively, up by Rs 19,000. Maruti claims a fuel economy of 32.52km per kg. The CNG Wagon RÃ¢â‚¬â„¢s continuation in the BS6 era is part of the carmakerÃ¢â‚¬â„¢s Ã¢â‚¬ËœMission Green MillionÃ¢â‚¬â„¢ initiative announced at Auto Expo 2020. Previously, the carmaker had updated the 1.0-litre powertrain to meet BS6 emission norms. It develops 68PS of power and 90Nm of torque, same as the BS4 unit. However, the updated motor now returns 21.79 kmpl, which is a little less than the BS4 unitÃ¢â‚¬â„¢s 22.5kmpl claimed figure. Barring the CNG variants, the prices of the Wagon R 1.0-litre have been hiked by Rs 8,000.', 4500, 'Automatic', 'Petrol', 2019, '1000', 5, 'Colombo', 'CAR-1001', '647461220a984_rear-3-4-left-589823254_930x620.jpg', '647461220acf0_steering-close-up-1288209207_930x620.jpg', '647461220ae1c_tail-lamp-1666712219_930x620.jpg', '647461220af82_rear-3-4-right-520328200_930x620.jpg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 13:54:02', NULL, 1, 'approved', 'available', 'good', NULL, NULL, NULL, NULL, 4, '2023-05-29 13:54:02', '2023-05-29 13:54:02'),
(2, 4, 'Mercedes AMG', 'Benz ', 'Edipisicing eiusmod tempor incididunt labore sed dolore magna aliqa enim ipsum ad minim veniams quis nostrud citation ullam laboris nisi ut aliquip laboris nisi ut aliquip ex ea commod. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 8500, 'Automatic', 'Petrol', 2015, '3900', 5, 'Colombo', 'CAR-1002', '647466ed2a721_bmw_x6_m_competition_2020_5k-1280x720-1-750x430.jpg', '647466ed2a8ac_gallery06-1024x728-min-480x360.jpg', '647466ed2a96b_gallery05-1024x728-min-480x360.jpg', '647466ed2aa11_gallery07-1024x728-min-480x360.jpg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 14:18:45', NULL, 1, 'approved', 'available', 'good', NULL, NULL, NULL, NULL, 4, '2023-05-29 14:18:45', '2023-05-29 14:18:45'),
(3, 4, 'Audi Q8', 'Audi', 'As per ARAI, the mileage of Q8 is 0 kmpl. Real mileage of the vehicle varies depending upon the driving habits. City and highway mileage figures also vary depending upon the road conditions.', 6500, 'Automatic', 'Petrol', 2017, '3000', 5, 'Negombo', 'CAR-1003', '647467ceb36f9_1audiq8.jpg', '647467ceb3a95_1920x1080_MTC_XL_framed_Audi-Odessa-Armaturen_Spiegelung_CC_v05.jpg', '647467ceb3b69_audi1.jpg', '647467ceb3c05_audi-q8-front-view4.jpeg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 14:22:30', NULL, 1, 'approved', 'booked', 'good', NULL, NULL, NULL, NULL, 4, '2023-05-29 14:22:30', '2023-05-29 14:22:30'),
(4, 4, 'Toyota Fortuner', 'Toyota ', 'Toyota Fortuner Features: It is a premium seven-seater SUV loaded with features such as LED projector headlamps with LED DRLs, LED fog lamp, and power-adjustable and foldable ORVMs. Inside, the Fortuner offers features such as power-adjustable driver seat, automatic climate control, push-button stop/start, and cruise control. Toyota Fortuner Safety Features: The Toyota Fortuner gets seven airbags, hill assist control, vehicle stability control with brake assist, and ABS with EBD.', 9500, 'Automatic', 'Diesel', 2020, '3500', 8, 'Colombo', 'CAR-1004', '6474689c404e9_zw-toyota-fortuner-2020-2.jpg', '6474689c40622_toyota-fortuner-legender-rear-quarters-6e57.jpg', '6474689c40711_marutisuzuki-vitara-brezza-dashboard10.jpg', '6474689c407c8_2015_Toyota_Fortuner_(New_Zealand).jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 14:25:56', NULL, 1, 'approved', 'booked', 'good', NULL, NULL, NULL, NULL, 4, '2023-05-29 14:25:56', '2023-05-29 14:25:56'),
(5, 4, 'Nissan Kicks', 'Nissan', 'Latest Update: Nissan has launched the Kicks 2020 with a new turbocharged petrol engine. You can read more about it here. Nissan Kicks Price and Variants: The Kicks is available in four variants: XL, XV, XV Premium, and XV Premium(O).', 6500, 'Automatic', 'Diesel', 2020, '2500', 8, 'Kandy', 'CAR-1005', '647f18794bf11_front-left-side-47.jpg', '647f18794c12b_kicksmodelimage.jpg', '647f18794c2b0_marutisuzuki-vitara-brezza-dashboard10.jpg', '647f18794c39f_Nissan-Sunny-Interior-114977.jpg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-06-06 16:58:57', NULL, 1, 'approved', 'available', 'good', NULL, NULL, NULL, NULL, 4, '2023-06-06 16:58:57', '2023-06-06 16:58:57');
-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` longtext,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `complaint_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `complainant_user_id` int(11) NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `target_vehicle_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` longtext,
  `attachment` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `driver_response` longtext,
  `admin_notes` longtext,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`complaint_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `promotion_id` int(11) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `final_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
CREATE TABLE IF NOT EXISTS `promotions` (
  `promotion_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` longtext,
  `discount_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT '0',
  `minimum_booking_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `applicable_vehicle_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`promotion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `driver_ads`
--

DROP TABLE IF EXISTS `driver_ads`;
CREATE TABLE IF NOT EXISTS `driver_ads` (
  `driver_ad_id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_user_id` int(11) NOT NULL,
  `ad_title` varchar(150) DEFAULT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `service_location` varchar(150) DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `experience_years` int(11) NOT NULL DEFAULT '0',
  `daily_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_group_size` int(11) NOT NULL DEFAULT '1',
  `availability_status` varchar(20) NOT NULL DEFAULT 'available',
  `advertisement_status` varchar(20) NOT NULL DEFAULT 'draft',
  `specialties` longtext,
  `description` longtext,
  `contact_preference` varchar(20) NOT NULL DEFAULT 'both',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`driver_ad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
-- Integrated from phase6_user_profile_role_management.sql
-- --------------------------------------------------------

ALTER TABLE `users`
  MODIFY COLUMN `password` VARCHAR(255) DEFAULT NULL,
  MODIFY COLUMN `role` ENUM('admin','staff','driver','customer') NOT NULL DEFAULT 'customer',
  MODIFY COLUMN `account_status` VARCHAR(20) NOT NULL DEFAULT 'active',
  MODIFY COLUMN `verification_status` VARCHAR(20) NOT NULL DEFAULT 'verified',
  MODIFY COLUMN `phone` VARCHAR(20) DEFAULT NULL,
  MODIFY COLUMN `profile_pic` VARCHAR(255) DEFAULT 'avatar.png',
  ADD COLUMN `last_login_at` DATETIME DEFAULT NULL AFTER `updated_at`;

UPDATE `users`
SET
  `account_status` = CASE
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('active', 'verified') THEN 'active'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('pending') THEN 'pending'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('suspended') THEN 'suspended'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('rejected') THEN 'rejected'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('deactivated') THEN 'deactivated'
    ELSE 'active'
  END,
  `verification_status` = CASE
    WHEN LOWER(COALESCE(`verification_status`, '')) IN ('unverified', 'pending', 'approved', 'rejected', 'verified') THEN LOWER(`verification_status`)
    ELSE CASE
      WHEN `role` IN ('driver', 'staff') THEN 'pending'
      ELSE 'verified'
    END
  END,
  `created_at` = COALESCE(`created_at`, `rag_date`, NOW()),
  `updated_at` = COALESCE(`updated_at`, `rag_date`, NOW());

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_key` VARCHAR(20) NOT NULL,
  `role_name` VARCHAR(80) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uk_roles_key` (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `roles` (`role_key`, `role_name`, `description`, `is_system`)
VALUES
  ('customer', 'Customer', 'Person who books vehicles and/or drivers', 1),
  ('driver', 'Driver', 'Driver who provides driving service', 1),
  ('staff', 'Staff', 'Rental provider or store operator', 1),
  ('admin', 'Admin', 'System administrator with full platform control', 1);

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `role_key` VARCHAR(20) NOT NULL,
  `role_status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `verification_status` VARCHAR(20) NOT NULL DEFAULT 'verified',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `assigned_by_user_id` INT(11) DEFAULT NULL,
  `verified_by_user_id` INT(11) DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `notes` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_role_id`),
  UNIQUE KEY `uk_user_roles_user_role` (`user_id`, `role_key`),
  KEY `idx_user_roles_role_key` (`role_key`),
  KEY `idx_user_roles_role_status` (`role_status`),
  KEY `idx_user_roles_verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_roles` (`user_id`, `role_key`, `role_status`, `verification_status`, `is_primary`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  u.`role`,
  CASE
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('active', 'verified') THEN 'active'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('pending') THEN 'pending'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('suspended') THEN 'suspended'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('rejected') THEN 'rejected'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('deactivated') THEN 'deactivated'
    ELSE 'active'
  END AS role_status,
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('unverified', 'pending', 'approved', 'rejected', 'verified')
      THEN LOWER(u.`verification_status`)
    WHEN u.`role` IN ('driver', 'staff')
      THEN 'pending'
    ELSE 'verified'
  END AS verification_status,
  1 AS is_primary,
  COALESCE(u.`created_at`, u.`rag_date`, NOW()) AS created_at,
  COALESCE(u.`updated_at`, u.`rag_date`, NOW()) AS updated_at
FROM `users` u
LEFT JOIN `user_roles` ur
  ON ur.`user_id` = u.`user_id`
 AND ur.`role_key` = u.`role`
WHERE ur.`user_role_id` IS NULL;

CREATE TABLE IF NOT EXISTS `customer_profiles` (
  `customer_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `preferences` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_profile_id`),
  UNIQUE KEY `uk_customer_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `driver_profiles` (
  `driver_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `driving_license_number` VARCHAR(100) DEFAULT NULL,
  `license_expiry_date` DATE DEFAULT NULL,
  `nic_id` VARCHAR(100) DEFAULT NULL,
  `service_area` VARCHAR(255) DEFAULT NULL,
  `verification_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`driver_profile_id`),
  UNIQUE KEY `uk_driver_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `staff_profiles` (
  `staff_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `store_name` VARCHAR(255) DEFAULT NULL,
  `store_owner` VARCHAR(255) DEFAULT NULL,
  `business_registration_number` VARCHAR(100) DEFAULT NULL,
  `store_address` LONGTEXT DEFAULT NULL,
  `store_contact_number` VARCHAR(30) DEFAULT NULL,
  `store_email` VARCHAR(255) DEFAULT NULL,
  `verification_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_profile_id`),
  UNIQUE KEY `uk_staff_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_profiles` (
  `admin_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `system_permissions` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_profile_id`),
  UNIQUE KEY `uk_admin_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customer_profiles` (`user_id`, `created_at`, `updated_at`)
SELECT u.`user_id`, COALESCE(u.`created_at`, NOW()), COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'customer'
LEFT JOIN `customer_profiles` cp ON cp.`user_id` = u.`user_id`
WHERE cp.`customer_profile_id` IS NULL;

INSERT INTO `driver_profiles` (`user_id`, `driving_license_number`, `nic_id`, `verification_status`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  NULLIF(u.`license_or_nic`, ''),
  NULLIF(u.`license_or_nic`, ''),
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('approved', 'verified') THEN 'verified'
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('rejected') THEN 'rejected'
    ELSE 'pending'
  END,
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'driver'
LEFT JOIN `driver_profiles` dp ON dp.`user_id` = u.`user_id`
WHERE dp.`driver_profile_id` IS NULL;

INSERT INTO `staff_profiles` (`user_id`, `store_name`, `store_owner`, `store_address`, `store_contact_number`, `store_email`, `verification_status`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  NULL,
  u.`full_name`,
  u.`address`,
  u.`phone`,
  u.`email`,
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('approved', 'verified') THEN 'verified'
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('rejected') THEN 'rejected'
    ELSE 'pending'
  END,
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'staff'
LEFT JOIN `staff_profiles` sp ON sp.`user_id` = u.`user_id`
WHERE sp.`staff_profile_id` IS NULL;

INSERT INTO `admin_profiles` (`user_id`, `system_permissions`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  'all',
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'admin'
LEFT JOIN `admin_profiles` ap ON ap.`user_id` = u.`user_id`
WHERE ap.`admin_profile_id` IS NULL;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `password_reset_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`password_reset_id`),
  UNIQUE KEY `uk_password_resets_token_hash` (`token_hash`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
