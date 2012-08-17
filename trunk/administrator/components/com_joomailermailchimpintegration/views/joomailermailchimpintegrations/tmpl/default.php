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
//var_dump($this->items[0]);

	JHTML::_('behavior.modal');
	JHTML::_('behavior.tooltip');
	if( version_compare(JVERSION,'1.6.0','ge') ){
	    $tt_image = JURI::root() .'administrator/components/com_joomailermailchimpintegration/assets/images/info.png';
	} else {
	    $tt_image = '../../../administrator/components/com_joomailermailchimpintegration/assets/images/info.png';
	}

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

?>
<form action="index.php" method="post" name="adminForm">

<?php
if ( !isset($this->items[0]) ) {
echo JText::_( 'JM_CREATE_A_LIST' );
} else {
?>

<div id="editcell">
    <table class="adminlist">
	<thead>
	    <tr>
		<th width="10">#</th>

		<th nowrap="nowrap">
		    <?php echo JText::_( 'JM_NAME' ); ?>
		</th>
		<th width="100" nowrap="nowrap">
		    <?php echo JText::_( 'JM_MERGE_FIELDS' ); ?>
		</th>
		<th width="100" nowrap="nowrap">
		    <?php echo JText::_( 'JM_CUSTOM_FIELDS' ); ?>
		</th>
		<th width="10%">
		    <?php echo JText::_( 'JM_LIST_RATING' );
		      echo '<a href="http://www.mailchimp.com/kb/article/how-do-you-determine-my-list-rating" target="_blank">';
		      echo '&nbsp;'.JHTML::tooltip( JText::_( 'JM_TOOLTIP_LIST_RATING' ), JText::_( 'JM_LIST_RATING' ), $tt_image, '' );
		      echo '</a>';
                ?>
		</th>
		<th width="8%">
		    <?php echo JText::_( 'JM_SUBSCRIBERS' ); ?>
		</th>
		<th width="8%">
		    <?php echo JText::_( 'JM_UNSUBSCRIBED' ); ?>
		</th>
		<th width="8%">
		    <?php echo JText::_( 'JM_CLEANED' ); ?>
		</th>
	    </tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
	    $row = &$this->items[$i];
	    $checked 	= JHTML::_('grid.id',   $i, $row['id'] );
	    ?>
	    <tr class="<?php echo "row$k"; ?>">
		<td align="center">
		    <?php echo $i+1; ?>
		</td>
		<td nowrap="nowrap">
		    <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $row['id'];?>&type=s" >
			<?php echo $row['name']; ?>
		    </a>
		</td>
		<td align="center">
		    <a href="index.php?option=com_joomailermailchimpintegration&view=fields&listid=<?php echo $row['id'];?>&name=<?php echo urlencode($row['name']);?>" ><?php echo JText::_( 'JM_MANAGE' ); ?></a>
		</td>
		<td align="center">
		    <a href="index.php?option=com_joomailermailchimpintegration&view=groups&listid=<?php echo $row['id'];?>&name=<?php echo urlencode($row['name']);?>" ><?php echo JText::_( 'JM_MANAGE' ); ?></a>
		</td>
		<td align="center">
		    <a href="http://www.mailchimp.com/kb/article/how-do-you-determine-my-list-rating" target="_blank" title="<?php
                    echo JText::_('JM_WHAT_IS_LIST_RATING');
		    ?>">
			<div class="ratingBG">
			    <?php $ratingWidth = $row['list_rating'] * 2 * 10;?>
			    <div class="rating5" style="width:<?php echo $ratingWidth;?>%"></div>
			</div>
		    </a>
		</td>
		<td align="center">
		    <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $row['id'];?>&type=s" >
			<?php echo $row['member_count']; ?>
		    </a>
		</td>
		<td align="center">
		    <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $row['id'];?>&type=u" >
			<?php echo $row['unsubscribe_count']; ?>
		    </a>
		</td>
		<td align="center">
		    <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $row['id'];?>&type=c" >
			<?php echo $row['cleaned_count']; ?>
		    </a>
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
<input type="hidden" name="controller" value="joomailermailchimpintegration" />
<input type="hidden" name="type" value="dashboard" />
</form>

<?php } ?>
<?php } ?>
