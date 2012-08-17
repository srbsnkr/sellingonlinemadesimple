<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllerCompetitors extends rsseoController
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
		$this->registerTask( 'unpublish' , 'publish');
		$this->registerTask( 'orderup' , 'move');
		$this->registerTask( 'orderdown' , 'move');
	}

	
	function save()
	{
		$model	= $this->getModel('competitors');
		$post	= JRequest::get('post',JREQUEST_ALLOWRAW);
		$row	=& JTable::getInstance('rsseo_competitors','Table');
		$cid	= JRequest::getInt('cid',0,'request');
		
		
		if(!$row->bind($post))
			return JError::raiseWarning(500, $row->getError());
			
		if(!$cid)
		{
			$protocol_select = JRequest::getVar('protocol_select');
			$row->Competitor = str_replace(array('http://','https://'),'',$row->Competitor);
			$row->Competitor = $protocol_select . $row->Competitor;
		}
		
		$msg = $row->store() ? JText::_('RSSEO_COMPETITOR_SAVE' ) : JText::_('RSSEO_COMPETITOR_SAVE_ERROR' );			
		$row->reorder();
		switch(JRequest::getCmd('task'))
		{
			case 'apply' :
					$link = 'index.php?option=com_rsseo&task=editcompetitor&cid='.$row->IdCompetitor;
			break;
			
			case 'save' :
					$link = 'index.php?option=com_rsseo&task=listcompetitors';
			break;
		}

		if($cid == 0) rsseoControllerCompetitors::refreshCompetitor($row->IdCompetitor);
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$db		= & JFactory::getDBO();
		$model	= $this->getModel('competitors');
		$row	=& JTable::getInstance('rsseo_competitors','Table');
		$cids	= JRequest::getVar('cid',array(0),'post','array');

		$db->setQuery("SELECT COUNT(*) AS cnt FROM #__rsseo_competitors WHERE IdCompetitor IN ('".implode("','",$cids)."')");
		$competitorsFound = $db->loadResult();
		
		if(!empty($competitorsFound)) 
		{
			$db->setQuery("DELETE FROM #__rsseo_competitors WHERE IdCompetitor IN ('".implode("','",$cids)."')");
			$db->query();
			
			$db->setQuery("DELETE FROM #__rsseo_competitors_history WHERE IdCompetitor IN ('".implode("','",$cids)."')");
			$db->query();
			
			$msg= JText::sprintf('RSSEO_COMPETITOR_DELETE',count($cids));
			$this->setRedirect( 'index.php?option=com_rsseo&task=listcompetitors', $msg );
		}
		else 	
			$msg = JText::_('RSSEO_COMPETITOR_DELETE_ERROR' );
		
		
		$this->setRedirect( 'index.php?option=com_rsseo&task=listcompetitors', $msg , 'error'); 
	}
	/**
	 * remove record(s)
	 * @return void
	 */
	function removeHistory()
	{
		$db = & JFactory::getDBO();
		$cids=JRequest::getVar('cid',array(0),'post','array');
	
		//get competitor
		$db->setQuery("SELECT IdCompetitor FROM #__rsseo_competitors_history WHERE IdCompetitorHistory = '".$cids[0]."'");
		$IdCompetitor = $db->loadResult();

		$db->setQuery("DELETE FROM #__rsseo_competitors_history WHERE IdCompetitorHistory IN ('".implode("','",$cids)."')");
		$db->query();
			
		$msg= JText::sprintf('RSSEO_COMPETITOR_HISTORY_DELETE',count($cids));
		$this->setRedirect( 'index.php?option=com_rsseo&task=listcompetitorshistory&cid='.$IdCompetitor, $msg );
		
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_rsseo&task=listcompetitors');
	}
	
	function saveOrder()
	{
		$model = $this->getModel('competitors');

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize variables
		$db			= & JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$order		= JRequest::getVar( 'order', array (0), 'post', 'array' );
		$total		= count($cid);
		$conditions	= array ();

		JArrayHelper::toInteger($cid, array(0));
		JArrayHelper::toInteger($order, array(0));

		// Instantiate an article table object
		$row = & JTable::getInstance('rsseo_competitors','Table');

		
		for( $i=0; $i < count($cid); $i++ )
		{
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i])
			{	
				$row->ordering = $order[$i];
				if (!$row->store()) 
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
		$msg = JText::_('RSSEO_COMPETITORS_ORDER_SAVE');
		$this->setRedirect( 'index.php?option=com_rsseo&task=listcompetitors', $msg );

	}
	
	
	function move() 
	{
		$db	   =& JFactory::getDBO();
		$model = $this->getModel('competitors');
		$row = & JTable::getInstance('rsseo_competitors','Table');
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$task = JRequest::getVar('task');
		if($task=='orderup') $direction=-1;
		else $direction=1;
		if(is_array($cid)) $cid=$cid[0];
		
		if (!$row->load($cid)) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}	

		$row->move( $direction);
	
		$this->setRedirect( 'index.php?option=com_rsseo&task=listcompetitors');
		

	}
	
	function refreshCompetitor($id=null)
	{
		$db  =& JFactory::getDBO();
		$app =& JFactory::getApplication();
		
		$rsseoConfig = $app->getuserState('rsseoConfig');	
		$cid = is_null($id) ? JRequest::getInt('cid',0) : $id;
		
		$db->setQuery("SELECT * FROM #__rsseo_competitors WHERE IdCompetitor= '".$cid."'");
		$Competitor = $db->loadObject();
		
		if(isset($Competitor->Competitor) && !is_null($Competitor->Competitor))
		{		
			
			$array['page_rank'] = rsseoHelper::getPageRank($Competitor->Competitor);
			
			$array['alexa_rank'] = rsseoHelper::getAlexaRank($Competitor->Competitor);
			$array['tehnorati_rank'] = rsseoHelper::getTehnoratiRank($Competitor->Competitor);
			$array['google_pages'] = rsseoHelper::getGooglePages($Competitor->Competitor);
			$array['yahoo_pages'] = rsseoHelper::getYahooPages($Competitor->Competitor);
			$array['bing_pages'] = rsseoHelper::getBingPages($Competitor->Competitor);
			$array['google_backlinks'] = rsseoHelper::getGoogleBacklinks($Competitor->Competitor);
			$array['yahoo_backlinks'] = rsseoHelper::getYahooBacklinks($Competitor->Competitor);
			$array['bing_backlinks'] = rsseoHelper::getBingBacklinks($Competitor->Competitor);
			$dmoz = rsseoHelper::getDmoz($Competitor->Competitor);
			
			$array['date_refreshed'] = time();
			
		
			$query = "INSERT INTO #__rsseo_competitors_history SET "
					."IdCompetitor='".$cid."', "
					."PageRank='".$array['page_rank']."', "
					."AlexaRank='".$array['alexa_rank']."', "
					."TehnoratiRank='".$array['tehnorati_rank']."', "
					."GoogleBacklinks='".$array['google_backlinks']."', "
					."YahooBacklinks='".$array['yahoo_backlinks']."', "
					."BingBacklinks='".$array['bing_backlinks']."', "
					."GooglePages='".$array['google_pages']."', "
					."YahooPages='".$array['yahoo_pages']."', "
					."BingPages='".$array['bing_pages']."', "
					."DateRefreshed='".$array['date_refreshed']."'";
		
			//save to keyword details
			$db->setQuery($query);
			$db->query();
			
			$query = "UPDATE #__rsseo_competitors SET "
					."LastPageRank='".$array['page_rank']."', "
					."LastAlexaRank='".$array['alexa_rank']."', "
					."LastTehnoratiRank='".$array['tehnorati_rank']."', "
					."LastGoogleBacklinks='".$array['google_backlinks']."', "
					."LastYahooBacklinks='".$array['yahoo_backlinks']."', "
					."LastBingBacklinks='".$array['bing_backlinks']."', "
					."LastGooglePages='".$array['google_pages']."', "
					."LastYahooPages='".$array['yahoo_pages']."', "
					."LastBingPages='".$array['bing_pages']."', "
					."Dmoz='".$dmoz."', "
					."LastDateRefreshed='".$array['date_refreshed']."' "
					."WHERE IdCompetitor = '".$cid."'";
			
			//update last keyword
			$db->setQuery($query);
			$db->query();
			
			$array['date_refreshed'] = date($rsseoConfig['global.dateformat'],$array['date_refreshed']);
			
			$db->setQuery("SELECT * FROM #__rsseo_competitors_history WHERE IdCompetitor = ".$cid." ORDER BY DateRefreshed DESC LIMIT 2 ");
			$history = $db->loadObjectList();
			if(isset($history[1])) $compare = $history[0]; 
			
			//google page rank
			if($compare->PageRank > $Competitor->LastPageRank) $color1 = 'colorgreen';
			if($compare->PageRank < $Competitor->LastPageRank) $color1 = 'colorred';
			if($compare->PageRank == $Competitor->LastPageRank) $color1 = 'colornone';
			
			//alexa page rank
			if($compare->AlexaRank > $Competitor->LastAlexaRank) $color2 = 'colorred';
			if($compare->AlexaRank < $Competitor->LastAlexaRank) $color2 = 'colorgreen';
			if($compare->AlexaRank == $Competitor->LastAlexaRank) $color2 = 'colornone';
			
			//google pages
			if($compare->GooglePages > $Competitor->LastGooglePages) $color3 = 'colorgreen';
			if($compare->GooglePages < $Competitor->LastGooglePages) $color3 = 'colorred';
			if($compare->GooglePages == $Competitor->LastGooglePages) $color3 = 'colornone';
			
			//yahoo pages
			if($compare->YahooPages > $Competitor->LastYahooPages) $color4 = 'colorgreen';
			if($compare->YahooPages < $Competitor->LastYahooPages) $color4 = 'colorred';
			if($compare->YahooPages == $Competitor->LastYahooPages) $color4 = 'colornone';
			
			//bing pages
			if($compare->BingPages > $Competitor->LastBingPages) $color5 = 'colorgreen';
			if($compare->BingPages < $Competitor->LastBingPages) $color5 = 'colorred';
			if($compare->BingPages == $Competitor->LastBingPages) $color5 = 'colornone';
			
			//google backlinks
			if($compare->GoogleBacklinks > $Competitor->LastGoogleBacklinks) $color6 = 'colorgreen';
			if($compare->GoogleBacklinks < $Competitor->LastGoogleBacklinks) $color6 = 'colorred';
			if($compare->GoogleBacklinks == $Competitor->LastGoogleBacklinks) $color6 = 'colornone';
			
			//yahoo backlinks
			if($compare->YahooBacklinks > $Competitor->LastYahooBacklinks) $color7 = 'colorgreen';
			if($compare->YahooBacklinks < $Competitor->LastYahooBacklinks) $color7 = 'colorred';
			if($compare->YahooBacklinks == $Competitor->LastYahooBacklinks) $color7 = 'colornone';
			
			//bing backlinks
			if($compare->BingBacklinks > $Competitor->LastBingBacklinks) $color8 = 'colorgreen';
			if($compare->BingBacklinks < $Competitor->LastBingBacklinks) $color8 = 'colorred';
			if($compare->BingBacklinks == $Competitor->LastBingBacklinks) $color8 = 'colornone';
			
			//tehnorati rank
			if($compare->TehnoratiRank > $Competitor->LastTehnoratiRank) $color9 = 'colorgreen';
			if($compare->TehnoratiRank < $Competitor->LastTehnoratiRank) $color9 = 'colorred';
			if($compare->TehnoratiRank == $Competitor->LastTehnoratiRank) $color9 = 'colornone';
			
			
			$array['color1'] = $color1;
			$array['color2'] = $color2;
			$array['color3'] = $color3;
			$array['color4'] = $color4;
			$array['color5'] = $color5;
			$array['color6'] = $color6;
			$array['color7'] = $color7;
			$array['color8'] = $color8;
			$array['color9'] = $color9;
			$array['dmoz'] = ($dmoz == 0) ? JText::_('RSSEO_NO') : JText::_('RSSEO_YES');
		
			echo implode("\n",$array);
		}
		if($id == null) exit();
	}
	
	
	function competeRedirect()
	{
		$db   = JFactory::getDBO();
		$app  =& JFactory::getApplication();
		$cids = JRequest::getVar('cid',array(0),'post','array');
		
		$db->setQuery("SELECT Competitor FROM #__rsseo_competitors WHERE IdCompetitor IN ('".implode("','", $cids)."')");
		$Competitors = $db->loadObjectList();
		
		foreach($Competitors as $Competitor)
			$Competitors_array[] = str_replace(array('http://','https://','www.'),'',$Competitor->Competitor);
		
		$app->redirect('http://siteanalytics.compete.com/'.implode('+',$Competitors_array).'/');
	}
	
	function export()
	{
		$db =& JFactory::getDBO();
		$file = 'rsseo_'.date('d-m-Y-His').'.csv';
		$content = '';
		
		$db->setQuery("SELECT Competitor, LastPageRank, LastAlexaRank, LastTehnoratiRank, LastGooglePages, LastYahooPages, LastBingPages, LastGoogleBacklinks, LastYahooBacklinks, LastBingBacklinks, Dmoz FROM #__rsseo_competitors ORDER BY ordering ASC");
		$competitors = $db->loadObjectList();
		
		$content .= 'Competitor,Page Rank,Alexa Rank, Tehnorati Rank,Google Pages,Yahoo Pages,Bing Pages,Google Backlinks,Yahoo Backlinks,Bing Backlinks,Dmoz'."\n";
		
		foreach($competitors as $competitor)
			$content .= $competitor->Competitor.','.$competitor->LastPageRank.','.$competitor->LastAlexaRank.','.$competitor->LastTehnoratiRank.','.$competitor->LastGooglePages.','.$competitor->LastYahooPages.','.$competitor->LastBingPages.','.$competitor->LastGoogleBacklinks.','.$competitor->LastYahooBacklinks.','.$competitor->LastBingBacklinks.','.$competitor->Dmoz."\n";
		
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($content));
		ob_clean();
		flush();
		echo $content;
		exit;
	}
	
}