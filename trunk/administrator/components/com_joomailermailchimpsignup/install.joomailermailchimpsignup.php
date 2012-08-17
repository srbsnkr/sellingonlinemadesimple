<?php
/**
 * Copyright (C) 2011 freakedout (www.freakedout.de)
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
defined( '_JEXEC' ) or die( ';)' );
jimport( 'joomla.filesystem.file' );

/**
 * Main installer
 */
function com_install(){
$mainframe = & JFactory::getApplication();
    if(!JFile::exists(JPATH_SITE.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'controller.php')) {
      $mainframe->redirect('index.php',JText::_('INSTALLJOOMAILER'),'error');
    }

    $db = & JFactory::getDBO();

    $query="CREATE TABLE IF NOT EXISTS #__joomailermailchimpsignup (`id` int(11) NOT NULL AUTO_INCREMENT, `fname` varchar(100), `lname` varchar(100), `email` varchar(100) NOT NULL, `groupings` text NOT NULL, `merges` text NOT NULL, PRIMARY KEY (`id`))";
    $db->setQuery($query);
    $db->query();

    AddColumnIfNotExists("#__joomailermailchimpsignup", "merges", "text NOT NULL", "");


    if($db->getErrorMsg()){$mainframe->redirect('index.php',$db->getErrorMsg());}
}

function AddColumnIfNotExists($table, $column, $attributes = "INT( 11 ) NOT NULL DEFAULT '0'", $after = '' ) {

    $mainframe =& JFactory::getApplication();
    $db				=& JFactory::getDBO();
    $columnExists 	= false;

    $query = 'SHOW COLUMNS FROM '.$table;
    $db->setQuery( $query );
    if (!$result = $db->query()){return $db->getErrorMsg();}
    $columnData = $db->loadObjectList();


    foreach ($columnData as $valueColumn) {
    	if ($valueColumn->Field == $column) {
    		$columnExists = true;
    		break;
    	}
    }

    if (!$columnExists) {
    	if ($after != '') {
    		$query = "ALTER TABLE `".$table."` ADD `".$column."` ".$attributes." AFTER `".$after."`";
    	} else {
    		$query = "ALTER TABLE `".$table."` ADD `".$column."` ".$attributes."";
    	}
    	$db->setQuery( $query );
    	if (!$result = $db->query()){return $db->getErrorMsg();}
    }

    return false;
}
