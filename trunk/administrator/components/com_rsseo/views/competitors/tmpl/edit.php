<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">

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
		<?php if(!$this->data->IdCompetitor) { ?>if(form.Competitor.value=='') { form.Competitor.className = 'rserror'; ret=false; } else { form.Competitor.className = '';  }	<?php } ?>
		if(ret) submitform(task);
	}
	return false;
	 
}
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
								<?php echo JText::_( 'RSSEO_COMPETITOR_NAME' ).':'; ?>
							</label>
						</td>
						<td>
						<?php if(!$this->data->IdCompetitor){?>
							<label for="title">
								<?php echo $this->lists['protocol_select']; ?>
							</label>
							<label for="title">
								<input type="text" name="Competitor" style="width:400px;"> (*)
							</label>
						<?php } else {?>
							<label for="title">
								<?php echo $this->data->Competitor;?>
								<input type="hidden" name="Competitor" value="<?php echo $this->data->Competitor;?>" />
							</label>
						<?php } ?>
						</td>
					</tr>	
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_COMPETITOR_TAGS' ).':'; ?>
							</label>
						</td>
						<td>
							<label for="title">
								<textarea name="Tags" cols="50" rows="5"><?php echo $this->data->Tags;?></textarea>
							</label>
							<br/>
							<?php echo JText::_('RSSEO_COMPETITOR_TAGS_DESC');?>
						</td>
					</tr>	
				</table>
			</td>
		</tr>
	</table>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="controller" value="competitors" />
<input type="hidden" name="view" value="competitors" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="cid" value="<?php echo $this->data->IdCompetitor;?>" />
<input type="hidden" name="IdCompetitor" value="<?php echo $this->data->IdCompetitor;?>" />
<input type="hidden" name="ordering" value="<?php echo $this->data->ordering;?>" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>