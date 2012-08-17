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

jimport( 'joomla.application.component.view');

class joomailermailchimpintegrationsViewSend extends JView
{
	
    function display($tpl = null)
    {

	JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_SEND_CAMPAIGN' ), 'MC_logo_48.png' );

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MCauth = new MCauth();

	if ( !$MCapi ) {
	    $user =& JFactory::getUser();
	    if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
		|| !version_compare(JVERSION,'1.6.0','ge') ) {
		JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
		JToolBarHelper::spacer();
	    }
	} else {

	    if( !$MCauth->MCauth() ) {
		$user =& JFactory::getUser();
		if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
		    || !version_compare(JVERSION,'1.6.0','ge') ) {
		    JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
		    JToolBarHelper::spacer();
		}
	    } else {
		$AECambraVM =& $this->get('AECambraVM');
		$this->assignRef('AECambraVM', $AECambraVM);
		if($AECambraVM){
		    JToolBarHelper::custom( 'syncHotness', 'hotness_32', 'hotness_32', 'Sync Hotness', false, false );
		    JToolBarHelper::spacer();
		}

		if(JRequest::getVar('campaign', 0)){
		    JToolBarHelper::custom( 'send', 'send', 'send', 'JM_SEND', false, false );
		    JToolBarHelper::spacer();
		}

		// Get data from the model
		$drafts =& $this->get( 'Drafts');
		$this->assignRef('drafts', $drafts);

		$campaigns =& $this->get( 'Campaigns');
		$this->assignRef('campaigns', $campaigns);

		$clientDetails =& $this->get( 'ClientDetails');
		$this->assignRef('clientDetails', $clientDetails);

		$MClists =& $this->get( 'MClists');
		$this->assignRef('MClists', $MClists);
	    }
	}

        parent::display($tpl);
    }
}
