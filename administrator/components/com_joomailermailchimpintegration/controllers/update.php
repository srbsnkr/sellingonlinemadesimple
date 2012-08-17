<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This file is based on AdminTools' update.php from Nicholas K. Dionysopoulos
 * @copyright Copyright (c)2010 Nicholas K. Dionysopoulos
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

class joomailermailchimpintegrationsControllerUpdate extends joomailermailchimpintegrationsController
{
    function display()
    {
	parent::display();
    }

    function update()
    {
	// Make sure there are updates available
	$model =& $this->getModel('Update','joomailermailchimpintegrationsModel');
	$updates =& $model->getUpdates(false);
	if(!$updates->update_available)
	{   
	    $url = JURI::base().'index.php?option=com_joomailermailchimpintegration';
	    $msg = JText::_('JM_ERR_UPDATE_NOUPDATES');
	    $this->setRedirect($url, $msg, 'error');
	    $this->redirect();
	    return;
	}

	// Download the package
	$package = $updates->package_url.$updates->package_url_suffix;

	$updater = $this->getModel('Update','joomailermailchimpintegrationsModel');
	$config  =& JFactory::getConfig();
	$target  = $config->getValue('config.tmp_path').DS.'joomailermailchimpintegration_update.zip';
	$result  = $updater->downloadPackage($package, $target);

	if($result === false)
	{
	    $url = JURI::base().'index.php?option=com_joomailermailchimpintegration';
	    $msg = JText::_('JM_ERR_UPDATE_CANTDOWNLOAD');
	    $this->setRedirect($url, $msg, 'error');
	    $this->redirect();
	    return;
	}

	// Extract the package
	jimport('joomla.installer.helper');
	$package = $config->getValue('config.tmp_path').DS.$result;
	$result = JInstallerHelper::unpack($package);

	if($result === false)
	{
	    $url = JURI::base().'index.php?option=com_joomailermailchimpintegration';
	    $msg = JText::_('JM_ERR_UPDATE_CANTEXTRACT');
	    $this->setRedirect($url, $msg, 'error');
	    $this->redirect();
	    return;
	}

	// Package extracted; run the installer
	$tempdir = $result['dir'];
	@ob_end_clean();
?>
<html>
<head>
</head>
<body>
    <form action="<?php echo (version_compare(JVERSION,'1.6.0','ge')) ? JURI::base().'index.php?option=com_installer&amp;view=install' : 'index.php';?>" method="post" name="frm" id="frm">
    <?php if( ! version_compare(JVERSION,'1.6.0','ge') ){ ?>
    <input type="hidden" name="option" value="com_installer" />
    <?php } ?>
    <input type="hidden" name="task" value="<?php echo (version_compare(JVERSION,'1.6.0','ge')) ? 'install.install' : 'doInstall';?>" />
    <input type="hidden" name="installtype" value="folder" />
    <input type="hidden" name="install_directory" value="<?php echo htmlspecialchars($tempdir) ?>" />
    <input type="hidden" name="<?php echo JUtility::getToken() ?>" value="1" />
</form>
<script type="text/javascript">
document.frm.submit();
</script>
</body>
<html>
<?php
	die();
    }
}