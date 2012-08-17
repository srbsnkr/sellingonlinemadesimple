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

JHTML::_('behavior.modal');
JHTML::_('behavior.calendar');
jimport( 'joomla.filesystem.file' );

$model =& $this->getModel('send');

$time = JRequest::getVar('campaign', 0, '', 'string');
$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
$MCapi  = $params->get( $paramsPrefix.'MCapi' );
$MCauth = new MCauth();

if ( !$MCapi ) {
    echo $MCauth->apiKeyMissing();
} else {

    if( !$MCauth->MCauth() ) {
	echo $MCauth->apiKeyMissing(1);
    } else if($this->drafts){

    $archiveDir = $params->get( 'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' );
    $doc  = & JFactory::getDocument();
    $doc->addScript( JURI::base()."components/com_joomailermailchimpintegration/assets/js/segments.js");
?>
    <div id="ajax_response" style="display:none;"></div>
    <div id="message" style="display:none;"></div>
    <div id="selectCampaign">
    <b><?php echo JText::_('JM_SELECT_CAMPAIGN_TO_SEND');?>:&nbsp;</b>
    <select name="draft" style="min-width: 200px;" onchange="if(this.value!='') { loadCampaign(this.value); }">
	    <option value=""></option>
	    <?php foreach($this->drafts as $draft){
		    if($time==$draft->creation_date) { $selected = ' selected="selected"'; } else { $selected = ''; }
		    $draftName		= (strlen($draft->name)>30) ? substr($draft->name, 0, 27).'...' : $draft->name;
		    $draftSubject	= (strlen($draft->subject)>30) ? substr($draft->subject, 0, 27).'...' : $draft->subject;
		    echo '<option value="'.$draft->creation_date.'" '.$selected.'>'.$draftName.' ('.$draftSubject.')</option>';
	    }
	    ?>
    </select>
    </div>
    <script type="text/javascript">
    function loadCampaign( cid ) {
	joomailermailchimpintegration_ajax_loader();
	window.location = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='+cid
    }
    var baseUrl = '<?php echo JURI::base();?>';
    var selectListAlert = '<?php echo JText::_('JM_PLEASE_SELECT_A_LIST');?>';
    </script>
<?php

if($time){
    $db = & JFactory::getDBO();
    $query = "SELECT * FROM #__joomailermailchimpintegration_campaigns WHERE creation_date = '".$time."'";
    $db->setQuery($query);
    $cDetails = $db->loadObjectList();
if (!isset($cDetails[0])) {
    echo JText::_('JM_CAMPAIGN_NOT_FOUND');
} else {
	
    $tt_image_abs = JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/info.png';

    $doc  = & JFactory::getDocument();
    $doc->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/jquery.clockpick.1.2.9.min.js');
    $doc->addStyleSheet( JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/css/jquery.clockpick.1.2.9.css' );

    $script = '<script type="text/javascript">
			    $j(document).ready(function() {
				    $j("#pickDeliveryTime").clockpick({
				    starthour : 0,
				    endhour : 23,
				    showminutes : true,
				    minutedivisions: 12,
				    military: true,
//					event: \'mouseover\',
				    layout: \'horizontal\',
				    valuefield: \'deliveryTime\'
				    }
				    );
			    });
			    </script>';
    $doc->addCustomTag( $script );

    $cDetails = $cDetails[0];

    $campaign_name_ent = htmlentities($cDetails->name);
    $campaign_name_ent = str_replace(' ','_',$campaign_name_ent);
    $html = JURI::root().(substr($archiveDir,1)) .'/'.$campaign_name_ent.'.html';
    $text = JURI::root().(substr($archiveDir,1)) .'/'.$campaign_name_ent.'.txt';
?>

<?php if($this->AECambraVM){
$doc->addScript( JURI::base()."components/com_joomailermailchimpintegration/assets/js/sync.js");
} ?>
<script type="text/javascript">
var AJAXloader = '<img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/loader_16.gif" style="margin: 0 0 0 10px;"/>';
<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
	<?php if($this->AECambraVM){ ?>
        if(pressbutton == 'syncHotness'){
            if($('listId').value==''){
		alert('<?php echo JText::_( 'JM_PLEASE_SELECT_A_LIST' ); ?>');
	    } else if(confirm("<?php echo JText::_('JM_SYN_HOTNESS_NOW');?>")){
                AjaxAddHotness(0);
            }

        } else
        <?php } ?>
    if ( pressbutton == 'remove' ){
	if(confirm('<?php echo JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_DRAFT');?>')){
	    if($('listId').value==''){
		alert('<?php echo JText::_( 'JM_PLEASE_SELECT_A_LIST' ); ?>');
	    } else {
		<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
		return true;
	    }
	} else {
	    return false;
	}
    } else {
	var email1 = document.adminForm.email1.value;
	var email2 = document.adminForm.email2.value;
	var email3 = document.adminForm.email3.value;
	var email4 = document.adminForm.email4.value;
	var email5 = document.adminForm.email5.value;

	var regDate = /\d{4}-\d{2}-\d{2}/;
	var regTime = /\d{2}:\d{2}/;

	if( $('schedule').getProperty('checked')==true ){
	    today = new Date();
	    tomorrow = new Date();
	    tomorrow.setDate(today.getDate()+1);
	    var deliveryDate = document.adminForm.deliveryDate.value;
	    deliveryDate=deliveryDate.replace('-','/');
	    deliveryDate=deliveryDate.replace('-','/');
	    selectedDate = new Date(deliveryDate+' '+document.adminForm.deliveryTime.value+':00');
	}
		
	if ($('test').getProperty('checked')==true &&
	( email1=='Email 1' && email2=='Email 2' && email3=='Email 3' && email4=='Email 4' && email5=='Email 5' ) ){
	    alert('<?php echo JText::_( 'JM_PLEASE_ENTER_TEST_RECIPIENTS' ); ?>');
	} else if ( $('test').getProperty('checked')==true && !checkEmail( email1 ) ) {
	    alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
	} else if ( $('test').getProperty('checked')==true && !checkEmail( email2 ) ) {
	    alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
	} else if ( $('test').getProperty('checked')==true && !checkEmail( email3 ) ) {
	    alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
	} else if ( $('test').getProperty('checked')==true && !checkEmail( email4 ) ) {
	    alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
	} else if ( $('test').getProperty('checked')==true && !checkEmail( email5 ) ) {
	    alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
	} else {
	    if ($('test').getProperty('checked')==true){
		joomailermailchimpintegration_ajax_loader();
		<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
	    } else if($('listId').value==''){
		alert('<?php echo JText::_( 'JM_PLEASE_SELECT_A_LIST' ); ?>');
	    } else if( creditCount == 0 && $('campaignType').getProperty('checked')==false ){
		alert('<?php echo JText::_( 'JM_NO_RECIPIENTS' ); ?>');
	    } else if( $('useSegments').getProperty('checked')==true && segmentsTested==0 ){
		alert('<?php echo JText::_( 'JM_PLEASE_TEST_SEGMENTS' ); ?>');
	    } else if(  $('schedule').getProperty('checked')==true &&
				    ( !$('deliveryDate').value.test(regDate) ||
				      !$('deliveryTime').value.test(regTime)  ) ){
		alert('<?php echo JText::_( 'JM_INVALID_DATE' ); ?>');
	    } else if(  $('timewarp').getProperty('checked')==true && '<?php echo $this->clientDetails['plan_type'];?>' == 'free' ){
		alert('<?php echo JText::_( 'JM_TIMEWARP_ONLY_FOR_PAID' ); ?>');
	    } else if(  $('timewarp').getProperty('checked')==true && $('schedule').getProperty('checked')==false ){
		alert('<?php echo JText::_( 'JM_TIMEWARP_MUST_BE_SCHEDULED' ); ?>');
	    } else if(  $('schedule').getProperty('checked')==true && today > selectedDate ){
		alert('<?php echo JText::_( 'JM_DELIVERY_DATE_IN_THE_PAST' ); ?>');
	    } else if(  $('timewarp').getProperty('checked')==true && $('schedule').getProperty('checked')==true && tomorrow > selectedDate ){
		alert('<?php echo JText::_( 'JM_TIMEWARP_MUST_BE_SCHEDULED' ); ?>');
	    } else if( 	$('campaignType').getProperty('checked')==true &&
			    (
				$('useSegments').getProperty('checked')==true ||
				$('schedule').getProperty('checked')==true ||
				$('timewarp').getProperty('checked')==true ||
				$('useTwitter').getProperty('checked')==true
			    )
		    ) {
			    alert('<?php echo JText::_( 'JM_AUTORESPONDER_SETUP_ERROR' ); ?>');
	    } else if( $('campaignType').getProperty('checked')==true &&
		       $('new-auto-offset-time').value <= 0 ) {
		alert('<?php echo JText::_( 'JM_AUTORESPONDER_DAYS_ERROR' ); ?>');
	    } else if( $('campaignType').getProperty('checked')==false &&
			confirm('<?php echo JText::_( 'JM_ARE_YOU_SURE');?> '+creditCount+' <?php echo JText::_( 'JM_CREDITS2' );?>?')){
		joomailermailchimpintegration_ajax_loader();
		<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
		return;
	    } else {
		joomailermailchimpintegration_ajax_loader();
		<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
		return;
	    }
	}
	return;
    }
}

function validateEmail(email){
    if ( !checkEmail( email ) ) {
	alert('<?php echo JText::_( 'JM_INVALID_EMAILS' ); ?>')
    }
}

function checkEmail(email) {
    if(email=='' || email=='Email 1' || email=='Email 2' || email=='Email 3' || email=='Email 4' || email=='Email 5' ){
	return true;
    } else {
	var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return pattern.test(email);
    }
}

window.addEvent('load', function() {

    var testmails = new Fx.Slide('testmails');
	
    if($('test').getProperty('checked')==false){
	testmails.slideOut();
    }
	
    if($('test_label')){
	$('test_label').addEvent('click', function(e){
	    if($('test').getProperty('checked')==true){
		testmails.slideIn();
		credits();
		$('sendTestButton').style.display = 'block';
	    } else {
		testmails.slideOut();
		$('credits').innerHTML = currentCredits;
		creditCount = currentCredits;
		$('sendTestButton').style.display = 'none';
	    }
	});
    } else {
	$('test').addEvent('click', function(e){
	    if($('test').getProperty('checked')==true){
		testmails.slideIn();
		credits();
		$('sendTestButton').style.display = 'block';
	    } else {
		testmails.slideOut();
		$('credits').innerHTML = currentCredits;
		creditCount = currentCredits;
		$('sendTestButton').style.display = 'none';
	    }
	});
    }

    if($('campaignType_label')){
	$('campaignType_label').addEvent('click', function(e){
	    getmerges();
	});
    } else {
	$('campaignType').addEvent('click', function(e){
	    getmerges();
	});
    }

    if($('timewarp_label')){
	$('timewarp_label').addEvent('click', function(e){
	    if($('timewarp').getProperty('checked')==true){
		if( '<?php echo $this->clientDetails['plan_type'];?>' == 'free' ){
		    alert('<?php echo JText::_( 'JM_TIMEWARP_ONLY_FOR_PAID' ); ?>');
		    $('timewarp').checked = false;
		    setTimeout( '$("timewarp_label").style.backgroundPosition= "100% -18px"', 200);
		} else {
		    document.adminForm.deliveryDate.style.color = '#666666';
		    document.adminForm.deliveryTime.style.color = '#666666';
		    $('schedule').checked = true;
		    setTimeout( '$("schedule_label").style.backgroundPosition= "0% -18px"', 200);
		}
	    }
	});
    } else {
	$('timewarp').addEvent('click', function(e){
	    if($('timewarp').getProperty('checked')==true){
		if( '<?php echo $this->clientDetails['plan_type'];?>' == 'free' ){
		    alert('<?php echo JText::_( 'JM_TIMEWARP_ONLY_FOR_PAID' ); ?>');
		    $('timewarp').checked = false;
		} else {
		    document.adminForm.deliveryDate.style.color = '#666666';
		    document.adminForm.deliveryTime.style.color = '#666666';
		    $('schedule').checked = true;
		}
	    }
	});
    }
	
    if($('schedule_label')){
	$('schedule_label').addEvent('click', function(e){
	    if($('schedule').getProperty('checked')==false){
		document.adminForm.deliveryDate.style.color = '#dedede';
		document.adminForm.deliveryTime.style.color = '#dedede';
	    } else {
		document.adminForm.deliveryDate.style.color = '#666666';
		document.adminForm.deliveryTime.style.color = '#666666';
	    }
	});
    } else {
	$('schedule').addEvent('click', function(e){
	    if($('schedule').getProperty('checked')==false){
		document.adminForm.deliveryDate.style.color = '#dedede';
		document.adminForm.deliveryTime.style.color = '#dedede';
	    } else {
		document.adminForm.deliveryDate.style.color = '#666666';
		document.adminForm.deliveryTime.style.color = '#666666';
	    }
	});
    }
	
    $('deliveryDate_img').addEvent('click', function(e){
	document.adminForm.schedule.checked = true;
	if($('schedule_label')){ $('schedule_label').style.backgroundPosition = '0 -18px'; }
	document.adminForm.deliveryDate.style.color = '#666666';
	document.adminForm.deliveryTime.style.color = '#666666';
    });
    $('pickDeliveryTime').addEvent('click', function(e){
	document.adminForm.schedule.checked = true;
	if($('schedule_label')){ $('schedule_label').style.backgroundPosition = '0 -18px'; }
	document.adminForm.deliveryDate.style.color = '#666666';
	document.adminForm.deliveryTime.style.color = '#666666';
    });

    $j('.calendar').attr('src', '<?php echo JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/calendar.png';?>');

});

var creditCount = 0;
var currentCredits = 0;

function setCredits( val ){
    currentCredits = list[val];
    $('total').value = list[val];
    if($('test').getProperty('checked')==false){
	$('credits').innerHTML = list[val];
	creditCount = list[val];
    } else {
	credits();
    }
}

function credits(){
    var counter = 0;
    if($('email1').value != '' && $('email1').value != 'Email 1') { counter++; }
    if($('email2').value != '' && $('email2').value != 'Email 2') { counter++; }
    if($('email3').value != '' && $('email3').value != 'Email 3') { counter++; }
    if($('email4').value != '' && $('email4').value != 'Email 4') { counter++; }
    if($('email5').value != '' && $('email5').value != 'Email 5') { counter++; }
    $('credits').innerHTML = counter;
}

function getmerges(){
    if($('campaignType').checked == true && $('listId').value != "") {
	$('test').checked = false;
	setTimeout( '$("test_label").style.backgroundPosition= "100% -18px"', 200);
	$('timewarp').checked = false;
	setTimeout( '$("timewarp_label").style.backgroundPosition= "100% -18px"', 200);
	$('schedule').checked = false;
	setTimeout( '$("schedule_label").style.backgroundPosition= "100% -18px"', 200);
	$('useSegments').checked = false;
	setTimeout( '$("useSegments_label").style.backgroundPosition= "100% -18px"', 200);
	$('useTwitter').checked = false;
	setTimeout( '$("useTwitter_label").style.backgroundPosition= "100% -18px"', 200);
	var testmails = new Fx.Slide('testmails');
	testmails.slideOut();
	inputState('scheduleContent',true);
	inputState('segmentsContent',true);
	inputState('socialContent',true);

	var listId = $('listId').value;
	var url="index.php?option=com_joomailermailchimpintegration&controller=send&task=merges_ajax&format=raw";
	$('auto-div').setStyle('display', 'block');
	$('merges').innerHTML = '<img src="<?php echo JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/loader_16.gif';?>"/>';

	var data = new Object();
	data['listid'] = listId;
	doAjaxTask(url, data, function(postback){
	    $('merges').innerHTML = postback.html;
	    eventcheck();
	});
    } else {
	$('auto-div').setStyle('display','none');
	$('merges').innerHTML = '';
	inputState('testContent',false);
	inputState('scheduleContent',false);
	inputState('segmentsContent',false);
	inputState('socialContent',false);
    }
}

function eventcheck() {
    if($('new-auto-event')) {
	if($('new-auto-event').value == 'signup') {
	    $('mergefield').setStyle('display','none');
	    if($('new-auto-offset-time').getStyle('display') == 'none') {
		eventType(1);
	    }
	} else {
	    $('mergefield').setStyle('display','');
	}
    }
}

function eventType(type){
    if(type == 1) {
        stylea = 'inline';
        styleb = 'none';
    } else {
        stylea = 'none';
        styleb = 'inline';
        $('new-auto-event').value = 'date';
    }
    $('timelbl1').setStyle('display',stylea);
    $('new-auto-offset-time').setStyle('display',stylea);
    $('new-auto-offset-units').setStyle('display',stylea);
    $('new-auto-offset-dir').setStyle('display',stylea);
    $('new-auto-event-switch-1').setStyle('display',stylea);
    $('new-auto-event').setStyle = ('display',styleb);
    $('timelbl2').setStyle('display',styleb);
    $('new-auto-event-switch-2').setStyle('display',styleb);
    eventcheck();
}

function inputState(parent,state) {
    var list=$(parent);
    list.getElements("select").each(function(el, i)
    {
	el.disabled = state;
    });
    list.getElements("text").each(function(el, i)
    {
	el.disabled = state;
    });
}

<?php if($this->AECambraVM){ ?>
function AJAXinit( total ) {
	var progressBar = '<div id="bg"></div>'
			    +'<div style="background:#FFFFFF none repeat scroll 0 0;border:10px solid #000000;height:100px;left:37%;position:relative;text-align:center;top:37%;width:300px; ">'
			    +'<div style="margin: 35px auto 3px; width: 300px; text-align: center;"><?php echo JText::_( 'JM_ADDING_USERS' );?> ( 0/'+total+' <?php echo JText::_( 'JM_DONE' );?> )</div>'
			    +'<div style="margin: auto; background: transparent url(<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/progress_bar_grey.gif);  width: 190px; height: 14px; display: block;">'
			    +'<div style="width: 0%; overflow: hidden;">'
			    +'<img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/progress_bar.gif" style="margin: 0 5px 0 0;"/>'
			    +'</div>'
			    +'<div style="width: 190px; text-align: center; position: relative;top:-13px; font-weight:bold;">0 %</div>'
			    +'</div>'
			    +'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:abortAJAXnoRefresh();">abort</a>'
			    +'</div>';

	$('ajax_response').style.display = 'block';
	$('ajax_response').setHTML(progressBar);
}

function AJAXsuccess(message) {
	var messageBlock =   '<dl id="system-message">'
			    +'<dt class="message">Message</dt>'
			    +'	<dd class="message message fade">'
			    +'		<ul>'
			    +'			<li style="text-indent:0; padding-left: 30px;">'+message+'</li>'
			    +'		</ul>'
			    +'	</dd>'
			    +'</dl>';
	$('message').style.display = 'block';
	$('message').setHTML(messageBlock);
}
<?php } ?>
</script>
<form action="index.php?option=com_joomailermailchimpintegration&view=send" method="post" name="adminForm">

<table width="100%">
	<tr>
		<td valign="top" id="campaignDetailsCell">
<div id="campaignDetails">
	<div id="campaignDetailsTitle">
	<h3><?php echo JText::_('JM_CAMPAIGN_DETAILS');?></h3>
	</div>
	<div id="campaignDetailsButtons">
	<a id="previewHTML" class="JMbuttonOrange modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $html;?>">
	    <span></span>
	    <?php echo JText::_('JM_HTML');?>
	</a>
	<a id="previewText" class="JMbuttonOrange modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $text;?>">
	    <span></span>
	    <?php echo JText::_('JM_TEXT');?>
	</a>
	</div>
	<div id="campaignDetailsTable">
		<table>
			<tr>
				<td width="120" nowrap="nowrap"><b><?php echo JText::_('JM_CAMPAIGN_NAME');?>:</b></td>
				<td><?php echo $cDetails->name;?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_SUBJECT');?>:</b></td>
				<td><?php echo $cDetails->subject;?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_FROM_NAME');?>:</b></td>
				<td><?php echo $cDetails->from_name;?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_FROM_EMAIL');?>:</b></td>
				<td><?php echo $cDetails->from_email;?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_REPLY_EMAIL');?>:</b></td>
				<td><?php echo $cDetails->reply;?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_CONFIRMATION_EMAIL');?>:</b></td>
				<td><?php echo $cDetails->confirmation;?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_CREATION_DATE');?>:</b></td>
				<td><?php echo strftime('%Y-%h-%d %H:%M:%S', $cDetails->creation_date);?></td>
			</tr>
			<?php /*
			<tr>
				<td valign="top"><?php echo JText::_('Lists');?>:</td>
				<td><?php echo str_replace(';','<br />',$cDetails->list_name);?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('Total Recipients');?>:</td>
				<td><?php echo $cDetails->recipients;?></td>
			</tr>
			*/ ?>
			<tr>
				<td nowrap="nowrap"><b><?php echo JText::_('JM_CREDITS');?>:</b></td>
				<td style="font-size: 2em;" id="credits"><?php echo $cDetails->recipients;?></td>
			</tr>
		</table>
	</div>
</div>
</td><td valign="top">
<div id="sendOptions">
	<div class="sendOptionsTitle" id="optionsTitle">
	<h3><?php echo JText::_('JM_CAMPAIGN_OPTIONS');?></h3>
	</div>
	<div class="sendOptionsContent">
	
	<h4><?php echo JText::_( 'JM_SUBSCRIBER_LIST' ); ?>:</h4>
	<select name="listId" id="listId" style="min-width: 200px; margin: 0 0 10px 0;" onchange="addInterests( this.value );setCredits(this.value);getmerges();"> 
		<option value=""></option>
		<?php
			$js = "var list = new Array();\n";
			foreach ($this->MClists as $list){
				$subscribers = $list['member_count'];
				$js .= 'list["'.$list['id'].'"] = '.$list['member_count'].";\n";
			?>
			<option value="<?php echo $list['id'];?>"><?php echo $list['name'].' ('.$subscribers.' '.JText::_('JM_SUBSCRIBERS').')';?></option>
			<?php
			}
			?>
	</select>
	<script type="text/javascript"><?php echo $js;?></script>
	<div class="checkboxInfo" style="margin-left: 15px;"><?php echo JText::_( 'JM_SUBSCRIBER_LIST_INFO' ); ?></div>

	<h4><?php echo JText::_('JM_TRACKING');?>:</h4>
	<input type="checkbox" class="checkbox" name="trackOpens" id="trackOpens" value="1" checked="checked" /><div class="checkboxInfo"><?php echo JText::_('JM_OPENS');?></div><br /><br />
	<input type="checkbox" class="checkbox" name="trackHTML" id="trackHTML" value="1" checked="checked" /><div class="checkboxInfo"><?php echo JText::_('JM_HTML_CLICKS');?></div><br /><br />
	<input type="checkbox" class="checkbox" name="trackText" id="trackText" value="1" /><div class="checkboxInfo"><?php echo JText::_('JM_TEXT_CLICKS');?></div>
	&nbsp;&nbsp;(<?php echo JText::_('JM_TRACK_TEXT_INFO');?>)<br /><br />
	<input type="checkbox" class="checkbox" name="ecomm360" id="ecomm360" value="1" /><div class="checkboxInfo"><?php echo JText::_('JM_ECOMM360');?>&nbsp;&nbsp;(<?php echo JText::_('JM_ECOMM360_INFO');?>)</div>
	</div>
	
	<div class="sendOptionsTitle" id="testTitle">
	<h3><?php echo JText::_('JM_SEND_CAMPAIGN_TEST');?></h3>
	</div>
	<div class="sendOptionsContent" id="testContent">
		<input type="checkbox" class="checkbox" name="test" id="test" value="1" checked="checked" />
		<div class="checkboxInfo"><?php echo JText::_('JM_CAMPAIGN_TEST');?></div>
		<div class="sendOptionsButton">
		    <a id="sendTestButton" class="JMbuttonOrange" onclick="javascript:<?php if(version_compare(JVERSION,'1.6.0','ge')){ echo 'Joomla.'; } ?>submitbutton('send')" href="javascript:void(0)" title="<?php echo JText::_('JM_SEND_CAMPAIGN_TEST');?>">
			<span></span>
			<?php echo JText::_('JM_SEND_TEST');?>
		    </a>
		</div>
		<br />
		<br />
		<div id="testmails">
		&nbsp;<b><?php echo JText::_('JM_TEST_ADDRESSES');?>:</b>
		<table id="testmailstbl">
			<tr>
				<td><input type="text" name="email[]" id="email1" value="Email 1" size="30" onfocus="if(this.value=='Email 1'){this.value='';}" onchange="validateEmail(this.value)" onblur="credits();if(this.value==''){this.value='Email 1';}" /></td>
			</tr><tr>
				<td><input type="text" name="email[]" id="email2" value="Email 2" size="30" onfocus="if(this.value=='Email 2'){this.value='';}" onchange="validateEmail(this.value)" onblur="credits();if(this.value==''){this.value='Email 2';}" /></td>
			</tr><tr>
				<td><input type="text" name="email[]" id="email3" value="Email 3" size="30" onfocus="if(this.value=='Email 3'){this.value='';}" onchange="validateEmail(this.value)" onblur="credits();if(this.value==''){this.value='Email 3';}" /></td>
			</tr><tr>
				<td><input type="text" name="email[]" id="email4" value="Email 4" size="30" onfocus="if(this.value=='Email 4'){this.value='';}" onchange="validateEmail(this.value)" onblur="credits();if(this.value==''){this.value='Email 4';}" /></td>
			</tr><tr>
				<td><input type="text" name="email[]" id="email5" value="Email 5" size="30" onfocus="if(this.value=='Email 5'){this.value='';}" onchange="validateEmail(this.value)" onblur="credits();if(this.value==''){this.value='Email 5';}" /></td>
			</tr>
		</table>
		</div>
	</div>
	
	<div class="sendOptionsTitle" id="scheduleTitle">
	<h3><?php echo JText::_('JM_SCHEDULE_DELIVERY_OR_SEND_NOW');?></h3>
	</div>
	<div class="sendOptionsContent" id="scheduleContent">
	    <div style="float:left;">
		<input type="checkbox" class="checkbox" name="timewarp" id="timewarp" value="1" />
		<div class="checkboxInfo" style="position:relative;top:-6px;">
		    <?php echo JText::_('JM_USE_TIMEWARP'); ?>
		    <a href="http://www.mailchimp.com/blog/timewarp-schedule-email-campaigns-by-recipient-timezone/" title="<?php echo JText::_( 'JM_WHAT_IS_TIMEWARP' ); ?>" class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" style="margin:5px;position:relative;top:3px;"><img src="<?php echo $tt_image_abs;?>" /></a>
		</div>
		<br />
		<br />
		<input type="checkbox" class="checkbox" name="schedule" id="schedule" value="1" onchange="if(this.checked==true){   document.adminForm.deliveryDate.value='';
																    document.adminForm.deliveryTime.value='';
																    document.adminForm.deliveryDate.style.background = '#BFBFBF';
																    document.adminForm.deliveryTime.style.background = '#BFBFBF';}
																    else {
																    document.adminForm.deliveryDate.style.background = '';
																    document.adminForm.deliveryTime.style.background = '';
																}" />
		<div class="checkboxInfo"><?php echo JText::_('JM_USE_SCHEDULE'); ?></div>
	    </div>
	    <div class="sendOptionsButton">
		<a id="sendNowButton" class="JMbuttonOrange" onclick="javascript:<?php if(version_compare(JVERSION,'1.6.0','ge')){ echo 'Joomla.'; } ?>submitbutton('send')" href="javascript:void(0)" title="<?php echo JText::_('JM_SEND');?>">
		    <span></span>
		    <?php echo JText::_('JM_SEND');?>
		</a>
	    </div>
	    <div style="clear:both;"></div>
	    <br />
		<table>
			<tr valign="middle">
				<td colspan="2"><b><?php echo JText::_('JM_SCHEDULE_DELIVERY'); ?>:</b></td>
			</tr>
			<tr valign="middle">
				<td>
				<?php echo JHTML::calendar(JText::_('JM_DATE'), 'deliveryDate', 'deliveryDate', '%Y-%m-%d',
								array('size'=>'12',
								'maxlength'=>'10',
								'style'    => 'color: #dedede',
								'onchange' => 'document.adminForm.deliveryDate.style.color = \'#666666\';
									       document.adminForm.deliveryTime.style.color = \'#666666\';
									       document.adminForm.schedule.checked = true;
									       if($(\'schedule_label\')){$(\'schedule_label\').style.backgroundPosition = \'0 -18px\';}' ));?>
				</td>
			</tr>
			<tr valign="middle">
				<td>
				<input id="deliveryTime" type="text" name="deliveryTime" value="<?php echo JText::_('JM_TIME');?>" size="12" style="color: #dedede"/>
				<img id="pickDeliveryTime" src="<?php echo JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/clock.png';?>" alt="<?php echo JText::_('JM_SELECT_DELIVERY_TIME'); ?>" />
				</td>
			</tr>
		</table>
	<br />
	<?php echo JText::_('JM_DELIVERY_INFO'); ?>
	</div>
	
	<div class="sendOptionsTitle" id="segmentsTitle">
	<h3><?php echo JText::_('JM_SEGMENTATION'); ?></h3>
	</div>
	<div class="sendOptionsContent" id="segmentsContent">
				<input type="checkbox" class="checkbox" name="useSegments" id="useSegments" value="1" />
				<div class="checkboxInfo"><?php echo JText::_('JM_USE_SEGMENTS');?> (<?php echo JText::_('JM_10_SEGMENTS_ALLOWED');?>)</div>
				<div class="sendOptionsButton">
				    <div id="ajax-spin"></div>
				    <div style="float:right;">
					<a id="testSegments" class="JMbuttonOrange" href="javascript:void(0);testSegments()" title="<?php echo JText::_('JM_TEST_SEGMENT'); ?>">
					    <span></span>
					    <?php echo JText::_('JM_TEST_SEGMENT'); ?>
					</a>
				    </div>
				</div>

				<div style="clear: both;"></div>
				<div id="testResponse"></div>
				<div style="float:left; width: 620px;">
				<?php echo JText::_('JM_MATCH'); ?> <select name="match" id="match">
						<option value="any"><?php echo JText::_('JM_ANY'); ?></option>
						<option value="all"><?php echo JText::_('JM_ALL'); ?></option>
					  </select> <?php echo JText::_('JM_OF_THE_FOLLOWING'); ?>:
				<br />
					<div id="segment1" class="segmentCondition">
						<select name="segmenttype1" id="segmenttype1" class="segmentType">
							<option value="date"><?php echo JText::_('JM_DATE_ADDED'); ?></option>
							<option value="email"><?php echo JText::_('JM_EMAIL_ADDRESS'); ?></option>
							<option value="fname"><?php echo JText::_('JM_FIRSTNAME'); ?></option>
							<option value="lname"><?php echo JText::_('JM_LASTNAME'); ?></option>
							<option value="rating"><?php echo JText::_('JM_MEMBER_RATING'); ?></option>
							<option value="aim"><?php echo JText::_('JM_AIM'); ?></option>
							<option value="social_network"><?php echo JText::_('JM_SOCIAL_NETWORK'); ?></option>
							<option value="social_influence"><?php echo JText::_('JM_SOCIAL_INFLUENCE'); ?></option>
							<option value="social_gender"><?php echo JText::_('JM_SOCIAL_GENDER'); ?></option>
							<option value="social_age"><?php echo JText::_('JM_SOCIAL_AGE'); ?></option>
						</select>
						<div id="segmentTypeConditionDiv_1" class="segmentConditionDiv">
						<select name="segmentTypeCondition_1" id="segmentTypeCondition_1">
							<option value="gt"><?php echo JText::_('JM_IS_AFTER'); ?></option>
							<option value="lt"><?php echo JText::_('JM_IS_BEFORE'); ?></option>
							<option value="eq"><?php echo JText::_('JM_IS'); ?></option>
						</select>
						<select name="segmentTypeConditionDetail_1" id="segmentTypeConditionDetail_1">
							<?php 
								if( !isset($this->campaigns[0]) ){
									$disabled = 'disabled="disabled"'; 
									$campaignDate = '('.JText::_('JM_NO_CAMPAIGN_SENT').')';
									$noCampain = ' - ('.JText::_('JM_NO_CAMPAIGN_SENT').')';
								} else {
									$disabled = ''; 
									$campaignDate = $this->campaigns[0]['send_time'];
									$noCampain = '';
								} 
							?>
							<option value="last" <?php echo $disabled;?>><?php echo JText::_('JM_THE_LAST_CAMPAIGN_WAS_SENT'); ?> - <?php echo $campaignDate;?></option>
							<option value="campaign" <?php echo $disabled;?>><?php echo JText::_('JM_A_SPECIFIC_CAMPAIGN_WAS_SENT'); ?><?php echo $noCampain;?></option>
							<option value="date"><?php echo JText::_('JM_A_SPECIFIC_DATE'); ?></option>
						</select>
						<div id="segmentTypeConditionDetailDiv_1" class="segmentTypeConditionDetailDiv">
							<?php if( isset($this->campaigns[0]) ){ ?>
							<input type="hidden" value="<?php echo $this->campaigns[0]['send_time'];?>" name="segmentTypeConditionDetailValue_1" id="segmentTypeConditionDetailValue_1" />
							<?php } ?>
						
						<?php 
						if( !isset($this->campaigns[0]) ){
							echo JHTML::calendar( date('Y-m-d'), 'segmentTypeConditionDetailValue_1', 'segmentTypeConditionDetailValue_1', '%Y-%m-%d',
											array('size'=>'12',
											'maxlength'=>'10' ));
						}?>
						</div>
						</div>
					</div>
					<div id="segment2" class="segmentCondition" style="display:none;"></div>
					<div id="segment3" class="segmentCondition" style="display:none;"></div>
					<div id="segment4" class="segmentCondition" style="display:none;"></div>
					<div id="segment5" class="segmentCondition" style="display:none;"></div>
					<div id="segment6" class="segmentCondition" style="display:none;"></div>
					<div id="segment7" class="segmentCondition" style="display:none;"></div>
					<div id="segment8" class="segmentCondition" style="display:none;"></div>
					<div id="segment9" class="segmentCondition" style="display:none;"></div>
					<div id="segment10" class="segmentCondition" style="display:none;"></div>
					<div id="segment11"></div>
				</div>
				<div style="clear:both;"></div>
				
				<div id="addCondition" style="cursor:pointer; width: 120px;"><img src="<?php echo JURI::root(); ?>administrator/components/com_joomailermailchimpintegration/assets/images/add.png" alt="<?php echo JText::_('JM_ADD_CONDITION'); ?>" style="padding:5px 5px 0 0;" /> <span style="position:relative;top:-3px;"><?php echo JText::_('JM_ADD_CONDITION'); ?></span></div>

				<input type="hidden" name="conditionCount" id="conditionCount" value="1" />
				<div class="preload">
				<img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/loader_16.gif"/>
				</div>
	</div>
	<div class="sendOptionsTitle" id="socialTitle">
		<h3><?php echo JText::_('JM_SOCIAL_INTEGRATIONS'); ?></h3>
	</div>			
	<div class="sendOptionsContent" id="socialContent">
		<input type="checkbox" class="checkbox" name="useTwitter" id="useTwitter" value="1" />
		<div class="checkboxInfo"><?php echo JText::_('JM_SHARE_CAMPAIGN_ON_TWITTER');?> (<?php echo JText::_('JM_TWITTERTIP');?>)</div>
	</div>
	
	<div class="sendOptionsTitle" id="autoTitle">
		<h3><?php echo JText::_('JM_AUTORESPONDER'); ?></h3>
	</div>
	<div class="sendOptionsContent">

	<input type="checkbox" class="checkbox" name="campaignType" id="campaignType" value="1" /><div class="checkboxInfo"><?php echo JText::_('JM_AUTORESPONDER_TIP');?></div>
	
	<div class="sendOptionsButton">
	    <a id="saveAuto" class="JMbuttonOrange" href="javascript:<?php if(version_compare(JVERSION,'1.6.0','ge')){ echo 'Joomla.'; } ?>submitbutton('send')" title="<?php echo JText::_('JM_SAVE_DRAFT'); ?>">
		<span></span>
		<?php echo JText::_('JM_SAVE_DRAFT'); ?>
	    </a>
	</div>
	
	<br />
    <br />

    <div id="auto-div" style="display:none">
      <label id="timelbl1" for="new-auto-offset-time" style="width: 1px; height: auto; overflow: hidden;"><?php echo JText::_('JM_SENDS');?></label>
      <label id="timelbl2" style="overflow: hidden; width: 60px; height: auto; display: none;" for="new-auto-offset-time"><?php echo JText::_('JM_SENDS_ON');?></label>
      <input type="text" value="1" id="new-auto-offset-time" name="offset-time" style="width: 20px; height: auto; overflow: hidden;"/>
      <select id="new-auto-offset-units" name="offset-units" style="width: auto; height: auto; overflow: hidden;">
        <option value="day"><?php echo JText::_('JM_DAYS');?></option>
        <option value="week"><?php echo JText::_('JM_WEEKS');?></option>
        <option value="month"><?php echo JText::_('JM_MONTHS');?></option>
        <option value="year"><?php echo JText::_('JM_YEARS');?></option>
      </select>
      <select id="new-auto-offset-dir" name="offset-dir" style="width: auto; height: auto; overflow: hidden;">
        <option value="after"><?php echo JText::_('JM_AFTER');?></option>
        <option value="before"><?php echo JText::_('JM_BEFORE');?></option>
      </select>
      <select id="new-auto-event" name="event" onchange="eventcheck();">
        <option value="signup"><?php echo JText::_('JM_SIGNUP');?></option>
        <option value="date"><?php echo JText::_('JM_DATE');?></option>
        <option value="annual"><?php echo JText::_('JM_ANNUAL');?></option>
      </select>
      <span id="merges"></span>
      <span style="width: auto; height: auto; overflow: hidden;" id="new-auto-event-switch-1">
          <strong><?php echo JText::_('JM_OR');?></strong><a class="event-switch-trigger" href="javascript:void(0)" onclick="eventType(2);"> <?php echo JText::_('JM_SEND_ON_SPECIFIC_DAY'); ?></a>
      </span>
      <span style="overflow: hidden; width: auto; height: auto; display: none;" id="new-auto-event-switch-2" onclick="eventType(1);">
          <strong><?php echo JText::_('JM_OR');?></strong> <a class="event-switch-trigger" href="javascript:void(0)"> <?php echo JText::_('JM_SEND_BEFORE_AFTER_DATE_EVENT'); ?></a>
      </span>
	</div>
	</div>
<div style="clear: both;"></div>
</td>
	</tr>
</table>
<input type="hidden" name="time" id="time" value="<?php echo $time;?>" />
<?php if(isset($cDetails)  && 0==1 ){ ?>
<input type="hidden" name="listId" id="listId" value="<?php echo $cDetails->list_id;?>" />
<?php } ?>

<input type="hidden" name="total" id="total" value="" />
<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="send" />
</form>	   

<?php } ?>
<?php } ?>
<?php }  else {
	    echo JText::_('JM_NO_DRAFTS');?>&nbsp;<?php echo JText::_('JM_PLEASE_CREATE_A_DRAFT');
	  }?>
<?php } ?>

	
