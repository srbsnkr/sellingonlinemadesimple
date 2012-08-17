<?php
/**
 * @package   	Yootheme WidgetKit Lightbox
 * @copyright 	Copyright (C) 2006 - 2012 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL Version 2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @author		Ryan Demmer
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined( '_WF_EXT' ) or die('RESTRICTED');

class WFPopupsExtension_Widgetkit extends JObject
{	
	/**
	* Constructor activating the default information of the class
	*
	* @access	protected
	*/
	function __construct($options = array())
	{		
		if (self::isEnabled()) {
			$scripts = array();
			
			$document = WFDocument::getInstance();
			
			$document->addScript('widgetkit', 'extensions/popups/widgetkit/js');
			$document->addStyleSheet('widgetkit', 'extensions/popups/widgetkit/css');
		}
	}
	
	function getParams()
	{
		$wf = WFEditorPlugin::getInstance();	
			
		return array(
			'lightbox_padding' 			=> $wf->getParam('popups.widgetkit.lightbox_padding', ''),
			'lightbox_overlayShow'		=> $wf->getParam('popups.widgetkit.lightbox_overlayShow', ''),
			'lightbox_transitionIn'		=> $wf->getParam('popups.widgetkit.lightbox_transitionIn', ''),
			'lightbox_transitionOut'	=> $wf->getParam('popups.widgetkit.lightbox_transitionOut', ''),
			'lightbox_titlePosition'	=> $wf->getParam('popups.widgetkit.lightbox_titlePosition', '')
		);
	}
	
	function isEnabled()
	{		
		$wf = WFEditorPlugin::getInstance();
		
		if (JPluginHelper::isEnabled('system', 'widgetkit_system') && $wf->getParam('popups.widgetkit.enable', 1) == 1) {
			return true;
		}
		
		return false;
	}
}
?>