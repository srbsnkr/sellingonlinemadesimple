<?php  defined('_JEXEC') or die();
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
<style type="text/css">
#klarna_invno {
    float:left; font-weight: bold; font-size: 13px;
}
#klarna_invno_text {
    width: 50%; float: left;
}
.clear {
    clear: both;
}
.klarna_info {
    float: left; left: -2px; position: relative; text-align: left; width: 99.8%;
}
.klarna_tulip {
    float: left; padding-right: 10px;
}
</style>
    <div class="klarna_info">
        <span class="sectiontableheader klarna_info">
	   <?php echo $viewData['payment_name']; ?>
	</span>
        <span id="klarna_invno_wrapper">
            <span id="klarna_invno_text"><?php echo JText::sprintf('VMPAYMENT_KLARNA_INVOICE_NUMBER_TEXT'); ?></span>
            <span id="klarna_invno"><?php echo  $viewData['klarna_invoiceno']; ?></span>
        </span>

    </div>

    <div class="clear"></div>


