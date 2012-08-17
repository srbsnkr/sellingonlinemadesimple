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

    class JFormFieldInterests extends JFormField
    {

	function getInput()
	{
	    jimport( 'joomla.filesystem.file' );
	    $mainframe = & JFactory::getApplication();
	    if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
		$mainframe->redirect('index.php',JText::_('JM_INSTALLJOOMAILER'),'error');
	    } else {
		$listid = $this->form->getValue('listid', 'params');
		require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
		$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		$MCapi = $params->get( $paramsPrefix.'MCapi' );
		$api = new joomlamailerMCAPI($MCapi);
		$interests = $api->listInterestGroupings( $listid );
		$key = 'id';
		$val = 'name';
		$options = false;
		if($interests) {
		    foreach ($interests as $interest){
			if ($interest['form_field']!='hidden') {
			    $options[]=array($key=>$interest[$key],$val=>$interest[$val]);
			}
		    }
		}
		
		$attribs = 'multiple="multiple"';
		if($options){
		    $content =  JHtml::_('select.genericlist',$options, 'jform[params][interests][]', $attribs, $key, $val, $this->value, $this->id);
		} else {
		    $content = '<div style="float:left;">'.JText::_('JM_NO_INTEREST_GROUPS').'</div>';
		}

		return $content;
	    }
	}
    }

} else {

    class JElementInterests extends JElement
    {
	var	$_name = 'Interests';

	function fetchElement($name, $value, &$node, $control_name)
	{
	    jimport( 'joomla.filesystem.file' );
	    $mainframe = & JFactory::getApplication();
	    if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
		$mainframe->redirect('index.php',JText::_('JM_INSTALLJOOMAILER'),'error');
	    } else {
		jimport('joomla.plugin.plugin');
		$pluginParams = new JParameter( $this->_parent->_raw );
		$listid = $pluginParams->get('listid');
		require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
		$params = & JComponentHelper::getParams('com_joomailermailchimpintegration');
		$apikey = $params->get('MCapi');
		$api = new joomlamailerMCAPI($apikey);
		$interests = $api->listInterestGroupings( $listid );
		$value = $pluginParams->get('interests');
		$key = 'id';
		$val = 'name';
		$options = false;
		if($interests) {
		    foreach ($interests as $interest){
			if ($interest['form_field']!='hidden') {
			    $options[]=array($key=>$interest[$key],$val=>$interest[$val]);
			}
		    }
		}
		$ctrl  = $control_name .'['. $name .']';
		$attribs = 'multiple="multiple"';
		$control_name = 'params';
		$ctrl .= '[]';
		if($options){
		    $content =  JHTML::_('select.genericlist',$options, $ctrl, $attribs, $key, $val, $value, $control_name.$name);
		} else {
		    $content = JText::_('JM_NO_INTEREST_GROUPS');
		}

		return $content;
	    }
	}
    }
}