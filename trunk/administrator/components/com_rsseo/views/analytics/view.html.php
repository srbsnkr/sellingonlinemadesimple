<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsseoViewAnalytics extends JView
{
	function display($tpl = null)
	{
		$db =& JFactory::getDBO();
		$app =& JFactory::getApplication();
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics',true);
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		if (!extension_loaded('curl'))
			$app->redirect('index.php?option=com_rsseo',JText::_('RSSEO_NO_CURL'));
		
		$task=JRequest::getVar('task','','request');
		switch($task)
		{
			case 'analytics':
			{
				JToolBarHelper::title(JText::_('RSSEO_ANALYTICS_TITLE'),'rsseo');
				
				JHTML::_('behavior.tooltip');
				jimport('joomla.html.pane');
				$tabs =& JPane::getInstance('Tabs',array(),true);
				
				$config = $app->getuserState('rsseoConfig');
				
				if (trim($config['analytics.username']) == '' || trim($config['analytics.password']) == '' || $config['analytics.enable'] == 0)
					$app->redirect('index.php?option=com_rsseo&task=editsettings&tabposition=2',JText::_('RSSEO_GA_ERROR'));
				
				$accounts = $this->get('Accounts');
				if (!empty($accounts))
				{
					foreach($accounts as $account)
						$acc[]	= JHTML::_('select.option', $account->getProfileId(), $account->getTitle());
				} else $acc[]	= JHTML::_('select.option', 0, JText::_('RSSEO_NO_ANALYTICS_ACCOUNTS'));
				
				$current = time();
				$startDate = date('Y-m-d',$current - 604800);
				$endDate = date('Y-m-d',$current - 86400);
				$start = empty($config['ga.start']) ? $startDate : $config['ga.start'];
				$end = empty($config['ga.end']) ? $endDate : $config['ga.end'];
				
				$lists['accounts']  = JHTML::_('select.genericlist', $acc, 'account', 'class="inputbox" size="1" ','value', 'text',$config['ga.account']);
				$lists['start']		= JHTML::_('calendar', $start, "rssestart", "rssestart");
				$lists['end']		= JHTML::_('calendar', $end, "rsseend", "rsseend");
				
				$visits = $this->get('GAVisits');
				$this->assignRef('visits',$visits);
				$sources = $this->get('GASources');
				$this->assignRef('sources',$sources);
				
				$this->assignRef('lists',$lists);
				$this->assignRef('tabs',$tabs);
				$this->assignRef('accounts',$accounts);
				$this->assignRef('config',$config);
				
			} break;
			
			case 'gageneral':
				$general = $this->get('GAgeneral');
				$this->assignRef('general',$general);
			break;
			
			case 'ganewreturning':
				$newreturning = $this->get('GANewReturning');
				$this->assignRef('newreturning',$newreturning);
			break;
			
			case 'gavisits':
				$visits = $this->get('GAVisits');
				$this->assignRef('visits',$visits);
			break;
			
			case 'gabrowsers':
				$browsers = $this->get('GABrowsers');
				$this->assignRef('browsers',$browsers);
			break;
			
			case 'gamobiles':
				$mobiles = $this->get('GAMobiles');
				$this->assignRef('mobiles',$mobiles);
			break;
			
			case 'gasources':
				$sources = $this->get('GASources');
				$this->assignRef('sources',$sources);
			break;
			
			case 'gacontent':
				$content = $this->get('GAContent');
				$this->assignRef('content',$content);
			break;
			
			
		}
		
		parent::display($tpl);
	}
}