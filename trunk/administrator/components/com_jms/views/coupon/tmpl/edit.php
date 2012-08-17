<?php
/**
 * @version     2.0.2
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @authorEmail	support@infoweblink.com 
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011. Infoweblink. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This component manages Subscriptions for members to access to Joomla Resource
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{	
		var form = document.adminForm;
		if (task != 'coupon.cancel' && form.jform_code.value == ""){
			alert( "<?php echo JText::_( 'Coupon item must have a code', true ); ?>" );
			form.jform_code.focus();
		} else if (task != 'coupon.cancel' && form.jform_recurring.checked == 1 && form.jform_num_recurring.value <= 0) {
			alert( "<?php echo JText::_( 'Please enter Number Recurring', true ); ?>" );
			form.jform_num_recurring.focus();
		} else if (task == 'coupon.cancel' || document.formvalidator.isValid(document.id('coupon-form'))) {
			Joomla.submitform(task, document.getElementById('coupon-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		} 
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jms&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="coupon-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_JMS_LEGEND_COUPON'); ?></legend>
			<ul class="adminformlist">

                <li><?php echo $this->form->getLabel('code'); ?>
                <?php echo $this->form->getInput('code'); ?></li> 
                
                <li><?php echo $this->form->getLabel('discount'); ?>
                <?php echo $this->form->getInput('discount'); ?>
                <?php echo $this->form->getInput('discount_type'); ?></li>  
                
                <li><?php echo $this->form->getLabel('recurring'); ?>
                <?php echo $this->form->getInput('recurring'); ?></li>       
                
                <li><?php echo $this->form->getLabel('num_recurring'); ?>
                <?php echo $this->form->getInput('num_recurring'); ?></li>     
                
                <li><?php echo $this->form->getLabel('user_ids'); ?>
                <?php echo $this->form->getInput('user_ids'); ?></li>
                
                <li><?php echo $this->form->getLabel('plan_ids'); ?>
                <?php echo $this->form->getInput('plan_ids'); ?></li>
                
                <li><?php echo $this->form->getLabel('limit_time'); ?>
                <?php echo $this->form->getInput('limit_time'); ?></li>
                
                <li><?php echo $this->form->getLabel('limit_time_user'); ?>
                <?php echo $this->form->getInput('limit_time_user'); ?></li>
                
                <li><?php echo $this->form->getLabel('created'); ?>
                <?php echo $this->form->getInput('created'); ?></li>
                
                <li><?php echo $this->form->getLabel('expired'); ?>
                <?php echo $this->form->getInput('expired'); ?></li>

                <li><?php echo $this->form->getLabel('state'); ?>
                <?php echo $this->form->getInput('state'); ?></li>
                
                <li><?php echo $this->form->getLabel('checked_out'); ?>
                <?php echo $this->form->getInput('checked_out'); ?></li>
                
                <li><?php echo $this->form->getLabel('checked_out_time'); ?>
                <?php echo $this->form->getInput('checked_out_time'); ?></li>
                
                <li><?php echo $this->form->getLabel('id'); ?>
                <?php echo $this->form->getInput('id'); ?></li>

            </ul>
		</fieldset>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>