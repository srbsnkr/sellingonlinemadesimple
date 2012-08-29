<?php

/**

* @package 		ezTestimonial Component

* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.

* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php

* @author		Saran Chamling (saaraan@gmail.com)

*/ 



// No direct access

defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.application.application' );

$app 				=& JFactory::getApplication();

$document 			=& JFactory::getDocument();

$user 				=& JFactory::getUser();

$myparams 			= &JComponentHelper::getParams('com_eztestimonial');

$styletype 			= $myparams->getValue('data.params.styletype','style.css');

$imageSubFolder 	= $myparams->getValue('data.params.imagefolder');

$hideaddbutton 		= $myparams->getValue('data.params.hideaddbutton',0);
$disableImg 		= $myparams->getValue('data.params.disableImg',0);


JHtml::_('behavior.framework', true);

JHTML::_('script','system/modal.js', true, true);	

JHTML::_('stylesheet','system/modal.css', array(), true);

$modalbox 	= 'window.addEvent(\'domready\', function() {SqueezeBox.initialize({});SqueezeBox.assign($$(\'a.modal\'), {parse: \'rel\'});});';

$document->addScriptDeclaration($modalbox);

$document->addStyleSheet('components/com_eztestimonial/assets/css/'.$styletype);



$listOrder   	= $this->state->get('list.ordering');

$listDirn   	= $this->state->get('list.direction');



$formlink 		= JRoute::_('index.php?option=com_eztestimonial&view=form');

if(!$this->items)

{

	if(strlen($this->lists['search'])>1)

	{

	$notfoundtxt = JText::sprintf(JText::_('COM_TESTIMONIALS_NORESULTFOUND'),htmlspecialchars($this->lists['search']));

	$app->enqueueMessage($notfoundtxt, 'error');

	}else{

	$app->enqueueMessage(JText::_('COM_TESTIMONIALS_BEFIRST'), 'message');

	}

}


if($hideaddbutton==0)

{

?>

<h2 class="title" style="visibility: visible; ">
	<span>Testimonials</span>
</h2>

<div align="right" style="display:none;">

<div class="addbutton">

<a href ="<?php echo $formlink; ?>"><?php echo JText::_('COM_TESTIMONIALS_ADDTESTIMONIAL'); ?></a>

</div>

</div>

<?php

}

?>

<ul id="monialLists">

<?php

$k = 0;

for ($i=0, $n=count( $this->items ); $i < $n; $i++)

{		

	$row =& $this->items[$i];

	$addeddate  = strtotime($row->added_date );

	$addedDate = date("d M Y", $addeddate); 

	$addedTime = date("H:m a", $addeddate); 

	

	$ImgUrl = JRoute::_(JURI::base().'images/'.$imageSubFolder.'/'); //user selected image folder

	$defaultImg = JRoute::_('components/com_eztestimonial/assets/images/default_user.jpg'); // default image url

	$stars = '';

	$estars = '';

	$ratedstars = round($row->rating);

	$emptystars = 5-$ratedstars;

	for ($r=1; $r<=$ratedstars; $r++)

	  {

	  $stars .= '<img src="'.JRoute::_('components/com_eztestimonial/assets/images/star_full.png').'" border="0" />'; 

	  }

	for ($er=1; $er<=$emptystars; $er++)

	  {

	  $estars .= '<img src="'.JRoute::_('components/com_eztestimonial/assets/images/star_empty.png').'" border="0" />'; 

	  }

	$ImageThumbUrl=(strlen($row->image_name)>0)?$ImgUrl.'thumb_'.$row->image_name:$defaultImg; //image path to thumbnail

	$ImageUrl=(strlen($row->image_name)>0)?$ImgUrl.$row->image_name:$defaultImg; //image path to large image

	$imageLink = (strlen($row->image_name)>0)?'<a href="'.$ImageUrl.'" class="modal" ><img src="'.$ImageUrl.'" border="0" width="82" height="110" /></a>':'<img src="'.$ImageThumbUrl.'" border="0" width="100" height="100" />';

	$UserUrlLink=(strlen($row->fullName)>8)?'<a href="'.$row->website.'" rel="nofollow" target="_new">'.$row->fullName.'</a>':$row->fullName; //image path to large image
	
	
	if(!$disableImg)
	{
	$marginLftZero='';
	$imageblock = '<div class="image">'.$imageLink.'</div>';
	}else{
	$marginLftZero = 'style="margin-left:0px!important;"';
	$imageblock ='';
	}
	
	echo '<li><div class="monialItem">'.$imageblock.'<div class="item-post"><div '.$marginLftZero.' class="message">'.$row->message_long.'</div>
			<div class="postername">'.$UserUrlLink.','.$row->aboutauthor.'</div></div></div>';
	$k = 1 - $k;
	echo '</li>';
}
$cache = & JFactory::getCache();
?>  

</ul>
<div class="next_page"></div>
<div class="prev_page"></div>
<div id="page_data" style="display:none">
<?php   
	echo '<form action="'.JRoute::_("index.php?option=com_eztestimonial").'" method=\"post\">';
	echo '<div class="pagination" align="center">'.$this->pagination->getListFooter().'</div>';
	echo '<input type="hidden" name="view" value="testimonials" /><input type="hidden" name="option" value="com_eztestimonial" /></form>';
	#  echo '<div class="pagination">'.$this->crdt.'</div><br />';
?>
</div>
<div class="num_of_page"></div>
<script>
	jQuery(".component-content").css("position","relative");
	
	var num_of_page = jQuery("#page_data .list-footer .counter").html();
	jQuery(".num_of_page").html(num_of_page);	
	var next = jQuery("#page_data .list-footer .pagination-next").html();
	var prev = jQuery("#page_data .list-footer .pagination-prev").html();
	
	jQuery(".next_page").html(next).css({"position":"absolute","top":"43%","right":"20px"});
	jQuery(".prev_page").html(prev).css({"position":"absolute","top":"43%","left":"20px"});
</script>