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

    class JFormFieldLists extends JFormField
    {

	    function getInput()
	    {
		jimport( 'joomla.filesystem.file' );
		$mainframe = & JFactory::getApplication();
		if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
		    $mainframe->redirect('index.php',JText::_('JM_INSTALLJOOMAILER'),'error');
		} else {
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'helpers'.DS.'MCauth.php' );
		    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		    $MCapi = $params->get( $paramsPrefix.'MCapi' );
		    $MCauth = new MCauth();
		    if( !$MCapi || !$MCauth->MCauth() ) {
			$mainframe->redirect('index.php?option=com_joomailermailchimpintegration&view=main',JText::_('JM_INVALID_API_KEY'),'error');
		    }
		    $api = new joomlamailerMCAPI($MCapi);
		    $lists = $api->lists();
		    $key = 'id';
		    $val = 'name';
		    $options[] = array($key=>'',$val=>JText::_('JM_PLEASE_SELECT_A_LIST'));
		    foreach ($lists as $list){
			$options[]=array($key=>$list[$key],$val=>$list[$val]);
		    }

		    $attribs = (version_compare(JVERSION,'1.6.0','ge')) ?  "onchange='Joomla.submitbutton(\"plugin.apply\")'" : "onchange='submitbutton(\"apply\")'";
		    $control_name = 'params';
		    $name = 'listid';
		    if($options){
			$content =  JHtml::_('select.genericlist', $options, 'jform[params][listid]', $attribs, $key, $val, $this->value, $this->id);
		    }

		    return $content;
		}
	    }
    }

} else {

    class JElementLists extends JElement
    {
	    var	$_name = 'Lists';

	    function fetchElement($name, $value, &$node, $control_name)
	    {
		jimport( 'joomla.filesystem.file' );
		$mainframe = & JFactory::getApplication();
		if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
		    $mainframe->redirect('index.php',JText::_('JM_INSTALLJOOMAILER'),'error');
		} else {
		    jimport('joomla.plugin.plugin');
		    $pluginParams = new JParameter( $this->_parent->_raw );
		    $value = $pluginParams->get('listid');
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'helpers'.DS.'MCauth.php' );
		    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		    $MCapi = $params->get( $paramsPrefix.'MCapi' );
		    $MCauth = new MCauth();
		    if( !$MCapi || !$MCauth->MCauth() ) {
			$mainframe->redirect('index.php?option=com_joomailermailchimpintegration&view=main',JText::_('JM_INVALID_API_KEY'),'error');
		    }
		    $api = new joomlamailerMCAPI($MCapi);
		    $lists = $api->lists();
		    $key = 'id';
		    $val = 'name';
		    $options[] = array($key=>'',$val=>JText::_('JM_PLEASE_SELECT_A_LIST'));
		    foreach ($lists as $list){
			$options[]=array($key=>$list[$key],$val=>$list[$val]);
		    }

		    $attribs = "onchange='submitbutton(\"apply\")'";
		    $control_name = 'params';
		    $name = 'listid';
		    if($options){
			$content =  JHTML::_('select.genericlist',$options, 'params[listid]', $attribs, $key, $val, $value, $control_name.$name);
		    }

		    return $content;
		}
	    }
    }
}
