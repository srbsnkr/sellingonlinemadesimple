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

class joomailermailchimpintegrationsModelCampaigns extends JModel
{

    var $_data;

    function mc_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$mc = new joomlamailerMCAPI($MCapi);
	return $mc;
    }

    function getClientDetails()
    {
	$MC	= $this->MC_object();
	$details = $MC->getAccountDetails();
	return $details;
    }

    function getCampaigns($filters=null, $page=0, $limit=5)
    {
	$mc = $this->mc_object();
	$results = $mc->campaigns($filters, $page, $limit );

	return $results;
    }

    function getCampaignStats($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignStats($cid);

	return $results;
    }

    function getOpens($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignOpenedAIM($cid);

	return $results;
    }

    function getClicksAIM($cid, $url)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignClickDetailAIM($cid, $url);
	uasort($results, 'cmp');

	return $results;
    }

    function getClicks($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignClickStats($cid);
	arsort($results);

	return $results;
    }

    function getShareReport($cid, $title)
    {
	$opts = array();
	$opts['secure'] = false;
	$opts['header_type'] = 'text';
	$opts['header_data'] = $title;
	$opts['password'] = '';
	$mc = $this->mc_object();
	$results = $mc->campaignShareReport($cid, $opts);
	return $results;
    }

    function getCampaignData($cid)
    {
	$db  =& JFactory::getDBO();
	$query = "SELECT * FROM #__joomailermailchimpintegration_campaigns WHERE `cid` = '".$cid."'";
	$db->setQuery($query);
	$result = $db->loadObjectList();
	if(!$result) {
	    $result = $this->mc_object()->campaigns(array('campaign_id' => $cid));
	}
	return $result;
    }

    function getUserDetails($email)
    {
	$db  =& JFactory::getDBO();
	$query = "SELECT id FROM #__users WHERE `email` = '".$email."'";
	$db->setQuery($query);
	$id = $db->loadResult();

	if($id){
	    $user =& JFactory::getUser($id);
	} else {
	    $user = new stdClass;
	    $user->id = '';
	    $user->name = '';
	    $user->email = '';
	}

	return $user;
    }

    function getAbuse($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignAbuseReports($cid);

	return $results;
    }

    function getUnsubscribes($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignUnsubscribes($cid);

	return $results;
    }

    function getAdvice($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignAdvice($cid);

	return $results;
    }

    function getEmailDomainPerformance($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignEmailDomainPerformance($cid);

	return $results;
    }

    function getClickStats($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignClickStats($cid);

	return $results;
    }

    function getGeoOpens($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignGeoOpens($cid);

	return $results;
    }

    function getHardBounces($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignHardBounces($cid);

	return $results;
    }

    function getSoftBounces($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignSoftBounces($cid);

	return $results;
    }

    function getCampaignEmailStatsAIMAll($cid, $page=0, $limit=1000)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignEmailStatsAIMAll($cid, (int)$page, (int)$limit);

	return $results;
    }

    function getFolders()
    {
	$MC = $this->MC_object();
	$folders = $MC->campaignFolders();

	if($folders) {
	    return $folders;
	} else {
	    return array();
	}
    }
	
    function getGeoStats($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignGeoOpens($cid);
	if(is_array($results)){
	    uasort($results, 'cmpGeo');
	}
	return $results;
    }

    function getTwitterStats($cid)
    {
	$mc = $this->mc_object();
	$results = $mc->campaignEepUrlStats($cid);

	return $results;
    }
	
    function getPalettes( $hex = false, $keyword = false )
    {
	$runs = ( $hex || $keyword ) ? 1 : 3;
	$colors = array();
	for($i=0;$i<$runs;$i++){

	    $curl = curl_init();
	    if( !$hex && !$keyword ){
		$url = "http://www.colourlovers.com/api/palettes/random?format=json";
	    } else {
		$url = "http://www.colourlovers.com/api/palettes?format=json";
		if($hex){
		    $url .= "&hex=".$hex;
		}
		if($keyword){
		    $url .= "&keywords=".$keyword;
		}
	    }

	    curl_setopt($curl,CURLOPT_URL, $url);
	    curl_setopt($curl,CURLOPT_HEADER,false);
	    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	    $xml = curl_exec($curl);

	    if( !$hex && !$keyword ){
		$colors[] = json_decode($xml);
	    } else {
		$result = json_decode($xml);
		for($i=0;$i<count($result);$i++){
		    $colors[] = array($result[$i]);
		}

	    }
	    curl_close($curl);
	}
		
	return $colors;
    }

}// class

function cmp($a, $b) {
    if ($a["clicks"] == $b["clicks"]) {
	return 0;
    }
    return ($a["clicks"] > $b["clicks"]) ? -1 : 1;
}
function cmpGeo($a, $b) {
    if ($a["opens"] == $b["opens"]) {
	return 0;
    }
    return ($a["opens"] > $b["opens"]) ? -1 : 1;
}

