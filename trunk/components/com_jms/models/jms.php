<?php
/**
 * @version     2.0.2
 * @package     com_jms
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.application.component.helper');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jms/tables');

/**
 * Model
 */
class JmsModelJms extends JModelForm
{
	/**
	 * Plan id
	 *
	 * @var int
	 */
	var $_id = null;
	
	/**
	 * Plan data
	 *
	 * @var array
	 */
	var $_plan = null;
	
	/**
	 * Plan data array
	 *
	 * @var array
	 */
	var $_data = null;
	
	/**
	 * subscriptions for current user
	 *
	 * @var array
	 */
	var $_subscriptions = null;
	
	/**
	 * Constructor
	 * @since 1.5
	 */
	public function __construct()
	{		
		parent::__construct();
		$id = JRequest::getVar('id', '', 'default', 'int');
		$this->setId($id);				
	}
	
	/**
	 * Method to set the plan identifier
	 *
	 * @access	public
	 * @param	int plan identifier
	 */
	public function setId($id)
	{
		// Set plan id and wipe data
		$this->_id			= $id;
		$this->_plan		= null;
		$this->_data		= null;
		$this->_subscriptions = null;		
	}
	
	/**
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jms.jms', 'jms', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
	/**
	 * Method to get plan data
	 *
	 * @return plan object list
	 */
	public function getData() {
		
		$sql = 'SELECT * FROM #__jms_plans' .
			' WHERE state = 1 ORDER BY ordering'
			;
		$this->_db->setQuery($sql);
		$this->_data = $this->_db->loadObjectList();
		
		return $this->_data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$app	= JFactory::getApplication();
		$params	= $app->getParams('com_jms');

		// Load the parameters.
		$this->setState('params', $params);
	}
	
	/**
	 * Method to get subscriptions data for current user
	 *
	 * @return subscriptions object list
	 */
	public function getSubscriptions() {
		// Get user
		$user = JFactory::getUser();
				
		$sql = 'SELECT a.*, p.name AS pname, DATEDIFF(a.expired, NOW()) AS days_left' .
			' FROM #__jms_plan_subscrs AS a' .
			' INNER JOIN #__jms_plans AS p ON (a.plan_id  = p.id)' .
			' WHERE a.user_id = ' . (int) $user->get('id');
			
		$this->_db->setQuery($sql);
		$this->_subscriptions = $this->_db->loadObjectList();
		
		return $this->_subscriptions;
	}

	/**
	 * Public method to process subscription
	 *
	 * @param array $data
	 */
	public function processSubscription($data) {
		global $Itemid;		
		
		// Import help library
		jimport('joomla.user.helper');
		$siteUrl = JURI::root();	
		
		// Get configuration
		$config = $this->getConfig();
				
		// Get user
		$user = &JFactory::getUser();		
		$data['transaction_id'] = strtoupper(JUserHelper::genRandomPassword());
		$row = JTable::getInstance('subscr', 'JmsTable');
		$row->bind($data);		
		
		// Get plan information
		$plan = $this->getPlan($row->plan_id);
		$row->user_id = $user->get('id');
		
		// offset
		$format = 'Y-m-d H:i:s';
		$date = date($format);
		$row->created = date($format, strtotime( '-1 day' . $date ));
		
		// Get maximum of expired date
		$maxExpDate = $this->_getMaxExpDate($row->plan_id, $row->user_id);		
		$row->expired = $this->_getExpiredDate($plan->period, $plan->period_type, $maxExpDate);
		$row->number = 0;
		$row->access_count = 0;
		$row->access_limit = $plan->limit_time;
		$paymentMethod = $data['payment_method'];
		if ($paymentMethod == 'iwl_paypal') {
			$row->payment_method = 'Paypal';
		} elseif ($paymentMethod == 'iwl_moneybooker') {
			$row->payment_method = 'MoneyBooker';
		} elseif ($paymentMethod == 'iwl_authnet') {
			$row->payment_method = 'Authorize.net';
		}
		$row->parent = 0;		
		$row->state = 0;
		
		// Get coupon recurring information
		$couponCode = JRequest::getVar('coupon');
		
		if (!empty($couponCode)) {
		$coupon = $this->getCoupon();
		
		$recurring = $coupon->recurring;
		$num_recurring = $coupon->num_recurring;
		} else {
			$recurring = 0;
		}
		
		if ($recurring) {
			$row->subscription_type = 'R';
			$row->r_times = $num_recurring;
		} else {
			$row->subscription_type = 'I';
			$row->r_times = 0;
		}
		//
		
		$row->payment_made = 0;
		$row->subscr_id = '';
		if ($row->price == 0.00) {
			$row->transaction_id = time();
			$row->state = 1;
		}
		$row->store();
		
		if ($row->price == 0.00) {
			// Send notification email
			$this->_sendEmails($row, $config);
			
			// Add user to autoresponder
			if ($plan->autores_enable || $plan->crm_enable || $plan->plan_mc_enable) {
				// Get user
				$user = & JFactory::getUser();
				if ($plan->autores_enable) {
                    require_once JPATH_COMPONENT.'/helpers/iwl_aweber.php';
    				$gateWay = new iwl_aweber();
    				$gateWay->autoresponder($plan, $user);
				}
				
                if ($plan->crm_enable) {
				    require_once JPATH_COMPONENT.'/helpers/iwl_crm.php';
    				$gateWay = new iwl_crm();
    				$gateWay->autoresponder($plan, $user);
                }
				
				if ($plan->plan_mc_enable) {
					require_once JPATH_COMPONENT.'/helpers/iwl_mailchimp.php';
					$gateWay = new iwl_mailchimp();
					$gateWay->autoresponder($plan, $user);
					
					// Display complete page
					JController::setRedirect($siteUrl.'index.php?option=com_jms&task=jms.complete&plan_id='.$row->plan_id.'&Itemid='.$Itemid);
					JController::redirect();
				}
				    
			} else {
				// Display complete page
				JController::setRedirect($siteUrl.'index.php?option=com_jms&task=jms.complete&plan_id='.$row->plan_id.'&Itemid='.$Itemid);
				JController::redirect();
			}
		} else {
			$gatewayData = array();			
			// Require the payment method
			switch ($paymentMethod) {
				case 'iwl_moneybooker':
					require_once JPATH_COMPONENT.'/helpers/iwl_moneybooker.php';
					$gatewayData['pay_to_email'] = $config->get('mb_merchant_email');
					$gatewayData['transaction_id'] = $data['transaction_id'];
		 			$gatewayData['currency'] = $config->get('mb_currency');
		 			$gatewayData['amount'] = $row->price;
		 			$gatewayData['language'] = 'EN';
		 			$gatewayData['merchant_fields'] = 'id';
		 			$gatewayData['id'] = $row->id;
		 			$gatewayData['payment_method'] = 'iwl_moneybooker';
		 			$gatewayData['return_url'] = $siteUrl.'index.php?option=com_jms&task=jms.complete&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
		 			$gatewayData['cancel_url'] = $siteUrl.'index.php?option=com_jms&task=jms.cancel&id='.$row->id.'&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
		 			$gatewayData['status_url'] = $siteUrl.'index.php?option=com_jms&task=jms.subscription_confirm&payment_method=iwl_moneybooker';
		 			$gateway =  new iwl_moneybooker();
		 			$gateway->setParams($gatewayData);
		 			$gateway->submitPost();
		 			break;
		 			
				case 'iwl_paypal':
					require_once JPATH_COMPONENT.'/helpers/iwl_paypal.php';			
					$gatewayData['business']  = $config->get('paypal_id');
					$gatewayData['item_name'] = JText::_('COM_JMS_JOOMLA_RESOURCE_SUBSCRIPTION') . ': ' . $plan->name;
					$gatewayData['amount'] = $row->price;
					$gatewayData['currency_code'] = $config->get('paypal_currency');
					$gatewayData['custom'] = $row->id;
					$gatewayData['return'] = $siteUrl.'index.php?option=com_jms&task=jms.complete&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
					$gatewayData['cancel_return'] = $siteUrl.'index.php?option=com_jms&task=jms.cancel&id='.$row->id.'&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
					$gatewayData['notify_url'] = $siteUrl.'index.php?option=com_jms&task=jms.subscription_confirm&payment_method=iwl_paypal';
					$gateway = new iwl_paypal($config);
					$gateway->setParams($gatewayData);
					$gateway->submitPost();
					break;
	
				case 'iwl_authnet':
					require_once JPATH_COMPONENT.'/helpers/iwl_authnet.php';
					$gateway =  new iwl_authnet($config);
					$data['x_description'] = JText::_('COM_JMS_JOOMLA_RESOURCE_SUBSCRIPTION') . $plan->name;
					$data['email'] = $user->get('email');
					$ret = $gateway->processPayment($data);
					if ($ret) {
						$row->transaction_id = $gateway->getTransactionID();
						// Get maximum of expired date
						$row->created = gmdate('Y-m-d H:i:s');
						$maxExpDate = $this->_getMaxExpDate($row->plan_id, $row->user_id);
			   			$row->expired = $this->_getExpiredDate($plan->period, $plan->period_type, $maxExpDate);
			   			$row->state = 1;
			   			$row->store();
			   			$this->_sendEmails($row, $config);
						
			   			// Get user
						$user = & JFactory::getUser();
			   			
						// Add user to autoresponder
						if ($plan->autores_enable) {							
							require_once JPATH_COMPONENT.'/helpers/iwl_aweber.php';
							$gateWay = new iwl_aweber();
							$gateWay->autoresponder($plan, $user);
						}
						
    					if ($plan->crm_enable) {
        				    require_once JPATH_COMPONENT.'/helpers/iwl_crm.php';
            				$gateWay = new iwl_crm();
            				$gateWay->autoresponder($plan, $user);
                        }

						if ($plan->plan_mc_enable) {
							require_once JPATH_COMPONENT.'/helpers/iwl_mailchimp.php';
							$gateWay = new iwl_mailchimp();
							$gateWay->autoresponder($plan, $user);
						}
					}
					break;
			}	
		}			
	}
	
	/**
	 * Public method to process recurring subscription
	 *
	 * @param array $data
	 */
	public function processRecurringSubscription($data) {
		global $Itemid;		
		
		// Import help library
		jimport('joomla.user.helper');
		$siteUrl = JURI::root();
			
		// Get configuration
		$config = $this->getConfig();
				
		// Get user
		$user = & JFactory::getUser();		
		
		$data['transaction_id'] = strtoupper(JUserHelper::genRandomPassword());
		$row = JTable::getInstance('subscr', 'JmsTable');
		$row->bind($data);
		
		// Get plan information
		$plan = $this->getPlan($row->plan_id);
		$row->user_id = $user->get('id');
		
		// offset
		$format = 'Y-m-d H:i:s';
		$date = date($format);
		$row->created = date($format, strtotime( '-1 day' . $date ));

		// Get maximum of expired date
		$maxExpDate = $this->_getMaxExpDate($row->plan_id, $row->user_id);		
		$row->expired = $this->_getExpiredDate($plan->period, $plan->period_type, $maxExpDate);
		$row->number = 0;
		$row->access_count = 0;
		$row->access_limit = $plan->limit_time;
		$paymentMethod = $data['payment_method'];
		
		if ($paymentMethod == 'iwl_paypal') {
			$row->payment_method = 'Paypal';
		} elseif ($paymentMethod == 'iwl_moneybooker') {
			$row->payment_method = 'MoneyBooker';
		} elseif ($paymentMethod == 'iwl_authnet') {
			$row->payment_method = 'Authorize.net';
		}
		
		$row->parent = 0;
		$row->state = 0;
		$row->subscription_type = 'R';
		$row->payment_made = 0;
		$row->subscr_id = '';
		$row->store();
				
		$gatewayData = array();
		
		// Require the payment method
		switch ($paymentMethod) {
			case 'iwl_moneybooker':
				require_once JPATH_COMPONENT.'/helpers/iwl_moneybooker.php';
				$gatewayData['pay_to_email'] = $config->get('mb_merchant_email');
				$gatewayData['transaction_id'] = $data['transaction_id'];
	 			$gatewayData['currency'] = $config->get('mb_currency');
	 			$gatewayData['amount'] = $row->price;
	 			$gatewayData['language'] = 'EN';
	 			$gatewayData['merchant_fields'] = 'id';
	 			$gatewayData['id'] = $row->id;
	 			$gatewayData['payment_method'] = 'iwl_moneybooker';
	 			$gatewayData['return_url'] = $siteUrl.'index.php?option=com_jms&task=jms.complete&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
	 			$gatewayData['cancel_url'] = $siteUrl.'index.php?option=com_jms&task=jms.cancel&id='.$row->id.'&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
	 			$gatewayData['status_url'] = $siteUrl.'index.php?option=com_jms&task=jms.recurring_subscription_confirm&payment_method=iwl_moneybooker';
	 			$gateway =  new iwl_moneybooker();
	 			$gateway->setParams($gatewayData);
	 			$gateway->submitPost();
	 			break;
	 			
			case 'iwl_paypal':
				require_once JPATH_COMPONENT.'/helpers/iwl_paypal.php';			
				$gatewayData['business']  = $config->get('paypal_id');
				$gatewayData['item_name'] = JText::_('COM_JMS_JOOMLA_RESOURCE_SUBSCRIPTION') . ': ' . $plan->name;
				$gatewayData['currency_code'] = $config->get('paypal_currency');
				$gatewayData['custom'] = $row->id;
				$gatewayData['return'] = $siteUrl.'index.php?option=com_jms&task=jms.complete&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
				$gatewayData['cancel_return'] = $siteUrl.'index.php?option=com_jms&task=jms.cancel&id='.$row->id.'&plan_id='.$row->plan_id.'&Itemid='.$Itemid;
				$gatewayData['notify_url'] = $siteUrl.'index.php?option=com_jms&task=jms.subscription_confirm&payment_method=iwl_paypal';
				$gatewayData['cmd'] = '_xclick-subscriptions';
				$gatewayData['src'] = 1;
				$gatewayData['sra'] = 1;
				$gatewayData['a3'] = $row->price;
				
				switch ($plan->period_type) {
					// Day type
					case '1':
						$p3 = $plan->period;
						$t3 = 'D';
						break;
					// Week type	
					case '2':
						$p3 = $plan->period;
						$t3 = 'W';
						break;
					// Month type	
					case '3':
						$p3 = $plan->period;
						$t3 = 'M';
						break;
					// Year type		
					case '4':
						$p3 = $plan->period;
						$t3 = 'Y';
						break;
				}
				$gatewayData['p3'] = $p3;
				$gatewayData['t3'] = $t3;
				$gatewayData['lc'] = 'US';
				if ($row->r_times > 1) {
					$gatewayData['srt'] = $row->r_times;
				}				
				$gateway = new iwl_paypal($config);
				$gateway->setParams($gatewayData);
				$gateway->submitPost();
				break;

			case 'iwl_authnet':
				require_once JPATH_COMPONENT.'/helpers/iwl_authnetrb.php';
				$gateway =  new iwl_AuthnetARB($config);
				switch ($plan->period_type) {
					// Day type
					case '1':
						$length = $plan->period;
						$unit = 'days';
						break;
					// Week type	
					case '2':
						$length = 7 * $plan->period;
						$unit = 'days';
						break;
					// Month type	
					case '3':
						$length = $plan->period;
						$unit = 'months';
						break;
					// Year type					
					case '4':
						$length = 12 * $plan->period;
						$unit = 'months';
						break;					
				}
				
				$gatewayData = array();
				$gatewayData['refID'] = $row->id;
				$gatewayData['subscrName'] = $user->get('name');
				$gatewayData['interval_length'] = $length;
				$gatewayData['interval_unit'] = $unit;
				$gatewayData['expirationDate'] = $data['x_exp_date'];
				$gatewayData['cardNumber'] = $data['x_card_num'];
				$gatewayData['firstName'] = $user->get('name');
				$gatewayData['lastName'] = $user->get('name');
				$gatewayData['amount'] = $row->price;
				$gatewayData['x_description'] = JText::_('COM_JMS_JOOMLA_RESOURCE_SUBSCRIPTION') . $plan->name;
				$gatewayData['email'] = $user->get('email');
				$gatewayData['plan_id'] = $row->plan_id;
 				$ret = $gateway->processPayment($gatewayData);
 				
				if ($ret) {
					$row->transaction_id = $gateway->getSubscriberID();
					$row->created = gmdate('Y-m-d H:i:s');
					// Get maximum of expired date
					$maxExpDate = $this->_getMaxExpDate($row->plan_id, $row->user_id);
		   			$row->expired = $this->_getExpiredDate($plan->period, $plan->period_type, $maxExpDate);
		   			$row->state = 1;
		   			$row->payment_made = 1;
		   			$row->store();
		   			$this->_sendEmails($row, $config);
					
		   			// Get user
					$user = & JFactory::getUser();

					// Add user to autoresponder
					if ($plan->autores_enable) {					
						require_once JPATH_COMPONENT.'/helpers/iwl_aweber.php';
						$gateWay = new iwl_aweber();
						$gateWay->autoresponder($plan, $user);
					}
					
				    if ($plan->crm_enable) {
    				    require_once JPATH_COMPONENT.'/helpers/iwl_crm.php';
        				$gateWay = new iwl_crm();
        				$gateWay->autoresponder($plan, $user);
                    }
					
					if ($plan->plan_mc_enable) {
						require_once JPATH_COMPONENT.'/helpers/iwl_mailchimp.php';
						$gateWay = new iwl_mailchimp();
						$gateWay->autoresponder($plan, $user);
					}
						
				}
				break;
		}
	}

	/**
	 * Process confirm subscription
	 *
	 */
	public function subscriptionConfirm() {	
		$config = $this->getConfig();
		$paymentMethod =  JRequest::getVar('payment_method', '');
		if ($paymentMethod == 'iwl_paypal') {
			$id = JRequest::getInt('custom');
		} else if ($paymentMethod == 'iwl_moneybooker') {
			$id = JRequest::getInt('id', 0);
		}
		$row =  JTable::getInstance('subscr', 'JmsTable');
   		$row->load($id);
   		$ret = false;
		
		// Get plan information
		$plan = $this->getPlan($row->plan_id);
		
		// Get maximum of expired date
		$maxExpDate = $this->_getMaxExpDate($row->plan_id, $row->user_id);
		switch ($paymentMethod) {
			case 'iwl_paypal':
				require_once JPATH_COMPONENT.'/helpers/iwl_paypal.php';
				$gateWay =  new iwl_paypal($config);
				$ret = $gateWay->processPayment($plan->period, $plan->period_type, $maxExpDate);
				break;				
			case 'iwl_moneybooker':
				require_once JPATH_COMPONENT.'/helpers/iwl_moneybooker.php';				
				$gateWay = new iwl_moneybooker();
				$ret =  $gateWay->processPayment($config, $plan->period, $plan->period_type, $maxExpDate);
				break;
		}
		if ($ret) {			
			$row =  JTable::getInstance('subscr', 'JmsTable');
			$row->load($id);
			$this->_sendEmails($row, $config);
			
			// Get user
			$user = & JFactory::getUser();
			
			// Add user to autoresponder
			if ($plan->autores_enable) {			
				require_once JPATH_COMPONENT.'/helpers/iwl_aweber.php';
				$gateWay = new iwl_aweber();
				$gateWay->autoresponder($plan, $user);
			}
			
		    if ($plan->crm_enable) {
			    require_once JPATH_COMPONENT.'/helpers/iwl_crm.php';
				$gateWay = new iwl_crm();
				$gateWay->autoresponder($plan, $user);
            }
			
			if ($plan->plan_mc_enable) {
				require_once JPATH_COMPONENT.'/helpers/iwl_mailchimp.php';
				$gateWay = new iwl_mailchimp();
				$gateWay->autoresponder($plan, $user);
			}
					
		}
	}
	
	/**
	 * Process recurring subscription confirm
	 *
	 */
	public function recurringSubscriptionConfirm() {
		$config = $this->getConfig();
		$paymentMethod =  JRequest::getVar('payment_method', '');
		if ($paymentMethod == 'iwl_paypal') {
			$id = JRequest::getInt('custom');
		} else if ($paymentMethod == 'iwl_moneybooker') {
			$id = JRequest::getInt('id', 0);
		}
		$row =  JTable::getInstance('subscr', 'JmsTable');
   		$row->load($id);
   		$ret = false;
		
		// Get plan information
		$plan = $this->getPlan($row->plan_id);
		
		// Get maximum of expired date
		$maxExpDate = $this->_getMaxExpDate($row->plan_id, $row->user_id);
		switch ($paymentMethod) {
			case 'iwl_paypal':
				require_once JPATH_COMPONENT.'/helpers/iwl_paypal.php';				
				$gateWay =  new iwl_paypal($config);
				$gateWay->ipn_log = true;
				$gateWay->ipn_log_file = JPATH_COMPONENT.DS.'log.txt';
				$ret = $gateWay->processRecurringPayment($plan->period, $plan->period_type, $plan->access_limit, $maxExpDate);
				break;
			case 'iwl_moneybooker':
				require_once JPATH_COMPONENT.'/helpers/iwl_moneybooker.php';				
				$gateWay = new iwl_moneybooker();
				$ret =  $gateWay->processPayment($config, $plan->period, $plan->period_type, $maxExpDate);
				break;
		}
		if ($ret) {
			$row =  JTable::getInstance('subscr', 'JmsTable');
			$row->load($id);
			$txnType = JRequest::getVar('txn_type', '');
			if ($txnType == 'subscr_signup') {
				$this->_sendEmails($row, $config);
				
				// Get user
				$user = & JFactory::getUser();
				
				// Add user to autoresponder
				if ($plan->autores_enable) {				
					require_once JPATH_COMPONENT.'/helpers/iwl_aweber.php';
					$gateWay = new iwl_aweber();
					$gateWay->autoresponder($plan, $user);
				}
				
			    if ($plan->crm_enable) {
				    require_once JPATH_COMPONENT.'/helpers/iwl_crm.php';
    				$gateWay = new iwl_crm();
    				$gateWay->autoresponder($plan, $user);
                }
				
				if ($plan->plan_mc_enable) {
					require_once JPATH_COMPONENT.'/helpers/iwl_mailchimp.php';
					$gateWay = new iwl_mailchimp();
					$gateWay->autoresponder($plan, $user);
				}
				
			} else {
				$this->_sendRecurringEmail($row, $config);
			}
		}
	}
	
	/**
	 * Public method to process Arb Silent Post from authorize.net
	 *
	 * @param object $subscription
	 */
	protected function arbSilentPostProcess($subscription) {
		$row =  JTable::getInstance('subscr', 'JmsTable');
   		$row->load($subscription->id);
		
   		// Get plan information
		$plan = $this->getPlan($row->plan_id);
   		$row->expired = $this->_getExpiredDate($plan->period, $plan->period_type, $row->expired);
		$row->access_limit = $row->access_limit + $plan->access_limit;
		$row->payment_made = $row->payment_made + 1;
		$row->price = $row->price + $_POST['x_amount'];
		$row->store();
		
		// Send recurring email
		$config = $this->getConfig();
		$this->_sendRecurringEmails($row, $config);
	}
	
	/**
	 * Public method to get config object
	 *
	 * @return object
	 */
	public function getConfig() {	
		// Get the application object.
		$app	= JFactory::getApplication();
		
		// Get the component config/params object.
		$config	= $app->getParams('com_jms');		
		return $config;
	}
	
	/**
	 * Public method to get price for a specific subscription plan
	 *
	 * @param int $planId
	 * @return plan object
	 */
	public function getPlan($planId) {
		$query = 'SELECT *' .
			' FROM #__jms_plans' .
			' WHERE id = ' . (int) $planId
			;
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}
	
	/**
	 *
	 *
	 */
	public function getCoupon() {
		$couponCode = JRequest::getVar('coupon');
		$query = 'SELECT *' .
			' FROM #__jms_coupons' .
			' WHERE code = "' . $couponCode . '"'
			;		
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}
	
	/**
	 * Private method to get maximum of expired date
	 *
	 * @param int $planId
	 * @param int $userId
	 * @return datetime
	 */
	protected function _getMaxExpDate($planId, $userId) {
		$query = 'SELECT MAX(expired)' .
			' FROM #__jms_plan_subscrs' .
			' WHERE plan_id = ' . $planId .
			' AND user_id = ' . $userId .
			' AND expired > NOW()' .
			' AND state = 1'
			;
		$this->_db->setQuery($query);
		$maxExpDate = $this->_db->loadResult();
		// If max expired date is not set, then assign it to current date
		if (!isset($maxExpDate)) {
			$maxExpDate = date('Y-m-d H:i:s');
		}
		return $maxExpDate;
	}
	
	/**
	 * Private method to get expired date
	 *
	 * @param int $periodType
	 * @param datetime $maxExpDate
	 * @return datetime
	 */
	protected function _getExpiredDate($period, $periodType, $maxExpDate) {
		if ( $periodType == 1 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm'), (int)JHTML::date($maxExpDate, 'd') + $period, (int)JHTML::date($maxExpDate, 'Y')));
		} else if ( $periodType == 2 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm'), (int)JHTML::date($maxExpDate, 'd') + $period * 7, (int)JHTML::date($maxExpDate, 'Y')));
		} else if ( $periodType == 3 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm') + $period, (int)JHTML::date($maxExpDate, 'd'), (int)JHTML::date($maxExpDate, 'Y')));
		} else if ( $periodType == 4 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm'), (int)JHTML::date($maxExpDate, 'd'), (int)JHTML::date($maxExpDate, 'Y') + $period));
		} else if ( $periodType == 5 ) {
			$expired = '3009-12-31 23:59:59';
		}
		return $expired;
	}
	
	/**
	 * Private method to send emails
	 *
	 * @param subscriber object $row
	 * @param array $config
	 */
	protected function _sendEmails($row, $config) {		
		// Get global joomla configuration
		$jconfig = new JConfig();
		$fromEmail =  $jconfig->mailfrom;
		$fromName = $jconfig->fromname;
		// Get user information
		$user = JFactory::getUser($row->user_id);
		
		// Send notification email to user
		$subject = $config->get('user_email_subject');
		$body = nl2br($config->get('user_email_body'));
		$subscriptionDetail = $this->_getSubscriptionDetail($row, $config);
		$body = str_replace('{subscription_detail}', $subscriptionDetail, $body);
		$body = str_replace('{username}', $user->username, $body);
		JUtility::sendMail($fromEmail, $fromName, $user->get('email'), $subject, $body, 1);
		
		// Send notification email to admin or notification emails address
		if ($config->get('notification_emails') == '') {
			$notificationEmails = $fromEmail;	
		}			
		else {
			$notificationEmails = $config->get('notification_emails');
		}
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails = explode(',', $notificationEmails);
		$subject = $config->get('admin_email_subject');
		$body = $config->get('admin_email_body');
		$body =  nl2br($body);
		$subscriptionDetail = $this->_getSubscriptionDetail($row, $config);
		$body = str_replace('{subscription_detail}', $subscriptionDetail, $body);
		$body = str_replace('{name}', $user->get('name'), $body);
		$body = str_replace('{email}', '<a href="mailto:'.$user->get('email').'">'.$user->get('email').'</a>', $body);
		for ($i = 0, $n  = count($emails); $i < $n; $i++) {
			$email = $emails[$i];
			JUtility::sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
		}
	}
	
	/**
	 * Private method to send recurring emails
	 *
	 * @param subscriber object $row
	 * @param array $config
	 */
	protected function _sendRecurringEmails($row, $config) {		
		// Get global joomla configuration
		$jconfig = new JConfig();
		$fromEmail =  $jconfig->mailfrom;
		$fromName = $jconfig->fromname;
		// Get user information
		$user = JFactory::getUser($row->user_id);
		
		// Send notification recurring email to user
		$subject = $config->get('user_recurring_email_subject');
		$body = nl2br($config->get('user_recurring_email_body'));
		$subscriptionDetail = $this->_getSubscriptionDetail($row, $config);
		$body = str_replace('{subscription_detail}', $subscriptionDetail, $body);
		$body = str_replace('{username}', $user->username, $body);
		JUtility::sendMail($fromEmail, $fromName, $user->get('email'), $subject, $body, 1);
		
		// Send notification recurring email to admin or notofication emails address
		if ($config->get('notification_emails') == '') {
			$notificationEmails = $fromEmail;	
		}			
		else {
			$notificationEmails = $config->get('notification_emails');
		}
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails = explode(',', $notificationEmails);
		$subject = $config->get('admin_recurring_email_subject');
		$body = $config->get('admin_recurring_email_body');
		$body =  nl2br($body);
		$subscriptionDetail = $this->_getSubscriptionDetail($row, $config);
		$body = str_replace('{subscription_detail}', $subscriptionDetail, $body);
		$body = str_replace('{name}', $user->get('name'), $body);
		$body = str_replace('{email}', '<a href="mailto:'.$user->get('email').'">'.$user->get('email').'</a>', $body);
		for ($i = 0, $n  = count($emails); $i < $n; $i++) {
			$email = $emails[$i];
			JUtility::sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
		}
	}
	
	/**
	 * Private method to get subscriber detail
	 *
	 * @param subscriber object $row
	 * @param array $config
	 * @return subscriber detail
	 */
	protected function _getSubscriptionDetail($row, $config) {
		$plan = $this->getPlan($row->plan_id);
		$return = '<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td>' . JText::_('COM_JMS_EMAIL_SUBSCRIPTION_NAME') . '</td>
						<td>' . $plan->name . '</td>
					</tr>					
					<tr>
						<td>' . JText::_('COM_JMS_EMAIL_START_ON') . '</td>
						<td>' . JHTML::date($row->created, '%d-%m-%Y') . '</td>
					</tr>
					<tr>
						<td>' . JText::_('COM_JMS_EMAIL_FINISH_ON') . '</td>
						<td>' . JHTML::date($row->expired, '%d-%m-%Y') . '</td>
					</tr>
					<tr>
						<td>' . JText::_('COM_JMS_EMAIL_PRICE') . '</td>
						<td>' . $config->get('currency_sign') . $row->price . '</td>
					</tr>
					<tr>
						<td>' . JText::_('COM_JMS_EMAIL_LIMIT') . '</td>
						<td>' . ($row->access_limit == 0 ? JText::_('COM_JMS_EMAIL_NO_LIMITS') : $row->access_limit) . '</td>
					</tr>
					<tr>
						<td>' . JText::_('COM_JMS_EMAIL_SUBSCRIPTION_DESCRIPTION') . '</td>
						<td>' . $plan->description . '</td>
					</tr>
				</table>';
		return $return;
	}
	
	/**
	 * Public method to cancel subscription
	 *
	 * @param array $data
	 */
	public function cancelSubscription($data) {
		$id = (int) $data['id'];
		$sql = 'DELETE FROM #__jms_plan_subscrs' .
			' WHERE id = ' . (int) $id
			;
		$this->_db->setQuery($sql);
		$this->_db->query();
	}
}
