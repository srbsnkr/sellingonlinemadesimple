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

require_once JPATH_COMPONENT.'/controller.php';

jimport('joomla.application.component.controller');

class JmsControllerForm extends JmsController
{
	
	/**
	 * Method to register a user.
	 *
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function register()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// If registration is disabled - Redirect to login page.
		if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
			$this->setRedirect(JRoute::_('index.php?option=com_jms&view=form', false));
			return false;
		}

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('form', 'JmsModel');

		// Get the user data.
		$requestData = JRequest::getVar('jform', array(), 'post', 'array');

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $requestData);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jms.form.data', $requestData);

			// Redirect back to the registration screen.
			$this->setRedirect(JRoute::_('index.php?option=com_jms&view=form', false));
			return false;
		}

		// Attempt to save the data.
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_jms.form.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('COM_JMS_REGISTRATION_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jms&view=form', false));
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_jms.registration.data', null);
	
		$this->setMessage(JText::_('COM_JMS_REGISTRATION_SAVE_SUCCESS'));
		$this->setRedirect(JRoute::_('index.php?option=com_jms&view=jms', false));

		return true;
	}
}