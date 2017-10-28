CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard` (
  `id_tracking` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tracking_code` text NOT NULL,
  `internal_name` varchar(256) NOT NULL,
  `page` varchar(256) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `is_extension` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tracking`),
  KEY `active` (`active`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard_product` (
  `id_tracking` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  KEY `id_tracking` (`id_tracking`,`id_product`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard_category` (
  `id_tracking` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  KEY `id_tracking` (`id_tracking`,`id_category`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard_shop` (
  `id_tracking` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_tracking`,`id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard_extensions` (
  `id_extension` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `extension_name` varchar(256) NOT NULL,
  PRIMARY KEY (`id_extension`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard_extensions_id` (
  `id_extension` int(10) unsigned NOT NULL,
  `id_tracking` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_tracking`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedtrackingwizard_extensions_var` (
  `id_tracking` int(10) unsigned NOT NULL,
  `var_name` varchar(255) NOT NULL,
  `value` varchar(256) NOT NULL,
  KEY `id_tracking` (`id_tracking`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;