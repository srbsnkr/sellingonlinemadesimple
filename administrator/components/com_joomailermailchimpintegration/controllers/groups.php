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

jimport('joomla.application.component.controller');

class joomailermailchimpintegrationsControllerGroups extends joomailermailchimpintegrationsController
{

    function __construct()
    {
	parent::__construct();

	// Register Extra tasks
	$this->registerTask( 'add' , 'edit' );
    }// function

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

    function edit()
    {
	JRequest::setVar( 'view', 'groups' );
	JRequest::setVar( 'layout', 'form'  );
	JRequest::setVar( 'hidemainmenu', 1);

	parent::display();
    }

    function save()
    {
	$db	=& JFactory::getDBO();

	$action  = JRequest::getVar('action', 'add', 'post', 'string');
	$fieldId = JRequest::getVar('fieldId', '', 'post', 'string');
	$groupingId = JRequest::getVar('$groupingId', '', 'post', 'string');

	$listid  = JRequest::getVar('listid',0, 'post', 'string');
	$name    = ($action=='add')?JRequest::getVar('name',  0, 'post', 'string', JREQUEST_ALLOWRAW):JRequest::getVar('nameOld',  0, 'post', 'string', JREQUEST_ALLOWRAW);

	$coreType = JRequest::getVar('coreType',  0, 'post', 'string');
	$CBfield  = JRequest::getVar('CBfield',   0, 'post', 'string', JREQUEST_ALLOWRAW);
	$JSfield  = JRequest::getVar('JSfield',   0, 'post', 'string', JREQUEST_ALLOWRAW);
	$VMfield  = JRequest::getVar('VMfield',   0, 'post', 'string', JREQUEST_ALLOWRAW);

	if($coreType){

	    $type      = $coreType;
	    $framework = 'core';
	    $db_field  = '';

	} else if($CBfield){

	    $framework = 'CB';
	    $CBfield = explode('|*|', $CBfield);
	    $db_field = $CBfield[0];
	    $db_id    = $CBfield[1];

	} else if($JSfield) {

	    $framework = 'JS'; $db_field = $JSfield;
	} else if($VMfield) {

	    $framework = 'VM';
	}

	// get options
	if($CBfield || $JSfield) { $options = ''; }
	if($framework=='CB') {
	    $query = "SELECT type FROM #__comprofiler_fields WHERE fieldid = ".$db_id;
	    $db->setQuery($query);
	    $fieldType = $db->loadResult();
	    if( $fieldType == 'select' || $fieldType == 'singleselect') {
		$type = 'dropdown';
	    } else if ($fieldType == 'checkbox' || $fieldType == 'multicheckbox' || $fieldType == 'multiselect'){
		$type = 'checkboxes';
	    } else if ( $fieldType != 'radio' ){
		$type = 'hidden';
	    } else {
		$type = $fieldType;
	    }
	    $query = "SELECT fieldtitle as options FROM #__comprofiler_field_values WHERE fieldid = ".$db_id;
	    $db->setQuery($query);
	    $fieldData = $db->loadObjectList();
	} else if($framework=='JS') {
	    $query = "SELECT type, options FROM #__community_fields WHERE id =  ".$db_field;
	    $db->setQuery($query);
	    $fieldData = $db->loadObjectList();
	} else if($framework=='VM') {
	    $query = "SELECT * FROM #__vm_userfield_values WHERE fieldid =  ".$VMfield;
	    $db->setQuery($query);
	    $fieldData = $db->loadObjectList();

	    $query = "SELECT name, type FROM #__vm_userfield "
		    ."WHERE fieldid = ".$VMfield;
	    $db->setQuery($query);
	    $fieldInfo = $db->loadObjectList();
	    $db_field  = $fieldInfo[0]->name;
	    $fieldType = $fieldInfo[0]->type;


	    if( $fieldType == 'select' || $fieldType == 'singleselect') {
		$type = 'dropdown';
	    } else if ($fieldType == 'checkbox' || $fieldType == 'multicheckbox' || $fieldType == 'multiselect'){
		$type = 'checkboxes';
	    } else if ( $fieldType != 'radio' ){
		$type = 'hidden';
	    } else {
		$type = $fieldType;
	    }
	}

	if($framework=='core'){
	    $options = explode("\n",JRequest::getVar('coreOptions',  '', 'post', 'string'));
	    for($i=0;$i<count($options);$i++){
		$options[$i] = trim($options[$i]);
	    }
	    $options = array_values(array_filter($options));
	} else if($framework=='CB') {
	    if( $fieldType == 'checkbox' ){
		$options[] = 'Yes';
	    } else if( $fieldType == 'text' || $fieldType == 'textarea' ){
		$options[] = '';
	    } else {
		foreach ($fieldData as $o){
		    $options[] = $o->options;
		}
	    }
	} else if($framework=='JS') {
	    foreach ($fieldData as $o){
		$options = explode("\n",$o->options);
		$type = $o->type;
		if( $type == 'select' || $type == 'singleselect' || $type == 'country' ) {
		    $type = 'dropdown';
		} else if ($type == 'checkbox' || $type == 'multicheckbox' || $type == 'multiselect'){
		    $type = 'checkboxes';
		} else if ( $type != 'radio' ){
		    $type = 'hidden';
		}
	    }
	} else if($framework=='VM') {
	    foreach ($fieldData as $o){
		$options[] = $o->fieldvalue;
	    }
	}


	if( count($options) > 60 ){
	    $msg = JText::_( 'JM_TOO_MANY_OPTIONS' );
	    $msgType = 'error';
	} else {
	    // create custom field using MC API
	    $MC		= $this->MC_object();
	    if( $action == 'add' ){
		$result = $MC->listInterestGroupingAdd( $listid, $name, $type, $options );
	    } else {
		$result = $MC->listInterestGroupingUpdate( $groupingId, $name, $options );
	    }
	    if(!$MC->errorCode){
		$groupingID = $result;
	    }

	    if ( !$MC->errorCode ) {
		// store field association in J! db
		if( $framework != 'core') {
		    if( $action == 'add'){
			$query = "INSERT INTO #__joomailermailchimpintegration_custom_fields ".
				 "( `listID`, `name`, `framework`, `dbfield`, `grouping_id`, `type` ) ".
				 "VALUES ('".$listid."', '".$name."', '".$framework."', '".$db_field."', '".$groupingID."', 'group' )";
		    } else {
			$query = "UPDATE #__joomailermailchimpintegration_custom_fields ".
				 "( `listID`, `name`, `framework`, `dbfield`, `grouping_id`, `type ) ".
				 "VALUES ('".$listid."', '".$name."', '".$framework."', '".$db_field."', '".$groupingID."', 'group' )";
		    }
		    $db->setQuery($query);
		    $db->Query();
		    if($db->getErrorMsg()) {
			$msg = $db->getErrorMsg();
			$msgType = 'error';
		    } else {
			$msg = JText::_( 'JM_CUSTOM_FIELD_CREATED' );
			$msgType = '';
		    }
		}
	    } else {
		$msg = MCerrorHandler::getErrorMsg($MC);
		$msgType = 'error';
	    }
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=groups&listid='.$listid;
	$this->setRedirect($link, $msg, $msgType);
    }

    function remove()
    {
	$db	=& JFactory::getDBO();
	$MC	= $this->MC_object();

	$listid   = JRequest::getVar('listid',  0, '', 'string');
	$listName = JRequest::getVar('listName',  0, '', 'string');
	$cid      = JRequest::getVar('cid',  0, '', 'array');
	foreach($cid as $id){
	    $delete = $MC->listInterestGroupingDel( $id );
	    // remove field association from J! db
	    $db	=& JFactory::getDBO();
	    $query = "DELETE FROM #__joomailermailchimpintegration_custom_fields WHERE grouping_id = '".$id."' LIMIT 1";
	    $db->setQuery($query);
	    $db->Query();

	    if( $delete->errorCode ) {
		$msg = MCerrorHandler::getErrorMsg($MC);
		$msgType = 'error';
		break;
	    } else {
		$msg = JText::_( 'JM_CUSTOM_FIELD_DELETED' );
		$msgType = '';
	    }
	}
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=groups&listid='.$listid.'&name='.$listName, $msg, $msgType );
    }

    function cancel()
    {
	$msg = JText::_( 'JM_OPERATION_CANCELLED' );
	$listid   = JRequest::getVar('listid',  0, '', 'string');
	$listName = JRequest::getVar('listName',  0, '', 'string');
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=groups&listid='.$listid.'&name='.$listName, $msg );
    }
	
    function goToLists(){
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations' );
    }
}
