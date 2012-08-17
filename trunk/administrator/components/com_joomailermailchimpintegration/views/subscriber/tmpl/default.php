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

$path = JURI::root() . 'media/com_joomailermailchimpintegration/backend/views/subscriber/';
JHTML::_('stylesheet', 'subscriber.css', $path);
$model =& $this->getModel();

$AIM = false;
$clientDetails = $model->getClientDetails();
foreach($clientDetails['modules'] as $mod){
	if($mod['name'] == 'AIM Reports'){
		$AIM = true;
		break;
	}
}

?>


<div id="userdiv">
	<div id="avatar">
		<img id="avatarimg" src="<?php echo $this->avatar; ?>" alt="avatar"/>
		<?php if($this->kloutScore !== false) : ?>
		<div id="kloutScore">
			<a href="http://klout.com/<?php echo $this->twitterName; ?>" target="_blank">
					<span><?php echo $this->kloutScore; ?></span>
			</a>
		</div>
		<?php endif; ?>
	</div>
	<div id="userInfo">
		<div id="username"><?php echo $this->user->username; ?></div>
		<table class="usertable">
			<tbody>
				<tr>
					<td class="lbl">
						<?php echo JText::_('Email');?>:
					</td>
					<td >
						<a href="mailto:<?php echo $this->user->email;?>"><?php echo $this->user->email; ?></a>
					</td>
				</tr>
				<tr>
					<td class="lbl">
						<?php echo JText::_('Joined'); ?>:
					</td>
					<td>
						<?php echo $this->user->registerDate; ?>
					</td>
				</tr>
				<tr>
					<td class="lbl">
						Last login:
					</td>
				<td>
					<?php echo $this->user->lastvisitDate?>
				</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div id="socialStuff">
		<div id="hotness">
			<span id="hotnessRatingStars" style="width:<?php echo 12*$this->hotnessRating; ?>px"></span>
				<?php echo JText::_('JM_HOTNESS_RATING'); ?>
		</div>
		<div class="ratings">
			<div class="ratingBG">
				<?php $ratingWidth = $this->memberRating * 2 * 10;?>
				<div class="rating5" style="width:<?php echo $ratingWidth;?>%"></div>
			</div>
			Member Rating
		</div>
		<div class="clear-both"></div>
		<?php
			jimport('joomla.html.pane');
			$pane =& JPane::getInstance( 'tabs',  array('startOffset'=>0));
			echo $pane->startPane( 'pane' );
			echo $pane->startPanel( JText::_( 'Social' ), 'social' );
				require_once('social.php');
			echo $pane->endPanel();
			echo $pane->startPanel( JText::_( 'JomSocial' ), 'jomsocial' );
				require_once('jomsocial.php');
			echo $pane->endPanel();
			echo $pane->endPane();
		?>
	</div>
	<div class="clear-both"></div>

</div>

<div>
	<table class="adminlist">
		<thead>
			<th width="10"></th>
			<th>Item</th>
			<th>Date</th>
			<th>Cost</th>
			<th>Product Category</th>
		</thead>
		<tbody>
			<?php if(count($this->hotActivity)) : ?>
				<?php foreach($this->hotActivity as $key => $hotActivity) : ?>
					<tr>
						<td></td>
						<td align="center">
							<?php echo $hotActivity->title; ?>
						</td>
						<td  align="center">
							<?php echo $hotActivity->crdate; ?>
						</td>
						<td  align="center">
							<?php echo $hotActivity->price; ?>
						</td>
						<td  align="center"><?php echo $hotActivity->joomailerProductCategory; ?></td>
					</tr>
				<?php endforeach;	?>
			<?php else : ?>
					<tr>
						<td colspan="7">This user was lazy. He didn't do anything...</td>
					</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
	<table class="adminlist">
	<thead>
		<tr>
			<th width="10">#</th>
			<th nowrap="nowrap">
				<?php echo JText::_( 'Newsletters Sent' ); ?>
			</th>


			<th width="70" nowrap="nowrap">
				<?php echo JText::_( 'Opened' ); ?>
			</th>
			<?php if($AIM){ ?>
			<th width="70" nowrap="nowrap">
				<?php echo JText::_( 'Clicks' ); ?>
			</th>
			<?php } ?>
			<th width="20" nowrap="nowrap">
				<?php echo JText::_( 'Segments' ); ?>
			</th>
            <th width="20" nowrap="nowrap">
				<?php echo JText::_( 'Campaign Sent Date' ); ?>
			</th>
			<th width="20" nowrap="nowrap">
				<?php echo JText::_( 'List Subscription Date')?>
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
    $limit = count($this->stats);
	$k = $this->limitstart;
    $i=0;
	foreach ($this->stats as $row)
	{

	if( isset($row['received']) && $row['received']) {
		$img = '<img src="'.JURI::base().'components/com_joomailermailchimpintegration/assets/images/tick.png"/>';
    } else {
        $img = '<img src="'.JURI::base().'components/com_joomailermailchimpintegration/assets/images/cross.png"/>';
    }

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td align="center">
                <?php echo $i+1+$this->limitstart; ?>
			</td>
			<td align="center" nowrap="nowrap">
				<?php echo $row['title']; ?>
			</td>


			<td align="center" nowrap="nowrap">
				<?php if(isset($row['opens'])){ echo $row['opens']; } ?>
			</td>
			<?php if($AIM){ ?>
			<td align="center" nowrap="nowrap">
				<?php echo $row['clicks']; ?>
			</td>
			<?php } ?>
			<td align="center" nowrap="nowrap">
				<?php echo $row['segment_text']; ?>
			</td>
			<td align="center" nowrap="nowrap">
				<?php echo $row['date']; ?>
			</td>
			<td align="center" nowrap="nowrap">
				<?php echo $row['list_sub'];?>
			</td>
		</tr>
		<?php
        $i++;
		$k = 1 - $k;
	}
	?>

</table>
<?php

?>
