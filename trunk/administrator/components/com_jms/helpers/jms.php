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

/**
 * Jms helper.
 */
class JmsHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_JMS_TITLE_CONTROL_PANEL'),
			'index.php?option=com_jms',
			$vName == 'jms'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_JMS_TITLE_PLANS'),
			'index.php?option=com_jms&view=plans',
			$vName == 'plans'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_JMS_TITLE_SUBSCRS'),
			'index.php?option=com_jms&view=subscrs',
			$vName == 'subscrs'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_JMS_TITLE_COUPONS'),
			'index.php?option=com_jms&view=coupons',
			$vName == 'coupons'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_JMS_TITLE_ABOUT'),
			'index.php?option=com_jms&view=about',
			$vName == 'about'
		);

	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($messageId)) {
			$assetName = 'com_jms';
		}
		else {
			$assetName = 'com_jms.message.'.(int) $messageId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete',
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
