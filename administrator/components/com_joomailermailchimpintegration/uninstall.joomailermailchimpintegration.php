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

function com_uninstall()
{
	$errors = FALSE;
	
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_WARN = '<img src="images/publish_y.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	$BR = '<br />';

	//--uninstall...
	$db =& JFactory::getDBO();

	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration`;";
	$db->setQuery($query);
	if( !$db->query() )
	{
		echo $img_ERROR.JText::_('Unable to remove table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}
	
	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_campaigns`;";
	$db->setQuery($query);
	if( !$db->query() )
	{
		echo $img_ERROR.JText::_('Unable to remove table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}

	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_crm`;";
	$db->setQuery($query);
	if( !$db->query() )
	{
		echo $img_ERROR.JText::_('Unable to remove table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}

	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_crm_users`;";
	$db->setQuery($query);
	if( !$db->query() )
	{
		echo $img_ERROR.JText::_('Unable to remove table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}
	
	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_custom_fields`;";
	$db->setQuery($query);
	if( !$db->query() )
	{
		echo $img_ERROR.JText::_('Unable to remove table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}

	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_misc`;";
	$db->setQuery($query);
	if( !$db->query() )
	{
		echo $img_ERROR.JText::_('Unable to remove table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}

	// disable signup plugin
	if( version_compare(JVERSION,'1.6.0','ge') ) {
	    $query = "UPDATE `#__extensions` SET enabled = 0 WHERE type = 'plugin' AND element = 'joomailermailchimpsignup'";
	} else {
	    $query = "UPDATE `#__plugins` SET published = 0 WHERE element = 'joomailermailchimpsignup'";
	}
	$db->setQuery($query);
	if( !$db->query() )
	{
	    echo $img_ERROR.JText::_('Error disabling signup plugin').$BR;
	    echo $db->getErrorMsg();
	    return FALSE;
	}
	// disable JomSocial plugin
	if( version_compare(JVERSION,'1.6.0','ge') ) {
	    $query = "UPDATE `#__extensions` SET enabled = 0 WHERE type = 'plugin' AND element = 'joomlamailer' AND `folder` = 'community'";
	} else {
	    $query = "UPDATE `#__plugins` SET published = 0 WHERE element = 'joomlamailer' AND `folder` = 'community'";
	}
	$db->setQuery($query);
	if( !$db->query() )
	{
	    echo $img_ERROR.JText::_('Error disabling JomSocial plugin').$BR;
	    echo $db->getErrorMsg();
	    return FALSE;
	}

	if( $errors )
	{
		return FALSE;
	}
	
	return TRUE;
}// function
