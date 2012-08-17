<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Tablersseo_pages extends JTable
{

	var $IdPage = null;
	var $PageURL = null;
	var $PageTitle = null;
	var $PageKeywords = null;
	var $PageKeywordsDensity = null;
	var $PageDescription = null;
	var $PageSitemap = null;
	var $PageInSitemap = null;
	var $PageCrawled = null;
	var $DatePageCrawled = null;
	var $PageLevel = null;
	var $PageGrade = null;
	var $params = null;
	var $densityparams = null;
	var $published = null;
	var $PageModified = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablersseo_pages(& $db) {
		parent::__construct('#__rsseo_pages', 'IdPage', $db);
	}
}
