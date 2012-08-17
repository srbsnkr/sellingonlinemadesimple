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
	
if($this->params->get( 'display_type', 0)==0) {
    JHTML::_('behavior.modal');
}
if ( !$this->campaigns ) {
    echo '<form action="index.php?option=com_joomailermailchimpintegration&view=archive" method="post" name="adminForm">';
    echo JText::_( 'JM_NO_CAMPAIGNS' );
} else {
     echo '<h2 class="componentheading">';
    if ( $this->page_title ) {
        echo $this->page_title;
    } else {
        echo JText::_( 'JM_CAMPAIGN_ARCHIVE' );
    }
    echo '</h2>'; 
?>
<div class="col100">
    <table class="adminlist" width="95%">

	<thead>
	    <tr>
		<th width="20" style="text-align:center">#</th>
		<th><?php echo JText::_( 'JM_SUBJECT' ); ?></th>
		<th width="120"><?php echo JText::_( 'JM_SENT_DATE' ); ?></th>
	    </tr>
	</thead>
        <?php
        $i = 1;
        foreach ( $this->campaigns as $email ) {
            if($this->params->get( 'display_type', 0)==0) { 
		$link = '<a class="modal" rel="{handler: \'iframe\', size: {x: 980, y: 550} }" href="'.$email['archive_url'].'">';
	    } else {
		$link = '<a target="_blank" href="'.$email['archive_url'].'">';
	    }
            echo '<tr>';
            echo '<td align="center">'.$i.'</td>';
            echo '<td align="left" nowrap="nowrap">';
            echo $link;
            echo $email['subject'];
            echo '</a>';
            echo '</td>';
            echo '<td align="center" nowrap="nowrap">'.substr($email['send_time'],0,-9).'</td>';
            echo '</tr>';
	    $i++;
        }
        ?>
    </table>
</div>
<div class="clr"></div>
<?php
}
?>
