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
	var form = document.adminForm;
	
	if(task == 'back')
		document.location='index.php?option=com_rsseo&task=editpage&cid=<?php echo $this->cid; ?>';
	else  
		Joomla.submitform(task);
	return false;
}
<?php } else { ?>
function submitbutton(task)
{
	var form = document.adminForm;
	
	if(task == 'back')
	{
		document.location='index.php?option=com_rsseo&task=editpage&cid=<?php echo $this->cid; ?>';
	}
	else  
		submitform(task);
	return false;
	 
}
<?php } ?>
</script>
<form action="index.php" method="post" name="adminForm">

<?php echo $this->layout; ?>


<input type="hidden" name="controller" value="pages" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="view" value="pages" />
</form>