<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');
jimport( 'joomla.application.component.model' );

class rsseoViewcrawler extends JView
{
	function display($tpl = null)
	{		
		$app =& JFactory::getApplication();
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler',true);
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		$task=JRequest::getVar('task');
		switch($task)
		{
			case 'crawler':
			{
				$db = & JFactory::getDBO();
				JToolBarHelper::title(JText::_('RSSEO_CRAWLER'),'rsseo');
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);

				$this->assignRef('rsseoConfig',$app->getuserState('rsseoConfig'));

			} break;
					
			
		}
		
		parent::display($tpl);
	}
}		
?>