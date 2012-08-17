<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
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
		if(form.Keyword.value=='') { form.Keyword.className = 'rserror'; ret=false; } else { form.Keyword.className = '';  }	
		if(ret) Joomla.submitform(task);
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
		if(form.Keyword.value=='') { form.Keyword.className = 'rserror'; ret=false; } else { form.Keyword.className = '';  }	
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
								<?php echo JText::_( 'RSSEO_KEYWORDS_NAME' ).':'; ?>
							</label>
						</td>
						<td>	
							<?php if($this->task=='addmultikeywords') { ?>
							<textarea name="Keyword" cols="80" rows="15"></textarea>
							<?php } else {?>
							<input name="Keyword" value="<?php echo $this->escape($this->data->Keyword); ?>" size="150" /> (*)
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_KEYWORD_IMPORTANCE' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo $this->lists['importance']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_KEYWORD_BOLD' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo $this->lists['bold']; ?>
						</td>
					</tr>
					<tr>
						<td>						
							<label for="title">
								<?php echo JText::_( 'RSSEO_UNDERLINE' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','KeywordUnderline','class="inputbox"',$this->data->KeywordUnderline); ?>
						</td>
					</tr>
					<tr>
						<td>						
							<label for="title" class="hasTip" title="<?php echo JText::_('RSSEO_KEYWORDS_LIMIT_DESC'); ?>">
								<?php echo JText::_( 'RSSEO_KEYWORDS_LIMIT' ).':'; ?>
							</label>
						</td>
						<td>
							<input name="KeywordLimit" style="text-align:center;" value="<?php echo $this->escape($this->data->KeywordLimit); ?>" size="3" /> <?php echo JText::_('RSSEO_KEYWORDS_TIMES'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="attributes">
								<?php echo JText::_( 'RSSEO_KEYWORD_ATTRIBUTES' ).':'; ?>
							</label>
						</td>
						<td>						
							<input name="KeywordAttributes" value="<?php echo $this->escape($this->data->KeywordAttributes); ?>" size="150" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="title" class="editLink hasTip" title="<?php echo JText::_('RSSEO_IAL_DESCRIPTION'); ?>">
								<?php echo JText::_( 'RSSEO_IAL' ).':'; ?>
							</label>
						</td>
						<td>						
							<input name="KeywordLink" value="<?php echo $this->data->KeywordLink; ?>" size="150" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="cid" value="<?php echo $this->data->IdKeyword; ?>" />
<input type="hidden" name="IdKeyword" value="<?php echo $this->data->IdKeyword; ?>" />
<input type="hidden" name="controller" value="keywords" />
<input type="hidden" name="view" value="keywords" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>