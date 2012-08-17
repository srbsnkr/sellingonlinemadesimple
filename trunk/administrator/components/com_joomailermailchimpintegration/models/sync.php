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

/**
 * joomailermailchimpintegration Model
 *
 * @package    joomailermailchimpintegration
 * @subpackage Models
 */
class joomailermailchimpintegrationsModelSync extends JModel
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
	$mainframe =& JFactory::getApplication();
	$db	=& JFactory::getDBO();
	$filter_type = $mainframe->getUserStateFromRequest( "filter_type",		'filter_type', 		0,			'string' );
	$search	= $mainframe->getUserStateFromRequest( "search",	        'search', 			'',			'string' );
	$search	= JString::strtolower( $search );
	$filter_date = $mainframe->getUserStateFromRequest( "filter_date",      'filter_date',      '',         'string' );
	if($filter_date == JText::_('Last visit after')){
	    $filter_date = false;
	}
		
	$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart	= $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

	if (isset( $search ) && $search!= '')
	{
	    $searchEscaped = '"%'.$db->getEscaped( $search, true ).'%"';
	    $where[] = ' username LIKE '.$searchEscaped.' OR email LIKE '.$searchEscaped.' OR name LIKE '.$searchEscaped;
	}

        if (version_compare(JVERSION,'1.6.0','ge')) {
	    if ( $filter_type > 1 ) {
		$where[] = ' um.group_id = '.$db->Quote($filter_type).' ';
	    }
	} else {
	    if ( $filter_type ) {
		if ( $filter_type == 'Public Frontend' ){
		    $where[] = ' a.usertype = \'Registered\' OR a.usertype = \'Author\' OR a.usertype = \'Editor\' OR a.usertype = \'Publisher\' ';
		} else if ( $filter_type == 'Public Backend' ) {
		    $where[] = 'a.usertype = \'Manager\' OR a.usertype = \'Administrator\' OR a.usertype = \'Super Administrator\' ';
		} else {
		    $where[] = 'a.usertype = LOWER( '.$db->Quote($filter_type).' ) ';
		}
	    }
	}

        $where[] = " block = '0' ";

        $where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

        if ( $filter_date && $filter_date != JText::_('JM_LAST_VISIT_AFTER') ) {
            $where .= " AND lastvisitDate >= '".$filter_date."' ";
        }

	if (version_compare(JVERSION,'1.6.0','ge')) {
	    $query =  'SELECT a.*, ug.title AS groupname'
		    . ' FROM #__users AS a'
		    . ' INNER JOIN #__user_usergroup_map AS um ON um.user_id = a.id'
		    . ' INNER JOIN #__usergroups AS ug ON ug.id = um.group_id'
		    . $where
		    . ' ORDER BY a.id';

	} else {
	    $query =  'SELECT a.*, g.name AS groupname'
		    . ' FROM #__users AS a'
		    . ' INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id'
		    . ' INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id'
		    . ' INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id'
		    . $where
		    . ' ORDER BY a.id';
	}
        
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
    
    function getUserByEmail($email)
    {
	$query = 'SELECT id,name,username,email,block,usertype FROM #__users WHERE email ="'.$email.'"';
	$this->_data = $this->_getList( $query );

	if(!isset($this->_data[0])){
	    $cm = $this->cm_object();
	    $lists = $this->getLists();
	    foreach($lists['anyType']['List'] as $listID){
		if( isset($result) && isset($result['anyType']['Code']) && $result['anyType']['Code'] != 203 ) break;
		$result = $cm->subscriberGetSingleSubscriber( $listID['ListID'], $email );
		if( isset($result['anyType']['Name']) ){
		    $this->_data[0] = new stdClass;
		    $this->_data[0]->name = $result['anyType']['Name'];
		    $this->_data[0]->email = $result['anyType']['EmailAddress'];
		}
	    }
	}

	return $this->_data;
    }
    
    function getTotalUsers()
    {
	 $db    =& JFactory::getDBO();
         $query = 'SELECT COUNT(id) FROM #__users WHERE block = 0';
	 $db->setQuery($query);

         return $db->loadResult();
    }

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }
	
    function getSubscriberLists()
    {
	$MC = $this->MC_object();
	$results = $MC->lists();

	return $results;
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

    function getGroups()
    {
	if (version_compare(JVERSION,'1.6.0','ge')) {
	    require_once( JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php' );
	    $groups = UsersHelper::getGroups();
	} else {
	    $db =& JFactory::getDBO();
	    $query = 'SELECT id, name FROM #__core_acl_aro_groups
		      WHERE name != "ROOT"
		      AND name != "USERS"
		      AND name != "Public Frontend"
		      AND name != "Public Backend"
		      ORDER BY lft';
	    $db->setQuery($query);
	    $groups = $db->loadObjectList();
	}

        return $groups;
    }
    
    function getConfig( $crm ){

	$db	=& JFactory::getDBO();
	$query = "SELECT params FROM #__joomailermailchimpintegration_crm WHERE crm = '".$crm."'";
	$db->setQuery($query);
	$config = json_decode($db->loadResult());

	return $config;
    }
	
    function getJSFields()
    {
	jimport('joomla.filesystem.file');
	$db =& JFactory::getDBO();
	$jsFields = array();
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_community'.DS.'admin.community.php')){
	    $query = "SELECT * FROM #__community_fields WHERE published = 1 AND type != 'group'";
	    $db->setQuery($query);
	    $jsFields = $db->loadObjectList();

	    return $jsFields;
	}

	return array();
    }

    function getCBFields()
    {
	jimport('joomla.filesystem.file');
	$db =& JFactory::getDBO();
	$cbFields = array();
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'admin.comprofiler.php')){
	    $query = "SELECT fieldid as id, name, title FROM #__comprofiler_fields ".
		     "WHERE `published` = 1 ".
		     "AND `profile` = '1' ".
		     "AND `readonly` = '0' ".
		     "AND `calculated` = '0' ";
	    $db->setQuery($query);
	    $cbFields = $db->loadObjectList();

	    return $cbFields;
	}

	return false;
    }

    function getSugarFields()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$sugar_name = $params->get( $paramsPrefix.'sugar_name' );
	$sugar_pwd  = $params->get( $paramsPrefix.'sugar_pwd' );
	$sugar_url  = $params->get( $paramsPrefix.'sugar_url' );
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'sugar.php');
	$sugar = new SugarCRMWebServices;
	$sugar->SugarCRM( $sugar_name, $sugar_pwd, $sugar_url );
	$sugar->login();

	$fields = $sugar->getModuleFields( 'Contacts' );

	$disallowedFields = array(	'id',
				    'date_entered',
				    'date_modified',
				    'modified_user_id',
				    'modified_by_name',
				    'created_by',
				    'created_by_name',
				    'deleted',
				    'assigned_user_id',
				    'assigned_user_name',
				    'email1'
				 );

	for($x=0;$x<count($fields);$x++){
	    if( in_array( $fields[$x]['name'], $disallowedFields ) ){
		unset( $fields[$x] );
	    }
	}

	return $fields;
    }
	
    function buildFieldsDropdown( $name, $JS, $CB, $config, $email = false )
    {
	$html = '<select name="crmFields['.$name.']" id="'.$name.'" style="min-width: 200px;">';
	$html .= '<option value="">do not sync</option>';

	if( $email ){
	    if(isset($config->{$name}) && $config->{$name} == 'default') { $selected = 'selected="selected"'; } else { $selected = ''; }
	    $html .= '<option value="default" '.$selected.'>Joomla User Account Email</option>';
	}

	if($JS){
	    $html .= '<optgroup label="JomSocial">';
	    foreach($JS as $field){
		if(isset($config->{$name}) && $config->{$name} == 'js;'.$field->id) { $selected = 'selected="selected"'; } else { $selected = ''; }
		$html .= '<option value="js;'.$field->id.'" '.$selected.'>'.$field->name.'</option>';
	    }
	    $html .= '</optgroup>';
	}
	if($CB){
	    $html .= '<optgroup label="Community Builder">';
	    foreach($CB as $field){
		if(isset($config->{$name}) && $config->{$name} == 'cb;'.$field->name) { $selected = 'selected="selected"'; } else { $selected = ''; }
		$html .= '<option value="cb;'.$field->name.'" '.$selected.'>'.$field->title.'</option>';
	    }
	    $html .= '</optgroup>';
	}

	$html .= '</select>';

	return $html;
    }

    function getCRMusers(){
	$db =& JFactory::getDBO();
	$query = "SELECT crm, user_id FROM #__joomailermailchimpintegration_crm_users";
	$db->setQuery($query);
	$data = $db->loadObjectList();
	if( isset($data[0]) ){
	    foreach ($data as $d){
		$result[$d->crm][] = $d->user_id;
	    }
	    return $result;
	} else {
	    return false;
	}
    }

}
