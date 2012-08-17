<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
 
jimport( 'joomla.application.component.model' );

class rsseoModelAnalytics extends JModel
{
	function __construct()
	{	
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'gapi.class.php');
		parent::__construct();
	}
	
	function convertseconds($sec) 
	{
		$text = '';

		$hours = intval(intval($sec) / 3600); 
		$text .= str_pad($hours, 2, "0", STR_PAD_LEFT). ":";

		$minutes = intval(($sec / 60) % 60); 
		$text .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

		$seconds = intval($sec % 60); 
		$text .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

		return $text;
	}
	
	//get accounts
	function getAccounts()
	{
		$db =& JFactory::getDBO();
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password']);
			$token = $ga->getAuthToken();
			$db->setQuery("UPDATE #__rsseo_config SET ConfigValue = '".$db->getEscaped($token)."' WHERE ConfigName = 'ga.token' ");
			$db->query();
			$ga->requestAccountData();
			return $ga->getResults();
		} catch(Exception $e)
		{
			$app->redirect('index.php?option=com_rsseo',$e->getMessage(),'error');
		}
	}
	
	function getGAgeneral()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],'',array('visits','pageviews','pageviewsPerVisit','avgTimeOnSite','visitBounceRate','percentNewVisits','visitors'),null,null,$config['ga.start'],$config['ga.end']);
			
			$return = array();
			
			$totalvisits = $ga->getVisits();
			$totalvisits = ($totalvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $totalvisits;
			$uniquevisits = $ga->getVisitors();
			$uniquevisits = ($uniquevisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $uniquevisits;
			$totalpageviews = $ga->getPageviews();
			$totalpageviews = ($totalpageviews === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $totalpageviews;
			$avgpageviews = $ga->getPageviewsPerVisit();
			$avgpageviews = ($avgpageviews === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($avgpageviews,2);
			$timeonsite = $ga->getAvgTimeOnSite();
			$timeonsite = ($timeonsite === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $this->convertseconds(number_format($timeonsite,0));
			$bouncerate = $ga->getVisitBounceRate();
			$bouncerate = ($bouncerate === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($bouncerate,2).' %';
			$newvisits = $ga->getPercentNewVisits();
			$newvisits = ($newvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($newvisits,2).' %';
			
			$obj1 = new stdClass();		$obj2 = new stdClass();		$obj3 = new stdClass();		$obj4 = new stdClass();
			$obj5 = new stdClass();		$obj6 = new stdClass();		$obj7 = new stdClass();
			
			$obj1->title = JText::_('RSSEO_GA_GENERAL_TOTALVISIORS');					$obj4->title = JText::_('RSSEO_GA_GENERAL_AVGPAGEVIEWS');
			$obj1->value = $totalvisits;												$obj4->value = $avgpageviews;
			$obj1->descr = JText::_('RSSEO_GA_GENERAL_TOTALVISIORS_DESC');				$obj4->descr = JText::_('RSSEO_GA_GENERAL_AVGPAGEVIEWS_DESC');
			
			$obj2->title = JText::_('RSSEO_GA_GENERAL_UNIQUEVISITS');					$obj5->title = JText::_('RSSEO_GA_GENERAL_TIMEONSITE');
			$obj2->value = $uniquevisits;												$obj5->value = $timeonsite;
			$obj2->descr = JText::_('RSSEO_GA_GENERAL_UNIQUEVISITS_DESC');				$obj5->descr = JText::_('RSSEO_GA_GENERAL_TIMEONSITE_DESC');
			
			$obj3->title = JText::_('RSSEO_GA_GENERAL_TOTALPAGEVIEWS');					$obj6->title = JText::_('RSSEO_GA_GENERAL_BOUNCERATE');
			$obj3->value = $totalpageviews;												$obj6->value = $bouncerate;
			$obj3->descr = JText::_('RSSEO_GA_GENERAL_TOTALPAGEVIEWS_DESC');			$obj6->descr = JText::_('RSSEO_GA_GENERAL_BOUNCERATE_DESC');
			
			$obj7->title = JText::_('RSSEO_GA_GENERAL_NEWVISITS');
			$obj7->value = $newvisits;
			$obj7->descr = JText::_('RSSEO_GA_GENERAL_NEWVISITS_DESC');
			
			$return[] = $obj1;			$return[] = $obj2;			$return[] = $obj3;
			$return[] = $obj4;			$return[] = $obj5;			$return[] = $obj6;
			$return[] = $obj7;
			
			return $return;
		} catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	
	function getGANewReturning()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],array('visitorType'),array('visits','pageviewsPerVisit','avgTimeOnSite','visitBounceRate'),null,null,$config['ga.start'],$config['ga.end']);
			
			$return = array();
			
			$results = $ga->getResults();
			
			if (!empty($results))
			foreach ($results as $result)
			{
				$object = new stdClass();
				
				$visits = $result->getVisits();
				$visits = ($visits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $visits;
				$bouncerate = $result->getVisitBounceRate();
				$bouncerate = ($bouncerate === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($bouncerate,2).' %';
				$avgtimesite = $result->getAvgTimeOnSite();
				$avgtimesite = ($avgtimesite === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $this->convertseconds(number_format($avgtimesite,0));
				$pagesvisits = $result->getpageviewsPerVisit();
				$pagesvisits = ($pagesvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($pagesvisits,2);
				
				$object->visits = $visits;
				$object->bouncerate = $bouncerate;
				$object->avgtimesite = $avgtimesite;
				$object->pagesvisits = $pagesvisits;
				
				$key = $result == 'Returning Visitor' ? JText::_('RSSEO_RETURNINGVISITOR') : JText::_('RSSEO_NEWVISITOR');
				$return[$key] = $object;
			}
			
			return $return;
		} catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	function getGAVisits()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		$filter = null;
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],array('year','month','day'),array('visits'),null,$filter,$config['ga.start'],$config['ga.end']);			
			
			$return = array();
			
			$totalvisits = $ga->getVisits();
			$totalvisits = empty($totalvisits) ? 1 : $totalvisits;
			$results = $ga->getResults();
			
			if (!empty($results))
			foreach ($results as $result)
			{
				$object = new stdClass();
				$year = $result->getYear();
				$month = $result->getMonth();
				$day = $result->getDay();
				$date = mktime(0,0,0,$month,$day,$year);
				
				$visits = $result->getVisits();
				$object->visits = $visits;
				$visitspercent = ($visits * 100) / $totalvisits;
				$visitspercent = number_format($visitspercent,2);
				$visitspercent = $visitspercent. ' %';
				$object->visitspercent = $visitspercent;
				
				$return[$date] = $object;
			}
			
			ksort($return);
			
			return $return;
		} catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	function getGABrowsers()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],array('browser'),array('visits','pageviewsPerVisit','avgTimeOnSite','visitBounceRate'),'-visits',null,$config['ga.start'],$config['ga.end']);			
			
			$return = array();
			
			$results = $ga->getResults();
			
			if (!empty($results))
			foreach ($results as $result)
			{
				$browser = $result->getBrowser();
				$object = new stdClass();
				
				$visits = $result->getVisits();
				$visits = ($visits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $visits;
				$bouncerate = $result->getVisitBounceRate();
				$bouncerate = ($bouncerate === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($bouncerate,2).' %';
				$avgtimesite = $result->getAvgTimeOnSite();
				$avgtimesite = ($avgtimesite === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $this->convertseconds(number_format($avgtimesite,0));
				$pagesvisits = $result->getpageviewsPerVisit();
				$pagesvisits = ($pagesvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($pagesvisits,2);
				
				$object->browser = $browser;
				$object->visits = $visits;
				$object->bouncerate = $bouncerate;
				$object->avgtimesite = $avgtimesite;
				$object->pagesvisits = $pagesvisits;
				
				$return[] = $object;
			}
			
			return $return;
		} catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	function getGAMobiles()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		$filter = "isMobile == Yes";
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],array('operatingSystem','isMobile'),array('visits','pageviewsPerVisit','avgTimeOnSite','visitBounceRate'),'-visits',$filter,$config['ga.start'],$config['ga.end']);			
			
			$return = array();
			
			$results = $ga->getResults();
			
			if (!empty($results))
			foreach ($results as $result)
			{
				$browser = $result->getOperatingSystem();
				$object = new stdClass();
				
				$visits = $result->getVisits();
				$visits = ($visits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $visits;
				$bouncerate = $result->getVisitBounceRate();
				$bouncerate = ($bouncerate === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($bouncerate,2).' %';
				$avgtimesite = $result->getAvgTimeOnSite();
				$avgtimesite = ($avgtimesite === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $this->convertseconds(number_format($avgtimesite,0));
				$pagesvisits = $result->getpageviewsPerVisit();
				$pagesvisits = ($pagesvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($pagesvisits,2);
				
				$object->browser = $browser;
				$object->visits = $visits;
				$object->bouncerate = $bouncerate;
				$object->avgtimesite = $avgtimesite;
				$object->pagesvisits = $pagesvisits;
				
				$return[] = $object;
			}
			
			return $return;
		} catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	function getGASources()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		$filter1 = 'medium == (none)';
		$filter2 = 'medium == organic';
		$filter3 = 'medium == referral';
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],array('source','medium'),array('visits','pageviewsPerVisit','avgTimeOnSite','visitBounceRate','percentNewVisits'),'-visits',null,$config['ga.start'],$config['ga.end'],1,20);			
			
			$total = $ga->getVisits();
			
			$return = array();
			$results = $ga->getResults();
			
			if (!empty($results))
			foreach ($results as $result)
			{
				$object = new stdClass();
				
				$object->source = $result->getSource() . ' / '.$result->getMedium();
				
				$visits = $result->getVisits();
				$visits = ($visits == '') ? JText::_('RSSEO_NOT_AVAILABLE') : $visits;
				$bouncerate = $result->getVisitBounceRate();
				$bouncerate = ($bouncerate === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($bouncerate,2).' %';
				$avgtimesite = $result->getAvgTimeOnSite();
				$avgtimesite = ($avgtimesite === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $this->convertseconds(number_format($avgtimesite,0));
				$pagesvisits = $result->getpageviewsPerVisit();
				$pagesvisits = ($pagesvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($pagesvisits,2);
				$newvisits = $result->getpercentNewVisits();
				$newvisits = ($newvisits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($newvisits,2).' %';
				
				$object->visits = $visits;
				$object->bouncerate = $bouncerate;
				$object->avgtimesite = $avgtimesite;
				$object->pagesvisits = $pagesvisits;
				$object->newvisits = $newvisits;
				
				$return[] = $object;
			}
			
			$ga->requestReportData($config['ga.account'],array('medium'),array('visits'),'-visits',$filter1,$config['ga.start'],$config['ga.end'],1,20);
			$directvisits = $ga->getVisits();
			$directvisits = !empty($directvisits) ? $directvisits : '0';
			
			$ga->requestReportData($config['ga.account'],array('medium'),array('visits'),'-visits',$filter2,$config['ga.start'],$config['ga.end'],1,20);
			$searchvisits = $ga->getVisits();
			$searchvisits = !empty($searchvisits) ? $searchvisits : '0';
			
			$ga->requestReportData($config['ga.account'],array('medium'),array('visits'),'-visits',$filter3,$config['ga.start'],$config['ga.end'],1,20);
			$refferingvisits = $ga->getVisits();
			$refferingvisits = !empty($refferingvisits) ? $refferingvisits : '0';
			
			$data['data'] = $return;
			$data['details'] = array($directvisits,$searchvisits,$refferingvisits);
			
			return $data;
		} catch(Exception $e)
		{
			return array('data' => $e->getMessage());
		}
	}
	
	function getGAContent()
	{
		$app =& JFactory::getApplication();
		$config = $app->getuserState('rsseoConfig');
		
		try {
			$ga = new gapi($config['analytics.username'],$config['analytics.password'],$config['ga.token']);
			$ga->requestReportData($config['ga.account'],array('pagePath'),array('pageviews','uniquePageviews','exitRate','avgTimeOnPage','bounces','entrances','entranceBounceRate'),'-pageviews',null,$config['ga.start'],$config['ga.end'],1,20);			
			
			$return = array();
			
			$results = $ga->getResults();
			
			if (!empty($results))
			foreach ($results as $result)
			{
				$object = new stdClass();
				
				$object->page = $result->getpagePath();
				
				$pageviews = $result->getPageviews();
				$pageviews = ($pageviews == '') ? JText::_('RSSEO_NOT_AVAILABLE') : $pageviews;
				$upageviews = $result->getUniquePageviews();
				$upageviews = ($upageviews == '') ? JText::_('RSSEO_NOT_AVAILABLE') : $upageviews;
				$avgtimesite = $result->getavgTimeOnPage();
				$avgtimesite = ($avgtimesite === '') ? JText::_('RSSEO_NOT_AVAILABLE') : $this->convertseconds(number_format($avgtimesite,0));
				$bouncerate = $result->getentranceBounceRate();
				$bouncerate = ($bouncerate === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($bouncerate,2).' %';
				$exits = $result->getexitRate();
				$exits = ($exits === '') ? JText::_('RSSEO_NOT_AVAILABLE') : number_format($exits,2).' %';
				
				$object->pageviews = $pageviews;
				$object->upageviews = $upageviews;
				$object->avgtimesite = $avgtimesite;
				$object->bouncerate = $bouncerate;
				$object->exits = $exits;
				
				$return[] = $object;
			}
			
			return $return;
		} catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
}