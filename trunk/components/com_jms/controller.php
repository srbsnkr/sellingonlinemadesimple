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

jimport('joomla.application.component.controller');

class JmsController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName	 = JRequest::getCmd('view', 'form');
		$vFormat = $document->getType();
		$lName	 = JRequest::getCmd('layout');

		if ($view = $this->getView($vName, $vFormat)) {
			
			// Do any specific processing by view.
			switch ($vName) {
				
				// Handle view specific models.
				case 'form':
					// If the user is already logged in, redirect to the profile page.
					$user = JFactory::getUser();
					if ($user->get('id')) {
						// Redirect to subscription page.
						$this->setRedirect('index.php?option=com_jms&view=jms');
						return;
					}
					$model = $this->getModel($vName);
					break;

				// Handle view specific models.
				case 'jms':
					$user = JFactory::getUser();	
					if ($user->get('id') == 0) {
						// Redirect to login page.
						$this->setRedirect('index.php?option=com_jms&view=form');
						return;
					}
					$model = $this->getModel($vName);
					break;

				default:
					$model = $this->getModel('form');
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}