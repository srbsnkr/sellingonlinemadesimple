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

class joomailermailchimpintegrationViewSubscriptions extends JView
{

	function display($tpl = null)
	{
        // redirect guests to login page
        $mainframe =& JFactory::getApplication(); 
        $user =& JFactory::getUser();
	if ($user->id != 0) {

	    $Lists = $this->get( 'Lists' );
	    $this->assignRef( 'lists',	$Lists );

	    //--Creating a link to the edit form
	    $this->assignRef( 'editlink', JRoute::_('index.php?option=com_joomailermailchimpintegration&view=subscriptions&task=edit') );

            // retrieve page title from the menuitem
            $menus =& JSite::getMenu();
	    $menu  = $menus->getActive();
            $menu_params = new JParameter( $menu->params );
            $this->assignRef('page_title', $menu_params->get( 'page_title'));

	    parent::display($tpl);

        } else {
	    // Redirect to login
	    $uri    = JFactory::getURI();
	    $return = $uri->toString();

	    if (version_compare(JVERSION,'1.6.0','ge')) {
		$url  = 'index.php?option=com_users&view=login';
	    } else {
		$url  = 'index.php?option=com_user&view=login';
	    } 
	    $url .= '&return='.base64_encode($return);

	    $mainframe->redirect($url, JText::_('JM_ONLY_LOGGED_IN_USERS_CAN_VIEW_SUBSCRIPTIONS') );
	}

    }

}
