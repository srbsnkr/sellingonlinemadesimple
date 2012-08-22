<?php // No direct access
/**
* @package 		ezTestimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

class TableTestimonials extends JTable
{
	var $id = null;
	var $jomid = null;
	var $fullName = null;
	var $email =  null;
	var $location =  null;
	var $aboutauthor =  null;
	var $website =  null;
	var $message_summary =  null;
	var $message_long =  null;
	var $image_name =  null;
	var $added_date =  null;
	var $rating =  null;
	var $approved = null;

	function TableTestimonials(& $db)
	{
		parent::__construct('#__testimonials', 'id', $db);
	}

}