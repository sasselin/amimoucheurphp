<?php
/**
 * hook.conf.php file defines all required constants and variables for hook context
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2016 Business Tech SARL
 */

require_once(dirname(__FILE__) . '/common.conf.php');

/* defines hook library path */
define('_GMC_PATH_LIB_HOOK', _GMC_PATH_LIB . 'hook/');

/* defines front tpl path */
define('_GMC_TPL_FRONT_PATH', 'front/');

/* defines hook tpl path */
define('_GMC_TPL_HOOK_PATH', 'hook/');

/* defines header tpl */
define('_GMC_TPL_HEADER', 'header.tpl');

/* defines variable for setting all request params */
$GLOBALS[_GMC_MODULE_NAME . '_REQUEST_PARAMS'] = array(
	'search' => array('action' => 'search', 'type' => 'product'),
);