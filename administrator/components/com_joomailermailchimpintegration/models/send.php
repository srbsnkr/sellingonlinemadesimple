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

class joomailermailchimpintegrationsModelSend extends JModel
{

    var $_data;

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }
	
    function getClientDetails()
    {
	$MC	= $this->MC_object();
	$details = $MC->getAccountDetails();

	return $details;
    }
	
    function getDrafts()
    {
	$db = & JFactory::getDBO();
	$query = "SELECT * FROM #__joomailermailchimpintegration_campaigns WHERE sent != 2 ORDER BY `creation_date` DESC";
	$db->setQuery($query);
	$cDetails = $db->loadObjectList();

	return $cDetails;
    }

    function getCampaigns()
    {
	$MC	= $this->MC_object();
	$filters = array( 'status' => 'sent' );
	$campaigns = $MC->campaigns( $filters);
	return $campaigns;
    }

    function getInterestGroupings( $listId=NULL )
    {
	$MC	= $this->MC_object();
	$result = $MC->listInterestGroupings( $listId );
	return $result;
    }
	
    function getMergeVars( $listId=NULL )
    {
	$MC	= $this->MC_object();
	$result = $MC->listMergeVars( $listId );
	return $result;
    }
	
    function getMClists()
    {
	$MC      = $this->MC_object();
	$lists   = $MC->lists();

	return $lists;
    }
	
    function getMergeFields( $listId ){
	$MC = $this->MC_object();
	$result = $MC->listMergeVars( $listId );
	return $result;
    }

    function getAECambraVM(){
	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acctexp'.DS.'admin.acctexp.php')
	   || JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'ambrasubs.php')
	   || JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'admin.virtuemart.php')){
	    return true;
	} else {
	    return false;
	}
    }

}
