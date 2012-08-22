<?php
/**
* @package 		ezTestimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

jimport( 'joomla.application.application' );
$document =& JFactory::getDocument();
JHTML::_( 'behavior.calendar' );
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$document->addStyleSheet('components/com_testimonial/assets/css/formstyle.css');
	$validScripts = '
	function isInt(val)
	{var intRegex = /^\d+$/;
	if(intRegex.test(val)) {return true;}else{return false;}
	}
	function chkRtng()
	{
		var rtng =  $(\'jform_ratingx\').value;
		if(rtng>5||rtng<0 ||isInt(rtng)==false) $(\'jform_ratingx\').value=\'5\';
	}
	';
$document->addScriptDeclaration($validScripts);
?>

<form enctype="multipart/form-data" onsubmit="chkRtng()" class="form-validate" id="user-form" name="adminForm" method="post" action="">
<?php echo AdminTestimonController::editBox(); ?>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="view" value="edit" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="option" value="com_eztestimonial" />
</form>