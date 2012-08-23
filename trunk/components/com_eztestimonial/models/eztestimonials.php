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

class testimonialModeleztestimonials extends JModel

{

    var $_data;

	var $_total = null;

	var $_pagination = null;

	

    function _buildQuery()

    {

		$db				=& JFactory::getDBO();

		$myparams 		= &JComponentHelper::getParams('com_eztestimonial');

		$sorting		= $myparams->getValue('data.params.sorting','DESC');

		$sortby			= $myparams->getValue('data.params.sortby','id');

		$mainframe 		=& JFactory::getApplication();

		$orderCol   	= JRequest::getCmd('filter_order', $sortby	);

		$this->setState('list.ordering', $orderCol);

		$listOrder   	=  JRequest::getCmd('filter_order_Dir',$sorting);

		$this->setState('list.direction', $listOrder);

		$orderCol   	= JRequest::getCmd('filter_order', $sortby	);

		

		/* chk versions */

		  $search	= $mainframe->getUserStateFromRequest( "search", 'search', '','string', true);

		/* chk versions end */	



		if (isset( $search ) && strlen($search)> 0)

		{

			$searchEscaped = '"%'.$db->getEscaped( $search, true ).'%"';

			$where = 'WHERE fullName LIKE '.$searchEscaped.' OR message_summary LIKE '.$searchEscaped.' AND approved=1';

		}else{

			$where ='WHERE approved=1';

		}

	



	$query = "SELECT *

	FROM #__testimonials ".$where." ORDER BY ".$orderCol." ".$listOrder;

      // JError::raiseError(500, JText::_($query, true ) ); 

	   return $query;

    }

   

   function __construct()

	  {

		parent::__construct();

	 

		$mainframe = JFactory::getApplication();

	 

		// Get pagination request variables

		$limit = 4;//$mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');

		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	 

		// In case limit has been changed, adjust it

		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);

		$this->setState('limitstart', $limitstart);

	  }



	  function getData() 

	  {

		// if data hasn't already been obtained, load it

		if (empty($this->_data)) {

			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));	

		}

		return $this->_data;

	  }

	  

	   function getTotal()

	  {

		// Load the content if it doesn't already exist

		if (empty($this->_total)) {

			$query = $this->_buildQuery();

			$this->_total = $this->_getListCount($query);	

		}

		return $this->_total;

	  }

	   function getPagination()

		  {

			// Load the content if it doesn't already exist

			if (empty($this->_pagination)) {

				jimport('joomla.html.pagination');

				$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );

			}

			return $this->_pagination;

		  }

		  function getcrdt()

		  {
		$myparams 		= &JComponentHelper::getParams('com_eztestimonial');
		$rmvcd 			= md5($myparams->getValue('data.params.brandingremoval'));

				if('a32ed01babf3c9be85201d9307758151' != $rmvcd)
				{

		  	return '<div align="center" style="font-size:11px;"><a href="http://www.saaraan.com" target="_new" />Powered by Saaraan</a></div>';
			}

		  }

}