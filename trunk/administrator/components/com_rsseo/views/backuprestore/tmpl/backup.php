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
}
<?php } else { ?>
function submitbutton(task)
{
	if(task == 'back') document.location = 'index.php?option=com_rsseo&task=backuprestore';
}
<?php } ?>
</script>
<form action="" name="adminForm" method="post">
</form>