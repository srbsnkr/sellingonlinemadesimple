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
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');

class plgCommunityJoomlamailer extends CApplications
{

    function onUserDetailsUpdate($user)
    {
	// check if the signup plugin is enabled; if not: return.
	$plugin = & JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');
	if( is_array($plugin) ){
	    return;
	}
	// include MCAPI wrapper
	jimport('joomla.filesystem.file');
	if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
	    return;
	}
	// update user if email has changed
	if( $user->email != $user->emailpass){
	    // include MCAPI wrapper
	    if(!class_exists('joomlamailerMCAPI')) {
		require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
	    }
	    // create the api object
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $api = new joomlamailerMCAPI($MCapi);

	    // get the ID of the mailing list
	    jimport( 'joomla.html.parameter' );
	    $plugin = & JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');
	    $pluginParams = new JParameter( $plugin->params );
	    $listId = $pluginParams->get('listid');

	    // check if the user is subscribed
	    $userlists = $api->listsForEmail($user->emailpass);
	    if( !$userlists || !in_array($listId,$userlists)) {
		return;
	    }

	    $name = explode(' ', JRequest::getVar('name', $user->name) );
	    $fname = $name[0];
	    $lname = '';
	    if( isset($name[1]) ) {
		for( $i=1; $i < count($name); $i++ ){
		    $lname .= $name[$i].' ';
		}
		$lname = trim($lname);
	    }

	    $ip = $this->get_ip_address();

	    $merge_vars = array( 'FNAME' => $fname, 'LNAME' => $lname, 'EMAIL' => $user->email, 'OPTINIP' => $ip );
	    $email_type = '';
	    // submit to MailChimp
	    $api->listUpdateMember($listId, $user->emailpass, $merge_vars, $email_type, true);
	    // update local database entry
	    $db =& JFactory::getDBO();
	    $query = 'UPDATE #__joomailermailchimpintegration SET email = "'.$user->email.'" WHERE email="'.$user->emailpass.'" AND listid = "'.$listId.'"';
	    $db->setQuery($query);
	    $db->query();

	    return;
	}
    }
	
    function get_ip_address() {
	foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
	    if (array_key_exists($key, $_SERVER) === true) {
		foreach (explode(',', $_SERVER[$key]) as $ip) {
		    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
			return $ip;
		    }
		}
	    }
	}
    }

}
