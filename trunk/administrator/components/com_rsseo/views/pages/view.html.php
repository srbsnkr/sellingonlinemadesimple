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

class rsseoViewpages extends JView
{
	function display($tpl = null)
	{
		$app =& JFactory::getApplication();
		
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages',true); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		$task=JRequest::getVar('task');
		switch($task)
		{
			case 'listpages':
			{
				$db = & JFactory::getDBO();
				JToolBarHelper::title(JText::_('RSSEO_LIST_PAGES'),'rsseo');

				JToolBarHelper::custom('publish','rsseo_ignore','rsseo_ignore',JText::_('RSSEO_PAGE_PUBLISHED'));
				JToolBarHelper::custom('unpublish','rsseo_unignore','rsseo_unignore',JText::_('RSSEO_PAGE_UNPUBLISHED'));
				JToolBarHelper::custom('restore','restore','restore',JText::_('RSSEO_RESTORE_PAGES'));
				JToolBarHelper::custom('refresh','refresh','refresh',JText::_('RSSEO_BULK_REFRESH'));
				
				JToolBarHelper::addNewX('editpage');
				JToolBarHelper::editListX('editpage');
				JToolBarHelper::deleteList("Are you sure you want to delete?", 'remove' , "Delete");
				$bar = & JToolBar::getInstance('toolbar');
				$bar->appendButton('Confirm',JText::_('RSSEO_DELETE_ALL_PAGES_MESSAGE',true),'delete',JText::_('RSSEO_DELETE_ALL_PAGES'),'removeall',false);
				
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				$filter			=$app->getUserStateFromRequest('com_rsseo.pages.rs_filter', 'rs_filter', '');
				
				$md5_descr		=JRequest::getVar('md5_descr');
				$md5_title		=JRequest::getVar('md5_title');
				$status_filter = JRequest::getVar('rs_status_filter');
				$page_level_filter = JRequest::getVar('rs_page_level_filter');
				
				
				$sortColumn		=JRequest::getVar('filter_order','PageLevel');
				$sortOrder	=JRequest::getVar('filter_order_Dir','asc');
				
				
				$pagination=$this->get('pagination');
				
				$page_published[]	= JHTML::_('select.option',  '', JText::_( 'RSSEO_PAGE_STATUS' ), 'val', 'text' );
				$page_published[]	= JHTML::_('select.option',  '0', JText::_( 'RSSEO_PAGE_UNPUBLISHED' ), 'val', 'text' );
				$page_published[]	= JHTML::_('select.option',  '1', JText::_( 'RSSEO_PAGE_PUBLISHED' ), 'val', 'text' );
				
				
				$page_level[]	= JHTML::_('select.option',  '', JText::_( 'RSSEO_PAGE_LEVEL' ), 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '0', '0', 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '1', '1', 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '2', '2', 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '3', '3', 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '4', '4', 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '5', '5', 'val', 'text' );
				$page_level[]	= JHTML::_('select.option',  '127', JText::_('RSSEO_UNDEFINED'), 'val', 'text' );
				
				
				$lists['page_published']      = JHTML::_('select.genericlist', $page_published, 'rs_status_filter', 'class="inputbox" size="1" onchange="submitbutton(\'listpages\');"','val', 'text', $status_filter);
				$lists['page_level']      = JHTML::_('select.genericlist', $page_level, 'rs_page_level_filter', 'class="inputbox" size="1" onchange="submitbutton(\'listpages\');"','val', 'text', $page_level_filter);
				
			
				$listpages=$this->get('data');
				
				
				$this->assignRef('location_filter',$category_filter);
				$this->assignRef('lists'      	, $lists);
				$this->assignRef('filter',$filter);
				$this->assignRef('md5_descr',$md5_descr);
				$this->assignRef('md5_title',$md5_title);
				$this->assignRef('sortColumn',$sortColumn);
				$this->assignRef('sortOrder',$sortOrder);
				$this->assignRef('listpages',$listpages);
				$this->assignRef('pagination',$pagination);
				$this->assignRef('rsseoConfig',$app->getuserState('rsseoConfig'));

			} break;
			
			case 'editpage':
			{
				JHTML::_('behavior.tooltip');
				$db=& JFactory::getDBO();
				$cid=JRequest::getVar('cid',array(0),'request','array');
				if(is_array($cid)) $cid=intval($cid[0]);
				if($cid==0)
					JToolBarHelper::title(JText::_('RSSEO_PAGE_NEW'),'rsseo');
				else 
					JToolBarHelper::title(JText::_('RSSEO_PAGE_EDIT'),'rsseo');
				
					
				JToolBarHelper::custom('checkpage','html','html',JText::_('RSSEO_CHECK'),false);
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				$page = $this->get('Page');
				$reg = JRegistry::getInstance('');
				$registry = JRegistry::getInstance('density');
				if (rsseoHelper::is16())
					$reg->loadJSON($page->params);
				else 
					$reg->loadINI($page->params);
					
				if (rsseoHelper::is16())
					$registry->loadJSON($page->densityparams);
				else
					$registry->loadINI($page->densityparams);
				
				$this->assignRef('cid',$cid);
				$this->assignRef('data',$page);
				$this->assignRef('params',$reg->toObject());
				$this->assignRef('densityparams',$registry->toArray());
				$this->assignRef('rsseoConfig',$app->getuserState('rsseoConfig'));
			} break;
			
			case 'showdetails':
			{
				JToolBarHelper::title(JText::_('RSSEO_PAGE_SIZE_DETAILS'),'rsseo');
				JToolBarHelper::custom('back','back','back',JText::_('RSSEO_BACK'),false);
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				$cid = JRequest::getVar('cid');
				$layout = $this->get('Details');
				
				$this->assignRef('layout',$layout);
				$this->assignRef('cid',$cid);
				
			} break;
		}
		
		parent::display($tpl);
	}
}		
?>