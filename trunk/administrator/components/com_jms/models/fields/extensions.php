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

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldExtensions extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Extensions';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $forceMultiple = true;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Initialize some field attributes.
		$class = $this->element['class'] ? ' class="checkboxes '.(string) $this->element['class'].'"' : ' class="checkboxes"';
		
		// get extensions installed
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('element AS value, name AS text, params');
		$query->from('#__extensions AS a');
		$query->where('type = "component" AND protected = 0 AND enabled = 1');
		$query->order('a.name');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		
		// get plan params
		$id		= JRequest::getVar('id');
		$query	= $db->getQuery(true);
		
		$query->select('*');
		$query->from('#__jms_plans');
		$query->where('id = ' . (int) $id);

		// Get param values.
		$db->setQuery($query);
		$row = $db->loadObject();
		
		$params = new JRegistry();
		if (isset($row->params)) {
			$params->loadString($row->params);
		}

		// Start the checkbox field output.
		$html[] = '<fieldset id="'.$this->id.'"'.$class.'>';

		// Build the checkbox field output.
		$html[] = '<div>';
		
		// make 2 columns for the div
		$extensions = $options;
		$cols = 2;
		$count = count($extensions);
		
		if($count%$cols > 0 ){
			for ($e = 0; $e < ($cols-$count%$cols); $e++) {
				$extensions[] = '&nbsp;';
			}
		}
		
		//print_r($count);
		
		$addform = new JXMLElement('<form />');
		$fields = $addform->addChild('fields');
		$fields->addAttribute('name', 'params');
		$fieldset = $fields->addChild('fieldset');
		$fieldset->addAttribute('name', 'item_associations');
		$fieldset->addAttribute('description', 'COM_MENUS_ITEM_ASSOCIATIONS_FIELDSET_DESC');
		
		foreach ($options as $i => $option) {

			// Initialize some option attributes.
			$checked	= (in_array((string)$option->value,(array)$this->value) ? ' checked="checked"' : '');
			$class		= !empty($option->class) ? ' class="'.$option->class.'"' : '';
			$disabled	= !empty($option->disable) ? ' disabled="disabled"' : '';

			// Initialize some JavaScript option attributes.
			// onclick="jmsShowComParams('component_name', 'component_name[]')
			
			if($i%$cols == 0) {
				$html[] = '<div class="jmsblock">';
				}
			
			$html[] = '<div class="col-' . ($i%$cols+1) . '">';
			
			$onclick	= " onclick=\"jmsShowComParams('" . $option->value . "', '" . $option->value . "');\"" ;			
			
			$html[] = '<div class="label-pad">';
			$html[] = '<input type="checkbox" id="check_'.$option->value.'" name="'.$this->name.'"' .
					' value="'.htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8').'"'
					.$checked.$class.$onclick.$disabled.'/>';
					
			$html[] = '<label for="'.$this->id.$i.'"'.$class.'>'.JText::_($option->text).'</label>';
			$html[] = '<div class="clr"></div>';
			$html[] = '</div>';
				
			if ( (!in_array($option->value, $options )) && !$checked ) {
				$html[] = '<div id="params_' . $option->value . '" class="jmsblockhide">';
			} else {
				$html[] = '<div id="params_' . $option->value . '" class="jmsblockshow">';
				}

			$html[] = '<div class="jmsblockshow-pad">';
			$html[] = '<table class="admintable" cellpadding="0" cellspacing="0">';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" align="right" width="100">';
			$html[] = JText::_( 'URL Variable 1:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="task_' . $option->value . '1" value="' . $params->get($option->value . '_task1') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" nowrap="" align="right" width="100">';
			$html[] = JText::_( 'Variable Value 1:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="value_' . $option->value . '1" value="' . $params->get($option->value . '_value1') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] = '</table>';
			
			$html[] = '<strong>AND</strong>';
			
			$html[] = '<table class="admintable" cellpadding="0" cellspacing="0">';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" align="right" width="100">';
			$html[] = JText::_( 'URL Variable 2:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="task_' . $option->value . '2" value="' . $params->get($option->value . '_task2') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" nowrap="" align="right" width="100">';
			$html[] = JText::_( 'Variable Value 2:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="value_' . $option->value . '2" value="' . $params->get($option->value . '_value2') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] = '</table>';
			$html[] = '</div>';
			
			$html[] = '<strong>OR</strong>';
			
			$html[] = '<div class="jmsblockshow-pad">';
			$html[] = '<table class="admintable" cellpadding="0" cellspacing="0">';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" align="right" width="100">';
			$html[] = JText::_( 'URL Variable 1:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="task_' . $option->value . '3" value="' . $params->get($option->value . '_task3') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" nowrap="" align="right" width="100">';
			$html[] = JText::_( 'Variable Value 1:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="value_' . $option->value . '3" value="' . $params->get($option->value . '_value3') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] = '</table>';
			
			$html[] = '<strong>AND</strong>';
			
			$html[] = '<table class="admintable" cellpadding="0" cellspacing="0">';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" align="right" width="100">';
			$html[] = JText::_( 'URL Variable 2:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="task_' . $option->value . '4" value="' . $params->get($option->value . '_task4') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] =   '<tr>';
			$html[] = 		'<td class="key" nowrap="" align="right" width="100">';
			$html[] = JText::_( 'Variable Value 2:' );
			$html[] = 		'</td>';
			$html[] = 		'<td>';
			$html[] = '<input class="inputbox" type="text" name="value_' . $option->value . '4" value="' . $params->get($option->value . '_value4') . '" size="30" />';
			$html[] = 		'</td>';
			$html[] =   '</tr>';
			$html[] = '</table>';
			$html[] = '</div><!-- pad -->';
			
			$html[] = '</div><!-- jmsblockhide-show -->';
			$html[] = '</div><!-- col div -->';

			if($i%$cols == ($cols - 1)) {
				$html[] = '</div><!-- block div -->';
				$html[] = '<div class="clr"></div>';
			}
			
		}
		$html[] = '</div>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option) {

			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', (string) $option['value'], trim((string) $option), 'value', 'text', ((string) $option['disabled']=='true'));

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
