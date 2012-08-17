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

class JFormFieldPassword extends JFormField
{

    function getInput()
    {
	unset($_SESSION['MCping']);

	$document	= &JFactory::getDocument();
	$option 	= JRequest::getCmd('option');

	$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
	$class = ( isset( $this->element['class'] ) ? 'class="'.$this->element['class'].'"' : 'class="text_area"' );
	$size = ( isset( $this->element['size'] ) ? 'size="'.$this->element['size'].'"' : '' );
        $onchange = ( isset( $this->element['onchange'] ) ? 'onchange="'.$this->element['onchange'].'"' : '' );

	$html = '<input type="password" name="'.$this->name.'" id="'.$this->id.'" value="'.$value.'" '.$class.' '.$size.' '.$onchange.'/>';
	if( JRequest::getVar( 'option' ) != 'com_menus'){
	$html .= "<script type=\"text/javascript\">
		    Joomla.submitform = function(task, form) {
			if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');
		/**
		 * Added to ensure Joomla 1.5 compatibility
		 */
		if(!form){
			form = document.adminForm;
		}
	}

	if (typeof(task) !== 'undefined') {
		form.task.value = task;
	}

	// Submit the form.
	if (typeof form.onsubmit == 'function') {
		form.onsubmit();
	}
	if (typeof form.fireEvent == \"function\") {
		form.fireEvent('submit');
	}
	form.submit();

	window.top.setTimeout('joomailermailchimpintegration_ajax_loader();window.location.reload()', 700);
		    }
		    </script>";
	}
	$document = & JFactory::getDocument();
	$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/joomailermailchimpintegration.js');

	return $html;
    }
}

// Joomla 1.5
} else {

    class JElementPassword extends JElement
    {

	function fetchElement($name, $value, &$node, $control_name)
	{
	    unset($_SESSION['MCping']);

	    $document	= &JFactory::getDocument();
	    $option 	= JRequest::getCmd('option');


	    $size = ( $node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '' );
	    $class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
	    $onchange = ( $node->attributes('onchange') ? 'onchange="'.$node->attributes('onchange').'"' : '' );
	    /*
	     * Required to avoid a cycle of encoding &
	     * html_entity_decode was used in place of htmlspecialchars_decode because
	     * htmlspecialchars_decode is not compatible with PHP 4
	     */
	    $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

	    $html = '<input type="password" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' '.$size.' '.$onchange.'/>';
	    if( JRequest::getVar( 'option' ) != 'com_menus'){
	    $html .= "<script type=\"text/javascript\">function submitbutton(pressbutton) {
			if(pressbutton=='save'){
			    submitform(pressbutton);
			    window.top.setTimeout('joomailermailchimpintegration_ajax_loader();window.location.reload()', 700);
			}
		    }</script>";
	    }
	    $document = & JFactory::getDocument();
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/joomailermailchimpintegration.js');

	    return $html;
	}
    }

}
?>
