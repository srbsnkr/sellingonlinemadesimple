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

/**
 * HTML View class for the joomailermailchimpintegration Component
 *
 * @package    joomailermailchimpintegration
 * @subpackage Views
 */

class joomailermailchimpintegrationsViewjoomailermailchimpintegration extends JView
{
    /**
     * joomailermailchimpintegration view display method
     * 
     * @return void
     **/
	function display($tpl = null)
	{
        //get the joomailermailchimpintegration Data
        $joomailermailchimpintegration =& $this->get('Data');
        if ( isset($joomailermailchimpintegration['anyType']['Title']) ){ $isNew = false; } else { $isNew = true; }

		$title = $isNew ? JText::_( 'New List' ) : JText::_( 'Edit List' );
		JToolBarHelper::title(  'Newsletter : ' . $title );
		JToolBarHelper::save();
		if (!$isNew)  {
            // for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', JText::_('Close') );
		} else {

            JToolBarHelper::cancel();
		}

        $this->assignRef('joomailermailchimpintegration', $joomailermailchimpintegration);


		parent::display($tpl);
		require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }// function
}// class
