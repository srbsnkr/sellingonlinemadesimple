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
class JmsViewForm extends JView
{
	protected $data;
	protected $form;
	protected $params;
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param	string	The template file to include
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
		JHTML::stylesheet( 'components/com_jms/assets/jms.css' );
		
		$app		= JFactory::getApplication();

		// Get some data from the models
		// Get the view data.
		$this->data		= $this->get('Data');
		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');
		$this->params	= $this->state->get('params');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->setLoginForm();
		$this->showPlans();
		$this->prepareDocument();

        parent::display($tpl);

	}
	
	/**
	 * Prepares the document.
	 *
	 * @since	1.6
	 */
	protected function prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_JMS_REGISTRATION'));
		}

		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
	
	// set login form
	public function setLoginForm() {
		
		$user = JFactory::getUser();
		$uri = JFactory::getURI();
		$url = $uri->toString(array('path', 'query', 'fragment'));
		$session = &JFactory::getSession();
		
		if ($session->get('jms_access_url')) {
			$url = $session->get('jms_access_url');
		}
		
		$this->url	= base64_encode($url);
				
		if ($user->get('id') != 0) {
			$this->name = $user->get('name');
			$this->username = $user->get('username');
			$this->type = 'logout';
		} else {
			$this->type = '';
			$this->name = '';
			$this->username = '';
		}
	}
	
	// get subscription plans
	public function showPlans() {

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('*');
		$query->from('#__jms_plans');
		$query->where('state = 1');
		$query->order('name');

		// Get the options.
		$db->setQuery($query);
		$plans = $db->loadObjectList();
		
		$this->plans = $plans;
	}
}