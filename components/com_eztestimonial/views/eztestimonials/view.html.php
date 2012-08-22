<?php

/**
* @package 		Testimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
class testimonialVieweztestimonials extends JView
{
    function display($tpl = null)
    { 
		$lists['search']=  JRequest::getVar( "search");
		$items =& $this->get('Data');	
		$pagination =& $this->get('Pagination');
		$totalcusers = $this->get('Total');
		$this->state = $this->get('State');
		$crd = $this->get('crdt');;
		$this->assignRef('items', $items);	
		$this->assignRef('pagination', $pagination);
		$this->assignRef('crdt', $crd);
		$this->assignRef('delete', $pagination);
		$this->assignRef('lists',	$lists);
		$this->assignRef('totalcusers', $totalcusers);
		parent::display($tpl);
    }

}

