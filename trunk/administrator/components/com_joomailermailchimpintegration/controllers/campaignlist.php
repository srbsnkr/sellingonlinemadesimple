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

class joomailermailchimpintegrationsControllerCampaignlist extends joomailermailchimpintegrationsController
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
	
    function edit(){

	$db    =& JFactory::getDBO();
	$cid   =  JRequest::getVar( 'cid', '', 'post', 'array' );
	$query = "SELECT cdata, folder_id FROM #__joomailermailchimpintegration_campaigns WHERE `creation_date` = '".$cid[0]."'";
	$db->setQuery($query);
	$result = $db->loadAssocList();
	$cdata = json_decode($result[0]['cdata']);

	JRequest::setVar('cid',   $cid[0]);
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

    function send(){

	$cid  =  JRequest::getVar( 'cid', '', 'post', 'array' );
	$link = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$cid[0];
	$this->setRedirect( $link );
    }
	
    function unschedule( ){

	$cid = JRequest::getVar( 'cid', '', 'post', 'array' );
	if(!$cid){
	    $msg = JText::_( 'JM_INVALID_CAMPAIGNID' );
	    $error = 'error';
	} else {
	    $error = '';
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MC = new joomlamailerMCAPI($MCapi);
	    $MCerrorHandler = new MCerrorHandler();

	    foreach($cid as $c){
		$result = $MC->campaignDelete( $c );
		if(!$result) {
		    $msg = $MCerrorHandler->getErrorMsg($MC);
		    $error = 'error';
		    break;
		} else {
		    $db  =& JFactory::getDBO();
		    $query = "UPDATE #__joomailermailchimpintegration_campaigns SET `sent` = 0, `cid` = '' WHERE `cid` = '".$c."'";
		    $db->setQuery($query);
		    $db->query();
		}
	    }
	    if(!$error){
		$msg = JText::_( 'JM_CAMPAIGNS_UNSCHEDULED' );
		$error = '';
	    }
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='.JRequest::getVar( 'type', 'sent');
	$this->setRedirect( $link, $msg, $error );
    }
	
    function pause(){ // you can only pause autoresponder and rss campaigns

	$cid = JRequest::getVar( 'cid', '', 'post', 'array' );
	if(!$cid){
	    $msg = JText::_( 'JM_INVALID_CAMPAIGNID' );
	    $error = 'error';
	} else {
	    $error = '';
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MC = new joomlamailerMCAPI($MCapi);
	    $MCerrorHandler = new MCerrorHandler();

	    foreach($cid as $c){
		$result = $MC->campaignPause( $c );
		if(!$result) {
		    $msg = $MCerrorHandler->getErrorMsg($MC);
		    $error = 'error';
		    break;
		}
	    }
	    if(!$error){
		$msg = JText::_( 'JM_CAMPAIGNS_PAUSED' );
		$error = '';
	    }
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='.JRequest::getVar( 'type', 'sent');
	$this->setRedirect( $link, $msg, $error );
    }

    function resume(){

	$cid = JRequest::getVar( 'cid', '', 'post', 'array' );
	if(!$cid){
	    $msg = JText::_( 'JM_INVALID_CAMPAIGNID' );
	    $error = 'error';
	} else {
	    $error = '';
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MC = new joomlamailerMCAPI($MCapi);
	    $MCerrorHandler = new MCerrorHandler();

	    foreach($cid as $c){
		$result = $MC->campaignResume( $c );
		if(!$result) {
		    $msg = $MCerrorHandler->getErrorMsg($MC);
		    $error = 'error';
		    break;
		}
	    }
	    if(!$error){
		$msg = JText::_( 'JM_CAMPAIGNS_RESUMED' );
		$error = '';
	    }
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='.JRequest::getVar( 'type', 'sent');
	$this->setRedirect( $link, $msg, $error );
    }

    function copy(){

	$db  =& JFactory::getDBO();
	$cid =  JRequest::getVar( 'cid', '', 'post', 'array' );
	$query = "SELECT cdata FROM #__joomailermailchimpintegration_campaigns WHERE `cid` = '".$cid[0]."'";
	$db->setQuery($query);
	$cdata = json_decode($db->loadResult());

	JRequest::setVar('cid',   $cid[0]);
	foreach( $cdata as $k => $v ){
	    JRequest::setVar( $k, $v );
	}
	JRequest::setVar( 'view',   'create' );
	JRequest::setVar( 'layout', 'default' );
	JRequest::setVar( 'action', 'copy'  );
	JRequest::setVar( 'hidemainmenu', 0 );
	parent::display();
    }

    function remove()
    {
	$cid    = JRequest::getVar( 'cid', '', 'post', 'array' );
	$status = JRequest::getVar( 'filter_status', '', 'post', 'string' );

	if(!$cid){
	    $msg = JText::_( 'JM_INVALID_CAMPAIGNID' );
	    $error = 'error';
	} else {
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $error = '';
	    if($status=='save'){
		jimport('joomla.filesystem.file');
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$archiveDir = $params->get( $paramsPrefix.'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' );
		$path = JPATH_SITE . $archiveDir . '/';
		foreach($cid as $c){
		    $db  =& JFactory::getDBO();
		    $query ="SELECT name FROM #__joomailermailchimpintegration_campaigns WHERE creation_date = ".$c;
		    $db->setQuery($query);
		    $cName = $db->loadResult();
		    $cName = str_replace(' ', '_', $cName);
		    $cName = htmlentities($cName);

		    if( !JFile::delete($path.$cName.'.html') ||
			!JFile::delete($path.$cName.'.txt')  ){
			$msg = JText::_('JM_DELETE_FAILED');
			$error = 'error';
		    } else {
			$query = "DELETE FROM #__joomailermailchimpintegration_campaigns WHERE creation_date = ".$c;
			$db->setQuery($query);
			$db->query();
		    }
		}
	    } else {
		$MCapi  = $params->get( $paramsPrefix.'MCapi' );
		$MC = new joomlamailerMCAPI($MCapi);
		$MCerrorHandler = new MCerrorHandler();

		foreach($cid as $c){
		    $result = $MC->campaignDelete( $c );
		    if(!$result) {
			$msg = $MCerrorHandler->getErrorMsg($MC);
			$error = 'error';
			break;
		    }
		}
	    }
	    if(!$error){
		if($status=='save'){
		    $msg = JText::_( 'JM_DRAFT_DELETED' );
		} else {
		    $msg = JText::_( 'JM_CAMPAIGNS_DELETED' );
		}
		$error = '';
	    }
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='.JRequest::getVar( 'type', 'sent');
	$this->setRedirect( $link, $msg, $error );
    }// function


    function create()
    {
	$link = 'index.php?option=com_joomailermailchimpintegration&view=create';
	$this->setRedirect( $link );
    }
	
	
    function cancel()
    {
	$msg = JText::_( 'JM_OPERATION_CANCELLED' );
	$link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='.JRequest::getVar( 'type', 'sent');
	$this->setRedirect( $link, $msg );
    }
}
