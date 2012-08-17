<?php
## v0.1	Beta ## 02 April 2005
## v0.2	Beta ## 05 April 2005
## v1.0	## 06 April 2005
##      { using curl to get filesize }
## v1.1 ## 07 April 2005
##      { supporting 'base url' }
##      ## 09 April 2005
##      { fixing bugs on parsing CSS }
##
######
## Webpage Size Calculator
##
## Is your website page too fat?
## If your website pages are too fat, there are many problems you face.
## Such as: (1) slow motion on displaying your webpage,
##          (2) bandwidth usage that is over-dose.
## But, how dou know the size of your webpage?
## It is not easy to measure your webpage weight, in particular a dynamic page.
##
## This class calculates size of webpage and all elements, such as image, js, swf, frame, etc.
## By knowing your page size, you can take an action,
## wether reducing the size or removing unimportant parts.
##
## Limitation
## Can not measure javascript`s pre-loading images
#######
## Author: Huda M Elmatsani
## Email: 	justhuda ## gmail ## com
##
## 04/02/2005
#######
## Copyright (c) 2005 Huda M Elmatsani All rights reserved.
## This program is freeware
## Please, tell me if you made improvements or just a little modification
## Please, tell me if you made online tool with this class
########
## USAGE
##
## $size = new WebpageSize;
## $size->setURL("http://www.php.net/");
## $size->printResult();
##
## see sample.webpagesize.php
##
## credits:
## Fauzan Aminuddin, Satya Agustan Dinata, Ciko Parera @phpug-at-yahoogroups
####

class WebpageSize {


	var $url 		= '';
    var $baseurl    = '';
    var $tailfile   = '';
    var $totalsize   = '';
    var $pages      = array();
    var $freqpages  = array(); //frequency of page element to be loaded

    function setURL($url) {
         $this->url = $this->parseURL($url);
	}

    function parseURL($url) {

        $this->tailfile = substr($url, strrpos($url, '/')+1);
        $parsed = parse_url($url);
        if($this->tailfile == $parsed['host'])  $this->tailfile = '';
		if(substr($url, -1)=='/' or $this->tailfile)
            return $url;
  		else
            $url =  $url.'/';
        return $url;
		
    }

    /*
    * searching base href
    */
	function setBaseURL($str) {
		preg_match("/base.*[\s]*href[\040]*=[\040]*\"?([^\"' >]+)/ie", $str, $match);

         if(isset($match[1])) {
            $url = $this->parseURL($match[1]);
            if(substr($url, -1)!='/') $url .= '/';
            $this->baseurl = $url;
        } else {
            $this->baseurl = $this->url;
        }
		
    }

    /*
    *  core function!
    *  page elements and the size
    */
	function getResult() {

		$paths      = $this->grabPageSources();
        array_unshift($paths, $this->url);
        $pages    = array();
		
		for($i=0; $i<count($paths); $i++)
		{
			if(!array_key_exists($paths[$i],$pages) )
			{
				$filesize = strlen($this->getContent($paths[$i]));	
				$this->freqpages[$paths[$i]] = 1;
				$pages[$paths[$i]] = $filesize;
				$this->totalsize  += $filesize;
			} else
				$this->freqpages[$paths[$i]] += 1;
		}

        natsort($pages);
        return $pages;
	}

	function totalPageSize() {
		return $this->totalsize;
	}

	/*
	* this one is usefull
	*/
	function readableSize($size) {
		return number_format($size/1024,2)." KB";
	}

    /*
    *  pre-formated output
    */
	function printResult() {
		$pages = $this->getResult();
		$strtable = '<table width="100%" class="adminlist">' .
                    '<tr bgcolor=#F3F3F3><td width="360" colspan="2">' .
                    '<div align="left"><strong>'.JText::_('RSSEO_PAGE_URL').' : <a href="'.$this->url.'" target="_blank">'.$this->url.'</a></strong></div></td>' .
                    '<td width="140" colspan="2"><strong>'.JText::_('RSSEO_PAGE_SIZE').' : ' . $this->readableSize($this->totalPageSize()) . '</strong></td></tr>' .
                    '<tr bgcolor="#F3F3F3"><td width="1%"><div align="center">#</div></td>' .
					'<td width="210">'.JText::_('RSSEO_PAGE_ELEMENTS').'</td>' .
					'<td width="86">'.JText::_('RSSEO_PAGE_FILESIZE').'</td>' .
					'<td width="32">'.JText::_('RSSEO_PAGE_FREQUENCY').'</td>' .
				  	'</tr>';
		$n=0;
		while(list($url,$size) = each($pages)){
			$strtable .= '<tr><td width=20>'.++$n.'</td><td width=440>'. $url. '</td>' .
                         '<td width=100>'. $this->readableSize($size) . '</td>' .
                         '<td width=40>' . $this->freqpages[$url] .     '</td></tr>';
		}
		$strtable .= '<tr bgcolor=#F3F3F3><td>&nbsp;</td><td><strong>'.JText::_('RSSEO_PAGE_TOTAL_SIZE').'</strong></td><td colspan=2><strong>'.
					 $this->readableSize($this->totalPageSize()) . '</strong></td></tr>';
		$strtable .='</table>';
		return $strtable;
	}
	
	function sizeofpage()
	{
		$pages = $this->getResult();
		return $this->readableSize($this->totalPageSize());
	}
	
	function getTime()
	{
		$pages = $this->getResult();
		while(list($url,$size) = each($pages))
		{
			$time = microtime();
			$time = explode(" ", $time);
			$time = $time[1] + $time[0];
			$start = $time;
			$this->getContent($url);
			$time = microtime();
			$time = explode(" ", $time);
			$time = $time[1] + $time[0];
			$finish = $time;
			$totaltime = ($finish - $start);
			$this->totaltime += $totaltime;
		}
		
		return $this->totaltime;
	}
	
    /*
    *  from "../../images/some.jpg" for example to "http://www.domain.com/images/some.jpg"
    */
	function resolvePathSources($paths) {

		$arr_path = array();
		
		if(!empty($paths))
		while(list(,$src) = each($paths))
			$arr_path[] = $this->makeAbsolutePath($src,$this->baseurl);
	   return $arr_path;
	}

    /*
    *  taking webpage content
    *  fopen is lighter than cURL
    */
	function getContent($url){
		
		return rsseoHelper::fopen($url);
    }

    /*
    * searching webpage elements
    */
	function grabPageSources() {

		$content  = $this->getContent($this->url);
        $this->setBaseURL($content);

		$arr_src1 = array();
		$arr_src2 = array(); 
		$arr_src3 = array();
        $arr_src4 = array();
		$arr_src5 = array();
		$arr_src6 = array();	
	
		$arr_src1 = $this->searchSources($content);
        //search CSS classes that applied on page
        $this->CSSclasses = $this->searchCSSClasses($content);

		
		$arr_src2 = $this->searchSourcesOnCSS($content);
		if(empty($arr_src2)) $arr_src2 = array();
		
		$arr_src3 = $this->searchCSSLinks($content);
        

        if(!empty($arr_src3))
		$arr_src4 = $this->searchSourcesOnCSSFiles($arr_src3);
        //search on frames if exists
        $arr_src5 = $this->searchFrames($content);
        if(!empty($arr_src5))
        $arr_src6 = $this->searchSourcesOnFrames($arr_src5);

		$arr_sources  = array_merge ($arr_src1, $arr_src2, $arr_src3,
                                 $arr_src4, $arr_src5, $arr_src6);

        return $this->resolvePathSources($arr_sources);

	}

    /*
    * searching image/js elements
    */
	function searchSources($str) {
		preg_match_all("/[img|input|embed|script]+.*[\s]*(src|background)[\040]*=[\040]*\"?([^\"' >]+)/ie", $str, $arr_source);
		return $arr_source[2];
	}

    /*
    * searching class elements
    */
	function searchCSSClasses($str) {
		preg_match_all("/class[\040]*=[\040]*\"?([^\"' >]+)/ie", $str, $arr_source);
		return $arr_source[1];
	}
    /*
    * searching frame elements
    */
	function searchFrames($str) {
		preg_match_all("/frame.*[\s]*src[\040]*=[\040]*\"?([^\"' >]+)/ie", $str, $arr_source);
		return $arr_source[1];
	}

    /*
    * searching css elements
    */
	function xsearchSourcesOnCSS($str) {
		preg_match_all("/(url\(\"?([^\")]+))/ie", $str, $arr_source);
		return $arr_source[2];
	}

     /*
    * searching css elements
    */
	function searchSourcesOnCSS($str) {
		$arr_sources = array();
		preg_match_all("/(\.(.*)\s+\{[\s]+)*.*url\(\"?([^\")]+)/ie", $str, $arr_source);
        
		//print_r($arr_source);
		if(!empty($arr_source[2]))
		for($i=0; $i<count($arr_source);$i++) 
		{
			if(isset($arr_source[2][$i]))
			if( in_array( $arr_source[2][$i], $this->CSSclasses )) 
                $arr_sources[] = $arr_source[3][$i];
        }
		return $arr_sources;
	}

    /*
    * searching webpage elements on frames
    */
    function searchSourcesOnFrames($framefiles) {
        $arr_source  = array();
        $arr_sources = array();
        while(list(,$src)   = each($framefiles)) {

            $framepage        = $this->makeAbsolutePath($src,$this->baseurl);

            $page = new WebpageSize;
            $page->setURL($framepage);


            $arr_source  = $page->grabPageSources();
            $arr_sources = array_merge($arr_sources, $arr_source);
        }
        return $arr_sources;
    }

    /*
    * searching webpage elements on CSS files
    */
    function searchSourcesOnCSSFiles($cssfiles) {

        //search sources on CSS file
        $arr_CSSlinks = array();
        while(list(,$src)   = each($cssfiles)) {
            $numstepback    = substr_count($src, "../");
            $CSSpage        = $this->makeAbsolutePath($src,$this->baseurl);

            $CSScontent     = $this->getContent($CSSpage);
            $arr_sourcelink    = $this->searchSourcesOnCSS($CSScontent);
            if(empty( $arr_sourcelink )) continue;

            while(list(,$srclink)   = each($arr_sourcelink)) {
                $arr_CSSlink[]  =   str_repeat("../",$numstepback) . $srclink;
            }
            $arr_CSSlinks   = array_merge($arr_CSSlinks, $arr_CSSlink);
        }
        return $arr_CSSlinks;

    }

    /*
    * searching webpage elements on CSS
    */
	function searchCSSLinks($str) {
         preg_match_all("/<link[^>]+href[\040]*=[\040]*[\"|\'|\\\\]*([^\'|\"|>|\040]*(.*)\.css)[\"|\'|>|\040|\\\\]*/ie",$str, $arr_CSSlink);
        return $arr_CSSlink[1];
	}


	function makeAbsolutePath ($src,$url) {
		
		$addone = 1;
		$config = new JConfig();
		$sef = $config->sef;
		
        if ($this->tailfile) {
            $url = substr($url, 0, -(strlen($this->tailfile)+1));
            $addone = 0;
        }
		
        if (strtolower(substr($src,0,4)) != 'http') 
		{
            $numrel  = substr_count($src, "../");
            $src     = str_replace("../","",$src);

            for($i=0; $i < $numrel+$addone; $i++) 
			{
                $lastslash  = strrpos($url,"/");
                $url       = substr($url, 0, $lastslash);
            }
		if($sef == 1)
			$src = str_replace(JURI::root(true).'/','',$src);
		else
		{	
			$src = str_replace(JURI::root(true),'',$src);
			if(substr($src,0,1) != '/') $src = '/'.$src;
		}
		
        $return =  $url.$src;
        }
        else
			$return = $src;
		
		return $return;
		
	}

}
?>