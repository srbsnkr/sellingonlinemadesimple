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

if(!class_exists('joomlamailerMCAPI')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
}

class JoomailerMailchimpSignupModelJoomailerMailchimpSignup extends JModel
{
    function register_save()
    {
	$option = JRequest::getCmd( 'option' );
	$extension = JRequest::getVar('component');
	$extension = (version_compare(JVERSION,'1.6.0','ge') && $extension == 'com_user'    ) ? 'com_users' : $extension;
	$db = & JFactory::getDBO();
	$user = & JFactory::getUser();
	$lang = & JFactory::getLanguage();
	$task = JRequest::getVar('oldtask');

	//com_user
	if ($extension == 'com_user' || $extension == 'com_users') {

	    if (version_compare(JVERSION,'1.6.0','ge')){
		$jform = JRequest::getVar('jform');
		$name = $jform['name'];
		$email_address = $jform['email1'];
	    } else {
		$name = JRequest::getVar('name');
		$email_address = JRequest::getVar('email');
	    }

	    $name = explode(' ',$name);
	    $email_address = $email_address;
	    $controller = '/controller.php';
	    $cname = (version_compare(JVERSION,'1.6.0','ge')) ? 'UsersController' : 'UserController';
	    $lang->load($extension,JPATH_BASE);

	//Jomsocial
	} elseif ($extension == 'com_community') {

	    if($user->email) {
		$email_address = $user->email;
		$name = explode(' ',$user->name);
		$_POST['view'] = 'profile';
	    } else {
		$query = 'SELECT token FROM #__community_register_auth_token WHERE auth_key="'.$_POST['authkey'].'"';
		$db->setQuery($query);
		$token = $db->loadResult();
		$query = 'SELECT name, email FROM #__community_register WHERE token="'.$token.'"';
		$db->setQuery($query);
		$details = $db->loadAssocList();
		$name = explode(' ',$details[0]['name']);
		$email_address = $details[0]['email'];
	    }

	    $cntrllr = JRequest::getVar('cntrllr');
	    $controller = '/controllers/'.$cntrllr.'.php';
	    $cname = 'Community'.strtoupper($cntrllr[0]).substr($cntrllr,1).'Controller';
	    $this->_name = str_replace('com_','',$extension);

	    require_once (JPATH_SITE.DS.'components'.DS.$extension.DS.'controllers'.DS.'controller.php');
	    require_once (JPATH_SITE.DS.'components'.DS.$extension.DS.'libraries'.DS.'core.php');
	    require_once (JPATH_SITE.DS.'components'.DS.$extension.DS.'libraries'.DS.'template.php');
	    require_once (JPATH_SITE.DS.'components'.DS.$extension.DS.'views'.DS.'views.php');
	    require_once (JPATH_SITE.DS.'components'.DS.$extension.DS.'views'.DS.'register'.DS.'view.html.php');
	    require_once (JPATH_SITE.DS.'components'.DS.$extension.DS.'views'.DS.'profile'.DS.'view.html.php');
	    $lang->load($extension,JPATH_BASE);
	    $_POST['option'] = $extension;
	    $_POST['task'] = $task;
	    $view = JRequest::getCmd( 'view');

	//Community Builder
	} elseif ($extension == 'com_comprofiler') {

	    $name[0] = JRequest::getVar('firstname');
	    $name[1] = JRequest::getVar('lastname');
	    $name = explode(' ',JRequest::getVar('name'));
	    $email_address = JRequest::getVar('email');
	    $controller = '/comprofiler.php';
	    $cname = 'Comprofiler';
	    $_POST['option'] = $extension;
	    $GLOBALS['_JREQUEST']['option'] = array('DEFAULTCMD0'=>'com_comprofiler');
	    $cbtask = JRequest::getVar('oldtask');
	} elseif ($extension == 'com_virtuemart') {
	    $name[0] = JRequest::getVar('first_name');
	    $name[1] = JRequest::getVar('middle_name');
	    $name[2] = JRequest::getVar('last_name');
	    $email_address = JRequest::getVar('email');
	}
	$email_address_old = JRequest::getVar('oldEmail', $email_address);

	$fname = $name[0];
	$lname = '';
	if( isset($name[1]) ) {
	    for( $i=1; $i < count($name); $i++ ){
		$lname .= $name[$i].' ';
	    }
	    $lname = trim($lname);
	}

	//Create the api object
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$api = new joomlamailerMCAPI($MCapi);

	//Get the ID of the mailing list
	jimport( 'joomla.html.parameter' );
	$plugin = & JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');
	$pluginParams = new JParameter( $plugin->params );
	$listId = $pluginParams->get('listid');

	//Check if the user is already logged in and subscribed
	if($user->email){
	    $userlists = $api->listsForEmail($email_address_old);
	    if($userlists && in_array($listId,$userlists)) {
		$sub = 1;
	    }
	}

	//User wishes to subscribe/update interests
	if (isset($_POST['newsletter'])) {

	    $double_optin = false;
	    $update_existing = false;
	    $replace_interests = false;
	    $send_welcome = false;

	    //Get merge vars from API
	    $fields = $api->listMergeVars( $listId );
	    $fieldids = $pluginParams->get('fields');
	    $key = 'tag';
	    $val = 'name';

	    //Get interests from API
	    $interests = $api->listInterestGroupings( $listId );
	    $interestids = $pluginParams->get('interests');
	    $groupings = array();
	    $merges = array();

	    if ($extension == 'com_user' || $extension == 'com_users') {

		//Default registration
		if ($fields) {
		    foreach ($fields as $f) {
			if (isset($_POST['mf_'.$f['tag']])) {
			    $val = $_POST['mf_'.$f['tag']];
			    $merges[$f['tag']] = $val;
			}
		    }
		}

		if ($interests) {
		    foreach ($interests as $i) {
			if ($_POST['interest_'.$i['id']]) {
			    $groups = '';
			    if (is_array($_POST['interest_'.$i['id']])) {
				foreach ($_POST['interest_'.$i['id']] as $g) {
				    //var_dump($i['groups']);die;
				    foreach ($i['groups'] as $gg) {
					if ($g == $gg['bit']) {
					    $groups .= $gg['name'].',';
					}
				    }
				}
				$groups = substr($groups,0,-1);
				$groupings[$i['name']] =  array('name' => $i['name'], 'id' => $i['id'], 'groups' => $groups);

			    } else {
				foreach ($i['groups'] as $gg) {
				    if ($_POST['interest_'.$i['id']] == $gg['bit']) {
					$groups .= $gg['name'];
				    }
				}
				$groupings[$i['name']] =  array('name' => $i['name'], 'id' => $i['id'], 'groups' => $groups);
			    }
			}
		    }
		}

	    } elseif ($extension == 'com_comprofiler' || $extension == 'com_community' || $extension == 'com_virtuemart') {

		//Get custom fields
		$query = 'SELECT dbfield, grouping_id as gid, type, framework FROM #__joomailermailchimpintegration_custom_fields WHERE listID="'.$listId.'"';
		$db->setQuery($query);
		$customfields = $db->loadAssocList();

		if ($customfields) {
		    //loop over groupings
		    if ($interests) {
			foreach ($interests as $i) {
			    foreach ($customfields as $cf) {
				if($cf['type']=='group') {
				    if ($i['id'] == $cf['gid'] ) {
					$groups = '';
					if (  ( $extension == 'com_comprofiler' && $cf['framework'] == 'CB')
					    || ($extension == 'com_virtuemart'  && $cf['framework'] == 'VM') ){
					    $field = $_POST[$cf['dbfield']];
					} else {
					    if (isset ($_POST['field'.$cf['dbfield']])) {
						$field = $_POST['field'.$cf['dbfield']];
					    }
					}
					if ( isset($field) && is_array($field)) {
					    foreach ($field as $g) {
						foreach ($i['groups'] as $gg) {
						    if ($g == $gg['name']) {
							$groups .= $gg['name'].',';
						    }
						}
					    }
					    $groups = substr($groups,0,-1);
					} else {
					    foreach ($i['groups'] as $gg) {
						if ( isset($field) && $field == $gg['name']) {
						    $groups .= $gg['name'];
						}
					    }
					}
					$groupings[$i['name']] =  array('name' => $i['name'], 'id' => $i['id'], 'groups' => $groups);
				    }
				}
			    }
			}
		    }
		}

		//loop over merge vars
		if($fields) {
		    foreach($fields as $f) {
			foreach($customfields as $cf) {
			    if($cf['type'] == 'field') {
				if($f['tag'] == strtoupper($cf['gid'])) {
				    if (   ($extension == 'com_comprofiler' && $cf['framework'] == 'CB')
					|| ($extension == 'com_virtuemart' && $cf['framework'] == 'VM') ) {
					if($f['field_type'] == 'date') {
					    if($extension == 'com_virtuemart'){
						$valDay = $_POST['birthday_selector_day'];
						$valMonth = $_POST['birthday_selector_month'];
						$valYear = $_POST['birthday_selector_year'];
						$val = $valMonth.'/'.$valDay.'/'.$valYear;
					    } else {
						$val = $_POST[$cf['dbfield']];
					    }
					    $merges[$f['tag']] = substr($val,3,2).'-'.substr($val,0,2).'-'.substr($val,6,4);
					} else {
					    $val = $_POST[$cf['dbfield']];
					    $merges[$f['tag']] = $val;
					}
				    } else {
					if (isset ($_POST['field'.$cf['dbfield']])) {
					    $val = $_POST['field'.$cf['dbfield']];
					    if($f['field_type'] == 'date') {
						$merges[$f['tag']] = $val[2].'-'.$val[1].'-'.$val[0];
					    } else {
						$merges[$f['tag']] = $val;
					    }
					}
				    }
				}
			    }
			}
		    }
		}
	    }

	    //If this is a new user then subscribe the user at activation
	    if(!$user->id) {

		$merges_string = '';

		if($merges) {
		    foreach($merges as $k => $v) {
			$merges_string .= "name=".$k."\n";
			if(is_array($v)) {
			    $merges_string .= "value=";
			    foreach($v as $vv) {
				$merges_string .= $vv."||";
			    }
			} else {
			    $merges_string .= "value=".$v;
			}
			$merges_string .= "\n\n";
		    }
		}

		$groupings_string = '';
		foreach($groupings as $g) {
		    $groupings_string .= 'name='.$g['name']."\n";
		    $groupings_string .= 'id='.$g['id']."\n";
		    $groupings_string .= 'groups='.$g['groups']."\n".'||'."\n";
		}
		$groupings_string = substr($groupings_string,0,-3);
		$merges_string = substr($merges_string,0,-2);

		$query = "INSERT INTO #__joomailermailchimpsignup (fname,lname,email,groupings,merges)
			    VALUES ('".$fname."','".$lname."','".$email_address."','".$groupings_string."','".$merges_string."')";
		$db->setQuery($query);
		$db->query();

	    //Otherwise workout whether to update or subscribe the user
	    } else {

		//Get the users ip address
		$ip = $this->get_ip_address();

		$merge_vars = array('FNAME'=>$fname, 'LNAME'=>$lname,'INTERESTS'=>'','GROUPINGS' => $groupings, 'OPTINIP'=>$ip);
		$merge_vars = array_merge($merge_vars,$merges);
		$email_type = '';
		if(!isset($sub)) {
		    //Subscribe the user
		    $retval = $api->listSubscribe( $listId, $email_address, $merge_vars, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome);
		    $query = 'INSERT INTO #__joomailermailchimpintegration VALUES ("", '.$user->id.', "'.$email_address.'", "'.$listId.'")';
		    $db->setQuery($query);
		    $db->Query();
		} else {
		    //Update the users subscription
		    // email address changed in CB?
		    if( ( $extension == 'com_comprofiler' || $extension == 'com_user' || $extension == 'com_users' )
			&& $email_address != $email_address_old
		    ){
			// update local database entry
			$query = 'UPDATE #__joomailermailchimpintegration SET email = "'.$email_address.'" WHERE email="'.$email_address_old.'" AND listid = "'.$listId.'"';
			$db->setQuery($query);
			$db->query();
			// add new email address to merge vars array
			$merge_vars['EMAIL'] = $email_address;
			$email_address = $email_address_old;
		    }
		    $retval = $api->listUpdateMember($listId, $email_address, $merge_vars, $email_type, true);
		}

	    }

	//User wishes to unsubscribe
	} elseif (!isset($_POST['newsletter']) && isset($sub)) {
	    $api->listUnsubscribe($listId, $email_address, false, false, false);
	    $query = 'DELETE FROM #__joomailermailchimpintegration WHERE email="'.$email_address.'" AND listid = "'.$listId.'"';
	    $db->setQuery($query);
	    $db->query();
	}
	if ($api->errorCode && $api->errorCode != 215 && $api->errorCode != 211){
	    echo "Unable to load listSubscribe()!\n";
	    echo "\tCode=".$api->errorCode."\n";
	    echo "\tMsg=".$api->errorMessage."\n";
	} else {

	    if(		$option == 'com_user'
		    ||  $option == 'com_users'
		    ||  $extension == 'com_virtuemart'
		    || ($extension == 'com_community' && $task != 'edit' )
		    ||  $extension == 'com_comprofiler' ){
		// we're done at this point
		return;
	    } else if ($extension !='com_comprofiler') {
		if( $extension != 'com_community' ){
		    if(version_compare(JVERSION,'1.6.0','ge')){
			JRequest::setVar('task', $task);
			JRequest::setVar('option', 'com_users');
			$task = explode('.',$task);
			$controllerpath = JPATH_SITE.DS.'components/com_users/controller.php';
			require_once($controllerpath);
			$controllerpath = JPATH_SITE.DS.'components/com_users/controllers/'.$task[0].'.php';
			require_once($controllerpath);
			$userController = JController::getInstance('Users',array('base_path'=>JPATH_ROOT.DS.'components'.DS.$extension, 'name' => str_replace('com_','',$extension)));
			$task = $task[1];
		    } else {
			$controllerpath = JPATH_SITE.DS.'components/'.$extension.$controller;
			require_once($controllerpath);
			$userController = new $cname(array('base_path'=>JPATH_ROOT.DS.'components'.DS.$extension, 'name' => $view));
			$this->_name = str_replace('com_','',$extension);
		    }
		} else {
		    $controllerpath = JPATH_SITE.DS.'components/'.$extension.$controller;
		    require_once($controllerpath);
		    $userController = new $cname(array('base_path'=>JPATH_ROOT.DS.'components'.DS.$extension, 'name' => $view));
		    $this->_name = str_replace('com_','',$extension);
		}

		$userController->execute($task);
		$userController->redirect();
	    } else {
		$controllerpath = JPATH_SITE.DS.'components/'.$extension.$controller;
		require_once($controllerpath);
		if($cbtask == 'saveUserEdit') {
		    userSave( $_POST['option'], $_POST['id'] );
		} else {
		    saveRegistration( $_POST['option'] );
		}
	    }
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
