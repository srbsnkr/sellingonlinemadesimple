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

class joomailermailchimpintegrationsModelTemplates extends JModel
{

    function getData()
    {
        $templates = '';
        return $templates;
    }
	
    function getPalettes( $hex = false, $keyword = false ){
	$runs = ( $hex || $keyword ) ? 1 : 3;
	$colors = array();
	for($i=0;$i<$runs;$i++){

	    $curl = curl_init();
	    if( !$hex && !$keyword ){
		$url = "http://www.colourlovers.com/api/palettes/random?format=json";
	    } else {
		$url = "http://www.colourlovers.com/api/palettes?format=json";
		if($hex){
		    $url .= "&hex=".$hex;
		}
		if($keyword){
		    $url .= "&keywords=".$keyword;
		}
	    }
	    curl_setopt($curl,CURLOPT_URL, $url);
	    curl_setopt($curl,CURLOPT_HEADER,false);
	    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	    $xml = curl_exec($curl);
	    if( !$hex && !$keyword ){
		$colors[] = json_decode($xml);
	    } else {
		$result = json_decode($xml);
		for($i=0;$i<count($result);$i++){
		    $colors[] = array($result[$i]);
		}

	    }
	    curl_close($curl);
	}

	return $colors;
    }
}
