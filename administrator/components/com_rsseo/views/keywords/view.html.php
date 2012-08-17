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

class rsseoViewkeywords extends JView
{
	function display($tpl = null)
	{
		$db = & JFactory::getDBO();
		$app = & JFactory::getApplication();
		
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords',true);
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		$task=JRequest::getVar('task');
		switch($task)
		{
			case 'listkeywords':
			{
				
				JToolBarHelper::title(JText::_('RSSEO_LIST_KEYWORDS'),'rsseo');
				JToolBarHelper::addNewX('editkeyword');
				JToolBarHelper::addNewX('addmultikeywords',JText::_('RSSEO_KEYWORD_ADDMULTI'));
				JToolBarHelper::editListX('editkeyword');
				JToolBarHelper::deleteList("Are you sure you want to delete?", 'remove' , "Delete");
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				$listkeywords=$this->get('data');
				$pagination=$this->get('pagination');
				
				$filter			=JRequest::getVar('rs_filter');
				$importance_filter = JRequest::getVar('rs_importance_filter');
				$location_filter=JRequest::getVar('rs_loc_filter');
				$sortColumn		=JRequest::getVar('filter_order','KeywordImportance');
				$sortOrder	=JRequest::getVar('filter_order_Dir','desc');
				
				
				
				$importancelist[]	= JHTML::_('select.option',  '0', JText::_( 'RSSEO_SELECT_IMPORTANCE' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'low', JText::_( 'RSSEO_KEYWORDS_LOW' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'relevant', JText::_( 'RSSEO_KEYWORDS_RELEVANT' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'important', JText::_( 'RSSEO_KEYWORDS_IMPORTANT' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'critical', JText::_( 'RSSEO_KEYWORDS_CRITICAL' ), 'val', 'text' );
				
				$lists['importance']      = JHTML::_('select.genericlist', $importancelist, 'rs_importance_filter', 'class="inputbox" size="1" onchange="submitbutton(\'listkeywords\');"','val', 'text', $importance_filter  );
				
				
				$this->assignRef('location_filter',$category_filter);
				$this->assignRef('importance_filter',$importance_filter);
				$this->assignRef('lists'      	, $lists);
				$this->assignRef('filter',$filter);
				$this->assignRef('sortColumn',$sortColumn);
				$this->assignRef('sortOrder',$sortOrder);
				$this->assignRef('listkeywords',$listkeywords);
				$this->assignRef('rsseoConfig',$app->getuserState('rsseoConfig'));
				$this->assignRef('pagination',$pagination);

			} break;
			
			case 'addmultikeywords':
			case 'editkeyword':
			{
				
				$db=& JFactory::getDBO();
				$cid=JRequest::getVar('cid',array(0),'request','array');
				if(is_array($cid)) $cid=intval($cid[0]);
				if($cid==0)
					JToolBarHelper::title(JText::_('RSSEO_KEYWORD_NEW'),'rsseo');
				else 
					JToolBarHelper::title(JText::_('RSSEO_KEYWORD_EDIT'),'rsseo');
					
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				
				//get keyword details
				$keyword=$this->get('Keyword');
				
				//load keyword importance..
				$importancelist[]	= JHTML::_('select.option',  'low', JText::_( 'RSSEO_KEYWORDS_LOW' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'relevant', JText::_( 'RSSEO_KEYWORDS_RELEVANT' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'important', JText::_( 'RSSEO_KEYWORDS_IMPORTANT' ), 'val', 'text' );
				$importancelist[]	= JHTML::_('select.option',  'critical', JText::_( 'RSSEO_KEYWORDS_CRITICAL' ), 'val', 'text' );
				
				$lists['importance'] = JHTML::_('select.genericlist', $importancelist, 'KeywordImportance', 'class="inputbox" size="1"','val', 'text', $keyword->KeywordImportance  );
				
				//get keyword bold
				$boldOptions[] = JHTML::_( 'select.option', '0', JText::_('RSSEO_NO_BOLD') );
				$boldOptions[] = JHTML::_( 'select.option', '1', JText::_('RSSEO_BOLD_STRONG') );
				$boldOptions[] = JHTML::_( 'select.option', '2', JText::_('RSSEO_BOLD_B') );
					  
				$lists['bold'] = JHTML::_('select.radiolist', $boldOptions, 'KeywordBold', 'class="inputbox" ', 'value', 'text', ($cid) ? $keyword->KeywordBold : '0' );
				
				
				$this->assignRef('lists',$lists);
				$this->assignRef('data',$keyword);
				$this->assignRef('task',$task);

			} break;
		}
		
		parent::display($tpl);
	}
}		
?>