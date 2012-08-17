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

/**
 * joomailermailchimpintegration Model
 *
 * @package    joomailermailchimpintegration
 * @subpackage Models
 */
class joomailermailchimpintegrationsModelCampaignlist extends JModel
{

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

    function getData()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$archiveDir = $params->get( $paramsPrefix.'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' );
	$filter = JRequest::getVar('filter_status', 'sent', '', 'string');
	$folder_id = JRequest::getVar('folder_id', 0, 'post', 'int');

	$and = '';
	if($folder_id && $filter == 'save') {
	    $and = 'AND folder_id='.$folder_id;
	} elseif ($folder_id) {
	    $filters['folder_id'] = $folder_id;
	}

	if($filter=='save'){

	    $db = & JFactory::getDBO();
	    $query = "SELECT * FROM #__joomailermailchimpintegration_campaigns WHERE sent != 1 ".$and." ORDER BY `creation_date` DESC ";
	    $db->setQuery($query);
	    $data = $db->loadObjectList();
	    $campaigns = array();
	    $i=0;
	    if($data){
		foreach($data as $dat){
		    $campaigns[$i]['id'] = $dat->creation_date;
		    $campaigns[$i]['title'] = $dat->name;
		    $campaigns[$i]['subject'] = $dat->subject;
		    $campaigns[$i]['creation_date'] = $dat->creation_date;
		    $campaign_name_ent = htmlentities($dat->name);
		    $campaign_name_ent = str_replace(' ','_',$campaign_name_ent);
		    $link = JURI::root() . (substr($archiveDir,1)) . "/" . $campaign_name_ent.".html";
		    $campaigns[$i]['archive_url'] = $link;
		    $i++;
		}
	    } else {
		$campaigns = false;
	    }

	} else {
	    $filters['status'] = $filter;
	    //$filters = array('status' => $filter);
	    $MC	= $this->MC_object();
	    $campaigns = $MC->campaigns( $filters );
	}

	return $campaigns; 
    }

    function getCampaignStats($campaign_id)
    {
	$MC	= $this->MC_object();
	$results = $MC->campaignStats( $campaign_id );

	return $results;
    }

    function getPagination()
    {
	// Load the content if it doesn't already exist

	$mainframe =& JFactory::getApplication();
	jimport('joomla.html.pagination');

	$limit = $mainframe->getUserStateFromRequest( 'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );

	$limitstart	= $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

	$this->_pagination = new JPagination( count($this->getData()), $limitstart, $limit );

	return $this->_pagination;
    }

    function getFolders()
    {
	$MC = $this->MC_object();
	$folders = $MC->campaignFolders();

	if(!$folders) {
	    return array();
	} else {
	    return $folders;
	}
    }

}  // end class
