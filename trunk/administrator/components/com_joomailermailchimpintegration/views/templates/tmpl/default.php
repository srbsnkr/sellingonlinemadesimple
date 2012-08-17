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

$jversion = (version_compare(JVERSION,'1.6.0','ge')) ? '16' : '15';

$template_folders = Jfolder::listFolderTree( '../administrator/components/com_joomailermailchimpintegration/templates/' , '', 1);

if ( !$MCapi ) {
	echo '<table>';
	echo $MCauth->apiKeyMissing();
} else if( !$MCauth->MCauth() ) {
	echo '<table>';
	echo $MCauth->apiKeyMissing(1);
} else if ( !$template_folders[0] ) {
	echo '<form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm">';
	echo JText::_( 'JM_NO_TEMPLATES' );
} else {

?>
<form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm">
<div class="col100">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="20">#</th>
			<th width="20">&nbsp;</th>
			<th><?php echo JText::_( 'JM_NAME' ); ?></th>
			<th width="200"><?php echo JText::_( 'JM_EXAMPLE' ); ?></th>
			<th width="180"><?php echo JText::_( 'JM_DOWNLOAD' ); ?></th>
		</tr>
	</thead>
	<?php
		$i = 1;
		foreach ( $template_folders as $tf ) {

			$template_files = Jfolder::files( $tf['fullname'] , '', 1);
			$editLink = 'index.php?option=com_joomailermailchimpintegration&view=templates&layout=edit&template[]='.$tf['name'];
			$editText = (version_compare(JVERSION,'1.6.0','ge')) ? 'JACTION_EDIT' : 'EDIT';
			$screenshot = false;

			echo '<tr>';
			echo '<td>'.$i.'</td>';
			echo '<td width="20"><input type="checkbox" name="template[]" id="template" value="'.$tf['name'].'" onclick="isChecked(this.checked);"></td>';
			echo '<td align="center"><a title="'.JText::_($editText).'" href="'.$editLink.'">'.$tf['name'].'</a></td>';
			echo '<td align="center">';
			if (is_file( $tf['fullname'].'/screenshot.gif' )) {
				$screenshot = $tf['fullname'].'/screenshot.gif';
			} else if (is_file( $tf['fullname'].'/screenshot.png' )) {
				$screenshot = $tf['fullname'].'/screenshot.png';
			} else if (is_file( $tf['fullname'].'/screenshot.jpg' )) {
				$screenshot = $tf['fullname'].'/screenshot.jpg';
			} else if (is_file( $tf['fullname'].'/screenshot.jpeg' )) {
				$screenshot = $tf['fullname'].'/screenshot.jpeg';
			} elseif (is_file( $tf['fullname'].'/screenshot.bmp' )) {
				$screenshot = $tf['fullname'].'/screenshot.bmp';
			} else if (is_file( $tf['fullname'].'/l.txt')){
				$screenshot = JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/templateLeftCol.gif';
			} else if (is_file( $tf['fullname'].'/r.txt')){
				$screenshot = JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/templateRightCol.gif';
			} else {
				$screenshot = JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/templateSingleCol.gif';
			}
			echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 980, y: 550} }" href="'.$tf['fullname'].'/template.html">'; 
			echo '<img src="'.$screenshot.'" height="150" />';
			echo '</a>';
			echo '</td>';
			echo '<td align="center" nowrap="nowrap"><div id="'.$tf['name'].'"><a href="javascript:ajax_download(\''.$tf['name'].'\', \''.JText::_( 'JM_DOWNLOAD_ERROR' ).'\',\''.$jversion.'\');">'.JText::_( 'JM_DOWNLOAD' ).'</a></div></td>';
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
<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="templates" />
<input type="hidden" name="type" value="templates" />
</form>

