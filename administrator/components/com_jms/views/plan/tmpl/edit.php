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
	function jmsShowComParams( cparam, com ) {
		cbox = document.getElementById("check_" + cparam).checked;
		if( cbox ) {
		document.getElementById("params_" + cparam).style.visibility='visible';
		document.getElementById("params_" + cparam).style.display='block';		
		var out =
			'<div class="modal-pad">' +
			'<div class="jmsblockshow-pad modal-pad">' +
			'<table class="admintable" cellpadding="0" cellspacing="0"><tr>' +
			'<td nowrap class="key" width="100" align="right">URL Variable 1:</td>' +
			'<td><input value="" type="text" name="task_' + com + '1" class="inputbox" size="30" /></td></tr>' +
			'<tr><td  nowrap class="key" width="100" align="right">Variable Value 1:</td>' +
			'<td><input value="" type="text" name="value_' + com + '1" class="inputbox" size="30" /></td></tr>' +
			'</table>' +
			'<strong>AND</strong>' +
			'<table class="admintable" cellpadding="0" cellspacing="0"><tr>' +
			'<td nowrap class="key" width="100" align="right">URL Variable 2:</td>' +
			'<td><input value="" type="text" name="task_' + com + '2" class="inputbox" size="30" /></td></tr>' +
			'<tr><td nowrap class="key" width="100" align="right">Variable Value 2:</td>' +
			'<td><input value="" type="text" name="value_' + com + '2" class="inputbox" size="30" /></td></tr></table>' +
			'</div>' +
			'<strong>OR</strong>' +
			'<div class="jmsblockshow-pad">' +
			'<table class="admintable" cellpadding="0" cellspacing="0" ><tr>' +
			'<td nowrap class="key" width="100" align="right">URL Variable 1:</td>' +
			'<td><input value="" type="text" name="task_' + com + '3" class="inputbox" size="30" /></td></tr>' +
			'<tr><td  nowrap class="key" width="100" align="right">Variable Value 1:</td>' +
			'<td><input value="" type="text" name="value_'+ com + '3" class="inputbox" size="30" /></td></tr>' +
			'</table>' +
			'<strong>AND</strong>' +
			'<table class="admintable" cellpadding="0" cellspacing="0" ><tr>' +
			'<td  nowrap class="key" width="100" align="right">URL Variable 2:</td>' +
			'<td><input value="" type="text" name="task_' + com + '4" class="inputbox" size="30" /></td></tr>' +
			'<tr><td nowrap class="key" width="100" align="right">Variable Value 2:</td>' +
			'<td><input value="" type="text" name="value_'+ com +'4" class="inputbox" size="30" /></td></tr></table>' +
			'</td>' +
			'</div>' +
			'</div>'
			;
			document.getElementById("params_" + cparam).innerHTML = out;
		} else {
			document.getElementById("params_" + cparam).style.visibility='hidden';
			document.getElementById("params_" + cparam).style.display='none';
			document.getElementById("params_" + cparam).innerHTML = '';
		}
	}

	Joomla.submitbutton = function(task) {
		// Do field validation
		var form = document.adminForm;
		if (task != 'plan.cancel' && form.jform_name.value == ""){
			alert( "<?php echo JText::_( 'Plan item must have a name', true ); ?>" );
			form.jform_name.focus();
		} else if (task != 'plan.cancel' && form.jform_plan_type.value == 'R' && form.jform_period_type.value == '5') {
			alert("You can't choose an unlimited period for a recurring plan");
		} else if (task != 'plan.cancel' && form.jform_period_type.value < 5 && (form.jform_period.value == "" || form.jform_period.value == 0)) {
			alert( "<?php echo JText::_( 'Plan item must have a period', true ); ?>" );
			form.jform_period.focus();
		} else if (task != 'plan.cancel' && form.jform_price.value == "") {
			alert( "<?php echo JText::_( 'Plan item must have a price', true ); ?>" );
			form.jform_price.focus();
		} else if (task != 'plan.cancel' && form.jform_period_type.value < 5 && parseInt(form.jform_number_of_installments.value) <= 1 && form.jform_plan_type.value == 'R') {
			alert( "<?php echo JText::_( 'Number of installements must be bigger than 1', true ); ?>" );
			form.jform_number_of_installments.focus();
		} else if (task == 'plan.cancel' || document.formvalidator.isValid(document.id('plan-form'))) {
			Joomla.submitform(task, document.getElementById('plan-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
	
	function applyUnlimitedPeriod() {
		var form = document.adminForm;
		if (form.jform_period_type.value == 5) {
			form.jform_period.disabled = true;
			form.jform_number_of_installments.disabled = true;
		} else {
			form.jform_period.disabled = false;
			form.jform_number_of_installments.disabled = false;
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jms&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="plan-form" class="form-validate">
	<div class="width-100">
    <?php echo JHtml::_('tabs.start','com_jms_tabs', array('useCookie'=>1)); ?>	
    <?php echo JHtml::_('tabs.panel', JText::_('COM_JMS_LEGEND_GEN_SETTINGS'),'tab1');?> 
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_JMS_LEGEND_GEN_SETTINGS'); ?></legend>
			<ul class="adminformlist">
            
            	<li><?php echo $this->form->getLabel('name'); ?>
                <?php echo $this->form->getInput('name'); ?></li>
                
                <li><?php echo $this->form->getLabel('plan_type'); ?>
                <?php echo $this->form->getInput('plan_type'); ?></li>
                
                <li><?php echo $this->form->getLabel('period'); ?>
                <?php echo $this->form->getInput('period'); ?>
                <?php echo $this->form->getInput('period_type'); ?></li>
                
                <li><?php echo $this->form->getLabel('number_of_installments'); ?>
                <?php echo $this->form->getInput('number_of_installments'); ?></li>
                
                <li><?php echo $this->form->getLabel('limit_time'); ?>
                <?php echo $this->form->getInput('limit_time'); ?></li>
                
                <li><?php echo $this->form->getLabel('price'); ?>
                <?php echo $this->form->getInput('price'); ?></li>
                
                <li><?php echo $this->form->getLabel('discount'); ?>
                <?php echo $this->form->getInput('discount'); ?></li>
                
                <li><?php echo $this->form->getLabel('state'); ?>
                <?php echo $this->form->getInput('state'); ?></li>
                
                <li><?php echo $this->form->getLabel('order'); ?>
                <?php echo $this->form->getInput('order'); ?></li>
                           
            </ul>
            <div>
            	<div class="clr"></div>
				<?php echo $this->form->getLabel('description'); ?>
                <div class="clr"></div>
                <?php echo $this->form->getInput('description'); ?>
            </div>
    </fieldset>
    
    <?php echo JHtml::_('tabs.panel', JText::_('COM_JMS_LEGEND_GEN_RESTRICTIONS'),'tab2');?> 
    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_JMS_LEGEND_GEN_RESTRICTIONS'); ?></legend>
        	<ul class="adminformlist">
                
            	<li><?php echo $this->form->getLabel('articles'); ?>
                <?php echo $this->form->getInput('articles'); ?></li>
                
                <li><?php echo $this->form->getLabel('user_type'); ?>
                <?php echo $this->form->getInput('user_type'); ?></li>
                
                <li><?php echo $this->form->getLabel('categories'); ?>
                <?php echo $this->form->getInput('categories'); ?></li>
            
            </ul>
	</fieldset>
    
    <?php echo JHtml::_('tabs.panel', JText::_('COM_JMS_LEGEND_COM_RESTRICTIONS'),'tab3');?> 
    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_JMS_LEGEND_COM_RESTRICTIONS'); ?></legend>
        
        	<div><?php echo $this->form->getLabel('components'); ?></div>
        	<div><?php echo $this->form->getInput('components'); ?></div>
            
	</fieldset>
    
    <?php echo JHtml::_('tabs.panel', JText::_('COM_JMS_LEGEND_PARAMS'),'tab4');?> 
    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_JMS_LEGEND_PARAMS'); ?></legend>
        	<ul class="adminformlist">
            
				<li><?php echo $this->form->getLabel('grant_new_user'); ?>
                <?php echo $this->form->getInput('grant_new_user'); ?></li>
                
                <li><?php echo $this->form->getLabel('grant_old_user'); ?>
                <?php echo $this->form->getInput('grant_old_user'); ?></li>
                
            </ul>
            <div>
            	<div class="clr"><br /></div>
            	<?php echo $this->form->getLabel('completed_msg'); ?>
                <div class="clr"></div>
                <?php echo $this->form->getInput('completed_msg'); ?>
                <div class="clr"><br /></div>
                <?php echo $this->form->getLabel('cancel_msg'); ?>
                <div class="clr"></div>
                <?php echo $this->form->getInput('cancel_msg'); ?>
            	<div class="clr"></div>
            </div>
	</fieldset>
    
    <?php echo JHtml::_('tabs.panel', JText::_('COM_JMS_LEGEND_AUTORES'),'tab4');?> 
    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_JMS_LEGEND_AUTORES'); ?></legend>
        	<ul class="adminformlist">
            
				<li><?php echo $this->form->getLabel('autores_enable'); ?>
                <?php echo $this->form->getInput('autores_enable'); ?></li>
                
                <li><?php echo $this->form->getLabel('autores_url'); ?>
                <?php echo $this->form->getInput('autores_url'); ?></li>
                
                <li><?php echo $this->form->getLabel('autores_redirect'); ?>
                <?php echo $this->form->getInput('autores_redirect'); ?></li>
                
                <li><?php echo $this->form->getLabel('autores_list'); ?>
                <?php echo $this->form->getInput('autores_list'); ?></li>
                
                <li><div class="clr"></div><hr /></li>
                
                <li><?php echo $this->form->getLabel('crm_enable'); ?>
                <?php echo $this->form->getInput('crm_enable'); ?></li>
                
                <li><?php echo $this->form->getLabel('crm_url'); ?>
                <?php echo $this->form->getInput('crm_url'); ?></li>
                
                <li><?php echo $this->form->getLabel('inf_form_xid'); ?>
                <?php echo $this->form->getInput('inf_form_xid'); ?></li>
                
                <li><?php echo $this->form->getLabel('inf_form_name'); ?>
                <?php echo $this->form->getInput('inf_form_name'); ?></li>
                
                <li><?php echo $this->form->getLabel('infusionsoft_version'); ?>
                <?php echo $this->form->getInput('infusionsoft_version'); ?></li>
                
                <li><div class="clr"></div><hr /></li>
                
                <li><?php echo $this->form->getLabel('plan_mc_enable'); ?>
                <?php echo $this->form->getInput('plan_mc_enable'); ?></li>
                
                <li><?php echo $this->form->getLabel('plan_mc_api'); ?>
                <?php echo $this->form->getInput('plan_mc_api'); ?></li>
                
                <li><?php echo $this->form->getLabel('plan_mc_listid'); ?>
                <?php echo $this->form->getInput('plan_mc_listid'); ?></li>
                
                <li><?php echo $this->form->getLabel('plan_mc_groupid'); ?>
                <?php echo $this->form->getInput('plan_mc_groupid'); ?></li>
                
                <li><?php echo $this->form->getLabel('checked_out'); ?>
                <?php echo $this->form->getInput('checked_out'); ?></li>
                
                <li><?php echo $this->form->getLabel('checked_out_time'); ?>
                <?php echo $this->form->getInput('checked_out_time'); ?></li>
                
                <li><?php echo $this->form->getLabel('id'); ?>
                <?php echo $this->form->getInput('id'); ?></li>

            </ul>
	</fieldset>
    
    <?php echo JHtml::_('tabs.end');?>
    
	</div>

	<input type="hidden" name="return" value="" />
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="cid[]" value="<?php echo JRequest::getVar('id'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>