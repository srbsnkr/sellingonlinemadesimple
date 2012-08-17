<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllerAnalytics extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	function save()
	{
		$db =& JFactory::getDBO();
		$app =& JFactory::getApplication();
		
		$account	= JRequest::getVar('account',0);
		$start		= JRequest::getVar('rssestart',0);
		$end		= JRequest::getVar('rsseend',0);
		
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($account)."' WHERE ConfigName = 'ga.account' ");
		$db->query();
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($start)."' WHERE ConfigName = 'ga.start' ");
		$db->query();
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($end)."' WHERE ConfigName = 'ga.end' ");
		$db->query();
		
		$app->redirect('index.php?option=com_rsseo&task=analytics');
	}	
}