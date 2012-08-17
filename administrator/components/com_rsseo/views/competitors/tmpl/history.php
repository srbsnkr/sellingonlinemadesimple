<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php?option=com_rsseo&task=listcompetitorshistory" method="post" name="adminForm">
	<div id="editcell1">
		<table class="adminlist" width="100%">
			<thead>
			<tr>
				<th width="1%">
					<?php echo JText::_( 'RSSEO_ROW_COMPETITORS_NUMBER' ); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->listhistory); ?>);"/>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_PAGE_RANK');
						echo $msg;
					?>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_ALEXA_RANK');
						echo $msg;
					?>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_TEHNORATI_RANK');
						echo $msg;
					?>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_GOOGLE_PAGES');
						echo $msg;
					?>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_YAHOO_PAGES');
						echo $msg;
					?>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_BING_PAGES');
						echo $msg;
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_GOOGLE_BACKLINKS');
						echo $msg;
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_YAHOO_BACKLINKS');
						echo $msg;
					?>
				</th>
				<th width="5%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_BING_BACKLINKS');
						echo $msg;
					?>
				</th>
				<th width="10%">
					<?php
						$msg=JText::_('RSSEO_LISTCOMPETITORS_DATE_REFRESHED');
						echo $msg;
					?>
				</th>
			</tr>
			</thead>
	<?php
	$k = 0;
	for ($i=0,$n=count($this->listhistory);$i<$n;$i++)
	{
		$row =& $this->listhistory[$i];		
		$checked=JHTML::_('grid.id',$i,$row->IdCompetitorHistory);
		
		$pageRank = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->PageRank : $this->listhistory[$i]->PageRank);
		$alexaRank = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->AlexaRank : $this->listhistory[$i]->AlexaRank);
		$tehnoratiRank = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->TehnoratiRank : $this->listhistory[$i]->TehnoratiRank);
		$googlePages = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->GooglePages : $this->listhistory[$i]->GooglePages);
		$yahooPages = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->YahooPages : $this->listhistory[$i]->YahooPages);
		$bingPages = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->BingPages : $this->listhistory[$i]->BingPages);
		$googleBacklinks = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->GoogleBacklinks : $this->listhistory[$i]->GoogleBacklinks);
		$yahooBacklinks = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->YahooBacklinks : $this->listhistory[$i]->YahooBacklinks);
		$bingBacklinks = ($i<count($this->listhistory)-1 ? $this->listhistory[$i+1]->BingBacklinks : $this->listhistory[$i]->BingBacklinks);
	?>		
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->IdCompetitorHistory; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				
				<td class="color<?php echo ($pageRank != $row->PageRank ? ($pageRank > $row->PageRank ? 'red':'green') : 'none'); ?>" align="center"><?php echo $row->PageRank; ?></td>	
				<td class="color<?php echo ($alexaRank != $row->AlexaRank ? ($alexaRank > $row->AlexaRank ? 'green':'red') : 'none') ; ?>" align="center"><?php echo number_format($row->AlexaRank,0,'','.'); ?></td>	
				<td class="color<?php echo ($tehnoratiRank != $row->TehnoratiRank ? ($tehnoratiRank > $row->TehnoratiRank ? 'green':'red') : 'none') ; ?>" align="center"><?php echo number_format($row->TehnoratiRank,0,'','.'); ?></td>	
				<td class="color<?php echo ($googlePages != $row->GooglePages ? ($googlePages > $row->GooglePages ? 'red':'green') : 'none'); ?>" align="center"><?php echo number_format($row->GooglePages,0,'','.'); ?></td>		
				<td class="color<?php echo ($yahooPages != $row->YahooPages ? ($yahooPages > $row->YahooPages ? 'red':'green') : 'none'); ?>" align="center"><?php echo number_format($row->YahooPages,0,'','.'); ?></td>
				<td class="color<?php echo ($bingPages != $row->BingPages ? ($bingPages > $row->BingPages ? 'red':'green') : 'none'); ?>" align="center"><?php echo number_format($row->BingPages,0,'','.') ;?></td>		
				<td class="color<?php echo ($googleBacklinks != $row->GoogleBacklinks ? ($googleBacklinks > $row->GoogleBacklinks ? 'red':'green') : 'none'); ?>" align="center"><?php echo number_format($row->GoogleBacklinks,0,'','.'); ?></td>		
				<td class="color<?php echo ($yahooBacklinks != $row->YahooBacklinks ? ($yahooBacklinks > $row->YahooBacklinks ? 'red':'green') : 'none'); ?>" align="center"><?php echo number_format($row->YahooBacklinks,0,'','.'); ?></td>		
				<td class="color<?php echo ($bingBacklinks != $row->BingBacklinks ? ($bingBacklinks > $row->BingBacklinks ? 'red':'green') : 'none'); ?>" align="center"><?php echo number_format($row->BingBacklinks,0,'','.');?></td>		
				
				<td align="center"><?php echo date($this->rsseoConfig['global.dateformat'],$row->DateRefreshed); ?></span></td>	
			</tr>
	<?php
		$k=1-$k;
	}
	?>
		<tfoot>
			<tr>
				<td colspan="12"><?php //echo $this->pagination->getListFooter(); ?></td>
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
	<input type="hidden" name="rs_filter" value="" id="filter" />
	</form>