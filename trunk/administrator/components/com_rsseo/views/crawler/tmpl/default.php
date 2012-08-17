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
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="200">
							<label for="title">
								<?php echo JText::_( 'RSSEO_CRAWLER_URL' ).':'; ?>
							</label>
						</td>
						<td>						
							<span id="PageURL"></span>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_CRAWLER_LEVEL' ).':'; ?>
							</label>
						</td>
						<td>						
							<span id="PageLevel"></span>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_CRAWLER_PAGES_SCANED' ).':'; ?>
							</label>
						</td>
						<td>						
							<span id="PagesScanned"></span>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_CRAWLER_PAGES_LEFT' ).':'; ?>
							</label>
						</td>
						<td>						
							<span id="PagesLeft"></span>
						</td>
					</tr>
					<tr>
						<td>
							<label for="title">
								<?php echo JText::_( 'RSSEO_CRAWLER_PAGES_TOTAL' ).':'; ?>
							</label>
						</td>
						<td>						
							<span id="TotalPages"></span>
						</td>
					</tr>					
					<tr>
						<td colspan="2">
							
								<table class="toolbar">
									<tbody>
									<tr>
										<td style="text-align:center;">
											<a class="toolbar" style="text-decoration:none;" onclick="javascript:document.getElementById('CrawlerPaused').value = 'active'; crawl(1,0);" href="#">
												<img src="<?php echo JURI::root(); ?>administrator/components/com_rsseo/assets/images/sitemap-new.png" alt="<?php echo JText::_('RSSEO_CRAWLER_NEW');?>" />
												<br />
												<?php echo JText::_('RSSEO_CRAWLER_NEW');?>
											</a>
										</td>
										<td style="text-align:center;">
											<a class="toolbar" style="text-decoration:none;" onclick="javascript: document.getElementById('CrawlerPaused').value = 'active';crawl(0,0);" href="#">
												<img alt="<?php echo JText::_('RSSEO_CRAWLER_CONTINUE');?>" src="<?php echo JURI::root();?>administrator/components/com_rsseo/assets/images/sitemap-play.png" />
												<br />
											<?php echo JText::_('RSSEO_CRAWLER_CONTINUE');?>
											</a>
										</td>
										<td style="text-align:center;">
											<a class="toolbar" style="text-decoration:none;" href="#" onclick="document.getElementById('CrawlerPaused').value = 'paused'">
												<img alt="<?php echo JText::_('RSSEO_CRAWLER_PAUSE');?>" src="<?php echo JURI::root();?>administrator/components/com_rsseo/assets/images/sitemap-pause.png" />
												<br />
												<?php echo JText::_('RSSEO_CRAWLER_PAUSE');?>
												<input type="hidden" name="CrawlerPaused" id="CrawlerPaused" value="active"/>
											</a>
										</td>
									</tr>
									</tbody>
								</table>
						</td>
					</tr>
				</table>
				
			</td>
		</tr>
	</table>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="controller" value="crawler" />
<input type="hidden" name="view" value="crawler" />
<input type="hidden" name="task" value="" />
</form>


<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>