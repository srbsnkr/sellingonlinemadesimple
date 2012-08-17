<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );

class rsseoModelpages extends JModel
{
	var $_query;
	var $_data;
	var $_total=null;
	var $_pagination=null;
	var $_componentList=null;
	function _buildQuery()
	{
		$app =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$filter = $app->getUserStateFromRequest('com_rsseo.pages.rs_filter', 'rs_filter', '');
		
		$md5_descr = JRequest::getVar('md5_descr');
		$md5_title = JRequest::getVar('md5_title');
		$page_level_filter=JRequest::getVar('rs_page_level_filter');
		
		if($page_level_filter!=null)
			$page_query=" AND PageLevel = ".intval($page_level_filter)." ";
		else
			$page_query="";
		
		if(!empty($md5_descr))
			$descr_filter = " AND MD5(PageDescription) = '".$db->getEscaped($md5_descr)."' AND published = 1 ";
		else
			$descr_filter = "";

		if(!empty($md5_title))
			$title_filter = " AND MD5(PageTitle) = '".$db->getEscaped($md5_title)."' AND published = 1 ";
		else
			$title_filter = "";
		
		$status_filter=JRequest::getVar('rs_status_filter');
		if($status_filter!=null)
			$status_query="AND published = '".$db->getEscaped($status_filter)."'";
		else
			$status_query="";
		
		$sortOrder=JRequest::getVar('filter_order_Dir','asc');
		$sortColumn=JRequest::getVar('filter_order','PageLevel');
		
		$app->setUserState('com_rsseo.pages.rs_filter',$filter);
		$app->setUserState('com_rsseo.pages.sortColumn',$sortColumn);
		$app->setUserState('com_rsseo.pages.sortOrder',$sortOrder);
		
		
		
		$this->_query="SELECT IdPage, PageURL, PageTitle, PageKeywords, PageDescription, PageLevel, PageInSitemap , PageModified , published, PageCrawled, DatePageCrawled, PageGrade "
						."FROM #__rsseo_pages "
						."WHERE (PageURL LIKE '%".$db->getEscaped($filter)."%' OR PageTitle LIKE '%".$db->getEscaped($filter)."%') $status_query $page_query $descr_filter $title_filter"
						."ORDER BY $sortColumn $sortOrder ";
		//echo $this->_query;
	}
	function __construct()
	{	
		parent::__construct();
		$this->_buildQuery();
		$app =& JFactory::getApplication();

		// Get pagination request variables
		$limit = $app->getUserStateFromRequest('com_rsseo.pages.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest('com_rsseo.pages.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('com_rsseo.pages.limit', $limit);
		$this->setState('com_rsseo.pages.limitstart', $limitstart);
		

		//echo $this->_db->_errorMsg;
	}
	function getData()
	{
		if (empty($this->_data))
			$this->_data=$this->_getList($this->_query,$this->getState('com_rsseo.pages.limitstart'), $this->getState('com_rsseo.pages.limit'));
		
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
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('com_rsseo.pages.limitstart'), $this->getState('com_rsseo.pages.limit'));
		}
		return $this->_pagination;
	}
	
	function getPage()
	{
		$cid= JRequest::getVar('cid',0,'request');
		if(is_array($cid)) $cid=$cid[0];
		$row= & JTable::getInstance('rsseo_pages','Table');
		$row->load($cid);
		
		return $row;
	}
	
	function getDetails()
	{
		require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'rsseo.php');
		include(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'class.webpagesize.php');
		$cid = intval(JRequest::getVar('cid',0,'request'));
		
		$row= & JTable::getInstance('rsseo_pages','Table');
		$row->load($cid);
		
		set_time_limit(100);
		$size = new WebpageSize;
		$size->setURL(JURI::root().$row->PageURL);
		$result = $size->printResult();
		
		return $result;
	}
	
}