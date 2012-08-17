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

class joomailermailchimpintegrationsViewSubscribers extends JView
{

    function display($tpl = null)
    {
	$mainframe =& JFactory::getApplication();
	$option = JRequest::getCmd('option');

	$limit	    = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

	JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_SUBSCRIBERS' ), 'MC_logo_48.png' );

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MCauth = new MCauth();

	if( $MCapi && $MCauth->MCauth() ) {
	    JToolBarHelper::custom( 'goToLists', 'lists', 'lists', 'JM_LISTS', false, false );
	    JToolBarHelper::spacer();
	    if ( JRequest::getVar('type') == 's' ){
		JToolBarHelper::custom( 'unsubscribe', 'unsubscribe', 'unsubscribe', 'JM_UNSUBSCRIBE', true, false );
		JToolBarHelper::spacer();
		JToolBarHelper::custom( 'delete', 'unsubscribe', 'unsubscribe', 'JM_DELETE', true, false );
		JToolBarHelper::spacer();
	    } else if ( JRequest::getVar('type') == 'u' ){
//		JToolBarHelper::custom( 'resubscribe', 'resubscribe', 'resubscribe', 'Resubscribe', false, false );
	    }

        }

	// Get data from the model
	$active		= & $this->get( 'Active');
	$this->assignRef('active', $active);

	$listdetails = & $this->get( 'ListDetails');
	$this->assignRef('listDetails', $listdetails);

	$total = & $this->get('Total');
	$this->assignRef('total', $total);

	$users = & $this->get('Users');
	$this->assignRef('users', $users);

//		if($total<$limit) $limit = $total;
	$this->assignRef('limitstart', $limitstart);
	$this->assignRef('limit', $limit);

	jimport('joomla.html.pagination');
	$pagination = new JPagination( $total, $limitstart, $limit );
	$this->assignRef('pagination',	$pagination);

	parent::display($tpl);
	require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }
}
