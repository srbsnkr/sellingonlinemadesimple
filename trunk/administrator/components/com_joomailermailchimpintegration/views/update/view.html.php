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

class joomailermailchimpintegrationsViewUpdate extends JView
{

    function display($tpl = null)
    {

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MCauth = new MCauth();

	if ( !$MCapi || !$MCauth->MCauth() ) {
	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER' ).' : '.JText::_( 'JM_UPDATE' ), 'MC_logo_48.png' );
	    $user =& JFactory::getUser();
	    if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
		|| !version_compare(JVERSION,'1.6.0','ge') ) {
		    JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
		    JToolBarHelper::spacer();
	    }
	} else {

	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER' ).' : '.JText::_( 'JM_UPDATE' ), 'MC_logo_48.png' );

	    $task = JRequest::getCmd('task');
	    $force = ($task == 'force');
	    // Load the model
	    $model =& $this->getModel();
	    $updates =& $model->getUpdates($force);
	    $this->assignRef('updates', $updates);
	}

	parent::display($tpl);
	require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }
}