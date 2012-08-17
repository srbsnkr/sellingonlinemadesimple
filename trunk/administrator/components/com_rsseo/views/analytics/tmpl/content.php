<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<?php if (is_array($this->content)) { ?>
	<fieldset>
		<legend><?php echo JText::_('RSSEO_GA_CONTENT'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('RSSEO_GA_CONTENT_PAGE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_CONTENT_PAGEVISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_CONTENT_UNIQUEPAGEVISITS'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_CONTENT_AVGTIME'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_CONTENT_BOUNCERATE'); ?></th>
					<th><?php echo JText::_('RSSEO_GA_CONTENT_EXITS'); ?></th>
				</tr>
			</thead>
		<?php 
			if (!empty($this->content))
			{
				$k=0;
				foreach ($this->content as $result)
				{
					echo '<tr class="row'.$k.'">';
					echo '<td>'.$result->page.'</td>';
					echo '<td align="center">'.$result->pageviews.'</td>';
					echo '<td align="center">'.$result->upageviews.'</td>';
					echo '<td align="center">'.$result->avgtimesite.'</td>';
					echo '<td align="center">'.$result->bouncerate.'</td>';
					echo '<td align="center">'.$result->exits.'</td>';
					echo '</tr>';
					$k = 1-$k;
				}
			}
		?>
		</table>
	</fieldset>
<?php } else echo $this->content; ?>