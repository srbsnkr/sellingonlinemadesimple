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

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Jms model.
 */
class JmsModelplan extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_JMS';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Plan', $prefix = 'JmsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jms.plan', 'plan', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		
		// Modify the form based on Period type value
		$data = $this->getItem();
		
		if ($data->period_type == 5) {
			$form->setFieldAttribute('period', 'disabled', 'true');
			$form->setFieldAttribute('number_of_installments', 'disabled', 'true');
		}
				
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jms.edit.plan.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {

			//Do any procesing on fields here if needed
			// Convert the articles field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->articles);
			$item->articles = $registry->toArray();
			
			// Convert the categories field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->categories);
			$item->categories = $registry->toArray();
			
			// Convert the components field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->components);
			$item->components = $registry->toArray();

		}

		return $item;
	}
	
	public function save($data) {
		
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$post       = JRequest::getVar('jform', array(), 'post', 'array');
		$postdata	= JRequest::get('post', JREQUEST_ALLOWHTML);
		
		$table		= $this->getTable();
		$key		= $table->getKeyName();
		$pk			= (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;
		
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		
		// add extension restrictions to plan params
		$extensions = $post['components'];
		
		if (isset($extensions)) {
			
			foreach ($extensions as $extension) {
				
				$data['params'][$extension . '_task1'] = $postdata['task_' . $extension . '1'];
				$data['params'][$extension . '_task2'] = $postdata['task_' . $extension . '2'];
				$data['params'][$extension . '_task3'] = $postdata['task_' . $extension . '3'];
				$data['params'][$extension . '_task4'] = $postdata['task_' . $extension . '4'];
				$data['params'][$extension . '_value1'] = $postdata['value_' . $extension . '1'];
				$data['params'][$extension . '_value2'] = $postdata['value_' . $extension . '2'];
				$data['params'][$extension . '_value3'] = $postdata['value_' . $extension . '3'];
				$data['params'][$extension . '_value4'] = $postdata['value_' . $extension . '4'];
			}
		
		} else {
			
			$data['params'] = '';
			
		}
				
		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0) {
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data)) {
				$this->setError($table->getError());
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$this->name, &$table, $isNew));
			if (in_array(false, $result, true)) {
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__jms_plans');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}

}