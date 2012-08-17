<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

$modifyRegister = JRequest::getVar('modify_register');
if(!empty($modifyRegister))
	$globalRegisterCode = '';
else 
	$globalRegisterCode = $this->rsseoConfig['global.register.code'];
JHTML::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_rsseo'); ?>" method="post" name="adminForm">
<table width="100%">
<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td valign="top">
					<table class="adminlist">
						<tr>
							<td>
								<div id="cpanel">
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_LIST_COMPETITORS'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=listcompetitors">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/competitors.png', JText::_('RSSEO_LIST_COMPETITORS')); ?>
											<span><?php echo JText::_('RSSEO_LIST_COMPETITORS'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_LIST_PAGES'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=listpages">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/pages.png', JText::_('RSSEO_LIST_PAGES')); ?>
											<span><?php echo JText::_('RSSEO_LIST_PAGES'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_LIST_REDIRECTS'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=listredirects">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/redirects.png', JText::_('RSSEO_LIST_REDIRECTS')); ?>
											<span><?php echo JText::_('RSSEO_LIST_REDIRECTS'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_SITEMAP'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=sitemap">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/sitemap.png', JText::_('RSSEO_SITEMAP')); ?>
											<span><?php echo JText::_('RSSEO_SITEMAP'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_CRAWLER'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=crawler">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/crawler.png', JText::_('RSSEO_CRAWLER')); ?>
											<span><?php echo JText::_('RSSEO_CRAWLER'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_LIST_KEYWORDS'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=listkeywords">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/keywords.png', JText::_('RSSEO_LIST_KEYWORDS')); ?>
											<span><?php echo JText::_('RSSEO_LIST_KEYWORDS'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_BACKUP_RESTORE'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=backuprestore">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/backup.png', JText::_('RSSEO_BACKUP_RESTORE')); ?>
											<span><?php echo JText::_('RSSEO_BACKUP_RESTORE'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_GOOGLE_ANALYTICS'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=analytics">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/googlea.png', JText::_('RSSEO_GOOGLE_ANALYTICS')); ?>
											<span><?php echo JText::_('RSSEO_GOOGLE_ANALYTICS'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_SETTINGS'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=editsettings">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/settings.png', JText::_('RSSEO_SETTINGS')); ?>
											<span><?php echo JText::_('RSSEO_SETTINGS'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_UPDATE'); ?>">
										<a href="index.php?option=com_rsseo&amp;task=update">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/update.png', JText::_('RSSEO_UPDATE')); ?>
											<span><?php echo JText::_('RSSEO_UPDATE'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
									<div class="icon hasTip" title="<?php echo JText::_('RSSEO_FEEDBACK'); ?>">
										<a href="http://www.rsjoomla.com/suggest-extensions/single-category/5-rsseo.html" target="_blank">
											<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/feedback.png', JText::_('RSSEO_FEEDBACK')); ?>
											<span><?php echo JText::_('RSSEO_FEEDBACK'); ?></span>
										</a>
									</div>
								</div>
								<div style="float: left">
								<div class="icon hasTip" title="<?php echo JText::_('RSSEO_SUPPORT'); ?>">
									<a href="http://www.rsjoomla.com/customer-support/tickets.html" target="_blank">
										<?php echo JHTML::_('image', 'administrator/components/com_rsseo/assets/images/support.png', JText::_('RSSEO_SUPPORT')); ?>
										<span><?php echo JText::_('RSSEO_SUPPORT'); ?></span>
									</a>
								</div>
								</div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
	<td width="50%" valign="top" align="center">
		<table border="1" width="100%" class="thisform">
			<tr class="thisform">
		        <th class="cpanel" colspan="2"><?php echo RSSEO_PRODUCT . RSSEO_VERSION.' rev '.RSSEO_REVISION;?></th></td>
		    </tr>
		    <tr class="thisform">
				<td bgcolor="#FFFFFF" colspan="2">
					<br />
					<div style="width=100%" align="center">
					<img src="<?php echo JURI::root(); ?>administrator/components/com_rsseo/assets/images/rsseo.jpg" align="middle" alt="RSEO! Logo"/>
					<br /><br /></div>
				</td>
			</tr>
		    <tr class="thisform">
		        <td width="120" bgcolor="#FFFFFF"><?php echo JText::_( 'RSSEO_INSTALLED_VERSION_LABEL' ); ?></td>
		        <td bgcolor="#FFFFFF"><?php echo RSSEO_VERSION;?></td>
		    </tr>
		    <tr class="thisform">
		        <td bgcolor="#FFFFFF"><?php echo JText::_( 'RSSEO_COPYRIGHT_LABEL' ); ?></td>
		        <td bgcolor="#FFFFFF"><?php echo RSSEO_COPYRIGHT;?></td>
		    </tr>
		    <tr class="thisform">
		        <td bgcolor="#FFFFFF"><?php echo JText::_( 'RSSEO_LICENSE_LABEL' ); ?></td>
		        <td bgcolor="#FFFFFF"><?php echo RSSEO_LICENSE;?></td>
		    </tr>
		    <tr class="thisform">
		        <td valign="top" bgcolor="#FFFFFF"><?php echo JText::_( 'RSSEO_AUTHOR_LABEL' ); ?></td>
		        <td bgcolor="#FFFFFF"><?php echo RSSEO_AUTHOR;?></td>
		    </tr>	 
			<tr class="<?php echo ($globalRegisterCode=='') ? 'thisformError':'thisformOk';?>">
				<td valign="top"><?php echo JText::_( 'RSSEO_LICENSE_CODE' ); ?></td>
				<td><?php echo ($globalRegisterCode=='') ? '<input type="text" name="rsseoConfig[global.register.code]" value=""/>':$globalRegisterCode;?></td>
		    </tr>
		    <tr class="<?php echo ($globalRegisterCode=='') ? 'thisformError':'thisformOk';?>">
				<td valign="top">&nbsp;</td>
				<td>
					<?php if($globalRegisterCode!='') { ?>
						<input type="submit" name="modify_register" value="<?php echo JText::_( 'RSSEO_MODIFY_LICENSE' ); ?>" /><br/>
						<?php } else { ?>
						<input type="button" name="register" value="<?php echo JText::_( 'RSSEO_UPDATE_BUTTON' ); ?>" onclick="javascript:submitbutton('saveRegistration');"/>
						<?php } ?>
				</td>
		    </tr>
		</table>
		<p align="center"><a style="background: url(&quot;<?php echo JURI::root(); ?>administrator/components/com_rsseo/assets/images/joomla-security-w.gif&quot;) no-repeat scroll 0% 0% transparent; display: block; width: 56px; height: 12px; text-decoration: none; margin: 0pt auto; padding-top: 28px; padding-left: 59px; font-size: 10px; color: rgb(255, 255, 255);" href="http://www.rsjoomla.com/joomla-components/joomla-security.html" target="_blank"><?php echo date('d.m.Y'); ?></a></p>
	</td>
</tr>
</table>
	<input type="hidden" name="task" value="" />
</form>

