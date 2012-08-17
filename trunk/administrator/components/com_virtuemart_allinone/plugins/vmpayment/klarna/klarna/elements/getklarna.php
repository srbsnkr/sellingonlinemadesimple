<?php
/**
 * @version $Id$
 *
 * @author Valérie Isaksen
 * @package VirtueMart

 * @copyright Copyright (C) iStraxx - All rights reserved.
 * @license istraxx_license.txt Proprietary License. This code belongs to istraxx UG (haftungsbeschränkt)
 * You are not allowed to distribute or sell this code.
 * You are not allowed to modify this code
 */
defined ('JPATH_BASE') or die();

/**
 * Renders a label element
 */
if (JVM_VERSION === 2) {
	require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
	if (!class_exists ('KlarnaHandler')) {
		require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
	}
} else {
	require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
	if (!class_exists ('KlarnaHandler')) {
		require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
	}
}

class JElementGetKlarna extends JElement {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'getKlarna';

	function fetchElement ($name, $value, &$node, $control_name) {

		$jlang = JFactory::getLanguage ();
		$lang = $jlang->getTag();
		$langArray = explode ("-", $lang);
		$lang = strtolower ($langArray[1]);
		$countriesData = KlarnaHandler::countriesData ();
		$signLang = "en";
		foreach ($countriesData as $countryData) {
			if ($countryData['country_code'] == $lang) {
				$signLang = $lang;
				break;
			}
		}
		$logo = '<a href="https://merchants.klarna.com/signup?locale=' . $signLang . '&partner_id=7829355537eae268a17667c199e7c7662d3391f7" target="_blank">
	             <img src="' . JURI::root () . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/get_klarna_now.png" /></a> ';
		return $logo;
	}

}