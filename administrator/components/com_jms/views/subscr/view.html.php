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
 * View to edit
 */
class JmsViewSubscr extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		
		JHtml::stylesheet( 'administrator/components/com_jms/assets/jms.css' );

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		
		JToolBarHelper::title($isNew ? JText::_('COM_JMS_TITLE_SUBSCR_NEW') : JText::_('COM_JMS_TITLE_SUBSCR_EDIT'), 'subscriber.png');
		
        if (isset($this->item->checked_out)) {
		    $checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
		$canDo		= JmsHelper::getActions();

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('subscr.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('subscr.save', 'JTOOLBAR_SAVE');
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('subscr.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('subscr.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
