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

class joomailermailchimpintegrationsViewGroups extends JView
{

	function display($tpl = null)
	{
		$fields =& $this->get('Data');
		$this->assignRef('fields', $fields);
		$CBfields =& $this->get('CBfields');
		$this->assignRef('CBfields', $CBfields);
		$JSfields =& $this->get('JSfields');
		$this->assignRef('JSfields', $JSfields);
		$VMfields =& $this->get('VMfields');
		$this->assignRef('VMfields', $VMfields);
		
		$listId = JRequest::getVar('listid', '');
		$this->assignRef('listId', $listId);
		$name = JRequest::getVar('name', '');
		if(!$name) $name = JRequest::getVar('listName', '');
		$this->assignRef('name', $name);
		if($name){ $title = ' ( '.$name.' )'; } else { $title = ''; }

		$layout = JRequest::getVar('layout',  0, 'post', 'string');
		if ( $layout == 'form' ) {
			JToolBarHelper::title(  JText::_('JM_NEWSLETTER_NEW_CUSTOM_FIELD') . $title );
			JToolBarHelper::save();
			JToolBarHelper::spacer();
			JToolBarHelper::cancel();
			JToolBarHelper::spacer();
		} else {
			JToolBarHelper::title(  JText::_('JM_NEWSLETTER_CUSTOM_FIELDS') . $title );
			JToolBarHelper::custom( 'goToLists', 'lists', 'lists', 'JM_LISTS', false, false );
			JToolBarHelper::spacer();
			JToolBarHelper::addNewX();
			JToolBarHelper::spacer();
//				JToolBarHelper::editListX();
//				JToolBarHelper::spacer();
			JToolBarHelper::deleteListX();
			JToolBarHelper::spacer();
		}

		parent::display($tpl);
		require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
	}// function
}// class
