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
jimport( 'joomla.cache.cache' );

class joomailermailchimpintegrationsViewCampaigns extends JView
{

    function display($tpl = null)
    {
	$mainframe =& JFactory::getApplication();
	if( !JOOMLAMAILER_MANAGE_REPORTS ){
	    $mainframe->redirect( 'index.php?option=com_joomailermailchimpintegration', JText::_('JERROR_ALERTNOAUTHOR'), 'error' );
	}

	$option = JRequest::getCmd('option');
	$cacheGroup = 'joomailermailchimpintegrationReports';

	if( version_compare(JVERSION,'1.6.0','ge') ) {
	    $cacheOptions = array();
	    $cacheOptions['cachebase'] = JPATH_ADMINISTRATOR.DS.'cache';
	    $cacheOptions['lifetime'] = 31556926;
	    $cacheOptions['storage'] = 'file';
	    $cacheOptions['defaultgroup'] = 'joomailermailchimpintegrationReports';
	    $cacheOptions['locking'] = false;
	    $cacheOptions['caching'] = true;

	    $cache = new JCache( $cacheOptions );
	    require_once( JPATH_COMPONENT.DS.'helpers'.DS.'cache_16.php' );
	} else {
	    $cacheOptions = array();
	    $cacheOptions['cachebase'] = JPATH_ADMINISTRATOR.DS.'cache';
	    $cacheOptions['lifetime'] = 31556926;
	    $cacheOptions['storage'] = 'file';
	    $cache =& new JCache( $cacheOptions );
	    $cache->gc();
	    require_once( JPATH_COMPONENT.DS.'helpers'.DS.'cache_15.php' );
	}
	
	$layout	    = JRequest::getVar('layout',  0, '', 'string');
	$model	    = $this->getModel();
	$limit	    = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

	if ($layout == 'sharereport') {
	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_SHARE_REPORT' ), 'MC_logo_48.png' );
	} else {
	    JToolBarHelper::title(   JText::_( 'JM_NEWSLETTER_CAMPAIGN_STATS' ), 'MC_logo_48.png' );
	}

	if ($layout == 'clickedlinks') {

	    $cid = JRequest::getVar('cid', 0,'','string');
	    $clicked = $model->getClicks($cid);

	    foreach($clicked as $key => $value){
		$unset = true;
		if($value['clicks']){
		    $unset = false;
		}
		if( $unset ){
		    unset($clicked[$key]);
		}
	    }
	    $this->assignRef('clicked', $clicked);
	    $this->assignRef('limitstart', $limitstart);
	    $this->assignRef('limit', $limit);
	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( count($clicked), $limitstart, $limit );
	    $this->assignRef('pagination',	$pagination);

	} elseif ($layout == 'clickedlinkdetails') {

	    $cid = JRequest::getVar('cid', 0,'','string');
	    $url = urldecode(JRequest::getVar('url', '','','string'));
	    $clicks = $model->getClicksAIM($cid, $url);
	    $this->assignRef('clicks', $clicks);
	    $this->assignRef('limitstart', $limitstart);
	    $this->assignRef('limit', $limit);
	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( count($clicks), $limitstart, $limit );
	    $this->assignRef('pagination',	$pagination);

	} elseif ($layout == 'clicked') {

	    $cid = JRequest::getVar('cid', 0,'','string');
	    $clicked = $model->getCampaignEmailStatsAIMAll($cid, $limitstart, 1000);

	    $i=0;
	    $click=array();
	    foreach($clicked as $key => $value){
		$unset = true;
		foreach($value as $v){
		    if($v['action'] == 'click'){
			$unset = false;
		    }
		}
		if( !$unset ){
		    $click[$key] = $clicked[$key];
		    $i++;
		}

		if($i==$limit) break;
	    }
	    $this->assignRef('clicked', $click);
	    $this->assignRef('limitstart', $limitstart);
	    $this->assignRef('limit', $limit);
	    $total = $model->getCampaignStats($cid);
	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( $total['unique_clicks'], $limitstart, $limit );
	    $this->assignRef('pagination',	$pagination);

	} elseif ($layout == 'recipients') {

		$cid = JRequest::getVar('cid', 0,'','string');
		$url = urldecode(JRequest::getVar('url', 0,'','string'));
		$clicked = $model->getCampaignEmailStatsAIMAll($cid, $limitstart, $limit);
		$total = $model->getCampaignStats($cid);
		$this->assignRef('clicked', $clicked);
		$this->assignRef('limitstart', $limitstart);
		$this->assignRef('limit', $limit);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total['emails_sent'], $limitstart, $limit );
		$this->assignRef('pagination',	$pagination);

	} elseif ($layout =='opened') {

	    $cid = JRequest::getVar('cid', 0,'','string');
	    $items = $model->getOpens($cid);
	    $this->assignRef('limitstart', $limitstart);
	    $this->assignRef('limit', $limit);
	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( count($items), $limitstart, $limit );
	    $this->assignRef('pagination',	$pagination);

	} elseif ($layout =='abuse') {

	    $cid = JRequest::getVar('cid', 0,'','string');
	    $items = $model->getAbuse($cid);
	    $this->assignRef('limitstart', $limitstart);
	    $this->assignRef('limit', $limit);
	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( count($items), $limitstart, $limit );
	    $this->assignRef('pagination',	$pagination);

	} elseif ($layout =='unsubscribes') {

	    $cid = JRequest::getVar('cid', 0,'','string');
	    $items = $model->getUnsubscribes($cid);
	    $this->assignRef('limitstart', $limitstart);
	    $this->assignRef('limit', $limit);
	    jimport('joomla.html.pagination');
	    $pagination = new JPagination( count($items), $limitstart, $limit );
	    $this->assignRef('pagination',	$pagination);

	} else {
	    $doc = & JFactory::getDocument();
	    $file = JURI::base().'components/com_joomailermailchimpintegration/assets/css/campaigns.css';
	    $doc->addStyleSheet($file);
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MCauth    = new MCauth();

	    if ( !$MCapi || !$MCauth->MCauth() ) {
		$user =& JFactory::getUser();
		if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
		    || !version_compare(JVERSION,'1.6.0','ge') ) {
			JToolBarHelper::preferences('com_joomailermailchimpintegration', '450');
			JToolBarHelper::spacer();
		}
	    } else {

		if ($layout == 'sharereport') {
		    // Get data from the model
		    $palettes = & $this->get( 'Palettes' );
		    $this->assignRef('palettes', $palettes);
		} else {
		    JToolBarHelper::custom( 'shareReport', 'shareReport', 'shareReport', 'JM_SEND_REPORT', true, false );
		    JToolBarHelper::spacer();
		    JToolBarHelper::custom( 'analytics', 'analytics360', 'analytics360', 'Analytics360°', false, false );
		    JToolBarHelper::spacer();
		    $user =& JFactory::getUser();
		    if ( (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('core.admin', 'com_joomailermailchimpintegration'))
			|| !version_compare(JVERSION,'1.6.0','ge') ) {
			    JToolBarHelper::preferences('com_joomailermailchimpintegration', '450');
			    JToolBarHelper::spacer();
		    }
		}

		//	JToolBarHelper::custom( 'delete', 'delete', 'delete', 'JM_DELETE_REPORT', true, false );
		$folders = $this->get('Folders');
		$undefined[0] = array('folder_id' => 0, 'name' => '- '.JText::_('JM_SELECT_FOLDER').' -');
		$folder_id = JRequest::getVar('folder_id', 0, '', 'int');
		$folders = array_merge($undefined,$folders);
		$foldersDropDown = JHTML::_( 'select.genericlist', $folders, 'folder_id', 'onchange="document.adminForm.submit();"', 'folder_id', 'name' , $folder_id);
		$this->assignRef('foldersDropDown', $foldersDropDown);
		$filters = array();
		if($folder_id) { $filters['folder_id'] = $folder_id; }
		$filters['status'] = 'sent';
		$limit = JRequest::getVar('limit', 5, '', 'int');

		$cacheID = 'sent_campaigns';
		if(!$cache->get($cacheID, $cacheGroup)){
		    $campaigns = array();
		    $run = array('1');
		    $x = 0;
		    while(!empty($run)){
			$run =& $model->getCampaigns(array('status'=>'sent'), $x, 1000);
			if(empty($run)){ break; }
			$campaigns = array_merge($campaigns, $run);
			$x++;
		    }

		    
		    if( isset($campaigns[0]) ){
			foreach($campaigns as $c) {
			    $stats = $model->getCampaignStats($c['id']);
			    $advice = $model->getAdvice($c['id']);
			    if($stats) {
				$items[$c['id']]['folder_id'] = $c['folder_id'];
				$items[$c['id']]['title'] = $c['title'];
				$items[$c['id']]['subject'] = $c['subject'];
				$items[$c['id']]['send_time'] = $c['send_time'];
				$items[$c['id']]['emails_sent'] = $c['emails_sent'];
				$items[$c['id']]['stats'] = $stats;
				$items[$c['id']]['advice'] = $advice;
				$items[$c['id']]['archive_url'] = $c['archive_url'];
				$items[$c['id']]['twitter'] =& $model->getTwitterStats( $c['id'] );
				$items[$c['id']]['geo'] =& $model->getGeoStats( $c['id'] );
			    } else {
				$items[$c['id']]['folder_id'] = $c['folder_id'];
				$items[$c['id']]['title'] = $c['title'];
				$items[$c['id']]['subject'] = $c['subject'];
				$items[$c['id']]['send_time'] = $c['send_time'];
				$items[$c['id']]['emails_sent'] = $c['emails_sent'];
				$items[$c['id']]['stats'] = '';
				$items[$c['id']]['advice'] = '';
				$items[$c['id']]['archive_url'] = $c['archive_url'];
				$items[$c['id']]['twitter'] =& $model->getTwitterStats( $c['id'] );
				$items[$c['id']]['geo'] =& $model->getGeoStats( $c['id'] );
			    }
			}
		    }
		    $cache->store(json_encode($items), $cacheID, $cacheGroup);
		}
		$campaigns = json_decode($cache->get($cacheID, $cacheGroup), true);

		// get timestamp of when the cache was modified
		$joomlamailerCache = new joomlamailerCache('file');
		$cacheDate = $joomlamailerCache->getCreationTime($cacheID, $cacheGroup);
		$this->assignRef('cacheDate', $cacheDate);

		if( $folder_id ){
		    foreach( $campaigns as $k => $v )
		    {
			if( $v['folder_id'] != $folder_id ){
			    unset( $campaigns[$k] );
			}
		    }
		}

		$total = count($campaigns);

		$items = array();
		$x = 0;
		if($total){
		    foreach( $campaigns as $k => $v ){
			if( $x == ( $limitstart + $limit ) ){
			    break;
			}
			if( $x >= $limitstart ){
			    $items[$k] = $v;
			}
			$x++;
		    }
		}

/*
		// cache total number of sent campaigns
		$cacheID = 'totalSentCampaigns';
		if(!$cacheData = $cache->get($cacheID, $cacheGroup)){
		    $total = array('1');
		    $totalSentCampaigns = 0;
		    $i=0;
		    while(!empty($total)){
			$total = & $model->getCampaigns( array('status'=>'sent'), $i, 1000 );
			if(!empty($total)){
			    $totalSentCampaigns = $totalSentCampaigns + count($total);
			    $i++;
			} else {
			    break;
			}

		    }
		    $cache->store($totalSentCampaigns, $cacheID, $cacheGroup);
		}
		$totalSentCampaigns = $cache->get($cacheID, $cacheGroup);

		$page = ($limit) ? round($limitstart/$limit,0) : 0;
		$entries = ($limit) ? $limit : $totalSentCampaigns;

		$cacheID = 'campaigns_'.$page.'_'.$entries;
		if(!$cacheData = $cache->get($cacheID, $cacheGroup)){
		    if($entries>1000){
			$campaigns = array();
			$run = array('1');
			$x = 0;
			while(!empty($run)){
			    $run =& $model->getCampaigns(array('status'=>'sent'), $x, $entries);
			    if(empty($run)){ break; }
			    $campaigns = array_merge($campaigns, $run);
			    $x++;
			}

		    } else {
			$campaigns =& $model->getCampaigns($filters, $page, $entries);
		    }
				     
		    $items = array();
		    if( isset($campaigns[0]) ){
			foreach($campaigns as $c) {
			    $stats = $model->getCampaignStats($c['id']);
			    $advice = $model->getAdvice($c['id']);
			    if($stats) {
				$items[$c['id']]['folder_id'] = $c['folder_id'];
				$items[$c['id']]['title'] = $c['title'];
				$items[$c['id']]['subject'] = $c['subject'];
				$items[$c['id']]['send_time'] = $c['send_time'];
				$items[$c['id']]['emails_sent'] = $c['emails_sent'];
				$items[$c['id']]['stats'] = $stats;
				$items[$c['id']]['advice'] = $advice;
				$items[$c['id']]['archive_url'] = $c['archive_url'];
				$items[$c['id']]['twitter'] =& $model->getTwitterStats( $c['id'] );
				$items[$c['id']]['geo'] =& $model->getGeoStats( $c['id'] );
			    } else {
				$items[$c['id']]['folder_id'] = $c['folder_id'];
				$items[$c['id']]['title'] = $c['title'];
				$items[$c['id']]['subject'] = $c['subject'];
				$items[$c['id']]['send_time'] = $c['send_time'];
				$items[$c['id']]['emails_sent'] = $c['emails_sent'];
				$items[$c['id']]['stats'] = '';
				$items[$c['id']]['advice'] = '';
				$items[$c['id']]['archive_url'] = $c['archive_url'];
				$items[$c['id']]['twitter'] =& $model->getTwitterStats( $c['id'] );
				$items[$c['id']]['geo'] =& $model->getGeoStats( $c['id'] );
			    }
			}
		    }

		    $cache->store(json_encode($items), $cacheID, $cacheGroup);
		}
		// get timestamp of when the cache was modified
		$joomlamailerCache = new joomlamailerCache($cache->getInstance()->_handler);
		$cacheDate = $joomlamailerCache->getCreationTime($cacheID, $cacheGroup);
		$this->assignRef('cacheDate', $cacheDate);

		$items = json_decode($cache->get($cacheID, $cacheGroup), true);

		$x = 0;
		foreach( $items as $item ){

		    if( $folder_id ){
			if( $item[$x]['folder_id'] != $folder_id ){
			    unset( $item[$x] );
			}
		    }
		    $x++;
		}
 */
		
	//	var_dump($items);die;

		jimport('joomla.html.pagination');
	//	$pagination = new JPagination( $totalSentCampaigns, $limitstart, $limit );
		$pagination = new JPagination( $total, $limitstart, $limit );
		$this->assignRef('pagination',	$pagination);
	    }
	}

	$this->assignRef('items', $items);

	parent::display($tpl);
	require_once( JPATH_COMPONENT.DS.'helpers'.DS.'footer.php' );
    }
}
