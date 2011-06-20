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
ALTER TABLE `qr4_users` ADD `usr_template` INT NOT NULL DEFAULT '1' AFTER `usr_type` 

ALTER TABLE `qr4_menu` ADD `menu_parent` INT NOT NULL AFTER `menu_lvl` 

ALTER TABLE `qr4_menu` ADD `menu_int` BOOLEAN NOT NULL AFTER `menu_lvl` ,
ADD `menu_ext` BOOLEAN NOT NULL AFTER `menu_int` ,
ADD `menu_paid` BOOLEAN NOT NULL AFTER `menu_ext` ,
ADD `menu_trial` BOOLEAN NOT NULL AFTER `menu_paid` ,
ADD `menu_exp` BOOLEAN NOT NULL AFTER `menu_trial` 
