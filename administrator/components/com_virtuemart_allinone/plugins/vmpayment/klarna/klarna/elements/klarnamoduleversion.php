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
class JElementKlarnaModuleVersion extends JElement {

    /**
     * Element name
     * @access	protected
     * @var		string
     */
    var $_name = 'klarnamoduleversion';
    var $type = 'klarnamoduleversion';

    function fetchElement($name, $value, &$node, $control_name) {
	if (!class_exists('Klarna_virtuemart'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_virtuemart.php');

	return  KLARNA_MODULE_VERSION;
    }

}