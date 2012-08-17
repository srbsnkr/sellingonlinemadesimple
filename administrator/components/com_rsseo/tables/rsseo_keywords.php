
<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Tablersseo_keywords extends JTable
{

	var $IdKeyword = null;
	
	var $Keyword = null;
	
	var $KeywordImportance = null;
	
	var $ActualKeywordPosition = null;
	
	var $LastKeywordPosition = null;
	
	var $DateRefreshed = null;
	
	var $KeywordBold = null;
	
	var $KeywordUnderline = null;
	
	var $KeywordLimit = null;
	
	var $KeywordAttributes = null;
	
	var $KeywordLink = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablersseo_keywords(& $db) {
		parent::__construct('#__rsseo_keywords', 'IdKeyword', $db);
	}
}