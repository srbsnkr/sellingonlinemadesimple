<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<?php if (is_array($this->newreturning)) { ?>
	<fieldset>
		<legend><?php echo JText::_('RSSEO_GA_NEWVSRETURNING'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('RSSEO_GA_NVSR_VISITRORS_TYPE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_NVSR_VISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_NVSR_PAGEVISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_NVSR_BOUNCERATE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_NVSR_AVGTIME'); ?></th>
				</tr>
			</thead>
		<?php 
			if (!empty($this->newreturning))
			{
				$k=0;
				foreach ($this->newreturning as $type => $result)
				{
					echo '<tr class="row'.$k.'">';
					echo '<td>'.$type.'</td>';
					echo '<td align="center">'.$result->visits.'</td>';
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
<?php } else echo $this->newreturning; ?>