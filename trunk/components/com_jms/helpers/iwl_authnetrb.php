<?php

/**
 * @version		$Id: iwl_authnetrb.php
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011 Infoweblink 
 * @license		GNU/GPL, see LICENSE.php
 * This component manages Subscriptions for members to access to Joomla Resource
*/

class iwl_AuthnetARB {

	/**

	 * Auth merchant ID

	 *

	 * @var string

	 */

    var $login    = null;

    /**

     * Auth transaction key 

     *

     * @var string

     */

    var $transkey = null;

    /**

     * Test or live mode

     *

     * @var boolean

     */

    var $mode     = true;

	/**

	 * Params which will be passed to authorize.net

	 *

	 * @var string

	 */

    var $params  = array();

    /**

     * Success or not

     *

     * @var boolean

     */

    var $success = false;

    /**

     * Error or not

     *

     * @var boolean

     */

    var $error   = true;	

    var $xml;

    var $response;

    var $resultCode;

    var $code;

    var $text;

    var $subscrId;

	/**

	 * Constructor function

	 *

	 * @param object $config

	 */

    function __construct($config)

    {

      	$this->mode = $config->authnet_mode;        

        $this->login = $config->x_login;

        $this->transkey = $config->x_tran_key;

        if ($this->mode)

        {

        	$this->url = "https://api.authorize.net/xml/v1/request.api";        	              

        }

        else

        {

            $this->url = "https://apitest.authorize.net/xml/v1/request.api";    

        }    	                                       

        $this->params['startDate']        = date("Y-m-d");

        $this->params['totalOccurrences'] = 9999;

        $this->params['trialOccurrences'] = 0;

        $this->params['trialAmount']      = 0.00;

    }

	/**

	 * Process payment

	 *

	 * @param int $retries Number of retries if error appear

	 */

    function process($retries = 3)

    {

        $count = 0;

        while ($count < $retries)

        {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));

            curl_setopt($ch, CURLOPT_HEADER, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml);

            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $this->response = curl_exec($ch);

            $this->parseResults();

            if ($this->resultCode === "Ok")

            {

                $this->success = true;

                $this->error   = false;

                break;

            }

            else

            {

                $this->success = false;

                $this->error   = true;

                break;

            }

            $count++;

        }

        curl_close($ch);

    }

	/**

	 * Perform a recurring payment subscription

	 *

	 */

    function createAccount()

    {

        $this->xml = "<?xml version='1.0' encoding='utf-8'?>

          <ARBCreateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>

              <merchantAuthentication>

                  <name>" . $this->login . "</name>

                  <transactionKey>" . $this->transkey . "</transactionKey>

              </merchantAuthentication>

              <refId>" . $this->params['refID'] ."</refId>

              <subscription>

                  <name>". $this->params['subscrName'] ."</name>

                  <paymentSchedule>

                      <interval>

                          <length>". $this->params['interval_length'] ."</length>

                          <unit>". $this->params['interval_unit'] ."</unit>

                      </interval>

                      <startDate>" . $this->params['startDate'] . "</startDate>

                      <totalOccurrences>". $this->params['totalOccurrences'] .

                      "</totalOccurrences>

                      <trialOccurrences>". $this->params['trialOccurrences'] .

                      "</trialOccurrences>

                  </paymentSchedule>

                  <amount>". $this->params['amount'] ."</amount>

                  <trialAmount>" . $this->params['trialAmount'] . "</trialAmount>

                  <payment>

                      <creditCard>

                          <cardNumber>" . $this->params['cardNumber'] . "</cardNumber>

                          <expirationDate>" . $this->params['expirationDate'] .

                          "</expirationDate>

                      </creditCard>

                  </payment>

                  <billTo>

                      <firstName>". $this->params['firstName'] . "</firstName>

                      <lastName>" . $this->params['lastName'] . "</lastName>

                  </billTo>

              </subscription>

          </ARBCreateSubscriptionRequest>";

        $this->process();

    }

	/**

	 * Set paramter

	 *

	 * @param string $field

	 * @param string $value

	 */

    function setParameter($field = "", $value = null)

    {

        $field = (is_string($field)) ? trim($field) : $field;

        $value = (is_string($value)) ? trim($value) : $value;

        $this->params[$field] = $value;

    }

	/**

	 * Parse the xml to get the necessary information

	 *

	 */

    function parseResults()

    {

        $this->resultCode = $this->parseXML('<resultCode>', '</resultCode>');

        $this->code       = $this->parseXML('<code>', '</code>');

        $this->text       = $this->parseXML('<text>', '</text>');

        $this->subscrId   = $this->parseXML('<subscriptionId>', '</subscriptionId>');

    }

	/**

	 * Parse xml to get the start and end code

	 *

	 * @param int $start

	 * @param int $end

	 * @return string

	 */

    function ParseXML($start, $end)

    {

        return preg_replace('|^.*?'.$start.'(.*?)'.$end.'.*?$|i', '$1', substr($this->response, 335));

    }



    function getSubscriberID()

    {

        return $this->subscrId;

    }



    function isSuccessful()

    {

        return $this->success;

    }



    function isError()

    {

        return $this->error;

    }

    /**

     * Processs payment 

     *

     * @param string $data

     * @return unknown

     */        

    function processPayment($data) {

    	foreach ($data as $key => $value) {

    		$this->setParameter($key, $value);	

    	}

    	$this->createAccount();

    	if($this->success){

        	JRequest::setVar('layout', 'complete');

        	JRequest::setVar('plan_id', $data['plan_id']);

        	return true;

        }

        else{

        	JRequest::setVar('layout', 'failure');

        	JRequest::setVar('reason', $this->text);

        	return false;

        }       

    }     

}

?>