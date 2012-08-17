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

class joomailermailchimpintegrationsControllerFields extends joomailermailchimpintegrationsController
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

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'fields' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar( 'hidemainmenu', 1);

		parent::display();
	}// function

	function save()
	{
		$db	=& JFactory::getDBO();
		$action  = JRequest::getVar('action', 'add', 'post', 'string');
		$listid  = JRequest::getVar('listid',0, 'post', 'string');

		$name = JRequest::getVar('name', 'Untitled', 'post', 'string', JREQUEST_ALLOWRAW);
		$field_type = JRequest::getVar('field_type',  0, 'post', 'string');
		$options['req'] = JRequest::getVar('req', 0, 'post', 'string');
		$newtag = JRequest::getVar('tag', '', 'post', 'string');
		$oldtag = JRequest::getVar('oldtag', '', 'post', 'string');

		$tag = ($newtag != $oldtag) ? $newtag : $oldtag;
		$tag = strtoupper( $tag );
		$options['tag'] = $tag;

		$CBfield  = JRequest::getVar('CBfield',   0, 'post', 'string', JREQUEST_ALLOWRAW);
		$JSfield  = JRequest::getVar('JSfield',   0, 'post', 'string', JREQUEST_ALLOWRAW);
		$VMfield  = JRequest::getVar('VMfield',   0, 'post', 'string', JREQUEST_ALLOWRAW);

			if($field_type && $action == 'add'){

				$type      = $field_type;
				$framework = 'core';
				$db_field  = '';

			} else if($CBfield) {

				$framework = 'CB';
				$CBfield = explode('|*|', $CBfield);
				$db_field = $CBfield[0];
				$db_id    = $CBfield[1];

			} else if($JSfield) {
				$framework = 'JS';
				$db_field = $JSfield;
			} else if($VMfield) {
				$framework = 'VM';
			} else {
				$framework = 'core';
			}


		// get options
		if($CBfield || $JSfield) { $options['choices'] = ''; }
		if($framework=='CB') {
			$query = "SELECT type FROM #__comprofiler_fields "
				."WHERE fieldid = ".$db_id;
			$db->setQuery($query);
			$fieldType = $db->loadResult();
			if( $fieldType == 'predefined' ){
				$type = 'text';
			} else if( $fieldType == 'select' || $fieldType == 'singleselect') {
				$type = 'dropdown';
			} else if ($fieldType == 'checkbox' || $fieldType == 'multicheckbox' || $fieldType == 'multiselect'){
				$type = 'checkboxes';
			} else if ( $fieldType != 'radio' ){
				$type = 'hidden';
			} else {
				$type = $fieldType;
			}
			$query = "SELECT fieldtitle as options FROM #__comprofiler_field_values "
				."WHERE fieldid = ".$db_id;
			$db->setQuery($query);
			$fieldData = $db->loadObjectList();
		} else if($framework=='JS') {
			$query = "SELECT type, options FROM #__community_fields "
				."WHERE id =  ".$db_field;
			$db->setQuery($query);
			$fieldData = $db->loadObjectList();
		} else if($framework == 'VM'){
			$query = "SELECT name, type FROM #__vm_userfield "
				."WHERE fieldid = ".$VMfield;
			$db->setQuery($query);
			$fieldInfo = $db->loadObjectList();
			$db_field  = $fieldInfo[0]->name;
			if( $db_field == 'title' || $db_field == 'country' || $db_field == 'state' ){
			    $fieldType = 'text';
			} else {
			    $fieldType = $fieldInfo[0]->type;
			}
			$query = "SELECT * FROM #__vm_userfield_values "
				."WHERE fieldid = ".$VMfield;
			$db->setQuery($query);
			$fieldData = $db->loadObjectList();
		}

		if($framework=='core'){
			$options['choices'] = explode("\n",JRequest::getVar('coreOptions',  '', 'post', 'string'));
			for($i=0;$i<count($options['choices']);$i++){
				$options['choices'][$i] = trim($options['choices'][$i]);
			}
			//$options = array_values(array_filter($options['choices']));

		} else if($framework=='CB') {
			if( $fieldType == 'checkbox' ){
				$options['choices'][] = '1';
				$field_type = 'radio';
			} else if( $fieldType == 'text' || $fieldType == 'textarea' ){
				$options['choices'][] = '';
				$field_type = 'text';
			} else if( $fieldType == 'datetime' || $fieldType == 'date') {
				$field_type = 'date';
			} else {
				foreach ($fieldData as $o){
					$options['choices'][] = $o->options;
				}
				$field_type = 'dropdown';
			}
		} else if($framework=='JS') {
			foreach ($fieldData as $o){
				$options['choices'] = explode("\n",$o->options);
				$type = $o->type;
				if( $type == 'select' || $type == 'singleselect' || $type == 'country' ) {
					$type = 'dropdown';
				} else if ( $type == 'text' || $type == 'textarea' ) {
					$type = 'text';
				} else if ( $type != 'radio' && $type != 'date' ){
					$type = 'hidden';
				}
				$field_type = $type;
			}
		} else if($framework == 'VM'){
			if( $fieldType == 'checkbox' ){
				$options['choices'][] = JText::_('JM_NO');
				$options['choices'][] = JText::_('JM_YES');
				$field_type = 'dropdown';
			} else if(	$fieldType == 'text'
				    || $fieldType == 'textarea'
				    || $fieldType == 'euvatid'
				    || $fieldType == 'editorta' ){
				$options['choices'][] = '';
				$field_type = 'text';
			} else if( $fieldType == 'webaddress' ) {
				$field_type = 'url';
			} else  if( $fieldType == 'age_verification' ) {
				$field_type = 'date';
			} else {
				foreach ($fieldData as $o){
				    $options['choices'][] = $o->fieldvalue;
				}
				if( $fieldType == 'radio' ){
				    $field_type = 'radio';
				} else {
				    $field_type = 'dropdown';
				}
			}
		}

		// create custom field using MC API
		$MC = $this->MC_object();
		if( $action == 'add' ){
			$options['field_type'] = $field_type;
			$result = $MC->listMergeVarAdd( $listid, $tag, $name, $options );
		} else {
			$options['name'] = $name;
			$result = $MC->listMergeVarUpdate( $listid, $oldtag, $options );
		}

		if ( !$MC->errorCode ) {
		    if( $framework != 'core' ) {
    			//Check to see if field associations are stored locally
    			$query = "SELECT id FROM #__joomailermailchimpintegration_custom_fields ".
				 "WHERE `grouping_id` = '".$tag."'";
    			$db->setQuery($query);
    			$cfid = $db->loadResult();
    			// store field association in J! db
    			if( $action == 'add' || !$cfid ){
    				$query = "INSERT INTO #__joomailermailchimpintegration_custom_fields ".
					 "( `listID`, `name`, `framework`, `dbfield`, `grouping_id`, `type` ) ".
					 "VALUES ('".$listid."', '".$name."', '".$framework."', '".$db_field."', '".$tag."', 'field' )";
    			} else {
    				$query = "UPDATE #__joomailermailchimpintegration_custom_fields ".
					 " SET `listID` = '".$listid."', ".
					 "`name`='".$name."', ".
					 "`framework`='".$framework."', ".
					 "`dbfield`='".$db_field."', ".
					 "`grouping_id`='".$tag."', ".
					 "`type`='field'".
					 " WHERE `grouping_id` = '".$tag."'";
    			}

    			$db->setQuery($query);
    			$db->Query();
    			if($db->getErrorMsg()) {
    				$msg = $db->getErrorMsg();
    				$msgType = 'error';
    			} else {
			    if( $action == 'add' ){
    				$msg = JText::_( 'JM_MERGE_FIELD_CREATED' );
			    } else {
				$msg = JText::_( 'JM_MERGE_FIELD_UPDATED' );
			    }
    				$msgType = '';
    			}
            }
	} else {
		$msg = MCerrorHandler::getErrorMsg($MC);
		$msgType = 'error';
	}


	$link = 'index.php?option=com_joomailermailchimpintegration&view=fields&listid='.$listid;
	$this->setRedirect($link, $msg, $msgType);
    }// function

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$db	=& JFactory::getDBO();
		$MC	= $this->MC_object();

		$listid   = JRequest::getVar('listid',  0, '', 'string');
		$cid      = JRequest::getVar('cid',  0, '', 'array');
		foreach($cid as $id){
			$attribs = explode(';',$id);
			$tag = $attribs[1];

			$delete = $MC->listMergeVarDel( $listid, $tag );
			// remove field association from J! db
			$db	=& JFactory::getDBO();
			$query = "DELETE FROM #__joomailermailchimpintegration_custom_fields WHERE grouping_id = '".$tag."' LIMIT 1";
			$db->setQuery($query);
			$db->Query();

			if( $delete->errorCode ) {
				$msg = MCerrorHandler::getErrorMsg($MC);
				$msgType = 'error';
				break;
			} else {
				$msg = JText::_( 'JM_MERGE_FIELDS_DELETED' );
				$msgType = '';
			}
		}
		$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=fields&listid='.$listid, $msg, $msgType );
	}// function

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'JM_OPERATION_CANCELLED' );
		$listid   = JRequest::getVar('listid',  0, '', 'string');
		$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=fields&listid='.$listid, $msg );
	}// function

	function goToLists(){
		$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations' );
	}
}// class
