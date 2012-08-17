<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

if (!empty($this->config['ga.account']))
{
$count = count($this->visits);
?>

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript"> google.load('visualization', '1', {packages: ['corechart','corechart']}); </script>

<script type="text/javascript">
function drawVisualization() 
{
	// Create and populate the data table.
	var data = new google.visualization.DataTable();

	data.addColumn('string', '<?php echo JText::_('RSSEO_GA_CHART_DATE'); ?>');
	data.addColumn('number', '<?php echo JText::_('RSSEO_GA_CHART_VISITS'); ?>');
	data.addRows(<?php echo $count; ?>);

	<?php $i = 0; ?>
	<?php if (!empty($this->visits)) { ?>
	<?php foreach ($this->visits as $date => $visit) { ?>
			data.setCell(<?php echo $i; ?>, 0, '<?php echo date('l, F d, Y',$date); ?>');
			data.setCell(<?php echo $i; ?>, 1, <?php echo $visit->visits; ?>);
	<?php	
			if ($i > $count) break;
			$i++;
		}}
	?>

	// Create and draw the visualization.
	new google.visualization.AreaChart(document.getElementById('rss_visualization')).
	draw(data, { legend: 'none', height : '250', width: '1500' , hAxis: {textPosition: 'none' } , pointSize: '6',  title: '<?php echo JText::_('RSSEO_GA_CHART_VISITS'); ?>', backgroundColor: {stroke:'#666', fill:'#FFFFFF', strokeSize: 1}});  
	
	
	<?php if (!empty($this->sources['details'])) { ?>
	// Create and populate the data table.
	var Pie = new google.visualization.DataTable();
	Pie.addColumn('string', '<?php echo JText::_('RSSEO_GRAPH_SOURCE'); ?>');
	Pie.addColumn('number', '<?php echo JText::_('RSSEO_GA_CHART_VISITS'); ?>');
	Pie.addRows(3);
	Pie.setValue(0, 0, '<?php echo JText::_('RSSEO_GRAPH_REFERRING_SITES'); ?>');
	Pie.setValue(0, 1, <?php echo $this->sources['details'][2]; ?>);
	Pie.setValue(1, 0, '<?php echo JText::_('RSSEO_GRAPH_DIRECT_TRAFFIC'); ?>');
	Pie.setValue(1, 1, <?php echo $this->sources['details'][0]; ?>);
	Pie.setValue(2, 0, '<?php echo JText::_('RSSEO_GRAPH_SEARCH_ENGINES'); ?>');
	Pie.setValue(2, 1, <?php echo $this->sources['details'][1]; ?>);

	
	// Create and draw the visualization.
	new google.visualization.PieChart(document.getElementById('rss_pie')).
		draw(Pie, { legend: 'none', legendFontSize: 12, pieSliceText : 'none' ,  height : '250', width: '350' , backgroundColor: {stroke:'#666', fill:'#FFFFFF', strokeSize: 1}});
	<?php } ?>
}
	
	google.setOnLoadCallback(drawVisualization);
</script>
<?php } ?>

<?php if (!empty($this->accounts)) { ?>
<form action="index.php?option=com_rsseo&task=analytics" method="post" name="adminForm" id="adminForm">
<div class="rss_options">
	<?php echo JText::_('RSSEO_ACCOUNT_NAME'). ' ' .$this->lists['accounts']; ?> <?php echo JText::_('RSSEO_GA_STARTDATE'). ' ' .$this->lists['start']; ?>  <?php echo JText::_('RSSEO_GA_ENDDATE'). ' ' .$this->lists['end']; ?>  <button type="button" onclick="javascript: submitbutton('save')"><?php echo JText::_('RSSEO_UPDATE_BTN'); ?></button> <br /> <span id="rss_loader" style="display:none;"><img src="<?php echo JURI::root(); ?>administrator/components/com_rsseo/assets/images/rssloader.gif" /></span>
</div>

<?php echo $this->tabs->startPane('content-pane'); ?>
<?php echo $this->tabs->startPanel(JText::_('RSSEO_GA_TAB1'),"analytics-tab1"); ?>
<table cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td colspan="2">
			<div id="rss_visualization" style="text-align: center; clear: both;"></div><br />
		</td>
	</tr>
	<tr>
		<td>
			<span id="gageneral"></span>
		</td>
		<td valign="top">
			<span id="ganewreturning"></span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<span id="gavisits"></span>
		</td>
	</tr>
	<tr>
		<td>
			<span id="gabrowsers"></span>
		</td>
		<td valign="top">
			<span id="gamobiles"></span>
		</td>
	</tr>
</table>
<?php echo $this->tabs->endPanel(); ?>

<?php echo $this->tabs->startPanel(JText::_('RSSEO_GA_TAB2'),"analytics-tab2"); ?>
<table cellpadding="0" cellspacing="0" width="100%">
	<?php if (!empty($this->sources['details'])) { ?>
	<?php 
		if (!empty($this->sources['details'][0]) && !empty($this->sources['details'][1]) && !empty($this->sources['details'][2]))
		{
			$dtraffic = $this->sources['details'][0];
			$straffic = $this->sources['details'][1];
			$rtraffoc = $this->sources['details'][2];
			$total = $this->sources['details'][0] + $this->sources['details'][1] + $this->sources['details'][2];
		} else { $total = 1; $dtraffic = 1; 	$straffic = 1; $rtraffoc = 1; }
		
		$direct = number_format( (($dtraffic * 100)/$total) , 2);
		$reffer = number_format( (($rtraffoc * 100)/$total) , 2);
		$search = number_format( (($straffic * 100)/$total) , 2);
		
	?>
	<tr style="text-align: center;">
		<td align="right" style="width:45%;">
			<table>
				<tr>
					<td align="right"><b><?php echo JText::_('RSSEO_GRAPH_DIRECT_TRAFFIC'); ?></b></td>
					<td><?php echo $direct; ?> % <span class="rss_color" style="background:#dc3912"></span></td>
				</tr>
				<tr>
					<td align="right"><b><?php echo JText::_('RSSEO_GRAPH_REFERRING_SITES'); ?></b></td>
					<td><?php echo $reffer; ?> % <span class="rss_color" style="background:#3366cc"></span></td>
				</tr>
				<tr>
					<td align="right"><b><?php echo JText::_('RSSEO_GRAPH_SEARCH_ENGINES'); ?></b></td>
					<td><?php echo $search; ?> % <span class="rss_color" style="background:#ff9900"></span></td>
				</tr>
			</table>
		</td>
		<td align="left"><div id="rss_pie" style="clear: both;"></div></td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2">
			<span id="gasources"></span>
		</td>
	</tr>
</table>
<?php echo $this->tabs->endPanel(); ?>

<?php echo $this->tabs->startPanel(JText::_('RSSEO_GA_TAB3'),"analytics-tab3"); ?>
<table cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td>
			<span id="gacontent"></span>
		</td>
	</tr>
</table>
<?php echo $this->tabs->endPanel(); ?>
<?php echo $this->tabs->endPane(); ?>


<span id="gaaccount"></span>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="controller" value="analytics" />
<input type="hidden" name="view" value="analytics" />
<input type="hidden" name="task" value="" />
</form>
<script type="text/javascript">
	var account = <?php if (empty($this->config['ga.account'])) { ?> $('account').options[$('account').selectedIndex].value <?php } else { ?> ''<?php } ?> ;
	var start = <?php if (empty($this->config['ga.account'])) { ?> $('rssestart').value <?php } else { ?> ''<?php } ?> ;
	var end = <?php if (empty($this->config['ga.account'])) { ?> $('rsseend').value <?php } else { ?> ''<?php } ?> ;
	
	window.addEvent('domready', function(){
	<?php if (empty($this->config['ga.account'])) { ?> rss_add_task('<?php echo JURI::root(); ?>','gaaccount',account,start,end); <?php } ?>
	rss_add_task('<?php echo JURI::root(); ?>','gageneral');
	rss_add_task('<?php echo JURI::root(); ?>','ganewreturning');
	rss_add_task('<?php echo JURI::root(); ?>','gavisits');
	rss_add_task('<?php echo JURI::root(); ?>','gabrowsers');
	rss_add_task('<?php echo JURI::root(); ?>','gamobiles');
	rss_add_task('<?php echo JURI::root(); ?>','gasources');
	rss_add_task('<?php echo JURI::root(); ?>','gacontent');
	});
</script>
<?php } else { ?>
	<div style="text-align:center; font-size:1.5em"><?php echo JText::_('RSSEO_NOACCOUNTS'); ?></div>
<?php } ?>


<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>