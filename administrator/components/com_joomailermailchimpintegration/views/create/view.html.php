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

class joomailermailchimpintegrationsViewCreate extends JView
{
    function display($tpl = null)
    {
	$mainframe =& JFactory::getApplication();
	if( !JOOMLAMAILER_CREATE_DRAFTS ){
	    $mainframe->redirect( 'index.php?option=com_joomailermailchimpintegration', JText::_('JERROR_ALERTNOAUTHOR'), 'error' );
	}
	$model = $this->getModel();
	$sec_filter = JRequest::getVar('sec_filter',-1,'','int');
	$cat_filter = JRequest::getVar('cat_filter',-1,'','int');
	$k2cat_filter = JRequest::getVar('k2cat_filter',-1,'','int');

	// Get data from the model
	$pagination   = $this->get('Pagination');
	$paginationK2 =& $this->get('PaginationK2');

	$this->assignRef('user',		JFactory::getUser());
	$this->assignRef('lists',		$this->get('Lists') );
	$this->assignRef('pagination',	$pagination);
	$this->assignRef('paginationK2',$paginationK2);

	JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_CREATE_DRAFT' ), 'MC_logo_48.png' );

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MCauth = new MCauth();

	if ( $MCapi ) {

	    if( !$MCauth->MCauth() ) {
		$user =& JFactory::getUser();
		if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
		    || !version_compare(JVERSION,'1.6.0','ge') ) {
			JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
			JToolBarHelper::spacer();
		}
	    } else {

		// Get data from the model
		$items = $this->get('Data');

		// apply stored article order
		$coreOrder = JRequest::getVar('coreOrder', 0, '', 'string');

		if($coreOrder){
		    $coreOrder = explode(';', $coreOrder);
		    unset($coreOrder[count($coreOrder)-1]);

		    $coreArticles = array();
		    $i=0;
		    foreach($coreOrder as $co){
			foreach($items as $c){
			    if( $co == $c->id){
				$coreArticles[$i] = $c;
			    }
			}
			$i++;
		    }
		    if(count($coreArticles) == count($items)) {
			$items = $coreArticles;
		    }
		}

		$this->assignRef('items', $items);

		if( ! version_compare(JVERSION,'1.6.0','ge') ){
		$seccat =& $this->get('seccat');
		$this->assignRef('seccat', $seccat);
		}

		$VMproducts =& $this->get('VMproducts');
		$this->assignRef('VMproducts', $VMproducts);

		$lists =& $this->get('MClists');
		$this->assignRef('lists', $lists);

		$merge =& $this->get('MergeTags');
		$this->assignRef('merge', $merge);

		$core =& $this->get('Core');
		$this->assignRef('core', $core);

		if (version_compare(JVERSION,'1.6.0','ge')) {
		    $K2Installed = false;
		} else {
		    $K2Installed =& $this->get('K2Installed');
		}
		$this->assignRef('K2Installed', $K2Installed);

		if ( ! version_compare(JVERSION,'1.6.0','ge') && $K2Installed ) {
		$k2Limit = JRequest::getVar('k2Limit', $mainframe->getCfg('list_limit'), '', 'int');
		$k2Limitstart = JRequest::getVar('k2Limitstart', 0, '', 'int');
		$k2 = $model->getK2( $k2Limitstart, $k2Limit );

		$K2Total =& $this->get('K2Total');
		$this->assignRef('K2Total', $K2Total);

	//	$k2 =& $this->get('K2');
		if( isset($k2[0]) ){
		    $k2Order = JRequest::getVar('k2Order', 0, '', 'string');
		    if($k2Order){
			$k2Order = explode(';', $k2Order);
			$k2Articles = array();
			$i = 0;
			foreach($k2Order as $co){
			    $x = 0;
			    foreach($k2 as $c){
				if( $co == $c->id){
				    $k2Articles[$i] = $c;
				    unset($k2[$x]);
				}
				$x++;
			    }
			    $i++;
			}
			$k2Articles = array_merge($k2Articles, $k2);
			$k2 = $k2Articles;
		    }
		    $k2cat =& $this->get('k2cat');
		} else {
		    $k2cat = false;
		}

		$k2Array = array();
		$k2Articles = $k2;
		$k2 = array();
		if( isset($k2Articles) ){
		    foreach( $k2Articles as $c ){
			if( ! in_array( $c->id, $k2Array ) ){
			    $k2[] = $c;
			    $k2Array[] = $c->id;
			}
		    }
		}
		$this->assignRef('k2', $k2);
		$this->assignRef('k2cat', $k2cat);
		}

		$ImagesDropdown =& $this->get('ImagesDropdown');
		$this->assignRef('ImagesDropdown', $ImagesDropdown);

		$sec = $this->get('Sec');
		$secDropDown = JHTML::_( 'select.genericlist', $sec, 'sec_filter', 'onchange="document.adminForm.cat_filter.selectedIndex=\'\';document.adminForm.submit();"', 'id', 'title' , $sec_filter);
		$this->assignRef('secDropDown',$secDropDown);

		$cat = $model->getCat($sec_filter);
		$catDropDown = JHTML::_( 'select.genericlist', $cat, 'cat_filter', 'onchange="document.adminForm.submit();"', 'id', 'title' , $cat_filter);
		$this->assignRef('catDropDown',$catDropDown);
		if($K2Installed) {
		    $first=new stdClass;
		    $first->id=-1;
		    $first->name='-- '.JText::_('JM_SELECT_A_CATEGORY').' --';
		    $k2cat = array_merge(array($first),$model->getK2Cat());
		    $k2catDropDown = JHTML::_( 'select.genericlist', $k2cat, 'k2cat_filter', 'onchange="document.adminForm.submit();"', 'id', 'name' , $k2cat_filter);
		    $this->assignRef('k2catDropDown',$k2catDropDown);

		    $allk2cat = $model->getK2Cat();
		    $this->assignRef('allk2cat',$allk2cat);
		}

		$folders = $this->get('Folders');
		$undefined[0] = array('folder_id' => 0, 'name' => JText::_('JM_UNFILED') );
		$folder_id = JRequest::getVar('folder_id', 0, '', 'int');
		if($folders) {
		    $folders = array_merge($undefined,$folders);
		} else {
		    $folders = $undefined;
		}
		$foldersDropDown = JHTML::_( 'select.genericlist', $folders, 'folder_id', '', 'folder_id', 'name' , $folder_id);
		$this->assignRef('foldersDropDown', $foldersDropDown);

		$jsLimit = JRequest::getVar('jsLimit', $mainframe->getCfg('list_limit'), '', 'int');
		$jsLimitstart = JRequest::getVar('jsLimitstart', 0, '', 'int');
		$jomsocial = $model->getJomsocial( $jsLimitstart, $jsLimit );
		// apply stored order
		$jsOrder = JRequest::getVar('jsOrder', 0, '', 'string');
		if($jsOrder){
		    $jsOrder = explode(';', $jsOrder);
		    unset($jsOrder[count($jsOrder)-1]);
		    $coreArticles = array();
		    $i=0;
		    if( isset($coreOrder) && is_array($coreOrder) ){
			foreach($coreOrder as $co){
			    foreach($items as $c){
				if( $co == $c->id){
				    $coreArticles[$i] = $c;
				}
			    }
			    $i++;
			}
		    }
		    if(count($coreArticles) == count($items)) {
			$items = $coreArticles;
		    }
		}
		$this->assignRef('jomsocial',$jomsocial);
		$jsTotal = $model->getJomsocialTotal();
		$this->assignRef('jsTotal',$jsTotal);
		$jsFields = $model->getJomsocialFields();
		$this->assignRef('jsFields',$jsFields);
		$jsdiscussions = $model->getJomsocialDiscussions();
		$this->assignRef('jsdiscussions',$jsdiscussions);
		$aecConfig = $model->getAECconfig();
		$this->assignRef('aecConfig',$aecConfig);
		$aec = $model->getAECplans();
		$this->assignRef('aec',$aec);
		$ambra = $model->getAmbra();
		$this->assignRef('ambra',$ambra);
	    }
	} else {
	    $user =& JFactory::getUser();
	    if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
		|| !version_compare(JVERSION,'1.6.0','ge') ) {
		JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
	    }
	}


	parent::display($tpl);
	require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }
}
