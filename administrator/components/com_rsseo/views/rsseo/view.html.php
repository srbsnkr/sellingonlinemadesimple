<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
jimport( 'joomla.application.component.model' );
jimport('joomla.html.pane');

class rsseoViewrsseo extends JView
{
	function display( $tpl = null )
	{	
		$app =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$task = JRequest::getVar('task','','request');
		$this->assignRef('rsseoConfig',$app->getUserState('rsseoConfig'));		
		
		switch($task)
		{
			case 'update':
			{
				JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update',true);
				
				JToolBarHelper::title(JText::_('RSSEO_UPDATE'),'rsseo');
				JToolBarHelper::custom('main','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
			
			} break;
		
			default:
			{
				
				JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo',true);
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
				JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update');
				
				JToolBarHelper::title(JText::_('RSSEO_PRODUCT'),'rsseo');
				JToolBarHelper::custom('checkconnections','checking','checking',JText::_('RSSEO_CHECKING_FOR_FUNCTIONS'),false);
				JToolBarHelper::custom('main','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
			
			} break;
			
		}
		
		
		parent::display($tpl);
	}
	
	function genKeyCode()
	{
		$app =& JFactory::getApplication();
		$rsseoConfig = $app->getUserState('rsseoConfig');
		return md5($rsseoConfig['global.register.code'].RSSEO_KEY);
	}
}