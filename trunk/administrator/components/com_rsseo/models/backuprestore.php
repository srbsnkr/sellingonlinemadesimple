<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );

class rsseoModelbackuprestore extends JModel
{
	
	function __construct()
	{	
		parent::__construct();
	}
	
	function getBackup()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'backup.php');
		$options = array();
		$options['queries'][] = array('query' => 'SELECT * FROM #__rsseo_pages', 'primary' => 'IdPage');
		$options['queries'][] = array('query' => 'SELECT * FROM #__rsseo_redirects', 'primary' => 'IdRedirect');
		$package = new RSPackage($options);
		$package->backup();
		echo $package->displayProgressBar();
	}
	
	function getRestore()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'backup.php');
		
		$ajax = JRequest::getVar('ajax',0,'post');
		
		$options = array();
		$options['redirect'] = 'index.php?option=com_rsseo&task=redirectrestore';
		$package = new RSPackage($options);
		$package->restore();
		echo $package->displayProgressBar();
	}
	
	
}