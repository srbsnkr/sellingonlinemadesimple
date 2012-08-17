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

$user = & JFactory::getUser();
$model	=& $this->getModel();

//--include joomla.javascript.js
$document	=& JFactory::getDocument();
$document->addScript( JURI::root(true).'/includes/js/joomla.javascript.js');

?>
<script language="javascript" type="text/javascript">
<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
	<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="col100">
	<?php
	 echo '<h2 class="componentheading">';
	if ( $this->page_title ) {
	    echo $this->page_title;
	} else {
	    echo JText::_( 'JM_MY_NEWSLETTER_SUBSCRIPTIONS' );
	}
	echo '</h2>';
	?>
	<table class="admintable">
<?php
foreach ($this->lists as $list){

echo '<tr><td align="left" class="key" style="padding: 0 15px 0 0;"><label for="listid">'.$list['name'].'</label></td>';

$is_sub =& $model->getIsSubscribed($list['id'], $user->email);
if ($is_sub) {
    $checked_yes = 'checked="checked"'; $checked_no = '';
    $is_subscribed = '<input type="hidden" name="is_sub_'.$list['id'].'" value="1" />';
} else {
    $checked_yes = ''; $checked_no = 'checked="checked"';
    $is_subscribed = '<input type="hidden" name="is_sub_'.$list['id'].'" value="0" />';
}

echo '<td style="padding: 0 15px 0 0;"><input type="hidden" name="listid[]" id="listid" value="'.$list['id'].'" />'.$is_subscribed.'
          <label for="subscribe_'.$list['id'].'_yes">'.JText::_( 'JM_SUBSCRIBE' ).':</label>
          <input type="radio" name="subscribe_'.$list['id'].'" '.$checked_yes.' value="1" id="subscribe_'.$list['id'].'_yes"/>
      </td>
      <td>
          <label for="subscribe_'.$list['id'].'_no">'.JText::_( 'JM_UNSUBSCRIBE' ).':</label>
          <input type="radio" name="subscribe_'.$list['id'].'" '.$checked_no.'  value="0" id="subscribe_'.$list['id'].'_no"/>
      </td></tr>';

}
?>
	</table>
	<br />
    </div>
    <div class="clr"></div>
    <input type="hidden" name="itemid" value="<?php echo $_GET['Itemid'];?>" />
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="save" />
    <input type="hidden" name="controller" value="" />
    <?php echo JHTML::_( 'form.token' ); ?>
    <button type="button" onclick="submitbutton('save')"><?php echo JText::_('JM_SAVE') ?></button>
    <button type="button" onclick="submitbutton('cancel')"><?php echo JText::_('JM_CANCEL') ?></button>
</form>
