<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Tablersseo_competitors extends JTable
{

	var $IdCompetitor = null;
	var $Competitor = null;
	var $LastPageRank = null;
	var $LastAlexaRank = null;
	var $LastGooglePages= null;
	var $LastYahooPages = null;
	var $LastBingPages = null;
	var $LastGoogleBacklinks = null;
	var $LastYahooBacklinks = null;
	var $LastBingBacklinks = null;
	var $Dmoz = null;
	var $LastDateRefreshed = null;
	var $Tags = null;
	var $ordering = null;
	


	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablersseo_competitors(& $db) {
		parent::__construct('#__rsseo_competitors', 'IdCompetitor', $db);
	}
}
