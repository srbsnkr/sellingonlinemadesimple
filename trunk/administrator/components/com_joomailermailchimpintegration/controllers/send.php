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

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

$task =	JRequest::getVar('task', '', 'post', 'string', JREQUEST_ALLOWRAW );

class joomailermailchimpintegrationsControllerSend extends joomailermailchimpintegrationsController
{

    function __construct()
    {
	parent::__construct();
    }

    function send() {

	$db = & JFactory::getDBO();
	$msg = false;
	$error = '';
	$list_error = false;

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$archiveDir = $params->get( $paramsPrefix.'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' );
	$model	=& $this->getModel('send');
	$clientDetails = $model->getClientDetails();

	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);

//	$MCSTS = new jmsts($MCapi);

	$time     = JRequest::getVar('time', 0, '', 'string');
	$listId   = JRequest::getVar('listId', '', 'post', 'string');
	$test     = JRequest::getVar('test', 0, 'post', 'int');
	$trackOpens = (bool)JRequest::getBool('trackOpens', false, 'post');
	$trackHTML  = (bool)JRequest::getBool('trackHTML',  false, 'post');
	$trackText  = (bool)JRequest::getBool('trackText',  false, 'post');
	$ecomm360   = (bool)JRequest::getBool('ecomm360',   false, 'post');
	$campaignType = JRequest::getVar('campaignType', 0, 'post');
	$offsetTime   = JRequest::getVar('offset-time', 0, 'post');
	$offsetUnits  = JRequest::getVar('offset-units', 0, 'post');
	$offsetDir    = JRequest::getVar('offset-dir', 0, 'post');
	$event        = JRequest::getVar('event', 0, 'post');
	$mergefield   = JRequest::getVar('mergefield', 0, 'post');

	$emails   = JRequest::getVar('email', '', '', 'array');
	$noGos = array( 'Email 1', 'Email 2', 'Email 3', 'Email 4', 'Email 5' );
	foreach($emails as $key => $value) {
	    if($value == "" || in_array($value, $noGos) ) {
		unset($emails[$key]);
	    }
	}
	$emails = array_values($emails);

	$timewarp  = JRequest::getVar('timewarp', 0, '', 'int');
	$schedule  = JRequest::getVar('schedule', 0, '', 'int');
	if(!$schedule){
	    $delivery = 'Immediately';
	} else {
	    $deliveryDate = JRequest::getVar('deliveryDate', 'Immediately', '', 'string');
	    $deliveryTime = JRequest::getVar('deliveryTime', '', '', 'string');
	    //		if($test) { $delivery = 'Immediately'; }
	    //		else
	    if( $deliveryDate != 'Immediately' ){
		$delivery = $deliveryDate.' '.$deliveryTime.':00';
		// convert time to GMT
		setlocale(LC_TIME, 'en_GB');
		$delivery = gmstrftime("%Y-%m-%d %H:%M:%S", strtotime($delivery) );
	    } else {
		$delivery = 'Immediately';
	    }
	}

	$useSegments = JRequest::getVar('useSegments', 0, '', 'int');
	if($useSegments){

	    $type = $condition = $conditionDetailValue = array();
	    for( $i=1; $i<11; $i++){

		$type[]			= JRequest::getVar('segmenttype'.$i, '');
		$condition[]		= JRequest::getVar('segmentTypeCondition_'.$i, '');
		$conditionDetailValue[] = JRequest::getVar('segmentTypeConditionDetailValue_'.$i, '');

	    }
	    // remove empty values
	    $type = array_values(array_filter($type));
	    $condition = array_values(array_filter($condition));
	    $conditionDetailValue = array_values(array_filter($conditionDetailValue));

	    $conditions = array();

	    for($i=0;$i<count($type);$i++){
		if( is_numeric($type[$i]) ){
		    $type[$i] = 'interests-'.$type[$i];
		}
		$conditions[] = array('field'=>$type[$i], 'op'=>$condition[$i], 'value'=>$conditionDetailValue[$i]);
	    }

	    $segment_opts = array('match'=>JRequest::getVar('match', 'any', 'post', 'string'), 'conditions'=>$conditions);

	} else {
	    $segment_opts = '';
	}

	$query = "SELECT * FROM #__joomailermailchimpintegration_campaigns WHERE creation_date = '".$time."'";
	$db->setQuery($query);
	$cDetails = $db->loadObjectList();
	$cDetails = $cDetails[0];

	$campaign_name_ent = htmlentities($cDetails->name);
	$campaign_name_ent = str_replace(' ','_',$campaign_name_ent);
	$html_file = JURI::root() . (substr($archiveDir,1)) . "/" . $campaign_name_ent.".html";
	$content = array( 'url' => $html_file );

	// remove cache-preventing meta tags from campaign to avoid rendering issues in email clients
	$metaData = array( "<meta http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n",
			   "<meta http-Equiv=\"Pragma\" Content=\"no-cache\">\n",
			   "<meta http-Equiv=\"Expires\" Content=\"0\">\n");

	$filename = JPATH_SITE . $archiveDir .'/'. $campaign_name_ent.".html";
	$template = JFile::read( $filename );
	$template = str_replace( $metaData, '', $template );
	$handle = JFile::write( $filename, $template );

	if(!$msg){

	    $lists  = $MC->lists();
	    if(!$listId){
		$i = 0;
		if( $clientDetails['plan_type'] == 'free' ){
		    for($i=0;$i<count($lists);$i++){
			if( $lists[$i]['member_count']<=2000){
			    $listId = $lists[$i]['id'];
			    $memberCount = $lists[$i]['member_count'];
			    break;
			}
		    }
		    if(!$listId){
			$error = 'error';
			$msg   = JText::_('JM_TO_MANY_RECIPIENTS');
			$link  = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$time;
			$this->setRedirect( $link, $msg );
		    }
		} else {
		    $listId = $lists[$i]['id'];
		}

	    } else {
		foreach($lists as $list){
		    if($list['id'] == $listId){
			$memberCount = $list['member_count'];
			break;
		    }
		}
	    }

	    if( !$test && $clientDetails['plan_type'] == 'free' && $memberCount > 2000 ){
		$error = 'error';
		$msg   = JText::_('JM_TO_MANY_RECIPIENTS');
		$link  = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$time;
	    } else {

		// submit to MC
		$type = 'regular';
//		$opts['list_id']	= $cDetails->list_id;
		$opts['list_id']	= $listId;
		$opts['title'] = $cDetails->name;
		if($test){
		    $opts['subject']	= JText::_('JM_CAMPAIGN_TEST').': '.$cDetails->subject;
		} else {
		    $opts['subject']	= $cDetails->subject;
		    if($timewarp){
			$opts['timewarp'] = true;
		    }
		}
		$opts['from_email']	= $cDetails->from_email;
		$opts['from_name']	= $cDetails->from_name;
		$opts['tracking']=array('opens' => $trackOpens, 'html_clicks' => $trackHTML, 'text_clicks' => $trackText);
		$opts['ecomm360'] = $ecomm360;
		$opts['authenticate'] = true;
//		$opts['analytics'] = array('google'=>'my_google_analytics_key');
		$opts['inline_css'] = true;
		$opts['generate_text'] = true;
		$opts['auto_footer'] = false;
		$opts['folder_id'] = $cDetails->folder_id;

		//Check for auto_tweet
		$tweet = JRequest::getVar('useTwitter');
		if($tweet) {
		    $opts['auto_tweet'] = true;
		}

		//Check for autoresponder
		$type_opts = array();
		if($campaignType == 1) {
		    $type = 'auto';
		    $type_opts['offset-units'] = $offsetUnits;
		    $type_opts['offset-time'] = $offsetTime;
		    $type_opts['offset-dir'] = $offsetDir;
		    $type_opts['event'] = $event;
		    $type_opts['event-datemerge'] = $mergefield;
		    // TODO: implement autoresponder folders
		    unset($opts['folder_id']);
		}

		$c_id = $MC->campaignCreate($type, $opts, $content, $segment_opts, $type_opts);

		if ( !$MC->errorCode ) {
		    if($test){
			$send = $MC->campaignSendTest( $c_id, $emails );
			if ( $c_id ) {
			    // wait 5 seconds for the campaign to be sent
			    sleep(5);
			    $MC->campaignDelete($c_id);
			}
		    } else {
			if( !$schedule ){
			    $send = $MC->campaignSendNow( $c_id );
			} else {
			    $send = $MC->campaignSchedule( $c_id, $delivery );
			}
		    }
		}

		if ( $MC->errorCode ) {
		    $msg = MCerrorHandler::getErrorMsg($MC);
		    $link = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$time;
		} else {
		    if($test){
			$msg = JText::_('JM_TEST_CAMPAIGN_SENT');
			$link = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$time;
		    } else {
			if( $schedule ){
			    $query = "UPDATE #__joomailermailchimpintegration_campaigns SET `sent`=1, `cid`='".$c_id."' WHERE creation_date = '".$time."'";
			    $db->setQuery($query);
			    $db->query();
			    $msg = JText::_('JM_CAMPAIGN_SCHEDULED');
			    $link = 'index.php?option=com_joomailermailchimpintegration&view=main';
			} else {
			    $query = "UPDATE #__joomailermailchimpintegration_campaigns SET `sent`=2, `cid`='".$c_id."' WHERE creation_date = '".$time."'";
			    $db->setQuery($query);
			    $db->query();

			    if($campaignType == 1) {
				$msg = JText::_('JM_AUTORESPONDER_CREATED');
			    } else {
				$msg = JText::_('JM_CAMPAIGN_SENT');
				// clear reports cache
				require_once( JPATH_ADMINISTRATOR .DS. 'components'.DS.'com_joomailermailchimpintegration'.DS.'controllers'.DS.'campaigns.php' );
				joomailermailchimpintegrationsControllerCampaigns::clearReportsCache();
			    }
			    $link = 'index.php?option=com_joomailermailchimpintegration&view=campaigns';
			}
		    }
		}
	    }
	}

	$this->setRedirect( $link, $msg, $error );
    }

    function cancel()
    {
	$msg = JText::_( 'JM_OPERATION_CANCELLED' );
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=archive', $msg );
    }

    function getSegmentFields(){

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$dc = explode('-',$MCapi);
	$dc = $dc[1];
	JHTML::_('behavior.calendar');
	$model	  =& $this->getModel('send');
	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$campaign = $elements->campaign;
	$listId  = $elements->listId;
	$type  = $elements->type;
	$condition  = $elements->condition;
	if(isset($elements->conditionDetail)){
	    $conditionDetail = $elements->conditionDetail;
	} else {
	    $conditionDetail = '';
	}

	$nr = $elements->nr;

	$interests = $model->getInterestGroupings($listId);
	if($interests){
	    foreach($interests as $int){
		$ints[]   = $int['name'];
		$intIds[] = $int['id'];
		foreach($int['groups'] as $group){
		    $intVals[$int['id']][] = $group['name'];
		}
	    }
	}
	$mergevars = $model->getMergeVars($listId);
	$mvTags = array();
	if($mergevars){
	    foreach($mergevars as $mv){
		if($mv['tag'] != 'EMAIL' && $mv['tag'] != 'FNAME' && $mv['tag'] != 'LNAME'){
		    $mvs[]   = $mv['name'];
		    $mvTags[] = $mv['tag'];
		    $mvTypes[$mv['tag']] = $mv['field_type'];
		    if(isset($mv['choices'])){
			foreach($mv['choices'] as $group){
			    $mvVals[$mv['tag']][] = $group;
			}
		    }
		}
	    }
	}


	if( $type=='date'){
	    $this->campaigns = $model->getCampaigns();
	    if( !isset($this->campaigns[0]) ){
		$disabled = 'disabled="disabled"';
		$campaignDate = '('.JText::_('JM_NO_CAMPAIGN_SENT').')';
		$noCampain = ' - ('.JText::_('JM_NO_CAMPAIGN_SENT').')';
		$conditionDetail = 'date';
	    } else {
		$disabled = '';
		$campaignDate = $this->campaigns[0]['send_time'];
		$noCampain = '';
	    }
	    $response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
				 <option value="gt" '.(($condition=='gt')?'selected="selected"':'').'>'.JText::_('JM_IS_AFTER').'</option>
				 <option value="lt" '.(($condition=='lt')?'selected="selected"':'').'>'.JText::_('JM_IS_BEFORE').'</option>
				 <option value="eq" '.(($condition=='eq')?'selected="selected"':'').'>'.JText::_('JM_IS').'</option>
				</select>
				<select name="segmentTypeConditionDetail_'.$nr.'" id="segmentTypeConditionDetail_'.$nr.'" onchange="getSegmentFields( \'segmentTypeConditionDiv_'.$nr.'\', '.$nr.' );">
				 <option value="last" '.$disabled.'>'.JText::_('JM_THE_LAST_CAMPAIGN_WAS_SENT').' - '.substr($campaignDate,0, -9).'</option>
				 <option value="campaign" '.$disabled;
	    if($conditionDetail=='campaign') $response['html'] .= ' selected="selected"';
	    $response['html'] .= '>'.JText::_('JM_A_SPECIFIC_CAMPAIGN_WAS_SENT').$noCampain.'</option>
				<option value="date"';
	    if($conditionDetail=='date') $response['html'] .= ' selected="selected"';
	    $response['html'] .= '>'.JText::_('JM_A_SPECIFIC_DATE').'</option>
				</select>';

	    if($conditionDetail=='campaign'){
		$response['html'] .= '<div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv" style="top:0;">'
		.'<select name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'">';
		foreach($this->campaigns as $campaign){
		    if(strlen($campaign['title'])>16){ $campaign['title'] = substr($campaign['title'],0,13).'...'; }
		    $response['html'] .= '<option value="'.$campaign['send_time'].'">'.$campaign['title'].' ('.substr($campaign['send_time'],0, -9).')</option>';
		}
		$response['html'] .= '</select>';
	    } else if ($conditionDetail=='date'){
		$response['html'] .= '<div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv">';
		$response['html'] .= JHTML::calendar(date('Y-m-d'), 'segmentTypeConditionDetailValue_'.$nr.'', 'segmentTypeConditionDetailValue_'.$nr.'', '%Y-%m-%d',
		array('size'=>'12',
									'maxlength'=>'10'
									));
		$response['html'] .= '</div>';
		$response['js'] = 'Calendar.setup({inputField : "segmentTypeConditionDetailValue_'.$nr.'", ifFormat : "%Y-%m-%d", button : "segmentTypeConditionDetailValue_'.$nr.'_img", align : "Tl", singleClick : true });';
	    } else {
		$response['html'] .= '<input type="hidden" value="'.$this->campaigns[0]['send_time'].'" name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'" /></div>';
	    }

	} else if( $type=='email' ||
		   $type=='fname' ||
		   $type=='lname' ){
			$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
						    <option value="eq">'.JText::_('JM_IS').'</option>
						    <option value="ne">'.JText::_('JM_IS_NOT').'</option>
						    <option value="like">'.JText::_('JM_CONTAINS').'</option>
						    <option value="nlike">'.JText::_('JM_DOES_NOT_CONTAIN').'</option>
						    <option value="starts">'.JText::_('JM_STARTS_WITH').'</option>
						    <option value="ends">'.JText::_('JM_ENDS_WITH').'</option>
						    <option value="gt">'.JText::_('JM_IS_GREATER_THAN').'</option>
						    <option value="lt">'.JText::_('JM_IS_LESS_THAN').'</option>
					    </select>
					    <div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv">
					    <input type="text" value="" id="segmentTypeConditionDetailValue_'.$nr.'" name="segmentTypeConditionDetailValue_'.$nr.'"/>
					    </div>';

	} else if( $interests && in_array($type, $intIds)){
		    $response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
						<option value="one">'.JText::_('JM_ONE_OF').'</option>
						<option value="all">'.JText::_('JM_ALL_OF').'</option>
						<option value="none">'.JText::_('JM_NONE_OF').'</option>
					</select>
					<div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv">
					<select multiple="multiple" size="3" id="segmentTypeConditionDetailValue_'.$nr.'" name="segmentTypeConditionDetailValue_'.$nr.'">';
		    foreach( $intVals[$type] as $val){
			$response['html'] .= '<option value="'.$val.'">'.$val.'</option>';
		    }

		    $response['html'] .= '</select></div>';

	} else if( $mergevars && in_array($type, $mvTags)){
		    if($mvTypes[$type] == 'radio' || $mvTypes[$type] == 'dropdown'){
			    $response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
							<option value="eq">'.JText::_('JM_IS').'</option>
							<option value="ne">'.JText::_('JM_IS_NOT').'</option>
						</select>
						<div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv">
						<select multiple="multiple" size="3" id="segmentTypeConditionDetailValue_'.$nr.'" name="segmentTypeConditionDetailValue_'.$nr.'">';
			    foreach( $mvVals[$type] as $val){
				$response['html'] .= '<option value="'.$val.'">'.$val.'</option>';
			    }
			    $response['html'] .= '</select></div>';
		    } else if($mvTypes[$type] == 'date'){
			    $response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
							<option value="gt">'.JText::_('JM_IS_AFTER').'</option>
							<option value="lt">'.JText::_('JM_IS_BEFORE').'</option>
							<option value="eq">'.JText::_('JM_IS').'</option>
							<option value="ne">'.JText::_('JM_IS_NOT').'</option>
						</select>';
			    $response['html'] .= '<div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv">';
			    $response['html'] .= JHTML::calendar(date('Y-m-d'), 'segmentTypeConditionDetailValue_'.$nr.'', 'segmentTypeConditionDetailValue_'.$nr.'', '%Y-%m-%d',
									    array('size'=>'12',
									    'maxlength'=>'10'
									    ));
			    $response['html'] .= '</div>';
			    $response['js'] = 'Calendar.setup({inputField : "segmentTypeConditionDetailValue_'.$nr.'", ifFormat : "%Y-%m-%d", button : "segmentTypeConditionDetailValue_'.$nr.'_img", align : "Tl", singleClick : true });';

		    } else {
			    $response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
							<option value="eq">'.JText::_('JM_IS').'</option>
							<option value="ne">'.JText::_('JM_IS_NOT').'</option>
							<option value="like">'.JText::_('JM_CONTAINS').'</option>
							<option value="nlike">'.JText::_('JM_DOES_NOT_CONTAIN').'</option>
							<option value="starts">'.JText::_('JM_STARTS_WITH').'</option>
							<option value="ends">'.JText::_('JM_ENDS_WITH').'</option>
							<option value="gt">'.JText::_('JM_IS_GREATER_THAN').'</option>
							<option value="lt">'.JText::_('JM_IS_LESS_THAN').'</option>
						</select>
						<div id="segmentTypeConditionDiv_'.$nr.'" class="segmentTypeConditionDetailDiv">
						<input type="text" value="" id="segmentTypeConditionDetailValue_'.$nr.'" name="segmentTypeConditionDetailValue_'.$nr.'"/>
						</div>';
		    }

	} else if($type=='rating'){
		$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
					    <option value="eq">'.JText::_('JM_IS').'</option>
					    <option value="ne">'.JText::_('JM_IS_NOT').'</option>
					    <option value="gt">'.JText::_('JM_IS_GREATER_THAN').'</option>
					    <option value="lt">'.JText::_('JM_IS_LESS_THAN').'</option>
				    </select>
				    <div style="margin-bottom:11px;">
				    <ul class="memberRating" onmouseout="restoreRating('.$nr.');">
					    <li class="rating_1" value="1" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_2" value="2" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_3" value="3" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_4" value="4" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_5" value="5" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
				    </ul>
				    <input type="hidden" value="0" name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'" />
				    </div>';


	} else if($type=='aim'){
		$this->campaigns  = $model->getCampaigns();
		$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
					    <option value="open">'.JText::_('JM_OPENED_').'</option>
					    <option value="noopen">'.JText::_('JM_NOT_OPENED_').'</option>
					    <option value="click">'.JText::_('JM_CLICKED').'</option>
					    <option value="noclick">'.JText::_('JM_NOT_CLICKED').'</option>
				    </select>
				    <select name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'">
					    <option value="any">'.JText::_('JM_ANY_CAMPAIGN').'</option>';
		foreach($this->campaigns as $campaign){
			$response['html'] .= '<option value="'.$campaign['id'].'">'.$campaign['title'].' ('.$campaign['send_time'].')</option>';
		}
		$response['html'] .= '</select>';

	} else if($type=='social_network'){
		$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
					    <option value="member">'.JText::_('JM_IS_A_MEMBER_OF').'</option>
					    <option value="notmember">'.JText::_('JM_IS_NOT_A_MEMBER_OF').'</option>
				    </select>
				    <select name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'">
					    <option value="twitter">Twitter</option>
					    <option value="facebook">Facebook</option>
					    <option value="myspace">MySpace</option>
					    <option value="linkedin">LinkedIn</option>
					    <option value="flickr">Flickr</option>';
		$response['html'] .= '</select>';
	} else if($type=='social_influence'){
		$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
					    <option value="eq">'.JText::_('JM_IS').'</option>
					    <option value="ne">'.JText::_('JM_IS_NOT').'</option>
					    <option value="gt">'.JText::_('JM_IS_GREATER_THAN').'</option>
					    <option value="lt">'.JText::_('JM_IS_LESS_THAN').'</option>
				    </select>
				    <div style="margin-bottom:11px;">
				    <ul class="memberRating" onmouseout="restoreRating('.$nr.');">
					    <li class="rating_1" value="1" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_2" value="2" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_3" value="3" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_4" value="4" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
					    <li class="rating_5" value="5" onclick="rating('.$nr.',this.value,1);" onmouseover="rating('.$nr.',this.value,0);"></li>
				    </ul>
				    <input type="hidden" value="0" name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'" />
				    </div>';
	} else if($type=='social_gender'){
		$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
					    <option value="eq">'.JText::_('JM_IS').'</option>
					    <option value="ne">'.JText::_('JM_IS_NOT').'</option>
				    </select>
				    <select name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'">
					    <option value="female">'.JText::_('JM_FEMALE').'</option>
					    <option value="male">'.JText::_('JM_MALE').'</option>
				    </select>';
	} else if($type=='social_age'){
		$response['html'] = '<select name="segmentTypeCondition_'.$nr.'" id="segmentTypeCondition_'.$nr.'">
					    <option value="eq">'.JText::_('JM_IS').'</option>
					    <option value="ne">'.JText::_('JM_IS_NOT').'</option>
					    <option value="gt">'.JText::_('JM_IS_GREATER_THAN').'</option>
					    <option value="lt">'.JText::_('JM_IS_LESS_THAN').'</option>
				    </select>
				    <div id="slider_'.$nr.'" class="slider">
					    <div id="knob_'.$nr.'"class="knob"></div>
				    </div>
				    <input type="text" value="0" size="2" class="ageResult" name="segmentTypeConditionDetailValue_'.$nr.'" id="segmentTypeConditionDetailValue_'.$nr.'">';
		$response['js'] = 'var mySlide_'.$nr.' = new Slider($(\'slider_'.$nr.'\'), $(\'knob_'.$nr.'\'), {
								steps: 99,
								onChange: function(step){
									$(\'segmentTypeConditionDetailValue_'.$nr.'\').value = step;
								}
							}).set(0);';
	} else {
		$response['html'] = '';
	}

	echo json_encode( $response );
    }


    function testSegments(){

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);

	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$listId   = $elements->listId;
	$condCount = $elements->condCount;

	$type			= array_filter(explode('|*|', $elements->type));
	$condition		= array_filter(explode('|*|', $elements->condition));
	$conditionDetailValue	= array_filter(explode('|*|', $elements->conditionDetailValue));

	$conditions = array();

	for($i=0;$i<count($type);$i++){
	    if( is_numeric($type[$i]) ){
		$type[$i] = 'interests-'.$type[$i];
	    }
	    $conditionDetailValue[$i] = array_filter(array_unique(explode('|*|', $conditionDetailValue[$i])));
	    $conditionDetailValue[$i] = implode(',', $conditionDetailValue[$i]);

	    $conditions[] = array('field'=>$type[$i], 'op'=>$condition[$i], 'value'=>$conditionDetailValue[$i]);
	}

	$opts = array('match'=>$elements->match, 'conditions'=>$conditions);

	$result = $MC->campaignSegmentTest( $listId, $opts );

	if(!$result){
	    $response['msg'] = sprintf ( JText::_( 'JM_X_RECIPIENTS_IN_THIS_SEGMENT' ), 0 );
	    $response['creditCount'] = 0;
	} else {
	    $response['msg'] = sprintf ( JText::_( 'JM_X_RECIPIENTS_IN_THIS_SEGMENT' ), $result );
	    $response['creditCount'] = $result;
	}

	echo json_encode( $response );
    }

    function addCondition(){

	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$listId   = $elements->listId;

	$model	  =& $this->getModel('send');
	$this->interests = $model->getInterestGroupings($listId);
	$this->mergevars = $model->getMergeVars($listId);
	$this->campaigns = $model->getCampaigns();

	$x = $elements->conditionCount+1;
	$response['js'] = false;

	$content = '<select name="segmenttype'.$x.'" id="segmenttype'.$x.'" class="segmentType">
			<option value="date">'.JText::_('JM_DATE_ADDED').'</option>
			<option value="email">'.JText::_('JM_EMAIL_ADDRESS').'</option>
			<option value="fname">'.JText::_('JM_FIRSTNAME').'</option>
			<option value="lname">'.JText::_('JM_LASTNAME').'</option>
			<option value="rating">'.JText::_('JM_MEMBER_RATING').'</option>
			<option value="aim">'.JText::_('JM_AIM').'</option>
			<option value="social_network">'.JText::_('JM_SOCIAL_NETWORK').'</option>
			<option value="social_influence">'.JText::_('JM_SOCIAL_INFLUENCE').'</option>
			<option value="social_gender">'.JText::_('JM_SOCIAL_GENDER').'</option>
			<option value="social_age">'.JText::_('JM_SOCIAL_AGE').'</option>';

	if($this->interests){
	    foreach ($this->interests as $interest){
		$content .= '<option value="'.$interest['id'].'">'.((strlen($interest['name'])>18) ? substr($interest['name'], 0, 15).'...' : $interest['name']).'</option>';
	    }
	}
	if($this->mergevars){
	    foreach ($this->mergevars as $mv){
		if($mv['tag'] != 'EMAIL' && $mv['tag'] != 'FNAME' && $mv['tag'] != 'LNAME'){
		    $content .= '<option value="'.$mv['tag'].'">'.((strlen($mv['name'])>18) ? substr($mv['name'], 0, 15).'...' : $mv['name']).'</option>';
		}
	    }
	}

	$content .= '</select>
		    <div id="segmentTypeConditionDiv_'.$x.'" class="segmentConditionDiv">
			<select name="segmentTypeCondition_'.$x.'" id="segmentTypeCondition_'.$x.'">
				<option value="gt">'.JText::_('JM_IS_AFTER').'</option>
				<option value="lt">'.JText::_('JM_IS_BEFORE').'</option>
				<option value="eq">'.JText::_('JM_IS').'</option>
			</select>
			<select name="segmentTypeConditionDetail_'.$x.'" id="segmentTypeConditionDetail_'.$x.'">';
	if( !isset($this->campaigns[0]) ){
	    $disabled = 'disabled="disabled"';
	    $campaignDate = '('.JText::_('JM_NO_CAMPAIGN_SENT').')';
	    $noCampain = ' - ('.JText::_('JM_NO_CAMPAIGN_SENT').')';
	} else {
	    $disabled = '';
	    $campaignDate = $this->campaigns[0]['send_time'];
	    $noCampain = '';
	}
	$content .= '<option value="last" '.$disabled.'>'.JText::_('JM_THE_LAST_CAMPAIGN_WAS_SENT').' - '.$campaignDate.'</option>
		    <option value="campaign" '.$disabled.'>'.JText::_('JM_A_SPECIFIC_CAMPAIGN_WAS_SENT').''.$noCampain.'</option>
		    <option value="date">'.JText::_('JM_A_SPECIFIC_DATE').'</option>
		</select>
		<div id="segmentTypeConditionDetailDiv_'.$x.'" class="segmentTypeConditionDetailDiv">';
	if( isset($this->campaigns[0]) ){
	    $content .= '<input type="hidden" value="'.$this->campaigns[0]['send_time'].'" name="segmentTypeConditionDetailValue_'.$x.'" id="segmentTypeConditionDetailValue_'.$x.'" />';
	} else {
	    $content .= JHTML::calendar( date('Y-m-d'), 'segmentTypeConditionDetailValue_'.$x.'', 'segmentTypeConditionDetailValue_'.$x.'', '%Y-%m-%d',
			    array('size'=>'12',
			    'maxlength'=>'10'
			    ));
	    $response['js'] .= 'Calendar.setup({inputField : "segmentTypeConditionDetailValue_'.$x.'", ifFormat : "%Y-%m-%d", button : "segmentTypeConditionDetailValue_'.$x.'_img", align : "Tl", singleClick : true });';
	}
	$content .= '</div></div>';

	$response['html'] = $content .'</div><div class="removeCondition"><a href="javascript:void(0);removeCondition('.$x.');" title="'.JText::_('JM_REMOVE').'"><img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/deselect.png" alt="'.JText::_('JM_REMOVE').'" style="padding:3px 5px;"/></a>';

	$response['js'] .= '$(\'segmenttype'.$x.'\').addEvent(\'change\', function(e){
				getSegmentFields( \'segmentTypeConditionDiv_'.$x.'\', '.$x.' );
			    });
			    $(\'segmentTypeConditionDetail_'.$x.'\').addEvent(\'change\', function(e){
				getSegmentFields( \'segmentTypeConditionDiv_'.$x.'\', '.$x.' );
			    });';

	echo  json_encode( $response );
    }


    function addInterests(){

	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$listId   = $elements->listId;

	$model	  =& $this->getModel('send');
	$interests = $model->getInterestGroupings($listId);
	$mergevars = $model->getMergeVars($listId);

	$response['id']   = false;
	$response['name'] = false;
	$response['counter'] = 0;

	if($interests){
	    foreach ($interests as $int){
		$response['id'][] = $int['id'];
		$response['name'][] = (strlen($int['name'])>18) ? substr($int['name'], 0, 15).'...' : $int['name'];
	    }
	    $response['counter'] = count($interests);
	}

	if($mergevars){
	    foreach ($mergevars as $mv){
		if($mv['tag'] != 'EMAIL' && $mv['tag'] != 'FNAME' && $mv['tag'] != 'LNAME'){
		    $response['id'][] = $mv['tag'];
		    $response['name'][] = (strlen($mv['name'])>18) ? substr($mv['name'], 0, 15).'...' : $mv['name'];
		    $response['counter']++;
		}
	    }
	}

	echo json_encode( $response );
    }

    function merges_ajax() {
	$response = array();
	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$listId   = $elements->listid;
	$MC = $this->MC_object();
	$result = $MC->listMergeVars( $listId );
	
	if( $result ){
	    $i=0;
	    foreach($result as $r) {
		if ($r['field_type'] != 'date') {
		    unset($result[$i]);
		}
		$i++;
	    }

	    $first=new stdClass;
	    $first->tag=-1;
	    $first->name='-- '.JText::_('JM_SELECT_A_MERGE_FIELD').' --';
	    $merges = array_merge(array($first),$result); 

	    $response['html'] = JHTML::_( 'select.genericlist', $merges, 'mergefield', '', 'tag', 'name' , '');
	} else {
	    $response['html'] = '<a id="mergefield" href="index.php?option=com_joomailermailchimpintegration&view=fields&listid='.$listId.'">'.JText::_('JM_CREATE_MERGE_FIELDS').'</a>';
	}
	
	echo json_encode( $response );
    }

    function ajax_sync_hotness() {

	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	if($elements->done == 0 ) {
	    $_SESSION['abortAJAX'] = 0;
	    unset($_SESSION['addedUsers']);
	    unset($_SESSION['HotnessExists']);
	}

	if($_SESSION['abortAJAX'] != 1){

	    $db     =& JFactory::getDBO();
	    $model  =& $this->getModel('sync');
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MC = new joomlamailerMCAPI($MCapi);
	    $MCerrorHandler = new MCerrorHandler();

	    $list_id  = $elements->listid;
	    $step = $elements->step;
	    $offset = $elements->offset;

	    // retrieve hotness rating
	    require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . '/libraries/joomailer/hotActivityComposite.php');
	    $composite = new hotActivityComposite();
	    $hotnessRating = $composite->getAllUserHotnessValue( $list_id );

	    if(isset($_SESSION['addedUsers'])){
		$exclude = $_SESSION['addedUsers'];
	    } else {
		$exclude = array();
	    }

	    if( !$elements->failed ) { $elements->failed = array(); }
	    $exclude = array_merge($exclude, $elements->failed);

	    $exclude = implode('","', $exclude);
	    $exclude = '"'.$exclude.'"';
	    if(isset($exclude[0])){
		$exclude = 'AND j.userid NOT IN ('.$exclude.') ';
	    } else {
		$exclude = '';
	    }

//		$data = array();
//		$run = true;
//		$page = 0;
//		while($run){
//		    $result = $MC->listMembers( $list_id, '', '', $page, '15000');
//		    $page++;
//		    if($result){
//			$run = true;
//			$data = array_merge($data, $result);
//		    } else {
//			$run = false;
//		    }
//		}

	    $data = $MC->listMembers( $list_id, '', '', $offset, $step);

	    if(count($data) > 0){

		// determine if the interest group Hotness already exists, if not: create it
		if(!isset($_SESSION['HotnessExists'])){
		    $query = "SELECT value FROM #__joomailermailchimpintegration_misc WHERE type = 'hotness' AND listid = '".$list_id."' ";
		    $db->setQuery($query);
		    $hotnessId = $db->loadResult();

		    if($hotnessId == NULL){
			$result = $MC->listInterestGroupingAdd($list_id, JText::_('JM_HOTNESS_RATING'), 'hidden', array(1,2,3,4,5));
			if(is_int($result)){
			    $query = "INSERT INTO #__joomailermailchimpintegration_misc (type, listid, value) VALUES ('hotness', '".$list_id."', '".$result."') ";
			    $db->setQuery($query);
			    $db->query();
			    $_SESSION['HotnessExists'] = $result;
			}
		    } else {
			 $_SESSION['HotnessExists'] = $hotnessId;
		    }
		}
/*
		    $userIds = array();
		    foreach($data as $dat){
			$userIds[$dat->email] = $dat->id;
		    }
*/
		$addedUsers = $elements->addedUsers;

		$m=0;
		$successCount = 0;
		$errorcount = $msgErrorsCount = 0;
		$msg = $msgErrors = false;

		$counter=0;
		$ids = '';
		$errorMsg = $elements->errorMsg;

		for ($x=0;$x<count($data);$x+=$step){
		    if($_SESSION['abortAJAX']==1) { unset($_SESSION['addedUsers']); break; }
		    $k=0;
		    $batch = array();
		    $errorcount = $msgErrorsCount = 0;

		    for ($y=$x;$y<($x+$step);$y++){
			if($_SESSION['abortAJAX']==1) { unset($_SESSION['addedUsers']); break; }
			if(isset($data[$y])){
			    $dat = $data[$y];
			} else {
			    $dat = false;
			}
			if($dat){
			    $addedUsers[] = $dat['email'];
			    $batch[$k]['EMAIL'] = $dat['email'];
			    if(!isset($hotnessRating[$dat['email']])){ $hotnessRating[$dat['email']] = 2; }
			    $batch[$k]['GROUPINGS'][] = array( 'id' => $_SESSION['HotnessExists'], 'groups' => $hotnessRating[$dat['email']]);
			    $k++;
			} else {
			    break;
			}
		    }
		    if($batch){
			$optin = false; //yes, send optin emails
			$up_exist = true; // yes, update currently subscribed users
			$replace_int = true; // false = add interest, don't replace
			$result = $MC->listBatchSubscribe($list_id, $batch, $optin, $up_exist, $replace_int);
			$successCount = $successCount + $result['success_count'];

			if ( $result['error_count'] ) {
			    foreach($result['errors'] as $e){
				$tmp = new stdClass;
				$tmp->errorCode = $e['code'];
				$tmp->errorMessage = $e['message'];
				$errorMsg .= '"'.$MCerrorHandler->getErrorMsg($tmp).' => '.$e['row']['EMAIL'].'", ';

			    //  $addedUsers = array_diff($addedUsers, array($userIds[$e['row']['EMAIL']]));

			    //  $elements->failed[] = $userIds[$e['row']['EMAIL']];
				$errorcount++;
			    }
			    $msgErrorsCount += $result['error_count'];
			}
		    }
		}

		$addedUsers = array_unique($addedUsers);

		if( !count($data)) {
		    $done = $elements->total;
		    unset($_SESSION['addedUsers']);
		    $percent = 100;
		} else {
		    $done = count($addedUsers);
		    $_SESSION['addedUsers'] = $addedUsers;
		    $percent = ( $done / $elements->total ) * 100;
		}

		$response['msg'] =   '<div id="bg"></div>'
				    .'<div style="background:#FFFFFF none repeat scroll 0 0;border:10px solid #000000;height:100px;left:37%;position:relative;text-align:center;top:37%;width:300px; ">'
				    .'<div style="margin: 35px auto 3px; width: 300px; text-align: center;">'.JText::_( 'adding users' ).' ( '.$done.'/'.$elements->total.' '.JText::_( 'done' ).' )</div>'
				    .'<div style="margin: auto; background: transparent url('.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/progress_bar_grey.gif) repeat scroll 0% 0%; width: 190px; height: 14px; display: block;">'
				    .'<div style="width: '.$percent.'%; overflow: hidden;">'
				    .'<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/progress_bar.gif" style="margin: 0 5px 0 0;"/>'

				    .'</div>'
				    .'<div style="width: 190px; text-align: center; position: relative;top:-13px; font-weight:bold;">'.round($percent,0).' %</div>'
				    .'</div>'
				    .'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:abortAJAXnoRefresh();">abort</a>'
				    .'</div>';

		$response['done'] = $done;

		$response['errors']	    = count($elements->failed);
		$response['errorMsg']   = $errorMsg;
		$response['addedUsers'] = array_values(array_unique($addedUsers));
    //	    $response['failed']	    = $elements->failed;

		if( ($done + count($elements->failed) +  $elements->errors) >= $elements->total ){
		    $response['finished'] = 1;

		    if( $errorMsg ) {
			$errorMsg  = substr($errorMsg,0,-2);
			$msgErrors = ' ( '.count($elements->failed).' '.JText::_('Errors').': '.$errorMsg.' )';
		    }
		    if ( !$msg ) { $msg = $done.' '.JText::_( 'JM_USERS_SYNCHRONIZED' ).'.'; }
		    if ( $msgErrors ) { $msg .= $msgErrors; }
		    $response['finalMessage'] = $msg;
		} else {
		    $response['finished'] = 0;
		    $response['finalMessage'] = '';
		}
		$response['abortAJAX'] = $_SESSION['abortAJAX'];
	    } else {
		unset($_SESSION['addedUsers']);
		$response['addedUsers']   = '';
		$response['finalMessage'] = JText::_('JM_NO_USERS_FOUND');
		$response['finished']     = 1;
		$response['abortAJAX']    = $_SESSION['abortAJAX'];
	    }
	    echo json_encode( $response );
	} else {
	    unset($_SESSION['addedUsers']);
	    $response['addedUsers'] = '';
	    $response['finished'] = 1;
	    $response['abortAJAX'] = $_SESSION['abortAJAX'];
	    echo json_encode( $response );
	}
    } // function
	
    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

} // class
