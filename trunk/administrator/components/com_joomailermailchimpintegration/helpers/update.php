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

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'update.php' );

class jmUpdate {

    private $model;

    function __construct(){
	$this->model = new joomailermailchimpintegrationsModelUpdate;
    }


    function getIcon(){
	$icon = '';
	$this->model = new joomailermailchimpintegrationsModelUpdate;
	$liveupdateinfo = $this->model->getUpdates();
	if( $liveupdateinfo->supported ){
	    if( $liveupdateinfo->update_available ){
		$icon  = '<a href="index.php?option=com_joomailermailchimpintegration&view=update">';
		$icon .= '<span style="color:#ff0000;">'.JText::_('JM_UPDATE_AVAILABLE').': '.$liveupdateinfo->latest_version.'</span>';
		$icon .= '</a>';
		$icon .=  '<div style="position:fixed;bottom:0;left:0;display:block;width:45px;height:32px;z-index:999999;">
			    <a href="index.php?option=com_joomailermailchimpintegration&view=update">
				<img src="'.JURI::base().'components/com_joomailermailchimpintegration/assets/images/freddie_32_right.png" alt="'.JText::_('JM_UPDATE_AVAILABLE').'" title="'.JText::_('JM_UPDATE_AVAILABLE').'" />
			    </a>
			</div>';
	    } else {
		$icon = '<a href="index.php?option=com_joomailermailchimpintegration&view=update">'.JText::_('JM_UPDATE_IS_LATEST').' ('.JOOMAILERMC_VERSION.')</a>';
	    }
	} else {
	    $icon = '<a href="http://www.joomlamailer.com" target="_blank">'.JText::_('JM_UPDATE_NOTSUPPORTED').'</a>';
	}

	return $icon;
    }


}