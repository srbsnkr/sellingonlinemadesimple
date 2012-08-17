<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Tablersseo_redirects extends JTable
{

	var $IdRedirect = null;
	
	var $RedirectFrom = null;
	
	var $RedirectTo = null;
	
	var $RedirectType = null;
	
	var $published = null;
	

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablersseo_redirects(& $db) {
		parent::__construct('#__rsseo_redirects', 'IdRedirect', $db);
	}
}
