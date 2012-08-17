<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllersitemap extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	//function for creating the sitemap 
	function sitemapGenerate()
	{
		$db =& JFactory::getDBO();
		$SitemapFrequency = JRequest::getVar('SitemapFrequency');
		$SitemapModification = JRequest::getVar('SitemapModification');
		$SitemapPriority = JRequest::getVar('SitemapPriority');
		$SitemapNew = JRequest::getVar('SitemapNew');
		$protocol = JRequest::getInt('protocol',0);
		$sitemap = JPATH_SITE.DS.'sitemap.xml';
		$ror = JPATH_SITE.DS.'ror.xml';
		$root = JURI::root();
		
		
		if (substr($root,0,8) == 'https://' && $protocol == 0)
			$root = str_replace('https://','http://',$root);
		
		if (substr($root,0,7) == 'http://' && $protocol == 1)
			$root = str_replace('http://','https://',$root);
		
		if(filesize($sitemap)==0) $SitemapNew = 'new';
		
		$replace = array();
		
		$db->setQuery("SELECT RedirectFrom,RedirectTo FROM #__rsseo_redirects WHERE published = 1");
		$redirects = $db->loadObjectList();
		
		foreach($redirects as $redirect)
		{
			$redirect->RedirectFrom = htmlentities($redirect->RedirectFrom);
			$redirect->RedirectTo = htmlentities($redirect->RedirectTo);
			$replace[$redirect->RedirectFrom] = $redirect->RedirectTo;
		}
		
		
		if($SitemapNew=='new')
		{
		
			$db->setQuery("UPDATE #__rsseo_pages SET PageSitemap=0");
			$db->query();
			$xml_string = 
'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
			$ror_string=
'<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:ror="http://rorweb.com/0.1/" >
<channel>
<title>ROR Sitemap for '.$root.'</title>
<link>'.$root.'</link>
<item>
	<title>ROR Sitemap for '.$root.'</title>
	<link>'.$root.'</link>
	<ror:about>sitemap</ror:about>
	<ror:type>SiteMap</ror:type>
</item>
';
		}
		else
		{
			$xml_string = '';
			$ror_string = '';
		}
	
	
		$db->setQuery("SELECT * FROM #__rsseo_pages WHERE PageSitemap=0 AND PageInSitemap = 1 ORDER BY PageLevel LIMIT 500");
		$pages = $db->loadObjectList();
		
		if(!empty($pages))
		{		
			foreach($pages as $page)
			{
				if($page->PageLevel==0) $page->PageLevel=1;
				if($SitemapPriority == 'auto')
					$priority = number_format((1/$page->PageLevel),2,'.','');
				else $priority = '';
				
				if(!empty($replace[$page->PageURL])) $page->PageURL = $replace[$page->PageURL];
				
				
				$xml_string .= 
'<url>
	<loc>'.rsseoControllersitemap::xmlentities($root.$page->PageURL).'</loc>
	<priority>'.$priority.'</priority>
	<changefreq>'.$SitemapFrequency.'</changefreq>
	<lastmod>'.$SitemapModification.'</lastmod>  
</url>
';
				$ror_string .=
'<item>
	<link>'.rsseoControllersitemap::xmlentities($root.$page->PageURL).'</link>
	<title>'.rsseoControllersitemap::xmlentities($page->PageTitle).'</title>
    <ror:updatePeriod>'.$SitemapFrequency.'</ror:updatePeriod>
    <ror:sortOrder>'.$page->PageLevel.'</ror:sortOrder>
    <ror:resourceOf>sitemap</ror:resourceOf>
</item>
';
			
				$db->setQuery("UPDATE #__rsseo_pages SET PageSitemap=1 WHERE IdPage = '".$page->IdPage."'");
				$db->query();
			}
			
			rsseoHelper::fwrite($sitemap,$xml_string,($SitemapNew ? 'w':'a'));
			rsseoHelper::fwrite($ror,$ror_string,($SitemapNew ? 'w':'a'));
		}
		else
		{
			$filename = JPATH_SITE.DS.'sitemap.xml';
			rsseoHelper::fwrite($filename,"\n".'</urlset>','a');
			
			$filename = JPATH_SITE.DS.'ror.xml';
			rsseoHelper::fwrite($filename,'</channel>'."\n".'</rss>','a');
			
			echo 'finish';
			exit();
		}
		
		//get total number of pages
		$db->setQuery("SELECT count(*) FROM #__rsseo_pages WHERE PageInSitemap = 1");
		$total=$db->loadResult();
		
		$db->setQuery("SELECT count(*) FROM #__rsseo_pages WHERE PageSitemap=1 AND PageInSitemap = 1");
		$processed=$db->loadResult();
	
		
		echo '<div class="sitemapContainer"><div class="sitemapProgress" style="width:'.ceil($processed*100/$total).'%;">Progress: '.ceil($processed*100/$total).'%</div></div>';
		
		exit();
	}
	
	function xmlentities($string, $quote_style=ENT_QUOTES)
	{		
		//prepare string 
		$string = str_replace('&amp;','&',$string);
		$string = str_replace('&','&#38;',$string);
		
		return $string;
		
	}
	

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_rsseo');
	}
	
	
	function save()
	{		
		$db			=& JFactory::getDBO();
		$app		=& JFactory::getApplication();
		$menus		= JRequest::getVar('menus');
		$excludes	= JRequest::getVar('excludes');
		
		$menus    = is_array($menus) ? implode(',',$menus) : $menus;
		$excludes = is_array($excludes) ? implode(',',$excludes) : $excludes;
		
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($menus)."' WHERE ConfigName = 'sitemap_menus' ");
		$db->query();
		
		$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($excludes)."' WHERE ConfigName = 'sitemap_excludes' ");
		$db->query();
		
		$app->redirect('index.php?option=com_rsseo&task=sitemap&tabposition=1',JText::_('RSSEO_SITEMAP_GENERATED'));
	}

}