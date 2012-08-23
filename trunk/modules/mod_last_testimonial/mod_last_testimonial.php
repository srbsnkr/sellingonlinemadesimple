<?php	    
    defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<?php
	$db =& JFactory::getDBO();	
	$query = "SELECT * FROM #__testimonials WHERE approved = 1 ORDER BY id DESC LIMIT 0,1";
	$db->setQuery($query);
	$ob = $db->loadObject();
	
	//var_dump($ob);
	$desc = $ob->message_summary;
	$fullname = $ob->fullName;
	$aboutauthor = $ob->aboutauthor;
?>
<div class="custom">
	<div class="desc">
<span class="quote">"</span><p class="blockquote"> <?php echo $desc; ?> <span class="quote inset">"</span>
- <?php echo $fullname; ?> (<?php echo $aboutauthor; ?>)</p>
</div>
<a href="index.php?option=com_eztestimonial&view=eztestimonials" class="more">Read more</a></div>