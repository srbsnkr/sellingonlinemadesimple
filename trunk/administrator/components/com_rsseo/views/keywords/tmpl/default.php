<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
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
<form action="index.php?option=com_rsseo&task=listkeywords" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="100%"><?php echo JText::_('RSSEO_FILTER'); ?> <input type="text" name="rs_filter" id="filter" onchange="createFilter(document.getElementById('filter').value);" class="text_area" value="<?php echo $this->filter; ?>"/> <input type="button" value="<?php echo JText::_('RSSEO_FILTER'); ?>" onclick="createFilter(document.getElementById('filter').value);return false;" /> <input type="button" onclick="eraseFilter();" value="<?php echo JText::_('RSSEO_CLEAR_FILTER'); ?> " />
			</td>
			<td nowrap="nowrap">
				<?php echo $this->lists['importance'];	?>
			</td>
		</tr>
	</table>
	<div id="editcell1">
		<table class="adminlist" width="100%">
			<thead>
			<tr>
				<th width="1%">
					<?php echo JText::_( 'RSSEO_ROW_NUMBER' ); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->listkeywords); ?>);"/>
				</th>
				<th width="20%">
					<?php
						$msg=JText::_('RSSEO_LISTKEYWORDS_KEYWORD');
						echo JHTML::_('grid.sort',$msg,'Keyword',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTKEYWORDS_KEYWORDIMPORTANCE');
						echo JHTML::_('grid.sort',$msg,'KeywordImportance',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTKEYWORDS_KEYWORDPOSITION');
						echo JHTML::_('grid.sort',$msg,'ActualKeywordPosition',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTKEYWORDS_DATEREFRESHED');
						echo JHTML::_('grid.sort',$msg,'DateRefreshed',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php echo JText::_('RSSEO_LISTKEYWORDS_REFRESH'); ?>
				</th>
			</tr>
			</thead>
	<?php
	$k = 0;
	for ($i=0,$n=count($this->listkeywords);$i<$n;$i++)
	{
		$color = '';
		$row =& $this->listkeywords[$i];		
		$checked=JHTML::_('grid.id',$i,$row->IdKeyword);
		
		if(!empty($row->LastKeywordPosition))
		{
			if($row->ActualKeywordPosition  > $row->LastKeywordPosition) $color = "red";
			if($row->ActualKeywordPosition  < $row->LastKeywordPosition) $color = "green";
			if($row->ActualKeywordPosition  == $row->LastKeywordPosition) $color = "none";
		} else $color = 'none';

		
	?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->IdKeyword; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td><?php echo '<a href="index.php?option=com_rsseo&task=editkeyword&cid='.$row->IdKeyword.'">'.$row->Keyword.'</a>'; ?></td>				
				<td align="center"><?php echo $row->KeywordImportance; ?></td>
				<td align="center"><span class="color<?php echo $color; ?>" id="KeywordId<?php echo $row->IdKeyword; ?>"><?php echo ($row->ActualKeywordPosition>100 ? '&gt; 100' : $row->ActualKeywordPosition); ?></span></td>
				<td align="center"><span id="DateKeywordId<?php echo $row->IdKeyword;?>"><?php echo date($this->rsseoConfig['global.dateformat'],$row->DateRefreshed); ?></span></td>
				<td align="center"><a href="#" onclick="refreshKeyword('<?php echo JURI::root(); ?>' ,<?php echo $row->IdKeyword; ?>);return false;">Refresh</a></td>
			</tr>
	<?php
		$k=1-$k;
	}
	?>
		<tfoot>
			<tr>
				<td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		</table>
	</div>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="listkeywords" />
	<input type="hidden" name="option" value="com_rsseo" />
	<input type="hidden" name="view" value="keywords" />
	<input type="hidden" name="controller" value="keywords" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortOrder; ?>" />
	
	</form>