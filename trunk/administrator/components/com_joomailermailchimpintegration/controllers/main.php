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

class joomailermailchimpintegrationsControllerMain extends joomailermailchimpintegrationsController
{
    function __construct()
    {
	parent::__construct();
    }

    function save()
    {
	$error = '';
	unset($_SESSION['MCping']);

	$MCapi = trim(JRequest::getVar( 'MCapi', '', 'post', 'string' ));
	if(!$MCapi){
	    $msg = JText::_('JM_INVALID_API_CLIENT_ID');
	} else {
	    $db = & JFactory::getDBO();
	    if(version_compare(JVERSION,'1.6.0','ge')) {
		$query  = "SELECT params FROM #__extensions WHERE `element` = 'com_joomailermailchimpintegration' ";
		$db->setQuery($query);
		$parameters = $db->loadResult();

		$parameters = json_decode( $parameters );
		$parameters->params->MCapi = $MCapi;
		$parameters = json_encode( $parameters );

		$query  = "UPDATE #__extensions SET `params` = '".$parameters."' WHERE `element` = 'com_joomailermailchimpintegration' ";
		$db->setQuery($query);
		$db->query();

		if($db->getErrorMsg()){
		    $error = 'error';
		    $msg   = $db->getErrorMsg();
		} else {
		    $msg = '';
		}
	    } else {
		$query  = "SELECT params FROM #__components WHERE `link` = 'option=com_joomailermailchimpintegration' ";
		$db->setQuery($query);
		$parameters = trim($db->loadResult());
		if($db->getErrorMsg()){
		    $error = 'error';
		    $msg   = $db->getErrorMsg();
		} else {
		    if($parameters){
			if( stristr($parameters, 'MCapi') ){
			    $parameters = preg_replace('#MCapi=(.*)#', "MCapi=".$MCapi, $parameters);
			} else {
			    $parameters = "MCapi=".$MCapi."\n".$parameters;
			}
		    } else {
			$parameters = "MCapi=".$MCapi."\n";
		    }
		    $query  = "UPDATE #__components SET `params` = '".$parameters."' WHERE `link` = 'option=com_joomailermailchimpintegration' ";
		    $db->setQuery($query);
		    $db->query();

		    if($db->getErrorMsg()){
			$error = 'error';
			$msg   = $db->getErrorMsg();
		    } else {
			$msg = '';
		    }
		}

	    }
	    
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=main';
	$this->setRedirect( $link, $msg, $error );
    }

    function copy()
    {
	$db  =& JFactory::getDBO();
	$cid =  JRequest::getVar( 'cid', '', 'post', 'string' );
	$query = "SELECT cdata FROM #__joomailermailchimpintegration_campaigns WHERE `cid` = '".$cid."'";
	$db->setQuery($query);
	$cdata = json_decode($db->loadResult());

	JRequest::setVar('cid',   $cid);
	foreach( $cdata as $k => $v ){
	    JRequest::setVar( $k, $v );
	}
	JRequest::setVar( 'view',   'create' );
	JRequest::setVar( 'layout', 'default'  );
	JRequest::setVar( 'action', 'copy'  );
	JRequest::setVar( 'hidemainmenu', 0 );
	JRequest::setVar( 'offset', 0 );
	parent::display();
    }

    function edit()
    {
	$db    =& JFactory::getDBO();
	$cid   =  JRequest::getVar( 'campaign', '', 'post', 'string' );
	$query = "SELECT cdata, folder_id FROM #__joomailermailchimpintegration_campaigns WHERE `creation_date` = '".$cid."'";
	$db->setQuery($query);
	$result = $db->loadAssocList();
	$cdata = json_decode($result[0]['cdata']);

	JRequest::setVar('cid',   $cid);
	foreach( $cdata as $k => $v ){
	    JRequest::setVar( $k, $v );
	}
	JRequest::setVar( 'view',   'create' );
	JRequest::setVar( 'action', 'edit' );
	JRequest::setVar( 'layout', 'default'  );
	JRequest::setVar( 'hidemainmenu', 0 );
	JRequest::setVar( 'offset', 0 );
	parent::display();
    }

    function send()
    {
	$cid  = JRequest::getVar( 'campaign', '', 'post', 'string' );
	$link = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$cid;
	$this->setRedirect( $link );
    }

    function archive()
    {
	$cid = JRequest::getVar( 'cid', '', 'post', 'string' );
	$msg  = 'Campaign archived: '.$cid;
	$link = 'index.php?option=com_joomailermailchimpintegration&view=main';
	$this->setRedirect( $link, $msg );
    }

    function templates()
    {
	$url = 'index.php?option=com_joomailermailchimpintegration&view=templates';
	$this->setRedirect($url);
    }

    function extensions()
    {
	$url = 'index.php?option=com_joomailermailchimpintegration&view=extensions';
	$this->setRedirect($url);
    }

    function hideSetupInfo()
    {
	$db = & JFactory::getDBO();
	$query = "INSERT INTO #__joomailermailchimpintegration_misc ( type, value ) VALUES ( 'setup_info', '1' ) ";
	$db->setQuery( $query );
	$db->query();

	$return['success'] = 1;
	echo json_encode( $return );
    }

    function showSetupInfo()
    {
	$db = & JFactory::getDBO();
	$query = "DELETE FROM #__joomailermailchimpintegration_misc WHERE `type` = 'setup_info' ";
	$db->setQuery( $query );
	$db->query();

	$return['success'] = 1;
	echo json_encode( $return );
    }
}
