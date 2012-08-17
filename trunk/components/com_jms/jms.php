<?php
/**
 * @version     2.0.2
 * @package     com_jms
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= JController::getInstance('Jms');
$controller->execute(JRequest::getVar('task',''));
$controller->redirect('');
