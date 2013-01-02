CREATE TABLE IF NOT EXISTS `qr4_catcodes` (
  `catcd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catcd_cat` int(11) NOT NULL,
  `catcd_code` bigint(20) NOT NULL,
  PRIMARY KEY (`catcd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `qr4_catforms` (
  `catform_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catform_cat` int(11) NOT NULL,
  `catform_form` bigint(20) NOT NULL,
  PRIMARY KEY (`catform_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_cats` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_catvids` (
  `catvid_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catvid_cat` int(11) NOT NULL,
  `catvid_vid` bigint(20) NOT NULL,
  PRIMARY KEY (`catvid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `qr4_clientcats` (
  `clcat_id` int(11) NOT NULL AUTO_INCREMENT,
  `clcat_cat` int(11) NOT NULL,
  `clcat_client` int(11) NOT NULL,
  PRIMARY KEY (`clcat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_clientcodes` (
  `clcd_id` int(11) NOT NULL AUTO_INCREMENT,
  `clcd_code` int(11) NOT NULL,
  `clcd_client` int(11) NOT NULL,
  PRIMARY KEY (`clcd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_clientforms` (
  `clform_id` int(11) NOT NULL AUTO_INCREMENT,
  `clform_cl` int(11) NOT NULL,
  `clform_form` int(11) NOT NULL,
  PRIMARY KEY (`clform_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_clients` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `cl_maxcodes` int(11) NOT NULL,
  `cl_maxvids` int(11) NOT NULL,
  `cl_maxforms` int(11) NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qr4_clientvids` (
  `clvid_id` int(11) NOT NULL AUTO_INCREMENT,
  `clvid_vid` int(11) NOT NULL,
  `clvid_client` int(11) NOT NULL,
  PRIMARY KEY (`clvid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_codes` (
  `cd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cd_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `cd_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cd_url` text COLLATE utf8_unicode_ci NOT NULL,
  `cd_type` enum('qr','web','txt') COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cd_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_domains` (
  `dom_id` int(11) NOT NULL AUTO_INCREMENT,
  `dom_dom` varchar(255) NOT NULL,
  `dom_type` enum('video','form','code','stream') NOT NULL,
  PRIMARY KEY (`dom_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formdata_answers` (
  `ans_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ans_data` bigint(20) NOT NULL,
  `ans_question` int(11) NOT NULL,
  `ans_answer` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ans_id`),
  KEY `ans_question` (`ans_question`),
  KEY `ans_data` (`ans_data`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formdata_sessions` (
  `form` varchar(20) NOT NULL,
  `time` varchar(14) DEFAULT '',
  `session_id` varchar(200) NOT NULL DEFAULT '0',
  `data` longtext,
  PRIMARY KEY (`session_id`(64)),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qr4_formitems` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_page` int(11) NOT NULL,
  `item_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `item_text` text COLLATE utf8_unicode_ci NOT NULL,
  `item_hint` text COLLATE utf8_unicode_ci NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formpages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_form` int(11) NOT NULL,
  `page_title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `page_type` enum('text','form','confirm') CHARACTER SET latin1 NOT NULL,
  `page_action` enum('next','submit','submitmail','none','reset','redirect') CHARACTER SET latin1 NOT NULL,
  `page_actiontext` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Submit',
  `page_redirurl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `page_content` text CHARACTER SET latin1 NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formpages_emails_attach` (
  `at_id` int(11) NOT NULL AUTO_INCREMENT,
  `at_email` int(11) NOT NULL,
  `at_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filetype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filesize` int(11) NOT NULL,
  `at_content` longblob NOT NULL,
  PRIMARY KEY (`at_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formpages_emails_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_eml` int(11) NOT NULL,
  `log_msg` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_eml` (`log_eml`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_forms` (
  `form_id` int(11) NOT NULL AUTO_INCREMENT,
  `form_code` varchar(16) CHARACTER SET latin1 NOT NULL,
  `form_title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `form_publictitle` varchar(255) CHARACTER SET latin1 NOT NULL,
  `form_template` int(11) NOT NULL,
  `form_domain` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  `form_header` text COLLATE utf8_unicode_ci NOT NULL,
  `form_body` text COLLATE utf8_unicode_ci NOT NULL,
  `form_sessiontime` int(11) NOT NULL DEFAULT '30',
  PRIMARY KEY (`form_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_mod` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_task` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_lvl` enum('lvl_basic','lvl_edit','lvl_admin','lvl_root') COLLATE utf8_unicode_ci NOT NULL,
  `menu_int` tinyint(1) NOT NULL,
  `menu_ext` tinyint(1) NOT NULL,
  `menu_paid` tinyint(1) NOT NULL,
  `menu_trial` tinyint(1) NOT NULL,
  `menu_exp` tinyint(1) NOT NULL,
  `menu_parent` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_qa_questions` (
  `qa_id` int(11) NOT NULL AUTO_INCREMENT,
  `qa_form` int(11) NOT NULL,
  `qa_data` int(11) NOT NULL,
  `qa_who` varchar(255) NOT NULL,
  `qa_whodetail` varchar(255) NOT NULL,
  `qa_question` text NOT NULL,
  `qa_published` tinyint(1) NOT NULL DEFAULT '0',
  `qa_answered` tinyint(4) NOT NULL,
  `qa_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `qa_order` int(11) NOT NULL,
  PRIMARY KEY (`qa_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `qr4_sessions` (
  `username` varchar(150) DEFAULT '',
  `time` varchar(14) DEFAULT '',
  `session_id` varchar(200) NOT NULL DEFAULT '0',
  `guest` tinyint(4) DEFAULT '1',
  `userid` int(11) DEFAULT '0',
  `data` longtext,
  PRIMARY KEY (`session_id`(64)),
  KEY `whosonline` (`guest`),
  KEY `userid` (`userid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qr4_templates` (
  `tmpl_id` int(11) NOT NULL AUTO_INCREMENT,
  `tmpl_name` varchar(80) CHARACTER SET latin1 NOT NULL,
  `tmpl_url` text CHARACTER SET latin1 NOT NULL,
  `tmpl_type` enum('video','form','admin') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tmpl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_userlvels` (
  `lvl_id` int(11) NOT NULL AUTO_INCREMENT,
  `lvl_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `lvl_basic` tinyint(1) NOT NULL,
  `lvl_edit` tinyint(1) NOT NULL,
  `lvl_admin` tinyint(1) NOT NULL,
  `lvl_root` tinyint(1) NOT NULL,
  `lvl_order` int(11) NOT NULL,
  PRIMARY KEY (`lvl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_users` (
  `usr_id` int(3) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(60) CHARACTER SET latin1 NOT NULL,
  `usr_fullname` varchar(255) NOT NULL,
  `usr_email` varchar(60) CHARACTER SET latin1 NOT NULL,
  `usr_pass` text CHARACTER SET latin1 NOT NULL,
  `usr_type` enum('trial','paid','ext','int','exp') NOT NULL DEFAULT 'int',
  `usr_template` int(11) NOT NULL DEFAULT '8',
  `usr_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usr_address1` varchar(555) NOT NULL,
  `usr_address2` varchar(255) NOT NULL,
  `usr_city` varchar(255) NOT NULL,
  `usr_state` varchar(255) NOT NULL,
  `usr_zip` varchar(255) NOT NULL,
  `usr_phone` varchar(255) NOT NULL,
  `usr_fax` varchar(255) NOT NULL,
  `usr_expdate` date NOT NULL,
  `usr_billamt` float NOT NULL,
  `usr_lastbilldate` datetime NOT NULL,
  `usr_nextbilldate` datetime NOT NULL,
  `usr_paypalrpid` varchar(50) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `usr_level` int(11) NOT NULL,
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`usr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qr4_usersclients` (
  `cu_id` int(11) NOT NULL AUTO_INCREMENT,
  `cu_user` int(11) NOT NULL,
  `cu_client` int(11) NOT NULL,
  PRIMARY KEY (`cu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_videos` (
  `vid_id` int(11) NOT NULL AUTO_INCREMENT,
  `vid_title` varchar(100) NOT NULL,
  `vid_file` varchar(255) NOT NULL,
  `vid_code` varchar(20) NOT NULL,
  `vid_domain` int(11) NOT NULL DEFAULT '1',
  `vid_ratio` enum('43','169') NOT NULL DEFAULT '169',
  `vid_pubtitle` varchar(150) NOT NULL,
  `vid_returl` varchar(255) NOT NULL,
  `vid_rettitle` varchar(255) NOT NULL,
  `vid_tmpl` int(11) NOT NULL DEFAULT '1',
  `vid_sdomain` int(11) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;