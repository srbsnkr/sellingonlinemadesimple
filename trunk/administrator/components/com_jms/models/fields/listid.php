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
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class JFormFieldListid extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Listid';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		
		$app = JFactory::getApplication();
		require_once JPATH_SITE.'/components/com_jms/helpers/MCAPI.class.php';
		require_once JPATH_SITE.'/components/com_jms/helpers/MCauth.php';
			
		$MCauth = new MCauth();
		
		// Get the component config/params object.
		$params = JComponentHelper::getParams('com_joomailermailchimpintegration');	
		$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		$api_key = $params->get( $paramsPrefix.'MCapi' );
					
		if ($MCauth->MCauth()) {
										
			$api = new MCAPI($api_key);
								
			$retval = $api->lists();
			
			if (is_array($retval['data'])) {
				foreach ($retval['data'] as $list) {
					$options[] = JHTML::_('select.option', $list['id'], JText::_($list['name']));
				}
			}
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
