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

class joomailermailchimpintegrationModelSubscriptions extends JModel
{
    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }
    
    function getLists()
    {
	$MC      = $this->MC_object();
	$lists   = $MC->lists();

	return $lists;
    }

    function getIsSubscribed($listid, $email)
    {
	$MC  = $this->MC_object();
	$sub = $MC->listMemberInfo( $listid, $email );

	if(isset($sub['status']) && $sub['status'] =='unsubscribed'){
	    return false;
	}

	return $sub;
    }

}
