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

class joomailermailchimpintegrationsViewFields extends JView
{

    function display($tpl = null)
    {
	$layout = JRequest::getVar('layout',  0, 'post', 'string');
	$CBfields =& $this->get('CBfields');
	$this->assignRef('CBfields', $CBfields);
	$JSfields =& $this->get('JSfields');
	$this->assignRef('JSfields', $JSfields);
	$VMfields =& $this->get('VMfields');
	$this->assignRef('VMfields', $VMfields);

	if ( $layout == 'form' ) {
	    $cid = JRequest::getVar('cid','');
	    if( $cid ){
		JToolBarHelper::title(  JText::_('JM_NEWSLETTER_EDIT_MERGE_FIELD') );
	    } else {
		JToolBarHelper::title(  JText::_('JM_NEWSLETTER_NEW_MERGE_FIELD') );
	    }
	    JToolBarHelper::save();
	    JToolBarHelper::spacer();
	    JToolBarHelper::cancel();
	    JToolBarHelper::spacer();
	    $types[] = array('type'=>'0','name'=>'--- '.JText::_('JM_SELECT_A_DATA_TYPE').' ---');
	    $types[] = array('type'=>'text','name'=>'text');
	    $types[] = array('type'=>'email','name'=>'email');
	    $types[] = array('type'=>'number','name'=>'number');
	    $types[] = array('type'=>'radio','name'=>'radio');
	    $types[] = array('type'=>'dropdown','name'=>'dropdown');
	    $types[] = array('type'=>'date','name'=>'date');
	    $types[] = array('type'=>'address','name'=>'address');
	    $types[] = array('type'=>'phone','name'=>'phone');
	    $types[] = array('type'=>'url','name'=>'url');
	    $types[] = array('type'=>'imageurl','name'=>'imageurl');

	    $item['listid'] = JRequest::getVar('listid');
	    $item['name'] = '';
	    $item['tag'] = '';
	    $item['field_type'] = '';
	    $item['req'] = 0;
	    $item['choices'] = array();

	    $fieldData[0]->dbfield = '';

	    if(isset($cid[0])){
		$attribs = explode(';',$cid[0]);
		$item['name'] = $attribs[0];
		$item['tag'] = $attribs[1];
		$item['field_type'] = $attribs[2];
		$item['req'] = $attribs[3];
		$item['choices'] = explode('||' , $attribs[4]);

		$db	=& JFactory::getDBO();
		$query = "SELECT * FROM #__joomailermailchimpintegration_custom_fields "
			."WHERE `grouping_id` = '".$item['tag']."'";
		$db->setQuery($query);
		$fieldData = $db->loadObjectList();

		if(isset($fieldData[0])){
		    $fieldId = $fieldData[0]->id;
		    $groupingId = $fieldData[0]->grouping_id;
		    if($fieldData[0]->framework == 'CB'){
			 $CBeditID		= $fieldData[0]->dbfield;
			 $JSeditID		= '';
			 $VMeditID		= '';
		    } else if($fieldData[0]->framework == 'JS'){
			 $CBeditID		= '';
			 $JSeditID		= $fieldData[0]->dbfield;
			 $VMeditID		= '';
		    } else if($fieldData[0]->framework == 'VM'){
			 $CBeditID		= '';
			 $JSeditID		= '';
			 $query = "SELECT fieldid FROM #__vm_userfield "
				 ."WHERE `name` = '".$fieldData[0]->dbfield."'";
			 $db->setQuery($query);
			 $VMeditID = $db->loadResult();
		    }
		} else {
		    $fieldId	= '';
		    $groupingId	= '';
		    $CBeditID	= '';
		    $JSeditID	= '';
		    $VMeditID	= '';
		    $fieldData[0]->dbfield = '';
		}
	    } else {
		$name	    = '';
		$CBeditID   = '';
		$JSeditID   = '';
		$VMeditID   = '';
		$fieldId    = '';
		$groupingId = '';
	    }

	    $typeDropDown = JHTML::_( 'select.genericlist', $types, 'field_type', '', 'type', 'name' , array($item['field_type']));
	    $this->assignRef('typeDropDown',$typeDropDown);
	    $this->assignRef('item',$item);

	    $firstoption=new stdClass;
	    $firstoption->id=0;
	    $firstoption->name='--- '.JText::_('JM_SELECT_FIELD').' ---';
            $JSDropDown = '';
            if($JSfields) {
		$JSfields = array_merge(array($firstoption),$JSfields);
		$JSDropDown = JHTML::_( 'select.genericlist', $JSfields, 'JSfield', 'id="JSField" style="min-width:303px;"', 'id', 'name', array($fieldData[0]->dbfield));
            }
            $this->assignRef('JSDropDown',$JSDropDown);

	    $this->assignRef('CBeditID', $CBeditID);

            $VMDropDown = '';
            if($VMfields) {
		$VMfields = array_merge(array($firstoption),$VMfields);
		$VMDropDown = JHTML::_( 'select.genericlist', $VMfields, 'VMfield', 'id="VMfield" style="min-width:303px;"', 'id', 'name', array($VMeditID));
            }
            $this->assignRef('VMDropDown',$VMDropDown);



	} else {
	    $fields =& $this->get('Data');
	    $this->assignRef('fields', $fields);
	    $listId = JRequest::getVar('listid', '');
	    $this->assignRef('listId', $listId);
	    $name = JRequest::getVar('name', '');
	    if(!$name) $name = JRequest::getVar('listName', '');
	    $this->assignRef('name', $name);
	    if($name){ $title = ' ( '.$name.' )'; } else { $title = ''; }

	    JToolBarHelper::title(  JText::_('JM_NEWSLETTER_CUSTOM_MERGE_FIELDS') . $title );
	    JToolBarHelper::custom( 'goToLists', 'lists', 'lists', 'JM_LISTS', false, false );
	    JToolBarHelper::spacer();
	    JToolBarHelper::addNewX();
	    JToolBarHelper::spacer();
	    JToolBarHelper::editListX();
//	    JToolBarHelper::spacer();
	    JToolBarHelper::deleteListX( JText::_('JM_ARE_YOU_SURE_TO_DELETE_THIS_MERGE_FIELD') );
	    JToolBarHelper::spacer();
	}

	parent::display($tpl);
	require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }// function
}// class
