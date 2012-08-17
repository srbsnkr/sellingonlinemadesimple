<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport('joomla.application.component.controller');

/**
 * joomailermailchimpintegration Controller
 *
 * @package    joomailermailchimpintegration
 * @subpackage Controllers
 */
class joomailermailchimpintegrationsControllerjoomailermailchimpintegration extends joomailermailchimpintegrationsController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add' , 'edit' );
	}// function

	function addUsers(){
		$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=sync' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'joomailermailchimpintegration' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar( 'hidemainmenu', 1);

		parent::display();
	}// function

/*
	function save()
	{
        $listid = JRequest::getVar('id'  ,  0, 'post', 'string');
        $title  = JRequest::getVar('name',  0, 'post', 'string');
        $type   = JRequest::getVar('type',  1, 'post', 'string');

        if ( $type == 1 ) { $confirmOptIn = 'false'; } else { $confirmOptIn = 'true'; }

        $cm  = $this->cm_object();

        $clients   = $cm->userGetClients($api['anyType']);
        $client_id = $clients['anyType']['Client']['ClientID'];

        if ($listid){
        $result = $cm->listUpdate( $listid, $title, '', $confirmOptIn, '' );
        $action = JText::_( 'updated' );
        } else {
        $result = $cm->listCreate( $client_id, $title, '', $confirmOptIn, '' );
        $action = JText::_( 'created' );
        }

		if ($result['Result']['Code'] == 0) {
			$msg = JText::_( 'List' ).' '.$action;
		} else {
			$msg = JText::_( 'Error: List Could not be' ).' '.$action.'!';
		}

		$link = 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations';
		$this->setRedirect($link, $msg);
	}// function

	function remove()
	{
        $db	=& JFactory::getDBO();
        $cm = $this->cm_object();
        $model =& $this->getModel( 'joomailermailchimpintegration' );

        $listid = JRequest::getVar('listid',  0, '', 'string');

        $delete = $cm->listDelete( $listid );

        $error = false;
    if( in_array( $delete['Result']['Code'], array( '0', '100', '101', '252' ) ) ) {

				switch( $delete['Result']['Code'] ) {

                    case '0':
						$error = JText::_( 'List deleted' );
						break;

                    case '100':
						$error = JText::_( 'JM_INVALID_API_KEY' );
						break;

                    case '101':
						$error = JText::_( 'JM_INVALID_LISTID' );
						break;

                    case '252':
						$error = JText::_( 'LIST HAS CAMPAIGNS' );
						
						$drafts =& $model->getAssociatedDrafts($listid);
						$error .= ' '.JText::_( 'Associated campaign drafts' ).': '.$drafts;
						
						break;

				}

			}

        $query = 'DELETE FROM #__joomailermailchimpintegration WHERE listid = "'.$listid.'" ';
        $db->setQuery( $query );
        $db->query();

		if( $delete['Result']['Code'] != '0' ) {
			$msg = JText::_( 'Error' ).': '.$error;
		} else {
			$msg = $error;
		}

		$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations', $msg );
	}// function
*/
	function cancel()
	{
		$msg = JText::_( 'JM_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=joomailermailchimpintegrations', $msg );
	}// function
}// class
