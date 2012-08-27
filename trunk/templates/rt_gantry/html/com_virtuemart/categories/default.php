<?php
/**
*
* Show the products in a category
*
* @package	VirtueMart
* @subpackage
* @author RolandD
* @author Max Milbers
* @todo add pagination
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
 * @version $Id: default.php 6104 2012-06-13 14:15:29Z alatak $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if ($this->category->haschildren) {

// Category and Columns Counter
$iCol = 1;
$iCategory = 1;

// Calculating Categories Per Row
$categories_per_row = VmConfig::get ( 'categories_per_row', 3 );
$category_cellwidth = ' width'.floor ( 100 / $categories_per_row );

// Separator
$verticalseparator = " vertical-separator";
?>

<h2 class="title" style="visibility: visible; ">SHOP</h2>
<div class="image-left-article">
	<img src="/sellingonlinemadesimple/images/image-shop.png" alt="">
</div>
<div class="view-category-shop">
	<div id="head_category_shop">
		<div class="head-cate left">
			<div class="wr-head-cate">
				<div class="footwear text">
					<p class="percent">80%</p>
					<p class="type">FOOTWEAR</p>
				</div>
				<span class="sepa">/</span>
				<div class="acessories text">
					<p class="percent">20%</p>
					<p class="type">FOOTWEAR</p>
				</div>
			</div>
		</div>
		<div class="head-cate right">
			<div class="wr-head-cate">
				<p>Only the finest quality materials are used in the construction, high grades in leathers, suede and sheepskin.</p>
			</div>
		</div>
	</div>
	<div class="category-view">

	<?php // Start the Output
	if ($this->category->children ) {
		foreach ( $this->category->children as $category ) {

			// Show the horizontal seperator
			if ($iCol == 1 && $iCategory > $categories_per_row) { ?>
			
			<?php }

			// this is an indicator wether a row needs to be opened or not
			if ($iCol == 1) { ?>
			<div class="row">
			<?php }

			// Show the vertical separator
			if ($iCategory == $categories_per_row or $iCategory % $categories_per_row == 0) {
				$show_vertical_separator = ' ';
			} else {
				$show_vertical_separator = $verticalseparator;
			}

			// Category Link
			$caturl = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id );

				// Show Category ?>
				<div class="category floatleft<?php echo $category_cellwidth . $show_vertical_separator ?>">
					<div class="spacer">
						<div class="image_thumb">						
							<?php #var_dump($category); ?>
							<?php# echo $category->images[0]->displayMediaThumb("",true); ?>	
							<img src="<?php echo $category->images[0]->file_url; ?>" alt="<?php echo $category->category_name; ?>">
						</div>
						<div class="info_category">
						<h2>
							<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>"><?php echo $category->category_name ?>
							<br /></a>
						</h2>
						<?php echo $category->category_description; ?>
						</div>
					</div>
				</div>
			<?php
			$iCategory ++;

			// Do we need to close the current row now?
			if ($iCol == $categories_per_row) { ?>
			<div class="clear"></div>
			</div>
				<?php
				$iCol = 1;
			} else {
				$iCol ++;
			}
		}
	}
// Do we need a final closing row tag?
if ($iCol != 1) { ?>
	<div class="clear"></div>
	</div>

<?php
}
?>
</div>
</div>
<?php } ?>