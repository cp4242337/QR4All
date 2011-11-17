ALTER TABLE `qr4_users` ADD `usr_type` ENUM( 'trial', 'paid', 'ext', 'int', 'exp' ) NOT NULL DEFAULT 'int' AFTER `usr_pass` ,
ADD `usr_address1` VARCHAR( 555 ) NOT NULL AFTER `usr_type` ,
ADD `usr_address2` VARCHAR( 255 ) NOT NULL AFTER `usr_address1` ,
ADD `usr_city` VARCHAR( 255 ) NOT NULL AFTER `usr_address2` ,
ADD `usr_state` VARCHAR( 255 ) NOT NULL AFTER `usr_city` ,
ADD `usr_zip` VARCHAR( 255 ) NOT NULL AFTER `usr_state` ,
ADD `usr_expdate` DATE NOT NULL AFTER `usr_zip` ,
ADD `usr_billamt` FLOAT NOT NULL AFTER `usr_expdate` ,
ADD `usr_lastbilldate` DATETIME NOT NULL AFTER `usr_billamt` ,
ADD `usr_nextbilldate` DATETIME NOT NULL AFTER `usr_lastbilldate`,
ADD `usr_phone` VARCHAR( 255 ) NOT NULL AFTER `usr_zip` ,
ADD `usr_fax` VARCHAR( 255 ) NOT NULL AFTER `usr_phone`,
ADD `usr_paypalrpid` VARCHAR( 50 ) NOT NULL AFTER `usr_nextbilldate`;

ALTER TABLE `qr4_users` ADD `usr_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `usr_type` ;
ALTER TABLE `qr4_users` ADD `usr_template` INT NOT NULL DEFAULT '8' AFTER `usr_type` ;

ALTER TABLE `qr4_menu` ADD `menu_parent` INT NOT NULL AFTER `menu_lvl` ;

ALTER TABLE `qr4_menu` ADD `menu_int` BOOLEAN NOT NULL AFTER `menu_lvl` ,
ADD `menu_ext` BOOLEAN NOT NULL AFTER `menu_int` ,
ADD `menu_paid` BOOLEAN NOT NULL AFTER `menu_ext` ,
ADD `menu_trial` BOOLEAN NOT NULL AFTER `menu_paid` ,
ADD `menu_exp` BOOLEAN NOT NULL AFTER `menu_trial` ;

CREATE TABLE IF NOT EXISTS `qr4_formdata_sessions` (
  `form` varchar(20) NOT NULL,
  `time` varchar(14) DEFAULT '',
  `session_id` varchar(200) NOT NULL DEFAULT '0',
  `data` longtext,
  PRIMARY KEY (`session_id`(64)),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

ALTER TABLE `qr4_forms` ADD `form_sessiontime` INT NOT NULL DEFAULT '30' AFTER `form_body` ;
ALTER TABLE `qr4_formpages` ADD `page_actiontext` VARCHAR( 100 ) NOT NULL  DEFAULT 'Submit' AFTER `page_action` ;
ALTER TABLE `qr4_formpages` ADD `page_redirurl` VARCHAR( 255 ) NOT NULL AFTER `page_actiontext` ;
ALTER TABLE `qr4_formpages` CHANGE `page_action` `page_action` ENUM( 'next', 'submit', 'submitmail', 'none', 'reset','redirect' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;


TRUNCATE qr4_menu;

DROP TABLE qr4_session;

INSERT INTO `qr4_menu` (`menu_id`, `menu_name`, `menu_mod`, `menu_task`, `menu_lvl`, `menu_int`, `menu_ext`, `menu_paid`, `menu_trial`, `menu_exp`, `menu_parent`, `ordering`, `published`) VALUES
(1, 'Home', 'home', '', 'lvl_basic', 1, 1, 1, 1, 1, 0, 1, 1),
(2, 'Codes', 'codelist', '', 'lvl_basic', 1, 1, 1, 1, 0, 0, 10, 1),
(3, 'Users', 'users', '', 'lvl_root', 1, 0, 0, 0, 0, 11, 20, 1),
(4, 'Clients', 'clients', '', 'lvl_admin', 1, 0, 0, 0, 0, 11, 30, 1),
(5, 'Logout', 'users', 'logoutuser', 'lvl_basic', 1, 1, 1, 1, 1, 0, 1000, 1),
(6, 'Cats', 'cats', '', 'lvl_edit', 1, 1, 1, 1, 0, 0, 40, 1),
(7, 'Videos', 'vidlist', '', 'lvl_basic', 1, 1, 0, 0, 0, 0, 15, 1),
(8, 'Forms', 'formlist', '', 'lvl_basic', 1, 1, 0, 0, 0, 0, 17, 1),
(9, 'Templates', 'templates', '', 'lvl_admin', 1, 0, 0, 0, 0, 11, 50, 1),
(10, 'My Account', 'users', 'myaccount', 'lvl_basic', 1, 1, 1, 1, 1, 0, 900, 0),
(11, 'Admin', 'home', '', 'lvl_admin', 1, 0, 0, 0, 0, 0, 100, 1);