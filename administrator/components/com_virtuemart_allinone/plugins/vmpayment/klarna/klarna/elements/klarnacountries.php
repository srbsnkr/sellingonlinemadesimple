<?php  defined('_JEXEC') or die();

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
/*
 * This class is used by VirtueMart Payment or Shipping Plugins
 * which uses JParameter
 * So It should be an extension of JElement
 * Those plugins cannot be configured througth the Plugin Manager anyway.
 */
class JElementKlarnaCountries extends JElement {

    /**
     * Element name
     * @access	protected
     * @var		string
     */
    var $_name = 'klarnacountries';
    var $type = 'klarnacountries';

    function fetchElement($name, $value, &$node, $control_name) {
	$db = JFactory::getDBO();
	$klarna_countries= '"se", "de", "dk", "nl", "fi", "no"';
	$query = 'SELECT `country_3_code` AS value, `country_name` AS text FROM `#__virtuemart_countries`
               		WHERE `published` = 1 AND `country_2_code` IN ('.$klarna_countries.') ORDER BY `country_name` ASC ';

	$db->setQuery($query);
	$fields = $db->loadObjectList();

	$class = 'multiple="true" size="10"';
	return JHTML::_('select.genericlist', $fields, $control_name . '[' . $name . '][]', $class, 'value', 'text', $value, $control_name . $name);
    }

}