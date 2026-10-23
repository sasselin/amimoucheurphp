<?php
/**
 * install.conf.php file defines all needed constants and variables used in installation of module
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2016 Business Tech SARL
 */

require_once(dirname(__FILE__) . '/common.conf.php');

/* defines install library path */
define('_GMC_PATH_LIB_INSTALL', _GMC_PATH_LIB . 'install/');

/* defines installation sql file */
define('_GMC_INSTALL_SQL_FILE', 'install.sql'); // comment if not use SQL

/* defines uninstallation sql file */
define('_GMC_UNINSTALL_SQL_FILE', 'uninstall.sql'); // comment if not use SQL

/* defines constant for plug SQL install/uninstall debug */
define('_GMC_LOG_JAM_SQL', false); // comment if not use SQL

/* defines constant for plug CONFIG install/uninstall debug */
define('_GMC_LOG_JAM_CONFIG', false);