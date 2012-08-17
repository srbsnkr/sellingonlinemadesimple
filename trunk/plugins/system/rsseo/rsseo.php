<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$_SESSION['VMCHECK'] = 'NOCHECK';

jimport( 'joomla.plugin.plugin' );

/**
 * RSSeo system plugin
 */
class plgSystemRsseo extends JPlugin
{
		
		/**
         * Constructor
         *
         * For php4 compatability we must not use the __constructor as a constructor for plugins
         * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
         * This causes problems with cross-referencing necessary for the observer design pattern.
         *
         * @access      protected
         * @param       object  $subject The object to observe
         * @param       array   $config  An array that holds the plugin configuration
         * @since       1.0
         */
        function plgSystemRsseo( &$subject, $config )
        {
			parent::__construct( $subject, $config);				
		   
			// load plugin parameters
			jimport( 'joomla.html.parameter' );
			$this->_plugin = & JPluginHelper::getPlugin('system', 'rsseo');
			$this->_params = new JParameter($this->_plugin->params);
        }
		
		
		/**
         * Do something onAfterInitialise 
         */
		function onAfterInitialise()
		{
			$db = JFactory::getDBO();
			$app =& JFactory::getApplication();
			$u =& JURI::getInstance('SERVER');
			$config = new JConfig();
			$sef = $config->sef;
			$sitename = $config->sitename;
			
			if($app->getName() == 'site')
			{	
				//get the current url of the page
				if($sef == 0)
					$curl = $u->getScheme().'://'.$u->getHost().JRequest::getURI();
				 else
				 {
					$curl = JURI::current();
					$uri = JFactory::getURI();
					$curl = $uri->toString();
				}
				
				
				//load the redirects 
				$db->setQuery("SELECT * FROM #__rsseo_redirects WHERE published = 1 ");
				$redirects = $db->loadObjectList();
				
				//redirect method
				
				foreach($redirects as $redirect)
				{					
					if(urldecode($this->_getRoute(trim($redirect->RedirectFrom))) == urldecode($curl) )
					{
						//check to see if the redirect link is internal
						if (JURI::isInternal($redirect->RedirectTo))
						{
							if (strpos($redirect->RedirectTo,JURI::root()) !== FALSE)
							{
								$redirect_url = str_replace(JURI::root(),'',$redirect->RedirectTo);
								$redirect_url = $this->_getRoute($redirect_url);
							}
							else $redirect_url = $this->_getRoute($redirect->RedirectTo);
						} else $redirect_url = $redirect->RedirectTo;
						//if the redirect url is empty, redirect to homepage
						if (empty($redirect_url)) $redirect_url = JURI::root();
						
						if($redirect->RedirectType == 301)
						{
							header("HTTP/1.1 301 Moved Permanently");
							header("Location: ".$redirect_url);
						} else if ($redirect->RedirectType == 302)
							header("Location: ".$redirect_url);
						exit;
					}
				}				
			}
			
			
			//canonicalization
			$enablecan = $this->_params->get('enablecan','0');
			$host = $this->_params->get('domain','');
			$host = str_replace(array('http://','https://'),'',$host);
			
			if($enablecan == 1 && trim($host) != '')
			{
				
				if(@$_SERVER['HTTP_HOST'] == $host || @$_SERVER['SERVER_NAME'] == $host) {
					return true;	
				}
				
				$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';

				$url = $protocol . $host . $_SERVER['REQUEST_URI'];
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '. $url);
				exit();
			}
			
		}
		
		
		
        /**
         * Do something onAfterDispatch 
         */
        function onAfterDispatch()
        {			
			$app =& JFactory::getApplication();
			$db = JFactory::getDBO();
			$doc =& JFactory::getDocument();
			$u =& JURI::getInstance('SERVER');
			$config = new JConfig();
			$sef = $config->sef;
			$sitename = $config->sitename;
			$format = JRequest::getCmd('format');
			
			if ($format == 'feed') return;
			
			if($app->getName() == 'site')
			{				
				$genabled = $this->_params->get('enable','0');
				$type = $this->_params->get( 'type','google-site-verification');
				$gcontent = $this->_params->get( 'content' );
				if($genabled == 1)
					$doc->setMetaData($type, $gcontent);
				
				$yenabled = $this->_params->get('enabley','0');
				$ycontent = $this->_params->get( 'contenty' );
				if($yenabled == 1)
					$doc->setMetaData('y_key', $ycontent);
				
				$benabled = $this->_params->get('enableb','0');
				$bcontent = $this->_params->get( 'contentb' );
				if($benabled == 1)
					$doc->setMetaData('msvalidate.01', $bcontent);
				
				$db->setQuery("SELECT * FROM `#__rsseo_config`");
				$rsseoConfigDb = $db->loadObjectList();
				
				$rsseoConfig = array();
				foreach ($rsseoConfigDb as $rowConfig)
					$rsseoConfig[$rowConfig->ConfigName] = $rowConfig->ConfigValue;
				
				//add site name in title
				if($rsseoConfig['site.name.in.title'] == 1 && !empty($sitename))
				{
					$old = $doc->getTitle();
					if (!empty($old))
					{
						if(strpos($old,$sitename) === FALSE)
						{
							if($rsseoConfig['site.name.position'] == 0)
								$doc->setTitle($old.' '.$rsseoConfig['site.name.separator'].' '.$sitename);
							else
								$doc->setTitle($sitename.' '.$rsseoConfig['site.name.separator'].' '.$old);
						}
					}
				}
				
				//get the curent URL
				$host = $this->getURL();
				
				//see if the auto-crawler option is enabled
				$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'crawler.enable.auto'");
				$enable = $db->loadResult();
				
				if($enable == 1)
				{				
					//get the ignored links
					$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'crawler.ignore'");
					$ignored = $db->loadResult();
					$ignored = str_replace("\r",'',$ignored);
					$ignored = explode("\n",$ignored);
					
					$auto_host = str_replace(JURI::root(),'',$host);
					
					//see if the current page is already crawled
					$db->setQuery("SELECT IdPage FROM #__rsseo_pages WHERE PageURL = '".$auto_host."'");
					$result = $db->loadResult();
					
					//if the page hasn`t been crawled , we crawl it
					if(empty($result) && !$this->_is_ignored($auto_host, $ignored))
					{
						$db->setQuery("INSERT INTO #__rsseo_pages SET PageURL = '".$db->getEscaped($auto_host)."', PageTitle ='".$db->getEscaped($doc->getTitle())."', PageKeywords ='".$db->getEscaped($doc->getMetaData('keywords'))."', PageDescription = '".$db->getEscaped($doc->getDescription())."', PageSitemap=0, PageCrawled=0, PageLevel = 127");
						$db->query();
					}
				}
				
				//load the page details 
				$db->setQuery("SELECT IdPage, PageModified, PageTitle , PageKeywords , PageDescription, PageCrawled , PageLevel FROM #__rsseo_pages WHERE PageURL = '".str_replace('&','&amp;',$host)."' AND published = 1 LIMIT 1");
				$page = $db->loadObject();
				
				//set the new Title , MetaKeywords , and the Description
				if(!empty($page))
				{
					if(!($page->PageLevel == 0 && $page->PageTitle == null))
					{
						$doc->setTitle($page->PageTitle);
						
						if ($config->MetaTitle)
							$doc->setMetaData('title',htmlspecialchars($page->PageTitle, ENT_QUOTES, 'UTF-8'));
							
						$doc->setMetaData('keywords',htmlspecialchars($page->PageKeywords, ENT_QUOTES, 'UTF-8'));
						$doc->setDescription(htmlspecialchars($page->PageDescription, ENT_QUOTES, 'UTF-8'));
					}
				}
			}
			
			$this->rsseofunction();
        }
		
        /**
         * Keywords Replacement and Redirect function 
         */
        function rsseofunction()
        {
			$app =& JFactory::getApplication();
			$db = JFactory::getDBO();
			$doc = JFactory::getDocument();
			$u =& JURI::getInstance('SERVER');
			$config = new JConfig();
			$sef = $config->sef;
			

			if($app->getName() == 'site')
			{
				//get the body of the component
				$body = $doc->getBuffer();
				$body = !isset($body['component']['']) ? @$body : @$body['component'][''] ;
				
				$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'component.heading'");
				$componentHeading = $db->loadResult();
				$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'content.heading'");
				$contentHeading = $db->loadResult();
				
				$CompStartElement = '<'.$componentHeading.'>'; 
				$CompEndElement   = '</'.$componentHeading.'>';
				$ContStartElement = '<'.$contentHeading.'>'; 
				$ContEndElement   = '</'.$contentHeading.'>';
				
				//search for the contentheading and componentheading class
				preg_match_all('#<div class="contentheading">(.*?)<\/div>#is', $body, $content);
				preg_match_all('#<div class="componentheading">(.*?)<\/div>#is', $body, $component);
				preg_match_all('#<td class="contentheading"(.*?)>(.*?)<\/td>#is', $body, $content_td);
				preg_match_all('#<td class="componentheading"(.*?)>(.*?)<\/td>#is', $body, $component_td);
				
				
				$contentCounter = count($content[0]);
				$contentCounter_td = count($content_td[2]);
				$componentCounter = count($component[0]);
				$componentCounter_td = count($component_td[0]);
				
				//replace the contentheading class
				if($contentHeading != '0')
					for($i=0;$i<$contentCounter;$i++)
						$body = str_replace($content[0][$i],$ContStartElement.$content[1][$i].$ContEndElement,$body);
				
				//replace the contentheading class in td
				if($contentHeading != '0')
					for($i=0;$i<$contentCounter_td;$i++)
						$body = str_replace($content_td[0][$i],'<td>'.$ContStartElement.$content_td[2][$i].$ContEndElement.'</td>',$body);
				
				//replace the componentheading class
				if($componentHeading != '0')
					for($i=0;$i<$componentCounter;$i++)
						$body = str_replace($component[0][$i],$CompStartElement.$component[1][$i].$CompEndElement,$body);
				
				//replace the componentheading class in td
				if($componentHeading != '0')
					for($i=0;$i<$componentCounter_td;$i++)
						$body = str_replace($component_td[0][$i],'<td>'.$CompStartElement.$component_td[2][$i].$CompEndElement.'</td>',$body);
			
				$doc->setBuffer($body,'component');
			}
        }
		
		function getURL()
		{
			$u =& JURI::getInstance('SERVER');
			$db =& JFactory::getDBO();
			$config = new JConfig();
			$sef = $config->sef;
			$base = JURI::base();			
			
			//check for joomfish
			$db->setQuery("SELECT `published` FROM `#__plugins` WHERE `element` = 'jfrouter' AND `folder` = 'system' ");
			$JRouterEnable = $db->loadResult();
			
			$db->setQuery("SELECT `published` FROM `#__plugins` WHERE `element` = 'shsef' AND `folder` = 'system' ");
			$SHPlugin = $db->loadResult();
			
			$sh404sef = $SHPlugin && file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'shsef.php');
			
			if ($sh404sef)
			{
				$host = $u->_uri;
				$host = str_replace(JURI::root(),'',$host);
				if (substr($host, 0, 1) == '/')
					$host = substr($host, 1, strlen($host));
				
				return $host;
			}
			
			//prepare link for when joomfish is installed
			if ($sef == 1)
			{
				// joomfish hack
				if(file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'jfrouter.php') && $JRouterEnable)
				{
					$rewrite_mode = $config->sef_rewrite;
					$suffix = $config->sef_suffix;
					$npath = str_replace(JURI::root(true),'',$u->getPath());
					$dir = JURI::root(true);
					
					if (empty($dir))
					{
						if($u->getPath()=='/')
							$u->setPath('/');
						else
						{
							if ($u->getPath() == '')
							{
								if ($rewrite_mode)
								{									
									if ($suffix)
										$u->setPath("/".JRequest::getVar('lang','en','get').'.html');
									else 
										$u->setPath("/".JRequest::getVar('lang','en','get'));
								}
								else 
									$u->setPath("/".JRequest::getVar('lang','en','get'));
							}
							else
							{
								if ($rewrite_mode)
									$u->setPath("/".JRequest::getVar('lang','en','get')."/".$u->getPath());
								else
								{
									if ($u->getPath() == '/index.php')
									{
										if ($suffix)
											$newpath =  str_replace('index.php','index.php/'.JRequest::getVar('lang','en','get').'.html',$u->getPath());
										else $newpath =  str_replace('index.php','index.php/'.JRequest::getVar('lang','en','get').'/',$u->getPath());
									} else
										$newpath =  str_replace('index.php','index.php/'.JRequest::getVar('lang','en','get').'/',$u->getPath());
									$u->setPath($newpath);
								}
							}
						}
						
					} else 
					{
						if ($npath != '/')
						{
							$dir = substr($dir, 1);								
							if($u->getPath()=='/')
								$u->setPath('/');
							else
							{
								if ($rewrite_mode)
								{
									if ($suffix && $npath == '')
									{
										$u->setPath("/".$dir."/".JRequest::getVar('lang','en','get').".html");
									}
									else
										$u->setPath("/".$dir."/".JRequest::getVar('lang','en','get')."/".$npath);
								}
								else
								{
									if ($u->getPath() == '/'.$dir.'/index.php')
									{
										if ($suffix)
											$newpath =  str_replace('index.php','index.php/'.JRequest::getVar('lang','en','get').'.html',$u->getPath());
										else $newpath =  str_replace('index.php','index.php/'.JRequest::getVar('lang','en','get').'/',$u->getPath());
									} else 
										$newpath =  str_replace('index.php','index.php/'.JRequest::getVar('lang','en','get').'/',$u->getPath());
									$u->setPath($newpath);
								}
							}
						}
					}
					$u->delVar("lang"); 
				}
			}

			//delete the format
			$u->delVar("format");
			
			//get the current link of the page 
			if($sef == 1)
			{					
				$host = $u->getScheme().'://'.$u->getHost().JRequest::getURI();
				$host = str_replace($base,'',$host);
				
			} else 
			{
				$host = $u->getScheme().'://'.$u->getHost().JRequest::getURI();
				$host = str_replace($base,'',$host);
				
				$ampHost = str_replace('&','&amp;',$host);
				
				$db->setQuery("SELECT PageURL FROM #__rsseo_pages WHERE PageURL = '".$ampHost."' AND published = 1 LIMIT 1");
				$result = $db->loadResult();
				
				if(strpos($result,".php?") === FALSE) $sef_in_db = 1; else $sef_in_db = 0;
				if(strpos($host,".php?") === FALSE) $sef_url = 1; else $sef_url = 0;
				if($sef_url == 0 && $sef_in_db == 1)
				{
					$app	= &JFactory::getApplication();
					$router = &$app->getRouter();
					$router->setMode(JROUTER_MODE_SEF);
					$root = JURI::root(true);
					$host = JRoute::_($host);
					$host = str_replace($root,'',$host);
					$router->setMode(JROUTER_MODE_RAW);
				}
			}
			
			if (substr($host, 0, 1) == '/')
				$host = substr($host, 1, strlen($host));
			
			if (!$this->is16())
				$host = rtrim($host,'/');
			
			return $host;
		}
		
		function onAfterRender()
		{
			$db = JFactory::getDBO();
			$app =& JFactory::getApplication();
			if($app->getName() != 'site') return;
			
			$body = JResponse::getBody();
			$change = false;
			
			//is the keyword replacement on
			$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'enable.keyword.replace'");
			$enableKeywords = $db->loadResult();
			
			//replace the keywords
			if($enableKeywords == 1)
			{
				$change = true;
				//get all the keywords
				$db->setQuery("SELECT Keyword , KeywordBold ,KeywordUnderline,KeywordLimit ,KeywordAttributes ,KeywordLink FROM #__rsseo_keywords ORDER BY CHAR_LENGTH(Keyword) DESC");
				$keywords = $db->loadObjectList();
				
				if(!empty($keywords))
				{
					//get all the links in the body
					preg_match_all('#<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>#siU', $body, $links);
					foreach($links[0] as $i => $link)
						$body = str_replace($link,'{rsseo '.$i.'}', $body);
					
					//get current url
					$current = $this->getURL();
					
					//replacement of the keywords
					foreach($keywords as $keyword)
					{
						if ($keyword->KeywordLink == JURI::root().$current) continue;
						
						$keyword_lower = strtolower($keyword->Keyword);
						$body_lower = strtolower($body);
						if(strpos($body_lower, $keyword_lower) !== FALSE)
						{
							$body = $this->keywordReplace($body, $keyword->Keyword, $this->_setOptions($keyword->Keyword,$keyword->KeywordBold,$keyword->KeywordUnderline,$keyword->KeywordLink,$keyword->KeywordAttributes),$keyword->KeywordLimit);
							
							preg_match_all('#<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>#siU', $body, $links2[$keyword->Keyword]);
							if(!empty($links2))
							foreach($links2[$keyword->Keyword][0] as $i => $link)
								$body = str_replace($link,'{rsseo '.md5($keyword->Keyword).' '.$i.'}', $body);
						}
					}
					
					foreach ($links[0] as $i => $link)
						$body = str_replace('{rsseo '.$i.'}', $link, $body);
					
					foreach ($keywords as $keyword)
						if (!empty($links2[$keyword->Keyword][0]))
							foreach ($links2[$keyword->Keyword][0] as $i => $link)
								$body = str_replace('{rsseo '.md5($keyword->Keyword).' '.$i.'}', $link, $body);
					
				}
			}
			
			//is the tracking enabled
			$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'ga.tracking'");
			$enableTracking = $db->loadResult();
			
			if ($enableTracking)
			{
				//get the google code
				$change = true;
				$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'ga.code'");
				$gacode = $db->loadResult();
				
				//do we have the code?
				if (!empty($gacode))
				{					
					//check the code in the page
					if (strpos($body,$gacode) === FALSE)
					{
						$gatext = "<script type=\"text/javascript\">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '".$gacode."']);
_gaq.push(['_trackPageview']);

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
</body>";
					
						$body = str_replace('</body>',$gatext,$body);
					}
				}
			}
			
			if ($change)
				JResponse::setBody($body);
		}
		
		
		//function for Routing the url
		function _getRoute($url)
		{
			$config = new JConfig();
			$sef = $config->sef;
			$u =& JURI::getInstance('SERVER');
			$host = $u->getScheme().'://'.$u->getHost();
			if(strpos($url,".php?") === FALSE) $sef_url = 1; else $sef_url = 0;
			
			if($sef == 1 && $sef_url == 1)
				return JURI::root().$url;
			else if ($sef == 1 && $sef_url == 0)
				return $host.JRoute::_($url,false);
			else if ($sef == 0 && $sef_url == 0)
				return JURI::root().$url;
			else if ($sef == 0 && $sef_url == 1)
				return JURI::root().$url;
		}
		
		
		function _is_ignored($url, $pattern_array)
		{
			$return = false;
			if (is_array($pattern_array))
				foreach ($pattern_array as $pattern)
				{
					$pattern = str_replace('&', '&amp;', $pattern);
					$pattern = $this->_transform_string($pattern);	
					preg_match_all($pattern, $url, $matches);
			
					if (count($matches[0]) > 0)
						$return = true;
				}
			return $return;
		}

		function _transform_string($string)
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
		
		//add custom tags to the keyword
		function _setOptions($text,$bold = '0',$underline = '0',$link = '',$attributes)
		{
			$pattern = '/^(https?|ftp):\/\/(?#)(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+(?#)(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?(?#)@)?(?#)((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*(?#)[a-z][a-z0-9-]*[a-z0-9](?#)|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}(?#)(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])(?#))(:\d+)?(?#))(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*(?#)(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)(?#)?)?)?(?#)(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?(?#)$/i';
			
			
			if($bold == '1')
			{
				$startB = '<strong>';
				$endB = '</strong>';
			} elseif($bold == '2')
			{
				$startB = '<b>';
				$endB = '</b>';
			} elseif($bold == '0')
			{
				$startB = '';
				$endB = '';
			}
			
			if ($underline == '1')
			{
				$startU = '<u>';
				$endU = '</u>';
			} elseif ($underline)
			{
				$startU = '';
				$endU = '';
			} else {
				$startU = '';
				$endU = '';
			}
			
			$valid_url = preg_match($pattern,$link);
			
			if($valid_url)
				return $startB.$startU.'<a href="'.$link.'" '.trim($attributes).'>'.$text.'</a>'.$endU.$endB;
			else
				return $startB.$startU.$text.$endU.$endB;
		}
		
		//function to replace the keywords
		function keywordReplace($bodyText, $searchTerm, $replaceWith,$limit)
		{
			$original = $replaceWith;
			$db = JFactory::getDBO();
			$newText = '';
			$i = -1;
			$lcSearchTerm = mb_strtolower($searchTerm);
			$lcBodyText = mb_strtolower($bodyText);
			
			$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'approved.chars'");
			$chars = $db->loadResult();

			$counter = 0;
			while (strlen($bodyText) > 0) 
			{				
				//Get index of search term
				$i = $this->_indexOf($lcBodyText, $lcSearchTerm, $i+1);
				if ($i < 0) 
				{
					$newText .= $bodyText;
					$bodyText = '';
				} else 
				{
					// skip anything inside an HTML tag
					if (($this->_lastIndexOf($bodyText,">",$i) >= $this->_lastIndexOf($bodyText,"<",$i))) 
					{
						// skip anything inside a <script> or <style> block
						if (($this->_lastIndexOf($lcBodyText,"/script>",$i) >= $this->_lastIndexOf($lcBodyText,"<script",$i)) && ($this->_lastIndexOf($lcBodyText,"/style>",$i) >= $this->_lastIndexOf($lcBodyText,"<style",$i)) && ($this->_lastIndexOf($lcBodyText,"/button>",$i) >= $this->_lastIndexOf($lcBodyText,"<button",$i)) && ($this->_lastIndexOf($lcBodyText,"/textarea>",$i) >= $this->_lastIndexOf($lcBodyText,"<textarea",$i)) && ($this->_lastIndexOf($lcBodyText,"/select>",$i) >= $this->_lastIndexOf($lcBodyText,"<select",$i)) && ($this->_lastIndexOf($lcBodyText,"/a>",$i) >= $this->_lastIndexOf($lcBodyText,"<a ",$i)) && ($this->_lastIndexOf($lcBodyText,"/title>",$i) >= $this->_lastIndexOf($lcBodyText,"<title",$i)) && ($this->_lastIndexOf($lcBodyText,"/h1>",$i) >= $this->_lastIndexOf($lcBodyText,"<h1",$i)) && ($this->_lastIndexOf($lcBodyText,"/h2>",$i) >= $this->_lastIndexOf($lcBodyText,"<h2",$i)) && ($this->_lastIndexOf($lcBodyText,"/h3>",$i) >= $this->_lastIndexOf($lcBodyText,"<h3",$i)) && ($this->_lastIndexOf($lcBodyText,"/h4>",$i) >= $this->_lastIndexOf($lcBodyText,"<h4",$i)) && ($this->_lastIndexOf($lcBodyText,"/h5>",$i) >= $this->_lastIndexOf($lcBodyText,"<h5",$i)) )
						{
							
							$word = substr($bodyText, $i - 1, strlen($searchTerm) + 2);
							$firstChar = substr($word, 0, 1);
							$lastChar = substr($word, -1);							
							
							if((strpos($chars,$firstChar) !== FALSE) && (strpos($chars,$lastChar) !== FALSE))
							{
								$exact_word = ltrim($word,$firstChar);
								$exact_word = rtrim($exact_word,$lastChar);
								
								$pattern = '#href="(.*?)"#is';
								preg_match($pattern,$replaceWith,$matches);								
								if (!empty($matches) && !empty($matches[1]))
									$replaceWith = str_replace($matches[1], '{rsseo_rskeydel_link}', $replaceWith);
								
								$replaceWith = str_replace(mb_strtolower($exact_word),$exact_word,mb_strtolower($replaceWith));					
								
								if (!empty($matches) && !empty($matches[1]))
									$replaceWith = str_replace('{rsseo_rskeydel_link}', $matches[1], $replaceWith);
								
								if (empty($limit))
									$newText .= substr($bodyText, 0, $i) . $replaceWith;
								else 
								{
									if ($counter < $limit)
										$newText .= substr($bodyText, 0, $i) . $replaceWith;
									else
										$newText .= substr($bodyText, 0, $i) . $searchTerm;
								}
								$bodyText = substr($bodyText, $i+strlen($searchTerm));
								$lcBodyText = mb_strtolower($bodyText);
								$i = -1;
								$counter++;
								$replaceWith = $original;
							}
						}
					}
				}
			}
		return $newText;
		}

		function _indexOf($text, $search, $i)
		{
			$return = strpos($text, $search, $i);
			if ($return === false)
				$return = -1;
			
			return $return;
		}

		function _lastIndexOf($text, $search, $i)
		{
			$length = strlen($text);
			$i = ($i > 0)?($length - $i):abs($i);
			$pos = strpos(strrev($text), strrev($search), $i);
			return ($pos === false)? -1 : ( $length - $pos - strlen($search) );
		}
		
		function is16()
		{
			$jversion = new JVersion();
			$current_version =  $jversion->getShortVersion();
			return (version_compare('1.6.0', $current_version) <= 0);
		}
		
}
