<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<th>
			<?php echo JText::_('RSSEO_UPDATE_CHECKING'); ?>
		</th>
	</tr>
	<tr>
		<td>
			<iframe src="http://www.rsjoomla.com/index.php?option=com_rshelp&task=rev.check&sess=<?php  echo rsseoViewrsseo::genKeyCode();?>&amp;rev=<?php echo RSSEO_REVISION;?>&amp;joomla=j15x&amp;Itemid=43" style="border:0px solid;width:100%;height:18px;" scrolling="no" frameborder="no"></iframe>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th>
			<?php echo JText::_('RSSEO_LATEST_NEWS'); ?>
		</th>
	</tr>
	<tr>
		<td><iframe src="http://www.rsjoomla.com/latest.html?tmpl=component" style="border:0px solid;width:100%;height:380px;" scrolling="no" frameborder="no"></iframe></td>
	</tr>
</table>

<input type="hidden" name="task" value="update"/>
<input type="hidden" name="option" value="com_rsseo"/>
</form>