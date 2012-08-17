<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );

class rsseoModelCompetitors extends JModel
{
	var $_query;
	var $_data;
	var $_total=null;
	var $_pagination=null;
	var $_componentList=null;
	function _buildQuery()
	{
		$db =& JFactory::getDBO();
		$filter = JRequest::getVar('rs_filter');
		
		$sortOrder=JRequest::getVar('filter_order_Dir','asc');
		$sortColumn=JRequest::getVar('filter_order','c.ordering');
		
		$this->_query="SELECT c.IdCompetitor, c.Competitor, c.LastPageRank, c.LastAlexaRank, c.LastTehnoratiRank , c.LastGooglePages, c.LastYahooPages, c.LastBingPages, c.LastGoogleBacklinks, c.LastYahooBacklinks, c.LastBingBacklinks,c.Dmoz, c.LastDateRefreshed, c.ordering "
						." FROM #__rsseo_competitors c "
						." WHERE 1=1 AND (c.Competitor LIKE '%".$db->getEscaped($filter)."%' "
						." OR c.Tags LIKE '%".$db->getEscaped($filter)."%') "
						." ORDER BY $sortColumn $sortOrder ";
		//echo $this->_query;
	}
	function __construct()
	{	
		parent::__construct();
		$this->_buildQuery();

		$app =& JFactory::getApplication();
		
		// Get pagination request variables
		$limit = $app->getUserStateFromRequest('com_rsseo.competitors.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest('com_rsseo.competitors.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('com_rsseo.competitors.limit', $limit);
		$this->setState('com_rsseo.competitors.limitstart', $limitstart);
		

		//echo $this->_db->_errorMsg;
	}
	function getData()
	{
		if (empty($this->_data))
			$this->_data=$this->_getList($this->_query,$this->getState('com_rsseo.competitors.limitstart'), $this->getState('com_rsseo.competitors.limit'));
		
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
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('com_rsseo.competitors.limitstart'), $this->getState('com_rsseo.competitors.limit'));
		}
		return $this->_pagination;
	}
	
	function getCompetitorsHistory()
	{
		$cid= JRequest::getVar('cid',0,'request');
		if(is_array($cid)) $cid=$cid[0];
		
		$sortOrder=JRequest::getVar('filter_order_Dir','desc');
		$sortColumn=JRequest::getVar('filter_order','ch.DateRefreshed');
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT ch.* FROM #__rsseo_competitors_history ch WHERE ch.IdCompetitor = ".$cid." ORDER BY ".$sortColumn." ".$sortOrder);
		$competitorHistory = $db->loadObjectList();
		
		return $competitorHistory;
	}
	
	function getCompetitor()
	{
		$cid = JRequest::getVar('cid',0,'request');
		if(is_array($cid)) $cid=$cid[0];
		
		$row= & JTable::getInstance('rsseo_competitors','Table');
		$row->load($cid);
		return $row;
	}
	 
}