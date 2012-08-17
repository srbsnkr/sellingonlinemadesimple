<?php
/**
* @version 1.0.0
* @package RSSEO! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
$params = array();
$params['startOffset'] = JRequest::getInt('tabposition', 0);
$tabs =& JPane::getInstance('Tabs',$params,true);

JHTML::_('behavior.modal');
JHTML::_('behavior.mootools');
JHTML::_('behavior.switcher');
$editor=& JFactory::getEditor();
?>

<!-- tab -->
<script type="text/javascript">

<?php if (rsseoHelper::is16()) { ?>
Joomla.submitbutton = function(task) 
{
	if (task == 'apply')
	{
		var dt = $('content-pane').getElements('dt');

		for (var i=0; i<dt.length; i++)
		{
			if (dt[i].hasClass('open'))
				$('tabposition').value = i;
		}
	}
	
	Joomla.submitform(task);

}
<?php } else { ?>
function submitbutton(pressbutton)
{
	if (pressbutton == 'apply')
	{
		var dt = $('content-pane').getElements('dt');

		for (var i=0; i<dt.length; i++)
		{
			if (dt[i].className == 'open')
				$('tabposition').value = i;
		}
	}
	
	submitform(pressbutton);
}
<?php } ?>
</script>
<form action="index.php" method="post" name="adminForm">
<?php echo $tabs->startPane('content-pane'); ?>
<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_GENERAL'),"settings-general"); ?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="200">
							<label for="license">
								<?php echo JText::_( 'RSSEO_LICENSE_CODE' ); ?>
							</label>
						</td>
						<td>
							<input type="text" name="rsseoConfig[global.register.code]" value="<?php echo $this->data['global.register.code'];  ?>" size="55" maxlength="50">
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="date">
								<?php echo JText::_( 'RSSEO_DATE_FORMAT' ).':'; ?>
							</label>
						</td>
						<td>
							<input type="text" name="rsseoConfig[global.dateformat]" value="<?php echo $this->data['global.dateformat'];  ?>" size="55" maxlength="50">
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="debug">
								<?php echo JText::_( 'RSSEO_DEBUG' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.debug]','class="inputbox"',$this->data['enable.debug']); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo $tabs->endPanel(); ?>
<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_COMPETITORS'),"settings-competitors"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%" class="adminform">
	<tr>
		<td width="200">
				<?php echo JText::_( 'RSSEO_SELECT_GOOGLE_DOMAIN' ).':'; ?>
		</td>
		<td>
			<?php echo $this->lists['googleDomain'];?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_PR' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.pr]','class="inputbox"',$this->data['enable.pr']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_ALEXA' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.alexa]','class="inputbox"',$this->data['enable.alexa']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_TEHNORATI' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.tehnorati]','class="inputbox"',$this->data['enable.tehnorati']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_GOOGLEP' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.googlep]','class="inputbox"',$this->data['enable.googlep']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_YAHOOP' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.yahoop]','class="inputbox"',$this->data['enable.yahoop']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_BINGP' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.bingp]','class="inputbox"',$this->data['enable.bingp']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_GOOGLEB' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.googleb]','class="inputbox"',$this->data['enable.googleb']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_YAHOOB' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.yahoob]','class="inputbox"',$this->data['enable.yahoob']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_ENABLE_BINGB' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.bingb]','class="inputbox"',$this->data['enable.bingb']); ?>
		</td>
	</tr>
	<tr>
		<td width="200">
			<label for="debug">
				<?php echo JText::_( 'RSSEO_SEARCH_DMOZ' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist','rsseoConfig[search.dmoz]','class="inputbox"',$this->data['search.dmoz']); ?>
		</td>
	</tr>
</table>
<?php echo $tabs->endPanel(); ?>
<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_ANALYTICS'),"settings-analytics"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td valign="top">
			<table  class="adminform">
			<?php
			if (extension_loaded('curl'))
			{
			?>
				<tr>
					<td width="200">
						<label for="analyticsEnable">
							<?php echo JText::_( 'RSSEO_SETTINGS_ANALYTICS_ENABLE' ).':'; ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'rsseoConfig[analytics.enable]' , '' , $this->data['analytics.enable']);?>
					</td>
				</tr>
				<tr>
					<td width="200">
						<label for="analyticsUsername">
							<?php echo JText::_( 'RSSEO_SETTINGS_ANALYTICS_USERNAME' ).':'; ?>
						</label>
					</td>
					<td>
						<input type="text" name="rsseoConfig[analytics.username]" value="<?php echo $this->data['analytics.username'];?>" size="55" maxlength="50">
					</td>
				</tr>
				<tr>
					<td width="200">
						<label for="analyticsPassword">
							<?php echo JText::_( 'RSSEO_SETTINGS_ANALYTICS_PASSWORD' ).':'; ?>
						</label>
					</td>
					<td>
						<input type="password" name="rsseoConfig[analytics.password]" value="<?php echo $this->data['analytics.password'];?>" size="55" maxlength="50">
					</td>
				</tr>
				<?php } else { echo JText::_('RSSEO_NO_CURL'); } ?>
				<tr>
					<td width="200">
						<label for="trackingEnable">
							<?php echo JText::_( 'RSSEO_SETTINGS_ANALYTICS_ENABLE_TRACKING' ).':'; ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'rsseoConfig[ga.tracking]' , '' , $this->data['ga.tracking']);?>
					</td>
				</tr>
				<tr>
					<td width="200">
						<label for="code" class="hasTip" title="<?php echo JText::_('RSSEO_SETTINGS_ANALYTICS_TRACKING_CODE_DESC'); ?>">
							<?php echo JText::_( 'RSSEO_SETTINGS_ANALYTICS_TRACKING_CODE' ).':'; ?>
						</label>
					</td>
					<td>
						<input type="text" name="rsseoConfig[ga.code]" value="<?php echo $this->data['ga.code'];?>" size="55" maxlength="50">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php echo $tabs->endPanel(); ?>
<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_CRAWLER'),"settings-crawler"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="300">
								<?php echo JText::_( 'RSSEO_PHP_FOLDER' ).':'; ?>
						</td>
						<td>
							<input type="text" name="rsseoConfig[php.folder]" value="<?php echo $this->data['php.folder'];?>" size="30" /> 
							<br/>
							<?php echo JText::_('RSSEO_PHP_FOLDER_DESC'); ?>
						</td>
					</tr>
					<tr>
						<td width="300">
								<?php echo JText::_( 'RSSEO_SETTINGS_CRAWLER_LEVEL' ).':'; ?>
						</td>
						<td>
							<?php echo $this->lists['crawler.level'];?>
						</td>
					</tr>
					<tr>
						<td width="300">
								<?php echo JText::_( 'RSSEO_ENABLE_CRAWL_ON_NEW_PAGE' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.enable.auto]','class="inputbox"',$this->data['crawler.enable.auto']); ?>
						</td>
					</tr>
					<tr>
						<td width="300">
								<?php echo JText::_( 'RSSEO_ENABLE_SITE_NAME' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[site.name.in.title]','class="inputbox"',$this->data['site.name.in.title']); ?>
						</td>
					</tr>
					<tr>
						<td width="300">
								<?php echo JText::_( 'RSSEO_ENABLE_SITE_NAME_POSITION' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[site.name.position]','class="inputbox"',$this->data['site.name.position'],JText::_('RSSEO_SITE_NAME_POSITION_BEFORE'),JText::_('RSSEO_SITE_NAME_POSITION_AFTER')); ?>
						</td>
					</tr>
					<tr>
						<td width="300">
								<?php echo JText::_( 'RSSEO_SITE_NAME_SEPARATOR' ).':'; ?>
						</td>
						<td>
							<input type="text" name="rsseoConfig[site.name.separator]" value="<?php echo $this->data['site.name.separator'];?>" size="10" style="text-align:center;" />
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_URL_SEF' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.sef]','class="inputbox"',$this->data['crawler.sef']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_METATITLE_DUPLICATE' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.title.duplicate]','class="inputbox"',$this->data['crawler.title.duplicate']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_METATITLE_LENGTH' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.title.length]','class="inputbox"',$this->data['crawler.title.length']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_METADESC_DUPLICATE' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.description.duplicate]','class="inputbox"',$this->data['crawler.description.duplicate']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_METADESC_LENGTH' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.description.length]','class="inputbox"',$this->data['crawler.description.length']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_METAKEYWORDS' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.keywords]','class="inputbox"',$this->data['crawler.keywords']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_HEADINGS' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.headings]','class="inputbox"',$this->data['crawler.headings']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_IMAGES' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.images]','class="inputbox"',$this->data['crawler.images']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_IMAGES_WO_ALT' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.images.alt]','class="inputbox"',$this->data['crawler.images.alt']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_IMAGES_WO_HW' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.images.hw]','class="inputbox"',$this->data['crawler.images.hw']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_CHECKPAGE_NR_INTEXT_LINKS' ).':'; ?>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[crawler.intext.links]','class="inputbox"',$this->data['crawler.intext.links']); ?>
						</td>
					</tr>
					<tr>
						<td>
								<?php echo JText::_( 'RSSEO_IGNORE_LINKS' ).':'; ?>
						</td>
						<td>
							<textarea cols="70" rows="15" name="rsseoConfig[crawler.ignore]"><?php echo $this->data['crawler.ignore']; ?></textarea><br/>
							<?php echo JText::_( 'RSSEO_IGNORE_LINKS_DESC' ); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo $tabs->endPanel(); ?>
<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_REPLACEMENTS'),"settings-replacements"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="200">
							<label for="enable.keywords">
								<?php echo JText::_( 'RSSEO_ENABLE_KEYWORD_REPLACEMENT' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[enable.keyword.replace]','class="inputbox"',$this->data['enable.keyword.replace']); ?>
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="approved.characters">
								<?php echo JText::_( 'RSSEO_APPROVED_CHARACTERS' ).':'; ?>
							</label>
						</td>
						<td>
							<input type="text" name="rsseoConfig[approved.chars]" value="<?php echo $this->escape($this->data['approved.chars']);?>" size="55" />
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="componentheading">
								<?php echo JText::_( 'RSSEO_REPLACE_COMPONENTHEADING' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo $this->lists['heading1']; ?>
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="contentheading">
								<?php echo JText::_( 'RSSEO_REPLACE_CONTENTHEADING' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo $this->lists['heading2']; ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo $tabs->endPanel(); ?>

<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_SUBDOMAINS'),"settings-subdomains"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="200">
							<label for="subdomains1">
								<?php echo JText::_( 'RSSEO_SETTINGS_SUBDOMAINS' ).':'; ?>
							</label>
						</td>
						<td>
							<textarea cols="70" rows="15" name="rsseoConfig[subdomains]"><?php echo $this->data['subdomains']; ?></textarea>
							<br/>
							<?php echo JText::_('RSSEO_DOMAINS_INFO'); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo $tabs->endPanel(); ?>

<?php echo $tabs->startPanel(JText::_('cURL'),"settings-curl"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="200">
							<label for="rsseoConfig[proxy.enable]">
								<?php echo JText::_( 'RSSEO_SETTINGS_ENABLE_PROXY' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[proxy.enable]','class="inputbox"',$this->data['proxy.enable']); ?>
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="rsseoConfig[proxy.server]">
								<?php echo JText::_( 'RSSEO_SETTINGS_PROXYSERVER' ).':'; ?>
							</label>
						</td>
						<td>
							<input type="text" name="rsseoConfig[proxy.server]" value="<?php echo $this->data['proxy.server'];?>" size="55" />
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="rsseoConfig[proxy.port]">
								<?php echo JText::_( 'RSSEO_SETTINGS_PROXYPORT' ).':'; ?>
							</label>
						</td>
						<td>
							<input type="text" name="rsseoConfig[proxy.port]" value="<?php echo $this->data['proxy.port'];?>" size="55" />
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="rsseoConfig[proxy.username]">
								<?php echo JText::_( 'RSSEO_SETTINGS_PROXYUSERNAME' ).':'; ?>
							</label>
						</td>
						<td>
							<input type="text" name="rsseoConfig[proxy.username]" value="<?php echo $this->data['proxy.username'];?>" size="55" />
						</td>
					</tr>
					<tr>
						<td width="200">
							<label for="rsseoConfig[proxy.password]">
								<?php echo JText::_( 'RSSEO_SETTINGS_PROXYPASSWORD' ).':'; ?>
							</label>
						</td>
						<td>
							<input type="password" name="rsseoConfig[proxy.password]" value="<?php echo $this->data['proxy.password'];?>" size="55" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo $tabs->endPanel(); ?>
<?php echo $tabs->startPanel(JText::_('RSSEO_SETTINGS_KEYWORDS_DENSITY'),"settings-keywordsdensity"); ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<table  class="adminform">
					<tr>
						<td width="250">
							<label for="rsseoConfig[keyword.density.enable]">
								<?php echo JText::_( 'RSSEO_SETTINGS_KEYWORDS_DENSITY_ENABLE' ).':'; ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','rsseoConfig[keyword.density.enable]','class="inputbox"',$this->data['keyword.density.enable']); ?>
						</td>
					</tr>
					<tr>
						<td width="250">
							<label for="copykeywords" class="hasTip" title="<?php echo JText::_( 'RSSEO_SETTINGS_KEYWORDS_DENSITY_COPY_DESC' ); ?>">
								<?php echo JText::_( 'RSSEO_SETTINGS_KEYWORDS_DENSITY_COPY' ); ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist','copykeywords','class="inputbox"',0); ?>
						</td>
					</tr>
					<tr>
						<td width="250">
							<label for="overwritekeywords" class="hasTip" title="<?php echo JText::_( 'RSSEO_SETTINGS_KEYWORDS_DENSITY_OVERWRITE_DESC' ); ?>">
								<?php echo JText::_( 'RSSEO_SETTINGS_KEYWORDS_DENSITY_OVERWRITE' ); ?>
							</label>
						</td>
						<td>
							<input type="checkbox" name="overwritekeywords" id="overwritekeywords" value="1" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php echo $tabs->endPanel(); ?>
<?php echo $tabs->endPane(); ?>
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="task" value="editSettings" />
<input type="hidden" name="option" value="com_rsseo" />
<input type="hidden" name="view" value="settings" />
<input type="hidden" name="controller" value="settings" />
<input type="hidden" name="tabposition" value="0" id="tabposition" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>