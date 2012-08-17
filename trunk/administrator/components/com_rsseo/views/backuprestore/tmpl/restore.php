<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">
<?php if (rsseoHelper::is16()) { ?>
Joomla.submitbutton = function(task) 
{
	if(task == 'back') document.location = 'index.php?option=com_rsseo&task=backuprestore';
	else Joomla.submitform(task);

}
<?php } else { ?>
function submitbutton(task)
{
	if(task == 'back') document.location = 'index.php?option=com_rsseo&task=backuprestore';
	else submitform(task);
}
<?php } ?>
</script>
<br/>
<form action="index.php?option=com_rsseo&task=restore" name="adminForm" method="post" enctype="multipart/form-data">
<center><input type="file" name="rspackage" size="50" /> <button type="button" onclick="submitbutton('restore')"><?php echo JText::_('RSSEO_IMPORT'); ?></button></center>

<input type="hidden" name="task" value="restore" />
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="view" value="backuprestore" />
</form>