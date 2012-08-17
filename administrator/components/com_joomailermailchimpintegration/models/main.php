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

class joomailermailchimpintegrationsModelMain extends JModel
{
	
    var $_data;

    function __construct()
    {
	parent::__construct();
    }

    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

    function setupInfo()
    {
	$db = & JFactory::getDBO();
	$query = "SELECT value FROM #__joomailermailchimpintegration_misc WHERE type = 'setup_info'";
	$db->setQuery( $query );
	$showInfo = $db->loadResult();
	$hide = ($showInfo) ? 'style="display:none;"' : '';

	$setupInfo = ( version_compare(JVERSION,'1.6.0','ge') ) ? 'JM_SETUP_INFO_16' : 'JM_SETUP_INFO';
	$msg = '<div id="setupInfo" '.$hide.'>
		<script type="text/javascript">var baseUrl = "'.JURI::base().'";</script>
		<dl id="system-message">
		    <dt class=""></dt>
		    <dd class=" message fade">

			    <ul>
				<li><div style="float:right;"><a href="javascript:hideSetupInfo()">'.JText::_( 'JM_HIDE' ).'</a></div>'.JText::_( $setupInfo ).'</li>
			    </ul>
		    </dd>
		</dl>
		</div>';

	return $msg;
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
	    $query = "SELECT * FROM #__joomailermailchimpintegration_campaigns WHERE `sent` = 0 ORDER BY `creation_date` DESC LIMIT 5";
	    $db->setQuery($query);
	    $cDetails = $db->loadObjectList();

	    return $cDetails;
    }

    function getCampaigns()
    {
	    $MC	= $this->MC_object();
	    $campaigns = $MC->campaigns( '', 0, 25);

	    return $campaigns;
    }

    function getCampaignStats($campaign_id)
    {
	    $MC	= $this->MC_object();
	    $results = $MC->campaignStats( $campaign_id );

	    return $results;
    }

    function getChimpChatter(){
	    $MC	= $this->MC_object();
	    $result = $MC->chimpChatter();

	    return $result;
    }

}
