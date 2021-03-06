<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );

class rsseoModelkeywords extends JModel
{
	var $_query;
	var $_data;
	var $_total=null;
	var $_pagination=null;
	var $_componentList=null;
	function _buildQuery()
	{
		$db =& JFactory::getDBO();
		
		$filter=JRequest::getVar('rs_filter');
		$importance_filter=JRequest::getVar('rs_importance_filter');
		
		if($importance_filter)
			$imp_filter_query="AND KeywordImportance = '".$db->getEscaped($importance_filter)."'";
		else
			$imp_filter_query="";
		
		
		$sortOrder=JRequest::getVar('filter_order_Dir','desc');
		$sortColumn=JRequest::getVar('filter_order','KeywordImportance');
		
		$this->_query="SELECT IdKeyword, Keyword, KeywordImportance , ActualKeywordPosition, LastKeywordPosition, DateRefreshed "
						."FROM #__rsseo_keywords  "
						."WHERE Keyword LIKE '%".$db->getEscaped($filter)."%' $imp_filter_query "
						."ORDER BY $sortColumn  $sortOrder ";
		
		//echo $this->_query;
	}
	function __construct()
	{	
		parent::__construct();
		$this->_buildQuery();
		$app =& JFactory::getApplication();

		// Get pagination request variables
		$limit = $app->getUserStateFromRequest('com_rsseo.keywords.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest('com_rsseo.keywords.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('com_rsseo.keywords.limit', $limit);
		$this->setState('com_rsseo.keywords.limitstart', $limitstart);
		

		//echo $this->_db->_errorMsg;
	}
		
	function getData()
	{
		if (empty($this->_data))
			$this->_data=$this->_getList($this->_query,$this->getState('com_rsseo.keywords.limitstart'), $this->getState('com_rsseo.keywords.limit'));
		
		return $this->_data;
	}
	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total))
			$this->_total = $this->_getListCount($this->_query);	
		
		return $this->_total;
	}
	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('com_rsseo.keywords.limitstart'), $this->getState('com_rsseo.keywords.limit'));
		}
		return $this->_pagination;
	}
	
	function getKeyword()
	{
		$cid= JRequest::getVar('cid',0,'request');
		if(is_array($cid)) $cid=intval($cid[0]);
		$row= & JTable::getInstance('rsseo_keywords','Table');
		$row->load($cid);
		
		return $row;
	}
}