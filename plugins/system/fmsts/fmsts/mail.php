<?php

/**
 * @author Daniel Dimitrov - http://compojoom.com
 * 
 * This file is part of Freakedout Mailchimp STS integration.
 * It is a modified version of the standard Joomla JMail class
 *
 * Fmsts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fmsts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fmsts.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @version		$Id: mail.php 14401 2010-01-26 14:10:00Z louis $
 * @package		Joomla.Framework
 * @subpackage	Mail
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('phpmailer.phpmailer');
jimport('joomla.mail.helper');

/**
 * E-Mail Class.  Provides a common interface to send e-mail from the Joomla! Framework
 *
 * @package 	Joomla.Framework
 * @subpackage		Mail
 * @since		1.5
 */
class JMail extends PHPMailer {

	private $apiKey = null;
	public $to = array();
	public $cc = array();
	public $bcc = array();
	public $attachment = array();

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		$plugin = JPluginHelper::getPlugin('system', 'fmsts');
		$this->params = new JParameter($plugin->params);

		$this->apiKey = $this->params->get('apiKey');

		// phpmailer has an issue using the relative path for it's language files
		if (FMSTS_JVERSION == '16') {
			$this->SetLanguage('joomla', JPATH_LIBRARIES . '/phpmailer/language/');
		} else {
			$this->SetLanguage('joomla', JPATH_LIBRARIES . DS . 'phpmailer' . DS . 'language' . DS);
		}
	}

	/**
	 * Returns a reference to a global e-mail object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $mail =& JMail::getInstance();</pre>
	 *
	 * NOTE: If you need an instance to use that does not have the global configuration
	 * values, use an id string that is not 'Joomla'.
	 *
	 * @static
	 * @access public
	 * @param string $id The id string for the JMail instance [optional]
	 * @return object The global JMail object
	 * @since 1.5
	 */
	public function & getInstance($id = 'Joomla') {
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$id])) {
			$instances[$id] = new JMail();
		}


		return $instances[$id];
	}

	/**
	 * @return mixed True if successful, a JError object otherwise
	 */
	public function Send() {

		if (!$this->isDailyQuotaExeeded() && !(count($this->cc) || count($this->bcc) || count($this->attachment))) {
			return $this->amazonSend();
		} else {
			return $this->phpMailerSend();
		}
	}

	private function isDailyQuotaExeeded() {
		$url = $this->getStsUrl() . 'GetSendQuota?apikey=' . $this->apiKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($this->params->get('secure')) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
		}

		$result = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($result);

		if ((int) $data->SentLast24Hours >= (int) $data->Max24HourSend) {
			jimport('joomla.error.log');
			$log = &JLog::getInstance('plg_fmsts.log.php');
			$log->addEntry(array('comment' => 'Daily message quota exceeded. Quota: ' . (int) $data->Max24HourSend . ' Sent: ' . (int) $data->SentLast24Hours));

			return true;
		}
		return false;
	}

	private function amazonSend() {
		$replyTo = array();
		if (isset($this->ReplyTo[0])) {
			$replyTo = $this->ReplyTo[0][0];
		}

		$message = array(
			'subject' => $this->Subject,
			'from_name' => $this->FromName,
			'from_email' => $this->From,
			'reply_to' => $replyTo
		);

		if ($this->ContentType == 'text/plain') {
			$message['text'] = $this->Body;
			$message['autogen_html'] = false;
		} else {
			$message['html'] = $this->Body;
		}

		$params = array(
			'apikey' => $this->apiKey,
			'track_opens' => true,
			'track_clicks' => true
		);
		$url = $this->getStsUrl() . 'SendEmail';

		$recepients = array_chunk($this->to, 50);

		foreach ($recepients as $k => $v) {
			$toEmails = array();
			$toNames = array();
			foreach ($v as $key => $value) {
				$toEmails[] = $value[0];
				$toNames[] = $value[1];
			}

			$message['to_email'] = $toEmails;
			$message['to_names'] = $toNames;

			$params['message'] = $message;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

			if ($this->params->get('secure')) {
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
			}

			$result = curl_exec($ch);

			curl_close($ch);
			$data = json_decode($result);

			if ((isset($data->http_code) && $data->http_code == 400) &&
					(isset($data->aws_code) && $data->aws_code == 'Throttling')) {
				jimport('joomla.error.log');
				$log = &JLog::getInstance('plg_fmsts.log.php');
				$log->addEntry(array('comment' => 'Daily message quota exceeded. We will try to send the message using phpmailer.'));

				return $this->phpMailerSend();
			}

			if ($data->status != 'sent') {
				return JError::raiseNotice(500, JText::_($data->status . $data->message));
			}
		}

		return true;
	}

	private function phpMailerSend() {
		if (( $this->Mailer == 'mail' ) && !function_exists('mail')) {
			if (FMSTS_JVERSION == '16') {
				return JError::raiseNotice(500, JText::_('JLIB_MAIL_FUNCTION_DISABLED'));
			} else {
				return JError::raiseNotice(500, JText::_('MAIL_FUNCTION_DISABLED'));
			}
		}

		@ $result = parent::Send();

		if ($result == false) {
			// TODO: Set an appropriate error number
			$result = & JError::raiseNotice(500, JText::_($this->ErrorInfo));
		}
		return $result;
	}

	/**
	 * Set the E-Mail sender
	 *
	 * @access public
	 * @param array $from E-Mail address and Name of sender
	 * 		<pre>
	 * 			array( [0] => E-Mail Address [1] => Name )
	 * 		</pre>
	 * @return void
	 * @since 1.5
	 */
	public function setSender($from) {
		// If $from is an array we assume it has an address and a name
		if (is_array($from)) {
			$this->From = JMailHelper::cleanLine($from[0]);
			$this->FromName = JMailHelper::cleanLine($from[1]);
			// If it is a string we assume it is just the address
		} elseif (is_string($from)) {
			$this->From = JMailHelper::cleanLine($from);
			// If it is neither, we throw a warning
		} else {
			if (FMSTS_JVERSION == '16') {
				JError::raiseWarning(0, JText::sprintf('JLIB_MAIL_INVALID_EMAIL_SENDER', $from));
			} else {
				JError::raiseWarning(0, "JMail::  Invalid E-Mail Sender: $from", "JMail::setSender($from)");
			}
		}
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * Set the E-Mail subject
	 *
	 * @access public
	 * @param string $subject Subject of the e-mail
	 * @return void
	 * @since 1.5
	 */
	public function setSubject($subject) {
		$this->Subject = JMailHelper::cleanLine($subject);
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * Set the E-Mail body
	 *
	 * @access public
	 * @param string $content Body of the e-mail
	 * @return void
	 * @since 1.5
	 */
	public function setBody($content) {
		/*
		 * Filter the Body
		 * TODO: Check for XSS
		 */
		$this->Body = JMailHelper::cleanText($content);
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * Add recipients to the email
	 *
	 * @access public
	 * @param mixed $recipient Either a string or array of strings [e-mail address(es)]
	 * @return void
	 * @since 1.5
	 */
	public function addRecipient($recipient) {
		// If the recipient is an aray, add each recipient... otherwise just add the one
		if (is_array($recipient)) {
			foreach ($recipient as $to) {
				$to = JMailHelper::cleanLine($to);
				$this->AddAddress($to);

				if (FMSTS_JVERSION == '16') {
					$this->AddAnAddress('to', $to, '');
				}
			}
		} else {
			$recipient = JMailHelper::cleanLine($recipient);
			$this->AddAddress($recipient);

			if (FMSTS_JVERSION == '16') {
				$this->AddAnAddress('to', $recipient, '');
			}
		}
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * This method is not implemented in Mailchimp's STS, so we just log the attempt to send a CC
	 *
	 * @access public
	 * @param mixed $cc Either a string or array of strings [e-mail address(es)]
	 * @return void
	 * @since 1.5
	 */
	public function addCC($cc) {
		$message = 'the addCC method is not supported by the mailchip\'s STS API. We will send this mail with PHPMailer';
		//If the carbon copy recipient is an aray, add each recipient... otherwise just add the one
		if (isset($cc)) {
			if (is_array($cc)) {
				foreach ($cc as $to) {
					$to = JMailHelper::cleanLine($to);
					parent::AddCC($to);

					if (FMSTS_JVERSION == '16') {
						$this->AddAnAddress('cc', $to, '');
					}
					$this->writeToLog($message);
				}
			} else {
				$cc = JMailHelper::cleanLine($cc);
				parent::AddCC($cc);

				if (FMSTS_JVERSION == '16') {
					$this->AddAnAddress('cc', $cc, '');
				}
				$this->writeToLog($message);
			}
		}
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * This method is not implemented in Mailchimp's STS, so we just log the attempt to send a bcc
	 *
	 * @access public
	 * @param mixed $cc Either a string or array of strings [e-mail address(es)]
	 * @return void
	 * @since 1.5
	 */
	public function addBCC($bcc) {
		$message = 'the addBCC method is not supported by the mailchip\'s STS API. We will send this mail with PHPMailer';
		// If the blind carbon copy recipient is an aray, add each recipient... otherwise just add the one
		if (isset($bcc)) {
			if (is_array($bcc)) {
				foreach ($bcc as $to) {
					$to = JMailHelper::cleanLine($to);
					parent::AddBCC($to);
					if (FMSTS_JVERSION == '16') {
						$this->AddAnAddress('bcc', $to, '');
					}
					$this->writeToLog($message);
				}
			} else {
				$bcc = JMailHelper::cleanLine($bcc);
				parent::AddBCC($bcc);
				if (FMSTS_JVERSION == '16') {
					$this->AddAnAddress('bcc', $to, '');
				}
				$this->writeToLog($message);
			}
		}
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * This function is a copy of PHPMailer 5.1 addAttachment function
	 * we need this function here, because the attachment array is declared private
	 * and we can't access it in this class to determine whether we should use
	 * mailchimp's sts api or phpmailer...
	 * 
	 * Adds an attachment from a path on the filesystem.
	 * Returns false if the file could not be found
	 * or accessed.
	 * @param string $path Path to the attachment.
	 * @param string $name Overrides the attachment name.
	 * @param string $encoding File encoding (see $Encoding).
	 * @param string $type File extension (MIME) type.
	 * @return bool
	 */
	public function AddAttachmentJMail($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
		try {
			if (!@is_file($path)) {
				throw new phpmailerException($this->Lang('file_access') . $path, self::STOP_CONTINUE);
			}
			$filename = basename($path);
			if ($name == '') {
				$name = $filename;
			}

			$this->attachment[] = array(
				0 => $path,
				1 => $filename,
				2 => $name,
				3 => $encoding,
				4 => $type,
				5 => false, // isStringAttachment
				6 => 'attachment',
				7 => 0
			);
		} catch (phpmailerException $e) {
			$this->SetError($e->getMessage());
			if ($this->exceptions) {
				throw $e;
			}
			echo $e->getMessage() . "\n";
			if ($e->getCode() == self::STOP_CRITICAL) {
				return false;
			}
		}
		return true;
	}

	/**
	 * This function is a copy of the PHPMailer 5.1 function AddAnAddress
	 * We need to call it also in this class, because otherwise we don't have
	 * access to the private to, cc and bcc variables... We don't need to change
	 * the method name as phpmailer has declared AddAnAddress as private and it
	 * is in different scope.
	 * 
	 * Adds an address to one of the recipient arrays
	 * Addresses that have been added already return false, but do not throw exceptions
	 * @param string $kind One of 'to', 'cc', 'bcc', 'ReplyTo'
	 * @param string $address The email address to send to
	 * @param string $name
	 * @return boolean true on success, false if address already used or invalid in some way
	 * @access private
	 */
	private function AddAnAddress($kind, $address, $name = '') {
		if (!preg_match('/^(to|cc|bcc|ReplyTo)$/', $kind)) {
			echo 'Invalid recipient array: ' . kind;
			return false;
		}
		$address = trim($address);
		$name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
		if (!self::ValidateAddress($address)) {
			$this->SetError($this->Lang('invalid_address') . ': ' . $address);
			if ($this->exceptions) {
				throw new phpmailerException($this->Lang('invalid_address') . ': ' . $address);
			}
			echo $this->Lang('invalid_address') . ': ' . $address;
			return false;
		}
		if ($kind != 'ReplyTo') {
			if (!isset($this->all_recipients[strtolower($address)])) {
				array_push($this->$kind, array($address, $name));
				$this->all_recipients[strtolower($address)] = true;
				return true;
			}
		} else {
			if (!array_key_exists(strtolower($address), $this->ReplyTo)) {
				$this->ReplyTo[strtolower($address)] = array($address, $name);
				return true;
			}
		}
		return false;
	}

	/**
	 * This method is not implemented in Mailchimp's STS, so we just log the attempt to add an attachment
	 *
	 * @access public
	 * @param mixed $attachment Either a string or array of strings [filenames]
	 * @return void
	 * @since 1.5
	 */
	public function addAttachment($attachment) {
		$message = 'The addAttachment method is not supported by Mailchimp\'s STS API. We will send this mail using PHPMailer';
		// If the file attachments is an aray, add each file... otherwise just add the one
		if (isset($attachment)) {
			if (is_array($attachment)) {
				foreach ($attachment as $file) {
					parent::AddAttachment($file);
					$this->AddAttachmentJMail($file);

					$this->writeToLog($message);
				}
			} else {
				parent::AddAttachment($file);
				$this->AddAttachmentJMail($file);
				$this->writeToLog($message);
			}
		}
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * Add Reply to e-mail address(es) to the e-mail
	 *
	 * @access public
	 * @param array $reply Either an array or multi-array of form
	 * 		<pre>
	 * 			array( [0] => E-Mail Address [1] => Name )
	 * 		</pre>
	 * @return void
	 * @since 1.5
	 */
	public function addReplyTo($replyto) {
		// Take care of reply email addresses
		if (is_array($replyto[0])) {
			foreach ($replyto as $to) {
				$to0 = JMailHelper::cleanLine($to[0]);
				$to1 = JMailHelper::cleanLine($to[1]);
				parent::AddReplyTo($to0, $to1);
			}
		} else {
			$replyto0 = JMailHelper::cleanLine($replyto[0]);
			$replyto1 = JMailHelper::cleanLine($replyto[1]);
			parent::AddReplyTo($replyto0, $replyto1);
		}
		
		if (FMSTS_JVERSION == '16') {
			return $this;
		}
	}

	/**
	 * Use sendmail for sending the e-mail
	 *
	 * @access public
	 * @param string $sendmail Path to sendmail [optional]
	 * @return boolean True on success
	 * @since 1.5
	 */
	public function useSendmail($sendmail = null) {
		$this->Sendmail = $sendmail;

		if (!empty($this->Sendmail)) {
			$this->IsSendmail();
			return true;
		} else {
			$this->IsMail();
			return false;
		}
	}

	/**
	 * Use SMTP for sending the e-mail
	 *
	 * @access public
	 * @param string $auth SMTP Authentication [optional]
	 * @param string $host SMTP Host [optional]
	 * @param string $user SMTP Username [optional]
	 * @param string $pass SMTP Password [optional]
	 * @param string $secure SMTP Secure ssl,tls [optinal]
	 * @param string $port SMTP Port [optional]
	 * @return boolean True on success
	 * @since 1.5
	 */
	public function useSMTP($auth = null, $host = null, $user = null, $pass = null, $secure = null, $port = 25) {
		$this->SMTPAuth = $auth;
		$this->Host = $host;
		$this->Username = $user;
		$this->Password = $pass;
		$this->Port = $port;
		if ($secure == 'ssl' || $secure == 'tls') {
			$this->SMTPSecure = $secure;
		}

		if (FMSTS_JVERSION == '16') {
			if (($this->SMTPAuth !== null && $this->Host !== null && $this->Username !== null && $this->Password !== null)
					|| ($this->SMTPAuth === null && $this->Host !== null)) {
				$this->IsSMTP();

				return true;
			} else {
				$this->IsMail();

				return false;
			}
		} else {
			if ($this->SMTPAuth !== null && $this->Host !== null && $this->Username !== null && $this->Password !== null) {
				$this->IsSMTP();
			}
		}
	}

	/**
	 *
	 * @return string - the datacenter to use from the apiKey
	 */
	private function getDataCenter() {
		$dc = "us1";
		if (strstr($this->apiKey, "-")) {
			list($key, $dc) = explode("-", $this->apiKey, 2);
			if (!$dc)
				$dc = "us1";
		}

		return $dc;
	}

	/**
	 * @return string - the url to mailchimp api
	 */
	private function getStsUrl() {
		$dc = $this->getDataCenter();
		$scheme = 'http';

		if ($this->params->get('secure')) {
			$scheme = 'https';
		}

		$url = $scheme . '://' . $dc . '.sts.mailchimp.com/1.0/';

		return $url;
	}

	/**
	 *
	 * @param type $methodName - the function name
	 * @param type $thing  - the action (bcc email, cc email, attachment) name
	 */
	public function writeToLog($message) {
		jimport('joomla.error.log');
		$log = &JLog::getInstance('plg_fmsts.log.php');
		$log->addEntry(array('comment' => $message));
	}

	/**
	 * Function to send an email
	 *
	 * @param	string	$from			From email address
	 * @param	string	$fromName		From name
	 * @param	mixed	$recipient		Recipient email address(es)
	 * @param	string	$subject		email subject
	 * @param	string	$body			Message body
	 * @param	boolean	$mode			false = plain text, true = HTML
	 * @param	mixed	$cc				CC email address(es)
	 * @param	mixed	$bcc			BCC email address(es)
	 * @param	mixed	$attachment		Attachment file name(s)
	 * @param	mixed	$replyTo		Reply to email address(es)
	 * @param	mixed	$replyToName	Reply to name(s)
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function sendMail($from, $fromName, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyTo=null, $replyToName=null) {
		$this->setSender(array($from, $fromName));
		$this->setSubject($subject);
		$this->setBody($body);

		// Are we sending the email as HTML?
		if ($mode) {
			$this->IsHTML(true);
		}

		$this->addRecipient($recipient);
		$this->addCC($cc);
		$this->addBCC($bcc);
		$this->addAttachment($attachment);

		// Take care of reply email addresses
		if (is_array($replyTo)) {
			$numReplyTo = count($replyTo);

			for ($i = 0; $i < $numReplyTo; $i++) {
				$this->addReplyTo(array($replyTo[$i], $replyToName[$i]));
			}
		} else if (isset($replyTo)) {
			$this->addReplyTo(array($replyTo, $replyToName));
		}

		return $this->Send();
	}

	/**
	 * Sends mail to administrator for approval of a user submission
	 *
	 * @param	string	$adminName	Name of administrator
	 * @param	string	$adminEmail	Email address of administrator
	 * @param	string	$email		[NOT USED TODO: Deprecate?]
	 * @param	string	$type		Type of item to approve
	 * @param	string	$title		Title of item to approve
	 * @param	string	$author		Author of item to approve
	 * @param	string	$url
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function sendAdminMail($adminName, $adminEmail, $email, $type, $title, $author, $url = null) {
		$subject = JText::sprintf('JLIB_MAIL_USER_SUBMITTED', $type);

		$message = sprintf(JText::_('JLIB_MAIL_MSG_ADMIN'), $adminName, $type, $title, $author, $url, $url, 'administrator', $type);
		$message .= JText::_('JLIB_MAIL_MSG') . "\n";

		$this->addRecipient($adminEmail);
		$this->setSubject($subject);
		$this->setBody($message);

		return $this->Send();
	}

}
