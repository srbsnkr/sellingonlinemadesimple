<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );

class rsseoModelsettings extends JModel
{
	var $_query;
	var $_data;
	var $_total=null;
	var $_pagination=null;
	var $_componentList=null;
	
	function _buildQuery()
	{	
		$this->_query="SELECT IdConfig as RecordId, ConfigName , ConfigValue FROM #__rsseo_config";
		//echo $this->_query;
	}
	
	function __construct()
	{	
		parent::__construct();
		$this->_buildQuery();
	}
	
	function getData()
	{
		if (empty($this->_data))
			$this->_data=$this->_getList($this->_query);
		
		$rsseoConfig = array();
		foreach($this->_data as $i => $objConfig)
			$rsseoConfig[$objConfig->ConfigName] = $objConfig->ConfigValue;
		
		return $rsseoConfig;
	}
	
	function getConfig()
	{
		$cid= JRequest::getVar('cid',0,'request');
		if(is_array($cid)) $cid=$cid[0];
		$row= & JTable::getInstance('rsseo_settings','Table');
		$row->load($cid);
		return $row;
	}
	
 
}