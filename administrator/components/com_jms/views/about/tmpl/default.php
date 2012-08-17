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

<h2 class="jms_h2"><?php echo JText::_( 'COM_JMS_TITLE_ABOUT' ); ?> <?php echo JText::_( 'COM_JMS' ); ?></h2>
<p class="jms_pdesc"><?php echo JText::_( 'COM_JMS_COMPONENT_DESC' ); ?></p>

<ul class="adminaboutlist">
            
    <li>
        <label><?php echo JText::_( 'COM_JMS_ABT_VERSION' ); ?>:</label>
        <?php echo JText::_( '2.0' ); ?>
    </li>
    
    <li>
        <label><?php echo JText::_( 'COM_JMS_ABT_AUTHOR' ); ?>:</label>
        <?php echo JText::_( 'Infoweblink' ); ?>
    </li>
    
    <li>
		<label><?php echo JText::_( 'COM_JMS_ABT_SUPPORTEMAIL' ); ?>:</label>
		<a href="mailto: <?php echo JText::_( 'support@joomlamadesimple.com' ); ?>">
			<?php echo JText::_( 'support@joomlamadesimple.com' ); ?>
		</a>	
    </li>
    
    <li>
		<label><?php echo JText::_( 'COM_JMS_ABT_HOMEPAGE' ); ?>:</label>
		<a href="<?php echo JText::_( 'http://joomlamadesimple.com/' ); ?>" target="_blank">
			<?php echo JText::_( 'http://joomlamadesimple.com/' ); ?>
		</a>	
    </li>
    
    <li>
		<label><?php echo JText::_( 'COM_JMS_ABT_COPYRIGHT' ); ?>:</label>
		<?php echo JText::_( 'Copyright &copy; 2011 Infoweblink' ); ?>
    </li>
                    
</ul>		

</div>

<div class="cpanel-right">
<img src="components/com_jms/assets/images/jms-520.jpg" alt="<?php echo JText::_( 'COM_JMS' ); ?>" align="middle" border="0"/>
</div>

<div class="clr"></div>

</div>

			
