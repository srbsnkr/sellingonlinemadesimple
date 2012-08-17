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

$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jms_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  `discount` double(12,2) DEFAULT NULL,
  `discount_type` tinyint(1) unsigned DEFAULT NULL,
  `recurring` tinyint(1) unsigned DEFAULT NULL,
  `num_recurring` int(11) DEFAULT NULL,
  `strict` tinyint(1) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `expired` datetime DEFAULT NULL,
  `user_ids` text,
  `plan_ids` text,
  `limit_time` int(11) DEFAULT NULL,
  `limit_time_user` int(11) DEFAULT NULL,
  `used_time` int(11) DEFAULT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
$db->query();

$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jms_coupon_subscrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `price` float(10,2) DEFAULT NULL,
  `discount` float(10,2) DEFAULT NULL,
  `discount_type` tinyint(1) unsigned DEFAULT NULL,
  `recurring` tinyint(1) unsigned DEFAULT NULL,
  `num_recurring` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
$db->query();

$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jms_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `catid` int(11) NOT NULL DEFAULT '0',
  `plan_type` varchar(50) NOT NULL,
  `period` int(11) DEFAULT NULL,
  `period_type` tinyint(1) unsigned DEFAULT NULL,
  `number_of_installments` int(11) NOT NULL,
  `limit_time` int(11) DEFAULT NULL,
  `limit_time_type` tinyint(1) unsigned DEFAULT NULL,
  `one_time` tinyint(1) unsigned DEFAULT NULL,
  `price` float(10,2) DEFAULT NULL,
  `discount` float(10,2) DEFAULT NULL,
  `state` tinyint(1) unsigned DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `description` mediumtext,
  `completed_msg` mediumtext,
  `cancel_msg` mediumtext,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `category_type` tinyint(1) unsigned DEFAULT NULL,
  `categories` text,
  `component_type` tinyint(1) unsigned DEFAULT NULL,
  `components` text,
  `user_type` tinyint(1) unsigned DEFAULT NULL,
  `article_type` tinyint(1) unsigned DEFAULT NULL,
  `articles` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `params` text,
  `date_strict` tinyint(3) unsigned DEFAULT NULL,
  `grant_new_user` tinyint(1) unsigned DEFAULT NULL,
  `grant_old_user` tinyint(1) unsigned DEFAULT NULL,
  `grant_url` text,
  `grant_plans` text NOT NULL,
  `alert_admin` tinyint(1) unsigned DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `adwords` text,
  `content_category` text,
  `cross_plans` text,
  `autores_enable` tinyint(1) NOT NULL,
  `autores_url` text NOT NULL,
  `autores_redirect` text NOT NULL,
  `autores_list` varchar(255) NOT NULL,
  `crm_enable` tinyint(1) NOT NULL,
  `crm_url` text NOT NULL,
  `inf_form_xid` text NOT NULL,
  `inf_form_name` text NOT NULL,
  `infusionsoft_version` text NOT NULL,
  `plan_mc_enable` tinyint(1) NOT NULL,
  `plan_mc_api` text NOT NULL,
  `plan_mc_listid` text NOT NULL,
  `plan_mc_groupid` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
$db->query();

$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jms_plan_subscrs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `expired` datetime DEFAULT NULL,
  `price` float(10,2) DEFAULT NULL,
  `number` varchar(200) DEFAULT NULL,
  `access_count` int(11) DEFAULT NULL,
  `access_limit` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'undefined',
  `transaction_id` varchar(255) DEFAULT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `parent` tinyint(1) unsigned DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `subscription_type` varchar(50) NOT NULL,
  `r_times` int(11) NOT NULL,
  `payment_made` int(11) NOT NULL,
  `subscr_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
$db->query();

$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jvmauth` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fname` varchar(50) NOT NULL DEFAULT '',
  `lname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `passwd` varchar(25) NOT NULL DEFAULT '',
  `pid` varchar(20) NOT NULL DEFAULT '',
  `debug` text NOT NULL COMMENT 'for debugging',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`pid`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
$db->query();

/***********************************************************************************************
* ---------------------------------------------------------------------------------------------
* PLUGIN INSTALLATION SECTION
* ---------------------------------------------------------------------------------------------
***********************************************************************************************/

$installer = new JInstaller();
$installer->install($this->parent->getPath('source').'/extensions/plg_jmscontent');

$installer = new JInstaller();
$installer->install($this->parent->getPath('source').'/extensions/plg_jmsloadsubscription');

$installer = new JInstaller();
$installer->install($this->parent->getPath('source').'/extensions/plg_jmscomponent');

$installer = new JInstaller();
$installer->install($this->parent->getPath('source').'/extensions/plg_jmsgrant');

// Published Plugins
$db->setQuery("UPDATE `#__extensions` SET enabled = 1 WHERE type = 'plugin' AND element = 'jmscontent' AND folder = 'content'");
$db->query();

$db->setQuery("UPDATE `#__extensions` SET enabled = 1 WHERE type = 'plugin' AND element = 'jmsloadsubscription' AND folder = 'content'");
$db->query();

$db->setQuery("UPDATE `#__extensions` SET enabled = 1 WHERE type = 'plugin' AND element = 'jmscomponent' AND folder = 'system'");
$db->query();

$db->setQuery("UPDATE `#__extensions` SET enabled = 1 WHERE type = 'plugin' AND element = 'jmsgrant' AND folder = 'user'");
$db->query();

?>

<img src="components/com_jms/assets/images/jms-520.jpg" alt="Joola Membership Sites" width="520" height="178" />

<h2>Joomla Membership Sites Installation</h2>
<h3><a href="index.php?option=com_jms">Go to Joomla Membership Sites</a></h3>
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
		<tr class="row1">
			<td class="key" colspan="2"><?php echo 'Joomla Membership Sites '.JText::_('Component'); ?></td>
			<td><strong><?php echo JText::_('Installed'); ?></strong></td>
		</tr>
		<tr>
			<th colspan="3"><?php echo JText::_('Plugins'); ?></th>
		</tr>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'Content - Joomla Membership Sites - Content Restrictions'; ?></td>
			<td><strong><?php echo JText::_('Installed'); ?></strong></td>
		</tr>
        <tr class="row1">
			<td class="key" colspan="2"><?php echo 'Content - Joomla Membership Sites - Load Subscription'; ?></td>
			<td><strong><?php echo JText::_('Installed'); ?></strong></td>
		</tr>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'System - Joomla Membership Sites - Component Restrictions'; ?></td>
			<td><strong><?php echo JText::_('Installed'); ?></strong></td>
		</tr>
         <tr class="row1">
			<td class="key" colspan="2"><?php echo 'User - Joomla Membership Sites - Grant User'; ?></td>
			<td><strong><?php echo JText::_('Installed'); ?></strong></td>
		</tr>
	</tbody>
</table>