<?php
/**
* @package 		ezTestimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
	// no direct access
	defined( '_JEXEC' ) or die( 'Restricted access' ); 

	JHtml::_("behavior.formvalidation");
	$lang = &JFactory::getLanguage();
	$curr_lang = $lang->get("tag");

	$document 			= &JFactory::getDocument();
	$user 				= &JFactory::getUser();
	$app 				= JFactory::getApplication();
	$myparams 			= &JComponentHelper::getParams('com_eztestimonial');
	$whocanpost 		= $myparams->getValue('data.params.whocanpost');
	$disableadding 		= $myparams->getValue('data.params.disableadding',0);
	$disableImg 		= $myparams->getValue('data.params.disableImg',0);
	if($disableadding==1)
	{
	$app->enqueueMessage(JText::_('COM_TESTIMONIALS_DISABLED'), 'error');
	$app->redirect(JRoute::_("index.php?option=com_eztestimonial&view=testimonials"));
	}

	if($whocanpost==1 && $user->guest)
	{
		$encodedurl = base64_encode(JRoute::_("index.php?option=com_eztestimonial&view=form"));
		$returnUrl			= JRoute::_("index.php?option=com_users&return=".$encodedurl);
		$app->redirect($returnUrl);
	}

	$document->addStyleSheet('components/com_eztestimonial/assets/css/formstyle.css');
	$document->addScript('components/com_eztestimonial/assets/js/moostarrating.js');

	$validScripts = 'window.addEvent(\'domready\', function(){
		MooTools.lang.setLanguage("'.$curr_lang.'");
		var myForm = document.id(\'testimonialForm\'),
		myResult = document.id(\'myResult\');
		myForm.getElements(\'[type=text], textarea\').each(function(el){new OverText(el);});
		new Form.Validator.Inline(myForm);});
		MooStarRatingImages.defaultImageFolder =
		\''.JText::_(JURI::base().'components/com_eztestimonial/assets/images').'\';
		var basicRating = new MooStarRating({ form: \'basic\' });
		window.addEvent("domready",function() {
		var advancedRating = new MooStarRating({
			form: \'testimonialForm\',
			radios: \'rating\',
			half: false, 
			imageEmpty: \'star_empty.png\', 
			imageFull:  \'star_full.png\', 
			imageHover: \'star_boxed_hover.png\', 
			width: 17, 
			tip: \'Rate <i>[VALUE] / 5</i>\',
			tipTarget: $(\'htmlTip\'), 
			tipTargetType: \'html\'
		});});';

	$document->addScriptDeclaration($validScripts);

?>

<div id="myResult"></div>

<form id="testimonialForm" action="" method="post" class="cmxform" enctype="multipart/form-data">

<fieldset>

  <legend><?php echo JText::_('COM_TESTIMONIALS_FORM_LEGEND');?></legend>

  <ol>

    <li>

      <label for="name"><?php echo JText::_('COM_TESTIMONIALS_FORM_NAME');?><em> *</em></label>

      <input name="iname" type="text" class="required" id="iname" <?php 

	  if(!$user->guest)

	  {

	  	echo ' value="'.$user->name.'"';

	  }else{

	  	echo ' value="'.JRequest::getVar('iname').'" ';

	  }

	  ?> size="40" maxlength="50" />

    </li>

    <li>

      <label for="name"><?php echo JText::_('COM_TESTIMONIALS_FORM_EMAIL');?><em> *</em></label>

      <input name="iemail" type="text" class="required validate-email" id="iemail"<?php 

	  if(!$user->guest)

	  {

	  	echo ' value="'.$user->email.'"';

	  }else{

	  	echo ' value="'.JRequest::getVar('iemail').'" ';

	  }

	  ?> size="40" maxlength="50" />

    </li>

    <li>

      <label for="iaddress"><?php echo JText::_('COM_TESTIMONIALS_FORM_LOCATION');?><em> *</em></label>

      <input name="iaddress" type="text" class="required" id="iaddress" value="<?php echo JRequest::getVar('iaddress'); ?>" size="40" maxlength="50" />

    </li>

    <li>

      <label for="iaddress"><?php echo JText::_('COM_TESTIMONIALS_FORM_WEBSITE');?></label>

      <input name="iwebsite" type="text" id="iwebsite" value="<?php echo JRequest::getVar('iwebsite'); ?>" size="40" maxlength="100" />

    </li>
<?php
if(!$disableImg)
{
?> 
    <li>

      <label for="image"><?php echo JText::_('COM_TESTIMONIALS_FORM_IMAGE');?></label>

      <input id="iimage" name="iimage"  type="file" size="40" />

    </li>
<?php
}
?>


    <li>

      <label for="imessage"><?php echo JText::_('COM_TESTIMONIALS_FORM_TESTIMONIAL');?><em> *</em></label>

      <textarea id="imessage" name="imessage" class="required" cols="40" rows="4"><?php echo JRequest::getVar('imessage'); ?></textarea>

    </li>

    

    <li>

      <label for="iboutme"><?php echo JText::_('COM_TESTIMONIALS_FORM_BOUTME');?><em> *</em></label>

      <input name="iboutme" type="text" id="iboutme" value="<?php echo JRequest::getVar('iboutme'); ?>" size="40" maxlength="50" class="required" />

    </li>

    <li>

        <label><?php echo JText::_('COM_TESTIMONIALS_FORM_RATINGTXT');?></label>

        <input type="radio" name="rating" value="1" checked="checked">

        <input type="radio" name="rating" value="2" >

        <input type="radio" name="rating" value="3">

        <input type="radio" name="rating" value="4">

        <input type="radio" name="rating" value="5">

      <span id="htmlTip"></span>

    </li>

    <li>

      <?php echo testimonialController::LoadAntiSpam(); ?>

    </li>

        <li>

      <input id="submit"  type="submit" value="<?php echo JText::_('COM_TESTIMONIALS_FORM_SUBMIT');?>" />
    </li>



  </ol>

</fieldset>

<?php echo JHTML::_( 'form.token' ); ?>

<input type="hidden" name="view" value="form" />

<input type="hidden" name="task" value="submitmonial" />

<input type="hidden" name="option" value="com_eztestimonial" />

</form>