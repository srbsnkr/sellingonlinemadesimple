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

class JFormFieldSetupinfo extends JFormField
{

    function getInput()
    {
	$html = '<div style="margin-top: 8px; float: left;"><span id="showSetupInfo"><a href="javascript:showSetupInfo()">Show setup info</a></span>';
	$html .= "<script type=\"text/javascript\">
		    /* <![CDATA[ */
		    function showSetupInfo(){
			var url = '".JURI::base()."index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=main&format=raw&task=showSetupInfo';
			var data = new Object();
			doAjaxTask(url, data, function(postback){
			    document.getElementById('showSetupInfo').innerHTML = 'ok';
			    window.parent.jQuery('#setupInfo').slideDown();
			});
		    }
		    /* ]]> */
		    </script>
		    </div>";

	$document = & JFactory::getDocument();
	$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/joomailermailchimpintegration.js');
	$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/jquery.min.js');
	$document->addScriptDeclaration('jQuery.noConflict(); var $j = jQuery.noConflict();');
	if( version_compare(JVERSION,'1.6.0','ge') ) {
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/ajax_16.js');
	} else {
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/ajax_15.js');
	}

	return $html;
    }
}

// Joomla 1.5
} else {

    class JElementSetupinfo extends JElement
    {

	function fetchElement($name, $value, &$node, $control_name)
	{
	    $html = '<span id="showSetupInfo"><a href="javascript:showSetupInfo()">Show setup info</a></span>';
	    $html .= "<script type=\"text/javascript\">
			function showSetupInfo(){
			    var url = '".JURI::base()."index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=main&format=raw&task=showSetupInfo';
			    var data = new Object();
			    doAjaxTask(url, data, function(postback){
				document.getElementById('showSetupInfo').innerHTML = 'ok';
				window.parent.jQuery('#setupInfo').slideDown();
			    });
			}
			</script>";

	    $document = & JFactory::getDocument();
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/joomailermailchimpintegration.js');
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/jquery.min.js');
	    $document->addScriptDeclaration('jQuery.noConflict(); var $j = jQuery.noConflict();');
	    if( version_compare(JVERSION,'1.6.0','ge') ) {
		$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/ajax_16.js');
	    } else {
		$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/ajax_15.js');
	    }

	    return $html;
	}
    }

}
?>
