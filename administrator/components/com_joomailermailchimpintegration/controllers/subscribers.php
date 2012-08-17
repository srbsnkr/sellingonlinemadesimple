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

class joomailermailchimpintegrationsControllerSubscribers extends joomailermailchimpintegrationsController
{

    function __construct()
    {
	parent::__construct();
    }// function


    function unsubscribe()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);

	$emails = JRequest::getVar( 'emails', array(), 'post', 'array' );
	$listId = JRequest::getVar( 'listid', 0, 'post', 'string' );

	$i=0;
	if( isset($emails[0]) && $listId ) {
	    foreach ( $emails as $email ) {
		$email = explode(';', $email);
		$unsubscribe = $MC->listUnsubscribe( $listId, $email[0], false, false, false );
		if( !$MC->errorCode ) $i++;
	    }
	}

	if ( $MC->errorCode ) {
	    $msg = MCerrorHandler::getErrorMsg($MC);
	} else {
	    $msg = $i.' '.JText::_('JM_USER_UNSUBSCRIBED');
	}
		
        $link = 'index.php?option=com_joomailermailchimpintegration&view=subscribers&type=s&listid='.$listId;
        $this->setRedirect($link, $msg);
    }
	
    function delete()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);

	$emails = JRequest::getVar( 'emails', array(), 'post', 'array' );
	$listId = JRequest::getVar( 'listid', 0, 'post', 'string' );

	$i=0;
	if( isset($emails[0]) && $listId ) {
	    foreach ( $emails as $email ) {
		$email = explode(';', $email);
		$unsubscribe = $MC->listUnsubscribe( $listId, $email[0], true, false, false );
		if( !$MC->errorCode ) $i++;
	    }
	}
		
	if ( $MC->errorCode ) {
	    $msg = MCerrorHandler::getErrorMsg($MC);
	} else {
	    $msg = $i.' '.JText::_('JM_USER_DELETED');
	}
		
        $link = 'index.php?option=com_joomailermailchimpintegration&view=subscribers&type=s&listid='.$listId;
        $this->setRedirect($link, $msg);
    }
	
    function resubscribe()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);

	$listId = JRequest::getVar( 'listid', 0, 'post', 'string' );
	$emails = JRequest::getVar( 'emails', array(), 'post', 'array' );

	$i=0;
	if( isset($emails[0]) && $listId ) {
	    foreach ( $emails as $email ) {
		$email = explode(';', $email);
		$memberInfo = $MC->listMemberInfo( $listId, $email[0]);

		$resubscribe = $MC->listSubscribe( $listId, $email[0], $memberInfo, $memberInfo['email_type'], false, true, false, false  );
		if( !$MC->errorCode ) $i++;
	    }
	}
		
	if ( $MC->errorCode ) {
	    $msg = MCerrorHandler::getErrorMsg($MC);
	} else {
	    $msg = $i.' '.JText::_('JM_USER_RESUBSCRIBED');
	}

	$link = 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations';
        $this->setRedirect($link, $msg);	
    }

    function cancel()
    {
	$msg = JText::_( 'JM_OPERATION_CANCELLED' );
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=templates', $msg );
    }

    function goToLists(){
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations' );
    }
    
}// class
