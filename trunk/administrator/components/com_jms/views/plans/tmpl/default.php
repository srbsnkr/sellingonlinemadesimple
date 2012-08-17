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
JHTML::_('script','system/multiselect.js',false,true);
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_jms');
$saveOrder	= $listOrder == 'a.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_jms&view=plans'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('Search'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">

            
                <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                    <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true);?>
                </select>
                

		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
                
                <th class="left" width="8%">
                    <?php echo JText::_('COM_JMS_PLAN_PERIOD_LABEL'); ?>
				</th>

				<th class="left">
                    <?php echo JHtml::_('grid.sort',  'COM_JMS_PLAN_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
                
                <?php if (isset($this->items[0]->ordering)) { ?>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
					<?php if ($canOrder && $saveOrder) :?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'plans.saveorder'); ?>
					<?php endif; ?>
				</th>
                <?php } ?>
                
                <th class="center">
                    <?php echo JHtml::_('grid.sort',  'COM_JMS_PLAN_PRICE_LABEL', 'a.price', $listDirn, $listOrder); ?>
				</th>

                <?php if (isset($this->items[0]->state)) { ?>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>
                <?php } ?>
                
                <?php if (isset($this->items[0]->id)) { ?>
                <th width="5%" class="nowrap">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <?php } ?>
                
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_jms');
			$canEdit	= $user->authorise('core.edit',			'com_jms');
			$canCheckin	= $user->authorise('core.manage',		'com_jms');
			$canChange	= $user->authorise('core.edit.state',	'com_jms');
			$planLink	= JRoute::_('index.php?option=com_jms&task=plan.edit&id=' . $item->id);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
                
                <td class="left">
					<?php
						$periodType = $item->period_type;
						switch ($periodType) {
							case 1:
								echo $item->period . ' ' . JText::_('Days');
								break;
							case 2:
								echo $item->period . ' ' . JText::_('Weeks');
								break;
							case 3:
								echo $item->period . ' ' . JText::_('Months');
								break;
							case 4:
								echo $item->period . ' ' . JText::_('Years');
								break;
							case 5:
								echo JText::_('Unlimited');
								break;
							default:
								echo $item->period . ' ' . JText::_('');
						}
                    ?>
				</td>

				<td class="left">
                	<a href="<?php echo $planLink; ?>" class="hasTip" title="<?php echo JText::_('COM_JMS_PLAN_EDIT_DESC'); ?>::<?php echo $item->name; ?>">
						<?php echo $item->name; ?>
                    </a><br />
                    <?php echo JText::_('COM_JMS_PLAN_YOUR_PLAN'); ?>
                    <span class="jms_greentxt">
						<?php
                            if ($item->article_type) {
                                echo JText::_('Article, ');
                            }
                            if ($item->category_type) {
                                echo JText::_('Category, ');
                            }
                            if ($item->user_type) {
                                echo JText::_('User, ');
                            }
                            if ($item->component_type) {
                                echo JText::_('Component');
                            }
                        ?>
                    </span><br />
					<?php echo JText::_('COM_JMS_PLAN_SUBSCRIBERS'); ?> <span class="jms_greentxt">(<?php echo $item->num_subscrs; ?>)</span><br />
                    <?php echo JText::_('COM_JMS_PLAN_TAG'); ?>
                    <span class="jms_greentxt">
						<?php echo JText::_('{JMS-SUBSCRIPTION ID=' . $item->id . '}'); ?>
                    </span>
				</td>
                
                 <?php if (isset($this->items[0]->ordering)) { ?>
				    <td class="order">
					    <?php if ($canChange) : ?>
						    <?php if ($saveOrder) :?>
							    <?php if ($listDirn == 'asc') : ?>
								    <span><?php echo $this->pagination->orderUpIcon($i, true, 'plans.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'plans.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							    <?php elseif ($listDirn == 'desc') : ?>
								    <span><?php echo $this->pagination->orderUpIcon($i, true, 'plans.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'plans.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							    <?php endif; ?>
						    <?php endif; ?>
						    <?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						    <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
					    <?php else : ?>
						    <?php echo $item->ordering; ?>
					    <?php endif; ?>
				    </td>
                <?php } ?>
                
                <td class="center">
                	<?php echo $item->price;?>
				</td>

                <?php if (isset($this->items[0]->state)) { ?>
				    <td class="center">
					    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'plans.', $canChange, 'cb'); ?>
				    </td>
                <?php } ?>
               
                <?php if (isset($this->items[0]->id)) { ?>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
                <?php } ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>