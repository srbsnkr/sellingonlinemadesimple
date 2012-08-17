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

class joomailermailchimpintegrationsControllerjoomailermailchimpintegrationinstall extends joomailermailchimpintegrationsController
{
    function __construct()
    {
	// remove obsolete files
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');
	$removeFiles = array();
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'js'.DS.'jquery-1.4.2.min.js';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'js'.DS.'jquery.clockpick.1.2.7.min.js';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'css'.DS.'jquery.clockpick.1.2.7.css';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'images'.DS.'ol_bg.jpg';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'models'.DS.'archive.php';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'controllers'.DS.'archive.php';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'models'.DS.'suppression.php';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'controllers'.DS.'suppression.php';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'helpers'.DS.'cache.php';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'images'.DS.'templateEditor.png';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'images'.DS.'clearPosition.png';
	$removeFiles[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'images'.DS.'apply.png';
	foreach($removeFiles as $rf){
	    if(JFile::exists($rf)){
		JFile::Delete($rf);
	    }
	}
	// remove obsolete folders
	$removeFolders = array();
	$removeFolders[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'assets'.DS.'scripts';
	$removeFolders[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'views'.DS.'archive';
	$removeFolders[] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'views'.DS.'suppression';
	foreach($removeFolders as $rf){
	    if(JFolder::exists($rf)){
		JFolder::Delete($rf);
	    }
	}

	parent::__construct();
	$this->registerTask( 'install'  , 'install' );
	$this->registerTask( 'upgrade'  , 'upgrade' );
    }

    function install() {

	$errors = FALSE; 
	$db =& JFactory::getDBO();

	//create joomailer tables
	$query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration`;";
	$db->setQuery($query);
	if( ! $db->query() )
	{
	    $msg    = JText::_( 'Installation error:' ) . $db->getErrorMsg();
	    $errors = TRUE;
	}
	if ( !$errors ) {
	    $query =   "CREATE TABLE `#__joomailermailchimpintegration` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`userid` int(11) NOT NULL ,
			`email` varchar(50) NOT NULL ,
			`listid` varchar(32) NOT NULL ,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	    $db->setQuery($query);
	    if( ! $db->query() )
	    {
		$msg = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		$errors = TRUE;
	    } else {
		$msg = JText::_( 'Newsletter component successfully installed!' );
	    }

	}
    
	// clear custom fields table
	if ( !$errors ) {
	    $query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_custom_fields`;";
		$db->setQuery($query);
		if( ! $db->query() )
		{
		    $msg    = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		    $errors = TRUE;
		}
		// create custom fields table
		if ( !$errors ) {
		     $query = "CREATE TABLE `#__joomailermailchimpintegration_custom_fields` (
			      `id` int(11) NOT NULL auto_increment,
			      `listID` varchar(255) NOT NULL,
			      `name` varchar(255) NOT NULL,
			      `framework` varchar(255) NOT NULL default '',
			      `dbfield` varchar(255) NOT NULL default '',
			      `grouping_id` varchar(255) NOT NULL default '',
			      `type` varchar(5) NOT NULL,
			      PRIMARY KEY  (`id`)
			      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ";
		    $db->setQuery($query);
		    if( ! $db->query() )
		    {
			$msg = JText::_( 'Installation error:' ) . $db->getErrorMsg();
			$errors = TRUE;
		    } else {
			$msg = JText::_( 'Newsletter component successfully installed!' );
		    }
		}
	}
	// clear campaigns table
	if ( !$errors ) {
	    $query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_campaigns`;";
		$db->setQuery($query);
	    if( ! $db->query() )
	    {
		$msg    = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		$errors = TRUE;
	    }

	    // create campaigns table
	    if ( !$errors ) {
		$query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_campaigns` (
			  `id` int(11) NOT NULL auto_increment,
			  `list_id` varchar(255) NOT NULL,
			  `list_name` text NOT NULL,
			  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
			  `subject` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
			  `from_name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
			  `from_email` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
			  `reply` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
			  `confirmation` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
			  `creation_date` int(22) NOT NULL,
			  `recipients` int(22) NOT NULL,
			  `sent` tinyint(4) NOT NULL,
			  `cid` varchar(255) NOT NULL,
			  `cdata` text NOT NULL,
			  `folder_id` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
		$db->setQuery($query);
		if( ! $db->query() )
		{
		    $msg = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		    $errors = TRUE;
		} else {

		    //add folder_id column
		    if ( !$errors ) {
			$msgError = joomailermailchimpintegrationsControllerjoomailermailchimpintegrationInstall::AddColumnIfNotExists( '#__joomailermailchimpintegration_campaigns', 'folder_id','int(11) NOT NULL', 'cdata' );
		    }
		    if(!$msgError){
			$msg = JText::_( 'Newsletter component, signup component, signup plugin, signup module, admin stats module and MailChimp STS plugin successfully installed!' );
		    } else {
			$msg = JText::_( 'Installation error:' ) . $msgError;
		    }
		}
	    }
	}
	// create misc configurations table
	if ( !$errors ) {
	    $query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_misc`;";
	    $db->setQuery($query);
	    if( ! $db->query() )
	    {
		$msg    = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		$errors = TRUE;
	    }
	    // create table
	    if ( !$errors ) {
		$query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_misc` (
			  `id` int(11) NOT NULL auto_increment,
			  `listid` varchar(50) character set utf8 NOT NULL,
			  `type` varchar(50) character set utf8 NOT NULL,
			  `value` text character set utf8 NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
		$db->setQuery($query);
		if( ! $db->query() )
		{
		    $msg = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		    $errors = TRUE;
		} else {

		    //add folder_id column
		    if ( !$errors ) {
			$msgError = joomailermailchimpintegrationsControllerjoomailermailchimpintegrationInstall::AddColumnIfNotExists( '#__joomailermailchimpintegration_campaigns', 'folder_id','int(11) NOT NULL', 'cdata' );
		    }
		    if(!$msgError){
			$msg = JText::_( 'Newsletter component, signup component, signup plugin, signup module, admin stats module and MailChimp STS plugin successfully installed!' );
		    } else {
			$msg = JText::_( 'Installation error:' ) . $msgError;
		    }
		}
	    }
	}
	// create crm configurations table
	if ( !$errors ) {
	    $query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_crm`;";
	    $db->setQuery($query);
	    if( ! $db->query() )
	    {
		$msg    = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		$errors = TRUE;
	    }
	    // create table
	    if ( !$errors ) {
		$query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_crm` (
			  `id` int(11) NOT NULL auto_increment,
			  `crm` varchar(256) NOT NULL,
			  `params` text NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
		$db->setQuery($query);
		if( ! $db->query() )
		{
		    $msg = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		    $errors = TRUE;
		} else {

		    if(!$msgError){
			$msg = JText::_( 'Newsletter component, signup component, signup plugin, signup module, admin stats module and MailChimp STS plugin successfully installed!' );
		    } else {
			$msg = JText::_( 'Installation error:' ) . $msgError;
		    }
		}
	    }
	}
	// create crm users table
	if ( !$errors ) {
	    $query = "DROP TABLE IF EXISTS `#__joomailermailchimpintegration_crm_users`;";
	    $db->setQuery($query);
	    if( ! $db->query() )
	    {
		$msg    = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		$errors = TRUE;
	    }
	    // create table
	    if ( !$errors ) {
		$query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_crm_users` (
			  `id` int(11) NOT NULL auto_increment,
			  `crm` varchar(20) NOT NULL,
			  `user_id` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
		$db->setQuery($query);
		if( ! $db->query() )
		{
		    $msg = JText::_( 'Installation error:' ) . $db->getErrorMsg();
		    $errors = TRUE;
		} else {

		    if(!$msgError){
			$msg = JText::_( 'Newsletter component, signup component, signup plugin, signup module, admin stats module and MailChimp STS plugin successfully installed!' );
		    } else {
			$msg = JText::_( 'Installation error:' ) . $msgError;
		    }
		}
	    }
	}


        $this->installExtensions($msg);

	$link = 'index.php?option=com_joomailermailchimpintegration&view=main';
	$mainframe =& JFactory::getApplication();
	$mainframe->redirect($link, $msg);
    }


    function upgrade() {

	$msgError = false;
	$db =& JFactory::getDBO();

	// add userid field to joomailermailchimpintegration table
	$msgError = joomailermailchimpintegrationsControllerjoomailermailchimpintegrationInstall::AddColumnIfNotExists( '#__joomailermailchimpintegration', 'userid','int(11) NOT NULL', 'id' );

	//set id to auto_increment
	if ( !$msgError ) {
	 $query = "ALTER TABLE #__joomailermailchimpintegration MODIFY id int auto_increment";
	 $db->setQuery($query);
	 if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}

	// create custom fields table
	if ( !$msgError ) {
	     $query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_custom_fields` (
		      `id` int(11) NOT NULL auto_increment,
		      `listID` varchar(255) NOT NULL,
		      `name` varchar(255) NOT NULL,
		      `framework` varchar(255) NOT NULL default '',
		      `dbfield` varchar(255) NOT NULL default '',
		      `grouping_id` varchar(255) NOT NULL default '',
		      `type` varchar(5) NOT NULL default 'group',
		      PRIMARY KEY  (`id`)
		    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ";
	    $db->setQuery($query);
	    if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}
	// add `type` column
	if ( !$msgError ) {
	    $msgError = joomailermailchimpintegrationsControllerjoomailermailchimpintegrationInstall::AddColumnIfNotExists( '#__joomailermailchimpintegration_custom_fields', 'type',"varchar(5) NOT NULL default 'group'", 'grouping_id' );
	}
	// set `grouping_id` to varchar
	if ( !$msgError ) {
	    $query = "ALTER TABLE #__joomailermailchimpintegration_custom_fields MODIFY grouping_id varchar(255) NOT NULL default 'group'";
	    $db->setQuery($query);
	    if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}


	// create campaigns table
	if ( !$msgError ) {
	    $query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_campaigns` (
		      `id` int(11) NOT NULL auto_increment,
		      `list_id` varchar(255) NOT NULL,
		      `list_name` text NOT NULL,
		      `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
		      `subject` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
		      `from_name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
		      `from_email` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
		      `reply` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
		      `confirmation` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
		      `creation_date` int(22) NOT NULL,
		      `recipients` int(22) NOT NULL,
		      `sent` tinyint(4) NOT NULL,
		      `cid` varchar(255) NOT NULL,
		      `cdata` text NOT NULL,
		      `folder_id` int(11) NOT NULL,
		      PRIMARY KEY  (`id`)
		    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
	    $db->setQuery($query);
	    if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}
	if ( !$msgError ) {
	    $msgError = joomailermailchimpintegrationsControllerjoomailermailchimpintegrationInstall::AddColumnIfNotExists( '#__joomailermailchimpintegration_campaigns', 'list_name','TEXT NOT NULL', 'list_id' );
	}
	//add folder_id column
	if ( !$msgError ) {
	    $msgError = joomailermailchimpintegrationsControllerjoomailermailchimpintegrationInstall::AddColumnIfNotExists( '#__joomailermailchimpintegration_campaigns', 'folder_id','int(11) NOT NULL', 'cdata' );
	}

	// create misc configurations table
	if ( !$msgError ) {
	     $query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_misc` (
		      `id` int(11) NOT NULL auto_increment,
		      `listid` varchar(50) character set utf8 NOT NULL,
		      `type` varchar(50) character set utf8 NOT NULL,
		      `value` text character set utf8 NOT NULL,
		      PRIMARY KEY  (`id`)
		    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;";
	    $db->setQuery($query);
	    if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}

	// create crm configurations table
	if ( !$msgError ) {
	     $query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_crm` (
		      `id` int(11) NOT NULL auto_increment,
		      `crm` varchar(256) NOT NULL,
		      `params` text NOT NULL,
		      PRIMARY KEY  (`id`)
		    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
	    $db->setQuery($query);
	    if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}
	// create crm users table
	if ( !$msgError ) {
	     $query = "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_crm_users` (
			  `id` int(11) NOT NULL auto_increment,
			  `crm` varchar(20) NOT NULL,
			  `user_id` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
	    $db->setQuery($query);
	    if (!$db->query()){ $msgError = $db->getErrorMsg(); }
	}

	// End Message
	if ( $msgError ) {
	    $msg = JText::_( 'Newsletter component upgrade failed' ) . ': ' . $msgError;
	} else {
	    $msg = JText::_( 'Newsletter component, signup component, signup plugin, signup module, admin stats module and MailChimp STS plugin successfully upgraded' );
	}

	$this->installExtensions($msg);

	$link = 'index.php?option=com_joomailermailchimpintegration&view=main';
	$this->setRedirect($link, $msg);
    }


    function AddColumnIfNotExists($table, $column, $attributes = "INT( 11 ) NOT NULL DEFAULT '0'", $after = '' ) {

	$mainframe =& JFactory::getApplication();
	$db		=& JFactory::getDBO();
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

    function installExtensions($mainmsg) {
        jimport('joomla.installer.helper');
        jimport('joomla.installer.installer');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
	$db =& JFactory::getDBO();
        $installer = new JInstaller();
//      $installer->_overwrite = true;

        $pkg_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'extensions'.DS;
        $pkgs = array( 'com_joomailermailchimpsignup.zip'=>'Joomlamailer Signup component',
                       'plg_joomailermailchimpsignup.zip'=>'Joomlamailer Signup plugin',
                       'mod_mailchimpsignup.zip'=>'MailChimp Signup',
                       'mod_mailchimpstats.zip'=>'MailChimp Admin Stats',
                       'plg_fmsts.zip'=>'MailChimp STS plugin',
		       'plg_joomlamailer_JomSocial.zip' => 'JomSocial plugin',
		       'plg_joomlamailer_CommunityBuilder.zip' => 'CommunityBuilder plugin'
                     );

        $mainframe = & JFactory::getApplication();

        foreach( $pkgs as $pkg => $pkgname ) {
	    if( $pkg == 'plg_joomlamailer_CommunityBuilder.zip' ){
		$cbinstaller = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'library'.DS.'cb'.DS.'cb.installer.php';
		if(JFile::exists( $cbinstaller )) {
		    require_once( $cbinstaller );
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'plugin.class.php' );
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php' );
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'library'.DS.'cb'.DS.'cb.database.php' );
		    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.class.php' );
		    
		    $installer = new cbInstallerPlugin();
		    $installer->installArchive( $pkg_path.$pkg );
		    $installer->extractArchive();
		    if( $installer->install() ){
			$query = "UPDATE #__comprofiler_plugin SET `published` = '1' WHERE `folder` = 'plug_joomlamailercbsignup'";
			$db->setQuery( $query );
			$db->query();
		    }
		}
	    } else {
		$package = JInstallerHelper::unpack( $pkg_path.$pkg );
		if( $installer->install( $package['dir'] ) )
		{
		    $msg = 1;
		    $msgtext  = "$pkgname successfully installed.";
		}
		else
		{
		    $msg = 0;
		    $msgtext  = "ERROR: Could not install the $pkgname. Please install manually.";
		}
		if (!$msg) {
		    $mainframe->redirect( 'index.php', $msgtext."\n".$mainmsg );
		} else {
		    if( $pkg == 'com_joomailermailchimpsignup.zip' ){
			// remove signup component from backend menu
			if( version_compare(JVERSION,'1.6.0','ge') ){
			    $query = "DELETE FROM #__menu WHERE `title` = 'com_joomailermailchimpsignup';";
			    $db->setQuery( $query );
			    $db->query();
			}
		    } else if ($pkg == 'mod_mailchimpstats.zip'){
			$query = "UPDATE #__modules SET published = '1', position = 'cpanel', ordering = '-1' WHERE `module` = 'mod_mailchimpstats'";
			$db->setQuery($query);
			$db->query();
			if( version_compare(JVERSION,'1.6.0','ge') ){
			    $query = "SELECT id FROM #__modules WHERE `module` = 'mod_mailchimpstats'";
			    $db->setQuery($query);
			    $moduleID = $db->loadResult();
			    $query = "INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('".$moduleID."', '0')";
			    $db->setQuery($query);
			    $db->query();
			}
		    } else if ( $pkg == 'plg_joomlamailer_JomSocial.zip' ){
			if( version_compare(JVERSION,'1.6.0','ge') ){
			    $query = "UPDATE #__extensions SET `enabled` = '1' WHERE `element` = 'joomlamailer' AND `folder` = 'community'";
			} else {
			    $query = "UPDATE #__plugins SET `published` = '1' WHERE `element` = 'joomlamailer' AND `folder` = 'community'";
			}
			$db->setQuery($query);
			$db->query(); 
		    }
		}
	    }
        }
        $path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'extensions';
        if(JFolder::exists($path)) {
	    JFolder::delete($path);
        }
    }

}
?>
