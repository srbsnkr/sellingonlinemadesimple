<?php // no direct access
defined('_JEXEC') or die('Restricted access');
?>

<?php $db =& JFactory::getDBO(); ?>
<?php $i = 0; ?>
<ul id="vm-mn-category">
<?php foreach ($categories as $category) {	
	$query = "SELECT file_url,file_url_thumb FROM #__virtuemart_medias WHERE virtuemart_media_id = ".$category->virtuemart_media_id[0]." LIMIT 0,1";
	$db->setQuery($query);
	$category_thumb = $db->loadObject();	
	$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id);
	$cattext = $category->category_name;		
	$category_description = $category->category_description;
	$class_menu = ($i++%3==0)?"last":"";
?>
	<li>
		<div class="category_item <?php echo $class_menu; ?>">
			<div class="img-categori">
				<img class="category_thumb" src="<?php echo $category_thumb->file_url; ?>" alt="<?php echo $cattext; ?>">
			</div>
			<span class="title_item"><?php echo JHTML::link($caturl, $cattext);?></span>
			<?php echo $category->category_description; ?>
		</div>
	</li>
<?php } ?>
</ul>
