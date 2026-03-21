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
-- Database: `carzo`
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
  `vehicle_ID` int(11) DEFAULT NULL,
  `start_Data` varchar(20) DEFAULT NULL,
  `end_Date` varchar(20) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `booking_Date` datetime DEFAULT NULL,
  `update_Date` datetime DEFAULT NULL,
  PRIMARY KEY (`booking_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `booking_No`, `user_ID`, `vehicle_ID`, `start_Data`, `end_Date`, `total`, `status`, `booking_Date`, `update_Date`) VALUES
(1, 'BOOK88523', 1, 3, '2023-06-06', '2023-06-08', 13000, 1, '2023-06-06 23:13:43', NULL),
(3, 'BOOK44665', 1, 4, '2023-06-07', '2023-06-08', 9500, 0, '2023-06-07 23:17:44', NULL);

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
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` longtext,
  `city` varchar(50) DEFAULT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `account_status` int(1) DEFAULT '1',
  `rag_date` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `address`, `city`, `phone`, `dob`, `profile_pic`, `account_status`, `rag_date`) VALUES
(3, 'hweranmadhuka@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Eran Madhuka', 'hweranmadhuka@gmail.com', 'No:94, willorawatta, moratuwa.', 'Moratuwa', '0785862007', '', '6485bb59d310d_Untitled-1_0005_Layer-3-545x389.jpg', 1, '2023-06-11 17:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_title` varchar(50) DEFAULT NULL,
  `vehicle_brand` varchar(50) DEFAULT NULL,
  `vehicle_desc` longtext,
  `price` float DEFAULT NULL,
  `transmission` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `engine_capacity` varchar(50) DEFAULT NULL,
  `capacity` int(3) DEFAULT NULL,
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
  PRIMARY KEY (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `vehicle_title`, `vehicle_brand`, `vehicle_desc`, `price`, `transmission`, `fuel_type`, `year`, `engine_capacity`, `capacity`, `vImg1`, `vImg2`, `vImg3`, `vImg4`, `airConditioner`, `powerdoorlocks`, `antilockbrakingsys`, `brakeassist`, `powersteering`, `driverairbag`, `passengerairbag`, `powerwindow`, `cdplayer`, `reg_date`, `updatation_date`, `vehicle_status`) VALUES
(1, 'Maruti Suzuki Wagon R', 'Suzuki ', 'Maruti Wagon R Latest Updates Maruti Suzuki has launched the BS6 Wagon R S-CNG in India. The LXI CNG and LXI (O) CNG variants now cost Rs 5.25 lakh and Rs 5.32 lakh respectively, up by Rs 19,000. Maruti claims a fuel economy of 32.52km per kg. The CNG Wagon Râ€™s continuation in the BS6 era is part of the carmakerâ€™s â€˜Mission Green Millionâ€™ initiative announced at Auto Expo 2020. Previously, the carmaker had updated the 1.0-litre powertrain to meet BS6 emission norms. It develops 68PS of power and 90Nm of torque, same as the BS4 unit. However, the updated motor now returns 21.79 kmpl, which is a little less than the BS4 unitâ€™s 22.5kmpl claimed figure. Barring the CNG variants, the prices of the Wagon R 1.0-litre have been hiked by Rs 8,000.', 4500, 'Automatic', 'Petrol', 2019, '1000', 5, '647461220a984_rear-3-4-left-589823254_930x620.jpg', '647461220acf0_steering-close-up-1288209207_930x620.jpg', '647461220ae1c_tail-lamp-1666712219_930x620.jpg', '647461220af82_rear-3-4-right-520328200_930x620.jpg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 13:54:02', NULL, 1),
(2, 'Mercedes AMG', 'Benz ', 'Edipisicing eiusmod tempor incididunt labore sed dolore magna aliqa enim ipsum ad minim veniams quis nostrud citation ullam laboris nisi ut aliquip laboris nisi ut aliquip ex ea commod. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 8500, 'Automatic', 'Petrol', 2015, '3900', 5, '647466ed2a721_bmw_x6_m_competition_2020_5k-1280x720-1-750x430.jpg', '647466ed2a8ac_gallery06-1024x728-min-480x360.jpg', '647466ed2a96b_gallery05-1024x728-min-480x360.jpg', '647466ed2aa11_gallery07-1024x728-min-480x360.jpg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 14:18:45', NULL, 1),
(3, 'Audi Q8', 'Audi', 'As per ARAI, the mileage of Q8 is 0 kmpl. Real mileage of the vehicle varies depending upon the driving habits. City and highway mileage figures also vary depending upon the road conditions.', 6500, 'Automatic', 'Petrol', 2017, '3000', 5, '647467ceb36f9_1audiq8.jpg', '647467ceb3a95_1920x1080_MTC_XL_framed_Audi-Odessa-Armaturen_Spiegelung_CC_v05.jpg', '647467ceb3b69_audi1.jpg', '647467ceb3c05_audi-q8-front-view4.jpeg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 14:22:30', NULL, 1),
(4, 'Toyota Fortuner', 'Toyota ', 'Toyota Fortuner Features: It is a premium seven-seater SUV loaded with features such as LED projector headlamps with LED DRLs, LED fog lamp, and power-adjustable and foldable ORVMs. Inside, the Fortuner offers features such as power-adjustable driver seat, automatic climate control, push-button stop/start, and cruise control. Toyota Fortuner Safety Features: The Toyota Fortuner gets seven airbags, hill assist control, vehicle stability control with brake assist, and ABS with EBD.', 9500, 'Automatic', 'Diesel', 2020, '3500', 8, '6474689c404e9_zw-toyota-fortuner-2020-2.jpg', '6474689c40622_toyota-fortuner-legender-rear-quarters-6e57.jpg', '6474689c40711_marutisuzuki-vitara-brezza-dashboard10.jpg', '6474689c407c8_2015_Toyota_Fortuner_(New_Zealand).jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, '2023-05-29 14:25:56', NULL, 1),
(5, 'Nissan Kicks', 'Nissan', 'Latest Update: Nissan has launched the Kicks 2020 with a new turbocharged petrol engine. You can read more about it here. Nissan Kicks Price and Variants: The Kicks is available in four variants: XL, XV, XV Premium, and XV Premium(O).', 6500, 'Automatic', 'Diesel', 2020, '2500', 8, '647f18794bf11_front-left-side-47.jpg', '647f18794c12b_kicksmodelimage.jpg', '647f18794c2b0_marutisuzuki-vitara-brezza-dashboard10.jpg', '647f18794c39f_Nissan-Sunny-Interior-114977.jpg', 1, 0, 0, 0, 0, 0, 0, 0, 0, '2023-06-06 16:58:57', NULL, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
