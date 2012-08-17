<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<?php if (is_array($this->visits)) { ?>
	<fieldset>
		<legend><?php echo JText::_('RSSEO_GA_VISITSPERDAY'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="15%"><?php echo JText::_('RSSEO_GA_VPD_DATE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_VPD_VISITS'); ?></th>
				</tr>
			</thead>
		<?php 
			if (!empty($this->visits))
			{
				$k=0;
				foreach ($this->visits as $date => $result)
				{
					echo '<tr class="row'.$k.'">';
					echo '<td>'.date('l, F d, Y',$date).'</td>';
					echo '<td><div class="rss_graph" style="width: '.str_replace(' ','',$result->visitspercent).'"></div>'.$result->visitspercent.' ('.$result->visits.')</td>';
					echo '</tr>';
					$k = 1-$k;
				}
			}
		?>
		</table>
	</fieldset>
<?php } else echo $this->visits; ?>