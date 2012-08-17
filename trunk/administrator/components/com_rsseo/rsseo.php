<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

define('RSSEO_PRODUCT','RSSEO!');
define('RSSEO_VERSION','1.0.0');
define('RSSEO_REVISION','11');
define('RSSEO_KEY','SEO56H8K3U');
define('RSSEO_COPYRIGHT','&copy;2007-2010 www.rsjoomla.com');
define('RSSEO_LICENSE','GPL License');
define('RSSEO_AUTHOR','<a href="http://www.rsjoomla.com" target="_blank">www.rsjoomla.com</a>');
	
// Require the base controller
require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'controller.php' );
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'rsseo.php');

JHTML::_('behavior.mootools');

//get the application
$app =& JFactory::getApplication();

$rsseoConfig = $app->getUserState('rsseoConfig');
if($rsseoConfig['enable.debug'])
{
	error_reporting(E_ALL);
	ini_set('display_errors',true);
}

// Require specific controller if requested
if ($controller = JRequest::getWord('controller')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

// Create the controller
$classname	= 'rsseoController'.$controller;
$controller	= new $classname();

$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/administrator/components/com_rsseo/assets/css/style.css');	
$document->addScript(JURI::root(true).'/administrator/components/com_rsseo/assets/js/rsseo.js');

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();