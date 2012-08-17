<?php
/**
 * @version		$Id: iwl_aweber.php  
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011 Infoweblink 
 * @license		GNU/GPL, see LICENSE.php
 * This component manages Subscriptions for members to access to Joomla Resource
*/
class iwl_aweber {
	
	/**
	 * Constructor functions, init some parameter
	 * @param object $config
	 */
	function __construct() {
		// Do nothing	
	}
	
	function autoresponder_old($plan, $user) {
		// Create array of data to be posted
		$post_data['meta_split_id'] = '';
		$post_data['unit'] = $plan->autores_list;
		$post_data['redirect'] = $plan->autores_redirect;
		$post_data['meta_adtracking'] = '';
		$post_data['meta_message'] = '1';
		$post_data['meta_required'] = 'from';
		$post_data['meta_forward_vars'] = '0';
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
		<form action="<?php echo $plan->autores_url; ?>" method="post" name="aweberForm">
			<input type="hidden" value="" name="meta_split_id" />
			<input type="hidden" value="<?php echo $plan->autores_list; ?>" name="unit" />
			<input type="hidden" value="<?php echo $plan->autores_redirect; ?>" name="redirect" />
			<input type="hidden" value="" name="meta_adtracking" />
			<input type="hidden" value="1" name="meta_message" />
			<input type="hidden" value="from" name="meta_required" />
			<input type="hidden" value="0" name="meta_forward_vars" />
			<input type="hidden" value="<?php echo $user->get('name'); ?>" name="name" />
			<input type="hidden" value="<?php echo $user->get('email'); ?>" name="from" />
			<script type="text/javascript">
				setTimeout('document.aweberForm.submit()', 1000);
			</script>
		</form>
		<?php
	}
}