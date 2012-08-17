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

class MCauth {
	
    function MCauth(){
	if(!isset($_SESSION['MCping'])){
	    jimport('joomla.html.parameter');
	    jimport('joomla.application.component.helper');
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MC = new MCAPI($MCapi);
	    $ping = $MC->ping();
	    $_SESSION['MCping'] = $ping;
	} else {
	    $ping = $_SESSION['MCping'];
	}

	return $ping;
    }
}
