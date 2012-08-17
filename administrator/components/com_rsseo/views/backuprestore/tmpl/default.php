<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php?option=com_rsseo&task=backuprestore" name="adminForm" method="post">
<center><h3><a href="index.php?option=com_rsseo&task=backup"><?php echo JText::_('RSSEO_BACKUP'); ?></a> | <a href="index.php?option=com_rsseo&task=restore"><?php echo JText::_('RSSEO_RESTORE'); ?></a></h3></center>

<input type="hidden" name="task" value="backuprestore" />
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="view" value="backuprestore" />
</form>
