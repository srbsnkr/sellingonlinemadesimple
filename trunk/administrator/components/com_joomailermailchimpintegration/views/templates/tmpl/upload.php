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
$MCauth = new MCauth();

if ( !$MCapi ) {
    echo '<table>';
    echo $MCauth->apiKeyMissing();
} else if( !$MCauth->MCauth() ) {
    echo '<table>';
    echo $MCauth->apiKeyMissing(1);
} else {

?>
<script language="javascript" type="text/javascript">
<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
    if (document.adminForm.Filedata.value == "" && pressbutton != 'cancel'){
	alert('<?php echo JText::_( 'JM_PLEASE_SELECT_A_FILE_TO_UPLOAD' ); ?>');
    } else {
	<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
    }
}
</script>
<form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm" enctype="multipart/form-data" >

<div class="col100">
    <fieldset class="adminform">
	<legend><?php echo JText::_( 'JM_UPLOAD_TEMPLATE' ); ?></legend>
        <input type="file" id="file-upload" name="Filedata" size="40"/>
    </fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="templates" />
<input type="hidden" name="type" value="templates" />
<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_joomailermailchimpintegration&view=templates'); ?>" />
</form>

<?php
}
?>
