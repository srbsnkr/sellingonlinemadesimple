<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

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

define('KLARNA_MODULE_VERSION', '5.0.3');
if (!class_exists('Klarna'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
class Klarna_virtuemart extends Klarna {

    public function __construct() {
        $this->VERSION = 'PHP:VirtueMart2:'.KLARNA_MODULE_VERSION.':r74';
        Klarna::$debug =  false;
    }
}

