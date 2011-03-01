--.5 to .7 update

RENAME TABLE  `qr4all`.`qr4_viddom` TO  `qr4all`.`qr4_domains` ;

ALTER TABLE  `qr4_domains` CHANGE  `vd_id`  `dom_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `vd_dom`  `dom_dom` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE  `qr4_domains` ADD  `dom_type` ENUM(  'video',  'form',  'code' ) NOT NULL;

ALTER TABLE `qr4_clients` ADD   `cl_maxcodes` int(11) NOT NULL;
ALTER TABLE `qr4_clients` ADD   `cl_maxvids` int(11) NOT NULL;
ALTER TABLE `qr4_clients` ADD   `cl_maxforms` int(11) NOT NULL;

CREATE TABLE IF NOT EXISTS `qr4_catforms` (
  `catform_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `catform_cat` int(11) NOT NULL,
  `catform_form` bigint(20) NOT NULL,
  PRIMARY KEY (`catform_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_clientforms` (
  `clform_id` int(11) NOT NULL AUTO_INCREMENT,
  `clform_cl` int(11) NOT NULL,
  `clform_form` int(11) NOT NULL,
  PRIMARY KEY (`clform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_fhits` (
  `hit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hit_form` bigint(20) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formitems` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_page` int(11) NOT NULL,
  `item_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `item_text` text COLLATE utf8_unicode_ci NOT NULL,
  `item_type` enum('msg','txt','tbx','eml','rad','mcb','cbx','dds','hdn') COLLATE utf8_unicode_ci NOT NULL,
  `item_req` tinyint(1) NOT NULL DEFAULT '0',
  `item_confirm` tinyint(1) NOT NULL DEFAULT '0',
  `item_verify` tinyint(1) NOT NULL DEFAULT '0',
  `item_verify_limit` int(11) NOT NULL,
  `item_depend_item` int(11) NOT NULL,
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
  `page_action` enum('next','submit','submitmail','none') CHARACTER SET latin1 NOT NULL,
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

CREATE TABLE IF NOT EXISTS `qr4_forms` (
  `form_id` int(11) NOT NULL AUTO_INCREMENT,
  `form_code` varchar(8) CHARACTER SET latin1 NOT NULL,
  `form_title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `form_publictitle` varchar(255) CHARACTER SET latin1 NOT NULL,
  `form_template` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`form_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_formtemplates` (
  `tmpl_id` int(11) NOT NULL AUTO_INCREMENT,
  `tmpl_name` varchar(80) CHARACTER SET latin1 NOT NULL,
  `tmpl_url` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`tmpl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `qr4_menu` (`menu_id`, `menu_name`, `menu_mod`, `menu_task`, `menu_lvl`, `ordering`, `published`) VALUES
(8, 'Forms', 'formlist', '', 1, 50, 1);

CREATE TABLE IF NOT EXISTS `qr4_formpages_emails_attach` (
  `at_id` int(11) NOT NULL AUTO_INCREMENT,
  `at_email` int(11) NOT NULL,
  `at_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filetype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `at_filesize` int(11) NOT NULL,
  `at_content` longblob NOT NULL,
  PRIMARY KEY (`at_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

