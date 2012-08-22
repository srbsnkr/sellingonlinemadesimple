<?php

/**

* @package 		ezTestimonial Component

* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.

* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php

* @author		Saran Chamling (saaraan@gmail.com)

*/ 

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' ); 

// Require the base controller

require_once( JPATH_COMPONENT.DS.'controller.php' );
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

// Create the controller
$classname    = 'testimonialController';
$controller   = new $classname( );

$jlang =& JFactory::getLanguage();
$jlang->load('com_eztestimonial', JPATH_COMPONENT, null, true);

// Perform the Request task
$controller->execute( JRequest::getWord( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

?>