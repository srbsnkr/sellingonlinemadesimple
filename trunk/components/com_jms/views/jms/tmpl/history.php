<?php

defined('_JEXEC') or die();

$Itemid = JRequest::getInt('Itemid');

?>

<h1><?php echo $this->params->get('history_page_title'); ?></h1>

<div class="contentpanopen">

<a href="<?php echo JRoute::_("index.php?option=com_jms&view=jms&Itemid=" . $Itemid); ?>">
<?php echo $this->params->get('subscription_page_title'); ?></a>

<p><?php echo $this->params->get('history_page_text'); ?></p>

<?php if (!count($this->items)) { ?>
    
<p><?php echo JText::_('COM_JMS_THERE_IS_NO_SUBSCIPTION_PURCHASED'); ?></p>

<?php } else { ?>

<table class="category" cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr>
	<th width="1%" align="center"><?php echo JText::_('COM_JMS_ORDER_HEAD'); ?></th>
	<th><?php echo JText::_('COM_JMS_SUBSCRIPTION_HEAD'); ?></td>
	<th width="1%" align="center"><?php echo JText::_('COM_JMS_ACTIVE_HEAD'); ?></th>
	<th nowrap width="1%" align="center"><?php echo JText::_('COM_JMS_TIME_LEFT_HEAD'); ?></th>
	<th nowrap width="1%" align="center"><?php echo JText::_('COM_JMS_LIMIT_HEAD'); ?></th>
	<th nowrap width="1%" align="center"><?php echo JText::_('COM_JMS_USED_HEAD'); ?></th>
	<th width="1%" align="center"><?php echo JText::_('COM_JMS_PRICE_HEAD'); ?></th>
  </tr>

<?php

$k = 0;
for ( $i = 0; $n = count($this->items), $i < $n; $i++ ) {
	$item = $this->items[$i];							

	// Check if this current subscription is active
	$active = true;
	if ( !$item->state ||  $item->days_left <= 0 ) {
		$active = false;
	}

	if ( $active ) {
		$imgActive = 'active.png';
	} else {
		$imgActive = 'block.png';
	}
	
?>

  <tr class="sectiontableentry<?php echo ($k + 1) ;?>">
    <td align="center"><?php echo ($i+1); ?></td>
    <td>

		<?php echo $item->pname; ?><br />
        
        <span>
			<?php echo JText::_('COM_JMS_ORDER_ID'); ?>:
            <strong><?php echo $item->transaction_id; ?></strong> |
            
            <?php echo JText::_('COM_JMS_START_ON'); ?>:
            <strong><?php echo JHTML::date($item->created, 'd-m-Y'); ?></strong> |
            
            <?php echo JText::_('COM_JMS_FINISH_ON'); ?>: 
            <strong>
                <?php
                
                if ( $item->days_left <= 0 ) {
                echo '<span class="jms-red">' . JHTML::date($item->expired, 'd-m-Y') . '</span>';	
                } else {
                echo JHTML::date($item->expired, 'd-m-Y');
                }
                
                ?>
            </strong>
        </span>											

    </td>

    <td align="center">
    	<img src="<?php echo JURI::base(); ?>components/com_jms/assets/images/<?php echo $imgActive; ?>" />
    </td>

    <td align="center" nowrap>
    
    <?php
    
		if ($item->expired == '3009-12-31 23:59:59') {
			echo '<strong><span class="jms-green">' . JText::_('COM_JMS_UNLIMITED') . '</span></strong>';
		} else {
			echo '<strong><span class="jms-green">' . $item->days_left . '</span></strong>' . JText::_(' Days');	
		}										
    
    ?>
    
    </td>
    <td align="center" nowrap>

		<?php
        
			if ( $item->access_limit == 0 ) {
				echo '<strong><span class="jms-green">' . JText::_('COM_JMS_NO_LIMITS') . '</span></strong>';
			} else {
				echo $item->access_limit;	
			}
                                        
        ?>

    </td>					
    <td align="center">

		<?php										
        
			if ( $item->access_limit > 0 && ($item->access_count >= $item->access_limit) ) {
				echo '<strong><span class="jms-red">' . $item->access_count . '</span></strong>';
			} else {
				echo $item->access_count;	
			}		
                                        
        ?>
        
    </td>
    <td align="center"><?php echo $this->params->get('currency_sign') . $item->price; ?></td>
  </tr>

<?php

	$k = 1 - $k;	
	}

?>

</table>	

<?php } ?>	

</div>

<div class="clr" style="clear: both"></div>
