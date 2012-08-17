<?php
/**
 * @version $Id$
 *
 * @author Valérie Isaksen
 * @package VirtueMart

 * @copyright Copyright (C) iStraxx - All rights reserved.
 * @license istraxx_license.txt Proprietary License. This code belongs to istraxx UG (haftungsbeschränkt)
 * You are not allowed to distribute or sell this code.
 * You are not allowed to modify this code
 */
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */

class JElementKlarnaLabel extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'KlarnaLabel';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		return '<label for="'.$name.'"'.$class.'>'.$value.'</label>';
	}
}