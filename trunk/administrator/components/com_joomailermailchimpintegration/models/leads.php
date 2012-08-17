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

class joomailermailchimpintegrationsModelLeads extends JModel
{
    function cm_object() 
    {
        $params    =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
        $subdomain = $params->get( 'subdomain' );
        $domain    = $params->get( 'domain' );
        $username  = $params->get( 'username' );
        $pw        = $params->get( 'pw' );

        $cm  = new CampaignMonitor(  );
        $api = $cm->userGetApiKey( 'http://'.$subdomain.'.'.$domain, $username, $pw );
        $cm  = new CampaignMonitor( $api['anyType'] );

        return $cm;
    }

	function getData()
	{
        $cm				  = $this->cm_object();
        $subscriberClicks = $cm->campaignGetSubscriberClicks( JRequest::getString('cid','','get') );
        if(isset($subscriberClicks['anyType']['SubscriberClick'])) {
			$subscriberClicks = $subscriberClicks['anyType']['SubscriberClick'];
		}
		
		// make sure the array looks the same with one entry as with several entries
		if ( !isset($subscriberClicks[0]) && isset($subscriberClicks) ) {
			$data = $subscriberClicks;
			$subscriberClicks = '';
			$subscriberClicks[0] = $data;
		}

		$result = array();
		for($i=0;$i<count($subscriberClicks);$i++){
			
			$result[$i] = new stdClass();
			$result[$i]->email = $subscriberClicks[$i]['EmailAddress'];
			$result[$i]->list  = $subscriberClicks[$i]['ListID'];
			
			// make sure the array looks the same with one entry as with several entries
			if ( !isset($subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink'][0]) && 
				isset($subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink']) ) {
				$data = $subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink'];
				$subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink'] = '';
				$subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink'][0] = $data;
			}
			$result[$i]->links = array();
			$clicks = 0;
			foreach( $subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink'] as $link){
				$result[$i]->links[] = $link['Link'];
				$clicks += $link['Clicks'];
			}
			$result[$i]->clickedLinks = count($subscriberClicks[$i]['ClickedLinks']['SubscriberClickedLink']);
			$result[$i]->clicks = $clicks;
		}
//		$result = array_multisort($result, SORT_DESC);

		usort($result, array($this,"cmp"));

		return $result;
  //       return $subscriberClicks;
	}
	
	function getUserdata( $email ){
		
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__users WHERE email= "'.$email.'"';
		$db->setQuery($query);
		$result = $db->loadObjectList();
		
		if(isset($result[0])){
			return $result[0];
		} else {
			return false;
		}
	}
	
	function getUserdataAPI( $email ){
		
		$cm = $this->cm_object();
		$lists = $this->getLists();
		foreach($lists['anyType']['List'] as $listID){
			if( isset($result) && isset($result['anyType']['Code']) && $result['anyType']['Code'] != 203 ) break;
			$result = $cm->subscriberGetSingleSubscriber( $listID['ListID'], $email );
		}
		
		return $result;
	}
	
	function getLists(){
		
		$cm		   = $this->cm_object();
		$params    =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$subdomain = $params->get( 'subdomain' );
		$domain    = $params->get( 'domain' );
		$username  = $params->get( 'username' );
		$pw        = $params->get( 'pw' );
		$api	   = $cm->userGetApiKey( 'http://'.$subdomain.'.'.$domain, $username, $pw );
		$clients   = $cm->userGetClients($api['anyType']);
		$client_id = $clients['anyType']['Client']['ClientID'];
		
		$results = $cm->clientGetLists( $client_id );
		// make sure the array looks the same with one entry as with several entries
		if ( !isset($results['anyType']['List'][0]) && isset($results['anyType']['List']) ) {
			$data = $results['anyType']['List'];
			$results['anyType']['List'] = '';
			$results['anyType']['List'][0] = $data;
		}
		
		return $results;
	}
	
	
	function cmp($a, $b) 
	{
	   global $results;
	
	   $pos1 = $a->clickedLinks;
	   $pos2 = $b->clickedLinks;

	   if ($pos1==$pos2)
	       return 0;
	   else
	      return ($pos1 > $pos2 ? -1 : 1);
	
	}




}  // end class
