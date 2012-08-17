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

class JElementKlarnaCountryLogo extends JElement {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'KlarnacountryLogo';

	function fetchElement ($name, $value, &$node, $control_name) {

		$flagImg = JURI::root (TRUE) . '/administrator/components/com_virtuemart/assets/images/flag/' . strtolower ($value) . '.png';
		return '<strong>'.JText::_ ('VMPAYMENT_KLARNA_CONF_SETTINGS_' . $value) . '</strong><img style="margin-left: 5px;margin-top: 15px;" src="' . $flagImg
			. '" />';

	}
}