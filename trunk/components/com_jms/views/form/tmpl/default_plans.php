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

// No direct access
defined('_JEXEC') or die;
?>

<h1><?php echo $this->params->get('subscription_page_title'); ?></h1>

<div class="contentpanopen">

<p><?php echo $this->params->get('subscription_page_text'); ?></p>

<?php

	if (!count($this->plans)) {

?>

<p><?php echo JText::_('COM_JMS_LAYOUT_NO_PLANS'); ?></p>

<?php } else {	?>

<table class="category" cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr>
	<th><?php echo JText::_('COM_JMS_SUBSCRIPTION_NAME_HEAD'); ?></th>
	<th width="20%" align="center"><?php echo JText::_('COM_JMS_PRICE_HEAD'); ?></th>
  </tr>
  
<?php foreach($this->plans as $i => $plan) : ?>

  <tr class="cat-list-row<?php echo $i % 2; ?>" valign="top">	    					
	<td>
		<strong><?php echo $plan->name; ?></strong><br />
		<p><?php echo $plan->description; ?></p>
	</td>
    
	<td align="center">
    
<?php

	if ($plan->discount > 0) {
		$discountedPrice = round(($plan->price - ($plan->price * ($plan->discount / 100))), 2);
		echo JText::_('COM_JMS_SETUP_PRICE') . ' <strong>' . $this->params->get('currency_sign') . $plan->price . '</strong><br />';
		echo JText::_('COM_JMS_DISCOUNT_PRICE') . ' <strong>' . $this->params->get('currency_sign') . $discountedPrice . '</strong> ';
	} else {
		echo '<strong>' . $this->params->get('currency_sign') . $plan->price . '</strong>';
	}
	
?>

	</td>
  </tr>

<?php endforeach; ?>

</table>

<?php } ?>
    
</div>
<div class="clr" style="clear:both"></div>