<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php $Itemid = JRequest::getInt('Itemid'); ?>

<h1><?php echo JText::_('COM_JMS_PURCHASE_SUBSCRIPTION_COMPLETION'); ?></h1>

<div class="contentpanopen">

	<p>

		<?php

			if ($this->plan->completed_msg) {
				echo $this->plan->completed_msg;
			} else {
				echo $this->params->get('completed_msg');
			}

		?>

	</p>

	<a href="<?php echo JRoute::_("index.php?option=com_jms&view=jms&Itemid=" . $Itemid); ?>">
		<?php echo $this->params->get('subscription_page_title'); ?>
	</a>

</div>	