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
<?php if (rsseoHelper::is16()) { ?>
Joomla.submitbutton = function(task) 
{
	var form = document.adminForm;
	
	if(task == 'cancel')
		Joomla.submitform(task);
	else  
	{
		ret = true;
		<?php if(!$this->cid) { ?>if(form.PageURL.value=='') { form.PageURL.className = 'rserror'; ret=false; } else { form.PageURL.className = '';  }	<?php } ?>
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
		<?php if(!$this->cid) { ?>if(form.PageURL.value=='') { form.PageURL.className = 'rserror'; ret=false; } else { form.PageURL.className = '';  }	<?php } ?>
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
						<td width="15%">
							<label for="title">
								<?php echo JText::_( 'RSSEO_PAGE_PAGEURL' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php 
								if ($this->cid != 0) 
								{
									echo $this->data->PageURL.' <a target="_blank" href="'.JURI::root().$this->data->PageURL.'"><img src="'.JURI::root().'administrator/components/com_rsseo/assets/images/external-link.png" alt="External" border="0"></a>';
							?>
								<input type="hidden" name="PageURL" value="<?php echo $this->data->PageURL; ?>" /> 
							<?php } else { ?>
								<?php echo JURI::root(); ?> <input type="text" id="PageURL" name="PageURL" style="width:582px" /> (*)
							<?php } ?>		
						</td>
					</tr>
					<tr>
						<td width="15%">
							<label for="title">
								<?php echo JText::_( 'RSSEO_PAGE_TITLE' ).':'; ?>
							</label>
						</td>
						<td>						
							<input type="text" name="PageTitle" value="<?php echo html_entity_decode($this->data->PageTitle); ?>" style="width:700px;"/> 
						</td>
					</tr>
					<tr>
						<td width="15%">
							<label for="title">
								<?php echo JText::_( 'RSSEO_PAGE_KEYWORDS' ).':'; ?>
							</label>
						</td>
						<td>						
							<input type="text" name="PageKeywords" value="<?php echo $this->data->PageKeywords; ?>" style="width:700px;"/> 
						</td>
					</tr>
					<?php if ($this->rsseoConfig['keyword.density.enable']) { ?>
					<tr>
						<td width="15%">
							<label for="title" class="hasTip" title="<?php echo JText::_('RSSEO_KEYWORDS_FOR_DENSITY_DESC'); ?>">
								<?php echo JText::_( 'RSSEO_KEYWORDS_FOR_DENSITY' ).':'; ?>
							</label>
						</td>
						<td>						
							<input type="text" name="PageKeywordsDensity" value="<?php echo $this->data->PageKeywordsDensity; ?>" style="width:700px;"/> 
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td width="15%">
							<label for="title">
								<?php echo JText::_( 'RSSEO_PAGE_DESCRIPTION' ).':'; ?>
							</label>
						</td>
						<td>						
							<textarea name="PageDescription" cols="80" rows="4" style="width:700px;"><?php echo $this->data->PageDescription; ?></textarea> 
						</td>
					</tr>
					<tr>
						<td width="15%">
							<label for="title">
								<?php echo JText::_( 'RSSEO_PAGE_LEVEL' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php if ($this->cid != 0) { echo $this->data->PageLevel; ?>
								<input type="hidden" name="PageLevel" value="<?php echo $this->data->PageLevel ?>" />
							<?php } else { ?>
								<input type="text" name="PageLevel" style="width:700px" />
							<?php } ?>		
						</td>
					</tr>
					<tr>
						<td width="15%">
							<label for="published">
								<?php echo JText::_( 'RSSEO_PAGE_ORIGINAL' ).':'; ?>
							</label>
						</td>
						<td>						
							<input type="checkbox" name="restoreoriginal" id="restoreoriginal" value="1" />
						</td>
					</tr>
					<tr>
						<td width="15%">
							<label for="published">
								<?php echo JText::_( 'RSSEO_PAGE_STATUS' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo JHTML::_('select.booleanlist','published','class="inputbox"',$this->data->published,JText::_('RSSEO_PAGE_PUBLISHED'),JText::_('RSSEO_PAGE_UNPUBLISHED')); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<?php
	if($this->cid != 0)
	if(isset($this->params->url_sef))
	{
	if($this->data->published == 1)
	{
	?>
	
	<div id="editcell1">
		<table class="adminlist" width="100%">
		<?php
		if($this->rsseoConfig['crawler.sef']){
		?>		
			<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_URL_SEF_TITLE');?></th>
				<th>&nbsp;</th>
			</tr></thead>
			
			<?php
			/* CHECKING FOR URL SEF */
			if($this->params->url_sef == 0)
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_SEFCHECK" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_URL_SEF');?></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_URL_SEF_NO');?></td>
			</tr>
			<?php
			}
			else
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_SEFCHECK" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/check24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_URL_SEF');?></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_URL_SEF_YES');?></td>
			</tr>
			<?php
			}
			?>
			<tr><td colspan="4">&nbsp;</td></tr>
		<?php
		}
		
		
		if($this->rsseoConfig['crawler.title.duplicate'] || $this->rsseoConfig['crawler.title.length']){
		?>		
			<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE');?></th>
				<th>&nbsp;</th>
			</tr></thead>
			<?php
			if($this->rsseoConfig['crawler.title.duplicate'])
				/* CHECKING FOR DUPLICATE TITLES */
				if($this->params->duplicate_title > 1)
				{
				?>
				<tr class="row0">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_TITLE_DUPLICATE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_DUPLICATE');?></td>
					<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METATITLE_DUPLICATE_YES', ($this->params->duplicate_title-1), md5($this->data->PageTitle));?></td>
				</tr>
				<?php
				}
				else
				{
				?>
				<tr class="row0">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_TITLE_DUPLICATE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/check24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_DUPLICATE');?></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_DUPLICATE_NO');?></td>
				</tr>
				<?php
				}
			
			
			if($this->rsseoConfig['crawler.title.length'])	
				/* CHECKING FOR TITLE LENGTH */
				switch($this->params->title_length)
				{
					case 0:
					{
						//the page has no title
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_TITLE_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_LENGTH');?></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_LENGTH_0');?></td>
						</tr>
						<?php
					}break;
					
					case ($this->params->title_length > 70):
					{
						//the page has a long title
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_TITLE_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_LENGTH');?></td>
							<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METATITLE_LENGTH_LONG',$this->params->title_length);?></td>
						</tr>
						<?php				
					}break;
					
					case ($this->params->title_length < 10):
					{
						//the page has a small title
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_TITLE_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_LENGTH');?></td>
							<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METATITLE_LENGTH_SHORT',$this->params->title_length);?></td>
						</tr>
						<?php	
					}
					break;
					
					default:
					{
						//the page title is ok
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_TITLE_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/check24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METATITLE_LENGTH');?></td>
							<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METATITLE_LENGTH_OK',$this->params->title_length);?></td>
						</tr>
						<?php	
					}break;
				}
			?>
			<tr><td colspan="4">&nbsp;</td></tr>
		<?php
		}
		
		
		if($this->rsseoConfig['crawler.description.duplicate'] || $this->rsseoConfig['crawler.description.length'])
		{
		?>
			<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_METADESCRIPTION');?></th>
				<th>&nbsp;</th>
			</tr></thead>
			<?php
			if($this->rsseoConfig['crawler.description.duplicate'])
				/* CHECKING FOR DUPLICATE META DESCRIPTIONS */
				if($this->params->duplicate_desc > 1)
				{
				?>
				<tr class="row0">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DESCRIPTION_DUPLICATE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_DUPLICATE');?></td>
					<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METADESC_DUPLICATE_YES', ($this->params->duplicate_desc-1), md5($this->data->PageDescription));?></td>
				</tr>
				<?php
				}
				else
				{
				?>
				<tr class="row0">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DESCRIPTION_DUPLICATE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/check24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_DUPLICATE');?></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_DUPLICATE_NO');?></td>
				</tr>
				<?php
				}
			
			if($this->rsseoConfig['crawler.description.length'])
				/* CHECKING FOR META DESCRIPTION LENGTH */
				switch($this->params->description_length)
				{
					case 0:
					{
						//the page has no meta description
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DESCRIPTION_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_LENGTH');?></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_LENGTH_0');?></td>
						</tr>
						<?php
					}break;
					
					case ($this->params->description_length > 150):
					{
						//the page has a long description
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DESCRIPTION_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_LENGTH');?></td>
							<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METADESC_LENGTH_LONG',$this->params->description_length);?></td>
						</tr>
						<?php				
					}break;
					
					case ($this->params->description_length < 70):
					{
						//the page has a small description
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DESCRIPTION_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_LENGTH');?></td>
							<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METADESC_LENGTH_SHORT',$this->params->description_length);?></td>
						</tr>
						<?php	
					}
					break;
					
					default:
					{
						//the page meta description is ok
						?>
						<tr class="row1">
							<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DESCRIPTION_LENGTH" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
							<td><img src="components/com_rsseo/assets/images/check24.png"></td>
							<td><?php echo JText::_('RSSEO_CHECKPAGE_METADESC_LENGTH');?></td>
							<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METADESC_LENGTH_OK',$this->params->description_length);?></td>
						</tr>
						<?php	
					}break;
				}
			?>
			<tr><td colspan="4">&nbsp;</td></tr>
		<?php
		}
		
		
		
		if($this->rsseoConfig['crawler.keywords'])
		{
		?>
			<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_METAKEYWORDS');?></th>
				<th>&nbsp;</th>
			</tr></thead>
			
			<?php
			
			/* CHECKING FOR THE NUMBER OF META KEYWORDS */
			if($this->params->keywords > 10)
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_KEYWORD_COUNT" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_METAKEYWORDS');?></td>
				<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METAKEYWORDS_BIG', $this->params->keywords);?></td>
			</tr>
			<?php
			}
			else
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_KEYWORD_COUNT" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/check24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_METAKEYWORDS');?></td>
				<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_METAKEYWORDS_OK', $this->params->keywords);?></td>
			</tr>
			<?php
			}
			?>
			<tr><td colspan="4">&nbsp;</td></tr>
		<?php
		}
		
		
		if($this->rsseoConfig['crawler.headings'])
		{
		?>
			<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_HEADINGS');?></th>
				<th>&nbsp;</th>
			</tr></thead>
			
			<?php
			
			/* CHECKING FOR HEADINGS */
			if($this->params->headings <= 0)
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_HEADINGS" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_HEADINGS');?></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_HEADINGS_ERROR');?></td>
			</tr>
			<?php
			}
			else
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_HEADINGS" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/check24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_HEADINGS');?></td>
				<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_HEADINGS_OK', $this->params->headings);?></td>
			</tr>
			<?php
			}
			?>
			<tr><td colspan="4">&nbsp;</td></tr>
		<?php
		}
		
		/* Checking for internal/external lins */
		
		if($this->rsseoConfig['crawler.intext.links'])
		{
			?>
				<thead><tr>
					<th width="1%">&nbsp;</th>
					<th width="1%">&nbsp;</th>
					<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_IE_LINKS');?></th>
					<th>&nbsp;</th>
				</tr></thead>
			
				<?php
			
			/* CHECKING FOR HEADINGS */
			if(isset($this->params->links))
			if($this->params->links > 100)
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IELINKS" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_IE_LINKS');?></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_IE_LINKS_ERROR');?></td>
			</tr>
			<?php
			}
			else
			{
			?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IELINKS" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td><img src="components/com_rsseo/assets/images/check24.png"></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_IE_LINKS');?></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_IE_LINKS_OK');?></td>
			</tr>
			<?php
			}
			?>
			<tr><td colspan="4">&nbsp;</td></tr>
			
			<?php
		}
		
		if($this->rsseoConfig['crawler.images'] || $this->rsseoConfig['crawler.images.alt'] || $this->rsseoConfig['crawler.images.hw'])
		{
		?>
			<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES');?></th>
				<th>&nbsp;</th>
			</tr></thead>
			
			
			<?php
			if($this->rsseoConfig['crawler.images'])
				/* CHECKING FOR IMAGES */
				if($this->params->images > 10)
				{
				?>
				<tr class="row0">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES');?></td>
					<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_IMAGES_ERROR',$this->params->images);?></td>
				</tr>
				<?php
				}
				else
				{
				?>
				<tr class="row0">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/check24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES');?></td>
					<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_IMAGES_OK', $this->params->images);?></td>
				</tr>
				<?php
				}
			
			if($this->rsseoConfig['crawler.images.alt'])
				/* CHECKING FOR IMAGES WITHOUT ALT*/
				if($this->params->images_wo_alt > 0)
				{
				?>
				<tr class="row1">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG_ALT" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_WO_ALT');?></td>
					<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_IMAGES_WO_ALT_ERROR',$this->params->images_wo_alt);?></td>
				</tr>
				<?php
				}
				else
				{
				?>
				<tr class="row1">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG_ALT" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/check24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_WO_ALT');?></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_WO_ALT_OK');?></td>
				</tr>
				<?php
				}
			
			if($this->rsseoConfig['crawler.images.hw'])
				/* CHECKING FOR IMAGES WITHOUT WIDTH/HEIGHT*/
				if($this->params->images_wo_hw > 0)
				{
				?>
				<tr class="row1">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG_RESIZE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/nocheck24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_WO_HW');?></td>
					<td><?php echo JText::sprintf('RSSEO_CHECKPAGE_IMAGES_WO_HW_ERROR',$this->params->images_wo_hw);?></td>
				</tr>
				<?php
				}
				else
				{
				?>
				<tr class="row1">
					<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG_RESIZE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
					<td><img src="components/com_rsseo/assets/images/check24.png"></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_WO_HW');?></td>
					<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_WO_HW_OK');?></td>
				</tr>
				<?php
				}
			?>
			<tr class="row1">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_IMG_NAMES" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_NAMES');?></td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_IMAGES_NAMES_DESC');?></td>
			</tr>
			<tr><td colspan="4">&nbsp;</td></tr>
		<?php
		}
		
		
		
		if($this->rsseoConfig['keyword.density.enable'])
		{
			?>
				<thead><tr>
					<th width="1%">&nbsp;</th>
					<th width="1%">&nbsp;</th>
					<th width="20%"><?php echo JText::_('RSSEO_CHECKPAGE_KEYWORDS_DENSITY');?></th>
					<th>&nbsp;</th>
				</tr></thead>
			
			<?php if (!empty($this->densityparams)) { ?>
			<?php foreach ($this->densityparams as $keyword => $value) { ?>
			<tr class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_DENSITY" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td></td>
				<td><?php echo $keyword;?></td>
				<td><?php echo $value;?></td>
			</tr>
			<?php }} ?>
			<tr><td colspan="4">&nbsp;</td></tr>
			
			<?php
		} ?>
		<thead><tr>
				<th width="1%">&nbsp;</th>
				<th width="1%">&nbsp;</th>
				<th width="20%"><a href="javascript:void(0)" onclick="rss_doCheck(<?php echo $this->cid; ?>);"><?php echo JText::_('RSSEO_CHECKPAGE_TOTAL_PAGE');?></a> <img id="rss_img_loading" style="vertical-align:middle;display:none;" src="<?php echo JURI::root() ?>administrator/components/com_rsseo/assets/images/loader.gif" /></th>
				<th>&nbsp;</th>
		</tr></thead>
			<tr id="rss_hide1" style="display:none;" class="row0">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_PAGELOAD" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td>&nbsp;</td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_TOTAL_PAGE_DESCR');?></td>
				<td id="rss_time"></td>
			</tr>
			<tr id="rss_hide2" style="display:none;" class="row1">
				<td><a href="http://www.rsjoomla.com/index.php?option=com_rsfirewall_kb&task=redirect&code=SEO_PAGESIZE" target="_blank"><img src="components/com_rsseo/assets/images/readmore.png"></a></td>
				<td>&nbsp;</td>
				<td><?php echo JText::_('RSSEO_CHECKPAGE_PAGE_SIZE');?></td>
				<td id="rss_size"></td>
			</tr>
			<tr id="rss_hide3"><td colspan="4">&nbsp;</td></tr>
		</table>
	</div>
	
	<?php
	} else  echo JText::_('RSSEO_PUBLISH_PAGE');
	}
	else
	{
	?>
	<?php echo JText::sprintf('RSSEO_CHECKPAGE_NOT_CHECKED',$this->cid);?>
	<?php
	}
	?>
	
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="cid" value="<?php echo $this->data->IdPage; ?>" />
<input type="hidden" name="IdPage" value="<?php echo $this->data->IdPage; ?>" />
<input type="hidden" name="controller" value="pages" />
<input type="hidden" name="view" value="pages" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>