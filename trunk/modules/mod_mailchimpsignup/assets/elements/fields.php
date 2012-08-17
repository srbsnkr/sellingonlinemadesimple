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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Joomla 1.6 ?
if(version_compare(JVERSION,'1.6.0','ge')) {

    class JFormFieldFields extends JFormField
    {

	function getInput()
	{
	    jimport( 'joomla.filesystem.file' );
	    $mainframe = & JFactory::getApplication();
	    if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
		$mainframe->redirect('index.php',JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER'),'error');
	    } else {
		$listid = $this->form->getValue('listid', 'params');
		require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
		$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$MCapi = $params->get( 'params.MCapi' );
		$api = new joomlamailerMCAPI($MCapi);
		$fields = $api->listMergeVars( $listid );
		$key = 'tag';
		$val = 'name';
		$options = false;
		if($fields) {
		    foreach ($fields as $field){
			$choices = '';
			if(isset($field['choices'])){
			    foreach($field['choices'] as $c){
				$choices .= $c.'##';
			    }
			    $choices = substr($choices,0,-2);
			}
			$req = ($field['req'])?1:0;
			if($field[$key]=='EMAIL') {
			    if( !is_array($this->value)){
				$oldValue = $this->value;
				$this->value = array();
				$this->value[] = $oldValue;
			    }
			    $this->value[] = $field[$key].';'.$field['field_type'].';'.$field['name'].';'.$req.';'.$choices;
			}
			$options[]=array($key=>$field[$key].';'.$field['field_type'].';'.$field['name'].';'.$req.';'.$choices,$val=>$field[$val]);
		    }
		}

		$attribs = 'multiple="multiple"';
		if($options){
		    $content =  JHtml::_('select.genericlist',$options, 'jform[params][fields][]', $attribs, $key, $val, $this->value, $this->id);
		    $content .= '<script type="text/javascript">
				window.addEvent(\'domready\',function() {
				    $("jform_params_fields").addEvent( \'change\', function(){
					$("jform_params_fields").options[0].setProperty(\'selected\', \'selected\');

				    });
				});
				</script>';
		} else {
		    $content = '<div style="float:left;">'.JText::_('JM_NO_FIELDS').'</div>';
		}

		return $content;
	    }
	}
    }

} else {

    class JElementFields extends JElement {
	var	$_name = 'Fields';

	function fetchElement($name, $value, &$node, $control_name) {
	    jimport( 'joomla.filesystem.file' );
	    $mainframe = & JFactory::getApplication();
	    $option = JRequest::getCmd('option');
	    if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
		$mainframe->redirect('index.php',JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER'),'error');
	    } else {
		$moduleParams = new JParameter( $this->_parent->_raw );
		$listid = $moduleParams->get('listid');
		require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
		$params = & JComponentHelper::getParams('com_joomailermailchimpintegration');
		$apikey = $params->get('MCapi');
		$api = new joomlamailerMCAPI($apikey);
		$fields = $api->listMergeVars( $listid );
		$value = $moduleParams->get('fields');
		$key = 'tag';
		$val = 'name';
		$options = false;
		if($fields) {
		    foreach ($fields as $field){
			$choices = '';
			if(isset($field['choices'])){
			    foreach($field['choices'] as $c){
				$choices .= $c.'##';
			    }
			    $choices = substr($choices,0,-2);
			}
			$req = ($field['req'])?1:0;
			if($field[$key]=='EMAIL') {
			    if( !is_array($value)){
				$oldValue = $value;
				$value = array();
				$value[] = $oldValue;
			    }
			    $value[] = $field[$key].';'.$field['field_type'].';'.$field['name'].';'.$req.';'.$choices;
			}
			$options[]=array($key=>$field[$key].';'.$field['field_type'].';'.$field['name'].';'.$req.';'.$choices,$val=>$field[$val]);
		    }
		}
		$ctrl  = $control_name .'['. $name .']';
		$attribs = 'multiple="multiple"';
		$ctrl .= '[]';
		if($options){
		    $content =  JHTML::_('select.genericlist',$options, $ctrl, $attribs, $key, $val, $value, $control_name.$name);
		    $content .= '<script type="text/javascript">
				window.addEvent(\'domready\',function() {
				    $("'.$control_name.$name.'").addEvent( \'change\', function(){
					$("'.$control_name.$name.'").options[0].setProperty(\'selected\', \'selected\');

				    });
				});
				</script>';
		} else {
		    $content = JText::_('JM_NO_FIELDS');
		}

		return $content;
	    }
	}
    }

}