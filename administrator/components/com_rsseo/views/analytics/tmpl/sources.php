<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<?php if (is_array($this->sources['data'])) { ?>
	<fieldset>
		<legend><?php echo JText::_('RSSEO_GA_SOURCES'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('RSSEO_GA_SOURCES_SOURCE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_SOURCES_VISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_SOURCES_NEWVISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_SOURCES_PAGEVISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_SOURCES_BOUNCERATE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_SOURCES_AVGTIME'); ?></th>
				</tr>
			</thead>
		<?php 
			if (!empty($this->sources['data']))
			{
				$k=0;
				foreach ($this->sources['data'] as $result)
				{
					echo '<tr class="row'.$k.'">';
					echo '<td>'.$result->source.'</td>';
					echo '<td align="center">'.$result->visits.'</td>';
					echo '<td align="center">'.$result->newvisits.'</td>';
					echo '<td align="center">'.$result->pagesvisits.'</td>';
					echo '<td align="center">'.$result->bouncerate.'</td>';
					echo '<td align="center">'.$result->avgtimesite.'</td>';
					echo '</tr>';
					$k = 1-$k;
				}
			}
		?>
		</table>
	</fieldset>
<?php } else echo $this->sources['data']; ?>