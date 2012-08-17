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

class checkPermissions {
	
    function check(){

	jimport('joomla.filesystem.folder');

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$archiveDir = $params->get( 'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' );
	$archiveDir = ($archiveDir[0] == '/') ? $archiveDir : '/'.$archiveDir;
	$archiveDir = (substr( $archiveDir, -1) == '/') ? substr($archiveDir, 0, -1) : $archiveDir;

	if( $archiveDir != $params->get('archiveDir') && $params->get( 'MCapi' ) ){
	    $db =& JFactory::getDBO();
	    $query = "SELECT params FROM #__components WHERE ".$db->nameQuote('option')." = 'com_joomailermailchimpintegration' AND ".$db->nameQuote('parent')." = 0";
	    $db->setQuery( $query );
	    $currentParams = $db->loadResult();
	    $currentParams = explode("\n", $currentParams);
	    foreach( $currentParams as $cp){
		$cp = explode('=', $cp, 2);
		$newParams[ $cp[0] ] = (isset($cp[1]))?$cp[1]:'';
	    }
	    $newParams[ 'archiveDir' ] = $archiveDir; 
	    foreach($newParams as $k => $v){
		$newParamsArray[] = "$k=$v";
	    }
	    $newParams = implode("\n", $newParamsArray);

	    $query = "UPDATE #__components SET ".$db->nameQuote('params')." = '".$newParams."' WHERE ".$db->nameQuote('option')." = 'com_joomailermailchimpintegration' AND ".$db->nameQuote('parent')." = 0";
	    $db->setQuery($query);
	    $db->query();
	}

	if( !JFolder::exists( JPATH_SITE . $archiveDir ) ){
	    $msg  = '<table width="100%"><tr><td align="left" valign="center" colspan="6">';
	    $msg .= '<div style="border: 2px solid #ff0000; padding: 10px; margin: 0 0 1em 0;">';
	    $msg .= '<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/warning.png" align="left"/>';
	    $msg .= '<span style="padding-left: 10px; line-height: 28px;">';
	    $msg .= JText::_('JM_INVALID_ARCHIVE_DIRECTORY').': '.$archiveDir;
	    $msg .= '</span>';
	    $msg .= '</div>';
	    $msg .= '</td></tr>';
	    $msg .= '</table>';

	    return $msg;
	}
	$archiveDir = JPATH_SITE . $archiveDir;

	$isWritable = false;
	$filename = JPATH_SITE.DS."tmp".DS."test.xyz";
	$handle   = @fopen( $filename , "w+");
	if($handle){
	    $isWritable = true;
	    @fclose($handle);
	    @unlink(JPATH_SITE.DS."tmp".DS."test.xyz");
	}
	if($isWritable){
	    $filename = $archiveDir .DS. "test.xyz";
	    $handle   = @fopen( $filename , "w+");
	    if($handle){
		$isWritable = true;
		@fclose($handle);
		@unlink($archiveDir .DS. "test.xyz");
	    } else {
		$isWritable = false;
	    }
	}

	if($isWritable){
	    $msg  = '';
	} else {
	    $msg  = '<table width="100%"><tr><td align="left">';
	    $msg .= '<div style="border: 2px solid #ff0000; padding: 10px; margin: 0 0 1em 0;">';
	    $msg .= '<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/warning.png" align="left"/>';
	    $msg .= '<span style="padding-left: 10px; line-height: 28px;">';
	    $msg .= JText::sprintf( 'JM_PERMISSIONS_ERROR_GLOBAL', $params->get( 'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' ) );
	    $msg .= '</span>';
	    $msg .= '</div>';
	    $msg .= '</td></tr>';
	    $msg .= '</table>';
	}

	return $msg;
    }
}
