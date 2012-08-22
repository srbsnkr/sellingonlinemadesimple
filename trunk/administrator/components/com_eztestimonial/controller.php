<?php

/**

* @package 		ezTestimonial Component

* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.

* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php

* @author		Saran Chamling (saaraan@gmail.com)

*/ 

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' ); 



jimport( 'joomla.application.component.controller' );



class AdminTestimonController extends JController

{

	function display() {

		

			switch (JRequest::getVar( 'view' )){

			

				case "testimonials":

							JRequest::setVar('view', 'eztestimonials' );

							break;

				case "edit":

							JRequest::setVar('view', 'editbox' );

							break;

				default:

							JRequest::setVar('view', 'eztestimonials' );

			}

			switch (JRequest::getVar( 'task' )){

			

				case "remove":

							AdminTestimonController::delete();

							break;

				case "unpublish":

							AdminTestimonController::unpublish();

							break;

				case "publish":

							AdminTestimonController::publish();

							break;

				case "save":

							AdminTestimonController::saveEdit();

							break;





			}

		

			parent::display();

	}

	

	function delete()

	{

		jimport( 'joomla.filesystem.file' );

		$app = JFactory::getApplication();

		$myparams 			= &JComponentHelper::getParams('com_eztestimonial');

		$imageSubFolder 	= $myparams->getValue('data.params.imagefolder');

		$ImgUrl 			= JPATH_ROOT.'/images/'.$imageSubFolder.'/';

		$db = & JFactory::getDBO();

		

		$delcount = 0;

		$row =& $this->getTable();

		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		

		foreach($cids as $cid) {

			$query = "SELECT image_name FROM #__testimonials WHERE id =".$cid;

			$db->setQuery($query);

			$tbrow = $db->loadAssoc();

			if($tbrow && strlen($tbrow["image_name"])>0)

			{

				$file1 = JPath::clean($ImgUrl.'thumb50_'.$tbrow["image_name"]);

				$file2 = JPath::clean($ImgUrl.'thumb_'.$tbrow["image_name"]);

				$file3 = JPath::clean($ImgUrl.$tbrow["image_name"]);

				@chmod($file1, 0777);@chmod($file2, 0777);@chmod($file3, 0777);

				 if (!@unlink($file1)) {$app->enqueueMessage(JText::sprintf(JText::_('COM_TESTIMONIALS_BAK_DELETEIMGFAILD'),$file1), 'error');}

				 if (!@unlink($file2)) {$app->enqueueMessage(JText::sprintf(JText::_('COM_TESTIMONIALS_BAK_DELETEIMGFAILD'),$file2), 'error');}

				 if (!@unlink($file3)) {$app->enqueueMessage(JText::sprintf(JText::_('COM_TESTIMONIALS_BAK_DELETEIMGFAILD'),$file3), 'error');}

			}

		}



		

		foreach($cids as $cid) {

			if (!$row->delete( $cid )) {

				$this->setError( $row->getErrorMsg() );

				return false;

				

			}else{

			$delcount++;

			}

		}

		$app->enqueueMessage(JText::sprintf(JText::_('COM_TESTIMONIALS_BAK_DELETED'),$delcount), 'error');

	 	$app->redirect($this->returnUrl());

	}

	

	function publish()

	{

		$app = JFactory::getApplication();

		$pubcount = 0;

		$row =& $this->getTable();

		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		foreach($cids as $cid) 

		{

			$row->id = $cid;

			$row->approved = 1;

			if (!$row->store()) {

				$this->setError( $row->getErrorMsg() );

				return false;

			}else{

			$pubcount++;

			}

		}

	 	$app->enqueueMessage(JText::sprintf(JText::_('COM_TESTIMONIALS_BAK_PUBLISHED'),$pubcount), 'message');

		$app->redirect($this->returnUrl());

	}

	

	function unpublish()

	{

		$app = JFactory::getApplication();

		$unpubcount = 0;

		$row =& $this->getTable();

		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		foreach($cids as $cid) 

		{

			$row->id = $cid;

			$row->approved = 0;

			if (!$row->store()) {

				$this->setError( $row->getErrorMsg() );

				return false;

			}else{

			$unpubcount++;

			}

		}

	 	$app->enqueueMessage(JText::sprintf(JText::_('COM_TESTIMONIALS_BAK_UNPUBLISHED'),$unpubcount), 'error');

	 	$app->redirect($this->returnUrl());

	}



	function saveEdit()

	{

		$app = JFactory::getApplication();

		$row =& $this->getTable();

		if (!$row->bind( JRequest::get( 'post' ) )) {

			return JError::raiseWarning( 500, $row->getError() );

		}

		if (!$row->store()) {

			JError::raiseError(500, $row->getError() );

		}

	}

	

	function editBox()

	{

		$app = JFactory::getApplication();

		$db = & JFactory::getDBO();

		$cid = JRequest::getVar( 'cid');

		

		$myparams 			= &JComponentHelper::getParams('com_eztestimonial');

		$imageSubFolder 	= $myparams->getValue('data.params.imagefolder');

		$ImgUrl 			= JRoute::_(JURI::root().'images/'.$imageSubFolder.'/');



		$query = "SELECT * FROM #__testimonials WHERE id = '".$cid."'";

		$db->setQuery($query);

		$row = $db->loadAssoc();

		if($row)

		{

			$calender = JHTML::_('calendar',$row["added_date"],'added_date','added_date','%Y-%m-%d 00:00:00','size="50"');

			$fields = '<fieldset class="adminform">

			<legend>Testimonial Details</legend>

			<ul class="adminformlist">

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_NAME").'" class="hasTip required" for="jform_name" >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_NAME").'<span class="star">&nbsp;*</span></label>	

			<input id="jform_name" name="fullName" type="text" size="40" value="'.$row["fullName"].'" class="inputbox required" required="required" />

			</li>

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_EMAIL").'" class="hasTip required" for="jform_email" >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_EMAIL").'<span class="star">&nbsp;*</span></label>				

			<input id="jform_email" name="email" type="text" size="40" value="'.$row["email"].'" class="required validate-email" required="required" />

			</li>

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_LOCATION").'" class="hasTip" for="jform_address" >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_LOCATION").'</label>				

			<input id="jform_address" name="location" type="text" size="40" value="'.$row["location"].'" class="inputbox required" required="required"/>

			</li>

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_WEBSITE").'" class="hasTip" for="jform_website" >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_WEBSITE").'</label>

			<input id="jform_website" name="website" type="text" size="40" value="'.$row["website"].'" />				

			</li>

			<li><label title="'.JText::sprintf(JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_PHOTONAME"),$ImgUrl).'" class="hasTip" for="jform_image">'.JText::_("COM_TESTIMONIALS_BAK_EDIT_IMGNAME").'</label>

			<input id="jform_website" name="image_name" type="text" size="40" value="'.$row["image_name"].'" />				

			</li>



			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_SUMMARY").'" class="hasTip" for="jform_summary " >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_SUMMARY").'</label>				

			<input id="jform_summary " name="message_summary" type="text" size="80" value="'.$row["message_summary"].'" class="inputbox required" required="required"/>

			</li>

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_LONGMESSAGE").'" class="hasTip required" for="jform_longmessage" id="jform_longmessage">'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TESTIMONIAL").'<span class="star">&nbsp;*</span></label>	

				<textarea id="jform_message" name="message_long" cols="40" rows="10" class="inputbox required" required="required">'.$row["message_long"].'</textarea>

			</li>

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_ABOUTME").'" class="hasTip" for="jform_summary " >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_ABOUTME").'</label>				

			<input id="jform_aboutauthor" name="aboutauthor" type="text" size="80" value="'.$row["aboutauthor"].'" class="inputbox required" required="required" maxlength="100"/>

			</li>

			<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_RATING").'" class="hasTip" for="jform_rating" >'.JText::_("COM_TESTIMONIALS_BAK_EDIT_RATING").'</label>

			<input id="jform_ratingx" name="rating" type="text" size="4" value="'.$row["rating"].'" class="required" onblur="if(this.value>5|| this.value<0 || isInt(this.value)==false) this.value=\'5\';"  onfocus="if(this.value>5 || this.value<0 || isInt(this.value)==false) this.value=\'5\';"  required="required validate-integer"/>			

			</li>



<li><label title="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_TIP_DATE").'" class="hasTip" for="jform_submitteddate" aria-invalid="false">'.JText::_("COM_TESTIMONIALS_BAK_EDIT_DATE").'</label>	

	

'.$calender.' </li>



			<li>

<label title="" class="hasTip" for="jform_submit" id="jform_submit" aria-invalid="false">&nbsp;</label>	

			<input name="" type="submit" value="'.JText::_("COM_TESTIMONIALS_BAK_EDIT_SUBMIT").'" /><input name="id" type="hidden" value="'.$row["id"].'" />

			</li>

			</ul>

			</fieldset>';

			return $fields;

		}else{

		 die("Invalid Request!");

		}

		

	}

	function GetCredit()
	{
		$myparams 		= &JComponentHelper::getParams('com_eztestimonial');
		$rmvcd 			= md5($myparams->getValue('data.params.brandingremoval'));

		if('a32ed01babf3c9be85201d9307758151' != $rmvcd)
		{
		return '<div align="center"><a href="http://www.saaraan.com" target="_new">Powered by Saaraan</a> | <a href="http://extensions.joomla.org/extensions/contacts-and-feedback/testimonials-a-suggestions/20311" target="_blank">Review</a> | <a href="http://saaraan.com/payment/brand_removal.php" target="_blank">Branding removal Option</a></div>';
		}
	}

	public function getTable($type = 'Testimonials', $prefix = 'Table', $config = array()) 

	{

		return JTable::getInstance($type, $prefix, $config);

	}

	

	public function returnUrl()

	{

		return JRoute::_("index.php?option=com_eztestimonial");

	}

}

?>

