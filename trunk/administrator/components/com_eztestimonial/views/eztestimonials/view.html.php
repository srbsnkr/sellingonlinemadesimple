<?php
/**
* @package 		ezTestimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
 */
 
class AdminTestimonVieweztestimonials extends JView
{
    function display($tpl = null)
    { 
		$lists['search']=  JRequest::getVar( "search");
		// Get data from the model
		$items =& $this->get('Data');	
		$pagination =& $this->get('Pagination');
		$totalcusers = $this->get('Total');
		$this->state = $this->get('State');
 		// push data into the template
		$this->assignRef('items', $items);	
		$this->assignRef('pagination', $pagination);
		$this->assignRef('delete', $pagination);
		$this->assignRef('lists',	$lists);
		$this->assignRef('totalcusers', $totalcusers);

		JToolBarHelper::title( JText::_( 'Testimonials' ), 'home.png' );	
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();
		JToolBarHelper::deleteList();
		JToolBarHelper::preferences( 'com_eztestimonial',500,750 );
		parent::display($tpl);
    }
}
