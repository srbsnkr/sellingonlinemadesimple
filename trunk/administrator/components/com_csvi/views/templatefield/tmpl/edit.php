<?php
/**
 * Template field editing page
 *
 * @package		CSVI
 * @subpackage 	Import
 * @author 		Roland Dalmulder
 * @link 		http://www.csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2012 RolandD Cyber Produksi
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 1924 2012-03-02 11:32:38Z RolandD $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Load some needed behaviors
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="item-form"  id="item-form" class="form-validate">
	<fieldset class="adminform">
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitbutton('templatefield.save');"><?php echo JText::_('JSUBMIT'); ?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="edittemplatefield">
			<?php echo JText::_('COM_CSVI_EDIT_TEMPLATEFIELD') ?>
		</div>
	</fieldset>
	<fieldset class="adminform">
		<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?></li>
			<li><?php echo $this->form->getLabel('field_name'); ?>
			<?php echo $this->form->getInput('field_name'); ?></li>
			<li><?php echo $this->form->getLabel('column_header'); ?>
			<?php echo $this->form->getInput('column_header'); ?></li>
			<li><?php echo $this->form->getLabel('default_value'); ?>
			<?php echo $this->form->getInput('default_value'); ?></li>
			<li><?php echo $this->form->getLabel('process'); ?>
			<?php echo $this->form->getInput('process'); ?></li>
			<li><?php echo $this->form->getLabel('combine'); ?>
			<?php echo $this->form->getInput('combine'); ?></li>
			<li><?php echo $this->form->getLabel('sort'); ?>
			<?php echo $this->form->getInput('sort'); ?></li>
			<li><?php echo $this->form->getLabel('replacement'); ?>
			<?php echo $this->form->getInput('replacement'); ?></li>
		</ul>
	</fieldset>
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>