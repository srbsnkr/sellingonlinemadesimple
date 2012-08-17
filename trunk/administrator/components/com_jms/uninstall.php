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

defined('_JEXEC') or die('Restricted access');

// Imports
jimport('joomla.installer.installer');

$db = &JFactory::getDBO();

$db->setQuery("DROP TABLE IF EXISTS `#__jms_coupons`");
$db->query();

$db->setQuery("DROP TABLE IF EXISTS `#__jms_coupon_subscrs`");
$db->query();

$db->setQuery("DROP TABLE IF EXISTS `#__jms_plans`");
$db->query();

$db->setQuery("DROP TABLE IF EXISTS `#__jms_plan_subscrs`");
$db->query();

$db->setQuery("DROP TABLE IF EXISTS `#__jvmauth`");
$db->query();

//$db->setQuery("RENAME TABLE `#__jms_plan_subscrs` TO `#__jms_plan_subscrs_backup`");
//$db->query();

$db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'plugin' AND element = 'jmscontent' AND folder = 'content' LIMIT 1");
$id = $db->loadResult();
if ($id) {
	$installer = new JInstaller();
	$installer->uninstall('plugin', $id);
}

$db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'plugin' AND element = 'jmsloadsubscription' AND folder = 'content' LIMIT 1");
$id = $db->loadResult();
if ($id) {
	$installer = new JInstaller();
	$installer->uninstall('plugin', $id);
}

$db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'plugin' AND element = 'jmscomponent' AND folder = 'system' LIMIT 1");
$id = $db->loadResult();
if ($id) {
	$installer = new JInstaller();
	$installer->uninstall('plugin', $id);
}

$db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'plugin' AND element = 'jmsgrant' AND folder = 'user' LIMIT 1");
$id = $db->loadResult();
if ($id) {
	$installer = new JInstaller();
	$installer->uninstall('plugin', $id);
}

?>

<h2>Joomla Membership Sites Removal</h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
			<th width="30%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<th colspan="3"><?php echo JText::_('Core'); ?></th>
		</tr>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'Joomla Membership Sites '.JText::_('Component'); ?></td>
			<td><strong><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
		<tr>
			<th colspan="3"><?php echo JText::_('Plugins'); ?></th>
		</tr>
		<tr class="row1">
			<td class="key" colspan="2"><?php echo 'Content - Joomla Membership Sites - Content Restrictions'; ?></td>
			<td><strong><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
        <tr class="row0">
			<td class="key" colspan="2"><?php echo 'Content - Joomla Membership Sites - Load Subscription'; ?></td>
			<td><strong><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
		<tr class="row1">
			<td class="key" colspan="2"><?php echo 'System - Joomla Membership Sites - Component Restrictions'; ?></td>
			<td><strong><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
         <tr class="row0">
			<td class="key" colspan="2"><?php echo 'User - Joomla Membership Sites - Grant User'; ?></td>
			<td><strong><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
	</tbody>
</table>