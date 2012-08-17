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
$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
$MCapi  = $params->get( $paramsPrefix.'MCapi' );
$sugar_name = $params->get( $paramsPrefix.'sugar_name', 0 );
$sugar_pwd  = $params->get( $paramsPrefix.'sugar_pwd', 0 );
$sugar_url  = $params->get( $paramsPrefix.'sugar_url', 0 );
$highrise_url = $params->get( $paramsPrefix.'highrise_url', 0 );
$highrise_api_token = $params->get( $paramsPrefix.'highrise_api_token', 0 );

$MCauth = new MCauth();

if ( !$MCapi ) {
    echo $MCauth->apiKeyMissing();
} else {
    if( !$MCauth->MCauth() ) {
	echo $MCauth->apiKeyMissing(1);
    } else {

if( $sugar_name && $sugar_pwd && $sugar_url ){
    $CRMauth = new CRMauth;
    echo $CRMauth->checkSugarLogin();
}
if( $highrise_url && $highrise_api_token ){
    $CRMauth = new CRMauth;
    echo $CRMauth->checkHighriseLogin();
}

$doc  = & JFactory::getDocument();
$doc->addScript( JURI::base()."components/com_joomailermailchimpintegration/assets/js/sync.js");

$page = &$this->get('Pagination');
?>
<script type="text/javascript">
window.addEvent('domready', function() {
    $$('.calendar').set({
	src: '../administrator/components/com_joomailermailchimpintegration/assets/images/calendar.png'
    });
});
<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
    if(pressbutton=='sync_sugar'){
      selectPopup('sugar');
    } else if(pressbutton=='sync_highrise'){
      selectPopup('highrise');

    } else if (pressbutton!='sugar' && pressbutton!='highrise' && document.adminForm.listid.value == ""){
	alert('<?php echo JText::_( 'JM_SELECT_A_LIST_TO_ASSIGN_THE_USERS_TO' );?>');
	document.adminForm.listid.style.border = "1px solid #ff0000";
    } else if( pressbutton == 'mailchimp' ){
	selectPopup('mailchimp');
    } else if(pressbutton=='sync_all'){
	if(confirm('<?php echo JText::_( 'JM_ARE_YOU_SURE_TO_ADD_ALL_USERS' );?>')){
	    if(document.adminForm.total.value == 0){
		alert('<?php echo JText::_( 'JM_ALL_USERS_ALREADY_ADDED' );?>');
	    } else {
		AjaxAddAll(0);
	    }
	}
    } else {
    <?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
    }
}

var baseUrl = '<?php echo JURI::base();?>';

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
			+'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:abortAJAX();">abort</a>'
			+'</div>';

	$('ajax_response').style.display = 'block';
	jQuery('#ajax_response').html(progressBar);
}

function AJAXsuccess(message) {
	var messageBlock =   '<dl id="system-message">'
			    +'<dt class="message">Message</dt>'
			    +'	<dd id="system-message-inner" class="message message fade">'
			    +'		<ul>'
			    +'			<li style="text-indent:0; padding-left: 30px;">'+message+'</li>'
			    +'		</ul>'
			    +'	</dd>'
			    +'</dl>';
	$('message').style.display = 'block';
	jQuery('#message').html(messageBlock);
}

function addToMailchimp( range ){
    if( range == 'selection' ){
	if(document.adminForm.boxchecked.value==0){
	    alert('<?php echo JText::_( 'JM_NO_USERS_SELECTED' );?>');
	} else {
	    submitbutton('sync')
	}
    } else {
	if(confirm('<?php echo JText::_( 'JM_ARE_YOU_SURE_TO_ADD_ALL_USERS' );?>')){
	    if(document.adminForm.total.value == 0){
		alert('<?php echo JText::_( 'JM_ALL_USERS_ALREADY_ADDED' );?>');
	    } else {
		AjaxAddAll(0);
	    }
	}
    }
}

function noListSelected(){
	alert('<?php echo JText::_( 'JM_SELECT_A_LIST_TO_ASSIGN_THE_USERS_TO' );?>');
	document.adminForm.listid.style.border = "1px solid #ff0000";
}
function noUsersSelected(){
	alert('<?php echo JText::_( 'JM_NO_USERS_SELECTED' );?>');
}

function selectPopup( system ){
    if( system == 'sugar' ){
	var addSelectionFunction = "AjaxAddSugar(1,\'selection\',0)";
	var addAllFunction = "AjaxAddSugar(1,\'all\',0)";
    } else if( system == 'highrise' ){
	var addSelectionFunction = "AjaxAddHighrise(1,\'selection\',0)";
	var addAllFunction = "AjaxAddHighrise(1,\'all\',0)";
    } else if( system == 'mailchimp' ){
	var addSelectionFunction = "addToMailchimp('selection')";
	var addAllFunction = "addToMailchimp('all')";
    }
    var progressBar = '<div id="bg"></div>'
    +'<div style="background:#FFFFFF none repeat scroll 0 0;border:10px solid #000000;height:100px;left:37%;position:relative;text-align:center;top:37%;width:300px; ">'

    +'<div style="margin: 30px auto 3px; width: 300px; text-align: center;">'

    +'<a class="button-orange" href="javascript:'+addSelectionFunction+'"><span style="font-size:1.3em;"><?php echo JText::_('JM_ADD_SELECTED_USERS');?></span></a>'
    +'<a class="button-orange" href="javascript:'+addAllFunction+'"><span style="font-size:1.3em;"><?php echo JText::_('JM_ADD_ALL_USERS');?></span></a>'

    +'</div>'
    +'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:closePopup();">abort</a>'
    +'</div>';

    $('ajax_response').style.display = 'block';
    jQuery('#ajax_response').html(progressBar);

}

function closePopup(){
    $('ajax_response').style.display = 'none';
    jQuery('#ajax_response').html('');
}
</script>
<style>
#form_container .calendar {
	top: 7px;
}
</style>
<div id="ajax_response" style="display: none"></div>
<div id="message" style="display: none"></div>
<div id="form_container" style="display:none">
<form action="index.php?option=com_joomailermailchimpintegration&view=sync" method="post" name="adminForm" id="adminForm">

<?php  // no lists created yet?
if ( !$this->subscriberLists ) {
echo JText::_( 'JM_CREATE_A_LIST' );
$i = $n = 1;
} else {
?>
    <div class="note"><?php echo JText::_( 'JM_NOTE' ); ?>: <?php echo JText::_( 'JM_ADDING_USERS_TAKES_SOME_TIME' ); ?></div>
	<table width="100%">
	    <tr>
		<td width="225" style="vertical-align: middle;" nowrap="nowrap">
		    <select name="listid" id="listid" onchange="javascript:this.style.border=''; markAdded2( this.value ); " style="float: left;">
		      <option value=""><?php echo JText::_( 'JM_SELECT_A_LIST_TO_ASSIGN_THE_USERS_TO' ); ?></option>
		      <?php
		       foreach ($this->subscriberLists as $list){
			  ?>
			  <option value="<?php echo $list['id'];?>"><?php echo $list['name'];?></option>
			  <?php
		       }
		      ?>
		    </select>
		    <div id="addUsersLoader" style="visibility:hidden;">
			<img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/loader_16.gif" style="margin: 0 0 0 10px;"/>
		    </div>
		    <div style="clear:both;"></div>
		</td>
		<td align="left" style="padding-left: 20px; vertical-align: middle;">
		    <?php echo JText::_( 'JM_FILTERS' ); ?>:&nbsp;&nbsp;
		    <input type="text" name="search" id="search" size="12" style="height: 14px;" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		    <?php echo $this->lists['type'];?>
		    <?php echo $this->lists['filter_date'];?>
		    <button onclick="this.form.submit();"><?php echo JText::_( 'JM_GO' ); ?></button>
		    <button onclick="document.getElementById('search').value='';document.getElementById('filter_type').selectedIndex = 0;this.form.getElementById('filter_date').value='';this.form.getElementById('filter_logged').value='0';this.form.submit();"><?php echo JText::_( 'JM_RESET' ); ?></button>
		    <br />
		    <br />
		</td>
		<td width="100" nowrap="nowrap" style="padding: 0 10px 0 0;">
		<div class="legendIcon" id="alreadyAssigned"><?php echo JText::_( 'JM_ALREADY_ASSIGNED_TO_LIST' ); ?></div><br />
		<div class="legendIcon" id="infoUpdated"><?php echo JText::_( 'JM_EMAIL_ADDRESS_CHANGED' ); ?></div><br />
		<div class="legendIcon" id="suppressed"><?php echo JText::_( 'JM_SUPPRESSED' ); ?></div>
		<div style="clear:both;"></div>
		</td>
	    </tr>
	</table>

<div id="editcell">
	<table class="adminlist">
	<thead>
	    <tr>
		<th width="5">
		    <?php echo JText::_( 'JM_ID' ); ?>
		</th>
		<th width="20">
		    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
		</th>
		<?php if(    ( $sugar_name && $sugar_pwd )
			  || ( $highrise_url && $highrise_api_token ) ){ ?>
		<th>
		    <?php echo JText::_( 'JM_CRM' ); ?>
		</th>
		<?php } ?>
		<th style="text-align:left;">
		    <?php echo JText::_( 'JM_NAME' ); ?>
		</th>
		<th style="text-align:left;">
		    <?php echo JText::_( 'JM_USERNAME' ); ?>
		</th>
		<th style="text-align:left;">
		    <?php echo JText::_( 'JM_EMAIL_ADDRESS' ); ?>
		</th>
		<th width="6%">
		    <?php echo JText::_( 'JM_ENABLED' ); ?>
		</th>
		<th>
		    <?php echo JText::_( 'JM_GROUP' ); ?>
		</th>
		<th>
		    <?php echo JText::_( 'JM_LAST_VISIT' ); ?>
		</th>
	    </tr>
	</thead>
	<tfoot>
	    <tr>
		<td colspan="15">
		    <?php echo $page->getListFooter(); ?>
		</td>
	    </tr>
	</tfoot>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)

	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_users&view=user&task=edit&cid[]='. $row->id );
        if ( $row->block == 0 ){ $blocked = '<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/tick.png" border="0" alt="Enabled" title="Enabled" />'; }
        else                   { $blocked = '<img src="images/publish_x.png" width="16" height="16" border="0" alt="Blocked" title="Blocked" />'; }

        $user_subscribed = '';
?>

	<tr class="<?php echo "row$k"; ?>" id="row_<?php echo $row->id;?>" <?php echo $user_subscribed; ?>>
	    <td>
		<?php echo $row->id; ?>
	    </td>
	    <td>
		<?php echo $checked;?>
	    </td>
	    <?php if(    ( $sugar_name && $sugar_pwd )
		      || ( $highrise_url && $highrise_api_token ) ){ ?>
	    <td align="center">
		<?php
		if( isset($this->CRMusers['sugar']) && in_array($row->id, $this->CRMusers['sugar']) ){
		    echo '<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/dot_blue.png" border="0" alt="SugarCRM" title="Added to SugarCRM" />&nbsp;';
		}
		if( isset($this->CRMusers['highrise']) && in_array($row->id, $this->CRMusers['highrise']) ){
		    echo '<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/dot_green.png" border="0" alt="Highrise" title="Added to Highrise" />';
		}
		?>
	    </td>
	    <?php } ?>
            <td>
		<a href="<?php echo $link; ?>"  id="link_<?php echo $row->id;?>" <?php echo $user_subscribed; ?>><?php echo $row->name; ?></a>
	    </td>
            <td>
		<?php echo $row->username; ?>
	    </td>
            <td>
		<?php echo $row->email; ?>
	    </td>
            <td align="center">
		<?php echo $blocked; ?>
	    </td>
            <td align="center">
		<?php echo $row->groupname; ?>
	    </td>
            <td align="center">
                <?php echo ($row->lastvisitDate == '0000-00-00 00:00:00') ? JText::_('JNEVER') : $row->lastvisitDate; ?>
            </td>

	</tr>
	<?php
	$k = 1 - $k;


    }
    ?>
    </table>

    <?php if( $sugar_name && $sugar_pwd ){ ?>
	<p>
	<img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/dot_blue.png" border="0" alt="SugarCRM" title="Added to SugarCRM" />
	&nbsp;
	<?php echo JText::_('JM_USER_ADDED_TO_SUGAR');?>
	</p>
    <?php } ?>
    <?php if( $highrise_url && $highrise_api_token ){ ?>
	<p>
	<img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/dot_green.png" border="0" alt="SugarCRM" title="Added to SugarCRM" />
	&nbsp;
	<?php echo JText::_('JM_USER_ADDED_TO_HIGHRISE');?>
	</p>
    <?php } ?>

</div>

<?php } // end - no list created ?>

<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="controller" value="sync" />
<input type="hidden" name="type" value="sync" />
<input type="hidden" name="total" id="total" value="<?php echo $this->total;?>" />
</form>

</div>
<?php
if ( ($i++) == $n ) {
?>
        <script type="text/javascript">document.getElementById("form_container").style.display = "";</script>
<?php
}

}
} ?>
