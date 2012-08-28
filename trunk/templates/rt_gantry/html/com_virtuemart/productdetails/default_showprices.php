<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_showprices.php 5834 2012-04-09 12:05:33Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<div class="product-price" id="productPrice<?php echo $this->product->virtuemart_product_id ?>">        	
	<strong>Price: </strong> <?php echo $this->currency->createPriceDiv('basePrice','basePrice' , $this->product->prices); ?>	
	<?php
	//-- select and convert currency
	jimport( 'joomla.application.module.helper' );
	$module = JModuleHelper::getModule( 'virtuemart_currencies', 'Currency Selector');
	echo JModuleHelper::renderModule( $module );
	//-- end select and convert currency	
    ?>
</div>