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
?>
<div id="cpanel">
<div class="cpanel-left">

<h2 class="jms_h2"><?php echo JText::_( 'COM_JMS_COMPONENT_LABEL' ); ?></h2>

<p class="jms_pdesc"><?php echo JText::_( 'COM_JMS_ICO_ABOUT_TIP' ); ?></p>

<div style="float:left;">
	<div class="icon">
        <a href="index.php?option=com_jms&amp;view=plans" style="text-decoration:none;" title="Subscription Plans">
            <img src="components/com_jms/assets/images/icon-48-plan.png" align="middle" border="0"/>
            <span><?php echo JText::_( 'COM_JMS_ICO_PLANS' ); ?></span>
        </a>
	</div>
</div>

<div style="float:left;">
	<div class="icon">
		<a href="index.php?option=com_jms&amp;view=subscrs" style="text-decoration:none;" title="Subscribers">
			<img src="components/com_jms/assets/images/icon-48-subscriber.png" align="middle" border="0"/>
				<span><?php echo JText::_( 'COM_JMS_ICO_SUBSCRIBERS' ); ?></span>
			</a>
	</div>
</div>

<div style="float:left;">
	<div class="icon">
		<a href="index.php?option=com_jms&amp;view=coupons" style="text-decoration:none;" title="Coupons">
			<img src="components/com_jms/assets/images/icon-48-coupon.png" align="middle" border="0"/>
			<span><?php echo JText::_( 'COM_JMS_ICO_COUPONS' ); ?></span>
		</a>
	</div>
</div>

<div style="float:left;">
	<div class="icon">
		<a href="index.php?option=com_jms&amp;view=about" style="text-decoration:none;" title="About">
			<img src="components/com_jms/assets/images/icon-48-about.png" align="middle" border="0"/>
			<span><?php echo JText::_( 'COM_JMS_ICO_ABOUT' ); ?></span>
		</a>
	</div>
</div>

</div>

<div class="cpanel-right">
<img src="components/com_jms/assets/images/jms-520.jpg" align="middle" border="0"/>
</div>

<div class="clr"></div>

</div>
