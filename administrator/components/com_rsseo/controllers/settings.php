<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllersettings extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask('apply' ,  'save');
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('settings');
		$app =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$post = JRequest::get('post',JREQUEST_ALLOWRAW);
		$rsseoConfigPost = $post['rsseoConfig'];
		
		$db->setQuery("SELECT * FROM `#__rsseo_config`");
		$rsseoConfigDb = $db->loadObjectList();
		foreach ($rsseoConfigDb as $objConfig)
		{  
			if(isset($rsseoConfigPost[$objConfig->ConfigName]))
			{
				$db->setQuery("UPDATE #__rsseo_config SET ConfigValue='".$db->getEscaped($rsseoConfigPost[$objConfig->ConfigName])."' WHERE ConfigName='".$objConfig->ConfigName."'");
				$db->query();
				$rsseoConfig[$objConfig->ConfigName] = $rsseoConfigPost[$objConfig->ConfigName];
			}
		} 
		$app->setUserState('rsseoConfig',$rsseoConfig);
		$msg = JText::_('RSSEO_SETTINGS_SAVE');
		$tabposition = JRequest::getInt('tabposition', 0);
		
		if ($post['copykeywords'])
		{
			$condition = isset($post['overwritekeywords']) ? "" : " AND `PageKeywordsDensity` = '' ";
			$db->setQuery("UPDATE `#__rsseo_pages` SET `PageKeywordsDensity` = `PageKeywords` WHERE 1=1 ".$condition." ");
			$db->query();
		}
		
		
		switch(JRequest::getCmd('task'))
		{
			case 'apply' :
				$link = 'index.php?option=com_rsseo&task=editsettings&tabposition='.$tabposition;
			break;
			
			case 'save' :
				$link = 'index.php?option=com_rsseo';
			break;
		}
		
		
		$this->setRedirect($link, $msg);
	}
	
	
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_rsseo');
	}
	
}