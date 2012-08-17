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

$Itemid = JRequest::getInt('Itemid');

if($this->user->get('id')){
	
	$paymentMethodsArr = $this->params->get('payment_method');
	
?>

	<script language="javascript" type="text/javascript">		
		// Method to process changing payment method
		var numberOfInstallments = new Array();
		var subscriptionType = new Array();
		<?php
		foreach ($this->items as $i => $item) {
			?>
			numberOfInstallments[<?php echo $i; ?>] = <?php echo $item->number_of_installments; ?>;
			subscriptionType[<?php echo $i; ?>] = '<?php echo $item->plan_type; ?>';
			<?php
		}
		?>
		function changePlanParameters() {
			var form = document.subscrform;
			var sid = document.getElementsByName('sid');
			for (var i = 0; i < sid.length; i++) {
				if (sid[i].checked == true) {
					break;
				}
			}
			form.r_times.value = numberOfInstallments[i];
			form.subscription_type.value = subscriptionType[i];
			if (form.subscription_type.value == 'I') {
				form.task.value = 'jms.process_subscription';
			} else {
				form.task.value = 'jms.process_recurring_subscription';
			}
		}
		
		function changePaymentMethod() {
			var form = document.subscrform;
			var paymentMethod;
			for (var i = 0; i < form.payment_method.length; i++) {
				if (form.payment_method[i].checked == true) {
					paymentMethod = form.payment_method[i].value ;
					break;
				}
			}						
			var trCardInfo = document.getElementById('card_info');
			if (paymentMethod == 'iwl_authnet') {
				trCardInfo.style.display = '';
			} else {
				trCardInfo.style.display = 'none';
			}
		}
		
		// Method to check number
		function checkNumber(txtName)
		{			
			var num = txtName.value			
			if(isNaN(num)) {			
				alert("<?php echo JText::_('COM_JMS_AUTH_ONLY_NUMBER'); ?>");			
				txtName.value = "";			
				txtName.focus();
			}			
		}
		
		function processSubscription() {
			var form = document.subscrform;
			// Authorize.net validate
			var paymentMethod = "";
			<?php				
			if (count($paymentMethodsArr) > 1) {
				?>
				for (var i = 0 ; i < form.payment_method.length; i++) {
					if (form.payment_method[i].checked == true) {
						paymentMethod = form.payment_method[i].value;
						break;
					}
				}
				<?php
			} else {
				?>
				paymentMethod = form.payment_method.value;
				<?php
			}
			?>
			if (paymentMethod == "iwl_authnet") {
				if (form.x_card_num.value == "") {
					alert('<?php echo  JText::_('COM_JMS_AUTH_ENTER_CARD_NUMBER'); ?>');
					form.x_card_num.focus();
					return;
				}
				if (form.x_exp_date.value == "") {
					alert('<?php echo JText::_('COM_JMS_AUTH_ENTER_EXP_DATE'); ?>');
					form.x_exp_date.focus();
					return ;					
				}
				if (form.x_card_code.value == "") {
					alert('<?php echo JText::_("COM_JMS_AUTH_ENTER_CARD_CODE"); ?>');
					form.x_card_code.focus();
					return ;
				}
			}
			form.submit();		
		}
	</script>
    
<h1><?php echo $this->params->get('subscription_page_title'); ?></h1>

<div class="contentpanopen">

<p><a href="<?php echo JRoute::_("index.php?option=com_jms&view=jms&layout=history&Itemid=$Itemid"); ?>">
<?php echo $this->params->get('history_page_title'); ?></a></p>

<form action="index.php" method="post" name="subscrform" id="subscrform">

<p><?php echo $this->params->get('subscription_page_text'); ?></p>

<?php if (!count($this->items)) { ?>

<p><?php echo JText::_('COM_JMS_LAYOUT_NO_PLANS'); ?></p>

<?php } else { ?>

<table class="category" cellpadding="1" cellspacing="0" border="0" width="80%">
  <tr>
	<th width="1%" align="center"><?php echo JText::_('COM_JMS_RADIO_HEAD'); ?></th>
	<th><?php echo JText::_('COM_JMS_SUBSCRIPTION_NAME_HEAD'); ?></td>
	<th width="20%" align="center"><?php echo JText::_('COM_JMS_PRICE_HEAD'); ?></th>
  </tr>
  
<?php
	foreach ($this->items as $i => $item) :
		if ($i == 0) {
?>
  <tr>
    <td colspan="4">
    	<input type="hidden" name="r_times" id="r_times" value="<?php echo $item->number_of_installments; ?>" />
   	 	<input type="hidden" name="subscription_type" id="subscription_type" value="<?php echo $item->plan_type?>" />
    </td>
  </tr>
<?php } ?>
  <tr class="cat-list-row<?php echo $i % 2; ?>" valign="top">
	<td align="center">
		<input id="sid<?php echo ($i+1); ?>" type="radio" <?php if ($i == 0) echo 'checked'; ?> value="<?php echo $item->id; ?>" name="sid" onclick="changePlanParameters();" />
	</td>	
	<td>
		<strong><label for="sid<?php echo ($i+1); ?>"><?php echo $item->name; ?></label></strong><br />
		<p><?php echo $item->description; ?></p>
	</td>
	<td align="center">
		<?php
			if ($item->discount > 0) {
				$discountedPrice = round(($item->price - ($item->price * ($item->discount / 100))), 2);
				echo JText::_('COM_JMS_SETUP_PRICE') . ' <strong>' . $this->params->get('currency_sign') . $item->price . '</strong><br />';
				echo JText::_('COM_JMS_DISCOUNT_PRICE') . ' <strong>' . $this->params->get('currency_sign') . $discountedPrice . '</strong> ';
			} else {
				echo '<strong>' . $this->params->get('currency_sign') . $item->price . '</strong>';
			}			    								
		?>
	</td>
  </tr>
  
<?php endforeach; ?>

</table>


<p><?php echo JText::_('COM_JMS_LAYOUT_COUPON_TEXT'); ?><br />
<input type="text" class="inputbox" name="coupon" id="coupon" size="40" value="<?php echo JRequest::getVar('coupon'); ?>" /></p>

<table class="jms button-table category" cellpadding="5" cellspacing="5" border="0" width="100%">
<?php	    								
	if (count($paymentMethodsArr) > 1) {
?>
  <tr>
    <td class="title_cell" valign="top" width="25%">
    	<?php echo JText::_('COM_JMS_PAYMENT_METHOD'); ?>
    </td>
    <td>
		<?php
            for ($i = 0 , $n = count($paymentMethodsArr); $i < $n; $i++) {
                $paymentMethod = $paymentMethodsArr[$i];
            if ($paymentMethod == $this->paymentMethod) {
                $checked = 'checked="checked" ';
            } else  {
                $checked = '';	
            }
        ?>
        <input onchange="changePaymentMethod();" type="radio" name="payment_method" value="<?php echo $paymentMethod; ?>" <?php echo $checked; ?> /><?php echo JText::_(strtoupper($paymentMethod)); ?><br />
        
        <?php } ?>
	</td>
  </tr>				
  
		<?php		
                    
            }
            if (($this->paymentMethod == 'iwl_authnet') || ((count($paymentMethodsArr) == 1) && ($paymentMethodsArr[0] == 'iwl_authnet'))) {
                $style = '';						
            } else {						
                $style = 'style="display:none"';
            }
            
        ?>
        
  <tr id="card_info" <?php echo $style; ?>>
	<td>&nbsp;</td>
	<td>
		<table width="100%" cellspacing="3" cellpadding="3" class="subscr_sub_table">								
		  <tr>
			<td class="title_cell">
				<?php echo  JText::_('COM_JMS_AUTH_CARD_NUMBER'); ?><span class="required">*</span>
			</td>
			<td class="field_cell">
				<input type="text" name="x_card_num" class="inputbox" onkeyup="checkNumber(this);" value="<?php echo $this->x_card_num; ?>" size="20" />
			</td>
		  </tr>
		  <tr>
			<td class="title_cell">
				<?php echo JText::_('COM_JMS_AUTH_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
			</td>
				<td class="field_cell">
					<input type="text" name="x_exp_date" class="inputbox" value="<?php echo $this->x_exp_date; ?>" size="20" />&nbsp;&nbsp;(mm/yy)
			</td>
		  </tr>
		  <tr>
			<td class="title_cell">
				<?php echo JText::_('COM_JMS_AUTH_CVV_CODE'); ?><span class="required">*</span>
			</td>
			<td class="field_cell">
				<input type="text" name="x_card_code" class="inputbox" onKeyUp="checkNumber(this);" value="<?php echo $this->x_card_code; ?>" size="20" />
			</td>
		  </tr>
		</table>
	</td>
  </tr>
  <tr>
</table>

<input type="button" class="button" name="btnProcess" id="btnProcess" value="<?php echo JText::_('COM_JMS_PROCESS_SUBSCRIPTION'); ?>" onclick="processSubscription();" />

<?php } ?>
		
<input type="hidden" name="option" value="com_jms" />
<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>" />
<input type="hidden" name="view" value="jms" />
<input type="hidden" value="" name="task" />
	    
<?php if($this->params->get('mc_enable')) { ?>
<!-- mailchimp -->
<input type="hidden" name="mc_api_key" value="<?php echo $this->params->get('mc_api_key'); ?>" />
<input type="hidden" name="mc_listid" value="<?php echo $this->params->get('mc_listid'); ?>" />
<input type="hidden" name="mc_groupid" value="<?php echo $this->params->get('mc_groupid'); ?>" />
<!-- /mailchimp -->
<?php } ?>
		
<?php
	if (count($paymentMethodsArr) == 1) {
?>

<input type="hidden" value="<?php echo $paymentMethodsArr[0]; ?>" name="payment_method" />

<?php } ?>

</form>
	    
<script language="javascript" type="text/javascript">		
		
	var form = document.subscrform;
		
	if (form.subscription_type.value == 'I') {
		form.task.value = 'jms.process_subscription';
	} else {
		form.task.value = 'jms.process_recurring_subscription';
	}

</script>
	 
</div>

<div class="clr"></div>

<?php } ?>