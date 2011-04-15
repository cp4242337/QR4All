-- QR4All 0.7
-- Licsensed under GPLv2
-- (C) Corona Productions

CREATE TABLE IF NOT EXISTS `qr4_catcodes` (
  `catcd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catcd_cat` int(11) NOT NULL,
  `catcd_code` bigint(20) NOT NULL,
  PRIMARY KEY (`catcd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_catforms` (
  `catform_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catform_cat` int(11) NOT NULL,
  `catform_form` bigint(20) NOT NULL,
  PRIMARY KEY (`catform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_cats` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_catvids` (
  `catvid_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catvid_cat` int(11) NOT NULL,
  `catvid_vid` bigint(20) NOT NULL,
  PRIMARY KEY (`catvid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_clientcats` (
  `clcat_id` int(11) NOT NULL AUTO_INCREMENT,
  `clcat_cat` int(11) NOT NULL,
  `clcat_client` int(11) NOT NULL,
  PRIMARY KEY (`clcat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_clientcodes` (
  `clcd_id` int(11) NOT NULL AUTO_INCREMENT,
  `clcd_code` int(11) NOT NULL,
  `clcd_client` int(11) NOT NULL,
  PRIMARY KEY (`clcd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_clientforms` (
  `clform_id` int(11) NOT NULL AUTO_INCREMENT,
  `clform_cl` int(11) NOT NULL,
  `clform_form` int(11) NOT NULL,
  PRIMARY KEY (`clform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_clients` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `cl_maxcodes` int(11) NOT NULL,
  `cl_maxvids` int(11) NOT NULL,
  `cl_maxforms` int(11) NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_clientvids` (
  `clvid_id` int(11) NOT NULL AUTO_INCREMENT,
  `clvid_vid` int(11) NOT NULL,
  `clvid_client` int(11) NOT NULL,
  PRIMARY KEY (`clvid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_codes` (
  `cd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cd_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `cd_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cd_url` text COLLATE utf8_unicode_ci NOT NULL,
  `cd_type` enum('qr','web','txt') COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_domains` (
  `dom_id` int(11) NOT NULL AUTO_INCREMENT,
  `dom_dom` varchar(255) CHARACTER SET latin1 NOT NULL,
  `dom_type` enum('video','form','code','stream') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`dom_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_fhits` (
  `hit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hit_form` bigint(20) NOT NULL,
  `hit_data` bigint(20) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formdata` (
  `data_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `data_form` int(11) NOT NULL,
  `data_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_end` datetime NOT NULL,
  `data_ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `data_session` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_form` (`data_form`),
  KEY `data_session` (`data_session`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formdata_answers` (
  `ans_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ans_data` bigint(20) NOT NULL,
  `ans_question` int(11) NOT NULL,
  `ans_answer` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ans_id`),
  KEY `ans_question` (`ans_question`),
  KEY `ans_data` (`ans_data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formitems` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_page` int(11) NOT NULL,
  `item_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `item_text` text COLLATE utf8_unicode_ci NOT NULL,
  `item_type` enum('msg','txt','tbx','eml','rad','mcb','cbx','dds','phn','hdn') COLLATE utf8_unicode_ci NOT NULL,
  `item_req` tinyint(1) NOT NULL DEFAULT '0',
  `item_confirm` tinyint(1) NOT NULL DEFAULT '0',
  `item_verify` tinyint(1) NOT NULL DEFAULT '0',
  `item_verify_limit` int(11) NOT NULL,
  `item_verify_msg` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_depend_item` int(11) NOT NULL,
  `item_match_item` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formitems_opts` (
  `opt_id` int(11) NOT NULL AUTO_INCREMENT,
  `opt_item` int(11) NOT NULL,
  `opt_text` text COLLATE utf8_unicode_ci NOT NULL,
  `opt_depend` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL,
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`opt_id`),
  KEY `opt_item` (`opt_item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formpages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_form` int(11) NOT NULL,
  `page_title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `page_type` enum('text','form','confirm') CHARACTER SET latin1 NOT NULL,
  `page_action` enum('next','submit','submitmail','none') CHARACTER SET latin1 NOT NULL,
  `page_content` text CHARACTER SET latin1 NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formpages_emails` (
  `eml_id` int(11) NOT NULL AUTO_INCREMENT,
  `eml_page` int(11) NOT NULL,
  `eml_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `eml_fromname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `eml_fromaddr` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `eml_toname` int(11) NOT NULL,
  `eml_toaddr` int(11) NOT NULL,
  `eml_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `eml_content` text COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eml_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formpages_emails_attach` (
  `at_id` int(11) NOT NULL AUTO_INCREMENT,
  `at_email` int(11) NOT NULL,
  `at_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filetype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filesize` int(11) NOT NULL,
  `at_content` longblob NOT NULL,
  PRIMARY KEY (`at_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_formpages_emails_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_eml` int(11) NOT NULL,
  `log_msg` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_eml` (`log_eml`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_forms` (
  `form_id` int(11) NOT NULL AUTO_INCREMENT,
  `form_code` varchar(8) CHARACTER SET latin1 NOT NULL,
  `form_title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `form_publictitle` varchar(255) CHARACTER SET latin1 NOT NULL,
  `form_template` int(11) NOT NULL,
  `form_domain` int(11) NOT NULL,
  `form_header` text COLLATE utf8_unicode_ci NOT NULL,
  `form_body` text COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`form_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_mod` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_task` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_lvl` enum('lvl_basic','lvl_edit','lvl_admin','lvl_root') COLLATE utf8_unicode_ci NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

INSERT INTO `qr4_menu` (`menu_id`, `menu_name`, `menu_mod`, `menu_task`, `menu_lvl`, `ordering`, `published`) VALUES
(1, 'Home', 'home', '', 'lvl_basic', 1, 1),
(2, 'Codes', 'codelist', '', 'lvl_basic', 10, 1),
(3, 'Users', 'users', '', 'lvl_root', 20, 1),
(4, 'Clients', 'clients', '', 'lvl_admin', 30, 1),
(5, 'Logout', 'users', 'logoutuser', 'lvl_basic', 1000, 1),
(6, 'Cats', 'cats', '', 'lvl_edit', 40, 1),
(7, 'Videos', 'vidlist', '', 'lvl_basic', 15, 1),
(8, 'Forms', 'formlist', '', 'lvl_basic', 17, 1);

CREATE TABLE IF NOT EXISTS `qr4_session` (
  `sess_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sess_user` int(11) NOT NULL,
  `sess_time` int(11) NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `qr4_templates` (
  `tmpl_id` int(11) NOT NULL AUTO_INCREMENT,
  `tmpl_name` varchar(80) CHARACTER SET latin1 NOT NULL,
  `tmpl_url` text CHARACTER SET latin1 NOT NULL,
  `tmpl_type` enum('video','form') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tmpl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_userlvels` (
  `lvl_id` int(11) NOT NULL AUTO_INCREMENT,
  `lvl_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `lvl_basic` tinyint(1) NOT NULL,
  `lvl_edit` tinyint(1) NOT NULL,
  `lvl_admin` tinyint(1) NOT NULL,
  `lvl_root` tinyint(1) NOT NULL,
  `lvl_order` int(11) NOT NULL,
  PRIMARY KEY (`lvl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

INSERT INTO `qr4_userlvels` (`lvl_id`, `lvl_name`, `lvl_basic`, `lvl_edit`, `lvl_admin`, `lvl_root`, `lvl_order`) VALUES
(1, 'Basic', 1, 0, 0, 0, 10),
(2, 'Admin', 1, 1, 1, 0, 30),
(3, 'Root', 1, 1, 1, 1, 100),
(4, 'Editor', 1, 1, 0, 0, 20);

CREATE TABLE IF NOT EXISTS `qr4_users` (
  `usr_id` int(3) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(60) CHARACTER SET latin1 NOT NULL,
  `usr_fullname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `usr_email` varchar(60) CHARACTER SET latin1 NOT NULL,
  `usr_pass` text CHARACTER SET latin1 NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `usr_level` int(11) NOT NULL,
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`usr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

INSERT INTO `qr4_users` (`usr_id`, `usr_name`, `usr_fullname`, `usr_email`, `usr_pass`, `published`, `usr_level`, `trashed`) VALUES
(12, 'root', 'Root User', 'mamundsen@coronapro.com', '33beb5939f8b799a102a9d5c9e698a6a', 1, 3, 0);

CREATE TABLE IF NOT EXISTS `qr4_usersclients` (
  `cu_id` int(11) NOT NULL AUTO_INCREMENT,
  `cu_user` int(11) NOT NULL,
  `cu_client` int(11) NOT NULL,
  PRIMARY KEY (`cu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qr4_videos` (
  `vid_id` int(11) NOT NULL AUTO_INCREMENT,
  `vid_title` varchar(100) CHARACTER SET latin1 NOT NULL,
  `vid_file` varchar(255) CHARACTER SET latin1 NOT NULL,
  `vid_code` varchar(20) CHARACTER SET latin1 NOT NULL,
  `vid_domain` int(11) NOT NULL DEFAULT '1',
  `vid_ratio` enum('43','169') CHARACTER SET latin1 NOT NULL DEFAULT '169',
  `vid_pubtitle` varchar(150) CHARACTER SET latin1 NOT NULL,
  `vid_sdomain` int(11) NOT NULL,
  `vid_tmpl` int(11) NOT NULL,
  `vid_returl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vid_rettitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

