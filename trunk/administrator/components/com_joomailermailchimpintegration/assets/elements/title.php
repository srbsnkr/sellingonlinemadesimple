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
*/

defined('_JEXEC') or die();

// Joomla 1.6 ?
if(version_compare(JVERSION,'1.6.0','ge')) {

    class JFormFieldTitle extends JFormField
    {
	function getLabel()
	{

	    $title = ( isset( $this->element['label'] ) && $this->element['label'] != '' ) ? JText::_( $this->element['label'] ) : ' ';

	    return '<label>'.$title.'</label>';
	}

	function getInput()
	{

	    $description = ( isset( $this->element['description'] ) && $this->element['description'] != '') ? JText::_($this->element['description']) : ' ';

	    return '<div style="margin-top: 8px; float: left;">'.$description.'</div>';
	}
    }

// Joomla 1.5
} else {

    class JElementTitle extends JElement
    {

	function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
	    if($xmlElement->attributes('label')){
		$output = JText::_($xmlElement->attributes('label'));
	    } else {
		$output = '';
	    }

	    return $output;
	}

	function fetchElement($name, $value, &$node, $control_name)
	{

	    $document	= &JFactory::getDocument();
	    $option 	= JRequest::getCmd('option');


	    $title = ( $node->attributes('title') ? $node->attributes('title') : '' );
	    $description = ( $node->attributes('description') ? $node->attributes('description') : '' );
	    /*
	     * Required to avoid a cycle of encoding &
	     * html_entity_decode was used in place of htmlspecialchars_decode because
	     * htmlspecialchars_decode is not compatible with PHP 4
	     */
	    $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

	    $html = JText::_($description);

	    return $html;
	}
    }

}