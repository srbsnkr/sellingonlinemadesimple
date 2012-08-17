<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT.DS.'helper.php');
$app		=& JFactory::getApplication(); 
$params		=& $app->getParams('com_rsseo');


if ($params->get('show_page_title', 1) && $params->get('page_title') != '') {
	echo '<div class="componentheading'.$params->get('pageclass_sfx').'">'
		.$params->get('page_title').
	'</div>';
}
echo rsseoHelper::generateSitemap();