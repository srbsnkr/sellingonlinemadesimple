<?php
/**
 * CSV Improved Database class
 *
 * @package 	CSVI
 * @subpackage 	Database
 * @author 		Roland Dalmulder
 * @link 		http://www.csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2012 RolandD Cyber Produksi
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: csvidb.php 1924 2012-03-02 11:32:38Z RolandD $
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/**
* @package CSVI
* @subpackage Database
 */
class CsviDb {

	private $_database = null;
	private $_error = null;

	public function __construct() {
		$this->_database = JFactory::getDBO();
	}

	public function setQuery($sql) {
		$this->_database->setQuery($sql);
		if (!$this->cur = $this->_database->query()) {
			$this->_error = $this->_database->getErrorMsg();
		}
	}

	public function getRow() {
		if (!is_object($this->cur)) $array = mysql_fetch_object($this->cur);
		else $array = $this->cur->fetch_object();
		if ($array) {
			return $array;
		}
		else {
			if (!is_object($this->cur)) mysql_free_result( $this->cur );
			else $this->cur->free_result();
			return false;
		}
	}

	public function getErrorMsg() {
		return $this->_error;
	}

	public function getNumRows() {
		return $this->_database->getNumRows($this->cur);
	}

	public function getQuery() {
		return $this->_database->getQuery();
	}
}
?>