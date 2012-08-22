<?php
/**
* @package 		ezTestimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

jimport( 'joomla.application.component.model' );
class AdminTestimonModeleztestimonials extends JModel
{
    var $_data;
	var $_total = null;
	var $_pagination = null;
	
    function _buildQuery()
    {
		$db				=& JFactory::getDBO();
		$mainframe =& JFactory::getApplication();
		$orderCol   = JRequest::getCmd('filter_order', 'id');
		$this->setState('list.ordering', $orderCol);
		$listOrder   =  JRequest::getCmd('filter_order_Dir', 'DESC');
		$this->setState('list.direction', $listOrder);
		$orderCol   = JRequest::getCmd('filter_order', 'id');
		$search	= $mainframe->getUserStateFromRequest( "search", 'search', '','string', true);
		if (isset( $search ) && strlen($search)> 0)
		{
			$searchEscaped = '"%'.$db->getEscaped( $search, true ).'%"';
			$where = 'WHERE fullName LIKE '.$searchEscaped.' OR message_summary LIKE '.$searchEscaped.' AND approved=1';
		}else{
			$where ='';
		}
	

	$query = "SELECT *
	FROM #__testimonials ".$where." ORDER BY ".$orderCol." ".$listOrder;
	   return $query;
    }
   
   function __construct()
	  {
		parent::__construct();
	 
		$mainframe = JFactory::getApplication();
	 
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
	 
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	  }

	  function getData() 
	  {
		if (empty($this->_data)) {
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));	
		}
		return $this->_data;
	  }
	  
	   function getTotal()
	  {
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);	
		}
		return $this->_total;
	  }
	   function getPagination()
		  {
			if (empty($this->_pagination)) {
				jimport('joomla.html.pagination');
				$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
			}
			return $this->_pagination;
		  }

}