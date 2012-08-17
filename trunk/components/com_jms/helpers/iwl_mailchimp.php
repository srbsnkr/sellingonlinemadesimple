<?php
/**
 * @version		$Id: iwl_mailchimp.php  
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011 Infoweblink 
 * @license		GNU/GPL, see LICENSE.php
 * This component manages Subscriptions for members to access to Joomla Resource
*/
class iwl_mailchimp {
	
	/**
	 * Constructor functions, init some parameter
	 * @param object $config
	 */
	function __construct() {
		// Do nothing	
	}
	
	function autoresponder($plan, $user) {
		
		$plan_mc_listid = $plan->plan_mc_listid;
		$plan_mc_groupid = $plan->plan_mc_groupid;
		$name = $user->get('name');
		$email = $user->get('email');
		
		$explodename = explode(' ', "$name ");
		$fname = $explodename[0];
		$lname = $explodename[1];
				
		$merge_vars = array('FNAME'=>$fname,'LNAME'=>$lname, 'INTERESTS'=>$plan_mc_groupid);
				
		require_once JPATH_COMPONENT.'/helpers/MCAPI.class.php';
						
		// Get the component config/params object.
		$params = JComponentHelper::getParams('com_joomailermailchimpintegration');	
		$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		$api_key = $params->get( $paramsPrefix.'MCapi' );
				
		$api = new MCAPI($api_key);

		$api->listSubscribe($plan_mc_listid, $email, $merge_vars);
		
		// check for duplicates
		$db = & JFactory::getDBO();
		$sql = 'SELECT COUNT(*)' .
					' FROM #__joomailermailchimpintegration' .
					' WHERE email = "' . $email . '" AND listid = "' . $plan_mc_listid . '"'
					;		
		$db->setQuery($sql);
		$count = $db->loadResult();
		
		if ($count == 0) {
		// add to joomailermailchimpintegration DB
		$sql = 'INSERT INTO #__joomailermailchimpintegration' .
				' VALUES ("",' . $user->get('id') . ',"' . $email . '","' . $plan_mc_listid .'")'
				;
		$db->setQuery($sql);
		$db->query();
		}
		//joomailermailchimpintegration DB
	}
}
?>