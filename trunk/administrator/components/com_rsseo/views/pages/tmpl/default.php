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
	
	<?php if (rsseoHelper::is16()) { ?>
	Joomla.submitbutton = function(task) 
	{
		var form = document.adminForm;
		var cid = document.getElementsByName('cid[]');
		var array1 = new Array();
		var array2 = new Array();
		
		if(task == 'restore')
		{
			for(i=0;i<cid.length;i++)
			{
				if(cid[i].checked == true)
					array1.push(cid[i].value);
			}
			rss_restore(array1);
		} else if (task == 'refresh')
		{
			for(i=0;i<cid.length;i++)
			{
				if(cid[i].checked == true)
					array2.push(cid[i].value);
			}
			rss_refresh(array2);
			
		} else Joomla.submitform(task);
		return false;
	}
	<?php } else { ?>
	function submitbutton(task)
	{
		var form = document.adminForm;
		var cid = document.getElementsByName('cid[]');
		var array1 = new Array();
		var array2 = new Array();
		
		if(task == 'restore')
		{
			for(i=0;i<cid.length;i++)
			{
				if(cid[i].checked == true)
					array1.push(cid[i].value);
			}
			rss_restore(array1);
		} else if (task == 'refresh')
		{
			for(i=0;i<cid.length;i++)
			{
				if(cid[i].checked == true)
					array2.push(cid[i].value);
			}
			rss_refresh(array2);
			
		} else submitform(task);
		return false;
		 
	}
	<?php } ?>
</script>
<form action="index.php?option=com_rsseo&task=listpages" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="100%"><?php echo JText::_('RSSEO_FILTER'); ?> <input type="text" name="rs_filter" id="filter" onchange="createFilter(document.getElementById('filter').value);" class="text_area" value="<?php echo $this->filter; ?>"/> <input type="button" value="<?php echo JText::_('RSSEO_FILTER'); ?>" onclick="createFilter(document.getElementById('filter').value);return false;" /> <input type="button" onclick="eraseFilter();" value="<?php echo JText::_('RSSEO_CLEAR_FILTER'); ?> " />
			</td>
			<td nowrap="nowrap">
				<?php echo $this->lists['page_published'];	?>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->lists['page_level'];	?>
			</td>
		</tr>
	</table>
	<div id="rss_loader" style="display:none;"><?php echo JText::_('RSSEO_PLEASE_WAIT'); ?> <img style="vertical-align:middle;" src="<?php echo JURI::root() ?>administrator/components/com_rsseo/assets/images/loader.gif" /></div>
	<div id="editcell1">
		<table class="adminlist" width="100%">
			<thead>
			<tr>
				<th width="1%">
					<?php echo JText::_( 'RSSEO_ROW_PAGE_NUMBER' ); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->listpages); ?>);"/>
				</th>
				<th width="20%">
					<?php
						$msg=JText::_('RSSEO_LISTPAGES_PAGEURL');
						echo JHTML::_('grid.sort',$msg,'PageURL',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="20%">
					<?php
						$msg=JText::_('RSSEO_PAGE_TITLE');
						echo JHTML::_('grid.sort',$msg,'PageTitle',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="4%">
					<?php
						$msg=JText::_('RSSEO_PAGE_LEVEL');
						echo JHTML::_('grid.sort',$msg,'PageLevel',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="2%">
					<?php
						$msg=JText::_('RSSEO_PAGE_GRADE');
						echo JHTML::_('grid.sort',$msg,'PageGrade',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="7%">
					<?php
						$msg=JText::_('RSSEO_PAGE_LAST_CRAWLED');
						echo JHTML::_('grid.sort',$msg,'PageCrawled',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="2%">
					<?php
						$msg=JText::_('RSSEO_PAGE_STATUS');
						echo JHTML::_('grid.sort',$msg,'published',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_PAGE_MODIFIED');
						echo JHTML::_('grid.sort',$msg,'PageModified',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_PAGE_IN_SITEMAP');
						echo JHTML::_('grid.sort',$msg,'PageInSitemap',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="3%">
					<?php
						echo JText::_('RSSEO_PAGE_REFRESH');
					?>
				</th>
			</tr>
			</thead>
	<?php
	$k = 0;
	for ($i=0,$n=count($this->listpages);$i<$n;$i++)
	{
		$u =& JURI::getInstance('SERVER');
		$row =& $this->listpages[$i];		
		$checked=JHTML::_('grid.id',$i,$row->IdPage);
		$publish=JHTML::_('grid.published',$row,$i);
		$PageGrade = ceil($row->PageGrade);
		$modified = ($row->PageModified == 1) ? '<img src="'.JURI::root().'administrator/components/com_rsseo/assets/images/ok.png" />' : '<img src="'.JURI::root().'administrator/components/com_rsseo/assets/images/notok.png" />' ;
		
		switch($row->PageGrade){
			case ($row->PageGrade>=33 && $row->PageGrade < 66): $color = 'Orange'; $PageGrade .= ' %';break;
			case ($row->PageGrade>=0 && $row->PageGrade <33): $color = 'Red'; $PageGrade .= ' %';break;
			case -1:$color = 'Gray';$PageGrade = JText::_('RSSEO_PAGE_NOT_RATED');break;
			default : $color = 'Green'; $PageGrade .= ' %';break;
		}
	
	?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->IdPage; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td><?php echo '<a href="index.php?option=com_rsseo&task=editpage&cid='.$row->IdPage.'">'.$row->PageURL.'</a>'; ?> <?php echo '<a href="'.JURI::root().$row->PageURL.'" target="_blank"><img src="'.JURI::root().'administrator/components/com_rsseo/assets/images/external-link.png" alt="External" border="0" /></a>'; ?> </td>	
				<td><?php echo '<a href="index.php?option=com_rsseo&task=editpage&cid='.$row->IdPage.'"><span id="PageTitle'.$row->IdPage.'">'.$row->PageTitle.'</span></a>'; ?></td>
				<td align="center"><?php echo ($row->PageLevel >= 127) ? JText::_('RSSEO_UNDEFINED') : $row->PageLevel; ?></td>
				<td class="pageGradeContainer"><div style="width:<?php echo ($row->PageGrade == -1) ? '100' : ceil($row->PageGrade);?>px;" id="pageGrade<?php echo $row->IdPage; ?>" class="pageGrade<?php echo $color;?>"><?php echo $PageGrade; ?></div></td>
				<td align="center"><span id="DatePageId<?php echo $row->IdPage; ?>"><?php echo ($row->DatePageCrawled==0 ? JText::_('RSSEO_PAGE_NOT_CRAWLED'): date($this->rsseoConfig['global.dateformat'],$row->DatePageCrawled)); ?></span></td>
				<td align="center"><?php echo $publish; ?></td>				
				<td align="center"><?php echo $modified; ?></td>				
				<td align="center"><?php echo rsseoHelper::add_to_sitemap($row,$i); ?></td>		
				<td align="center"><a href="#" onclick="crawl(0,<?php echo $row->IdPage;?>);return false;"><?php echo JText::_('RSSEO_REFRESH'); ?></a></td>		
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
	<input type="hidden" name="controller" value="pages" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="listpages" />
	<input type="hidden" name="option" value="com_rsseo" />
	<input type="hidden" name="view" value="pages" />
	<input type="hidden" name="md5_descr" value="<?php echo $this->md5_descr; ?>" />
	<input type="hidden" name="md5_title" value="<?php echo $this->md5_title; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortOrder; ?>" />
	</form>