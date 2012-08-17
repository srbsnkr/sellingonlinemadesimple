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

class joomailermailchimpintegrationsModelGroups extends JModel
{

    function __construct()
    {
	parent::__construct();

	$array = JRequest::getVar('cid',  0, '', 'array');
	$this->setId((int)$array[0]);
    }//function

    function setId($id)
    {
	// Set id and wipe data
	$this->_id	= $id;
	$this->_data	= null;
    }//function


    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

    function &getData()
    {
	$listid	= JRequest::getVar('listid',  0, '', 'string');
	$MC	= $this->MC_object();
	$this->_data = $MC->listInterestGroupings( $listid );
	return $this->_data;
    }

    function getCBfields() {

	jimport('joomla.filesystem.file');
	if ( JFile::exists( JPATH_ADMINISTRATOR.'/components/com_comprofiler/admin.comprofiler.php') ) {
	    jimport( 'joomla.application.component.helper' );
	    $cHelper = JComponentHelper::getComponent( 'com_comprofiler', true );

	} else {
	     $cHelper->enabled = false;
	}
	if( $cHelper->enabled ){
	    $db =& JFactory::getDBO();
	    $query = "SELECT * FROM #__comprofiler_fields WHERE `published` = 1 AND ( ".
		     "`type` = 'checkbox' OR ".
		     "`type` = 'multicheckbox' OR ".
		     "`type` = 'select' OR ".
		     "`type` = 'multiselect' OR ".
		     "`type` = 'radio' ".
		     ") ";
	    $db->setQuery($query);
	    $fields = $db->loadObjectList();

	    if($fields) {
		return $fields;
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }

    function getJSfields() {

	jimport('joomla.filesystem.file');
	if ( JFile::exists( JPATH_ADMINISTRATOR.'/components/com_community/community.xml') ) {
	    jimport( 'joomla.application.component.helper' );
	    $cHelper = JComponentHelper::getComponent( 'com_community', true );
	} else {
	    $cHelper->enabled = false;
	}
	if( $cHelper->enabled ){
	    $db =& JFactory::getDBO();
	    $query = "SELECT * FROM #__community_fields WHERE published = 1 AND ( ".
		     "`type` = 'checkbox' OR ".
//		     "`type` = 'country' OR ".
		     "`type` = 'list' OR ".
		     "`type` = 'select' OR ".
		     "`type` = 'singleselect' OR ".
		     "`type` = 'radio' ".
		     ") ";
	    $db->setQuery($query);
	    $fields = $db->loadObjectList();

	    if($fields) {
		return $fields;
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }

    function getVMfields()
    {
	jimport('joomla.filesystem.file');
	if ( JFile::exists( JPATH_ADMINISTRATOR.'/components/com_virtuemart/virtuemart.xml') ) {
	    jimport( 'joomla.application.component.helper' );
	    $cHelper = JComponentHelper::getComponent( 'com_virtuemart', true );
	} else {
	    $cHelper->enabled = false;
	}
	if( $cHelper->enabled ){
	    $db =& JFactory::getDBO();

	    $query = "SELECT fieldid as id, name ".
		     "FROM #__vm_userfield ".
		     "WHERE `published` = 1 ".
		     "AND `registration` = 1 ".
		     "AND `type` != 'delimiter' ".
		     "AND `type` != 'password' ".
		     "AND `type` != 'emailaddress' ".
		     "AND `type` != 'text' ".
		     "AND `type` != 'euvatid' ".
		     "AND `type` != 'editorta' ".
		     "AND `type` != 'textarea' ".
		     "AND `type` != 'webaddress' ".
		     "AND `type` != 'age_verification' ".
		     "Order By `ordering` ASC";
	    $db->setQuery($query);
	    $fields = $db->loadObjectList();

	    if($fields) {
		return $fields;
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }

    function store()
    {
	return true;
    }

}
