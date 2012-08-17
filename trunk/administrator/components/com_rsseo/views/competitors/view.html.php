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

class rsseoViewcompetitors extends JView
{
	function display($tpl = null)
	{
		$app =& JFactory::getApplication();
		$db  =& JFactory::getDBO();
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors', true); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		$this->assignRef('rsseoConfig',$app->getuserState('rsseoConfig'));
		$task=JRequest::getVar('task');
		
		switch($task)
		{
			case 'listcompetitors':
			{
				JToolBarHelper::title(JText::_('RSSEO_LIST_COMPETITORS'),'rsseo');	
				JToolBarHelper::addNewX('editcompetitor');
				JToolBarHelper::editListX('editcompetitor');
				JToolBarHelper::deleteList("Are you sure you want to delete?", 'remove' , "Delete");
								
				
				JToolBarHelper::custom('competeRedirect','compete','compete',JText::_('RSSEO_COMPETE'),true);
				JToolBarHelper::custom('export','upload','upload_f2',JText::_('RSSEO_EXPORT_COMPETITORS'),false);
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				
				$listcompetitors	= $this->get('data');
				$pagination			= $this->get('pagination');
				$filter				= JRequest::getVar('rs_filter');
				$sortColumn			= JRequest::getVar('filter_order','c.ordering');
				$sortOrder			= JRequest::getVar('filter_order_Dir','asc');
				
				$this->assignRef('filter',$filter);
				$this->assignRef('sortColumn',$sortColumn);
				$this->assignRef('sortOrder',$sortOrder);
				$this->assignRef('listcompetitors',$listcompetitors);
				$this->assignRef('pagination',$pagination);

			} break;
			
			case 'listcompetitorshistory':
			{
				$competitor = $this->get('competitor');
				JToolBarHelper::title(JText::_('RSSEO_LIST_COMPETITOR_HISTORY') . ' '. $competitor->Competitor,'rsseo');
				JToolBarHelper::custom('listcompetitors','competitors-32','competitors-32',JText::_('RSSEO_LISTCOMPETITORS_COMPETITORS_TITLE'),false);
				JToolBarHelper::deleteList("Are you sure you want to delete?", 'removeHistory' , "Delete");
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				
				$listhistory=$this->get('competitorsHistory');
				
				$filter			=JRequest::getVar('rs_filter');
				$sortColumn		=JRequest::getVar('filter_order','ch.DateRefreshed');
				$sortOrder	=JRequest::getVar('filter_order_Dir','desc');

				$this->assignRef('filter',$filter);
				$this->assignRef('sortColumn',$sortColumn);
				$this->assignRef('sortOrder',$sortOrder);
				$this->assignRef('listhistory',$listhistory);

			} break;
			
			case 'editcompetitor':
			{	
				$cid=JRequest::getVar('cid',array(0),'request','array');
				if(is_array($cid)) $cid=intval($cid[0]);
				
				if($cid)
					JToolBarHelper::title(JText::_('RSSEO_COMPETITOR_EDIT'),'rsseo');
				else
					JToolBarHelper::title(JText::_('RSSEO_COMPETITOR_NEW'),'rsseo');
			
				
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				
				$protocol_select[]	= JHTML::_('select.option',  'http://', JText::_( 'RSSEO_COMPETITOR_HTTP' ), 'val', 'text' );
				$protocol_select[]	= JHTML::_('select.option',  'https://', JText::_( 'RSSEO_COMPETITOR_HTTPS' ), 'val', 'text' );
				
				$lists['protocol_select'] = JHTML::_('select.genericlist', $protocol_select, 'protocol_select', 'class="inputbox" size="1"','val', 'text' );
				
				$competitor = $this->get('competitor');
				
				$this->assignRef('lists',$lists);
				$this->assignRef('data',$competitor);
				
			} break;
		}
		
		parent::display($tpl);
	}
}		
?>