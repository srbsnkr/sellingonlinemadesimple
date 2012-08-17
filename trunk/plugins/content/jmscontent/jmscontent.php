<?php
/**
 * @version     2.0.2
 * @package		Joomla
 * @subpackage	Joomla Membership Sites
 * @author		Infoweblink
 * @authorEmail	support@infoweblink.com 
 * @home page	http://joomlasubscriptionsites.com/ 
 * @copyright	Copyright (C) 2011. Infoweblink. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This plugin manages Subscriptions for members to access to Joomla Resource
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.event.plugin');
jimport('joomla.plugin.plugin');
//jimport( 'joomla.html.parameter' );

class plgContentJmscontent extends JPlugin
{
	function plgContentJmscontent(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	/**
	 * Method to check retriction before displaying articles
	 *
	 * @param object $article
	 * @param array $params
	 * @param int $limitstart
	 * @return boolean
	 */
	//function onContentBeforeDisplay(&$article, &$params, $limitstart)
	function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{		
		$mainframe = JFactory::getApplication();
		
		if ($mainframe->isAdmin())
		return;
		
		if (!isset($row->text))
		return;

		$Itemid = JRequest::getInt('Itemid');
		$db = JFactory::getDbo();
		$my = JFactory::getUser();
		$userid = $my->get('id');
		$view = JRequest::getWord('view');
		
		// Get the component config/params object.
		$cnf = JComponentHelper::getParams('com_jms');
				
		$session = & JFactory::getSession();
		$url = $session->get('jms_access_url');
		$current = JURI::base().ereg_replace("^/","",$_SERVER['REQUEST_URI']);

		if($url == $current)
		{
			$session->clear('jms_access_url');
		}
		
		if ($cnf->get('se', 1)) {
			
			$bots = explode("\n", $cnf->def('bots'));
			foreach($bots as $bot)
				if (strpos($_SERVER['HTTP_USER_AGENT'], $bot))
			return;
			
		}

		if ($view == 'article' && JRequest::getInt('id')) {
			
			$return = true;
			
		} else {
			
			$row->text = preg_replace("/{JMSBOT USER=[^}]+}/iU", "", $row->text);
			$row->text = preg_replace("/{JMSBOT SUBSCRIPTION=[^}]+}/iU", "", $row->text);
			
			$return = false;
			
		}
		
		if (preg_match("/{JMSBOT SKIP}/", $row->text)) {
			$row->text = preg_replace("/{JMSBOT SKIP}/", "", $row->text);
			return false;
		}
		
		// Check to add/remove from jvm to subscription
		if ($userid) {
			$sql = 'SELECT ju.pid, jp.id AS plan_id, jp.price' .
				' FROM #__jvmauth AS ju, #__jms_plans AS jp' .
				' WHERE ju.email = "' . $my->get('username') . '"' .
				' AND jp.jvm_product_ids = ju.pid'
				;
			$db->setQuery( $sql );
			$prows = $db->loadObjectList();
			$plans = array();
			for ( $i = 0; $n = count($prows), $i < $n; $i++ ) {
				$prow = $prows[$i];
				$sql = 'SELECT id' .
					' FROM #__jms_plan_subscrs' .
					' WHERE user_id = ' . (int) $userid .
					' AND plan_id = ' . (int) $prow->plan_id
					;
				$db->setQuery( $sql );
				$isSubscriber = $db->loadResult();
				// Check if the subcription is present
				if(!$isSubscriber){				
					$created = date('Y-m-d H:i:s');
					$expired = '3009:07:15 15:00:00';
					$gateway = 'jvm' ;
					$gateway_id = time();
					
					$sql = 'INSERT INTO #__jms_plan_subscrs' .
						' (id, user_id, plan_id, created, expired, price, number, access_count, access_limit, gateway, gateway_id, parent, published)' .
						' VALUES("", ' . $userid . ', ' . $prow->plan_id . ', NOW(), "' . $expired . '", ' . $prow->price . ', 0, 0, 0, "' . $gateway . '", "' . $gateway_id . '", 0, 1)'
						;
					$db->setQuery( $sql );
					$db->query();						
				}
				$plans[] = $prow->plan_id;	
			}
			$plans = implode(',', $plans);
			if ($plans){
				$sql = 'DELETE FROM #__jms_plan_subscrs' .
					' WHERE user_id = ' . (int) $userid .
					' AND plan_id NOT IN (' . $plans . ')' .
					' AND gateway = "jvm"'
					;
			} else {
				$sql = 'DELETE FROM #__jms_plan_subscrs' .
					' WHERE user_id = ' . (int) $userid .
					' AND gateway = "jvm"'
					;
			}
			$db->setQuery( $sql );
			$db->query();
		}
		
		// Check articles restrict
		$query	= $db->getQuery(true);
		
		$query->select('id, articles');
		$query->from('#__jms_plans');
		$query->where('state = 1');
		$query->where('article_type = 1');
		$query->where('articles != ""');
	
		$db->setQuery($query);
		$restrictArticles = $db->loadObjectList();		
		$restrictArticles = jmsGetArticles($restrictArticles);
		
		if($restrictArticles && in_array($row->id, $restrictArticles) && $row->catid != 0) {
			// Restrict to visitor
			if (!$userid)
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
			$subscr = jmsGetSubscr('article', $userid, $db);
			//sherdex
			//print_r($subscr);
			if ($subscr)
			{
				$usart = jmsGetArticles($subscr);
				if (!in_array($row->id, $usart))
				{
					$subscrCat = jmsGetSubscr('category', $userid, $db);
					$uscat = jmsGetCategories($subscrCat);				
					if (!in_array($row->catid, $uscat))
					{
						if ($return)
						jmsRestrictC();
						jmsMarkTitle($row, $cnf);
						return;
					}
				}
				foreach($subscr as $srow)
				{
					if ($return)
					jmsUpdateArticle('article_type', $srow->plan_id);
					jmsMarkTitlePaid($row, $cnf);
				}
				unset($subscr, $srow);
			}
			else
			{				
				$subscrCat = jmsGetSubscr('category', $userid, $db);
				$uscat = jmsGetCategories($subscrCat);				
				if (!in_array($row->catid, $uscat))
				{
					if ($return)
					jmsRestrictC();
					jmsMarkTitle($row, $cnf);
					return;
				}
				foreach($subscrCat as $srow)
				{
					if ($return)
					jmsUpdateArticle('category_type', $srow->plan_id);
					jmsMarkTitlePaid($row, $cnf);
				}
				unset($subscrCat, $srow);
			}
			return;			
		}

		// Check categry restrict
		$query	= $db->getQuery(true);
		
		$query->select('id, categories, params');
		$query->from('#__jms_plans');
		$query->where('state = 1');
		$query->where('category_type = 1');
		$query->where('categories != ""');
		
		$db->setQuery($query);
		$categories = $db->loadObjectList();
		$categories = jmsGetCategories($categories);
		
		if ($categories && in_array($row->catid, $categories))
		{
			// Restrict to visitor
			if (!$userid)
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
			// Check if this current user has available subscription for this current plan
			$subscr = jmsGetSubscr('category', $userid, $db);
			
			if ($subscr)
			{
				$uscat = jmsGetCategories($subscr);
				
				if (!in_array($row->catid, $uscat))
				{
					if ($return)
					jmsRestrictC();
					jmsMarkTitle($row, $cnf);
					return;
				}
				foreach($subscr as $srow)
				{
					if ($return)
					jmsUpdateArticle('category_type', $srow->plan_id);
					jmsMarkTitlePaid($row, $cnf);
				}
				unset($subscr, $srow);
			}
			else
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
		}

		// Check user type subscription
		if (preg_match("/{JMSBOT USER=([\d,]+)}/iU", $row->text, $matches))
		{
			$row->text = preg_replace("/{JMSBOT USER=[^}]+}/iU", "", $row->text);
			$ids = explode(",", $matches[1]);

			if (!in_array($userid, $ids) || !$userid)
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
			$subscr = jmsGetSubscr('user', $userid, $db);
			if ($subscr)
			{
				if (count($subscr) <= 0)
				{
					if ($return)
					jmsRestrictC();
					jmsMarkTitle($row, $cnf);
					return;
				}
				foreach($subscr as $srow)
				{
					if ($return)
					jmsUpdateArticle('user_type', $srow->plan_id);
					jmsMarkTitlePaid($row, $cnf);
				}
				unset($subscr, $srow);
			}
			else
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
		}

		// Check article type subscription
		if (preg_match("/{JMSBOT SUBSCRIPTION=([\d,]+)}/iU", $row->text, $matches))
		{

			$row->text = preg_replace("/{JMSBOT SUBSCRIPTION=[^}]+}/iU", "", $row->text);

			if (!$userid)
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
			$subscr = jmsGetSubscr('article', $userid, $db, $matches[1]);

			if ($subscr)
			{
				if (count($subscr) <= 0)
				{
					if ($return)
					jmsRestrictC();
					jmsMarkTitle($row, $cnf);
					return;
				}
				foreach($subscr as $srow)
				{
					if ($return)
					jmsUpdateArticle('article_type', $srow->plan_id);
					jmsMarkTitlePaid($row, $cnf);
				}
			}
			else
			{
				if ($return)
				jmsRestrictC();
				jmsMarkTitle($row, $cnf);
				return;
			}
		}
		return '';
	}
}

/**
 * Method to get a specific subscription type for a specific user 
 * @param string $type
 * @param int $userid
 * @param database object $db
 * @param string $ids
 * @return object
 */
function jmsGetSubscr($type, $userid, $db, $ids = '')
{
	$db->setQuery(
		'SELECT u.plan_id, s.params, u.id, s.`categories`, s.`articles`, u.access_count, u.access_limit, s.limit_time_type,' .
		' IF(u.access_limit > 0, IF(u.access_count >= u.access_limit, 0, 1), 1) AS cl'.
		' FROM #__jms_plan_subscrs AS u' .
		' LEFT JOIN #__jms_plans AS s ON s.id = u.plan_id  ' .
		' WHERE u.user_id = ' . (int)$userid .
        ' AND u.expired > NOW()' .
        ' AND u.created < NOW()' .
        ' AND u.state = 1' .
        ' AND s.`' . $type . '_type` = 1'
        . ((int)$ids ? ' AND s.id IN (' . (int)$ids . ') ' : null) .
        ' GROUP BY u.plan_id'.
		' HAVING cl > 0'.
		' ORDER BY u.created DESC');
		
	return $db->loadObjectList();
}
/**
 * Method to mark title of an article as restricted
 * @param article object $row
 * @param plugin param object $param
 * @return updated article object
 */
function jmsMarkTitle(&$row, $param)
{
	$Itemid = JRequest::getInt('Itemid');
	
	if ($param->def('mark') == 0)
	return;
		
	$sign = $param->def('pic');
	$img = sprintf('<img src="%s" alt="Subscribe" border="0" align="absmiddle" class="jmslock" /> %s', $sign, $param->def('pic_text'));
	if ($param->def('link') == 1)
	{
		$img = sprintf('<a href="%s">%s</a>', JRoute::_('index.php?option=com_jms&view=form&Itemid' . $Itemid), $img);
	}
	switch ($param->def('mark_type'))
	{
		case 0 :
			$row->title = $img . ' ' . $row->title;
			break;

		case 1 :
			$row->title .= ' ' . $img;
			break;

		case 2 :
			$class = '<style type="text/css">.jmslock{float:left}</style>';
			$row->text = $class . $img . ' ' . $row->text;
			break;

		case 3 :
			$row->text .= ' <br />' . $img;
			break;
	}
	return $row;
}

/**
 * Method to update access count for subscription
 * @param string $type
 * @param int $id
 */
function jmsUpdateArticle($type, $id = FALSE)
{

	$db = & JFactory::getDBO();
	$my = & JFactory::getUser();
	$userid = $my->get('id');
	
	$sql = "UPDATE #__jms_plan_subscrs AS u, #__jms_plans AS s
			SET u.access_count = u.access_count + 1 
			WHERE u.plan_id = s.id 
			AND s.$type = 1 " . ($id ? " AND u.plan_id = '{$id}'" : "") . " AND u.user_id = {$userid}";
			
	$db->setQuery($sql);
	$db->query();
}

/**
 * Method to get articles
 * @param articles array $array
 * @return array
 */
function jmsGetArticles($array) {
	
    if(count($array) > 0) {
		$registry = new JRegistry;
        $restrict = array();
		foreach($array AS $key => $arr) {			
			$registry->loadString($arr->articles);
			$arr->articles = $registry->toArray();
			$restrict = array_merge($restrict, $arr->articles);
		}
        $restrict = array_flip($restrict);
        $restrict = array_flip($restrict);
        return $restrict;
    }
    return;
}

/**
 * Method to get categories
 * @param categories array $array
 * @return array
 */
function jmsGetCategories($array) {
	
	if (count($array) > 0) {
		$registry = new JRegistry;
		$restrict = array();
		foreach($array as $arr)
		{
			$registry->loadString($arr->categories);
			$arr->categories = $registry->toArray();
			$restrict = array_merge($restrict, $arr->categories);
		}
		$restrict = array_flip($restrict);
		$restrict = array_flip($restrict);
		return $restrict;
	}
	return array();
}

/**
 * Method to restrict access to an article and redirect to subscription system
 */
function jmsRestrictC()
{
	$mainframe = JFactory::getApplication();
	$Itemid = JRequest::getInt('Itemid');

	$session = & JFactory::getSession();
	$uri = &JFactory::getURI();
	$url = $uri->toString();

	$current = JURI::base() . ereg_replace("^/", "", $_SERVER['REQUEST_URI']);
	$session->set('jms_access_url', $url);

	$mainframe->redirect("index.php?option=com_jms&view=form&Itemid=" . $Itemid);
}

/**
 * Method to mark title of an article as accessible
 * @param article object $row
 * @param array $param
 * @return updated article object
 */
function jmsMarkTitlePaid(&$row, $param)
{
	$mainframe = JFactory::getApplication();
	$Itemid = JRequest::getInt('Itemid');
	
	if($param->def('mark') == 0) return;
		
		$sign = $param->def('regpic');
		$img = sprintf('<img src="%s" alt="You can access now" border="0" align="absmiddle" class="jmslock" /> %s', $sign, $param->def('regpic_text'));
		
		$temptitle =  str_replace('alt="You can access now"','', $row->title);	
		
	if ( strlen( $temptitle) !=  strlen($row->title) ) {
		return $row;
		exit();
	}
	
	switch($param->def('mark_type')){
		case 0:
			$row->title = $img . ' ' . $row->title;
			break;	
		case 1:
			$row->title .= ' '.$img;
		break;	
		case 2:
			$class = '';
			$row->text = $class.$img . ' ' . $row->text;
			break;	
		case 3:
			$row->text .= ' <br />'.$img;
			break;
	}
	return $row;
}