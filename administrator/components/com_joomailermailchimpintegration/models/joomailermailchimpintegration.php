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

jimport( 'joomla.application.component.model' );

/**
 * joomailermailchimpintegration Model
 *
 * @package    joomailermailchimpintegration
 * @subpackage Models
 */
class joomailermailchimpintegrationsModeljoomailermailchimpintegration extends JModel
{

	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}//function

	/**
	 * Method to set the joomailermailchimpintegration identifier
	 *
	 * @access	public
	 * @param	int joomailermailchimpintegration identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}//function


    function cm_object() {

        $params    =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
        $subdomain = $params->get( 'subdomain' );
        $domain    = $params->get( 'domain' );
        $username  = $params->get( 'username' );
        $pw        = $params->get( 'pw' );

        $cm  = new CampaignMonitor(  );
        $api = $cm->userGetApiKey( 'http://'.$subdomain.'.'.$domain, $username, $pw );
        $cm  = new CampaignMonitor( $api['anyType'] );

        return $cm;
    }

	/**
	 * Method to get a record
	 * @return object with data
	 */
	function &getData()
	{

        $listid      = JRequest::getVar('listid',  0, '', 'string');

        $cm  = $this->cm_object();

        $this->_data = $cm->listGetDetail( $listid );

        return $this->_data;

	}//function

	function getAssociatedDrafts($listId) {
		
		$db =& JFactory::getDBO();
		$query = "SELECT name FROM #__joomailermailchimpintegration_campaigns WHERE list_id LIKE '%".$listId."%' ";
		$db->setQuery($query);
		$drafts = $db->loadObjectList();
		
		$listNames = '';
		foreach($drafts as $draft){
			$listNames .= $draft->name.', ';
		}
		$listNames = substr($listNames, 0 , -2);
		return $listNames;
	}
	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
        return true;
	}//function

}// class
