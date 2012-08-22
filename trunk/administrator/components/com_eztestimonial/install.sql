CREATE TABLE IF NOT EXISTS `#__testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jomid` int(10) NOT NULL,
  `fullName` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `location` varchar(60) NOT NULL,
  `aboutauthor` text NOT NULL,
  `website` varchar(60) NOT NULL,
  `message_summary` tinytext NOT NULL,
  `message_long` text NOT NULL,
  `image_name` varchar(60) NOT NULL,
  `added_date` datetime NOT NULL,
  `rating` int(5) NOT NULL,
  `approved` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;