<?php defined('_JEXEC') or die('Restricted access');
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

JHTML::stylesheet('style.css', VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/', false);
JHTML::script('klarna_pp.js', VMKLARNAPLUGINWEBASSETS.'/js/', false);
JHTML::script('klarnapart.js', 'https://static.klarna.com:444/external/js/', false);
$document = JFactory::getDocument();
$document->addScriptDeclaration("

jQuery(function(){
	jQuery('.klarna_PPBox_bottomMid_readMore a').click( function(){
		InitKlarnaPartPaymentElements('klarna_partpayment', '". $viewData['eid'] ."', '". $viewData['country'] ."');
		ShowKlarnaPartPaymentPopup();
		return false;
	});
});
");
$js = '<script type="text/javascript">jQuery(document).find(".product_price").width("25%");</script>';
$js .= '<style>';
$js .= 'div.klarna_PPBox{z-index: 200 !important;}';
$js .= 'div.cbContainer{z-index: 10000 !important;}';
$js .= 'div.klarna_PPBox_bottomMid{overflow: visible !important;}';
$js .= '</style>';
//$html .= '<br>';
if ($viewData['country'] == 'nl') {
	$js .= '<style>.klarna_PPBox_topMid{width: 81%;}</style>';
}
$document = JFactory::getDocument();
//$document->addScriptDeclaration($js);
?>

<?php
if ($viewData['country']== "nl") {
	$country_width="klarna_PPBox_topMid_nl";
} else {
	$country_width="";
}
?>

<div class="klarna_PPBox">
    <div id="klarna_partpayment" style="display: none"></div>
    <div class="klarna_PPBox_inner">
        <div class="klarna_PPBox_top">
            <span class="klarna_PPBox_topRight"></span>
            <span class="klarna_PPBox_topMid  <?php echo $country_width ?>">
                <p><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_FROMTEXT'); ?><label> <?php echo $viewData['defaultMonth'] ?> </label><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_MONTHTEXT'); ?><?php echo $viewData['asterisk']; ?></p>
            </span>
            <span class="klarna_PPBox_topLeft"></span>
        </div>
        <div class="klarna_PPBox_bottom">
            <div class="klarna_PPBox_bottomMid">
                <table cellpadding="0" cellspacing="0" width="100%" border="0">
                    <thead>
                        <tr>
                            <th style="text-align: left"><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_TH_MONTH'); ?></th>
                            <th style="text-align: right"><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_TH_SUM'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
						<?php foreach ($viewData['monthTable'] as $monthTable) { ?>
							<tr>
								<td style = 'text-align: left' >
								<?php echo $monthTable['pp_title'] ?>
								</td>
								<td class='klarna_PPBox_pricetag' >
								<?php echo   $monthTable['pp_price']   ?>
								</td>
							</tr>
						<?php } ?>
                    </tbody>
                </table>
                <div class="klarna_PPBox_bottomMid_readMore">
                    <a href="#"><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_READMORE'); ?></a>
                </div>
                <div class="klarna_PPBox_pull" id="klarna_PPBox_pullUp">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/productPrice/default/pullUp.png" alt="More info" />
                </div>
            </div>
        </div>
        <div class="klarna_PPBox_pull" id="klarna_PPBox_pullDown">
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/productPrice/default/pullDown.png" alt="More info" />
        </div>
        <?php
	$notice = (($viewData['country']  == 'nl') ? '<div class="nlBanner"><img src="' . VMKLARNAPLUGINWEBASSETS . '/images/account/notice_nl.jpg" /></div>' : "");
	echo $notice;
	 ?>
    </div>
</div>
<div style="clear: both; height: 80px;"></div>
