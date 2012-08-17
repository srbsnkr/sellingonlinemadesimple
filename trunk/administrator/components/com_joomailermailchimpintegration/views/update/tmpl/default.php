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
 *
 * This file is based on AdminTools' default.php from Nicholas K. Dionysopoulos
 * @copyright Copyright (c)2010 Nicholas K. Dionysopoulos
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

JHTML::_('behavior.modal');

if(!$this->updates->supported)
{
    $overview_class = 'notok';
    $mode = 'unsupported';
}
elseif( $this->updates->update_available )
{
    $overview_class = 'update';
    $mode = 'update';
}
else
{
    $overview_class = 'ok';
    $mode = 'ok';
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var button1 = jQuery("#updateform .submit");
    var button2 = jQuery("#requeryform .submit");
    button1Width = button1.width() + parseInt(button1.css("padding-left"), 10) + parseInt(button1.css("padding-right"), 10);
    button2Width = button2.width() + parseInt(button2.css("padding-left"), 10) + parseInt(button2.css("padding-right"), 10);
    if(button1Width > button2Width){
	button2.width( button1Width + 2 );
    } else {
	button1.width( button2Width + 2 );
    }
});
</script>
<style>
div.note h3 { color: #555555; margin-top: 5px; margin-bottom: 2px; }
div.ok { background-color: #53DF59; color: #009900 !important; }
div.unsupported { background-color: #ff9999; }
div.update { background-color: #FBE3E4; }

#version_info_table {
    border: thin solid gray;
    background: #efefef;
    padding: 1em;
    margin: 1em auto;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
}
#version_info_table td { padding: 3px 10px 3px 0; border-bottom: thin solid #d0d0d0; }
#version_info_table .label { font-weight: bold; }
.version { font-weight: bold; color: #333300; }
.version-status { color: #666666; font-style: italic; }

form {
    text-align: center;
}
.submit {
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-color: #D16700;
    display:block;
    height:45px;
    margin:auto;
    padding:0 5px 0 40px;
    cursor: pointer;
    font-size: 12px;
    text-align: left;
}
#updateform .submit {
    background: url(<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/paramsIcon.png) no-repeat 5px center #FF7E00;
}
#requeryform .submit {
    background: url(<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/sync.png) no-repeat 5px center #FF7E00;
}
.submit:hover {
    background-color: #EF7600 !important;
    border-color: #BF5E00 !important;
}
</style>
<div class="note <?php echo $overview_class; ?>">
    <h3>
    <?php switch($mode):
	    case 'ok': ?>
		<?php echo JText::_('JM_LBL_UPDATE_NOUPGRADESFOUND') ?>
    <?php	break;
	    case 'update': ?>
		<?php echo JText::_('JM_LBL_UPDATE_UPGRADEFOUND') ?>
    <?php	break;
	    default: ?>
		<?php echo JText::_('JM_LBL_UPDATE_NOTAVAILABLE') ?>
    <?php endswitch; ?>
    </h3>
</div>

<?php if($mode != 'unsupported'): ?>
    <table id="version_info_table" class="ui-corner-all">
	<tr>
	    <td class="label"><?php echo JText::_('JM_LBL_UPDATE_EDITION') ?></td>
	    <td colspan="3">
		    <strong>joomlamailer MailChimp integration</strong>
	    </td>
	</tr>
	<tr>
	    <td class="label"><?php echo JText::_('JM_LBL_UPDATE_YOURVERSION') ?></td>
	    <td>
		<span class="version"><?php echo $this->updates->current_version ?></span>
		<span class="version-status">
			(<?php echo JText::_('JM_LBL_UPDATE_STATUS_'.strtoupper($this->updates->current_status)); ?>)
		</span>
	    </td>
	    <td colspan="2">
		<?php echo JText::_('JM_LBL_UPDATE_RELEASEDON') ?>
		<span class="reldate"><?php echo $this->updates->current_date ?></span>
	    </td>
	</tr>
	<tr>
	    <td class="label"><?php echo JText::_('JM_LBL_UPDATE_LATESTVERSION') ?></td>
	    <td>
		<span class="version"><?php echo $this->updates->latest_version ?></span>
		<span class="version-status">
			(<?php echo JText::_('JM_LBL_UPDATE_STATUS_'.strtoupper($this->updates->status)); ?>)
		</span>
	    </td>
	    <td colspan="2">
		<?php echo JText::_('JM_LBL_UPDATE_RELEASEDON') ?>
		<span class="reldate"><?php echo $this->updates->latest_date ?></span>
	    </td>
	</tr>
	<tr>
	    <td class="label"><?php echo JText::_('JM_LBL_UPDATE_PACKAGELOCATION') ?></td>
	    <td colspan="3">
		<a href="<?php echo htmlentities($this->updates->package_url.$this->updates->package_url_suffix) ?>">
			<?php echo htmlentities($this->updates->package_url); ?>
		</a>
	    </td>
	</tr>
	<tr>
	    <td class="label"><?php echo JText::_('JM_LBL_UPDATE_CHANGELOG') ?></td>
	    <td colspan="3">
		<a href="<?php echo htmlentities($this->updates->changelog) ?>" class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }">
			<?php echo htmlentities($this->updates->changelog); ?>
		</a>
	    </td>
	</tr>
	</table>

	<div id="updater-buttons">
	    <table width="100%">
		<?php if($mode == 'update'): ?>
		<tr>
		    <td></td>
		    <td width="200">
			<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="updateform">
			    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
			    <input type="hidden" name="controller" value="update" />
			    <input type="hidden" name="task" value="update" />
			    <input type="submit" class="submit" value="<?php echo JText::_('JM_LBL_UPDATE_UPDATENOW'); ?>" />
			</form>
		    </td>
		    <td></td>
		</tr>
		<?php endif; ?>
		<tr>
		    <td></td>
		    <td width="200">
			<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="requeryform">
			    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
			    <input type="hidden" name="view" value="update" />
			    <input type="hidden" name="task" value="force" />
			    <input type="submit" class="submit" value="<?php echo JText::_('JM_LBL_UPDATE_FORCE'); ?>" />
			</form>
		    </td>
		    <td></td>
		</tr>
	    </table>
	</div>
	<br />

<?php endif; ?>