<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
<?php if (rsseoHelper::is16()) { ?>
Joomla.submitbutton = function(task) 
{
	var form = document.adminForm;
	
	if(task == 'cancel')
		Joomla.submitform(task);
	else  
	{
		ret = true;
		if(form.RedirectFrom.value=='') { form.RedirectFrom.className = 'rserror'; ret=false; } else { form.RedirectFrom.className = '';  }	
		if(ret) submitform(task);
	}
	return false;
}
<?php } else { ?>
function submitbutton(task)
{
	var form = document.adminForm;
	
	if(task == 'cancel')
	{
		submitform(task);
	}
	else  
	{
		ret = true;
		if(form.RedirectFrom.value=='') { form.RedirectFrom.className = 'rserror'; ret=false; } else { form.RedirectFrom.className = '';  }	
		if(ret) submitform(task);
	}
	return false;
	 
}
<?php } ?>
</script>
<style>
.rserror 
{
	border:1px solid red;
}
</style>
<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_PUBLISHED' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo JHTML::_('select.booleanlist','published','class="inputbox"',empty($this->data->IdRedirect) ? 1 : $this->data->published); ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_REDIRECTS_REDIRECTSTYPE' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo $this->RedirectType; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_REDIRECTS_REDIRECTSFROM' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo JURI::root(); ?> <input id="RedirectFrom" name="RedirectFrom" value="<?php echo $this->data->RedirectFrom; ?>" size="150" /> (*)
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_REDIRECTS_REDIRECTSTO' ).':'; ?>
							</label>
						</td>
						<td>						
							<span style="margin: 106px;"></span><input name="RedirectTo" value="<?php echo empty($this->data->RedirectTo) ? JURI::root() : $this->data->RedirectTo; ?>" size="150" /> (*)
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="cid" value="<?php echo $this->data->IdRedirect; ?>" />
<input type="hidden" name="IdRedirect" value="<?php echo $this->data->IdRedirect; ?>" />
<input type="hidden" name="controller" value="redirects" />
<input type="hidden" name="view" value="redirects" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>