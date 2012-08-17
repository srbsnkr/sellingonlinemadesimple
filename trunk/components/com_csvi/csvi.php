<?php
/**
 * Front-end interface
 *
 * @package 	CSVI
 * @author 		Roland Dalmulder
 * @link 		http://www.csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2012 RolandD Cyber Produksi
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: csvi.php 1924 2012-03-02 11:32:38Z RolandD $
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

$jinput = JFactory::getApplication()->input;

// Make sure we are coming in via raw format
$format = $jinput->get('format');
if ($format != 'raw') {
	$app = JFactory::getApplication();
	$uri = JFactory::getURI();
	$uri->setVar('format', 'raw');
	$app->redirect(JRoute::_($uri->toString()));
}

// Load the logger
require_once (JPATH_COMPONENT_ADMINISTRATOR.'/helpers/log.php');

// Load the general helper
require_once (JPATH_COMPONENT_ADMINISTRATOR.'/helpers/csvi.php');

// Define the tmp folder
$config = JFactory::getConfig();
$tmp_path = $config->getValue('config.tmp_path');
if (!defined('CSVIPATH_TMP')) define('CSVIPATH_TMP', JPath::clean($tmp_path.'/com_csvi', '/'));
if (!defined('CSVIPATH_DEBUG')) define('CSVIPATH_DEBUG', JPath::clean($tmp_path.'/com_csvi/debug', '/'));

// Include dependancies
jimport('joomla.application.component.controller');
$jinput->set('task', 'export.export');


// Create the controller
$controller = JController::getInstance('csvi');
$controller->execute($jinput->get('task'));
$controller->redirect();
?>