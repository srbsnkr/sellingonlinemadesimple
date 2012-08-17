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

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<h1><?php echo $this->params->get('login_form_title'); ?></h1>
<p><?php echo $this->params->get('login_form_text'); ?></p>

<div class="registration<?php echo $this->pageclass_sfx?>">

<div class="jms40 floatleft">

<?php if ($this->type == 'logout') : ?>

<?php if ($this->name != '') {
		echo JText::sprintf('MOD_LOGIN_HINAME', $this->name);
	} else {
		echo JText::sprintf('MOD_LOGIN_HINAME', $this->username);
	} 
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="login-form" >
	<div class="logout-button">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGOUT'); ?>" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $this->url; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<?php else : ?>
<form action="<?php echo JRoute::_('index.php?option=com_jms'); ?>" method="post" id="login-form" >
    <fieldset name="login">
    	<legend><?php echo JText::_('COM_JMS_LOGIN');?></legend>
        <p id="form-login-username">
            <label for="modlgn-username">User Name</label>
            <input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" />
        </p>
        <p id="form-login-password">
            <label for="modlgn-passwd">Password</label>
            <input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18"  />
        </p>
            
        <input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
        
        <ul>
            <li>
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                <?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
            </li>
            <li>
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
                <?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
            </li>
        </ul>
        
        <input type="hidden" name="option" value="com_users" />
        <input type="hidden" name="task" value="user.login" />
        <input type="hidden" name="return" value="<?php echo $this->url; ?>" />
	<?php echo JHtml::_('form.token'); ?>
    </fieldset>
</form>
<?php endif; ?>
</div>

<div class="jms60 floatright">
<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_jms'); ?>" method="post" class="form-validate">
	<fieldset name="default">
    	<legend><?php echo JText::_('COM_JMS_REGISTER');?></legend>
		<dl>
			<dt><?php echo $this->form->getLabel('name'); ?></dt>
			<dd><?php echo $this->form->getInput('name'); ?></dd>
            
            <dt><?php echo $this->form->getLabel('username'); ?></dt>
			<dd><?php echo $this->form->getInput('username'); ?></dd>
            
            <dt><?php echo $this->form->getLabel('email1'); ?></dt>
			<dd><?php echo $this->form->getInput('email1'); ?></dd>
            
            <dt><?php echo $this->form->getLabel('email2'); ?></dt>
			<dd><?php echo $this->form->getInput('email2'); ?></dd>
            
            <dt><?php echo $this->form->getLabel('password1'); ?></dt>
			<dd><?php echo $this->form->getInput('password1'); ?></dd>
            
            <dt><?php echo $this->form->getLabel('password2'); ?></dt>
			<dd><?php echo $this->form->getInput('password2'); ?></dd>
            
		</dl>
        <div class="clr"></div>
		<div>
        	<p><?php echo JText::_('COM_JMS_FIELDS_REQUIRED');?></p>
			<button type="submit" class="validate"><?php echo JText::_('COM_JMS_REGISTER_BUTTON');?></button>
			<?php echo JText::_('COM_JMS_OR');?>
			<a href="<?php echo JRoute::_('');?>" title="<?php echo JText::_('COM_JMS_CANCEL_BUTTON');?>"><?php echo JText::_('COM_JMS_CANCEL_BUTTON');?></a>
			<input type="hidden" name="option" value="com_jms" />
			<input type="hidden" name="task" value="form.register" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</fieldset>
</form>
</div>

<div class="clr"></div>

<?php
	if ($this->params->get('show_available_plans_to_guest')) {
		echo $this->loadTemplate('plans');	
	}	
?>

</div>