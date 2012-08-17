<?php
/**
 * @version		$Id: iwl_paypal.php  
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011 Infoweblink 
 * @license		GNU/GPL, see LICENSE.php
 * This component manages Subscriptions for members to access to Joomla Resource
*/
class iwl_paypal {
	/**
	 * Paypal mode
	 * @var boolean live mode : true, test mode : false
	 */
	var $_mode = 0;
	
	/**
	 * Paypal url
	 * @var string
	 */
	var $_url = null;

	/**
	 * Array of params will be posted to server
	 * @var string
	 */
	var $_params = array();
	
	/**
	 * Array containing data posted from paypal to our server
	 * @var array
	 */
	var $_data = array();
	
	/**
	 * Constructor functions, init some parameter
	 * @param object $config
	 */
	function __construct($config) {
		$this->_mode = $config->get('paypal_mode');
		if ($this->_mode)
			$this->_url = 'https://www.paypal.com/cgi-bin/webscr';
		else
			$this->_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';	
		$this->setParam('rm', 2);
		$this->setParam('cmd', '_xclick');
	}
	
	/**
	 * Set param value
	 * @param string $name
	 * @param string $val
	 */
	function setParam($name, $val) {
		$this->_params[$name] = $val;
	}

	/**
	 * Setup payment parameter
	 * @param array $params
	 */
	function setParams($params) {
		foreach ($params as $key => $value) {
			$this->_params[$key] = $value;
		}
	}

	/**
	 * Submit post to paypal server
	 *
	 */	
	function submitPost() {
	?>
		<div class="contentheading"><?php echo  JText::_('COM_JMS_WAIT_PAYPAL'); ?></div>
		<form method="post" action="<?php echo $this->_url; ?>" name="formsubscr" id="formsubscr">
			<?php
				foreach ($this->_params as $key=>$val) {
					echo '<input type="hidden" name="'.$key.'" value="'.$val.'" />';
					echo "\n";
				}
			?>
			<script type="text/javascript">
				function redirect() {
					document.formsubscr.submit();
				}
				setTimeout('redirect()', 5000);
			</script>
		</form>
	<?php
	}

	/**
	 * Validate the post data from paypal to our server
	 *
	 * @return string
	 */
	function _validate() {
		$errNum = "";
	   	$errStr = "";
	    $urlParsed = parse_url($this->_url);
	    $host = $urlParsed['host'];
	    $path = $urlParsed['path'];        
	    $postString = ''; 
	    $response = '';   
	    foreach ($_POST as $key=>$value) { 
	       $this->_data[$key] = $value;
	       $postString .= $key.'='.urlencode($value).'&';
	    }
		$postString .='cmd=_notify-validate';
		$fp = fsockopen($host , '80', $errNum, $errStr, 30);
		if(!$fp) {
			return false;
		} else {
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($postString)."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $postString . "\r\n\r\n");
			while(!feof($fp)) {
				$response .= fgets($fp, 1024);
			}
			fclose($fp);
		}
		if (eregi("VERIFIED", $response)) 	         
			return true;
		else 
			return false;
	}

	/**
	 * Method to process payment
	 *
	 * @param datetime $maxExpDate
	 * @return boolean
	 */
	function processPayment($period, $periodType, $maxExpDate) {
		$ret = $this->_validate();
		if ($ret) {
			$id = $this->_data['custom'];
   			$transactionId = $this->_data['txn_id'];
   			$amount = $this->_data['mc_gross'];
   			if ($amount < 0)
   				return false;
   			$row =  JTable::getInstance('subscr', 'JmsTable');
   			$row->load($id);
   			$row->transaction_id = $transactionId;
   			$row->created = gmdate('Y-m-d H:i:s');
			$row->expired = $this->_getExpiredDate($period, $periodType, $maxExpDate);
   			$row->state = 1;
   			$row->store();
   			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Method to process recurring payment
	 *
	 * @return
	 */
	function processRecurringPayment($period, $periodType, $accessLimit, $maxExpDate) {
		$ret = $this->_validate();				
		if ($ret) {
			$id = $this->_data['custom'];						
   			$transactionId = $this->_data['txn_id'];
   			$amount = $this->_data['mc_gross'];
   			$txnType = $this->_data['txn_type'];
   			$subscrId = $this->_data['subscr_id'];
   			JRequest::setVar('txn_type', $txnType);   			
   			if ($amount < 0)
   				return false;
   			$row =  JTable::getInstance('subscr', 'JmsTable');
   			$row->load($id);   			
   			switch ($txnType) {
   				case 'subscr_signup':
   					$row->created = gmdate('Y-m-d H:i:s');
   					$row->expired = $this->_getExpiredDate($period, $periodType, $maxExpDate);
		   			$row->transaction_id = $transactionId;		   			
		   			$row->state = 1;
		   			$row->payment_made = 1;
		   			$row->subscr_id = $subscrId;
		   			break;
   				case 'subscr_payment':
   					$row->expired = $this->_getExpiredDate($period, $periodType, $row->expired);
   					$row->access_limit = $row->access_limit + $accessLimit;
   					$row->payment_made = $row->payment_made + 1;
   					if ($row->payment_made > 1) {
   						$row->price = $row->price + $amount;
   					}
   					break;   				 
   			}			
			$row->store();			
   			return true;
		} else {
			return false;
		}		     
	}
	
	/**
	 * Private method to get expired date
	 *
	 * @param int $periodType
	 * @param datetime $maxExpDate
	 * @return datetime
	 */
	function _getExpiredDate($period, $periodType, $maxExpDate) {
		if ( $periodType == 1 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm'), (int)JHTML::date($maxExpDate, 'd') + $period, (int)JHTML::date($maxExpDate, 'Y')));
		} else if ( $periodType == 2 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm'), (int)JHTML::date($maxExpDate, 'd') + $period * 7, (int)JHTML::date($maxExpDate, 'Y')));
		} else if ( $periodType == 3 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm') + $period, (int)JHTML::date($maxExpDate, 'd'), (int)JHTML::date($maxExpDate, 'Y')));
		} else if ( $periodType == 4 ) {
			$expired = date('Y-m-d H:i:s', mktime(JHTML::date($maxExpDate, 'H'), (int)JHTML::date($maxExpDate, 'M'), (int)JHTML::date($maxExpDate, 'S'), (int)JHTML::date($maxExpDate, 'm'), (int)JHTML::date($maxExpDate, 'd'), (int)JHTML::date($maxExpDate, 'Y') + $period));
		} else if ( $periodType == 5 ) {
			$expired = '3009-12-31 23:59:59';
		}
		return $expired;
	}
}