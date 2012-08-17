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
JHTML::_('behavior.tooltip');
$tt_image = '../../../administrator/components/com_joomailermailchimpintegration/assets/images/info.png';

$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
$MCapi  = $params->get( $paramsPrefix.'MCapi' );
$MCauth = new MCauth();

if ( !$MCapi ) {
    echo $MCauth->apiKeyMissing();
} else {
    if( !$MCauth->MCauth() ) {
	echo $MCauth->apiKeyMissing(1);
    } else {

	$model =& $this->getModel();
    //	var_dump($this->pagination);die;
	$mainframe =& JFactory::getApplication();
	$limit		= $mainframe->getUserStateFromRequest( 'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart	= $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

	$filter = JRequest::getVar('filter_status', 'sent', '', 'string');
?>
<script language="javascript" type="text/javascript">
<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
    if(pressbutton == 'unschedule') {
	if(confirm('<?php echo JText::_( 'JM_ARE_YOU_SURE_TO_UNSCHEDULE_THE_SELECTED_CAMPAIGNS' );?>?')){
	    joomailermailchimpintegration_ajax_loader();
	    <?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
	}
    } else {
	joomailermailchimpintegration_ajax_loader();
	<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
    }
}
</script>
<form action="index.php?option=com_joomailermailchimpintegration&view=campaignlist" method="post" name="adminForm">

<div id="filter">
<?php if( JOOMLAMAILER_MANAGE_CAMPAIGNS ){ ?>
    <?php echo JText::_( 'JM_SELECT_FOLDER' ); ?>:
    <?php echo $this->foldersDropDown; ?>
    &nbsp;&nbsp;
<?php } ?>
<?php echo JText::_( 'JM_SELECT_STATUS' ); ?>:
<select onchange="document.adminForm.submit();" size="1" class="inputbox" id="filter_status" name="filter_status">
    <?php if( JOOMLAMAILER_CREATE_DRAFTS ){ ?>
    <option value="save" <?php if($filter=='save') echo 'selected="selected"';?>><?php echo JText::_( 'JM_SAVED' ); ?></option>
    <?php } ?>
    <?php if( JOOMLAMAILER_MANAGE_CAMPAIGNS ){ ?>
    <option value="sent" <?php if($filter=='sent') echo 'selected="selected"';?>><?php echo JText::_( 'JM_SENT' ); ?></option>
    <option value="paused" <?php if($filter=='paused') echo 'selected="selected"';?>><?php echo JText::_( 'JM_PAUSED' ); ?></option>
    <option value="schedule" <?php if($filter=='schedule') echo 'selected="selected"';?>><?php echo JText::_( 'JM_SCHEDULE' ); ?></option>
    <option value="sending" <?php if($filter=='sending') echo 'selected="selected"';?>><?php echo JText::_( 'JM_SENDING' ); ?> & <?php echo JText::_( 'JM_AUTORESPONDERS' ); ?></option>
    <?php } ?>
</select>
</div>
<div style="clear: both;"></div>
<?php
if ( !isset($this->items[0]) ) {
echo '<div>'.JText::_( 'JM_NO_CAMPAIGNS_WITH_THE_SELECTED_STATUS' ).'</div>';
} else {
?>

<div id="editcell">
    <table class="adminlist">
	<thead>
	    <tr>
		<th width="10">#</th>
		<th width="20">
		    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
		</th>
		<th><?php echo JText::_( 'JM_NAME' ); ?></th>
		<th><?php echo JText::_( 'JM_SUBJECT' ); ?></th>
		<th width="75"><?php echo JText::_( 'JM_STATUS' ); ?></th>
		<th width="150"><?php echo ($filter=='save')?JText::_( 'JM_CREATION_DATE' ):JText::_( 'JM_DELIVERY_DATE' ); ?></th>
		<?php if($filter!='save') { ?>
		<th width="110"><?php echo JText::_( 'JM_TOTAL_RECIPIENTS' ); ?></th>
		<th width="100"><?php echo JText::_( 'JM_UNIQUE_OPENS' ); ?></th>
		<th width="100"><?php echo JText::_( 'JM_CLICKS' ); ?></th>
		<?php } ?>
		<th width="75"><?php echo JText::_( 'JM_ARCHIVE' ); ?></th>
	    </tr>
	</thead>
	<tfoot>
	    <tr>
		<td colspan="15">
		    <?php echo $this->pagination->getListFooter(); ?>
		</td>
	    </tr>
	</tfoot>
	
	<?php
	if($limit ==0) { $limit = count($this->items); }
	$k = 0;
//	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	for ($i=0, $n=$limit; $i < $n; $i++)
	{
	    $campaign = &$this->items[$i+$limitstart];
	    if(!$campaign) break;

	    $checked = JHTML::_('grid.id',   $i, $campaign['id'] );
	    $link    = JRoute::_( 'index.php?option=com_joomailermailchimpintegration&controller=joomailermailchimpintegration&task=edit&listid='. $campaign['id'] );
	    if( isset($campaign['status']) && $campaign['status']=='schedule') {
		$campaign['emails_sent'] = '-';
		$summary['unique_opens'] = '-';
		$summary['clicks']	 = '-';
	    } else if( isset($campaign['type']) && $campaign['type'] == 'auto' ){
		if( $campaign['status'] == 'paused' ) {
		    $campaign['status'] = 'JM_AUTORESPONDER_PAUSED';
		} else {
		    $campaign['status'] = 'JM_AUTORESPONDER';
		}
		$campaign['send_time'] = JText::_('JM_VARIABLE');
		$summary =& $model->getCampaignStats( $campaign['id'] );
	    } else {
		$summary =& $model->getCampaignStats( $campaign['id'] );
	    }

	    ?>
	    <tr class="<?php echo "row$k"; ?>">
		<td align="center">
		    <?php echo $i+1+$limitstart; ?>
		</td>
		<td>
		    <?php echo $checked;?>
		</td>
		<td align="center" nowrap="nowrap">
		    <?php echo (strlen($campaign['title'])>50) ? substr($campaign['title'], 0, 47).'...' : $campaign['title']; ?>
		</td>
		<td align="center">
		    <?php echo (strlen($campaign['subject'])>50) ? substr($campaign['subject'], 0, 47).'...' : $campaign['subject']; ?>
		</td>
		<td align="center">
		    <?php echo ($filter!='save') ? JText::_($campaign['status']) : JText::_('JM_SAVED'); ?>
		</td>
		<td align="center">
		    <?php echo ($filter=='save')? strftime('%Y-%m-%d %H:%M:%S',$campaign['creation_date']) : $campaign['send_time'] ; ?>
		</td>
	    <?php if($filter!='save') { ?>
		<td align="center">
		    <?php echo $campaign['emails_sent']; ?>
		</td>
		<td align="center">
		    <?php echo $summary['unique_opens']; ?>
		</td>
		<td align="center">
		    <?php echo $summary['clicks']; ?>
		</td>
	    <?php } ?>
		<td align="center"><a class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $campaign['archive_url'];?>">
		    <img src="../administrator/components/com_joomailermailchimpintegration/assets/images/preview_32.png" alt="Preview" title="Preview" height="17"/></a>
		</td>
	    </tr>
<?php
	$k = 1 - $k;
    }
?>
    </table>
</div>

<?php } // end if no lists created ?>

<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="campaignlist" />
<input type="hidden" name="type" value="<?php echo $filter;?>" />
</form>

<?php } ?>
<?php } ?>
