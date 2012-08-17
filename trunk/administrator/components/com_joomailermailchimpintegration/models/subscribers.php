<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.application.component.model' );

class joomailermailchimpintegrationsModelSubscribers extends JModel
{

    var $_data;
    var $_total = null;
    var $_pagination = null;

    function __construct()
    {
	parent::__construct();

	$mainframe =& JFactory::getApplication();
	$option = JRequest::getCmd('option');

	// Get pagination request variables
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	// In case limit has been changed, adjust it
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);
    }


    function _buildQuery()
    {
	$mainframe   =& JFactory::getApplication();
	$db	     =& JFactory::getDBO();
	$filter_type = $mainframe->getUserStateFromRequest( "filter_type",		'filter_type', 		0,			'string' );
	$search	     = $mainframe->getUserStateFromRequest( "search",	        'search', 			'',			'string' );
	$search	     = JString::strtolower( $search );

	$limit	     = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart  = $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

	if (isset( $search ) && $search!= '')
	{
	    $searchEscaped = '"%'.$db->getEscaped( $search, true ).'%"';
	    $where[] = ' username LIKE '.$searchEscaped.' OR email LIKE '.$searchEscaped.' OR name LIKE '.$searchEscaped;
	}

	if ( $filter_type )
	{
	    if ( $filter_type == 'Public Frontend' )
	    {
		$where[] = ' usertype = \'Registered\' OR usertype = \'Author\' OR usertype = \'Editor\' OR usertype = \'Publisher\' ';
	    }
	    else if ( $filter_type == 'Public Backend' )
	    {
		$where[] = ' usertype = \'Manager\' OR usertype = \'Administrator\' OR usertype = \'Super Administrator\' ';
	    }
	    else
	    {
		$where[] = ' usertype = LOWER( '.$db->Quote($filter_type).' ) ';
	    }
	}

	$where[] = " block = '0' ";

	$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

	$query = ' SELECT id, name, username, email, block, usertype FROM #__users '. $where .' ORDER BY id';

	return $query;
    }

    function getData()
    {
	if (empty( $this->_data ))
	{

            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}

	return $this->_data;
    }

    function getUser($id){
         $query = 'SELECT id,name,username,email,block,usertype FROM #__users WHERE id ='.$id;
         $this->_data = $this->_getList( $query );
         return $this->_data;
    }

    function getJUser($email)
    {
	$db  =& JFactory::getDBO();
	$query = "SELECT id FROM #__users WHERE `email` = '".$email."'";
	$db->setQuery($query);
	$id = $db->loadResult();

	if($id){
	    $user =& JFactory::getUser($id);
	} else {
	    $user = new stdClass;
	    $user->id = '';
	    $user->name = '';
	    $user->email = '';
	}

	return $user;
    }

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }
    

    function getSubscribed(){
	$query = 'SELECT * FROM #__joomailermailchimpintegration';
	$this->_data = $this->_getList( $query );
	return $this->_data;
    }
	
    function getUsers(){
	$query = 'SELECT * FROM #__users';
	$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
	return $this->_data;
    }
    
    function getActive()
    {
	$MC     = $this->MC_object();
	$listid = JRequest::getVar('listid',  0, '', 'string');
	$type   = JRequest::getVar('type',  's', '', 'string');

	$mainframe  =& JFactory::getApplication();
	$option = JRequest::getCmd('option');

	$limit	    = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

	if($limit==0){
	    $limit = 15000; $limitstart = 0;
	} else {
	    $limitstart = round($limitstart / $limit, 0);
	}

	switch($type){
	    case 's':
		$result = $MC->listMembers( $listid, 'subscribed', '', $limitstart, $limit);
		break;
	    case 'u':
		$result = $MC->listMembers( $listid, 'unsubscribed', '', $limitstart, $limit);
		break;
	    case 'c':
		$result = $MC->listMembers( $listid, 'cleaned', '', $limitstart, $limit);
		break;
	}

	if($result){
	    return $result;
	} else {
	    return false;
	}
    }
	
    function getUserDetails($email,$list){

	$MC     = $this->MC_object();
	$result = $MC->listMemberInfo( $list, $email);
	return $result;
    }
	
    function getListDetails()
    {
	$MC      = $this->MC_object();
	$details = $MC->lists();

	return $details;
    }


    function getTotal()
    {
        $listId = JRequest::getVar('listid',  0, '', 'string');
        $type   = JRequest::getVar('type',  's', '', 'string');
        
        $lists = $this->getListDetails();
        foreach($lists as $list){
	    if( $list['id'] == $listId ) {
		switch($type){
		    case 's':
			$total = $list['member_count'];
			break;
		    case 'u':
			$total = $list['unsubscribe_count'];
			break;
		    case 'c':
			$total = $list['cleaned_count'];
			break;
		}
		break;
	    }
	}

        return $total;
    }

    function getPagination()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
	    $mainframe =& JFactory::getApplication();
	    $option = JRequest::getCmd('option');
	    $limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	    $limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
	    if($limit==0){ $limit = 15000; }
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }

        return $this->_pagination;
    }

}
