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

$model =& $this->getModel();

$listid = JRequest::getVar('listid',  0, '', 'string');
foreach($this->listDetails as $detail){
    if( $detail['id'] == $listid ){
	$listName = $detail['name'];
	break;
    }
}

$type   = JRequest::getVar('type',  's', '', 'string');
		
switch($type){				
    case 's':
	$state = JText::_('JM_ACTIVE_SUBSCRIBERS');
	break;
    case 'u':
	$state = JText::_('JM_SUBSCRIBERS').' '.JText::_('JM_STATE_UNSUBSCRIBED');
	break;
    case 'c':
	$state = JText::_('JM_SUBSCRIBERS').' '.JText::_('JM_STATE_CLEANED');
	break;
}
?>
<h3><?php echo $listName;?> - <?php echo $this->total.' '.$state;?></h3>

<form action="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo JRequest::getVar('listid');?>&type=<?php echo JRequest::getVar('type');?>" method="post" name="adminForm">
<?php if(is_array($this->active)){ ?>
	<table class="adminlist">
	<thead>
	    <tr>
		<th width="5">#</th>
		<?php if ( $type=='s' ){ ?>
		<th width="5">
		    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->active ); ?>);" />
		</th>
		<?php } ?>
		<th>
		    <?php echo JText::_( 'JM_NAME' ); ?>
		</th>
		<th width="10" nowrap="nowrap">
		    <?php echo JText::_( 'JM_EMAIL_ADDRESS' ); ?>
		</th>
		<th width="110" nowrap="nowrap">
		    <?php echo JText::_( 'JM_MEMBER_RATING' ); ?>
		</th>
		<th width="10" nowrap="nowrap">
		    <?php echo JText::_( 'JM_DATE' ); ?>
		</th>
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
	$k = $this->limitstart;
	for ($i=0; $i < count($this->active); $i++)
	{
	    $row = &$this->active[$i];
	    $user = $model->getJUser($row['email']);
	    if(!$row) break;
	    ?>
	    <tr class="<?php echo "row$k"; ?>">
		<td align="center">
		    <?php echo $i+1+$this->limitstart; ?>
		</td>
		<?php if ( $type=='s' ){ ?>
		<td>
		    <input type="checkbox" name="emails[]" id="cb<?php echo $i;?>" value="<?php echo $row['email'].';'.$user->name; ?>" onclick="isChecked(this.checked);"/>
		</td>
		<?php } ?>
		<td>
		    <?php if($user->id){?>
		    <a href="index.php?option=com_joomailermailchimpintegration&view=subscriber&listid=<?php echo $listid; ?>&uid=<?php echo $user->id; ?>&email=<?php echo $user->email; ?>">
			<?php echo $user->name; ?>
		    </a>
		    <?php } ?>
		</td>
		<td align="center" nowrap="nowrap">
		    <?php echo $row['email']; ?>
		</td>
		<td align="center">
		    <div class="ratingBG">
			<div class="rating<?php echo $details['member_rating'];?>"></div>
		    </div>
		</td>
		<td align="center" nowrap="nowrap">
		    <?php echo $row['timestamp']; ?>
		</td>
	    </tr>
	    <?php
	    $k = 1 - $k;
	}
	?>
</table>
<?php } ?>
<input type="hidden" name="listid" value="<?php echo JRequest::getVar('listid');?>" />

<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="subscribers" />
</form>

