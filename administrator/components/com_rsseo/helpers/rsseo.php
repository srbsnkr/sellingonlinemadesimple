<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'google.pr.php');

class rsseoHelper
{
	
	function add_to_sitemap(&$row, $i, $imgY = 'ok.png', $imgX = 'notok.png')
	{
		$img 	= $row->PageInSitemap ? $imgY : $imgX;
		$task 	= $row->PageInSitemap ? 'notinsitemap' : 'insitemap';
		$alt 	= $row->PageInSitemap ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
		$action = $row->PageInSitemap ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="'. JURI::root().'/administrator/components/com_rsseo/assets/images/'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}
	
	function is16()
	{
		$jversion = new JVersion();
		$current_version =  $jversion->getShortVersion();
		return (version_compare('1.6.0', $current_version) <= 0);
	}
	
	function fwrite($filename, $string, $write_type)
	{
		
		if (is_writable($filename)) {
			if (!$handle = fopen($filename, $write_type)) {
				 echo "Cannot open file ($filename)";
				 exit;
			}
			// Write $somecontent to our opened file.
			if (fwrite($handle, $string) === FALSE) {
				echo "Cannot write to file ($filename)";
				exit;
			}
			fclose($handle);

		} else {
			echo "The file $filename is not writable";
		}
	}
	
	/**
	 * Open a connection through several methods
	 */
	 function fopen($url,$int=1)
	 {
		$app =& JFactory::getApplication();
		$u =& JURI::getInstance('SERVER');
		$base = $u->getHost();

		$rsseoConfig = $app->getuserState('rsseoConfig');
		
		$errors = array();
		$url_info = parse_url($url);
		if($url_info['host'] == 'localhost') $url_info['host'] = '127.0.0.1';
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)";
		$data = false;

		$url = html_entity_decode($url);
		
		// cURL
		if (extension_loaded('curl'))
		{
			// Init cURL
			$ch = @curl_init();
			
			// Set options
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, $int);
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			@curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
			@curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			
			if ($rsseoConfig['proxy.enable'])
			{
				@curl_setopt($ch, CURLOPT_PROXY, $rsseoConfig['proxy.server']);
				@curl_setopt($ch, CURLOPT_PROXYPORT, $rsseoConfig['proxy.port']);
				@curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rsseoConfig['proxy.username'].":".$rsseoConfig['proxy.password']); 
			}
			
			// Set timeout
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			// Grab data
			$data = @curl_exec($ch);
			
			$objs = explode("\n",$data);
			foreach($objs as $obj)
				if(strpos($obj,'Location:') !== false)
				{
					$new_url = trim(str_replace('Location: ','',$obj));
					if(strpos($new_url,$base) !== false) $data = rsseoHelper::fopen($new_url,0);
				}
			
			$curl_error = curl_error($ch);
			
			// Clean up
			@curl_close($ch);
			
			if(empty($data)) $errors[] = 'cURL';
			
			// Return data
			if ($data !== false)
				return $data;
		}
		

		// fsockopen
		if (function_exists('fsockopen'))
		{
			$errno = 0;
			$errstr = '';

			// Set timeout
			$fsock = @fsockopen($url_info['host'], 80, $errno, $errstr, 5);
		
			if ($fsock)
			{
				@fputs($fsock, 'GET '.$url_info['path'].(!empty($url_info['query']) ? '?'.$url_info['query'] : '').' HTTP/1.1'."\r\n");
				@fputs($fsock, 'HOST: '.$url_info['host']."\r\n");
				@fputs($fsock, "User-Agent: ".$useragent."\r\n");
				@fputs($fsock, 'Connection: close'."\r\n\r\n");
        
				// Set timeout
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);
				
				$data = '';
				$passed_header = false;
				while (!@feof($fsock))
				{
					if ($passed_header)
						$data .= @fread($fsock, 1024);
					else
					{
						if (@fgets($fsock, 1024) == "\r\n")
							$passed_header = true;
					}
				}
				
				// Clean up
				@fclose($fsock);
				
				if(empty($data)) $errors[] = 'fsockopen';
				
				// Return data
				if ($data !== false)
					return $data;
			}
		}

	 	// fopen
		if (function_exists('fopen') && ini_get('allow_url_fopen'))
		{
			// Set timeout
			if (ini_get('default_socket_timeout') < 5)
				ini_set('default_socket_timeout', 5);
			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);
			@ini_set('user_agent',$useragent);
			
			$url = str_replace('://localhost', '://127.0.0.1', $url);
			
			$handle = @fopen ($url, 'r');
			
			if ($handle)
			{
				$data = '';
				while (!feof($handle))
					$data .= @fread($handle, 8192);
			
				// Clean up
				@fclose($handle);
			
				if(empty($data)) $errors[] = 'fopen';
			
				// Return data
				if ($data !== false)
					return $data;
			}
		}
						
		// file_get_contents
		if(function_exists('file_get_contents') && ini_get('allow_url_fopen'))
		{
			$url = str_replace('://localhost', '://127.0.0.1', $url);
			@ini_set('user_agent',$useragent);
			$data = @file_get_contents($url);
			
			if(empty($data)) $errors[] = 'file_get_contents';
			
			// Return data
			if ($data !== false)
				return $data;
		}
	
		if(function_exists('exec'))
		{
			$uri = JURI::getInstance($url);

			$url = str_replace(JURI::root(), '', $url);
			$url = str_replace('&',"^&",$url);
			$url = escapeshellcmd($url);
			$server = $uri->getScheme().'://'.$uri->getHost();
			$server = escapeshellcmd($server);
			$folder = JURI::root(true).'/';
			$folder = escapeshellcmd($folder);
			
			$php = strpos($rsseoConfig['php.folder'],'php') !== FALSE ? $rsseoConfig['php.folder'] : 'php' ;
			
			exec("$php ".JPATH_SITE.DS."indexseo.php $url $server $folder", $data);
			$data = implode('', $data);
		}
		
		return $data;
	}
	
	
	function checkconnections($url)
	{		
		$u =& JURI::getInstance('SERVER');
		$base = $u->getHost();

		$err = array();
		$ok = array();
		$url_info = parse_url($url);
		if($url_info['host'] == 'localhost') $url_info['host'] = '127.0.0.1';
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)";
		$data = false;

		// cURL
		if (extension_loaded('curl'))
		{
			// Init cURL
			$ch = @curl_init();
			
			// Set options
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			@curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
			
			// Set timeout
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			// Grab data
			$data = @curl_exec($ch);
			
			
			$objs = explode("\n",$data);
			foreach($objs as $obj)
				if(strpos($obj,'Location:') !== false)
				{
					$new_url = trim(str_replace('Location: ','',$obj));
					if(strpos($new_url,$base) !== false) $data = rsseoHelper::fopen($new_url,0);
				}
			
			// Clean up
			@curl_close($ch);
			
			if(empty($data)) $err[] = 'cURL'; else $ok[] = 'cURL';
		} else $err[] = 'cURL';
		
		// fsockopen
		if (function_exists('fsockopen'))
		{
			$errno = 0;
			$errstr = '';

			// Set timeout
			$fsock = @fsockopen($url_info['host'], 80, $errno, $errstr, 5);
		
			if ($fsock)
			{
				@fputs($fsock, 'GET '.$url_info['path'].(!empty($url_info['query']) ? '?'.$url_info['query'] : '').' HTTP/1.1'."\r\n");
				@fputs($fsock, 'HOST: '.$url_info['host']."\r\n");
				@fputs($fsock, "User-Agent: ".$useragent."\n");
				@fputs($fsock, 'Connection: close'."\r\n\r\n");
        
				// Set timeout
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);
				
				$data = '';
				$passed_header = false;
				while (!@feof($fsock))
				{
					if ($passed_header)
						$data .= @fread($fsock, 1024);
					else
					{
						if (@fgets($fsock, 1024) == "\r\n")
							$passed_header = true;
					}
				}
				
				// Clean up
				@fclose($fsock);
				
				if(empty($data)) $err[] = 'fsockopen'; else $ok[] = 'fsockopen';
			}
		} else $err[] = 'fsockopen';

	 	// fopen
		if (function_exists('fopen') && ini_get('allow_url_fopen'))
		{
			// Set timeout
			if (ini_get('default_socket_timeout') < 5)
				ini_set('default_socket_timeout', 5);
			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);
			@ini_set('user_agent',$useragent);
			
			$url = str_replace('://localhost', '://127.0.0.1', $url);
			
			$handle = @fopen ($url, 'r');
			
			if ($handle)
			{
				$data = '';
				while (!feof($handle))
					$data .= @fread($handle, 8192);
			
				// Clean up
				@fclose($handle);
			
				if(empty($data)) $err[] = 'fopen'; else $ok[] = 'fopen';
			
			}
		} else $err[] = 'fopen';
						
		// file_get_contents
		if(function_exists('file_get_contents') && ini_get('allow_url_fopen'))
		{
			$url = str_replace('://localhost', '://127.0.0.1', $url);
			@ini_set('user_agent',$useragent);
			$data = @file_get_contents($url);
			
			if(empty($data)) $err[] = 'file_get_contents'; else $ok[] = 'file_get_contents';
		} else $err[] = 'file_get_contents';
		
		$return = new stdClass();
		$return->err = $err;
		$return->ok = $ok;
		return $return;
	}
	
	
	function file_get_html($url) 
	{
		header('Content-type: text/html; charset=utf-8');
		$content = rsseoHelper::fopen($url);
	
		return new HtmlParser ($content);
	}
	
	function clean_url($url)
	{
		$internal_links[] = JURI::root();
		$internal_links[] = JURI::root(true);

		foreach($internal_links as $internal_link)
		{
			$url = str_replace($internal_link,'',$url);
		}
		
		//if url still contains http:// it's an external link
		if(strpos($url,'http://') !== false) return null;
		if(strpos($url,'https://') !== false) return null;
		if(strpos($url,'ftp://') !== false) return null;
		
		//let's clear anything after #
		$url_exp = explode('#',$url);
		$url = $url_exp[0];
		
		$array_extensions = array('jpg','jpeg','gif','png','pdf','doc','xls','odt','mp3','wav','wmv','wma','evy','fif','spl','hta','acx','hqx','doc','dot','bin','class','dms','exe','lha','lzh','oda','axs','pdf','prf','p10','crl','ai','eps','ps','rtf','setpay','setreg','xla','xlc','xlm','xls','xlt','xlw','msg','sst','cat','stl','pot','pps','ppt','mpp','wcm','wdb','wks','wps','hlp','bcpio','cdf','z','tgz','cpio','csh','dcr','dir','dxr','dvi','gtar','gz','hdf','ins','isp','iii','js','latex','mdb','crd','clp','dll','m13','m14','mvb','wmf','mny','pub','scd','trm','wri','cdf','nc','pma','pmc','pml','pmr','pmw','p12','pfx','p7b','spc','p7r','p7c','p7m','p7s','sh','shar','sit','sv4cpio','sv4crc','tar','tcl','tex','texi','texinfo','roff','t','tr','man','me','ms','ustar','src','cer','crt','der','pko','zip','au','snd','mid','rmi','mp3','aif','aifc','aiff','m3u','ra','ram','wav','bmp','cod','gif','ief','jpe','jpeg','jpg','jfif','svg','tif','tiff','ras','cmx','ico','pnm','pbm','pgm','ppm','rgb','xbm','xpm','xwd','nws','css','323','stm','uls','bas','c','h','txt','rtx','sct','tsv','htt','htc','etx','vcf','mp2','mpa','mpe','mpeg','mpg','mpv2','mov','qt','lsf','lsx','asf','asr','asx','avi','movie','flr','vrml','wrl','wrz','xaf','xof','swf');
		
		for($i = 0;$i<count($array_extensions);$i++)
			if(substr($url, strlen($url) - (strlen($array_extensions[$i]) + 1))=='.'.$array_extensions[$i])
				return null;
		
		if(substr($url,0,1) == '/') $url = substr($url,1);
		
		return $url;
	}

	//get the page rank 
	function getPageRank($url)
	{
		$pagerank = -1;
		$gpr = new GooglePR();
		$gpr->useCache = false;
		$pagerank = $gpr->GetPR($url);
		
		return intval($pagerank);
	}

	//get the alexa rank
	function getAlexaRank($Competitor)
	{
		$Competitor = trim($Competitor);
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		$url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url=' . urlencode($Competitor);
		$v = rsseoHelper::fopen($url);
		preg_match('/\<popularity url\="(.*?)" TEXT\="([0-9]+)"\/\>/si', $v, $r);		
		$alexa_rank= isset($r[2]) ? $r[2] : '-1';
		return $alexa_rank;
	}
	
	//get the Tehnorati Rank
	function getTehnoratiRank($Competitor)
	{
		$Competitor = trim($Competitor);
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		$url = 'http://technorati.com/blogs/'. urlencode($Competitor);
		$v = rsseoHelper::fopen($url);
		
		preg_match('/Authority: (.*)<\/strong>/isU',$v,$match);		
		if(!empty($match))
		{
			$tehnoratiRank = ($match[1]) ? intval($match[1]) : '-1';
			return $tehnoratiRank;
		}else return  JText::_('RSSEO_COMPETITOR_NOT_PROCESSED');
	}
	
	//get the google backlinks score
	function getGoogleBacklinks($Competitor)
	{
		$app =& JFactory::getApplication();
		$rsseoConfig = $app->getuserState('rsseoConfig');
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		
		$url = 'http://www.'.$rsseoConfig['google.domain'].'/search?q=link:' . urlencode($Competitor);
		$v = rsseoHelper::fopen($url);
	
		$pattern = '#<div id=resultStats>(.*?)<nobr>#is';
		
		preg_match($pattern, $v, $match);
		
		if(!empty($match[1]))
		{
			$result = trim($match[1]);
			$google_backlinks = preg_replace('#[^0-9]#', '', $result);
		}
		if(!empty($google_backlinks))
		{
			return $google_backlinks;
		} else return JText::_('RSSEO_COMPETITOR_NOT_PROCESSED');
	}
	
	//get the yahoo backlinks score
	function getYahooBacklinks($Competitor)
	{
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		$url = 'https://siteexplorer.search.yahoo.com/search?p='.urlencode($Competitor).'&bwm=i&bwmf=s&bwmo=d';
		$v = rsseoHelper::fopen($url);
		
		//$pattern = '#<li class="last"><span class="btn">(.*?) \(([0-9\,\.]+)\)<\/span><\/li>#i';
		$pattern = '#<ol id="results-tab" class="btn-list"><li><a class="btn" href="(.*?)">(.*?)<\/a><\/li><li><span class="btn">(.*?) \(([0-9\,\.]+)\)<\/span>#i';
		
		preg_match($pattern, $v, $matches);
		if(isset($matches[4])) return str_replace(array(',','.'),'',$matches[4]);
		else return -1;
	}
	
	//get the bing backlinks score
	function getBingBacklinks($Competitor)
	{
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		$url = 'http://www.bing.com/search?filt=all&q=link%3D' . urlencode($Competitor);
		$v = rsseoHelper::fopen($url);
		
		$pattern = '#<span class="sb_count" id="count">(.*?)<\/span>#i';
		preg_match($pattern, $v, $matches);
		
		if(!empty($matches[1]))
		{
			$str = $matches[1];
			$exp = explode(' ', $str);
		
			if(isset($exp[2])) $bing_backlinks = $exp[2];
			else $bing_backlinks=-1;			
			return str_replace(array(',','.'),'',$bing_backlinks);
		
		} else return JText::_('RSSEO_COMPETITOR_NOT_PROCESSED');
	}
	
	//get the google pages score
	function getGooglePages($Competitor)
	{
		$app =& JFactory::getApplication();
		$rsseoConfig = $app->getuserState('rsseoConfig');
		
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		
		$url = 'http://www.'.$rsseoConfig['google.domain'].'/search?q=site%3A' . urlencode($Competitor);
		$v = rsseoHelper::fopen($url);

		$pattern = '#<div id=resultStats>(.*?)<nobr>#is';
		
		preg_match($pattern, $v, $match);
		
		if(!empty($match[1]))
		{
			$result = trim($match[1]);
			//$google_pages = str_replace(array('&nbsp;','\'',',',',','`'),'',$result[1]);
			$google_pages = preg_replace('#[^0-9]#', '', $result);
		}
		if(!empty($google_pages))
		{
			return $google_pages;
		} else return JText::_('RSSEO_COMPETITOR_NOT_PROCESSED');
	}
	
	//get the yahoo pages score
	function getYahooPages($Competitor)
	{
		$Competitor = trim($Competitor);
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		$url = 'http://siteexplorer.search.yahoo.com/search?p='.urlencode($Competitor).'&bwmf=s&bwmo=d';
		$v = rsseoHelper::fopen($url,1);
		
		//$pattern = '#<li class="first"><span class="btn">(.*?) \(([0-9,\.]+)\)<\/span><\/li>#i';
		$pattern = '#<ol id="results-tab" class="btn-list"><li><span class="btn">(.*?) \(([0-9,\.]+)\)<\/span>#i';
		preg_match($pattern, $v, $matches);		
		if(isset($matches[2])) return str_replace(array(',','.'),'',$matches[2]);
		else return -1;
	}
	
	//get the bing pages score
	function getBingPages($Competitor)
	{
		$Competitor = str_replace(array('http://','https://','www.'),'',$Competitor);
		$url = 'http://www.bing.com/search?filt=all&q=site%3D' . urlencode($Competitor);
		$v = rsseoHelper::fopen($url);
		
		$pattern = '#<span class="sb_count" id="count">(.*?)<\/span>#i';
		preg_match($pattern, $v, $matches);
		if(!empty($matches[1]))
		{
			$str = $matches[1];
			$exp = explode(' ', $str);
			
			if(isset($exp[2])) $bing_pages = $exp[2];
			else $bing_pages=-1;
			
			return str_replace(array(',','.'),'',$bing_pages);
		} else return JText::_('RSSEO_COMPETITOR_NOT_PROCESSED');
	}
	
	//is the site in dmoz
	function getDmoz($competitor)
	{
		$competitor = str_replace(array('http://','https://','www.'),array('','',''),$competitor);
		$url = 'http://search.dmoz.org/search?q='.urlencode($competitor);
		$v = rsseoHelper::fopen($url,0);
		
		$pattern = '#<h3 class=\"open-dir-sites\">(.*?)<\/h3>#is';
		preg_match($pattern,$v,$matches);
		
		if (!empty($matches[1]))
		{
			$pattern = '#<small>\((.*?) of (.*?)\)</small>#is';
			preg_match($pattern,$matches[1],$match);
			
			if (!empty($match[2])) return 1; else return 0;
			
		} else return 0;
	}
	
	function keywordDensity($string,$keyword)
	{
		$string = utf8_encode($string);
		$pattern =  "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u";
		$total_words = preg_match_all($pattern, $string, $matches);
		$times_used  = mb_substr_count($string,$keyword);
		
		if (!$total_words) return '0.00 %';
		if (!$times_used) return '0.00 %';
		
		$density = ($times_used / $total_words) * 100;
		return number_format($density,2).' %';
	}
	
	
	//check the given page
	function checkPage($idPage)
	{
		$app =& JFactory::getApplication();
		$rsseoConfig = $app->getuserState('rsseoConfig');
		$db = & JFactory::getDBO();
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsseo'.DS.'tables');
		$page= & JTable::getInstance('rsseo_pages','Table');
		
		$page->load($idPage);
		$parser = rsseoHelper::file_get_html(JURI::root().$page->PageURL,0);
		$html = rsseoHelper::fopen(JURI::root().$page->PageURL);
		$link = JURI::root().$page->PageURL;
		
		$title = '';
		$description = '';
		$keywords = '';
		$images = 0;
		$images_no_alt = 0;
		$images_no_hw = 0;
		$links = 0;
		
		preg_match('#<title>(.*?)<\/title>#',$html,$match);
		preg_match_all('#<h([0-9+])(.*?)<\/h([0-9+])>#is',$html,$matches);
		
		$title = @$match[1];
		$headings = count($matches[0]);
		
		while($parser->parse())
		{
			if($parser->iNodeName == 'a' && isset($parser->iNodeAttributes['href'])) $links++;
			if(strtolower($parser->iNodeName) == 'meta' && @$parser->iNodeAttributes['name'] == 'description' ) $description = $parser->iNodeAttributes['content'];
			if(strtolower($parser->iNodeName) == 'meta' && @$parser->iNodeAttributes['name'] == 'keywords' ) $keywords = $parser->iNodeAttributes['content'];	
			if(empty($title) && strtolower($parser->iNodeName) == 'meta' && @$parser->iNodeAttributes['name'] == 'title' ) $title = $parser->iNodeAttributes['content'];	
			if(strtolower($parser->iNodeName) == 'img') $images++;
			if(strtolower($parser->iNodeName) == 'img' && !isset($parser->iNodeAttributes['alt'])) $images_no_alt++;
			if(strtolower($parser->iNodeName) == 'img' && !isset($parser->iNodeAttributes['width']) && !isset($parser->iNodeAttributes['height']) ) $images_no_hw++;
		}
		
		$valid_text = strtolower(strip_tags($html));
		$density_keywords = $page->PageKeywordsDensity;
		
		if (!empty($density_keywords))
		{
			$density_keywords = explode(',',$density_keywords);
			array_walk($density_keywords,array('rsseoHelper','lowercasearray'));
			$density_keywords = array_unique($density_keywords);
			$densityparams = array();
			
			foreach ($density_keywords as $keyword)
			{
				if (empty($keyword)) continue;
				$densityparams[$keyword] = rsseoHelper::keywordDensity($valid_text,$keyword);
			}
				
			$registry = JRegistry::getInstance('density');
			$registry->loadArray($densityparams);			
			$page->densityparams = $registry->toString();
		} else $page->densityparams = '';
		
		$page->PageTitle = $title;
		$page->PageDescription = $description;
		$page->PageKeywords = $keywords;
		
		//build the params
		$params = array();
		
		//check if url is sef friendly
		if(strpos($page->PageURL,".php?") === FALSE) $params['url_sef'] = 1;
		else $params['url_sef'] = 0;
		
		
		//check if page title is unique
		$db->setQuery("SELECT COUNT(*) cnt FROM #__rsseo_pages WHERE PageTitle = '".$db->getEscaped($page->PageTitle)."' AND published = 1 ");
		$params['duplicate_title'] = $db->loadResult();
		
		//check title length
		$params['title_length'] = strlen($page->PageTitle);
		
		//check if page meta description is unique
		$db->setQuery("SELECT COUNT(*) cnt FROM #__rsseo_pages WHERE PageDescription = '".$db->getEscaped($page->PageDescription)."' AND published = 1 ");
		$params['duplicate_desc'] = $db->loadResult();		
		
		//check description length
		$params['description_length'] = strlen($page->PageDescription);
		
		//check number of keywords
		$keyw = (trim($keywords) != '') ? explode(',',$page->PageKeywords) : array();
		$params['keywords'] = count($keyw);
		
		$params['headings'] = $headings;
		$params['images'] = $images;
		$params['images_wo_alt'] = $images_no_alt;
		$params['images_wo_hw'] = $images_no_hw;
		$params['links'] = $links;
		
		$reg = JRegistry::getInstance('');
		$reg->loadArray($params);
		$page->params = $reg->toString();
		
		//the raw html
		$page->_link = $link;
		
		//the page grade
		$grade = 0;
		$total = 0;
		
		if($params['url_sef'] == 1 && $rsseoConfig['crawler.sef']) $grade ++;
		if($params['duplicate_title'] == 1 && $rsseoConfig['crawler.title.duplicate']) $grade ++;
		if($params['title_length'] >=10 && $params['title_length'] <= 70 && $rsseoConfig['crawler.title.length']) $grade ++;
		if($params['duplicate_desc'] == 1 && $rsseoConfig['crawler.description.duplicate']) $grade ++;
		if($params['description_length'] >= 70 && $params['description_length'] <= 150 && $rsseoConfig['crawler.description.length']) $grade ++;
		if($params['keywords']<=10 && $rsseoConfig['crawler.keywords']) $grade ++;
		if($params['headings']>0 && $rsseoConfig['crawler.headings']) $grade ++;
		if($params['images']<=10 && $rsseoConfig['crawler.images']) $grade ++;
		
		if($params['images_wo_alt']==0 && $rsseoConfig['crawler.images.alt']) $grade ++;
		if($params['images_wo_hw']==0 && $rsseoConfig['crawler.images.hw']) $grade ++;
		if($params['links'] <= 100) $grade ++;
		
		if($rsseoConfig['crawler.sef']) $total ++;
		if($rsseoConfig['crawler.title.duplicate']) $total ++;
		if($rsseoConfig['crawler.title.length']) $total ++;
		if($rsseoConfig['crawler.description.duplicate']) $total ++;
		if($rsseoConfig['crawler.description.length']) $total ++;
		if($rsseoConfig['crawler.keywords']) $total ++;
		if($rsseoConfig['crawler.headings']) $total ++;
		if($rsseoConfig['crawler.images']) $total ++;
		if($rsseoConfig['crawler.images.alt']) $total ++;
		if($rsseoConfig['crawler.images.hw']) $total ++;
		if($rsseoConfig['crawler.intext.links']) $total ++;
		
		$page->PageGrade = ($grade * 100 / $total);
		
		return $page;
	}
	
	function lowercasearray(&$item)
	{
		$item = strtolower(trim($item));
	}
	
}


/*
 * Copyright (c) 2003 Jose Solorzano.  All rights reserved.
 * Redistribution of source must retain this copyright notice.
 *
 * Jose Solorzano (http://jexpert.us) is a software consultant.
 *
 * Contributions by:
 * - Leo West (performance improvements)
 */

define ("NODE_TYPE_START",0);
define ("NODE_TYPE_ELEMENT",1);
define ("NODE_TYPE_ENDELEMENT",2);
define ("NODE_TYPE_TEXT",3);
define ("NODE_TYPE_COMMENT",4);
define ("NODE_TYPE_DONE",5);

/**
 * Class HtmlParser.
 * To use, create an instance of the class passing
 * HTML text. Then invoke parse() until it's false.
 * When parse() returns true, $iNodeType, $iNodeName
 * $iNodeValue and $iNodeAttributes are updated.
 *
 * To create an HtmlParser instance you may also
 * use convenience functions HtmlParser_ForFile
 * and HtmlParser_ForURL.
 */
class HtmlParser {

    /**
     * Field iNodeType.
     * May be one of the NODE_TYPE_* constants above.
     */
    var $iNodeType;

    /**
     * Field iNodeName.
     * For elements, it's the name of the element.
     */
    var $iNodeName = "";

    /**
     * Field iNodeValue.
     * For text nodes, it's the text.
     */
    var $iNodeValue = "";

    /**
     * Field iNodeAttributes.
     * A string-indexed array containing attribute values
     * of the current node. Indexes are always lowercase.
     */
    var $iNodeAttributes;

    // The following fields should be 
    // considered private:

    var $iHtmlText;
    var $iHtmlTextLength;
    var $iHtmlTextIndex = 0;
    var $iHtmlCurrentChar;
    var $BOE_ARRAY;
    var $B_ARRAY;
    var $BOS_ARRAY;
    
    /**
     * Constructor.
     * Constructs an HtmlParser instance with
     * the HTML text given.
     */
    function HtmlParser ($aHtmlText) {
        $this->iHtmlText = $aHtmlText;
        $this->iHtmlTextLength = strlen($aHtmlText);
        $this->iNodeAttributes = array();
        $this->setTextIndex (0);

        $this->BOE_ARRAY = array (" ", "\t", "\r", "\n", "=" );
        $this->B_ARRAY = array (" ", "\t", "\r", "\n" );
        $this->BOS_ARRAY = array (" ", "\t", "\r", "\n", "/" );
    }

    /**
     * Method parse.
     * Parses the next node. Returns false only if
     * the end of the HTML text has been reached.
     * Updates values of iNode* fields.
     */
    function parse() {
        $text = $this->skipToElement();
        if ($text != "") {
            $this->iNodeType = NODE_TYPE_TEXT;
            $this->iNodeName = "Text";
            $this->iNodeValue = $text;
            return true;
        }
        return $this->readTag();
    }

    function clearAttributes() {
        $this->iNodeAttributes = array();
    }

    function readTag() {
        if ($this->iCurrentChar != "<") {
            $this->iNodeType = NODE_TYPE_DONE;
            return false;
        }
        $this->clearAttributes();
        $this->skipMaxInTag ("<", 1);
        if ($this->iCurrentChar == '/') {
            $this->moveNext();
            $name = $this->skipToBlanksInTag();
            $this->iNodeType = NODE_TYPE_ENDELEMENT;
            $this->iNodeName = $name;
            $this->iNodeValue = "";            
            $this->skipEndOfTag();
            return true;
        }
        $name = $this->skipToBlanksOrSlashInTag();
        if (!$this->isValidTagIdentifier ($name)) {
                $comment = false;
                if (strpos($name, "!--") === 0) {
                    $ppos = strpos($name, "--", 3);
                    if (strpos($name, "--", 3) === (strlen($name) - 2)) {
                        $this->iNodeType = NODE_TYPE_COMMENT;
                        $this->iNodeName = "Comment";
                        $this->iNodeValue = "<" . $name . ">";
                        $comment = true;                        
                    }
                    else {
                        $rest = $this->skipToStringInTag ("-->");    
                        if ($rest != "") {
                            $this->iNodeType = NODE_TYPE_COMMENT;
                            $this->iNodeName = "Comment";
                            $this->iNodeValue = "<" . $name . $rest;
                            $comment = true;
                            // Already skipped end of tag
                            return true;
                        }
                    }
                }
                if (!$comment) {
                    $this->iNodeType = NODE_TYPE_TEXT;
                    $this->iNodeName = "Text";
                    $this->iNodeValue = "<" . $name;
                    return true;
                }
        }
        else {
                $this->iNodeType = NODE_TYPE_ELEMENT;
                $this->iNodeValue = "";
                $this->iNodeName = $name;
                while ($this->skipBlanksInTag()) {
                    $attrName = $this->skipToBlanksOrEqualsInTag();
                    if ($attrName != "" && $attrName != "/") {
                        $this->skipBlanksInTag();
                        if ($this->iCurrentChar == "=") {
                            $this->skipEqualsInTag();
                            $this->skipBlanksInTag();
                            $value = $this->readValueInTag();
                            $this->iNodeAttributes[strtolower($attrName)] = $value;
                        }
                        else {
                            $this->iNodeAttributes[strtolower($attrName)] = "";
                        }
                    }
                }
        }
        $this->skipEndOfTag();
        return true;            
    }

    function isValidTagIdentifier ($name) {
        return preg_match("#^[A-Za-z0-9_\\-]+$#", $name);
    }
    
    function skipBlanksInTag() {
        return "" != ($this->skipInTag ($this->B_ARRAY));
    }

    function skipToBlanksOrEqualsInTag() {
        return $this->skipToInTag ($this->BOE_ARRAY);
    }

    function skipToBlanksInTag() {
        return $this->skipToInTag ($this->B_ARRAY);
    }

    function skipToBlanksOrSlashInTag() {
        return $this->skipToInTag ($this->BOS_ARRAY);
    }

    function skipEqualsInTag() {
        return $this->skipMaxInTag ("=", 1);
    }

    function readValueInTag() {
        $ch = $this->iCurrentChar;
        $value = "";
        if ($ch == "\"") {
            $this->skipMaxInTag ("\"", 1);
            $value = $this->skipToInTag ("\"");
            $this->skipMaxInTag ("\"", 1);
        }
        else if ($ch == "'") {
            $this->skipMaxInTag ("'", 1);
            $value = $this->skipToInTag ("'");
            $this->skipMaxInTag ("'", 1);
        }                
        else {
            $value = $this->skipToBlanksInTag();
        }
        return $value;
    }

    function setTextIndex ($index) {
        $this->iHtmlTextIndex = $index;
        if ($index >= $this->iHtmlTextLength) {
            $this->iCurrentChar = -1;
        }
        else {
            $this->iCurrentChar = $this->iHtmlText{$index};
        }
    }

    function moveNext() {
        if ($this->iHtmlTextIndex < $this->iHtmlTextLength) {
            $this->setTextIndex ($this->iHtmlTextIndex + 1);
            return true;
        }
        else {
            return false;
        }
    }

    function skipEndOfTag() {
        while (($ch = $this->iCurrentChar) !== -1) {
            if ($ch == ">") {
                $this->moveNext();
                return;
            }
            $this->moveNext();
        }
    }

    function skipInTag ($chars) {
        $sb = "";
        while (($ch = $this->iCurrentChar) !== -1) {
            if ($ch == ">") {
                return $sb;
            } else {
                $match = false;
                for ($idx = 0; $idx < count($chars); $idx++) {
                    if ($ch == $chars[$idx]) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    return $sb;
                }
                $sb .= $ch;
                $this->moveNext();
            }
        }
        return $sb;
    }

    function skipMaxInTag ($chars, $maxChars) {
        $sb = "";
        $count = 0;
        while (($ch = $this->iCurrentChar) !== -1 && $count++ < $maxChars) {
            if ($ch == ">") {
                return $sb;
            } else {
                $match = false;
                for ($idx = 0; $idx < count($chars); $idx++) {
                    if ($ch == $chars[$idx]) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    return $sb;
                }
                $sb .= $ch;
                $this->moveNext();
            }
        }
        return $sb;
    }

    function skipToInTag ($chars) {
        $sb = "";
        while (($ch = $this->iCurrentChar) !== -1) {
            $match = $ch == ">";
            if (!$match) {
                for ($idx = 0; $idx < count($chars); $idx++) {
                    if ($ch == $chars[$idx]) {
                        $match = true;
                        break;
                    }
                }
            }
            if ($match) {
                return $sb;
            }
            $sb .= $ch;
            $this->moveNext();
        }
        return $sb;
    }

    function skipToElement() {
        $sb = "";
        while (($ch = $this->iCurrentChar) !== -1) {
            if ($ch == "<") {
                return $sb;
            }
            $sb .= $ch;
            $this->moveNext();
        }
        return $sb;             
    }

    /**
     * Returns text between current position and $needle,
     * inclusive, or "" if not found. The current index is moved to a point
     * after the location of $needle, or not moved at all
     * if nothing is found.
     */
    function skipToStringInTag ($needle) {
        $pos = strpos ($this->iHtmlText, $needle, $this->iHtmlTextIndex);
        if ($pos === false) {
            return "";
        }
        $top = $pos + strlen($needle);
        $retvalue = substr ($this->iHtmlText, $this->iHtmlTextIndex, $top - $this->iHtmlTextIndex);
        $this->setTextIndex ($top);
        return $retvalue;
    }
}

