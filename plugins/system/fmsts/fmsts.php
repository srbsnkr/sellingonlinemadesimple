<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Joomla! Sendmail Plugin
 *
 * @package 		Joomla
 * @subpackage	System
 */

class plgSystemFmsts extends JPlugin {

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
		if(!version_compare( JVERSION, '1.6.0', 'ge' )) {
			define('FMSTS_JVERSION','15');
		} else {
			define('FMSTS_JVERSION','16');
		}
	}
	

	public function onAfterInitialise() {
		
		$key = $this->params->get('apiKey');

		if(strlen($key)) {
			if(FMSTS_JVERSION == '16') {
				$path = JPATH_ROOT . DS . 'plugins' . DS . 'system' . DS . 'fmsts' .DS. 'fmsts' .DS . 'mail.php';
			} else {
				$path = JPATH_ROOT . DS . 'plugins' . DS . 'system' . DS . 'fmsts' .DS . 'mail.php';
			}
			
			JLoader::register('JMail', $path );
			JLoader::load('JMail');

		} else {
			return JError::raiseWarning( 500, JText::_('NO_APIKEY_SPECIFIED_FOR_MAILCHIMP_STS_PLUGIN') );
		}	
	}

}
