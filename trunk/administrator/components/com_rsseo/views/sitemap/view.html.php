<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsseoViewsitemap extends JView
{
	function display($tpl = null)
	{
		$db =& JFactory::getDBO();
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap',true); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		$task=JRequest::getVar('task','','request');
		switch($task)
		{
			case 'sitemap':
			{
				jimport('joomla.html.pane');
				$params = array();
				$params['startOffset'] = JRequest::getInt('tabposition', 0);
				$tabs =& JPane::getInstance('Tabs',$params,true);
				
				$sitemaps = (!(file_exists(JPATH_SITE.DS.'sitemap.xml') && is_writable(JPATH_SITE.DS.'sitemap.xml') && file_exists(JPATH_SITE.DS.'ror.xml') && is_writable(JPATH_SITE.DS.'ror.xml'))) ? 0 : 1;
				$this->assignRef('sitemaps',$sitemaps);
				
				$db->setQuery("SELECT COUNT(*) FROM #__rsseo_pages");
				$no_pages = (!$db->loadResult()) ? 0 : 1;
				$this->assignRef('pages',$no_pages);				
				
				JToolBarHelper::title(JText::_('RSSEO_SITEMAP'),'rsseo');
				JToolBarHelper::cancel();
				
				//set the options for the frequency
				$frequency[]	= JHTML::_('select.option',  '', JText::_( 'RSSEO_SITEMAP_FREQUENCY_NONE' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'always', JText::_( 'RSSEO_SITEMAP_FREQUENCY_ALWAYS' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'hourly', JText::_( 'RSSEO_SITEMAP_FREQUENCY_HOURLY' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'daily', JText::_( 'RSSEO_SITEMAP_FREQUENCY_DAILY' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'weekly', JText::_( 'RSSEO_SITEMAP_FREQUENCY_WEEKLY' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'monthly', JText::_( 'RSSEO_SITEMAP_FREQUENCY_MONTHLY' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'yearly', JText::_( 'RSSEO_SITEMAP_FREQUENCY_YEARLY' ), 'val', 'text' );
				$frequency[]	= JHTML::_('select.option',  'never', JText::_( 'RSSEO_SITEMAP_FREQUENCY_NEVER' ), 'val', 'text' );
				
				$lists['frequency']      = JHTML::_('select.genericlist', $frequency, 'SitemapFrequency', 'class="inputbox" size="1" ','val', 'text','weekly' );
				
				$priority[]	= JHTML::_('select.option',  '', JText::_( 'RSSEO_SITEMAP_PRIORITY_NONE' ), 'val', 'text' );
				$priority[]	= JHTML::_('select.option',  'auto', JText::_( 'RSSEO_SITEMAP_PRIORITY_AUTOMATIC' ), 'val', 'text' );
				
				$lists['priority']      = JHTML::_('select.genericlist', $priority, 'SitemapPriority', 'class="inputbox" size="1" ','val', 'text','auto');
				
				$protocols[] = JHTML::_('select.option',  '0', JText::_( 'HTTP' ));
				$protocols[] = JHTML::_('select.option',  '1', JText::_( 'HTTPS' ));
				$lists['scheme'] = JHTML::_('select.genericlist', $protocols, 'protocol', 'class="inputbox" size="1" ','value', 'text',0);
				
				$this->assignRef('lists',$lists);
				
				//get total number of pages
				$db->setQuery("SELECT count(*) FROM #__rsseo_pages WHERE PageInSitemap = 1");
				$total = $db->loadResult();
				if (!$total) $total = 1;
		
				//get the numbet of pages that have been processed
				$db->setQuery("SELECT count(*) FROM #__rsseo_pages WHERE PageSitemap=1 AND PageInSitemap = 1");
				$processed=$db->loadResult();
				
				$data     = $this->get('Data');
				$selected = $this->get('Selected');
				$excludes = $this->get('Excludes');
				
				
				$this->assignRef('progress',ceil($processed*100/$total));
				$this->assignRef('tabs',$tabs);
				$this->assignRef('data',$data);
				$this->assignRef('selected',$selected);
				$this->assignRef('excludes',$excludes);

			} break;
		}
		
		
		parent::display($tpl);
	}
}