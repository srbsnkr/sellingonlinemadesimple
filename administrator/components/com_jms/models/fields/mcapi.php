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

defined('_JEXEC') or die();

// Joomla 1.6 ?
class JFormFieldMcapi extends JFormField
{
	
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Mcapi';

	function getInput()
	{
		$app = JFactory::getApplication();
		require_once JPATH_SITE.'/components/com_jms/helpers/MCAPI.class.php';
		require_once JPATH_SITE.'/components/com_jms/helpers/MCauth.php';
			
		$MCauth = new MCauth();
		
		// Get the component config/params object.
		$params = JComponentHelper::getParams('com_joomailermailchimpintegration');	
		$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		$api_key = $params->get( $paramsPrefix.'MCapi' );
		
		if ($MCauth->MCauth()) {
		
			$text = '<div style="margin: 8px 0; float: left;">' . $api_key . '</div>';
			
		} else {
			
			$text 	 = '<div style="margin: 8px 0; float: left;">';
			$text	.= '<span style="color: red;">';
			$text	.= 'No valid API Key or Joomlamailer Extension is not installed. ';
			$text	.= '</span>';
			$text	.= 'If Joomlamailer is installed';
			$text	.= '<a href="index.php?option=com_joomailermailchimpintegration">';
			$text	.= ' click here ';
			$text	.= '</a>';
			$text	.= 'to edit Configurations.<br />';
			$text	.= '</div>';
		}

	    return $text;
	}
	
}


