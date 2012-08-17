<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllerkeywords extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('apply' ,  'save');
	}


	function refreshkeyword($IdKeyword = null)
	{
		$app =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$rsseoConfig = $app->getuserState('rsseoConfig');
	
		if(!$IdKeyword) $cid = intval(JRequest::getVar('cid'));
		else $cid = $IdKeyword;
		
		$db->setQuery("SELECT ActualKeywordPosition FROM #__rsseo_keywords WHERE IdKeyword =".$cid);
		$currentPosition = $db->loadResult();
		
		$db->setQuery("SELECT Keyword FROM #__rsseo_keywords WHERE IdKeyword='".$cid."'");
		$keyword = $db->loadResult();
		
		$rsseoConfig['subdomains'] = str_replace("\r",'',$rsseoConfig['subdomains']);
		
		$domains = array();
		$domains = explode("\n",$rsseoConfig['subdomains']);
		$domains[] = JURI::root();
		
		$q = str_replace(" ","+",$keyword);    
		$q = str_replace("%26","&",$q);    
		$valid=0;
		$i=1;
		for($google_page=0;$google_page<5;$google_page++)
		{
			$parser = rsseoHelper::file_get_html('http://www.'.$rsseoConfig['google.domain'].'/search?q='.$q.'&pws=0&start='.(10*$google_page));
			while($parser->parse())
			{
				if(strtolower($parser->iNodeName) == 'a' && @$parser->iNodeAttributes['class'] == 'l' && empty($parser->iNodeAttributes['title']) && empty($parser->iNodeAttributes['style']))
				{
					$href = @$parser->iNodeAttributes['href'];
					foreach($domains as $domain)
					{
						if(empty($domain)) continue;
						if(strpos($href,$domain) !== false)
						{
							$valid=1;
							continue;
						}
					}
					if($valid) continue;
					$i++;
				}
			}
			if($valid) break;
		}
		
		$array['position'] = $valid ? $i : 0;
		$array['date_refreshed'] = time();
		
		if($array['position']  > $currentPosition) $array['color'] = "colorred";
		if($array['position']  < $currentPosition) $array['color'] = "colorgreen";
		if($array['position']  == $currentPosition) $array['color'] = "colornone";
		
		//update last keyword
		$db->setQuery("UPDATE #__rsseo_keywords SET ActualKeywordPosition ='".$array['position']."' , LastKeywordPosition = ".$currentPosition." , DateRefreshed='".$array['date_refreshed']."' WHERE IdKeyword = '".$cid."'");
		$db->query();
		
		$array['date_refreshed'] = date($rsseoConfig['global.dateformat'],$array['date_refreshed']);
		
		if($IdKeyword == null)
		{
			echo serialize($array);
			exit();
		}
	}


	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('keywords');
		$post=JRequest::get('post');
		
		$row= & JTable::getInstance('rsseo_keywords','Table');
		
		if(isset($post['Keyword']) && stristr($post['Keyword'],"\n"))
		{
			$keywords = explode("\n",$post['Keyword']);
			if(!empty($keywords))
			{
				foreach($keywords as $keyword)
				{
					if(trim($keyword)!='')
					{
						$post['Keyword'] = trim($keyword);
						$row->bind($post);
						$row->IdKeyword = null;
						
						if (!$row->store()) 
							$msg = JText::_('RSSEO_KEYWORD_MULTI_ERROR' );
						else 
							rsseoControllerkeywords::refreshkeyword($row->IdKeyword);
					}
				}
			}
		}
		else
		{
		
			if(!$row->bind($post)) {
				return JError::raiseWarning(500, $row->getError());
			}
			
			if ($row->store()) 
			{
				rsseoControllerkeywords::refreshkeyword($row->IdKeyword);
				$msg = JText::_('RSSEO_KEYWORD_SAVE' );
			}
			else $msg = JText::_('RSSEO_KEYWORD_SAVE_ERROR' );
		}
		
		switch(JRequest::getCmd('task'))
		{
			case 'apply' :
					$link = 'index.php?option=com_rsseo&task=editkeyword&cid='.$row->IdKeyword;

			break;
			
			case 'save' :
					$link = 'index.php?option=com_rsseo&task=listkeywords';

			break;
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$db = & JFactory::getDBO();
		$model = $this->getModel('keywords');
		$row =& JTable::getInstance('rsseo_keywords','Table');
		$cids=JRequest::getVar('cid',array(0),'post','array');
		
		$db->setQuery("SELECT COUNT(*) AS cnt FROM #__rsseo_keywords WHERE IdKeyword IN ('".implode("','",$cids)."')");
		$keywordsFound = $db->loadResult();
		
		if(!empty($keywordsFound)) 
		{
			$db->setQuery("DELETE FROM #__rsseo_keywords WHERE IdKeyword IN ('".implode("','",$cids)."')");
			$db->query();
			
			$db->setQuery("DELETE FROM #__rsseo_keyword_details WHERE IdKeyword IN ('".implode("','",$cids)."')");
			$db->query();
			$msg= JText::sprintf('RSSEO_KEYWORD_DELETE',count($cids));
			$this->setRedirect( 'index.php?option=com_rsseo&task=listkeywords', $msg );
		}
		else 	
			$msg = JText::_('RSSEO_KEYWORD_DELETE_ERROR' );
		
		
		$this->setRedirect( 'index.php?option=com_rsseo&task=listkeywords', $msg , 'error'); 
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_rsseo&task=listkeywords');
	}
	
}