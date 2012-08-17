<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
 
jimport( 'joomla.application.component.model' );

class rsseoModelsitemap extends JModel
{
	function __construct()
	{	
		parent::__construct();
	}
	
	function getData()
	{
		$db =& JFactory::getDBO();
		
		$db->setQuery("SELECT `menutype`, `title` FROM `#__menu_types`");
		return $db->loadObjectList();
	}
	
	function getSelected()
	{
		$db =& JFactory::getDBO();
		
		$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'sitemap_menus' ");
		$selected = $db->loadResult();
		
		return !empty($selected) ? explode(',',$selected) : array();
	}
	
	function getExcludes()
	{
		$db =& JFactory::getDBO();
		
		$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'sitemap_excludes' ");
		$selected = $db->loadResult();
		
		return !empty($selected) ? explode(',',$selected) : array();
	}
}