<?php  defined ('_JEXEC') or die();
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
<fieldset>

	<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tbody>
		<tr>
			<td >
				<input class="klarnaPayment" data-stype="<?php echo $viewData['stype'] ?>" id="<?php echo $viewData['id'] ?>" type="radio"
				       name="virtuemart_paymentmethod_id" value="<?php echo  $viewData['virtuemart_paymentmethod_id'] ?>" <?php echo  $viewData['selected'] ?> />
				<input value="<?php echo $viewData['id'] ?>" type="hidden" name="klarna_paymentmethod"/>
				<label for="<?php echo $viewData['id']?>">
					<?php echo $viewData['module'] ?>
				</label>
				<br/>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo  $viewData['klarna_form']  ?>
			<td>
		</tr>
		</tbody>
	</table>
</fieldset>
<?php
// preventing 2 x load javascript
static $loadjavascript;
if ($loadjavascript) {
	return TRUE;
}
$loadjavascript = TRUE;
$html_js = '<script type="text/javascript">
            setTimeout(\'jQuery(":radio[value=' . $viewData['klarna_paymentmethod'] . ']").click();\', 200);
        </script>';
?>

