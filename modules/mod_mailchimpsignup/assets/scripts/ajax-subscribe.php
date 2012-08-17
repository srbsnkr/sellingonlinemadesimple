<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define ('ABSOLUTE_PATH', dirname(__FILE__) );
define ('RELATIVE_PATH', 'modules'.DS.'mod_mailchimpsignup'.DS.'assets'.DS.'scripts');
define ('JPATH_BASE', str_replace(RELATIVE_PATH, "", ABSOLUTE_PATH));
require_once ( JPATH_BASE . DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE . DS.'includes'.DS.'framework.php' );
// JSON support in case of PHP < 5.2
require_once ( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'jsonwrapper'.DS.'jsonwrapper.php' );

$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();

$language = JFactory::getLanguage();
$language->load('mod_mailchimpsignup');
jimport('joomla.filesystem.file');
if(JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
}

$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
$MCapi  = $params->get( $paramsPrefix.'MCapi' );
$MC = new joomlamailerMCAPI($MCapi);

$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
$elements = json_decode($elements);

$listId = $elements->listid;
$email = $elements->EMAIL;
$userId = $elements->userId;
if(isset($elements->FNAME)){
    $merge['FNAME'] = $elements->FNAME;
}
if(isset($elements->LNAME)){
    $merge['LNAME'] = $elements->LNAME;
}
$merge['OPTINIP'] = $elements->ip;

$thankyouMsg = $elements->thankyouMsg;
$updateMsg = $elements->updateMsg;

$merges = $elements->merges;
$mergesArray = array_filter(explode('|', $merges));
foreach($mergesArray as $m){
    if( stristr( $m, '#*#' ) ){
	$mArray = explode('#*#', $m);
	$mergeVars[$mArray[0]][$mArray[1]] = $elements->{$m};
    } else if( stristr( $m, '*#*' ) ){
	$mArray = explode('*#*', $m);
	if( isset($mergeVars[$mArray[0]]) ){
	    $mergeVars[$mArray[0]] .= '-'.$elements->{$m};
	} else {
	    $mergeVars[$mArray[0]] = $elements->{$m};
	}
    } else if( stristr( $m, '***' ) ){
	$mArray = explode('***', $m);
	$mergeVars[$mArray[0]][$mArray[1]] = $elements->{$m};
    } else {
	$mergeVars[$m] = $elements->{$m};
    }
}

$groups = $elements->groups;
$groupsArray = array_unique(array_filter(explode('|', $groups)));

foreach($groupsArray as $g){
    if($elements->{$g}){
	if($elements->{$g}[strlen($elements->{$g})-1] == ',') { $elements->{$g} = substr($elements->{$g}, 0, -1); }
	$mergeVars['GROUPINGS'][] = array( 'id' => $g, 'groups' => $elements->{$g});
    }
}

$userlists = $MC->listsForEmail($email);
if($userlists && in_array($listId,$userlists)) {
    $updated = true;
} else {
    $updated = false;
}

$subscribe = $MC->listSubscribe( $listId, $email, $mergeVars, 'html', true, true, true, false );

if ( $MC->errorCode ) {
	$response['html'] = $MC->errorMessage;
	$response['error'] = true;
} else {
	$db = & JFactory::getDBO();
	$query = 'INSERT INTO #__joomailermailchimpintegration VALUES ("", "'.$userId.'", "'.$email.'", "'.$listId.'")';
	$db->setQuery($query);
	$db->Query();
	$response['html'] = ($updated) ? $updateMsg : $thankyouMsg;
	$response['error'] = false;
}

echo json_encode( $response );
