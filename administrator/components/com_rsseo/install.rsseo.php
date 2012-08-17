<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

// Get a new installer
$plg_installer = new JInstaller();
jimport('joomla.filesystem.folder');

@$plg_installer->install($this->parent->getPath('source').DS.'plg_rsseo');
$db->setQuery("UPDATE #__plugins SET published=1 WHERE `element`='rsseo' AND `folder`='system'");
$db->query();


//UPDATE PROCEDURE
$db =& JFactory::getDBO();
$db->setQuery("DESCRIBE `#__rsseo_pages`"); 
$pages_table = $db->loadObjectList();

foreach($pages_table as $page)
{
	if(!($page->Field == 'PageModified'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_pages` ADD `PageModified` INT( 2 ) NOT NULL");
		$db->query();
	}
	
	if(!($page->Field == 'PageKeywordsDensity'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_pages` ADD `PageKeywordsDensity` TEXT NOT NULL AFTER `PageKeywords`");
		$db->query();
	}
	
	if(!($page->Field == 'PageInSitemap'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_pages` ADD `PageInSitemap` INT ( 2 ) NOT NULL AFTER `PageSitemap`");
		$db->query();
		$db->setQuery("UPDATE `#__rsseo_pages` SET `PageInSitemap` = 1 ");
		$db->query();
	}
	
	if(!($page->Field == 'densityparams'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_pages` ADD `densityparams` TEXT NOT NULL AFTER `params`");
		$db->query();
	}
}

$db->setQuery("DESCRIBE `#__rsseo_competitors`"); 
$competitors_table = $db->loadObjectList();

foreach($competitors_table as $competitor)
{
	if(!($competitor->Field == 'LastTehnoratiRank'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_competitors` ADD `LastTehnoratiRank` INT( 11 ) NOT NULL DEFAULT '-1'");
		$db->query();
	}
	
	if(!($competitor->Field == 'Dmoz'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_competitors` ADD `Dmoz` INT( 1 ) NOT NULL DEFAULT '-1'");
		$db->query();
	}
}

$db->setQuery("DESCRIBE `#__rsseo_competitors_history`"); 
$competitors_history_table = $db->loadObjectList();

foreach($competitors_history_table as $competitor)
{
	if(!($competitor->Field == 'TehnoratiRank'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_competitors_history` ADD `TehnoratiRank` INT( 11 ) NOT NULL");
		$db->query();
	}
}

$db->setQuery("DESCRIBE `#__rsseo_keywords`");
$keywords_table = $db->loadObjectList();
foreach ($keywords_table as $keyword)
{
	if(!($keyword->Field == 'KeywordAttributes'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_keywords` ADD `KeywordAttributes` TEXT NOT NULL");
		$db->query();
	}
	
	if(!($keyword->Field == 'KeywordLimit'))
	{
		$db->setQuery("ALTER TABLE `#__rsseo_keywords` ADD `KeywordLimit` INT( 3 ) NOT NULL");
		$db->query();
	}
}

if (JFolder::exists(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'images'))
	JFolder::delete(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'images');
if (JFolder::exists(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'css'))
	JFolder::delete(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'css');


?>
	<table class="adminlist">
		<thead>
			<tr>
				<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
				<th width="30%"><?php echo JText::_('Status'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="3"></td>
			</tr>
		</tfoot>
		<tbody>
			<tr class="row0">
				<td class="key" colspan="2"><?php echo 'RSSeo! '.JText::_('Component'); ?></td>
				<td><strong><?php echo JText::_('Installed'); ?></strong></td>
			</tr>
			<tr>
				<th><?php echo JText::_('Plugin'); ?></th>
				<th><?php echo JText::_('Group'); ?></th>
				<th></th>
			</tr>
			<tr class="row1">
				<td class="key">System - RSSeo! Plugin</td>
				<td class="key">system</td>
				<td><strong><?php echo JText::_('Installed'); ?></strong></td>
			</tr>
		</tbody>
	</table>
	<table>
	<tr>
		<td width="1%"><img src="components/com_rsseo/assets/images/rsseo-box.jpg" alt="RSSeo! Box" /></td>
		<td align="left">
		<div id="rsseo_message">
		<p>Thank you for choosing RSSeo!</p>
		<p>New in this version:</p>
		<ul id="rsseo_changelog">
			<li>Google Analytics Reports</li>
			<li>Google Analytics Tracker</li>
			<li>Bug fixes</li>
		</ul>
		<a href="http://www.rsjoomla.com/customer-support/documentations/68-general-overview-of-the-component/279-rsseo-changelog.html" target="_blank">Full Changelog</a>
		<ul id="rsseo_links">
			<li>
				<div class="button2-left">
					<div class="next">
						<a href="index.php?option=com_rsseo">Start using RSSeo!</a>
					</div>
				</div>
			</li>
			<li>
				<div class="button2-left">
					<div class="readmore">
						<a href="http://www.rsjoomla.com/customer-support/documentations/67-rsseo.html" target="_blank">Read the RSSeo! User Guide</a>
					</div>
				</div>
			</li>
			<li>
				<div class="button2-left">
					<div class="blank">
						<a href="http://www.rsjoomla.com/customer-support/tickets.html" target="_blank">Get Support!</a>
					</div>
				</div>
			</li>
		</ul>
		</div>
		</td>
	</tr>	
</table>
<div align="left" width="100%"><b>RSSeo! 1.0.0 Installed</b></div>
<style type="text/css">
.green { color: #009E28; }
.red { color: #B8002E; }
.greenbg { background: #B8FFC9 !important; }
.redbg { background: #FFB8C9 !important; }
#rsseo_changelog
{
	list-style-type: none;
	padding: 0;
}
#rsseo_changelog li
{
	background: url(components/com_rsseo/assets/images/ok.png) no-repeat center left;
	padding-left: 24px;
}

#rsseo_links
{
	list-style-type: none;
	padding: 0;
}
</style>