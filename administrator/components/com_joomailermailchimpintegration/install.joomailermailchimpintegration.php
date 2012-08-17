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
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.filesystem.folder' );

function com_install()
{
    $message = '<p>Please select if you want to Install or Upgrade the Newsletter component:<ul><li>Click Install for a fresh Installation. If you click on Install and some previous version is installed on your system, all data stored in the database will be lost.</li><li>If you click on Upgrade, previous data stored in the database will be maintained.</li></ul></p>';?>
    <div style="padding:20px;border:1px solid #616161;background:#fff">
	<table>
	    <tr>
		<td align="left">
		    <?php echo $message; ?>
		</td>
		<td align="right">
		    <img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/logo.png" alt="joomlamailer" title="joomlamailer" />
		</td>
	    </tr>
	</table>
	<div style="clear:both; height:0;">&nbsp;</div>
	<div style="text-align:center"><center><table border="0" cellpadding="20" cellspacing="20">
	    <tr>
		<td align="center" valign="middle">
		    <a href="index.php?option=com_joomailermailchimpintegration&amp;controller=joomailermailchimpintegrationinstall&amp;task=install">
		    <img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/install.png" alt="Install" title="Install" />
		    </a>
		</td>
		<td align="center" valign="middle">
		    <a href="index.php?option=com_joomailermailchimpintegration&amp;controller=joomailermailchimpintegrationinstall&amp;task=upgrade">
		    <img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/upgrade.png" alt="Upgrade" title="Upgrade" />
		    </a>
		</td>
	    </tr>
	</table></center></div>
<?php
}// function
