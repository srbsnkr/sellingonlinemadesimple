<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgSystemJmscomponent extends JPlugin
{

	var $_db = null;
	
	function plgSystemJmscomponent(& $subject, $config)
	{
		parent :: __construct($subject, $config);
	}

	function onAfterRoute()
	{
		global $query;
		
		$mainframe = JFactory::getApplication();
		
		if($mainframe->isAdmin()) {
			return true;
		}
		
		$db       =& JFactory::getDBO();
		$my       =& JFactory::getUser();
		$userid   = $my->get('id');
		$view     =  JRequest::getVar('view');
		$session  =& JFactory::getSession();
		$option   = JRequest::getVar('option');
		//$prm = &$mainframe->getParams();
		$cnf = JComponentHelper::getParams('com_jms');

		$url = $session->get('jms_access_url');
		$current = JURI::base().ereg_replace("^/","",$_SERVER['REQUEST_URI']);

		if ($url == $current) {
			$session->clear('jms_access_url');
		}

		if($cnf->get('se', 1))
		{
			$bots = explode("\n", $cnf->get('bots'));
			foreach($bots AS $bot)
			{
				if(!$bot) continue;
				if(@strpos($_SERVER['HTTP_USER_AGENT'], $bot)) return;
			}
		}
				
		$query = "SELECT id, components, params  "
			. "\n FROM #__jms_plans WHERE state = 1 AND `component_type` = 1 AND `components` != ''"
			;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		$components = jmsGetComponents($rows);

		if(@in_array($option, $components))
		{
			foreach($rows AS $row)
			{
				// If this plan is not for this component continue
				
				//if(!in_array("/".$option."/", $row->components)) continue;
				if(!in_array($option, $row->components)) continue;
				
				$params = jmsGetParams($option, $row);

				if($params)
				{
					unset($task, $valarray);
					foreach($params AS $var => $val)
					{
						if(strstr(JRequest::getVar($var, 'novalsfound'), ":"))
							$task[] = substr(JRequest::getVar($var, 'novalsfound'),0, strpos(JRequest::getVar($var, 'novalsfound'), ":"));
						else
							$task[] = JRequest::getVar($var, 'novalsfound');
						$valarray[] = explode(",", $val);
					}
					if(@$task[0] && (@$task[0] != "novalsfound"))
					{
						if(!$valarray[0]) jmsUserAccess($userid);

						if(in_array($task[0], $valarray[0]))
						{
							if(@$task[1] && (@$task[1] != "novalsfound"))
							{
								if(!$valarray[1]) jmsUserAccess($userid);

								if(in_array($task[1], $valarray[1]))
								{
									jmsUserAccess($userid);
									return;
								}
							}
							else
							{
								jmsUserAccess($userid);
								return;
							}
						}
					}

					$params = jmsGetParams2($option, $row);
					if($params)
					{
						unset($task, $valarray);
						foreach($params AS $var => $val)
						{
							$task[] = JRequest::getVar($var, 'novalsfound');
							$valarray[] = explode(",", $val);
						}
							
						if(@$task[0] && (@$task[0] != "novalsfound"))
						{
							if(!$valarray[0]) jmsUserAccess($userid);

							if(in_array($task[0], $valarray[0]))
							{
								if(@$task[1] && (@$task[1] != "novalsfound"))
								{
									if(!$valarray[1]) jmsUserAccess($userid);

									if(in_array($task[1], $valarray[1]))
									{
										jmsUserAccess($userid);
										return;
									}
								}
								else
								{
									jmsUserAccess($userid);
									return;
								}
							}
						}
					}
				}
				else
				{
					jmsUserAccess($userid);
					return;
				}
			}
		}
	}
}

function jmsUserAccess($userid)
{
	// Get DB connector
	$db =& JFactory::getDBO();

	$sql = "SELECT s.*, u.access_count, u.access_limit, u.id AS uid
		FROM #__jms_plan_subscrs AS u
		LEFT JOIN #__jms_plans AS s ON s.id = u.plan_id
		WHERE u.user_id = {$userid}
		AND u.expired > NOW()
		AND u.created < NOW()
		AND u.state = 1
		AND s.component_type = 1
		ORDER BY u.created DESC";
	$db->setQuery( $sql );
	$rows = $db->loadObjectList();

	$components = jmsGetComponents($rows);
	$option =JRequest::getVar('option');

	if(@in_array($option, $components))
	{
		foreach($rows AS $key => $row)
		{
			$params = jmsGetParams($option, $row);

			if($params)
			{
				unset($task, $valarray);
				foreach($params AS $var => $val)
				{
					$task[] = JRequest::getVar($var, 'novalsfound');
					$valarray[] = explode(",", $val);
				}
				if(@$task[0] && (@$task[0] != "novalsfound"))
				{
					if(!$valarray[0])
					{
						jmsUpdateC($row);
						return true;
					}
					if(in_array($task[0], $valarray[0]))
					{
						if(@$task[1] && (@$task[1] != "novalsfound"))
						{
							if(!$valarray[1])
							{
								jmsUpdateC($row);
								return true;
							}
							if(in_array($task[1], $valarray[1]))
							{
								jmsUpdateC($row);
								return true;
							}
						}
						else
						{
							jmsUpdateC($row);
							return true;
						}
					}
				}
				$params = jmsGetParams2($option, $row);
				if($params)
				{
					unset($task, $valarray);
					foreach($params AS $var => $val)
					{
						$task[] = JRequest::getVar($var, 'novalsfound');
						$valarray[] = explode(",", $val);
					}
					if(@$task[0] && (@$task[0] != "novalsfound"))
					{
						if(!$valarray[0])
						{
							jmsUpdateC($row);
							return true;
						}
						if(in_array($task[0], $valarray[0]))
						{
							if(@$task[1] && (@$task[1] != "novalsfound"))
							{
								if(!$valarray[1])
								{
									jmsUpdateC($row);
									return true;
								}
								if(in_array($task[1], $valarray[1]))
								{
									jmsUpdateC($row);
									return true;
								}
							}
							else
							{
								jmsUpdateC($row);
								return true;
							}
						}
					}
				}
			}
			else
			{
				jmsUpdateC($row);
				return true;
			}
		}
	}
	jmsRestrictCom();
}

/**
 * Method to restrict access to component and redirect to subscription system
 *
 */
function jmsRestrictCom()
{
	$mainframe = JFactory::getApplication();
	
	$Itemid = JRequest::getInt('Itemid');
	$session  =& JFactory::getSession();

	$uri = &JFactory::getURI();
	$url = $uri->toString();
	$session->set('jms_access_url', $url);

	$mainframe->redirect("index.php?option=com_jms&view=jms&Itemid=".$Itemid);
}

/**
 * Method to update access count for subscription
 *
 * @param object $row
 */
function jmsUpdateC($row)
{
	$mainframe = JFactory::getApplication();

	$db =& JFactory::getDBO();
	$user =& JFactory::getUser();
	$userid = $user->get('id');
	$uri = &JFactory::getURI();
	$url = $uri->toString();

	$params = new JRegistry();
	$params->loadString($row->params);

	if(($row->access_limit > 0) && ($row->access_count >= $row->access_limit))
	{
		jmsRestrictCom();
	}
	
	$sql = "UPDATE #__jms_plan_subscrs AS u SET u.access_count = u.access_count + 1	WHERE u.id = $row->uid	AND u.user_id = {$userid}";
	$db->setQuery( $sql );
	$db->query();
}

/**
 * Method to get 2 first parameters of component subscription type
 *
 * @param string $option
 * @param object $row
 * @return parameters
 */
function jmsGetParams($option, $row)
{
	$out = false;
	$params = new JRegistry();
	$params->loadString($row->params);
	
	if($params->get($option.'_task1'))
	{
		$out[$params->get($option.'_task1')] = $params->get($option.'_value1');
	}
	
	if($params->get($option.'_task2'))
	{
		$out[$params->get($option.'_task2')] = $params->get($option.'_value2');
	}
	
	return $out;
}

/**
 * Method to get 2 last parameters of component subscription type
 *
 * @param string $option
 * @param object $row
 * @return parameters
 */
function jmsGetParams2($option, $row)
{
	$out = false;
	$params = new JRegistry();
	$params->loadString($row->params);
	
	if($params->get($option.'_task3'))
	{
		$out[$params->get($option.'_task3')] = $params->get($option.'_value3');
	}
	
	if($params->get($option.'_task4'))
	{
		$out[$params->get($option.'_task4')] = $params->get($option.'_value4');
	}
	
	return $out;
}

/**
 * Method to get components
 *
 * @param components array $array
 * @return array
 */
function jmsGetComponents($array)
{
	
	$registry = new JRegistry;
	$restrict = array();
	if(count($array) > 0)
	{
		foreach($array AS $arr)
		{
			$registry->loadString($arr->components);
			$arr->components = $registry->toArray();
			$restrict = array_merge($restrict, $arr->components);
		}
		$restrict = array_flip($restrict);
		$restrict = array_flip($restrict);
		return $restrict;
	}
	return $restrict;
}
?>