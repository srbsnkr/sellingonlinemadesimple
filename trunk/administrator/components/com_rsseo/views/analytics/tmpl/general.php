<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<?php if (is_array($this->general)) { ?> 
<fieldset>
	<legend><?php echo JText::_('RSSEO_GA_GENERAL'); ?></legend>
	<table class="admintable" style="width: 65%">
	<?php 
		if (!empty($this->general))
		foreach ($this->general as $result)
		{
			echo '<tr class="hasTip" title="'.$result->descr.'">';
			echo '<td style="text-align:right;">'.$result->title.'</td>';
			echo '<td class="key" style="text-align:left;">'.$result->value.'</td>';
			echo '</tr>';
		}
	?>
	</table>
</fieldset>
<?php } else echo $this->general; ?>