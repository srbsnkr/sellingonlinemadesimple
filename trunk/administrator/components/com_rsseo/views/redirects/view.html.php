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

class rsseoViewredirects extends JView
{
	function display($tpl = null)
	{		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects',true); 
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
			case 'listredirects':
			{
				$db =& JFactory::getDBO();
				JToolBarHelper::title(JText::_('RSSEO_LIST_REDIRECTS'),'rsseo');
				
				JToolBarHelper::addNewX('editredirect');
				JToolBarHelper::editListX('editredirect');
				JToolBarHelper::deleteList("Are you sure you want to delete?", 'remove' , "Delete");
				JToolBarHelper::publishList();
				JToolBarHelper::unpublishList();
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				$filter			=JRequest::getVar('rs_filter');
				$sortColumn		=JRequest::getVar('filter_order','r.RedirectTo');
				$sortOrder		=JRequest::getVar('filter_order_Dir','desc');
				
				
				$pagination=$this->get('pagination');
				$listredirects=$this->get('data');
				

				$this->assignRef('location_filter',$category_filter);
				$this->assignRef('lists', $lists);
				$this->assignRef('filter',$filter);
				$this->assignRef('sortColumn',$sortColumn);
				$this->assignRef('sortOrder',$sortOrder);
				$this->assignRef('listredirects',$listredirects);
				$this->assignRef('pagination',$pagination);

			} break;
			
			case 'editredirect':
			{
				$db=& JFactory::getDBO();
				$cid=JRequest::getVar('cid',array(0),'request','array');
				if(is_array($cid)) $cid=intval($cid[0]);
				if($cid==0)
					JToolBarHelper::title(JText::_('RSSEO_REDIRECT_NEW'),'rsseo');
				else 
					JToolBarHelper::title(JText::_('RSSEO_REDIRECT_EDIT'),'rsseo');
					
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				
				//get redirect details
				$redirect=$this->get('Redirect');
				
				//load redirect types
				$redirect_types[] = JHTML::_( 'select.option', '301', JText::_('RSSEO_REDIRECT_PERMANENT') );
				$redirect_types[] = JHTML::_( 'select.option', '302', JText::_('RSSEO_REDIRECT_TEMPORARY') );
				

				$this->assignRef('RedirectType',JHTML::_( 'select.radiolist', $redirect_types, 'RedirectType', 'class="inputbox" ', 'value', 'text', empty($redirect->IdRedirect) ? 301 : $redirect->RedirectType));
				$this->assignRef('data',$redirect);
			} break;
		}
		
		parent::display($tpl);
	}
}		
?>