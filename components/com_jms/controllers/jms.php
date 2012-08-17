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

require_once JPATH_COMPONENT.'/controller.php';

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.controller');

class JmsControllerJms extends JmsController
{
	/**
	 * Coupon Exsit
	 *
	 * @var boolean, true if coupon is exists, false if vice versa
	 */
	var $_couponExists = true;
	
	/**
	 * Coupon Valid
	 *
	 * @var boolean, true if coupon is valid, false if vice versa
	 */
	var $_couponValid = true;
	
	/**
	 * Coupon Error
	 *
	 * @var string
	 */
	var $_couponError = '';
	
	/**
	 * Method to process subscription
	 *
	 */
	public function process_subscription() {		
		$mainframe = JFactory::getApplication();		
		$model = & $this->getModel('jms');
		$post = JRequest::get('post');
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$sid = JRequest::getInt('sid');
		$coupon = JRequest::getVar('coupon', '', 'default', 'string');
		
		// If subscription id is null, user can't purchase it
		if(!$sid){
			$msg = JText::_('COM_JMS_CAN_NOT_PURCHASE');
			$url = JRoute::_('index.php?option=com_jms&view=form&Itemid=' . JRequest::getInt('Itemid'));
			$mainframe->enqueueMessage($msg, 'Error');
			$this->setRedirect($url);
			return;
		}
		
		// If users are not login, they can't purchase subscription
		if (!$user->get('id')) {
			$msg = JText::_('COM_JMS_YOU_MUST_LOGIN_TO_PURCHASE');
			$url = JRoute::_('index.php?option=com_jms&view=form&Itemid=' . JRequest::getInt('Itemid'));
			$mainframe->enqueueMessage($msg, 'Error');
			$this->setRedirect($url);
			return;
		}
		
		// Get subscription information		
		$plan = $model->getPlan($sid);

		// As default, order amount is equal subscription price
		$post['price'] = $plan->price;

		// Re-calculate order amount based on discount
		if($plan->discount > 0) {
			$post['price'] = round(($post['price'] - ($post['price'] * ($plan->discount / 100))), 2);
		}
		$post['plan_id'] = $sid;
		$post['item_name']  = $mainframe->getCfg('sitename') . ' :: ' . $plan->name;
		$post['order_quantity'] = 1;		
		
		if($coupon && ($post['price'] > 0)) {
			$this->_validateCoupon($coupon);
			if(!$this->_couponExists) {
				JError::raiseNotice(100, JText::_('COM_JMS_COUPON_NOT_EXISTS'));
				parent::display();
				return;
			} else {
				if(!$this->_couponValid) {
					JError::raiseNotice(100, JText::_('COM_JMS_COUPON_NOT_VALID'));
					JError::raiseNotice(100, $this->_couponError);
					parent::display();
					return;
				} else {
					$post['price'] = $this->_applyCoupon($coupon, $post['price']);
				}
			}
		}
				
		// Process Subscription
		$model->processSubscription($post);
		
		$paymentMethod = $post['payment_method'];
		if ($paymentMethod == 'iwl_authnet') {
			$this->display();
		}
	}
	
	/**
	 * Method to process recurring subscription
	 *
	 */
	public function process_recurring_subscription() {
		$mainframe = JFactory::getApplication();		
		$model = & $this->getModel('jms');
		$post = JRequest::get('post');
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$sid = JRequest::getInt('sid');
		$coupon = JRequest::getVar('coupon', '', 'default', 'string');
		
		// If subscription id is null, user can't purchase it
		if(!$sid){
			$msg = JText::_('COM_JMS_CAN_NOT_PURCHASE');
			$url = 'index.php?option=com_jms&view=form&Itemid='.JRequest::getInt('Itemid');
			$mainframe->enqueueMessage($msg, 'Error');
			$this->setRedirect($url);
			return;
		}
		
		// If users are not login, they can't purchase subscription
		if (!$user->get('id')) {
			$msg = JText::_('COM_JMS_YOU_MUST_LOGIN_TO_PURCHASE');
			$url = JRoute::_('index.php?option=com_jms&view=form&Itemid=' . JRequest::getInt('Itemid'));
			$mainframe->enqueueMessage($msg, 'Error');
			$this->setRedirect($url);
			return;
		}
		
		// Get subscription information		
		$plan = $model->getPlan($sid);

		// As default, order amount is equal subscription price
		$post['price'] = $plan->price;

		// Re-calculate order amount based on discount, if user have already purchase this subscription, then they will be discounted
		if($plan->discount > 0) {
			$post['price'] = round(($post['price'] - ($post['price'] * ($plan->discount / 100))), 2);
		}
		$post['plan_id'] = $sid;
		$post['item_name']  = $mainframe->getCfg('sitename') . ' :: ' . $plan->name;
		$post['order_quantity'] = 1;		
		
		if($coupon && ($post['price'] > 0)) {
			$this->_validateCoupon($coupon);
			if(!$this->_couponExists) {
				JError::raiseNotice(100, JText::_('COM_JMS_COUPON_NOT_EXISTS'));
				parent::display();
				return;
			} else {
				if(!$this->_couponValid) {
					JError::raiseNotice(100, JText::_('COM_JMS_COUPON_NOT_VALID'));
					JError::raiseNotice(100, $this->_couponError);
					parent::display();
					return;
				} else {
					$post['price'] = $this->_applyCoupon($coupon, $post['price']);
				}
			}
		}
				
		// Process Subscription
		if ($post['price'] == 0.00) {
			$model->processSubscription($post);
		} else {
			$model->processRecurringSubscription($post);	
		}		
		
		$paymentMethod = $post['payment_method'];
		if ($paymentMethod == 'iwl_authnet') {
			$this->display();
		}
	}
	
	/**
	 * Method to confirm subscription
	 *
	 */
	public function subscription_confirm() {						
		$model = & $this->getModel('jms');				
		$model->subscriptionConfirm();
	}
	
	/**
	 * Method to confirm recurring subscription
	 *
	 */
	public function recurring_subscription_confirm() {
		$model = & $this->getModel('jms');
		$model->recurringSubscriptionConfirm();
	}
	
	
	public function arb_silent_post_process() {
		$subscription_id = (int) $_POST['x_subscription_id'];		
		if ($subscription_id) {
		    // Get the response code. 1 is success, 2 is decline, 3 is error
		    $response_code = (int) $_POST['x_response_code'];		 
		    // Get the reason code. 8 is expired card.
		    $reason_code = (int) $_POST['x_response_reason_code'];		 
		    if ($response_code == 1) {
		        // Success
		        $db = JFactory::getDBO();
		        // Get the corresponding subscription base on subscription id
		        $query = 'SELECT * FROM #__jms_plan_subscrs WHERE transaction_id = "' . $subscription_id . '" AND subscription_type = "R"';
		        $db->setQuery($query);
		        $subscription = $db->loadObject();
		        if (is_object($subscription)) {
		        	// Update subscription
		        	$model = $this->getModel('jms');
		        	$model->arbSilentPostProcess($subscription);
		        }
		    } else if ($response_code == 2) {
		        // Declined
		    } else if ($response_code == 3 && $reason_code == 8) {
		        // An expired card
		    } else {
		        // Other error
		    }
		}
	}
	
	/**
	 * Method to cancel subscription
	 *
	 */
	public function cancel() {
		$post = $_REQUEST;
		$model = & $this->getModel('jms');
		$model->cancelSubscription($post);
		$this->setRedirect('index.php?option=com_jms&view=jms&layout=cancel&plan_id=' . $post['plan_id']);		
	}
	
	/**
	 * Method to cancel subscription
	 *
	 */
	public function complete() {
		$post = $_REQUEST;
		$this->setRedirect('index.php?option=com_jms&view=jms&layout=complete&plan_id=' . $post['plan_id']);		
	}
	
	/**
	 * Private method to apply coupon
	 *
	 * @param string $coupon
	 * @param float $price
	 * @return new price after apply coupon
	 */
	protected function _applyCoupon($coupon, $price)
	{
		$db = & JFactory::getDBO();
		$user = & JFactory::getUser();
		$sid = JRequest::getInt('sid');
		
		// Update used time for this coupon
		$sql = 'UPDATE #__jms_coupons' .
			' SET used_time = used_time + 1' .
			' WHERE code = "' . $coupon . '"'
			;
		$db->setQuery($sql);
		$db->query();

		// Get coupon information
		$sql = 'SELECT *' .
			' FROM #__jms_coupons' .
			' WHERE code = "' . $coupon . '"'
			;		
		$db->setQuery($sql);
		$row = $db->loadObject();
				
		// Make coupon history for this current user
		$sql = 'INSERT INTO #__jms_coupon_subscrs' .
			' VALUES ("", ' . $user->get('id') . ', ' . $row->id . ', ' . $sid . ', NOW(), ' . $price . ', ' . $row->discount . ', ' . $row->discount_type . ', ' . $row->recurring . ', ' . $row->num_recurring . ')'
			;
		$db->setQuery($sql);
		$db->query();		
		
		// Discount based on amount
		if($row->discount_type == 2)
		{
			$new = round(($row->discount - $price), 2);
			if($new < 0) $new = 0;
			$sql = 'UPDATE #__jms_coupons' .
				' SET discount = ' . $new .
				' WHERE code = "' . $coupon . '"'
				;			
			$db->setQuery($sql);
			$db->query();

			$out = $price - $row->discount;
			if($out < 0) $out = 0;
			$out = round($out, 2);			
		} else {
			$out = $price - ($price * ($row->discount / 100));
			$out = round($out, 2);			
		}
		return $out;
	}
	
	/**
	 * Private method to validate coupon
	 *
	 * @param string $coupon
	 */
	protected function _validateCoupon($coupon)
	{
		// Get DB connector
		$db = & JFactory::getDBO();		
		$sql = 'SELECT *, ' .
			' IF(expired > NOW() OR YEAR (expired) = 0, 0, 1) AS expired_coupon' .
			' FROM #__jms_coupons' .
			' WHERE code = "' . $coupon . '"' .
			' AND state = 1' .
			' AND created <= NOW()'
			;
		$db->setQuery($sql);
		$row = $db->loadObject();		

		if (!isset($row)) {
			$this->_couponExists = false;
			$this->_couponValid = false;
			$this->_couponError = JText::_('COM_JMS_COUPON_USED_OUT');
		} else {
			if ($row->code) {
				$this->_couponExists = true;
			} else {
				$this->_couponExists = false;
			}
			
			// Validate limit time
			if(($row->limit_time > 0) && ($row->used_time >= $row->limit_time)) {
				$this->_couponValid =  false;
				$this->_couponError = JText::_('COM_JMS_COUPON_USED_OUT');
				return;
			}
			
			// Validate gif certificate
			if(($row->discount_type == 2) && ($row->discount <= 0)) {
				$this->_couponValid =  false;
				$this->_couponError = JText::_('COM_JMS_COUPON_USED_OUT');
				return;
			}
	
			// Validate expired
			if($row->expired_coupon) {
				$this->_couponValid =  false;
				$this->_couponError = JText::_('COM_JMS_COUPON_EXPIRED');
				return;
			}
	
			// Validate bind to this user
			if($row->user_ids) {
				$user = JFactory::getUser();
				$ids = $row->user_ids;
				// string to array
				$registry = new JRegistry;
				$registry->loadString($ids);
				$ids = $registry->toArray();
				if(!in_array($user->get('id'), $ids)) {
					$this->_couponValid =  false;
					$this->_couponError = JText::_('COM_JMS_COUPON_NOT_ALLOWED_TO_USE');
					return;
				}
			}
			
			// Validate bind to this subscription
			if($row->plan_ids) {
				$ids = $row->plan_ids;
				// string to array
				$registry = new JRegistry;
				$registry->loadString($ids);
				$ids = $registry->toArray();
				$sid = JRequest::getInt('sid');
				if(!in_array($sid, $ids)) {
					$this->_couponValid =  false;
					$this->_couponError = JText::_('COM_JMS_COUPON_NOT_VALID_FOR_PLAN');
					return;
				}
			}
			
			// Validate limit time per user
			if($row->limit_time_user > 0) {
				$user = &JFactory::getUser();				
				$sql = 'SELECT COUNT(*)' .
					' FROM #__jms_coupon_subscrs' .
					' WHERE coupon_id = ' . (int) $row->id .
					' AND user_id = ' . (int) $user->get('id')
					;
				$db->setQuery($sql);
				$num = $db->loadResult();
				if($num >= $row->limit_time_user) {
					$this->_couponValid =  false;
					$this->_couponError = JText::_('COM_JMS_EXCEED_COUPON_TIME');
					return;
				}
			}	
		}					
	}
}