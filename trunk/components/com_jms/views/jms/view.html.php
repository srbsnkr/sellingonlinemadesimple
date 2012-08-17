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

jimport('joomla.application.component.view');

/**
 * HTML View class for the Jms component
 */
class JmsViewJms extends JView
{
	protected $data;
	protected $form;
	protected $params;
	protected $state;
	protected $item;
	
	/**
	 * Method to display the view.
	 *
	 * @param	string	The template file to include
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		
		JHTML::stylesheet( 'components/com_jms/assets/jms.css' );
		
		switch ($this->getLayout()) {
			case 'history':{
				$this->_displayHistory($tpl);
				return;
			}
			case 'cancel':
			case 'complete':{
				$this->_displayFinalPage($tpl);
				return;
			}
			case 'failure':{
				$this->_displayFailure($tpl);
				return;
			}
			case 'default': {
				$this->setLayout('default');	
			}				
		}

		// Get some data from the models
		$this->items	= $this->get('Data');
		$this->state	= $this->get('State');
		$this->params	= $this->state->get('params');
		$this->user 	= JFactory::getUser();
		
		// Get return post variables
		$rTimes 		= JRequest::getVar('r_times', '2');
		$paymentMethod 	= JRequest::getVar('payment_method', 'os_paypal');
		$xCardNum 		= JRequest::getVar('x_card_num', '');
		$xExpDate 		= JRequest::getVar('x_exp_date', '');
		$xCardCode 		= JRequest::getVar('x_card_code', '');
		
		$this->assignRef('rTimes', $rTimes);
		$this->assignRef('paymentMethod', $paymentMethod);
		$this->assignRef('x_card_num', $xCardNum);
		$this->assignRef('x_exp_date', $xExpDate);
		$this->assignRef('x_card_code', $xCardCode);
				
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        parent::display($tpl);
	}
	
	/**
	 * Private method to display history
	 *
	 * @param template object $tpl
	 */
	protected function _displayHistory($tpl) {
		// Get configuration
		$this->state = $this->get('State');
		$this->params = $this->state->get('params');
		// Get subscriptions
		$this->items = $this->get('Subscriptions');			
		parent::display($tpl);
	}
	
	/**
	 * Private method to display final (cancel/complete) page
	 *
	 * @param template object $tpl
	 */
	 protected function _displayFinalPage($tpl) {
		// Get configuration
		$planId = JRequest::getInt('plan_id');
		
		$model = $this->getModel();
		$this->plan = $model->getPlan($planId);
		
		$this->state = $this->get('State');
		$this->params = $this->state->get('params');
		
		parent::display($tpl);
	}
	
	/**
	 * Private method to display failure page from authorize.net payment gateway
	 *
	 * @param template object $tpl
	 */
	protected function _displayFailure($tpl) {
		$reason =  JRequest::getVar('reason', '');
		$this->assignRef('reason', $reason);
		parent::display($tpl);
	}
}