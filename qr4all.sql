-- QR4All 0.5
-- Licsensed under GPLv2
-- (C) Corona Productions



CREATE TABLE IF NOT EXISTS `qr4_catcodes` (
  `catcd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catcd_cat` int(11) NOT NULL,
  `catcd_code` bigint(20) NOT NULL,
  PRIMARY KEY (`catcd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `qr4_cats` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_catvids` (
  `catvid_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catvid_cat` int(11) NOT NULL,
  `catvid_vid` bigint(20) NOT NULL,
  PRIMARY KEY (`catvid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

CREATE TABLE IF NOT EXISTS `qr4_clientcats` (
  `clcat_id` int(11) NOT NULL AUTO_INCREMENT,
  `clcat_cat` int(11) NOT NULL,
  `clcat_client` int(11) NOT NULL,
  PRIMARY KEY (`clcat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_clientcodes` (
  `clcd_id` int(11) NOT NULL AUTO_INCREMENT,
  `clcd_code` int(11) NOT NULL,
  `clcd_client` int(11) NOT NULL,
  PRIMARY KEY (`clcd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_clients` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qr4_clientvids` (
  `clvid_id` int(11) NOT NULL AUTO_INCREMENT,
  `clvid_vid` int(11) NOT NULL,
  `clvid_client` int(11) NOT NULL,
  PRIMARY KEY (`clvid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_codes` (
  `cd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cd_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `cd_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cd_url` text COLLATE utf8_unicode_ci NOT NULL,
  `cd_type` enum('qr','web','txt') COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_hits` (
  `hit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hit_code` bigint(20) NOT NULL,
  `hit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hit_ipaddr` varchar(15) CHARACTER SET latin1 NOT NULL,
  `hit_useragent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_browser` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_browserver` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_platform` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_ismobile` tinyint(1) NOT NULL DEFAULT '0',
  `hit_lat` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_long` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_region` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_countrycode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `hit_timezone` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`hit_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_mod` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_task` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_lvl` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

INSERT INTO `qr4_menu` (`menu_id`, `menu_name`, `menu_mod`, `menu_task`, `menu_lvl`, `ordering`, `published`) VALUES
(1, 'Home', 'home', '', 1, 1, 1),
(2, 'Codes', 'codelist', '', 1, 10, 1),
(3, 'Users', 'users', '', 3, 20, 1),
(4, 'Clients', 'clients', '', 2, 30, 1),
(5, 'Logout', 'users', 'logoutuser', 1, 1000, 1),
(6, 'Cats', 'cats', '', 2, 40, 1),
(7, 'Videos', 'vidlist', '', 1, 15, 1);

CREATE TABLE IF NOT EXISTS `qr4_session` (
  `sess_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sess_user` int(11) NOT NULL,
  `sess_time` int(11) NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_userlvels` (
  `lvl_id` int(11) NOT NULL,
  `lvl_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `qr4_userlvels` (`lvl_id`, `lvl_name`) VALUES
(1, 'Registered'),
(2, 'Admin'),
(3, 'Root');

CREATE TABLE IF NOT EXISTS `qr4_users` (
  `usr_id` int(3) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(60) CHARACTER SET latin1 NOT NULL,
  `usr_fullname` varchar(255) NOT NULL,
  `usr_email` varchar(60) CHARACTER SET latin1 NOT NULL,
  `usr_pass` text CHARACTER SET latin1 NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `usr_level` int(11) NOT NULL,
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`usr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

INSERT INTO `qr4_users` (`usr_id`, `usr_name`, `usr_fullname`, `usr_email`, `usr_pass`, `published`, `usr_level`, `trashed`) VALUES
(12, 'root', 'Root User', 'mamundsen@coronapro.com', '33beb5939f8b799a102a9d5c9e698a6a', 1, 3, 0);


CREATE TABLE IF NOT EXISTS `qr4_usersclients` (
  `cu_id` int(11) NOT NULL AUTO_INCREMENT,
  `cu_user` int(11) NOT NULL,
  `cu_client` int(11) NOT NULL,
  PRIMARY KEY (`cu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_vhits` (
  `hit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hit_vid` bigint(20) NOT NULL,
  `hit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hit_ipaddr` varchar(15) CHARACTER SET latin1 NOT NULL,
  `hit_useragent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_browser` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_browserver` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_platform` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_ismobile` tinyint(1) NOT NULL DEFAULT '0',
  `hit_lat` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_long` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_region` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hit_countrycode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `hit_timezone` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`hit_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


CREATE TABLE IF NOT EXISTS `qr4_viddom` (
  `vd_id` int(11) NOT NULL AUTO_INCREMENT,
  `vd_dom` varchar(255) NOT NULL,
  PRIMARY KEY (`vd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `qr4_videos` (
  `vid_id` int(11) NOT NULL AUTO_INCREMENT,
  `vid_title` varchar(100) NOT NULL,
  `vid_file` varchar(255) NOT NULL,
  `vid_code` varchar(20) NOT NULL,
  `vid_domain` int(11) NOT NULL DEFAULT '1',
  `vid_ratio` enum('43','169') NOT NULL DEFAULT '169',
  `vid_pubtitle` varchar(150) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

