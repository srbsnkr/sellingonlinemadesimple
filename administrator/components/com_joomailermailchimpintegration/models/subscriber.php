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

class joomailermailchimpintegrationsModelSubscriber extends JModel
{

    var $_data;
    var $_total = null;
    var $_pagination = null;

    function __construct()
    {
        parent::__construct();

        $mainframe =& JFactory::getApplication();

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
  }

    /**
	* Retrieves the data
	* @return an Array of objects containing the data
	*/
	function _buildQuery()
	{
        $mainframe =& JFactory::getApplication();
        $db	=& JFactory::getDBO();
        $filter_type = $mainframe->getUserStateFromRequest( "filter_type",		'filter_type', 		0,			'string' );
        $search		 = $mainframe->getUserStateFromRequest( "search",	        'search', 			'',			'string' );
		$search		 = JString::strtolower( $search );

        $limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	    $limitstart	= $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

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

	/**
	 * Retrieves the data
	 * @return an Array of objects containing the data
	 */
	function getData()
	{
        // Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
            //$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	        //$this->_data = $db->loadObjectList();

			//$this->_data = $this->_getList( $query );
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

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

	function getClientDetails()
	{
		$MC	= $this->MC_object();
		$details = $MC->getAccountDetails();
		return $details;
	}
	
	function getListsForEmail(){
		
		$MC    = $this->MC_object();
		$email = JRequest::getVar('email',  0, '', 'string', JREQUEST_ALLOWRAW);
		$listsForEmail = $MC->listsForEmail( $email );
		
		$lists = $MC->lists();
		foreach($lists as $list){
			if( in_array($list['id'], $listsForEmail)){

				$memberInfo = $MC->listMemberInfo( $list['id'], $email );
				$listsArray = $memberInfo;
				$listsArray['lists'][$list['id']] = array(
						'id' => $list['id'],
						'name' => $list['name'], 
						'member_count' => $list['member_count'],
						'member_rating' => $memberInfo['member_rating']			
						);
			}
		}

		return $listsArray;
	}
	function getSubscribed(){
		$query = 'SELECT * FROM #__joomailermailchimpintegration';
		$this->_data = $this->_getList( $query );
		return $this->_data;
	}
	
	function getUsers(){
		$query = 'SELECT * FROM #__users';
		$this->_data = $this->_getList( $query );
		return $this->_data;
	}
    
    function getActive(){
		$MC     = $this->MC_object();
		$listid = JRequest::getVar('listid',  0, '', 'string');
		$type   = JRequest::getVar('type',  's', '', 'string');
		
		$mainframe =& JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
//		var_dump($limitstart);var_dump($limit);
		if($limit==0){ 
			$limit = 15000; $limitstart = 0; 
		} else {
			$limitstart = round($limitstart / $limit, 0);
		}
//var_dump($limitstart);var_dump($limit);
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
	
	function getListDetails()
	{
		$MC      = $this->MC_object();
		$details = $MC->lists();

        return $details;
	}

	function campaignEmailStatsAIM($listId, $email){
		
		$MC      = $this->MC_object();
		$result = $MC->campaignEmailStatsAIM($listId, $email);

		return $result;
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
			$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
			$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
			if($limit==0){ $limit = 15000; }
            jimport('joomla.html.pagination');
            var_dump($this->getTotal());die;
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
    }

    function getMemberInfo($id,$email)
    {

		$mc = $this->MC_object();
		$results = $mc->listMemberInfo($id,$email);

		return $results;

    }

    function getHardBounces($cid) {

        $mc = $this->MC_object();
        $results = $mc->campaignHardBounces($cid);

        return $results;

    }


    function getSoftBounces($cid) {

        $mc = $this->MC_object();
        $results = $mc->campaignSoftBounces($cid);

        return $results;

    }
    
    function getAmbraPayments() {
    	$userId = JRequest::getInt('uid');
    	
    	$db = & JFactory::getDBO();
    	$query = 'SELECT u.created_datetime, t.title, t.value as price'
				. ' FROM ' . $db->nameQuote('#__ambrasubs_users2types') . ' AS u '
				. ' LEFT JOIN ' . $db->nameQuote('#__ambrasubs_types') . ' AS t '
				. ' ON u.typeid = t.id'
				. ' WHERE u.userid = '. $db->Quote($userId);

		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		return $results;
    }
	
	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param boole $img True to return a complete IMG tag False for just the URL
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	public function getGravatar($default = '', $img = false, $s = 155, $d = 'mm', $r = 'g', $atts = array()) {
		$email = JRequest::getVar('email');
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if($default) {
			$url .= '&amp;default='.urlencode($default);
		}
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
		return $url;
	}
	
	public function getJomSocialGroups() {
		$userId = JRequest::getInt('uid');
		$db = JFactory::getDBO();
		
		if($this->isJomSocialInstalled()) {
			$query = 'SELECT g.id, g.name  FROM ' . $db->nameQuote('#__community_groups_members') . ' AS m'
					. ' LEFT JOIN ' . $db->nameQuote('#__community_groups') . ' AS g'
					. ' ON m.groupid = g.id '
					. ' WHERE m.memberid = ' . $userId;
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		return '';
	}
	
	public function getRecentJomSocialDiscussions() {
		$userId = JRequest::getInt('uid');
		$db = JFactory::getDBO();
		
		if($this->isJomSocialInstalled()) {
			$query = 'SELECT id, title, groupid FROM ' . $db->nameQuote('#__community_groups_discuss')
					. ' WHERE creator = ' . $db->Quote($userId)
					. ' ORDER BY created DESC';

			$db->setQuery($query, 0, 5);
			return $db->loadObjectList();
		}
		return '';
	}
	
	public function getTotalJomSocialDiscussionsOfUser() {
		$userId = JRequest::getInt('uid');
		$db = JFactory::getDBO();
		
		if($this->isJomSocialInstalled()) {
			$query = 'SELECT COUNT(*) as count FROM ' . $db->nameQuote('#__community_groups_discuss')
					. ' WHERE creator = ' . $db->Quote($userId);

			$db->setQuery($query);
			return $db->loadObject()->count;
		}
		return '';
	}
	
	public function getKloutScore() {
		$settings =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$kloutAPIkey  = $settings->get( 'KloutAPI' );
		$twitterName = $this->getTwitterName();
		$kscore = 0;
		
		if($twitterName != '') {
			$kloutXML = new DOMDocument();
			$kloutDataString = @file_get_contents('http://api.klout.com/1/klout.xml?key='.$kloutAPIkey.'&users='.$twitterName);
			if($kloutDataString) {
				$kloutXML->loadXML($kloutDataString);
				$kscore = (int)$kloutXML->getElementsByTagName('kscore')->item(0)->nodeValue;
			}
		} else {
			$kscore = false;
		}
		
		return $kscore;
	}
	
	public function getTwitterName() {
		$userId = JRequest::getInt('uid');
		$db = JFactory::getDBO();
		$settings =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$twitterName  = $settings->get( 'jomsocial_twitter_name' );
		
		if($twitterName != '' && $this->isJomSocialInstalled()) {
			$query = 'SELECT v.value as twitter_name FROM ' . $db->nameQuote('#__community_fields') . ' AS f'
					. ' LEFT JOIN ' . $db->nameQuote('#__community_fields_values') . ' AS v'
					. ' ON f.id = v.field_id'
					. ' WHERE ' . $db->nameQuote('fieldcode') . '=' . $db->Quote($twitterName)
					. ' AND v.user_id = ' . $db->Quote($userId);
			$db->setQuery($query);

			return $db->loadObject()->twitter_name;
		} else {
			return false;
		}
	}
	
	public function getFacebookName() {
		$userId = JRequest::getInt('uid');
		$db = JFactory::getDBO();

		if($this->isJomSocialInstalled()) {
			$query = 'SELECT connectid FROM ' . $db->nameQuote('#__community_connect_users') 
					. ' WHERE  userid = ' . $db->Quote($userId);
			$db->setQuery($query);
			$result = $db->loadObject();
			if($result != NULL){
			return $result->connectid;
			} else {
			    return '';
			}
		}
		return '';
		
	}
	
	public function isJomSocialInstalled() {
		$jspath = JPATH_ADMINISTRATOR.DS.'components/com_community/admin.community.php';
		return JFile::exists($jspath);
	}
}
