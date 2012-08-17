<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class com_rsseoInstallerScript 
{
	function install($parent) 
	{
		
	}

	function postflight($type, $parent) 
	{
		$db =& JFactory::getDBO();
		
		if ($type == 'install')
		{
			
			$db->setQuery("UPDATE `#__extensions` SET `enabled` = '1' WHERE `element`='rsseo' AND `type`='plugin' ");
			$db->query();
		}
		
		if ($type == 'update')
		{			
			$sqlfile = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsseo'.DS.'install.mysql.utf8.sql';
			$buffer = file_get_contents($sqlfile);
			if ($buffer === false)
			{
				JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'));
				return false;
			}
			
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) == 0) {
				// No queries to process
				return 0;
			}
			
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);
					if (!$db->query())
					{
						JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
						return false;
					}
				}
			}
		}
		
	}
	
	function uninstall($parent) 
	{
		$db 		= JFactory::getDBO();
		$installer	= new JInstaller();

		$db->setQuery("SELECT extension_id FROM #__extensions WHERE `element`='rsseo' AND `folder`='system' AND `type` = 'plugin' LIMIT 1");
		$id = $db->loadResult();
		if ($id) $installer->uninstall('plugin', $id);
	}
}
