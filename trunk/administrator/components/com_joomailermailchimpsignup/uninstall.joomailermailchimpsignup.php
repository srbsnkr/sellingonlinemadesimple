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
defined( '_JEXEC' ) or die( ';)' );

/**
 * The main uninstaller function
 */
function com_uninstall()
{
	$errors = FALSE;

	//-- common images


	//--uninstall...

	$db = & JFactory::getDBO();

	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpsignup`;";
	$db->setQuery($query);
	if( ! $db->query() )
	{
		echo $db->getErrorMsg();
		return FALSE;
	}

	if( $errors )
	{
		return FALSE;
	}

	return TRUE;
}// function