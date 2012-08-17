<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript" type="text/javascript">
	function createFilter(filter)
	{
		document.getElementById('filter').value = filter;
		submitform();
	}
	
	function eraseFilter()
	{
		document.getElementById('filter').value = '';
		submitform();
	}
</script>
<form action="index.php?option=com_rsseo&task=listredirects" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="100%"><?php echo JText::_('RSSEO_FILTER'); ?> <input type="text" name="rs_filter" id="filter" onchange="createFilter(document.getElementById('filter').value);" class="text_area" value="<?php echo $this->filter; ?>"/> <input type="button" value="<?php echo JText::_('RSSEO_FILTER'); ?>" onclick="createFilter(document.getElementById('filter').value);return false;" /> <input type="button" onclick="eraseFilter();" value="<?php echo JText::_('RSSEO_CLEAR_FILTER'); ?> " />
			</td>
			
		</tr>
	</table>
	<div id="editcell1">
		<table class="adminlist" width="100%">
			<thead>
			<tr>
				<th width="1%">
					<?php echo JText::_( 'RSSEO_ROW_REDIRECTS_NUMBER' ); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->listredirects); ?>);"/>
				</th>
				<th width="20%">
					<?php
						$msg=JText::_('RSSEO_REDIRECTS_REDIRECTSFROM');
						echo JHTML::_('grid.sort',$msg,'r.RedirectFrom',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="20%">
					<?php
						$msg=JText::_('RSSEO_REDIRECTS_REDIRECTSTO');
						echo JHTML::_('grid.sort',$msg,'r.RedirectTo',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="20%">
					<?php
						$msg=JText::_('RSSEO_REDIRECTS_REDIRECTSTYPE');
						echo JHTML::_('grid.sort',$msg,'r.RedirectType',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_PUBLISHED');
						echo JHTML::_('grid.sort',$msg,'published',$this->sortOrder,$this->sortColumn);
					?>
				</th>
			</tr>
			</thead>
	<?php
	$k = 0;
	for ($i=0,$n=count($this->listredirects);$i<$n;$i++)
	{
		$row =& $this->listredirects[$i];
		$checked=JHTML::_('grid.id',$i,$row->IdRedirect);
		$publish=JHTML::_('grid.published',$row,$i);		
	
	?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->IdRedirect; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td><?php echo '<a href="index.php?option=com_rsseo&task=editredirect&cid='.$row->IdRedirect.'">'.$row->RedirectFrom.'</a>'; ?></td>
				<td><?php echo '<a href="index.php?option=com_rsseo&task=editredirect&cid='.$row->IdRedirect.'">'.$row->RedirectTo.'</a>'; ?></td>
				<?php if($row->RedirectType == 301) 
					echo '<td><a href="index.php?option=com_rsseo&task=editredirect&cid='.$row->IdRedirect.'">'.JText::_('RSSEO_REDIRECT_PERMANENT').'</a></td>';
					if($row->RedirectType == 302)
					echo '<td><a href="index.php?option=com_rsseo&task=editredirect&cid='.$row->IdRedirect.'">'.JText::_('RSSEO_REDIRECT_TEMPORARY').'</a></td>';
				?>
				<td align="center"><?php echo $publish; ?></td>	
			</tr>
	<?php
		$k=1-$k;
	}
	?>
		<tfoot>
			<tr>
				<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		</table>
	</div>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="listredirects" />
	<input type="hidden" name="option" value="com_rsseo" />
	<input type="hidden" name="view" value="redirects" />
	<input type="hidden" name="controller" value="redirects" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortOrder; ?>" />
	</form>