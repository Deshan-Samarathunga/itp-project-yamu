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
  `specialties` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `contact_preference` varchar(20) NOT NULL DEFAULT 'both',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`driver_ad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
