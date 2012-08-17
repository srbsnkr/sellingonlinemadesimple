<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllerCrawler extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		
	}

	function crawl()
	{	
		$db =& JFactory::getDBO();
		$app =& JFactory::getApplication();
		$start = JRequest::getInt('start',0);
		$idPage= JRequest::getInt('idPage',0);
		$rsseoConfig = $app->getuserState('rsseoConfig');
		$autocrawler = $rsseoConfig['crawler.enable.auto'];
		
		
		if ($autocrawler)
		{
			$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '0' WHERE ConfigName = 'crawler.enable.auto' ");
			$db->query();
		}
		
		
		if($start == 1) 
		{
			$db->setQuery("UPDATE #__rsseo_pages SET PageCrawled=0");
			$db->query();
		}
		
		if($idPage != 0)
		//load the selected page
			$db->setQuery("SELECT * FROM #__rsseo_pages WHERE IdPage='".$idPage."'");
		else
		//load the first un-crawled page
			$db->setQuery("SELECT * FROM #__rsseo_pages WHERE PageCrawled = 0 AND PageLevel != 127 ORDER BY PageLevel asc, IdPage asc LIMIT 1");

		$page = $db->loadObject();
		if(!empty($page))
		{
			$newPage = rsseoHelper::checkPage($page->IdPage,$idPage);
			$newPage->PageCrawled = 1;
			$newPage->DatePageCrawled = time();
			if ($newPage->PageLevel < 127) $newPage->store();
			
			$link = $newPage->_link;
			
			//get the ignored href`s
			$ignored = $rsseoConfig['crawler.ignore'];
			$ignored = str_replace("\r",'',$ignored);
			$ignored = explode("\n",$ignored);
			
			$parser = rsseoHelper::file_get_html($link);
			
			if( $rsseoConfig['crawler.level'] == -1 || ($rsseoConfig['crawler.level'] != -1 && $page->PageLevel < $rsseoConfig['crawler.level'] ))		
			while($parser->parse())
			{
				if(strtolower($parser->iNodeName) == 'a')
				{
					$href = rsseoHelper::clean_url(@$parser->iNodeAttributes['href']);
				
					foreach($ignored as $ignore)
					{
						if(!empty($ignore))
						{
							$ignore = str_replace('&', '&amp;', $ignore);
							if ($this->is_ignored($href, $ignore))
								continue 2;
						}
					}
					
					if(strpos($href,'mailto:') !== FALSE) continue;
					if(strpos($href,'javascript:') !== FALSE) continue;
					if($newPage->PageLevel >= 127) continue;
					if($href == 'administrator/' || $href == 'administrator') continue;
					
					if($href != null)
					{
						$href = str_replace(JURI::root(),'',$href);
						
						$db->setQuery("SELECT COUNT(*) FROM #__rsseo_pages WHERE PageURL='".$href."'");
						if($db->loadResult()==0)
						{
							$db->setQuery("INSERT INTO #__rsseo_pages SET PageURL = '".$href."', PageTitle ='', PageKeywords ='', PageDescription = '', PageInSitemap = 1 , PageSitemap=0, PageCrawled=0, PageLevel = '".($page->PageLevel+1)."' ");
							$db->query();
						}
					}
				}
			}
			
			//count the number of pages crawled
			$db->setQuery("SELECT COUNT(*) FROM #__rsseo_pages WHERE PageCrawled != 0 AND PageLevel != 127");
			$pages_crawled = $db->loadResult();
			
			//count the number of pages left on this level..
			$db->setQuery("SELECT COUNT(*) FROM #__rsseo_pages WHERE PageCrawled = 0 AND PageLevel='".$page->PageLevel."'");
			$pages_left = $db->loadResult();
			
			//count total pages crawled
			$db->setQuery("SELECT COUNT(*) FROM #__rsseo_pages");
			$total_pages = $db->loadResult();
		
		
			if ($autocrawler)
			{
				$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '1' WHERE ConfigName = 'crawler.enable.auto' ");
				$db->query();
			}
			
			$page_properties = array();
			$page_properties[] = $newPage->PageURL;
			$page_properties[] = $newPage->PageLevel;
			$page_properties[] = $pages_crawled;
			$page_properties[] = $pages_left;
			$page_properties[] = date($rsseoConfig['global.dateformat']);
			$page_properties[] = $newPage->PageTitle;
			$page_properties[] = $total_pages;
			$page_properties[] = ceil($newPage->PageGrade);
			echo implode("\n",$page_properties);
		
		}else
		{
			if ($autocrawler)
			{
				$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '1' WHERE ConfigName = 'crawler.enable.auto' ");
				$db->query();
			}
			
			echo 'Finished'."\n\n\n\n\n\n\n\n\n";
		}
		exit();
	}
	
	function is_ignored($url, $pattern)
	{
		$pattern = $this->transform_string($pattern);	
		preg_match_all($pattern, $url, $matches);
		
		if (count($matches[0]) > 0)
			return true;
		else
			return false;
	}

	function transform_string($string)
	{
		$string = preg_quote($string, '/');
		$string = str_replace(preg_quote('{*}', '/'), '(.*)', $string);	
		
		$pattern = '#\\\{(\\\\\?){1,}\\\}#';
		preg_match_all($pattern, $string, $matches);
		if (count($matches[0]) > 0)
			foreach ($matches[0] as $match)
			{
				$count = count(explode('\?', $match)) - 1;
				$string = str_replace($match, '(.){'.$count.'}', $string);
			}
		
		return '#'.$string.'$#';
	}

}