<?php
defined('_JEXEC') or die();
/**
 * @version $Id$
 *
 * @author Valérie Isaksen
 * @package VirtueMart

 * @copyright Copyright (C) iStraxx - All rights reserved.
 * @license istraxx_license.txt Proprietary License. This code belongs to istraxx UG (haftungsbeschränkt)
 * You are not allowed to distribute or sell this code.
 * You are not allowed to modify this code
 */
?>

<?php
$logo = '<img src="' . JURI::base() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo' . $viewData['logo'] . '"/>';
?>


<div class="klarna_info">
    <span style="">
	<a href="http://www.klarna.com/"><?php echo $logo ?></a><br /><?php echo $viewData['text'] ?>
    </span>
</div>

<div class="clear"></div>
<span class="payment_name"><?php echo $viewData['payment_name'] ?> </span>
<?php
if (!empty($description)) {
?>
 <span class="payment_description"><?php echo $viewData['payment_description'] ?> . '</span>
	 <?php
}

?>

