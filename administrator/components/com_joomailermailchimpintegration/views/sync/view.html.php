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

jimport( 'joomla.application.component.view');

class joomailermailchimpintegrationsViewSync extends JView
{

    function display($tpl = null)
    {
	$mainframe =& JFactory::getApplication();
	$option = JRequest::getCmd('option');
	$model =& $this->getModel('sync');

	$layout = JRequest::getVar('layout', 'default');
	if($layout == 'default'){
	    $db =& JFactory::getDBO();

	    $filter_order	= $mainframe->getUserStateFromRequest( "$option.filter_order",		'filter_order',		'a.name',	'cmd' );
	    $filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'',		'word' );
	    $filter_type	= $mainframe->getUserStateFromRequest( "$option.filter_type",		'filter_type', 		0,		'string' );
	    $filter_status	= $mainframe->getUserStateFromRequest( "$option.filter_status",		'filter_status',	0,		'string' );
	    $filter_logged	= $mainframe->getUserStateFromRequest( "$option.filter_logged",		'filter_logged',	0,		'int' );
	    $filter_date	= $mainframe->getUserStateFromRequest( "$option.filter_date",		'filter_date',		'',		'string' );
	    $search		= $mainframe->getUserStateFromRequest( "$option.search",		'search', 		'',		'string' );
	    $search		= JString::strtolower( $search );

	    $limit	= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	    $limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

	    $where = array();
	    if (isset( $search ) && $search!= '')
	    {
		$searchEscaped = $db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		$where[] = 'a.username LIKE '.$searchEscaped.' OR a.email LIKE '.$searchEscaped.' OR a.name LIKE '.$searchEscaped;
	    }

	    if (version_compare(JVERSION,'1.6.0','ge')) {
		if ( $filter_type ) {
		    if ( $filter_type == 1 ){
			$where[] = ' a.usertype = \'Registered\' OR a.usertype = \'Author\' OR a.usertype = \'Editor\' OR a.usertype = \'Publisher\' ';
		    } else if ( $filter_type == 'Public Backend' ) {
			$where[] = 'a.usertype = \'Manager\' OR a.usertype = \'Administrator\' OR a.usertype = \'Super Administrator\' ';
		    } else {
			$where[] = ' um.group_id = '.$db->Quote($filter_type).' ';
		    }
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

	    $where[] = ' a.block = 0 ';

	    $orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;
	    $where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );


	    if (version_compare(JVERSION,'1.6.0','ge')) {
		$query = 'SELECT COUNT(a.id)'
		. ' FROM #__users AS a'
		. ' INNER JOIN #__user_usergroup_map AS um ON um.user_id = a.id'
		. ' INNER JOIN #__usergroups AS ug ON ug.id = um.group_id'
		. $where
		;
	    } else {
		$query = 'SELECT COUNT(a.id)'
		. ' FROM #__users AS a'
		. $where
		;
	    }
	    $db->setQuery( $query );
	    $total = $db->loadResult();

	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( $total, $limitstart, $limit );

	    if (version_compare(JVERSION,'1.6.0','ge')) {
		$query =  'SELECT a.*, ug.title AS groupname'
			. ' FROM #__users AS a'
			. ' INNER JOIN #__user_usergroup_map AS um ON um.user_id = a.id'
			. ' INNER JOIN #__usergroups AS ug ON ug.id = um.group_id'
			. $where
			. ' GROUP BY a.id'
			. $orderby;

	    } else {
		$query =  'SELECT a.*, g.name AS groupname'
			. ' FROM #__users AS a'
			. ' INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id'
			. ' INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id'
			. ' INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id'
			. $where
			. ' GROUP BY a.id'
			. $orderby;
	    }
	    $db->setQuery( $query, $pagination->limitstart, $pagination->limit );
	    $rows = $db->loadObjectList();

	    // get list of Groups for dropdown filter
	    if (version_compare(JVERSION,'1.6.0','ge')) {
		// Include the component HTML helpers.
		require_once( JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php' );
		$dropdown = '<select name="filter_type" id="filter_type" class="inputbox" onchange="this.form.submit()">';
		$dropdown .= '<option value="">- '. JText::_( 'JM_USERGROUP' ) .' -</option>';
		$dropdown .=  JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $filter_type);
		$dropdown .= '</select>';
		$lists['type'] 	= $dropdown;
	    } else {
		$query =  'SELECT name AS value, name AS text'
			. ' FROM #__core_acl_aro_groups'
			. ' WHERE name != "ROOT"'
			. ' AND name != "USERS"';

		$db->setQuery( $query );
		$types[] = JHtml::_('select.option',  '0', '- '. JText::_( 'JM_USERGROUP' ) .' -' );
		foreach( $db->loadObjectList() as $obj )
		{
		    $types[] = JHtml::_('select.option',  $obj->value, JText::_( $obj->text ) );
		}
		$lists['type'] 	= JHtml::_('select.genericlist',   $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type" );
	    }

	    // table ordering
	    $lists['order_Dir']	= $filter_order_Dir;
	    $lists['order']	= $filter_order;

	    // search filter
	    $lists['search']= $search;

	    //date filter
	    if($filter_date == ''){ $filter_date = JText::_('JM_LAST_VISIT_AFTER'); }
	    JHtml::_('behavior.calendar');
	    if (version_compare(JVERSION,'1.6.0','ge')) {
		$attr = array('size'=>'16','style'=>'top:0;');
	    } else {
		$attr = array('size'=>'16','style'=>'top:0;', 'readonly' => 'readonly', 'onclick'=>"showCalendar('filter_date', '%Y-%m-%d')");
	    }
	    $lists['filter_date'] = JHtml::_( 'calendar', $filter_date, 'filter_date', 'filter_date', '%Y-%m-%d', $attr);

	    $this->assignRef('lists',		$lists);
	    $this->assignRef('pagination',	$pagination);

	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_ADD_USERS' ), 'MC_logo_48.png' );
	} else if($layout=='sugar'){
	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_SUGARCRM_CONFIGURATION' ), 'MC_logo_48.png' );
	} else if($layout=='highrise'){
	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_HIGHRISE_CONFIGURATION' ), 'MC_logo_48.png' );
	}
		
		

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MCauth = new MCauth();
	
	if ( $MCapi && $MCauth->MCauth() ) {
	    if($layout=='default'){
		if($params->get( $paramsPrefix.'sugar_name' ) && $params->get( $paramsPrefix.'sugar_pwd' )){
		JToolBarHelper::custom( 'sync_sugar', 'sync_sugar', 'sync_sugar', 'JM_ADD_TO_SUGAR', false, false );
		JToolBarHelper::spacer();
		JToolBarHelper::custom( 'sugar', 'sync_sugar', 'sync_sugar', 'JM_SUGAR_CONFIG', false, false );
		JToolBarHelper::spacer();
		}
		if($params->get( $paramsPrefix.'highrise_url' ) && $params->get( $paramsPrefix.'highrise_api_token' )){
		JToolBarHelper::custom( 'sync_highrise', 'sync_highrise', 'sync_highrise', 'JM_ADD_TO_HIGHRISE', false, false );
		JToolBarHelper::spacer();
		JToolBarHelper::custom( 'highrise', 'sync_highrise', 'sync_highrise', 'JM_HIGHRISE_CONFIG', false, false );
		JToolBarHelper::spacer();
		}
		JToolBarHelper::custom( 'mailchimp', 'sync', 'sync', 'JM_ADD_TO_MAILCHIMP', false, false );
		JToolBarHelper::spacer();
	    } else if($layout=='sugar'){
		JToolBarHelper::custom( 'cancel', 'back', 'back', 'JM_BACK', false, false );
		JToolBarHelper::spacer();
		JToolBarHelper::custom( 'setConfig', 'sync_sugar', 'sync_sugar', 'JM_SAVE_CONFIG', false, false );
		JToolBarHelper::spacer();
	    } else if($layout=='highrise'){
		JToolBarHelper::custom( 'cancel', 'back', 'back', 'JM_BACK', false, false );
		JToolBarHelper::spacer();
		JToolBarHelper::custom( 'setConfig', 'sync_highrise', 'sync_highrise', 'JM_SAVE_CONFIG', false, false );
		JToolBarHelper::spacer();
	    }
	}
		
	if($layout=='default'){
	    // Get data from the model
	    $items =& $this->get('Data');
	    $this->assignRef('items', $items);

	    $subscriberLists =& $this->get('SubscriberLists');
	    $this->assignRef('subscriberLists',	$subscriberLists);

	    $suppressed =& $this->get( 'Suppressed');
	    $this->assignRef('suppressed', $suppressed);

	    $groups = & $this->get( 'Groups');
	    $this->assignRef('groups', $groups);

	    $CRMusers = & $this->get('CRMusers');
	    $this->assignRef('CRMusers', $CRMusers);
	} else if($layout=='sugar'){
	    $sugarFields =& $this->get('SugarFields');
	    $this->assignRef('sugarFields', $sugarFields);
	    $JSFields =& $this->get('JSFields');
	    $this->assignRef('JSFields', $JSFields);
	    $CBFields =& $this->get('CBFields');
	    $this->assignRef('CBFields', $CBFields);
	    $config =& $model->getConfig('sugar');
	    $this->assignRef('config', $config);
	} else if($layout=='highrise'){
	    $JSFields =& $this->get('JSFields');
	    $this->assignRef('JSFields', $JSFields);
	    $CBFields =& $this->get('CBFields');
	    $this->assignRef('CBFields', $CBFields);
	    $config =& $model->getConfig('highrise');
	    $this->assignRef('config', $config);
	}
		
	$total =& $this->get( 'TotalUsers');
	$this->assignRef('total', $total);

	parent::display($tpl);
	require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }
}
