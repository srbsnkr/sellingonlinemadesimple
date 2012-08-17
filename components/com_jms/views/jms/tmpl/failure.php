<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php $Itemid = JRequest::getInt('Itemid'); ?>

<h1><?php echo JText::_('COM_JMS_PURCHASE_SUBSCRIPTION_FAILURE'); ?></h1>

<div class="contentpanopen">

<p><?php echo  JText::_('COM_JMS_FAILURE_MESSAGE'); ?></p>

<p>
	<?php echo JText::_('COM_JMS_FAILURE_REASON'); ?>:<br />
	<?php echo $this->reason; ?>
</p>			

<a href="<?php echo JRoute::_("index.php?option=com_jms&view=jms&Itemid=" . $Itemid); ?>">
<?php echo JText::_('COM_JMS_PURCHASE_NEW_SUBSCRIPTION')?></a>


</div>	