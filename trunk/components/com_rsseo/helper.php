<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.base.tree');
jimport('joomla.utilities.simplexml');

class rsseoHelper
{
	function is16()
	{
		$jversion = new JVersion();
		$current_version =  $jversion->getShortVersion();
		return (version_compare('1.6.0', $current_version) <= 0);
	}
	
	function generateSitemap()
	{
		jimport('joomla.html.parameter');
		
		$db  =& JFactory::getDBO();
		$doc =& JFactory::getDocument();
		
		//load the style
		$doc->addStyleSheet(JURI::root(true).'/components/com_rsseo/style.css');
		
		//get selected menus
		$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'sitemap_menus' ");
		$menus = $db->loadResult();
		
		//get excluded items
		$db->setQuery("SELECT ConfigValue FROM #__rsseo_config WHERE ConfigName = 'sitemap_excludes' ");
		$excludes = $db->loadResult();
		
		if (empty($menus)) return '';
		$menus = explode(',',$menus);
		
		$return = '';
		
		if (!empty($menus))
			foreach ($menus as $menu)
			{
				$params = new JParameter('');
				$params->set('menutype',$menu);
				$params->set('ignore',$excludes);
				
				$html = rsseoHelper::render($params, 'rsseoCallback');
				if (empty($html)) continue;
				
				$db->setQuery("SELECT title FROM #__menu_types WHERE menutype = '".$db->getEscaped($menu)."'");
				$title = $db->loadResult();
				
				$return .= '<div class="rsseo_title">'. $title .'</div>';
				$return .= $html;
			}
		return $return;
	}
	
	
	function buildXML($params)
	{
		$menu = new JMenuTreeRSseo($params);
		$items = &JSite::getMenu();
		// Get Menu Items
		$rows = $items->getItems('menutype', $params->get('menutype'));
		$maxdepth = 15;
		
		//get ignored items
		$ignored = $params->get('ignore','');
		$ignored = !empty($ignored) ? explode(',',$ignored) : array(); 
		
		// Build Menu Tree root down (orphan proof - child might have lower id than parent)
		$user =& JFactory::getUser();
		$ids = rsseoHelper::is16() ? array(1 => true) : array(0 => true);
		$last = null;
		$unresolved = array();
		// pop the first item until the array is empty if there is any item	
		if (is_array($rows)) 
		{
			while (count($rows) && !is_null($row = array_shift($rows)))
			{
				//remove unwanted items
				if (in_array($row->id,$ignored)) continue;				
				$parent = rsseoHelper::is16() ? $row->parent_id : $row->parent;
				if (array_key_exists($parent, $ids)) 
				{
					$row->ionly = 0;
					$menu->addNode($params, $row);

					// record loaded parents
					$ids[$row->id] = true;
				} else 
				{
					// no parent yet so push item to back of list
					if(!array_key_exists($row->id, $unresolved) || $unresolved[$row->id] < $maxdepth) 
					{
						array_push($rows, $row);
						// so let us do max $maxdepth passes
						// TODO: Put a time check in this loop in case we get too close to the PHP timeout
						if(!isset($unresolved[$row->id])) $unresolved[$row->id] = 1;
						else $unresolved[$row->id]++;
					}
				}
			}
		}
		
		return $menu->toXML();
	}

	function &getXML($type, &$params, $decorator)
	{
		static $xmls;

		if (!isset($xmls[$type])) 
		{
			$cache =& JFactory::getCache('com_rsseo');
			$string = $cache->call(array('rsseoHelper', 'buildXML'), $params);
			$xmls[$type] = $string;
		}
		
		// Get document
		$xml = JFactory::getXMLParser('Simple');
		$xml->loadString($xmls[$type]);
		$doc = &$xml->document;

		$menu	= &JSite::getMenu();
		$active	= $menu->getActive();
		$start	= 0;
		$end	= 0;
		$sChild	= 1;
		$path	= array();

		// Get subtree
		if ($start)
		{
			$found = false;
			$root = true;
			if(!isset($active)){
				$doc = false;
			}
			else
			{
				$path = $active->tree;
				for ($i=0,$n=count($path);$i<$n;$i++)
				{
					foreach ($doc->children() as $child)
					{
						if ($child->attributes('id') == $path[$i]) 
						{
							$doc = &$child->ul[0];
							$root = false;
							break;
						}
					}

					if ($i == $start-1) 
					{
						$found = true;
						break;
					}
				}
				if ((!is_a($doc, 'JSimpleXMLElement')) || (!$found) || ($root))
					$doc = false;
			}
		}
		
		if ($doc && is_callable($decorator))
			$doc->map($decorator, array('end'=>$end, 'children'=>$sChild));
		
		return $doc;
	}

	function render(&$params, $callback)
	{
		// Include the new menu class
		$xml = rsseoHelper::getXML($params->get('menutype'), $params, $callback);
		if ($xml) 
		{
			$childrens = $xml->children();
			$xml->addAttribute('class', 'rsseo_links');
			$result = JFilterOutput::ampReplace($xml->toString(false));
			$result = str_replace(array('<ul/>', '<ul />'), '', $result);
			if (empty($childrens)) $result = '';
			return $result;
		}
		return;
	}
}

/**
 * Main Menu Tree Class.
 *
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */

class JMenuTreeRSseo extends JTree
{
	/**
	 * Node/Id Hash for quickly handling node additions to the tree.
	 */
	var $_nodeHash = array();

	/**
	 * Menu parameters
	 */
	var $_params = null;

	/**
	 * Menu parameters
	 */
	var $_buffer = null;

	function __construct(&$params)
	{
		$this->_params		=& $params;
		if (rsseoHelper::is16())
			$this->_root		= new JMenuNodeRSSeo(1, 'ROOT');
		else
			$this->_root		= new JMenuNodeRSSeo(0, 'ROOT');
		if (rsseoHelper::is16())
			$this->_nodeHash[1]	=& $this->_root;
		else
			$this->_nodeHash[0]	=& $this->_root;
		$this->_current		=& $this->_root;
	}

	function addNode(&$params, $item)
	{
		// Get menu item data
		$data = $this->_getItemData($params, $item);

		// Create the node and add it
		if (rsseoHelper::is16())
			$node = new JMenuNodeRSSeo($item->id, $item->title, $item->access, $data);
		else
			$node = new JMenuNodeRSSeo($item->id, $item->name, $item->access, $data);

		if (isset($item->mid)) {
			$nid = $item->mid;
		} else {
			$nid = $item->id;
		}
		$this->_nodeHash[$nid] =& $node;
		$parent = rsseoHelper::is16() ? $item->parent_id : $item->parent;
		$this->_current =& $this->_nodeHash[$parent];

		if ($item->type == 'menulink' && !empty($item->query['Itemid'])) {
			$node->mid = $item->query['Itemid'];
		}

		if ($this->_current) {
			$this->addChild($node, true);
		} else {
			// sanity check
			JError::raiseError( 500, 'Orphan Error. Could not find parent for Item '.$item->id );
		}
	}

	function toXML()
	{
		// Initialize variables
		$this->_current =& $this->_root;
		
		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML(0);
			}
			$this->_buffer .= '</ul>';
		}
		if($this->_buffer == '') { $this->_buffer = '<ul />'; }
		return $this->_buffer;
	}

	function _getLevelXML($depth)
	{
		$depth++;

		// Start the item
		$rel = (!empty($this->_current->mid)) ? ' rel="'.$this->_current->mid.'"' : '';
		$this->_buffer .= '<li access="'.$this->_current->access.'" level="'.$depth.'" id="'.$this->_current->id.'"'.$rel.'>';

		// Append item data
		$this->_buffer .= $this->_current->link;

		// Recurse through item's children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML($depth);
			}
			$this->_buffer .= '</ul>';
		}

		// Finish the item
		$this->_buffer .= '</li>';
	}

	function _getItemData(&$params, $item)
	{
		jimport('joomla.html.parameter');
		$data = null;

		// Menu Link is a special type that is a link to another item
		if ($item->type == 'menulink')
		{
			$menu = &JSite::getMenu();
			if ($newItem = $menu->getItem($item->query['Itemid'])) 
			{
				$tmp = clone($newItem);
				if (rsseoHelper::is16())
					$tmp->title	 = '<span><![CDATA['.$item->title.']]></span>';
				else
					$tmp->name	 = '<span><![CDATA['.$item->name.']]></span>';
				$tmp->mid	 = $item->id;
				if (rsseoHelper::is16())
					$tmp->parent_id = $item->parent_id;
				else 
					$tmp->parent = $item->parent;
			} else {
				return false;
			}
		} else {
			$tmp = clone($item);
			if (rsseoHelper::is16())
				$tmp->title = '<span><![CDATA['.$item->title.']]></span>';
			else
				$tmp->name = '<span><![CDATA['.$item->name.']]></span>';
		}

		$iParams = new JParameter($tmp->params);
		switch ($tmp->type)
		{
			case 'separator' :
				if (rsseoHelper::is16())
					return '<span class="separator">'.$tmp->title.'</span>';
				else
					return '<span class="separator">'.$tmp->name.'</span>';
				break;

			case 'url' :
				if ((strpos($tmp->link, 'index.php?') === 0) && (strpos($tmp->link, 'Itemid=') === false)) {
					$tmp->url = $tmp->link.'&amp;Itemid='.$tmp->id;
				} else {
					$tmp->url = $tmp->link;
				}
				break;

			default :
				$router = JSite::getRouter();
				$tmp->url = $router->getMode() == JROUTER_MODE_SEF ? 'index.php?Itemid='.$tmp->id : $tmp->link.'&Itemid='.$tmp->id;
				break;
		}

		// Print a link if it exists
		if ($tmp->url != null)
		{
			// Handle SSL links
			$iSecure = $iParams->def('secure', 0);
			if ($tmp->home == 1) {
				$tmp->url = JURI::base();
			} elseif (strcasecmp(substr($tmp->url, 0, 4), 'http') && (strpos($tmp->link, 'index.php?') !== false)) {
				$tmp->url = JRoute::_($tmp->url, true, $iSecure);
			} else {
				$tmp->url = str_replace('&', '&amp;', $tmp->url);
			}

			switch ($tmp->browserNav)
			{
				default:
				case 0:
					// _top
					if (rsseoHelper::is16())
						$data = '<a href="'.$tmp->url.'">'.$tmp->title.'</a>';
					else
						$data = '<a href="'.$tmp->url.'">'.$tmp->name.'</a>';
					break;
				case 1:
					// _blank
					if (rsseoHelper::is16())
						$data = '<a href="'.$tmp->url.'" target="_blank">'.$tmp->title.'</a>';
					else
					$data = '<a href="'.$tmp->url.'" target="_blank">'.$tmp->name.'</a>';
					break;
				case 2:
					// window.open
					$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$this->_params->get('window_open');

					// hrm...this is a bit dickey
					$link = str_replace('index.php', 'index2.php', $tmp->url);
					if (rsseoHelper::is16())
						$data = '<a href="'.$link.'" onclick="window.open(this.href,\'targetWindow\',\''.$attribs.'\');return false;">'.$tmp->title.'</a>';
					else
						$data = '<a href="'.$link.'" onclick="window.open(this.href,\'targetWindow\',\''.$attribs.'\');return false;">'.$tmp->name.'</a>';
					break;
			}
		} else {
			if (rsseoHelper::is16())
				$data = '<a>'.$tmp->title.'</a>';
			else
				$data = '<a>'.$tmp->name.'</a>';
		}

		return $data;
	}
}



/**
 * Main Menu Tree Node Class.
 *
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */

class JMenuNodeRSSeo extends JNode
{
	/**
	 * Node Title
	 */
	var $title = null;

	/**
	 * Node Link
	 */
	var $link = null;

	/**
	 * CSS Class for node
	 */
	var $class = null;

	function __construct($id, $title, $access = null, $link = null, $class = null)
	{
		$this->id		= $id;
		$this->title	= $title;
		$this->access	= $access;
		$this->link		= $link;
		$this->class	= $class;
	}
}



function rsseoCallback(&$node, $args)
{
	$user	= &JFactory::getUser();
	$menu	= &JSite::getMenu();
	$active	= $menu->getActive();
	$path	= isset($active) ? array_reverse($active->tree) : null;

	if (($args['end']) && ($node->attributes('level') >= $args['end']))
	{
		$children = $node->children();
		foreach ($node->children() as $child)
		{
			if ($child->name() == 'ul')
				$node->removeChild($child);
		}
	}
	
	if (rsseoHelper::is16())
		$accessList = JAccess::getAuthorisedViewLevels($user->id);
	else 
		$accessList = array($user->get('aid',0));
	
	if ($node->name() == 'ul')
		foreach ($node->children() as $child)
			if (!in_array($child->attributes('access'),$accessList))
				$node->removeChild($child);
	
	if (($node->name() == 'li') && isset($node->ul))
		$node->addAttribute('class', 'parent');
	
	if (isset($path) && (in_array($node->attributes('id'), $path) || in_array($node->attributes('rel'), $path)))
	{
		$node->addAttribute('class', '');
	}
	else
	{
		if (isset($args['children']) && !$args['children'])
		{
			$children = $node->children();
			foreach ($node->children() as $child)
				if ($child->name() == 'ul')
					$node->removeChild($child);
		}
	}
	
	if (($node->name() == 'li') && ($id = $node->attributes('id'))) 
	{
		if ($node->attributes('class'))
			$node->addAttribute('class', $node->attributes('class').' item'.$id);
		else
			$node->addAttribute('class', 'item'.$id);
	}
	
	$node->removeAttribute('id');
	$node->removeAttribute('rel');
	$node->removeAttribute('level');
	$node->removeAttribute('access');
}