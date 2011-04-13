--.5 to .7 update

--RENAME TABLE  `qr4all`.`qr4_viddom` TO  `qr4all`.`qr4_domains` ;

ALTER TABLE  `qr4_domains` CHANGE  `vd_id`  `dom_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `vd_dom`  `dom_dom` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE  `qr4_domains` ADD  `dom_type` ENUM(  'video',  'form',  'code', 'stream' ) NOT NULL;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `form_domain` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `trashed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`form_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr4_templates` (
  `tmpl_id` int(11) NOT NULL AUTO_INCREMENT,
  `tmpl_name` varchar(80) CHARACTER SET latin1 NOT NULL,
  `tmpl_url` text CHARACTER SET latin1 NOT NULL,
  `tmpl_type` enum('video','form') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tmpl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `qr4_menu` (`menu_id`, `menu_name`, `menu_mod`, `menu_task`, `menu_lvl`, `ordering`, `published`) VALUES
(8, 'Forms', 'formlist', '', 1, 17, 1);

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

ALTER TABLE  `qr4_userlvels` ADD PRIMARY KEY (  `lvl_id` );
ALTER TABLE  `qr4_userlvels` CHANGE  `lvl_id`  `lvl_id` INT( 11 ) NOT NULL AUTO_INCREMENT;

ALTER TABLE  `qr4_userlvels` ADD  `lvl_basic` BOOLEAN NOT NULL ,
ADD  `lvl_edit` BOOLEAN NOT NULL ,
ADD  `lvl_admin` BOOLEAN NOT NULL ,
ADD  `lvl_root` BOOLEAN NOT NULL,
ADD  `lvl_order` INT NOT NULL;

ALTER TABLE `qr4_videos ADD 
ADD  `vid_returl` varchar(255) NOT NULL,
ADD  `vid_rettitle` varchar(255) NOT NULL,
ADD  `vid_tmpl` int(11) NOT NULL DEFAULT '1',
ADD  `vid_sdomain` int(11) NOT NULL DEFAULT '4';

UPDATE  `qr4all`.`qr4_userlvels` SET  `lvl_basic` =  '1', `lvl_name` = 'Basic', `lvl_order` = '10' WHERE  `qr4_userlvels`.`lvl_id` =1;

UPDATE  `qr4all`.`qr4_userlvels` SET  `lvl_basic` =  '1', `lvl_edit` =  '1', `lvl_admin` =  '1', `lvl_order` = '30' WHERE  `qr4_userlvels`.`lvl_id` =2;

UPDATE  `qr4all`.`qr4_userlvels` SET  `lvl_basic` =  '1', `lvl_edit` =  '1', `lvl_admin` =  '1', `lvl_root` =  '1', `lvl_order` = '100' WHERE  `qr4_userlvels`.`lvl_id` =3;

INSERT INTO  `qr4all`.`qr4_userlvels` (`lvl_id` ,`lvl_name` ,`lvl_basic` ,`lvl_edit` ,`lvl_admin` ,`lvl_root`, `lvl_order`)
VALUES (4 ,  'Editor',  '1',  '1',  '0',  '0', '20');


UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_basic' WHERE  `qr4_menu`.`menu_id` =1;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_basic' WHERE  `qr4_menu`.`menu_id` =2;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_root' WHERE  `qr4_menu`.`menu_id` =3;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_admin' WHERE  `qr4_menu`.`menu_id` =4;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_basic' WHERE  `qr4_menu`.`menu_id` =5;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_edit' WHERE  `qr4_menu`.`menu_id` =6;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_basic' WHERE  `qr4_menu`.`menu_id` =7;

UPDATE  `qr4all`.`qr4_menu` SET  `menu_lvl` =  'lvl_basic' WHERE  `qr4_menu`.`menu_id` =8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_formdata_answers` (
  `ans_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ans_data` bigint(20) NOT NULL,
  `ans_question` int(11) NOT NULL,
  `ans_answer` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ans_id`),
  KEY `ans_question` (`ans_question`),
  KEY `ans_data` (`ans_data`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `qr4_formpages_emails_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_eml` int(11) NOT NULL,
  `log_msg` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_eml` (`log_eml`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
