ALTER TABLE `qr4_templates` CHANGE `tmpl_type` `tmpl_type` ENUM( 'video', 'form', 'admin' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 

INSERT INTO `qr4_menu` (`menu_name`, `menu_mod`, `menu_task`, `menu_lvl`, `ordering`, `published`) VALUES
('Templates', 'templates', '', 'lvl_admin', 50, 1);