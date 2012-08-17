<?php
/**
 * @version		$Id: iwl_crm.php  
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011 Infoweblink 
 * @license		GNU/GPL, see LICENSE.php
 * This component manages Subscriptions for members to access to Joomla Resource
*/
class iwl_crm {
	
	/**
	 * Constructor functions, init some parameter
	 * @param object $config
	 */
	function __construct() {
		// Do nothing	
	}
	
	function autoresponder_old($plan, $user) {
		// Create array of data to be posted
		$post_data['inf_form_xid'] = $plan->inf_form_xid;
		$post_data['inf_form_name'] = $plan->inf_form_name;
		$post_data['infusionsoft_version'] = $plan->infusionsoft_version;
		$post_data['name'] = $user->get('name');
		$post_data['from'] = $user->get('email');

		//traverse array and prepare data for posting (key1=value1)
		foreach ( $post_data as $key => $value) {
			$post_items[] = $key . '=' . $value;
		}

		//create the final string to be posted using implode()
		$post_string = implode ('&', $post_items);

		//create cURL connection
		$curl_connection = curl_init($plan->autores_url);

		//set options
		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);

		//set data to be posted
		curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

		//perform our request
		$result = curl_exec($curl_connection);

		//close the connection
		curl_close($curl_connection);
	}
	
	function autoresponder($plan, $user) {
		?>
		<form action="<?php echo $plan->crm_url; ?>" method="post" name="crmForm">
			<input type="hidden" value="<?php echo $plan->inf_form_xid; ?>" name="inf_form_xid" />
			<input type="hidden" value="<?php echo $plan->inf_form_name; ?>" name="inf_form_name" />
			<input type="hidden" value="<?php echo $plan->infusionsoft_version; ?>" name="infusionsoft_version" />			
			<input type="hidden" value="<?php echo $user->get('name'); ?>" name="inf_field_FirstName" />
			<input type="hidden" value="<?php echo $user->get('email'); ?>" name="inf_field_Email" />
			<script type="text/javascript">
				setTimeout('document.crmForm.submit()', 1000);
			</script>
		</form>
		<?php
	}
}