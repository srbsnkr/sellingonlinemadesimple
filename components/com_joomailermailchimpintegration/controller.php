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

class joomailermailchimpintegrationController extends JController
{
    function display()
    {
	parent::display();
    }
	
    function edit()
    {
	JRequest::setVar( 'layout', 'form' );
	parent::display();
    }
	
    function save()
    {
	//--Check for request forgeries
	if (version_compare(JVERSION,'1.6.0','ge')) {
	    JRequest::checkToken() or jexit( 'JINVALID_TOKEN' );
	} else {
	    JRequest::checkToken() or jexit( 'Invalid Token' );
	}

	$user = & JFactory::getUser();
	//--get the model
	$model =& $this->getModel();

	//--get data from request
	$post   = JRequest::get('post');
	$listid = JRequest::getVar('listid', '', 'post', 'array', JREQUEST_ALLOWRAW);

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);

	$merge = array();
	$names = explode(' ', $user->name);
	if( $names[0] && $names[1]) {
	    $merge['FNAME'] = $names[0];
	    $merge['LNAME'] = $names[1];
	} else {
	    $merge['FNAME'] = $user->name;
	}
		
	$optin = false;
		
	foreach ( $listid as $list ) {

	    $subscribe = '';
	    $subscribe = (bool)JRequest::getVar( 'subscribe_'.$list, '', 'post', 'int');
	    $is_sub    = (bool)JRequest::getVar( 'is_sub_'.$list, '', 'post', 'int');

	    if ( $subscribe && !$is_sub ) {
//		var_dump($list, $user->email , $merge, $optin);die;
		$result = $MC->listSubscribe( $list, $user->email , $merge, '', $optin, true, false, false );
		$this->db_add( $user->id, $user->email, $list );
	    } else if ( !$subscribe && $is_sub ) {
		$result = $MC->listUnsubscribe( $list, $user->email, false, false, false );
		$this->db_remove( $user->id, $user->email, $list );
	    }
	}

	$msg = JText::_( 'JM_SUBSCRIPTIONS_UPDATED' );

        $itemid = JRequest::getVar( 'itemid', '', 'post', 'string', JREQUEST_ALLOWRAW);
        if ( $itemid ) { $itemid = '&Itemid='.$itemid; } else { $itemid = ''; }

	$link = 'index.php?option=com_joomailermailchimpintegration&view=subscriptions' . $itemid;
	$this->setRedirect($link, $msg);
    }

    function db_add($id, $email, $list_id)
    {
        $db    =& JFactory::getDBO();
        $query = 'INSERT INTO #__joomailermailchimpintegration (userid,email,listid) VALUES ("'.$id.'", "'.$email.'", "'.$list_id.'")';
	$db->setQuery($query);
	$db->query();
    }

    function db_remove($id, $email, $list_id)
    {
        $db    =& JFactory::getDBO();
        $query = 'DELETE FROM #__joomailermailchimpintegration WHERE userid = "'.$id.'" AND email= "'.$email.'" AND listid= "'.$list_id.'"';
	$db->setQuery($query);
	$db->query();
    }
	
    function cancel()
    {
	$itemid = JRequest::getVar( 'itemid', '', 'post', 'string', JREQUEST_ALLOWRAW);
	if ( $itemid ) { $itemid = '&Itemid='.$itemid; } else { $itemid = ''; }

	$link = 'index.php?option=com_joomailermailchimpintegration&view=subscriptions' . $itemid;
	$this->setRedirect($link);
    }
	
	
	
}
