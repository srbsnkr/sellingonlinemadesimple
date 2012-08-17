<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Tablersseo_settings extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $IdConfig = null;

	var $ConfigName = null;
	
	var $ConfigValue = null;
	
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablersseo_settings(& $db) {
		parent::__construct('#__rsseo_config', 'IdConfig', $db);
	}
}