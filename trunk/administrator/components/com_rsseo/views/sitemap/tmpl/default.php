<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php echo $this->tabs->startPane('content-pane'); ?>
<?php echo $this->tabs->startPanel(JText::_('RSSEO_SITEMAP_XML'),"sitemap-xml"); ?>
	<?php if (!$this->pages) echo '<h4 style="text-align:center;">'.JText::_('RSSEO_SITEMAP_NO_PAGES').'</h4>'; ?>
	<?php if ($this->sitemaps) { ?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table class="adminform">
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_SITEMAP_SCHEME' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo $this->lists['scheme']; ?>
							
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_SITEMAP_FREQUENCY' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo $this->lists['frequency']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_SITEMAP_MODIFICATION' ).':'; ?>
							</label>
						</td>
						<td>						
							<input name="SitemapModification" id="SitemapModification" value="<?php echo date('Y-m-d'); ?>" size="50"/>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_SITEMAP_PRIORITY' ).':'; ?>
							</label>
						</td>
						<td>						
							<?php echo $this->lists['priority']; ?>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							
								<table class="toolbar">
									<tbody>
									<tr>
										<td style="text-align:center;">
											<a class="toolbar" style="text-decoration:none;" onclick="javascript: sitemapGenerate($('SitemapFrequency').value,$('SitemapModification').value,$('SitemapPriority').value,'new',$('protocol').value);return false;" href="#">
												<img alt="<?php echo JText::_('RSSEO_SITEMAP_NEW');?>" src="<?php echo JURI::root();?>administrator/components/com_rsseo/assets/images/sitemap-new.png" />
												<br/>
												<?php echo JText::_('RSSEO_SITEMAP_NEW');?>
											</a>
										</td>
										<td style="text-align:center;">
											<a class="toolbar" style="text-decoration:none;" onclick="javascript: sitemapGenerate($('SitemapFrequency').value,$('SitemapModification').value,$('SitemapPriority').value,'new',$('protocol').value);return false;" href="#">
												<img alt="<?php echo JText::_('RSSEO_SITEMAP_CONTINUE');?>" src="<?php echo JURI::root();?>administrator/components/com_rsseo/assets/images/sitemap-play.png" />
												<br/>
											<?php echo JText::_('RSSEO_SITEMAP_CONTINUE');?>
											</a>
										</td>
										<td style="text-align:center;">
											<a class="toolbar" style="text-decoration:none;" href="index.php?option=com_rsseo&task=sitemap">
												<img alt="<?php echo JText::_('RSSEO_SITEMAP_PAUSE');?>" src="<?php echo JURI::root();?>administrator/components/com_rsseo/assets/images/sitemap-pause.png" />
												<br/>
												<?php echo JText::_('RSSEO_SITEMAP_PAUSE');?>
											</a>
										</td>
									</tr>
									</tbody>
								</table>
						</td>
					</tr>
				</table>
				<div id="sitemap"><div class="sitemapContainer"><div class="sitemapProgress" style="width:<?php echo $this->progress;?>%"><?php echo JText::_('RSSEO_SITEMAP_PROGRESS');?> <?php echo $this->progress;?>%</div></div></div>
			</td>
		</tr>
	</table>
	
	<?php } else echo '<h3 style="text-align:center;">'.JText::sprintf('RSSEO_SITEMAP_NOT_EXIST',JPATH_SITE).'</h3>'; ?>
<?php echo $this->tabs->endPanel(); ?>
<?php echo $this->tabs->startPanel(JText::_('RSSEO_SITEMAP_HTML'),"sitemap-html"); ?>
	<table class="adminform">
		<tr>
			<td valign="top" style="width:50%">
				<div class="rsseo_info"><?php echo JText::_('RSSEO_SITEMAP_HTML_INFO'); ?></div>
				<?php
					if (!empty($this->data)) 
						foreach ($this->data as $menu)
						{
							$checked = in_array($menu->menutype,$this->selected) ? 'checked="checked"' : '';
							echo '<input type="checkbox" '.$checked.' name="menus[]" id="'.$menu->menutype.'" value="'.$menu->menutype.'" /> <label for="'.$menu->menutype.'">'.$menu->title.'</label><br />';
						}

				?>
			</td>
			<td valign="top" style="width:50%">
				<div class="rsseo_info"><?php echo JText::_('RSSEO_SITEMAP_HTML_EXCLUDE_INFO'); ?></div>
				<?php echo JHTML::_('select.genericlist', JHTML::_('menu.linkoptions'), 'excludes[]', 'class="inputbox" style="width:25%" size="10" multiple="multiple"', 'value', 'text',$this->excludes); ?>
			</td>
		</tr>
	</table>
<button type="button" onclick="submitbutton('save')"><?php echo JText::_('RSSEO_GENERATE_SITEMAP'); ?></button>
<?php echo $this->tabs->endPanel(); ?>
<?php echo $this->tabs->endPane(); ?>


<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="controller" value="sitemap" />
<input type="hidden" name="view" value="sitemap" />
<input type="hidden" name="task" value="" />
</form>


<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>