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

// Make sure the user is authorized to view this page
$user =& JFactory::getUser();
if( version_compare(JVERSION,'1.6.0','ge') ){
    if (!$user->authorise('core.manage', 'com_joomailermailchimpintegration')) {
	return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
    }
} else {
    if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
    }
}

// set permission constants
if (   (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('joomlamailer.lists', 'com_joomailermailchimpintegration'))
    || !version_compare(JVERSION,'1.6.0','ge') ) {
    define('JOOMLAMAILER_MANAGE_LISTS', 1);
} else {
    define('JOOMLAMAILER_MANAGE_LISTS', 0);
}
if (   (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('joomlamailer.create', 'com_joomailermailchimpintegration'))
    || !version_compare(JVERSION,'1.6.0','ge') ) {
    define('JOOMLAMAILER_CREATE_DRAFTS', 1);
} else {
    define('JOOMLAMAILER_CREATE_DRAFTS', 0);
}
if (   (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('joomlamailer.campaigns', 'com_joomailermailchimpintegration'))
    || !version_compare(JVERSION,'1.6.0','ge') ) {
    define('JOOMLAMAILER_MANAGE_CAMPAIGNS', 1);
} else {
    define('JOOMLAMAILER_MANAGE_CAMPAIGNS', 0);
}
if (   (version_compare(JVERSION,'1.6.0','ge') && $user->authorise('joomlamailer.reports', 'com_joomailermailchimpintegration'))
    || !version_compare(JVERSION,'1.6.0','ge') ) {
    define('JOOMLAMAILER_MANAGE_REPORTS', 1);
} else {
    define('JOOMLAMAILER_MANAGE_REPORTS', 0);
}

// load language files. include en-GB as fallback
$jlang =& JFactory::getLanguage();
$jlang->load('com_joomailermailchimpintegration', JPATH_ADMINISTRATOR, 'en-GB', true);
$jlang->load('com_joomailermailchimpintegration', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
$jlang->load('com_joomailermailchimpintegration', JPATH_ADMINISTRATOR, null, true);

// if AJAX we force the format to raw.
if( JRequest::getString('action','') == 'AJAX' )
{
    JRequest::setVar('format','raw');
} else {
    if(JRequest::getVar('format') != 'raw') {

	jimport('joomla.html.parameter');
	jimport('joomla.application.component.helper');
	// Include css and js files
	JHTML::_('behavior.mootools');
	$document = & JFactory::getDocument();
	$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/default.css');
	$document->addCustomTag('<!--[if IE 8]><link rel="stylesheet" href="'.JURI::base().'components/com_joomailermailchimpintegration/assets/css/ie8.css"/><![endif]-->');
	$document->addCustomTag('<!--[if lte IE 7]><link rel="stylesheet" href="'.JURI::base().'components/com_joomailermailchimpintegration/assets/css/ie.css"/><![endif]-->');
	$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/joomailermailchimpintegration.js');
	if( version_compare(JVERSION,'1.6.0','ge') ) {
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/ajax_16.js');
	} else {
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/ajax_15.js');
	}

	$document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/jquery.min.js');
	$document->addScriptDeclaration('jQuery.noConflict(); var $j = jQuery.noConflict();');

	jimport( 'joomla.environment.browser' );
	$browser  = JBrowser::getInstance();
	$bName    = $browser->getBrowser();
	if($bName != 'msie'){
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/engage.itoggle-min.js');
	    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/jquery.easing.1.3.js');
	    $document->addCustomTag('<script type="text/javascript">$j(document).ready(function() {
	    $j(\'input.checkbox\').iToggle({
		    easing: \'easeOutBack\',
		    type: \'checkbox\',
		    keepLabel: true,
    //		easing: \'easeInExpo\',
		    speed: 300,
		    onClick: function(){
			    //Function here
		    },
		    onClickOn: function(){
			    //Function here
		    },
		    onClickOff: function(){
			    //Function here
		    },
		    onSlide: function(){
			    //Function here
		    },
		    onSlideOn: function(){
			    //Function here
		    },
		    onSlideOff: function(){
			    //Function here
		    }
		});
	    });</script>');
	    $document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/engage.itoggle.css');
	}
    }
}

// create meta menu
$ext	= JRequest::getWord('view');
if ( !$ext ) { $ext = 'main'; }
else if ( $ext == 'subscribers' ) { $ext = 'joomailermailchimpintegrations'; }
$subMenu = array();

$subMenu['JM_DASHBOARD']    = 'main';
if ( JOOMLAMAILER_MANAGE_LISTS ) {
$subMenu['JM_LISTS']	    = 'joomailermailchimpintegrations';
}
if ( JOOMLAMAILER_MANAGE_CAMPAIGNS ) {
$subMenu['JM_CAMPAIGNS']    = 'campaignlist';
}
if ( JOOMLAMAILER_MANAGE_REPORTS ) {
$subMenu['JM_REPORTS']	    = 'campaigns';
}

    
foreach ($subMenu as $name => $extension) {
    JSubMenuHelper::addEntry(JText::_( $name ), 'index.php?option=com_joomailermailchimpintegration&view='.$extension. '" onclick="javascript:joomailermailchimpintegration_ajax_loader();', $extension == $ext );
}

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );
// Require the MC base file
require_once( JPATH_COMPONENT.DS.'libraries'.DS.'MCAPI.class.php' );
//require_once( JPATH_COMPONENT.DS.'libraries'.DS.'MCSTS.class.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'MCerrorHandler.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'MCauth.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'CRMauth.php' ); 
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'common.php' );
// Check neccessary directory permissions
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'permissions.php' );
// Check for updates
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'update.php' );
// JSON support in case of PHP < 5.2
require_once( JPATH_COMPONENT.DS.'libraries'.DS.'jsonwrapper'.DS.'jsonwrapper.php' );

// Require specific controller if requested
$controller = JRequest::getWord('controller');
if( $controller )
{
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if( file_exists($path)) {
	require_once $path;
    } else {
	$controller = '';
    }
}

// Create the controller
$classname    = 'joomailermailchimpintegrationsController'.$controller;
$controller   = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );
// Redirect if set by the controller
$controller->redirect();
