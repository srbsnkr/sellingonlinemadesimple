<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');
if( ! JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
    echo JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER');
} else if( ! $params->get( 'listid' ) ){
    echo JText::_('JM_PLEASE_SELECT_A_LIST_IN_THE_CONFIG');
} else {
    JHTML::_('behavior.mootools');

    // load language files. include en-GB as fallback
    $jlang =& JFactory::getLanguage();
    $jlang->load('mod_mailchimpsignup', JPATH_SITE, 'en-GB', true);
    $jlang->load('mod_mailchimpsignup', JPATH_SITE, $jlang->getDefault(), true);
    $jlang->load('mod_mailchimpsignup', JPATH_SITE, null, true);
    
    // Joomla 1.6 ?
    if(version_compare(JVERSION,'1.6.0','ge')) {
	require JModuleHelper::getLayoutPath('mod_mailchimpsignup', $params->get('layout', 'default'));
    } else {
	require( JModuleHelper::getLayoutPath( 'mod_mailchimpsignup' ) );
    }
}
?>
