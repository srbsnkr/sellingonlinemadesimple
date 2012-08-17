<?php
/**
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011 Infoweblink 
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgUserJmsgrant extends JPlugin {

	function plgUserJmsgrant(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}
	function onUserBeforeSave($user, $isNew)
	{
		$mainframe = JFactory::getApplication();
	}
	
	function onUserAfterSave($user, $isNew, $succes, $msg)
	{

		$mainframe = JFactory::getApplication();
		
		if ($mainframe->isAdmin()) {
			return true;
		}
		
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS."components".DS."com_jms".DS.'tables');
		$db =& JFactory::getDBO();
		
		$sql = 'SELECT p.id as pid , p.price as price, p.period, p.period_type, p.limit_time' .
			' FROM #__jms_plans as p' .
			' WHERE p.grant_new_user = 1' .
			' AND p.state = 1'
			;
		$db->setQuery($sql);
		$subcription_user = false;
		
		if ($plans = $db->loadObjectList()) {
			foreach ($plans as $plan){
				$sql = 'SELECT plan_id, user_id' .
					' FROM #__jms_plan_subscrs' .
				    ' WHERE user_id = ' . $user['id'] . 
				    ' AND plan_id = ' . $plan->pid .
				    ' AND state = 1' .
				    ' AND expired > NOW()'
				    ;
				$db->setQuery($sql);
				$result = $db->loadObjectList();
				
				if (count($result) <= 0) {
					
					$row	=& JTable::getInstance('subscr', 'Table');
					$row->user_id = $user['id'];
					$row->plan_id = $plan->pid;				
					$row->created = gmdate('Y-m-d H:i:s');
					if ( $plan->period_type == 1 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $plan->period, date('Y')));
					} else if ( $plan->period_type == 2 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $plan->period * 7, date('Y')));
					} else if ( $plan->period_type == 3 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m') + $plan->period, date('d'), date('Y')));
					} else if ( $plan->period_type == 4 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y') + $plan->period));
					}
					$row->price = $plan->price;
					$row->number = 0;
					$row->access_count = 0;
					$row->access_limit = $plan->limit_time;
					$row->payment_method = 'grant_new_user';
					$row->transaction_id = time();
					$row->parent = 0;
					$row->state = 1;
					$row->subscription_type = 'R';
					$row->r_times = 0;
					$row->payment_made = 0;
					$row->subscr_id = '';
					$row->store();
				}
			}
		}		
	}
	function onUserBeforeDelete($user) {
		
	}

	function onUserAfterDelete($user, $success, $msg) {

	}

	function onUserLogin($user, $options) {
		
		$mainframe = JFactory::getApplication();
		
		if ($mainframe->isAdmin()) {
			return true;
		}
		
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS."components".DS."com_jms".DS.'tables');
		$db =& JFactory::getDBO();

		$sql = 'SELECT p.grant_old_user, p.id as pid , p.price as price, p.period, p.period_type, p.limit_time, u.id as uid' .
        	' FROM #__jms_plans as p, #__users AS u' .
        	' WHERE p.grant_old_user' .
        	' AND p.created > u.registerDate' .
        	' AND u.username = "' . $user['username'] . '"' .
        	' AND p.state = 1'
        	;
		$db->setQuery($sql);
		$plans = $db->loadObjectList();

		if (count($plans) > 0) {
			
			foreach ($plans as $plan) {
				$sql = 'SELECT plan_id, user_id' .
			    	' FROM #__jms_plan_subscrs' .
			    	' WHERE user_id = ' . $plan->uid .
			    	' AND plan_id = ' . $plan->pid .
			    	' AND state = 1' .
			    	' AND expired > NOW()'
			    	;
				$db->setQuery($sql);
				$result = $db->loadObjectList();
				
				if (count($result) <= 0) {
					
					$row	=& JTable::getInstance('subscr', 'Table');
					$row->user_id = $plan->uid;
					$row->plan_id = $plan->pid;				
					$row->created = gmdate('Y-m-d H:i:s');
					if ( $plan->period_type == 1 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $plan->period, date('Y')));
					} else if ( $plan->period_type == 2 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $plan->period * 7, date('Y')));
					} else if ( $plan->period_type == 3 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m') + $plan->period, date('d'), date('Y')));
					} else if ( $plan->period_type == 4 ) {
						$row->expired = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y') + $plan->period));
					}
					$row->price = $plan->price;
					$row->number = 0;
					$row->access_count = 0;
					$row->access_limit = $plan->limit_time;
					$row->payment_method = 'grant_old_user';
					$row->transaction_id = time();
					$row->parent = 0;
					$row->state = 1;
					$row->subscription_type = 'R';
					$row->r_times = 0;
					$row->payment_made = 0;
					$row->subscr_id = '';
					$row->store();
				}	
			}
		}
	}

	function onUserLogout($user) {

	}
}