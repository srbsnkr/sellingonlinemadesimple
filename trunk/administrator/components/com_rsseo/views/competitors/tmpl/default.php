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
		Joomla.submitform();
	}
	
	function eraseFilter()
	{
		document.getElementById('filter').value = '';
		Joomla.submitform();
	}
</script>
<form action="index.php?option=com_rsseo&task=listcompetitors" method="post" name="adminForm">
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
					<?php echo JText::_( 'RSSEO_ROW_COMPETITORS_NUMBER' ); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->listcompetitors); ?>);"/>
				</th>
				<th width="1%">
					<?php echo	$msg=JText::_('RSSEO_LISTCOMPETITORS_HISTORY'); ?>
				</th>
				<th width="15%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_COMPETITORS');
						echo JHTML::_('grid.sort',$msg,'c.Competitor',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php if($this->rsseoConfig['enable.pr']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_PAGE_RANK');
						echo JHTML::_('grid.sort',$msg,'c.LastPageRank',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.alexa']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_ALEXA_RANK');
						echo JHTML::_('grid.sort',$msg,'c.LastAlexaRank',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.tehnorati']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_TEHNORATI_RANK');
						echo JHTML::_('grid.sort',$msg,'c.LastTehnoratiRank',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.googlep']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_GOOGLE_PAGES');
						echo JHTML::_('grid.sort',$msg,'c.LastGooglePages',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.yahoop']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_YAHOO_PAGES');
						echo JHTML::_('grid.sort',$msg,'c.LastYahooPages',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.bingp']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_BING_PAGES');
						echo JHTML::_('grid.sort',$msg,'c.LastBingPages',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.googleb']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_GOOGLE_BACKLINKS');
						echo JHTML::_('grid.sort',$msg,'c.LastGoogleBacklinks',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.yahoob']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_YAHOO_BACKLINKS');
						echo JHTML::_('grid.sort',$msg,'c.LastYahooBacklinks',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['enable.bingb']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_BING_BACKLINKS');
						echo JHTML::_('grid.sort',$msg,'c.LastBingBacklinks',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<?php if($this->rsseoConfig['search.dmoz']) { ?>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_DMOZ');
						echo JHTML::_('grid.sort',$msg,'c.Dmoz',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<?php } ?>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_DATE_REFRESHED');
						echo JHTML::_('grid.sort',$msg,'c.LastDateRefreshed',$this->sortOrder,$this->sortColumn);
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_ORDER');
						echo JHTML::_('grid.sort',$msg,'c.ordering',$this->sortOrder,$this->sortColumn);
						echo '&nbsp;&nbsp;';
						echo JHTML::_('grid.order',$this->listcompetitors);
					?>
				</th>
				<th width="10%">
					<?php
						echo JText::_('RSSEO_LISTCOMPETITORS_REFRESH');
					?>
				</th>
			</tr>
			</thead>
	<?php
	$db = JFactory::getDBO();
	$k = 0;
	for ($i=0,$n=count($this->listcompetitors);$i<$n;$i++)
	{
		$row =& $this->listcompetitors[$i];		
		$checked=JHTML::_('grid.id',$i,$row->IdCompetitor);
		$competitor = (empty($row->Competitor)) ? JText::_('RSSEO_EMPTY') : $row->Competitor;
		
		$db->setQuery("SELECT * FROM #__rsseo_competitors_history WHERE IdCompetitor = ".$row->IdCompetitor." ORDER BY DateRefreshed DESC LIMIT 2 ");
		$history = $db->loadObjectList();
		
		$color1 = 'none';$color2 = 'none';$color3 = 'none';$color4 = 'none';$color5 = 'none';$color6 = 'none';$color7 = 'none';$color8 = 'none';$color9 = 'none';
		
		if(!empty($history))
		{
			if(isset($history[1])) $compare = $history[1]; else $compare = $history[0];
			
			//google page rank
			if($compare->PageRank < $row->LastPageRank) $color1 = 'green';
			if($compare->PageRank > $row->LastPageRank) $color1 = 'red';
			if($compare->PageRank == $row->LastPageRank) $color1 = 'none';
			
			//alexa page rank
			if($compare->AlexaRank < $row->LastAlexaRank) $color2 = 'red';
			if($compare->AlexaRank > $row->LastAlexaRank) $color2 = 'green';
			if($compare->AlexaRank == $row->LastAlexaRank) $color2 = 'none';
			
			//google pages
			if($compare->GooglePages < $row->LastGooglePages) $color3 = 'green';
			if($compare->GooglePages > $row->LastGooglePages) $color3 = 'red';
			if($compare->GooglePages == $row->LastGooglePages) $color3 = 'none';
			
			//yahoo pages
			if($compare->YahooPages < $row->LastYahooPages) $color4 = 'green';
			if($compare->YahooPages > $row->LastYahooPages) $color4 = 'red';
			if($compare->YahooPages == $row->LastYahooPages) $color4 = 'none';
			
			//bing pages
			if($compare->BingPages < $row->LastBingPages) $color5 = 'green';
			if($compare->BingPages > $row->LastBingPages) $color5 = 'red';
			if($compare->BingPages == $row->LastBingPages) $color5 = 'none';
			
			//google backlinks
			if($compare->GoogleBacklinks < $row->LastGoogleBacklinks) $color6 = 'green';
			if($compare->GoogleBacklinks > $row->LastGoogleBacklinks) $color6 = 'red';
			if($compare->GoogleBacklinks == $row->LastGoogleBacklinks) $color6 = 'none';
			
			//yahoo backlinks
			if($compare->YahooBacklinks < $row->LastYahooBacklinks) $color7 = 'green';
			if($compare->YahooBacklinks > $row->LastYahooBacklinks) $color7 = 'red';
			if($compare->YahooBacklinks == $row->LastYahooBacklinks) $color7 = 'none';
			
			//bing backlinks
			if($compare->BingBacklinks < $row->LastBingBacklinks) $color8 = 'green';
			if($compare->BingBacklinks > $row->LastBingBacklinks) $color8 = 'red';
			if($compare->BingBacklinks == $row->LastBingBacklinks) $color8 = 'none';
			
			//tehnorati rank
			if($compare->TehnoratiRank < $row->LastTehnoratiRank) $color9 = 'green';
			if($compare->TehnoratiRank > $row->LastTehnoratiRank) $color9 = 'red';
			if($compare->TehnoratiRank == $row->LastTehnoratiRank) $color9 = 'none';
		}
	?>		
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->IdCompetitor; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td align="center"><?php echo '<a href="index.php?option=com_rsseo&task=listcompetitorshistory&cid='.$row->IdCompetitor.'"><img src="components/com_rsseo/assets/images/history.png" alt="history" border="0"/></a>'; ?></td>	
				<td><?php echo '<a href="index.php?option=com_rsseo&task=editcompetitor&cid='.$row->IdCompetitor.'">'.$competitor.'</a>'; ?></td>	
				
				<?php if($this->rsseoConfig['enable.pr']) { ?>
				<td align="center"><span id="PageRankId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color1; ?>"><?php echo ($row->LastPageRank == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : $row->LastPageRank ) ?></span></td>	
				<?php } else { ?>
				<input type="hidden" name="PageRankId<?php echo $row->IdCompetitor;?>" id="PageRankId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.alexa']) { ?>
				<td align="center"><span id="PageAlexaRankId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color2; ?>"><?php echo ($row->LastAlexaRank == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastAlexaRank,0,'','.') ) ?></span></td>
				<?php } else { ?>
				<input type="hidden" name="PageAlexaRankId<?php echo $row->IdCompetitor;?>" id="PageAlexaRankId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.tehnorati']) { ?>
				<td align="center"><span id="PageTehnoratiRankId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color9; ?>"><?php echo ($row->LastTehnoratiRank == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastTehnoratiRank,0,'','.') ) ?></span></td>
				<?php } else { ?>
				<input type="hidden" name="PageTehnoratiRankId<?php echo $row->IdCompetitor;?>" id="PageTehnoratiRankId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>				
				
				<?php if($this->rsseoConfig['enable.googlep']) { ?>
					<td align="center">
					<span id="GooglePagesId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color3; ?>"><?php echo ($row->LastGooglePages == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastGooglePages,0,'','.') ) ?></span></td>		
				<?php } else { ?>
				<input type="hidden" name="GooglePagesId<?php echo $row->IdCompetitor;?>" id="GooglePagesId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.yahoop']) { ?>
				<td align="center"><span id="YahooPagesId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color4; ?>"><?php echo ($row->LastYahooPages == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastYahooPages,0,'','.') ) ?></span></td>		
				<?php } else { ?>
				<input type="hidden" name="YahooPagesId<?php echo $row->IdCompetitor;?>" id="YahooPagesId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.bingp']) { ?>
				<td align="center"><span id="BingPagesId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color5; ?>"><?php echo ($row->LastBingPages == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastBingPages,0,'','.') )?></span></td>		
				<?php } else { ?>
				<input type="hidden" name="BingPagesId<?php echo $row->IdCompetitor;?>" id="BingPagesId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.googleb']) { ?>
				<td align="center"><span id="GoogleBacklinksId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color6; ?>"><?php echo ($row->LastGoogleBacklinks == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastGoogleBacklinks,0,'','.') ) ?></span></td>	
				<?php } else { ?>
				<input type="hidden" name="GoogleBacklinksId<?php echo $row->IdCompetitor;?>" id="GoogleBacklinksId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.yahoob']) { ?>
				<td align="center"><span id="YahooBacklinksId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color7; ?>"><?php echo ($row->LastYahooBacklinks == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastYahooBacklinks,0,'','.')) ?></span></td>		
				<?php } else { ?>
				<input type="hidden" name="YahooBacklinksId<?php echo $row->IdCompetitor;?>" id="YahooBacklinksId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['enable.bingb']) { ?>
				<td align="center"><span id="BingBacklinksId<?php echo $row->IdCompetitor;?>" class="color<?php echo $color8; ?>"><?php echo ($row->LastBingBacklinks == -1 ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : number_format($row->LastBingBacklinks,0,'','.') )?></span></td>		
				<?php } else { ?>
				<input type="hidden" name="BingBacklinksId<?php echo $row->IdCompetitor;?>" id="BingBacklinksId<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<?php if($this->rsseoConfig['search.dmoz']) { ?>
				<td align="center"><span id="dmoz<?php echo $row->IdCompetitor;?>"><?php if($row->Dmoz == 0) echo JText::_('RSSEO_NO'); if($row->Dmoz == 1) echo JText::_('RSSEO_YES'); if($row->Dmoz == -1) echo JText::_('RSSEO_COMPETITOR_NOT_PROCESSED'); ?></span></td>	
				<?php } else { ?>
				<input type="hidden" name="dmoz<?php echo $row->IdCompetitor;?>" id="dmoz<?php echo $row->IdCompetitor;?>" value="" />
				<?php } ?>
				
				<td align="center"><span id="DateCompetitorId<?php echo $row->IdCompetitor;?>"><?php echo ($row->LastDateRefreshed == -1) ? JText::_('RSSEO_COMPETITOR_NOT_PROCESSED') : date($this->rsseoConfig['global.dateformat'],$row->LastDateRefreshed); ?></span></td>	
				<td class="order" align="center">
					<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', 'ordering'); ?></span>
					<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', 'ordering' ); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align:center;" />
				</td>		
				<td align="center"><a href="#" onclick="refreshCompetitor('<?php echo JURI::root(); ?>',<?php echo $row->IdCompetitor;?>);return false;">Refresh</a></td>
			</tr>
	<?php
		$k=1-$k;
	}
	?>
		<tfoot>
			<tr>
				<td colspan="17"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		</table>
	</div>
	<?php echo JHTML::_('form.token');?>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="listcompetitors" />
	<input type="hidden" name="option" value="com_rsseo" />
	<input type="hidden" name="view" value="competitors" />
	<input type="hidden" name="controller" value="competitors" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortOrder; ?>" />
	</form>