<?php
/**
 * @copyright	Copyright (C) 2011 Cedric KEIFLIN alias ced1870
 * http://www.joomlack.fr
 * Module Maximenu CK
 * @license		GNU/GPL
 * */

// no direct access
defined('_JEXEC') or die;

class modMaximenuckHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param	JRegistry	$params	The module options.
	 *
	 * @return	array
	 */
	static function getItems(&$params)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// If no active menu, use default
		$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
		
		$user = JFactory::getUser();
		$levels = $user->getAuthorisedViewLevels();
		asort($levels);
		$key = 'menu_items'.$params.implode(',', $levels).'.'.$active->id;
		$cache = JFactory::getCache('mod_maximenuck', '');
		if (!($items = $cache->get($key)))
		{
			// Initialise variables.
			$list		= array();
			$modules	= array();
			$db			= JFactory::getDbo();
			$document = JFactory::getDocument();
			
			// load the libraries
			jimport('joomla.application.module.helper');

			$path = isset($active) ? $active->tree : array();
			$start		= (int) $params->get('startLevel');
			$end		= (int) $params->get('endLevel');
			$items 		= $menu->getItems('menutype',$params->get('menutype'));
			
			// if no items in the menu then exit
			if (!$items) return false;
			
			$lastitem	= 0;
			// list all modules
			$modulesList = modmaximenuckHelper::CreateModulesList();
			
			foreach($items as $i => $item)
			{
				$isdependant = $params->get('dependantitems', false) ? ($start > 1 && !in_array($item->tree[$start-2], $path)) : false;
				if (($start && $start > $item->level)
					|| ($end && $item->level > $end)
					|| $isdependant
				) {
					unset($items[$i]);
					continue;
				}

				$item->deeper = false;
				$item->shallower = false;
				$item->level_diff = 0;

				if (isset($items[$lastitem])) {
					$items[$lastitem]->deeper		= ($item->level > $items[$lastitem]->level);
					$items[$lastitem]->shallower	= ($item->level < $items[$lastitem]->level);
					$items[$lastitem]->level_diff	= ($items[$lastitem]->level - $item->level);
				}
				
				// Test if this is the last item
				$item->is_end = !isset($items[$i + 1]);

				$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);
				$item->active		= false;
				$item->flink = $item->link;

				switch ($item->type)
				{
					case 'separator':
						// No further action needed.
						continue;

					case 'url':
						if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
							// If this is an internal Joomla link, ensure the Itemid is set.
							$item->flink = $item->link.'&Itemid='.$item->id;
						}
                                                $item->flink = JFilterOutput::ampReplace(htmlspecialchars($item->flink));
						break;

					case 'alias':
						// If this is an alias use the item id stored in the parameters to make the link.
						$item->flink = 'index.php?Itemid='.$item->params->get('aliasoptions');
						break;

					default:
						$router = JSite::getRouter();
						if ($router->getMode() == JROUTER_MODE_SEF) {
							$item->flink = 'index.php?Itemid='.$item->id;
						}
						else {
							$item->flink .= '&Itemid='.$item->id;
						}
						break;
				}

				if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
					$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
				}
				else {
					$item->flink = JRoute::_($item->flink);
				}

				//$item->title = htmlspecialchars($item->title);
				$item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''));
				$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''));
				$item->menu_image = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', '')) : '';
				
				
				
				//  ---------------- begin the maximenu work on items --------------------
				
				$item->ftitle = htmlspecialchars($item->title);
                                $item->ftitle = JFilterOutput::ampReplace($item->ftitle);
				$parentItem = modMaximenuckHelper::getParentItem($item->parent_id, $items);
				
				// ---- add some classes ----
				
				// add itemid class
				$item->classe = ' item' . $item->id;
				// add current class
				if (isset($active) && $active->id == $item->id) {
					$item->classe .= ' current';
				}
				// add active class
				if (	$item->type == 'alias' && is_array($path) &&
						in_array($item->params->get('aliasoptions'),$path)
					||	in_array($item->id, $path)) {
					$item->classe .= ' active';
                                        $item->active = true;
				}
				// add the parent class
				if ($item->deeper) {
					$item->classe .= ' deeper';
				}

				if ($item->parent) {
					if ($params->get('layout', 'default') != '_:flatlist')
						$item->classe .= ' parent';
				}
				
				// add last and first class
				$item->classe .= $item->is_end ? ' last' : '';
				$item->classe .= !isset($items[$i-1]) ? ' first' : '';	

                                if (isset($items[$lastitem])) {
                                    $items[$lastitem]->classe .= $items[$lastitem]->shallower ? ' last' : '';
                                    $item->classe .= $items[$lastitem]->deeper ? ' first' : '';
                                    if (isset($items[$i+1]) AND $item->level - $items[$i+1]->level > 1) {
                                            $parentItem->classe .= ' last';
                                    }
                                }


                // ---- manage params ----

				// -- manage column --
				$item->colwidth = $item->params->get('maximenu_colwidth', '180');
				$item->createnewrow = $item->params->get('maximenu_createnewrow', 0) || stristr($item->ftitle,'[newrow]');
                                // check if there is a width for the subcontainer
                                preg_match('/\[subwidth=([0-9]+)\]/', $item->ftitle, $subwidth);
                                $subwidth = isset($subwidth[1]) ? $subwidth[1] : '';
                                if ($subwidth) $item->ftitle = preg_replace('/\[subwidth=[0-9]+\]/', '', $item->ftitle);
				$item->submenucontainerwidth = $item->params->get('maximenu_submenucontainerwidth', '') + $subwidth;
				
				if ($item->params->get('maximenu_createcolumn', 0)) {
					$item->colonne = true;
					//$parentItem = modMaximenuckHelper::getParentItem($item->parent_id, $items);
					// add the value to give the total parent container width
					
					if (isset($parentItem->submenuswidth)) {
						$parentItem->submenuswidth = strval($parentItem->submenuswidth) + strval($item->colwidth);
					} else {
						$parentItem->submenuswidth = strval($item->colwidth);
					}
					// if specified by user with the plugin, then give the width to the parent container
					//if (isset($parentItem->submenucontainerwidth)) $parentItem->submenuswidth = $parentItem->submenucontainerwidth;
					if (isset($items[$lastitem]) && $items[$lastitem]->deeper) {
						$items[$lastitem]->columnwidth = $item->colwidth;
					} else {
						$item->columnwidth = $item->colwidth;
					}
				} elseif (preg_match('/\[col=([0-9]+)\]/', $item->ftitle, $resultat)) {
                                        $item->ftitle = str_replace('[newrow]', '', $item->ftitle);
					$item->ftitle = preg_replace('/\[col=[0-9]+\]/', '', $item->ftitle);
					$item->colonne = true;
					//$parentItem = modMaximenuckHelper::getParentItem($item->parent_id, $items);
					if (isset($parentItem->submenuswidth)) {
						$parentItem->submenuswidth = strval($parentItem->submenuswidth) + strval($resultat[1]);
					} else {
						$parentItem->submenuswidth = strval($resultat[1]);
					}
					if (isset($items[$lastitem]) && $items[$lastitem]->deeper) {
						$items[$lastitem]->columnwidth = $resultat[1];
					} else {
						$item->columnwidth = $resultat[1];
					}
				}
				if (isset($parentItem->submenucontainerwidth) AND $parentItem->submenucontainerwidth) $parentItem->submenuswidth = $parentItem->submenucontainerwidth; 
				
				// -- manage module --
				$moduleid = $item->params->get('maximenu_module', '');
				$style = $item->params->get('maximenu_forcemoduletitle', 0) ? 'xhtml' : '';
				if ($item->params->get('maximenu_insertmodule', 0)) {
					if (!isset($modules[$moduleid])) $modules[$moduleid] = modmaximenuckHelper::GenModuleById($moduleid, $params, $modulesList, $style);
					$item->content = '<div class="maximenuck_mod">' . $modules[$moduleid] . '<div class="clr"></div></div>';
				} elseif (preg_match('/\[modid=([0-9]+)\]/', $item->ftitle, $resultat)) { 
					$item->ftitle = preg_replace('/\[modid=[0-9]+\]/', '', $item->ftitle);
					$item->content = '<div class="maximenuck_mod">' . modmaximenuckHelper::GenModuleById($resultat[1], $params, $modulesList, $style) . '<div class="clr"></div></div>';
				}
				
				// -- manage rel attribute --
				$item->rel = '';
				if ($rel = $item->params->get('maximenu_relattr', '')) {
					$item->rel = ' rel="' . $rel . '"';
				} elseif (preg_match('/\[rel=([a-z]+)\]/i', $item->ftitle, $resultat)) {
					$item->ftitle = preg_replace('/\[rel=[a-z]+\]/i', '', $item->ftitle);
					$item->rel = ' rel="' . $resultat[1] . '"';
				}
				
				// -- manage link description --
				$item->description = $item->params->get('maximenu_desc', '');
				if ($item->description) {
					$item->desc = $item->description;
				} else {
					$resultat = explode("||", $item->ftitle);
					if (isset($resultat[1])) {
						$item->desc = $resultat[1];
					} else {
						$item->desc = '';
					}
					$item->ftitle = $resultat[0];	
				}
				

				// add styles to the page for customization
				$menuID = $params->get('menuid', 'maximenuck');
				$itemstyles = "";
				if ($item->titlecolor = $item->params->get('maximenu_titlecolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > a span.titreck {color:" . $item->titlecolor . " !important;} div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > span.separator span.titreck {color:" . $item->titlecolor . " !important;}";
				if ($item->desccolor = $item->params->get('maximenu_desccolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > a span.descck {color:" . $item->desccolor . " !important;} div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > span.separator span.descck {color:" . $item->desccolor . " !important;}";
				if ($item->titlehovercolor = $item->params->get('maximenu_titlehovercolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > a:hover span.titreck {color:" . $item->titlehovercolor . " !important;} div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > span.separator:hover span.titreck {color:" . $item->titlehovercolor . " !important;}";
				if ($item->deschovercolor = $item->params->get('maximenu_deschovercolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > a:hover span.descck {color:" . $item->deschovercolor . " !important;} div#" . $menuID . " ul.maximenuck li.item" . $item->id . " > span.separator:hover span.descck {color:" . $item->deschovercolor . " !important;}";
				if ($item->titleactivecolor = $item->params->get('maximenu_titleactivecolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.active.item" . $item->id . " > a span.titreck {color:" . $item->titleactivecolor . " !important;} div#" . $menuID . " ul.maximenuck li.active.item" . $item->id . " > span.separator span.titreck {color:" . $item->titleactivecolor . " !important;}";
				if ($item->descactivecolor = $item->params->get('maximenu_descactivecolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.active.item" . $item->id . " > a span.descck {color:" . $item->descactivecolor . " !important;} div#" . $menuID . " ul.maximenuck li.active.item" . $item->id . " > span.separator span.descck {color:" . $item->descactivecolor . " !important;}";
				if ($item->libgcolor = $item->params->get('maximenu_libgcolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.item" . $item->id . " {background:" . $item->libgcolor . " !important;}";
				if ($item->lihoverbgcolor = $item->params->get('maximenu_lihoverbgcolor', ''))
					$itemstyles .= "div#" . $menuID . " ul.maximenuck li.item" . $item->id . ":hover {background:" . $item->lihoverbgcolor . " !important;}";
				if ($itemstyles)
					$document->addStyleDeclaration($itemstyles);

				// get plugin parameters that are used directly in the layout
				$item->leftmargin = $item->params->get('maximenu_leftmargin', '');
				$item->topmargin = $item->params->get('maximenu_topmargin', '');
				$item->liclass = $item->params->get('maximenu_liclass', '');
				$item->colbgcolor = $item->params->get('maximenu_colbgcolor', '');
				$item->tagcoltitle = $item->params->get('maximenu_tagcoltitle', 'none');
				
				$lastitem			= $i;
				
			} // end of boucle for each items

			// give the correct deep infos for the last item
			if (isset($items[$lastitem])) {
				$items[$lastitem]->deeper		= (($start?$start:1) > $items[$lastitem]->level);
				$items[$lastitem]->shallower	= (($start?$start:1) < $items[$lastitem]->level);
				$items[$lastitem]->level_diff	= ($items[$lastitem]->level - ($start?$start:1));
			}

			$cache->store($items, $key);
		}
		return $items;
	}
	
	/**
	 * Get a the parent item object
	 * 
	 * @param Object $id The current item
	 * @param Array $items The list of all items
	 *
	 * @return object
	 */
	static function getParentItem($id, $items) {
        foreach ($items as $item) {
            if ($item->id == $id)
                return $item;
        }
    }

	/**
	 * Render the module
	 * 
	 * @param Int $moduleid The module ID to load
	 * @param JRegistry $params
	 * @param Array $modulesList The list of all module objects published
	 *
	 * @return string with HTML
	 */
    static function GenModuleById($moduleid, $params, $modulesList, $style) {
			
			
			$attribs['style'] = $style;
			// get the title of the module to load
			$modtitle = $modulesList[$moduleid]->title;
			$modname = $modulesList[$moduleid]->module;
			//$modname = preg_replace('/mod_/', '', $modname);

			// load the module
			if (JModuleHelper::isEnabled($modname)) {
				$module = JModuleHelper::getModule($modname, $modtitle);
				return JModuleHelper::renderModule($module, $attribs);
			}
			return 'Module ID='.$moduleid.' not found !';
		
    }

	/**
	 * Create the list of all modules published as Object
	 *
	 * @return Array of Objects
	 */
    static function CreateModulesList() {
        $db = JFactory::getDBO();
        $query = "
			SELECT *
			FROM #__modules
			WHERE published=1
			ORDER BY id
			;";
        $db->setQuery($query);
        $modulesList = $db->loadObjectList('id');
        return $modulesList;
    }
}
