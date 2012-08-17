<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class rsseoController extends JController
{	
	var $_params;
	
	function __construct()
	{
		parent::__construct();
		
		$db = & JFactory::getDBO();
		$app = & JFactory::getApplication();
		$this->_params=& JComponentHelper::getParams('com_rsseo');
		
		$db->setQuery("SELECT * FROM `#__rsseo_config`");
		$rsseoConfigDb = $db->loadObjectList();
		
		$rsseoConfig = array();
		foreach ($rsseoConfigDb as $rowConfig)
		{
			$rsseoConfig[$rowConfig->ConfigName] = $rowConfig->ConfigValue;
		}
		//sets the rsseoConfig object in to the session
		$app->setuserState('rsseoConfig',$rsseoConfig);
		
		
	}
	
	
	function saveRegistration()
	{
		$db = JFactory::getDBO();
		$rsseoConfigPost = JRequest::getVar('rsseoConfig','post');
		if(!isset($rsseoConfigPost['global.register.code']))$rsseoConfigPost['global.register.code']='';

		if($rsseoConfigPost['global.register.code']=='')
		{
		$msg=JText::_('RSSEO_REGISTRATION_CODE');
		$this->setRedirect('index.php?option=com_rsseo',$msg);
		return;
		}

		$db->setQuery("UPDATE `#__rsseo_config` SET ConfigValue = '". trim( $rsseoConfigPost['global.register.code'] ). "' WHERE ConfigName = 'global.register.code'");
		$db->query();

		$msg=JText::_('RSSEO_REGISTRATION_SAVED');
		$this->setRedirect('index.php?option=com_rsseo&task=update',$msg);
	}
	

//KEYWORDS	
	function listkeywords()
	{
		JRequest::setVar('view','keywords');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editkeyword()
	{
		JRequest::setVar('view','keywords');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	function addmultikeywords()
	{
		JRequest::setVar('view','keywords');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
//PAGES
	function listpages()
	{
		JRequest::setVar('view','pages');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editpage()
	{
		JRequest::setVar('view','pages');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function showdetails()
	{
		JRequest::setVar('view','pages');
		JRequest::setVar('layout','details');
		parent::display();
	}

//COMPETITORS	
	function listcompetitors()
	{
		JRequest::setVar('view','competitors');
		JRequest::setVar('layout','default');
		parent::display();
	}

	function listcompetitorshistory()
	{
		JRequest::setVar('view','competitors');
		JRequest::setVar('layout','history');
		parent::display();
	}
	function editcompetitor()
	{
		JRequest::setVar('view','competitors');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
//REDIRECTS	
	function listredirects()
	{
		JRequest::setVar('view','redirects');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editredirect()
	{
		JRequest::setVar('view','redirects');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
//SITEMAP	
	function sitemap()
	{
		JRequest::setVar('view','sitemap');
		JRequest::setVar('layout','default');
		parent::display();
	}

//CRAWLER
	function crawler()
	{
		JRequest::setVar('view','crawler');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	
	function rsseo()
	{
		JRequest::setVar('view','rsseo');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
//SETTINGS
	function editsettings()
	{
		JRequest::setVar('view','settings');
		JRequest::setVar('layout','default');
		parent::display();
	}

//UPDATE
	function update()
	{
		JRequest::setVar('view','rsseo');
		JRequest::setVar('layout','update');
		parent::display();
	}
	
//BACKUP
	
	function backuprestore()
	{
		JRequest::setVar('view','backuprestore');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function backup()
	{
		JRequest::setVar('view','backuprestore');
		JRequest::setVar('layout','backup');
		parent::display();
	}
	
	function restore()
	{
		JRequest::setVar('view','backuprestore');
		JRequest::setVar('layout','restore');
		parent::display();
	}
	
	
	function redirectrestore()
	{
		jimport('joomla.filesystem.folder');
		$tmp_folder = base64_decode(JRequest::getVar('delfolder','','request'));
		if (JFolder::exists($tmp_folder))
			JFolder::delete($tmp_folder);
		$this->setRedirect('index.php?option=com_rsseo&task=backuprestore',JText::_('RSSEO_RESTORE_COMPLETE'));
	}
	
	function checkconnections()
	{
		$url = JURI::root();
		$result = rsseoHelper::checkconnections($url);
		
		if(empty($result->err) && !empty($result->ok))
		{
			$ok = implode(',',$result->ok);
			JError::raiseNotice(200, 'Connection with "'.$ok.'" successfully');
		} 
		
		if(empty($result->ok) && !empty($result->err))
		{
			JError::raiseWarning(500, 'Your server does not accept loopback connections.Click <a href="http://www.rsjoomla.com/customer-support/documentations/73-troubleshooting/417-how-can-you-use-rsseo-without-loopback-connections.html" target="_blank">here</a> for more informations about this problem.');
		}
		
		if(!empty($result->err))
		{
			$err = implode(',',$result->err);
			$ok = implode(',',$result->ok);
			JError::raiseWarning(500, 'Could not connect using "'.$err.'" but could connect with "'.$ok.'"');
		}
		
		echo 'Click <a href="index.php?option=com_rsseo">here</a> to go to RSSeo! Control Panel';
	}
	
	function analytics()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function gageneral()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','general');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}
	
	function ganewreturning()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','newreturning');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}
	
	function gavisits()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','visits');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}
	
	function gabrowsers()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','browsers');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}
	
	function gamobiles()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','mobiles');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}
	
	function gasources()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','sources');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}
	
	function gacontent()
	{
		JRequest::setVar('view','analytics');
		JRequest::setVar('layout','content');
		JRequest::setVar('tmpl','component');
		parent::display();
		exit();
	}

	function gaaccount()
	{
		JRequest::setVar('tmpl','component');
		$db = & JFactory::getDBO();
		$account = JRequest::getVar('account');
		$start = JRequest::getVar('start');
		$end = JRequest::getVar('end');
		
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($account)."' WHERE ConfigName = 'ga.account' ");
		$db->query();
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($start)."' WHERE ConfigName = 'ga.start' ");
		$db->query();
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($end)."' WHERE ConfigName = 'ga.end' ");
		$db->query();
		return '';
		exit();
	}

	function display()
	{
		parent::display();
	}

}
?>